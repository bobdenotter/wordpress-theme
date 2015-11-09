<?php

namespace Bolt\Extension\Bobdenotter\WPTheme;

use Bolt\Application;
use Bolt\BaseExtension;
use Bolt\Pager;
use Symfony\Component\HttpFoundation\Request;
use Bolt\Extension\Bobdenotter\WPTheme\WPhelper;

require_once(__DIR__ . '/src/WPhelper.php');

class Extension extends BaseExtension
{


    public function initialize()
    {
        $end = $this->app['config']->getWhichEnd();

        if ($end =='frontend') {
            $this->loadWPCruft();
        }

        $root = $this->app['resources']->getUrl('bolt');
        $this->addMenuOption('WP Theme', $root . 'wp-theme', 'fa:wordpress');
        $this->app->get($root . 'wp-theme', array($this, 'wpThemeDashboard'))->bind('wpThemeDashboard');
        $this->app->get($root . 'wp-theme/gather', array($this, 'wpThemeGatherSettings'))->bind('wpThemeGatherSettings');

    }

    public function getName()
    {
        return "WP Theme";
    }

    public function before(Request $request)
    {

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
        require_once(__DIR__ . '/wp-functions.php');
        require_once(__DIR__ . '/wp-plugin.php');
        require_once(__DIR__ . '/wp-includes/widgets.php');
        require_once(__DIR__ . '/wp-includes/class-wp-customize-control.php');

        chdir($this->app['paths']['themepath']);

        $GLOBALS['config'] = $this->app['config'];
        $GLOBALS['paths'] = $this->app['paths'];

        if (file_exists('functions.php')) {
            require_once('functions.php');
        }
    }


    public function homepage()
    {
        // Get the 'record' / 'records' for the homepage
        $content = $this->app['storage']->getContent($this->app['config']->get('general/homepage'));

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
        $contenttype = $this->app['storage']->getContentType($contenttypeslug);

        // If the contenttype is 'viewless', don't show the record page.
        if (isset($contenttype['viewless']) && $contenttype['viewless'] === true) {
            return $this->app->abort(Response::HTTP_NOT_FOUND, "Page $contenttypeslug/$slug not found.");
        }

        // Perhaps we don't have a slug. Let's see if we can pick up the 'id', instead.
        if (empty($slug)) {
            $slug = $this->app['request']->get('id');
        }

        $slug = $this->app['slugify']->slugify($slug);

        // First, try to get it by slug.
        $post = $this->app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true, 'log_not_found' => !is_numeric($slug)));

        if (!$post && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $post = $this->app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $globals = [
            'posts' => [ $post ],
            'post' => $post
        ];

        return $this->render('single.php', $globals);

    }

    private function render($templatefile, $globals = [])
    {
        $globals['app'] = $this->app;
        $globals['currentuser'] = $this->app['users']->getCurrentUser();

        foreach($globals as $key =>$value) {
            $GLOBALS[$key] = $value;
        }

        ob_start();

        require_once($templatefile);

        do_action('wp_enqueue_scripts');

        $html = ob_get_clean();

        $html = WPhelper::outputQueue($html);

        return $html;

    }

    private function getPagedRecords($contenttypeslug = 'posts')
    {
        $contenttype = $this->app['storage']->getContentType($contenttypeslug);

        $pagerid = Pager::makeParameterId($contenttypeslug);
        // First, get some content
        $page = $this->app['request']->query->get($pagerid, $this->app['request']->query->get('page', 1));

        // Theme value takes precedence over CT & default config
        // @see https://github.com/bolt/bolt/issues/3951
        if (!$amount = $this->app['config']->get('theme/listing_records', false)) {
            $amount = empty($contenttype['listing_records']) ? $this->app['config']->get('general/listing_records') : $contenttype['listing_records'];
        }
        if (!$order = $this->app['config']->get('theme/listing_sort', false)) {
            $order = empty($contenttype['sort']) ? null : $contenttype['sort'];
        }
        // If $order is not set, one of two things can happen: Either we let `getContent()` sort by itself, or we
        // explicitly set it to sort on the general/listing_sort setting.
        if ($order === null) {
            $taxonomies = $this->app['config']->get('taxonomy');
            $hassortorder = false;
            if (!empty($contenttype['taxonomy'])) {
                foreach ($contenttype['taxonomy'] as $contenttypetaxonomy) {
                    if ($taxonomies[$contenttypetaxonomy]['has_sortorder']) {
                        // We have a taxonomy with a sortorder, so we must keep $order = false, in order
                        // to let `getContent()` handle it. We skip the fallback that's a few lines below.
                        $hassortorder = true;
                    }
                }
            }
            if (!$hassortorder) {
                $order = $this->app['config']->get('general/listing_sort');
            }
        }

        $content = $this->app['storage']->getContent($contenttype['slug'], ['limit' => $amount, 'order' => $order, 'page' => $page, 'paging' => true]);

        return $content;
    }


    public function wpThemeDashboard(Request $request)
    {
        $data = [];

        $this->app['twig.loader.filesystem']->addPath(__DIR__);

        return $this->app['twig']->render('wp-theme-templates/dashboard.twig', $data);

    }

    public function wpThemeGatherSettings(Request $request)
    {
        global $wp_filter;

        $data = [];

        $this->loadWPCruft();

        $customize = new WPcustomize();

        do_action('customize_register', $customize);

        // dump($wp_filter);
        // $customize->dumpSettings();
        $data['output'] = $customize->getYaml();

        $data['text'] = "The following configuration file was generated automatically from the <tt>twentyfifteen</tt> theme, and will be saved as <tt>config.yml</tt> in the theme folder.";
        // dump($data);

        $this->app['twig.loader.filesystem']->addPath(__DIR__);

        return $this->app['twig']->render('wp-theme-templates/dashboard.twig', $data);

    }

}







