<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Bolt\Configuration\ResourceManager;
use Bolt\Helpers\Str;

class WordpressHelper {

    public static $markCssOutputted;

    public static $cssQueue;
    public static $scriptQueue;

    /**
     * Print out a stub for an un-implemented function
     *
     */
    public function stub($functionname, $arguments)
    {

        $arguments = self::printParameters($arguments);

        if (!self::$markCssOutputted) {
            echo "<style>mark { background-color: #fff9c0; text-decoration: none; border: 1px solid #DDB; padding: 1px 3px; display: inline-block; font-size: 13px; } </style>";
            self::$markCssOutputted = true;
        }

        echo " <mark>{$functionname}({$arguments})</mark> ";
    }

    public function printParameters($parameters = array())
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


    public function render($template, $data = [])
    {
        $app = ResourceManager::getApp();

        $app['twig.loader.filesystem']->addPath(dirname(__DIR__));

        return $app['twig']->render($template, $data);
    }


    public function enqueueScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false )
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

            $jqueryfile = $app['paths']['extensions'] . basename(dirname(dirname(__DIR__))) . '/' .
                    basename(dirname(__DIR__)) . '/wp-theme/assets/jquery-2.1.4.min.js';
            self::enqueueScript('jquery', $jqueryfile);
        }

    }



    public function enqueueStyleSheet($handle, $src = false, $deps = array(), $ver = false, $media = 'all' )
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



    public function enqueueInlineStyle($handle, $data = false)
    {
        if (!is_array(self::$cssQueue)) {
            self::$cssQueue = [];
        }

        if ($data != false) {
            $data = "<style type=\"text/css\" media=\"screen\">\n" . $data . "\n</style>\n";
            self::$cssQueue[$handle.'-inline'] = $data;
        }
    }


    public function addStyleData($handle, $key, $value)
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
            // dump(self::$scriptQueue['footer']);
            // echo implode('', self::$scriptQueue['footer']);
            $html = self::insertEndOfBody(implode('', self::$scriptQueue['footer']), $html);
        }

        $html = self::lowercasePDangit($html);

        return $html;

    }

    static function lowercasePDangit($html)
    {
        return preg_replace('/WordPress/i', 'Wordpress', $html);
    }


    /**
     * Helper function to insert some HTML into the head section of an HTML page.
     *
     * @param string $tag
     * @param string $html
     *
     * @return string
     */
    public function insertAfterMeta($tag, $html)
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
    public function insertEndOfHead($tag, $html)
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
    public function insertEndOfBody($tag, $html)
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
