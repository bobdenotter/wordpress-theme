<?php

namespace Bolt\Extension\Bobdenotter\WPTheme;

use Bolt\Application;
use Bolt\BaseExtension;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/wp_functions.php');

class Extension extends BaseExtension
{


    public function initialize()
    {
        $end = $this->app['config']->getWhichEnd();

        if ($end =='frontend') {

            chdir($this->app['paths']['themepath']);

            $GLOBALS['config'] = $this->app['config'];
            $GLOBALS['paths'] = $this->app['paths'];
            $GLOBALS['currentuser'] = $this->app['users']->getCurrentUser();
            $GLOBALS['safe_render'] = $this->app['safe_render'];

        }

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

    public function record($contenttypeslug, $slug = '') {

        // dump($this->app['config']->get('general/theme'));

        // $phpfile = $this->app['paths']['themepath'] . '/single.php';

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
        $content = $this->app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true, 'log_not_found' => !is_numeric($slug)));

        if (!$content && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $content = $this->app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $GLOBALS['content'] = $content;
        $GLOBALS['record'] = $content;

        return $this->render('single.php');

    }

    private function render($templatefile)
    {
        ob_start();

        require_once($templatefile);

        $html = ob_get_clean();

        $html = $this->lowercasePDangit($html);

        return $html;

    }

    private function lowercasePDangit($html)
    {
        return preg_replace('/WordPress/i', 'Wordpress', $html);
    }

}







