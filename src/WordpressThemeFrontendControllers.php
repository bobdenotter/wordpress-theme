<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Bolt\Configuration\ResourceManager;
use Bolt\Helpers\Input;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use utilphp\util;

class WordpressThemeFrontendControllers implements ControllerProviderInterface
{
    /** @var Application */
    protected $app;
    protected $extension;

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $this->app = $app;

        $requiremements = new \Bolt\Controller\Requirement($app['config']);

        /** @var ControllerCollection $ctr */
        $ctr = $app['controllers_factory'];

        $ctr->match('/', [$this, 'homepage'])->bind('wp-homepage');

        $ctr->match('/search', [$this, 'search'])->bind('wp-search');

        $ctr->match('/{contenttypeslug}/{slug}', [$this, 'record'])
            ->bind('wp-record')
            ->assert('contenttypeslug', $requiremements->anyContentType());

        $ctr->match('/{contenttypeslug}', [$this, 'listing'])
            ->bind('wp-listing')
            ->assert('contenttypeslug', $requiremements->pluralContentTypes());

       $ctr->match('/{taxonomytype}/{slug}', [$this, 'taxonomy'])
            ->bind('wp-taxonomy')
            ->assert('taxonomytype', $requiremements->anyTaxonomyType());

        $ctr->before([$this, 'before']);

        return $ctr;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
    }

    public function before(Request $request)
    {
        $this->wordpressHelper = new WordpressHelper();
        $this->wordpressHelper->loadWPCruft();

        $route = $request->get('_route');

        if (substr($route, 0, 3) === 'wp-') {
            if (file_exists('functions.php')) {
                require_once('functions.php');
            }
            $GLOBALS['request'] = $request;
        }
    }

    public function after()
    {

    }

    public function homepage()
    {
        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
        $app = ResourceManager::getApp();

        // Get the 'record' / 'records' for the homepage
        $content = $app['storage']->getContent($app['config']->get('general/homepage'));

        if (is_array($content)) {
            $first = current($content);
            $globals[$first->contenttype['slug']] = $content;
        } elseif (!empty($content)) {
            $globals['post'] = $content;
            $globals[$content->contenttype['singular_slug']] = $content;
        }

        // We most likely also want a few 'posts'.
        $posts = $this->getPagedRecords('posts');

        if (is_array($posts)) {
            $globals['posts'] = $posts;
        }

        return $this->render('index.php', $globals);
    }

    public function record($contenttypeslug, $slug = '')
    {
        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
        $app = ResourceManager::getApp();

        $contenttype = $app['storage']->getContentType($contenttypeslug);

        // If the contenttype is 'viewless', don't show the record page.
        if (isset($contenttype['viewless']) && $contenttype['viewless'] === true) {
            return $app->abort(Response::HTTP_NOT_FOUND, "Page $contenttypeslug/$slug not found.");
        }

        // Perhaps we don't have a slug. Let's see if we can pick up the 'id', instead.
        if (empty($slug)) {
            $slug = $app['request']->get('id');
        }

        $slug = $app['slugify']->slugify($slug);

        // First, try to get it by slug.
        $post = $app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true, 'log_not_found' => !is_numeric($slug)));

        if (!$post && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $post = $app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $globals = [
            'posts' => [ $post ],
            'post' => $post
        ];

        return $this->render('single.php', $globals);

    }

    public function listing($contenttypeslug)
    {
        $app = ResourceManager::getApp();

        // Get the 'record' / 'records' for the homepage
        $content = $app['storage']->getContent($app['config']->get('general/homepage'));

        if (is_array($content)) {
            $first = current($content);
            $globals[$first->contenttype['slug']] = $content;
        } elseif (!empty($content)) {
            $globals['post'] = $content;
            $globals[$content->contenttype['singular_slug']] = $content;
        }

        // We most likely also want a few 'posts'.
        $posts = $this->getPagedRecords($contenttypeslug);

        if (is_array($posts)) {
            $globals['posts'] = $posts;
        }

        return $this->render('archive.php', $globals);
    }

    public function search(Request $request)
    {
        $app = ResourceManager::getApp();
        $q = '';
        $context = __FUNCTION__;

        if ($request->query->has('q')) {
            $q = $request->query->get('q');
        } elseif ($request->query->has($context)) {
            $q = $request->query->get($context);
        }
        $q = Input::cleanPostedData($q, false);

        $page = $this->app['pager']->getCurrentPage($context);

        // Theme value takes precedence over default config @see https://github.com/bolt/bolt/issues/3951
        $pageSize = $app['config']->get('theme/search_results_records', false);
        if ($pageSize === false && !$pageSize = $app['config']->get('general/search_results_records', false)) {
            $pageSize = $app['config']->get('theme/listing_records', false) ?: $app['config']->get('general/listing_records', 10);
        }

        $offset = ($page - 1) * $pageSize;
        $limit = $pageSize;

        // set-up filters from URL
        $filters = [];
        foreach ($request->query->all() as $key => $value) {
            if (strpos($key, '_') > 0) {
                list($contenttypeslug, $field) = explode('_', $key, 2);
                if (isset($filters[$contenttypeslug])) {
                    $filters[$contenttypeslug][$field] = $value;
                } else {
                    $contenttype = $this->getContentType($contenttypeslug);
                    if (is_array($contenttype)) {
                        $filters[$contenttypeslug] = [
                            $field => $value,
                        ];
                    }
                }
            }
        }
        if (count($filters) == 0) {
            $filters = null;
        }

        $result = $app['storage']->searchContent($q, $contenttypes, $filters, $limit, $offset);

        /** @var \Bolt\Pager\PagerManager $manager */
        $manager = $this->app['pager'];
        $manager
            ->createPager($context)
            ->setCount($result['no_of_results'])
            ->setTotalpages(ceil($result['no_of_results'] / $pageSize))
            ->setCurrent($page)
            ->setShowingFrom($offset + 1)
            ->setShowingTo($offset + count($result['results']));

        $manager->setLink($this->generateUrl('search', ['q' => $q]) . '&page_search=');

        $globals = [
            'posts'      => $result['results'],
            $context       => $result['query']['sanitized_q'],
            'searchresult' => $result,
        ];

        return $this->render('search.php', $globals);
    }


