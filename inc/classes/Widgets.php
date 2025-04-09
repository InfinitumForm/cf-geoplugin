<?php

/**
 * Widgets settings
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       3.0.1
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Widgets', false)) : class CFGP_Widgets extends CFGP_Global
{
    private function __construct()
    {
        $this->add_action('after_setup_theme', 'register', 10);
    }

    /*
     * Register widgets
     * @verson    1.0.2
     */
    public function register()
    {
        if (!apply_filters('cfgp/current_theme_supports/widgets', true)) {
            return;
        }

        // Call main classes
        $classes = apply_filters('cfgp/widget/classes', [
            'CFGP_Widget_Currency_Converter',
        ]);

        // For each class include file and collect widgets
        $load_widgets = [];

        // For each class include file and register widget
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $i => $class) {

                // Include
                if (!class_exists($class, false)) {
                    CFGP_U::include_once(CFGP_INC . '/widgets/' . str_replace('CFGP_Widget_', '', $class) . '.php');
                }

                // Register widget
                if (class_exists($class, false)) {
                    $load_widgets[] = $class;
                }

            }

            // Register widget
            if (!empty($load_widgets)) {
                add_action('widgets_init', function () use ($load_widgets) {
                    foreach ($load_widgets as $widget) {
                        if (class_exists($widget, false)) {
                            register_widget($widget);
                        }
                    }
                });
            }

            unset($load_widgets);
        }
    }

    /*
     * Instance
     * @verson    8.0.0
     */
    public static function instance()
    {
        $class    = self::class;
        $instance = CFGP_Cache::get($class);

        if (!$instance) {
            $instance = CFGP_Cache::set($class, new self());
        }

        return $instance;
    }
}
endif;
