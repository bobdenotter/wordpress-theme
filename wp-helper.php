<?php

use Bolt\Configuration\ResourceManager;

class WPhelper {

    public static $markCssOutputted;

    public static $cssQueue;

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

        $app['twig.loader.filesystem']->addPath(__DIR__);

        return $app['twig']->render($template, $data);
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

    public function outputQueue()
    {
        if (!empty(self::$cssQueue)) {
            dump(self::$cssQueue);
            echo implode('', self::$cssQueue);
        }
    }

}