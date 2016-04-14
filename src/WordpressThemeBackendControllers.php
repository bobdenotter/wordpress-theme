<?php

namespace Bolt\Extension\Bobdenotter\WordpressTheme;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class WordpressThemeBackendControllers implements ControllerProviderInterface
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

        $ctr->match('/wptheme-settings', [$this, 'wpThemeDashboard'])->bind('wpThemeDashboard');
        $ctr->match('/wptheme-gather', [$this, 'wpThemeGatherSettings'])->bind('wpThemeGatherSettings');

        return $ctr;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
    }

    public function wpThemeDashboard(Request $request)
    {
        $data = [];

        return $this->app['twig']->render('dashboard.twig', $data);
    }

    public function wpThemeGatherSettings(Request $request)
    {
        global $wp_filter;

        $data = [];
        
        $this->extension->loadWPCruft();

        $customize = new WordpressCustomize($this->app);

        do_action('customize_register', $customize);

        // $customize->dumpSettings();

        $data['output'] = $customize->getYaml();

        $data['text'] = "The following configuration file was generated automatically from the <tt>" .
            $this->app['config']->get('general/theme') .
            "</tt> theme, and will be saved as <tt>config.yml</tt> in the theme folder.";
        // dump($data);

        $result = $customize->writeThemeYaml($data['output']);

        if (!$result) {
            echo "File not saved!";
        }

        return $this->app['twig']->render('dashboard.twig', $data);
    }
}