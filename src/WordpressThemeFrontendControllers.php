<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Bolt\Configuration\ResourceManager;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

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

        /** @var ControllerCollection $ctr */
        $ctr = $app['controllers_factory'];

        $ctr->match('/', [$this, 'homepage'])->bind('wp-homepage');

        // TODO: Needs requirement:
        //     requirements:
        //      contenttypeslug: controller.requirement:anyContentType
        $ctr->match('/{contenttypeslug}/{slug}', [$this, 'record'])->bind('wp-record');


        // $ctr->match('/wptheme-gather', [$this, 'wpThemeGatherSettings'])->bind('wpThemeGatherSettings');

        $ctr->before([$this, 'before']);

        return $ctr;
    }

    public function before(Request $request)
    {
        $this->extension->loadWPCruft();

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

    public function setExtension($extension) {
        $this->extension = $extension;
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

    private function getPagedRecords($contenttypeslug = 'posts')
    {
        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
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
        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
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




}