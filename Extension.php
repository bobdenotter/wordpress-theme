<?php

namespace Bolt\Extension\Bobdenotter\WPTheme;

use Bolt\Application;
use Bolt\BaseExtension;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/wp_functions.php');
require_once(__DIR__ . '/wp_helper.php');

class Extension extends BaseExtension
{


    public function initialize()
    {
        $end = $this->app['config']->getWhichEnd();

        if ($end =='frontend') {

            chdir($this->app['paths']['themepath']);

            $GLOBALS['config'] = $this->app['config'];
            $GLOBALS['paths'] = $this->app['paths'];
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


    public function homepage()
    {
        // Get the 'record' / 'records' for the homepage
        $content = $this->app['storage']->getContent($this->app['config']->get('general/homepage'));

        if (is_array($content)) {
            $first = current($content);
            $globals[$first->contenttype['slug']] = $content;
        } elseif (!empty($content)) {
            $globals['record'] = $content;
            $globals[$content->contenttype['singular_slug']] = $content;
        }

        // We most likely also want a few 'posts'.
        $posts = $this->app['storage']->getContent('posts/latest/6');

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
        $content = $this->app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true, 'log_not_found' => !is_numeric($slug)));

        if (!$content && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $content = $this->app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $globals = [
            'content' => $content,
            'record' => $content
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
        $html = ob_get_clean();

        $html = $this->lowercasePDangit($html);

        return $html;

    }

    private function lowercasePDangit($html)
    {
        return preg_replace('/WordPress/i', 'Wordpress', $html);
    }

}







