<?php

/**
 * Shortcodes
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       3.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Shortcodes_Automat', false)) : class CFGP_Shortcodes_Automat extends CFGP_Global
{
    protected $settings = [];

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, array_keys($this->settings), true)) {
            return $this->settings[$name];
        }
    }

    public function generate()
    {
        foreach ($this->settings as $shortcode => $option) {
            $this->add_shortcode($shortcode, $shortcode);
        }
    }
}
endif;
