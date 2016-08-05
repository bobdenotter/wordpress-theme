<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Bolt\Configuration\ResourceManager;
use Bolt\Helpers\Str;

class WordpressHelper {

    public static $markCssOutputted;
    public static $cssQueue;
    public static $scriptQueue;

    public function loadWPCruft()
    {
        define('ABSPATH', dirname(__DIR__));
        define('WPINC', '/wp-includes');

        $dirname = dirname(__DIR__) . '/wp-includes/';
        require_once($dirname . 'cache.php');
        require_once($dirname . 'class-wp-customize-control.php');
        require_once($dirname . 'class-wp-error.php');
        require_once($dirname . 'class-wp-theme.php');
        require_once($dirname . 'class-wp-walker.php');
        require_once($dirname . 'class-wp-widget-factory.php');
        require_once($dirname . 'class-wp-widget.php');
        require_once($dirname . 'formatting.php');
        require_once($dirname . 'kses.php');
        require_once($dirname . 'nav-menu-template.php');
        require_once($dirname . 'script-loader.php');
        require_once($dirname . 'theme.php');
        // require_once($dirname . 'query.php');
        require_once($dirname . 'widgets.php');
        require_once($dirname . 'general-template.php');
        require_once($dirname . 'wp-functions.php');
        require_once($dirname . 'wp-plugin.php');

        wp_cache_init();

        $app = ResourceManager::getApp();

        chdir($app['paths']['themepath']);

        $GLOBALS['config'] = $app['config'];
        $GLOBALS['paths'] = $app['paths'];
        $GLOBALS['request'] = $app['request'];

        $GLOBALS['wp_widget_factory'] = new \WP_Widget_Factory();

        if (file_exists('functions.php')) {
            require_once('functions.php');
        }

        // Get the theme.yml, if it exists.
        if (is_readable($app['paths']['themepath'] . '/theme.yml')) {
            $yaml = new \Symfony\Component\Yaml\Parser();
            $GLOBALS['theme_config'] = $yaml->parse(file_get_contents($app['paths']['themepath'] . '/theme.yml'));
        }
    }

    /**
     * Print out a stub for an un-implemented function
     *
     */
    static function stub($functionname, $arguments)
    {

        $arguments = self::printParameters($arguments);

        if (!self::$markCssOutputted) {
            echo "<style>mark { background-color: #fff9c0; text-decoration: none; border: 1px solid #DDB; padding: 1px 3px; display: inline-block; font-size: 13px; } </style>";
            self::$markCssOutputted = true;
        }

        echo " <mark>{$functionname}({$arguments})</mark> ";
    }

    static function printParameters($parameters = array())
    {
        if (empty($parameters)) {
            return;
        }

        $res = [];

        foreach($parameters as $parameter) {

            if (is_array($parameter)) {
                $res[] = " [ " . self::printParameters($parameter) . " ] ";
            } else {
                $res[] = sprintf("<tt>&quot;%s&quot;</tt>", htmlspecialchars((string) $parameter));
            }
        }

        return implode(", ", $res);
    }


    static function render($template, $data = [])
    {
        $app = ResourceManager::getApp();

        $app['twig.loader.filesystem']->addPath(dirname(__DIR__));

        return $app['twig']->render($template, $data);
    }


