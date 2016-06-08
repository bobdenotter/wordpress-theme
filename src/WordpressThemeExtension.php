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
}







