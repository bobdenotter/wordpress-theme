<?php

namespace Bolt\Extension\Bobdenotter\WPTheme;

use Bolt\Configuration\ResourceManager;
use Bolt\Helpers\Str;

class WPcustomize {

    public static $markCssOutputted;

    public static $cssQueue;
    public static $scriptQueue;

    public function get_setting()
    {
        return new \stdClass();
    }

    public function add_setting($id, $args = array())
    {
        $this->settings[$id] = $args;
    }

    public function add_control($id, $args = array())
    {
        if ( $id instanceof \WP_Customize_Control ) {
            $control = $id;
        } else {
            $control = new \WP_Customize_Control( $this, $id, $args );
        }
        $this->controls[$control->id] = $control;
    }

    public function remove_control()
    {
        return true;
    }

    public function get_section()
    {
        return new \stdClass();
    }

    public function dumpSettings()
    {

        dump($this->settings);
        dump($this->controls);

    }

    public function getYaml()
    {
        $output = '';
        $lastsection = '';

        foreach($this->controls as $id => $control) {

            if ($control->section != $lastsection) {
                $output .= sprintf("\n# ---- SECTION %s ------ \n", strtoupper($control->section));
                $lastsection = $control->section;
            }

            $output .= sprintf("\n# %s \n", $control->label);
            if (!empty($control->description)) {
                $output .= sprintf("# %s \n", $control->description);
            }

            if (!empty($control->choices) && is_array($control->choices)) {
                $output .= "# Valid choices are: \n";

                foreach ($control->choices as $key => $value) {
                    $output .= sprintf("#  - %s (%s)\n", $key, $value);
                }
            }

            $default = "";
            if (isset($this->settings[$id])) {
                $default = $this->settings[$id]['default'];
            }

            $output .= sprintf("%s: %s\n", $control->id, $default);

        }

        return $output;

    }


}