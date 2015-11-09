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


    public function get_control()
    {
        return new \stdClass();
    }


    public function add_section($id, $args = array())
    {
        $this->sections[$id] = $args;
    }

    public function dumpSettings()
    {

        dump($this->controls);
        dump($this->settings);
        dump($this->sections);

    }

    public function getYaml()
    {
        $output = '';
        $lastsection = '';

        foreach($this->controls as $id => $control) {

            if ($control->section != $lastsection) {

                if (isset($this->sections[$control->section]) && !empty($this->sections[$control->section]['title'])) {
                    $section = $this->sections[$control->section]['title'];
                } else {
                    $section = $control->section;
                }

                $output .= sprintf("\n# ---- SECTION %s ---- \n", strtoupper($section));

                if (isset($this->sections[$control->section]) && !empty($this->sections[$control->section]['description'])) {
                    $output .= sprintf("# ---- %s ---- \n", $this->sections[$control->section]['description']);
                }

                $lastsection = $control->section;
            }

            $output .= "\n";
            if (!empty($control->label)) {
                $output .= sprintf("# %s \n", $control->label);
            }
            if (!empty($control->description)) {
                $output .= sprintf("# %s \n", strip_tags($control->description));
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