<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;


use Bolt\Configuration\ResourceManager;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Bolt\Pager\Pager;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;


class WordpressThemeExtension extends SimpleExtension
{
    protected function registerMenuEntries()
    {
        $menu = new MenuEntry('wptheme-menu', 'wptheme-settings');
        $menu->setLabel('WordpressTheme Settings')
            ->setIcon('fa:wordpress')
            ->setPermission('settings')
        ;

        return [
            $menu,
        ];
    }

    protected function registerBackendRoutes(ControllerCollection $collection)
    {
        // GET requests on the /bolt/koala route
        $collection->get('/extend/wptheme-settings', 'wpThemeDashboard')->bind('wpThemeDashboard');;
        $collection->get('/extend/wptheme-gather', 'wpThemeGatherSettings')->bind('wpThemeGatherSettings');

    }

    public function before(Request $request)
    {
        $this->loadWPCruft();

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

    public function loadWPCruft()
    {
        $dirname = dirname(__DIR__) . '/wp-includes/';
        require_once($dirname . 'wp-functions.php');
        require_once($dirname . 'wp-plugin.php');
        require_once($dirname . 'cache.php');
        require_once($dirname . 'kses.php');
        require_once($dirname . 'formatting.php');
        require_once($dirname . 'widgets.php');
        require_once($dirname . 'theme.php');
        require_once($dirname . 'script-loader.php');
        require_once($dirname . 'class-wp-error.php');
        require_once($dirname . 'class-wp-theme.php');
        require_once($dirname . 'class-wp-widget.php');
        require_once($dirname . 'class-wp-customize-control.php');

        wp_cache_init();

        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
        $app = ResourceManager::getApp();

        chdir($app['paths']['themepath']);

        $GLOBALS['config'] = $app['config'];
        $GLOBALS['paths'] = $app['paths'];

        if (file_exists('functions.php')) {
            require_once('functions.php');
        }

        // Get the theme.yml, if it exists.
        if (is_readable($app['paths']['themepath'] . '/theme.yml')) {
            $yaml = new \Symfony\Component\Yaml\Parser();
            $GLOBALS['theme_config'] = $yaml->parse(file_get_contents($app['paths']['themepath'] . '/theme.yml'));
        }
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

    private function getPagedRecords($contenttypeslug = 'posts')
    {
        // TODO: Figure out why `$this->getContainer` doesn't work.
        // $app = $this->getContainer();
        $app = ResourceManager::getApp();

        $contenttype = $app['storage']->getContentType($contenttypeslug);

//        $pagerid = Pager::makeParameterId($contenttypeslug);
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


    public function wpThemeDashboard(Request $request)
    {
        $data = [];

        return $this->renderTemplate('dashboard.twig', $data);
    }

    public function wpThemeGatherSettings(Request $request)
    {
        global $wp_filter;

        $app = $this->getContainer();

        $data = [];

        $this->loadWPCruft();

        $customize = new WordpressCustomize($app);

        do_action('customize_register', $customize);

        // $customize->dumpSettings();

        $data['output'] = $customize->getYaml();

        $data['text'] = "The following configuration file was generated automatically from the <tt>" .
            $app['config']->get('general/theme') .
            "</tt> theme, and will be saved as <tt>config.yml</tt> in the theme folder.";
        // dump($data);

        $result = $customize->writeThemeYaml($data['output']);

        if (!$result) {
            echo "File not saved!";
        }

        return $this->renderTemplate('dashboard.twig', $data);
    }

}