public function taxonomy(Request $request, $taxonomytype, $slug)
    {
        $app = ResourceManager::getApp();

        $taxonomy = $app['storage']->getTaxonomyType($taxonomytype);
        // No taxonomytype, no possible content.
        if (empty($taxonomy)) {
            return false;
        } else {
            $taxonomyslug = $taxonomy['slug'];
        }
        // First, get some content
        $context = $taxonomy['singular_slug'] . '_' . $slug;
        $page = $this->app['pager']->getCurrentPage($context);
        // Theme value takes precedence over default config @see https://github.com/bolt/bolt/issues/3951
        $amount = $app['config']->get('theme/listing_records', false) ?: $app['config']->get('general/listing_records');

        // Handle case where listing records has been override for specific taxonomy
        if (array_key_exists('listing_records', $taxonomy) && is_int($taxonomy['listing_records'])) {
            $amount = $taxonomy['listing_records'];
        }

        $order = $app['config']->get('theme/listing_sort', false) ?: $app['config']->get('general/listing_sort');
        $content = $app['storage']->getContentByTaxonomy($taxonomytype, $slug, ['limit' => $amount, 'order' => $order, 'page' => $page]);

        if (!$this->isTaxonomyValid($content, $slug, $taxonomy)) {
            $this->abort(Response::HTTP_NOT_FOUND, "No slug '$slug' in taxonomy '$taxonomyslug'");

            return;
        }

        $name = $slug;
        // Look in taxonomies in 'content', to get a display value for '$slug', perhaps.
        foreach ($content as $record) {
            $flat = util::array_flatten($record->taxonomy);
            $key = $this->app['resources']->getUrl('root') . $taxonomy['slug'] . '/' . $slug;
            if (isset($flat[$key])) {
                $name = $flat[$key];
            }
            $key = $this->app['resources']->getUrl('root') . $taxonomy['singular_slug'] . '/' . $slug;
            if (isset($flat[$key])) {
                $name = $flat[$key];
            }
        }

        $globals = [
            'posts'        => $content,
            'slug'         => $name,
            'taxonomy'     => $app['config']->get('taxonomy/' . $taxonomyslug),
            'taxonomytype' => $taxonomyslug,
        ];

        return $this->render('archive.php', $globals);
    }


    private function getPagedRecords($contenttypeslug = 'posts')
    {
        $app = ResourceManager::getApp();

        $contenttype = $app['storage']->getContentType($contenttypeslug);

        // $pagerid = Pager::makeParameterId($contenttypeslug);
        // First, get some content
        $page = $app['request']->query->get($pagerid, $app['request']->query->get('page', 1));

//        // Theme value takes precedence over CT & default config
//        // @see https://github.com/bolt/bolt/issues/3951
//        if (!$amount = $app['config']->get('theme/listing_records', false)) {
//            $amount = empty($contenttype['listing_records']) ? $app['config']->get('general/listing_records') : $contenttype['listing_records'];
//        }
//        if (!$order = $app['config']->get('theme/listing_sort', false)) {
//            $order = empty($contenttype['sort']) ? null : $contenttype['sort'];
//        }
//        // If $order is not set, one of two things can happen: Either we let `getContent()` sort by itself, or we
//        // explicitly set it to sort on the general/listing_sort setting.
//        if ($order === null) {
//            $taxonomies = $app['config']->get('taxonomy');
//            $hassortorder = false;
//            if (!empty($contenttype['taxonomy'])) {
//                foreach ($contenttype['taxonomy'] as $contenttypetaxonomy) {
//                    if ($taxonomies[$contenttypetaxonomy]['has_sortorder']) {
//                        // We have a taxonomy with a sortorder, so we must keep $order = false, in order
//                        // to let `getContent()` handle it. We skip the fallback that's a few lines below.
//                        $hassortorder = true;
//                    }
//                }
//            }
//            if (!$hassortorder) {
//                $order = $app['config']->get('general/listing_sort');
//            }
//        }

        $content = $app['storage']->getContent($contenttype['slug'], ['limit' => $amount, 'order' => $order, 'page' => $page, 'paging' => true]);

        return $content;
    }

    private function render($templatefile, $globals = [])
    {
        $app = ResourceManager::getApp();

        $globals['app'] = $app;
        $globals['currentuser'] = $app['users']->getCurrentUser();

        foreach($globals as $key =>$value) {
            $GLOBALS[$key] = $value;
        }

        ob_start();
        require_once($templatefile);
        wp_scripts();
        do_action('wp_enqueue_scripts');

        $html = ob_get_clean();

        $html = WordpressHelper::outputQueue($html);

        return $html;
    }

    /**
     * Shortcut for {@see UrlGeneratorInterface::generate}
     *
     * @param string $name          The name of the route
     * @param array  $params        An array of parameters
     * @param int    $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     */
    protected function generateUrl($name, $params = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $app = ResourceManager::getApp();

        /** @var UrlGeneratorInterface $generator */
        $generator = $app['url_generator'];

        return $generator->generate($name, $params, $referenceType);
    }

    /**
     * Check if the taxonomy is valid.
     *
     * @see https://github.com/bolt/bolt/pull/2310
     *
     * @param Content $content
     * @param string  $slug
     * @param array   $taxonomy
     *
     * @return boolean
     */
    protected function isTaxonomyValid($content, $slug, array $taxonomy)
    {
        if ($taxonomy['behaves_like'] === 'tags' && !$content) {
            return false;
        }

        $isNotTag = in_array($taxonomy['behaves_like'], ['categories', 'grouping']);
        $options = isset($taxonomy['options']) ? array_keys($taxonomy['options']) : [];
        $isTax = in_array($slug, $options);
        if ($isNotTag && !$isTax) {
            return false;
        }

        return true;
    }

}