    static function enqueueScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false )
    {
        if (!is_array(self::$scriptQueue)) {
            self::$scriptQueue = [
                'head' => [],
                'footer' => []
            ];
        }

        if ($in_footer) {
            $location = 'footer';
        } else {
            $location = 'head';
        }

        if ($src != false) {
            $script = sprintf("<script type='text/javascript' src='%s%s' type='text/css'></script>\n",
                    $src,
                    !empty($ver) ? '?ver=' . $ver : ''
                );
            self::$scriptQueue[$location][$handle] = $script;
        }

        if (in_array('jquery', $deps)) {
            $app = ResourceManager::getApp();

            $jqueryfile = sprintf(
                '%s%s/%s/wordpress-theme/assets/jquery-2.2.3.min.js',
                $app['paths']['extensions'],
                basename(dirname(dirname(dirname(__DIR__)))),
                basename(dirname(dirname(__DIR__)))
            );
            self::enqueueScript('jquery', $jqueryfile);
        }

    }



    static function enqueueStyleSheet($handle, $src = false, $deps = array(), $ver = false, $media = 'all' )
    {
        if (!is_array(self::$cssQueue)) {
            self::$cssQueue = [];
        }

        if ($src != false) {
            $css = sprintf("<link rel='stylesheet' id='%s' href='%s%s' type='text/css' media='%s' />\n",
                    $handle,
                    $src,
                    !empty($ver) ? '?ver=' . $ver : '',
                    $media
                );
            self::$cssQueue[$handle] = $css;
        }
    }



    static function enqueueInlineStyle($handle, $data = false)
    {
        if (!is_array(self::$cssQueue)) {
            self::$cssQueue = [];
        }

        if ($data != false) {
            $data = "<style type=\"text/css\" media=\"screen\">\n" . $data . "\n</style>\n";
            self::$cssQueue[$handle.'-inline'] = $data;
        }
    }


    static function addStyleData($handle, $key, $value)
    {
        if (empty(self::$cssQueue[$handle])) {
            return;
        }

        if ($key = 'conditional') {
            $conditional_pre  = "<!--[if {$value}]>\n";
            $conditional_post = "<![endif]-->\n";
            self::$cssQueue[$handle] = $conditional_pre . self::$cssQueue[$handle] . $conditional_post;
        }
    }

    static function outputQueue($html)
    {
        if (!empty(self::$cssQueue)) {
            $html = self::insertAfterMeta(implode('', self::$cssQueue), $html);
        }

        if (!empty(self::$scriptQueue) && !empty(self::$scriptQueue['head'])) {
            $html = self::insertAfterMeta(implode('', self::$scriptQueue['head']), $html);
        }

        if (!empty(self::$scriptQueue) && !empty(self::$scriptQueue['footer'])) {
            $html = self::insertEndOfBody(implode('', self::$scriptQueue['footer']), $html);
        }

        $html = self::lowercasePDangit($html);

        return $html;

    }

    static function lowercasePDangit($html)
    {
        return preg_replace('/WordPress/', 'Wordpress', $html);
    }


    /**
     * Helper function to insert some HTML into the head section of an HTML page.
     *
     * @param string $tag
     * @param string $html
     *
     * @return string
     */
    static function insertAfterMeta($tag, $html)
    {
        // first, attempt to insert it after the last meta tag, matching indentation.
        if (preg_match_all("~^([ \t]*)<meta (.*)~mi", $html, $matches)) {

            // matches[0] has some elements, the last index is -1, because zero indexed.
            $last = count($matches[0]) - 1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $tag);
            $html = Str::replaceFirst($matches[0][$last], $replacement, $html);
        } else {
            $html = self::insertEndOfHead($tag, $html);
        }

        return $html;
    }

    /**
     * Helper function to insert some HTML into the head section of an HTML
     * page, right before the </head> tag.
     *
     * @param string $tag
     * @param string $html
     *
     * @return string
     */
    static function insertEndOfHead($tag, $html)
    {
        // first, attempt to insert it before the </head> tag, matching indentation.
        if (preg_match("~([ \t]*)</head~mi", $html, $matches)) {

            // Try to insert it just before </head>
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $tag, $matches[0]);
            $html = Str::replaceFirst($matches[0], $replacement, $html);
        } else {

            // Since we're serving tag soup, just append it.
            $html .= $tag . "\n";
        }

        return $html;
    }

    /**
     * Helper function to insert some HTML into the body section of an HTML
     * page, right before the </body> tag.
     *
     * @param string $tag
     * @param string $html
     *
     * @return string
     */
    static function insertEndOfBody($tag, $html)
    {
        // first, attempt to insert it before the </body> tag, matching indentation.
        if (preg_match("~([ \t]*)</body~mi", $html, $matches)) {

            // Try to insert it just before </head>
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $tag, $matches[0]);
            $html = Str::replaceFirst($matches[0], $replacement, $html);
        } else {

            // Since we're serving tag soup, just append it.
            $html .= $tag . "\n";
        }

        return $html;
    }


}
