<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
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

    protected function registerBackendControllers()
    {
        $controllers = new WordpressThemeBackendControllers();
        $controllers->setExtension($this);

        return [
            '/extend' => $controllers,
        ];
    }


    protected function registerFrontendControllers()
    {
        $controllers = new WordpressThemeFrontendControllers();
        $controllers->setExtension($this);

        return [
            '/' => $controllers,
        ];
    }

    protected function registerTwigPaths()
    {
        return [
            'templates',
        ];
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


}







