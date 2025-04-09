<?php

/**
 * Settings page
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

if (!class_exists('CFGP_Settings', false)) :
    class CFGP_Settings extends CFGP_Global
    {
        private $seo_redirection_page;

        public function __construct()
        {
            if (!class_exists('CFGP_Sidebar', false)) {
                CFGP_U::include_once(CFGP_INC . '/settings/sidebar.php');
            }

            if (class_exists('CFGP_Sidebar', false)) {
                CFGP_Sidebar::instance();
            }

            $this->add_action((CFGP_NETWORK_ADMIN ? 'network_admin_menu' : 'admin_menu'), 'add_pages', 10);
            $this->add_action('admin_init', 'admin_init');
            add_filter('set-screen-option', [&$this, 'set_screen_option'], 30, 3);
        }

        // Initialize plugin settings
        public function admin_init()
        {

            if (isset($_GET['rstr_response']) && $_GET['rstr_response'] == 'saved') {
                $this->add_action('admin_notices', 'notices__saved');
            }

            if (isset($_GET['save_settings'])) {
                if ($_GET['save_settings'] == 'true') {
                    $this->save_settings();
                } elseif ($_GET['save_settings'] == 'false') {
                    $this->add_action('admin_notices', 'notices__error');
                }
            }
        }

        // Save settings
        public function save_settings()
        {
            $parse_url = CFGP_U::parse_url();
            $url       = $parse_url['url'];

            if ($nonce = CFGP_U::request_string('nonce', false)) {
                if (wp_verify_nonce($nonce, CFGP_NAME.'-save-settings') !== false) {
                    if (isset($_POST['cf-geoplugin'])) {
                        if (CFGP_Options::set($_POST['cf-geoplugin'])) {
                            $url = remove_query_arg('save_settings');
                            $url = remove_query_arg('nonce');
                            $url = add_query_arg('rstr_response', 'saved', $url);
                            wp_safe_redirect($url);
                        } else {
                            $url = add_query_arg('save_settings', 'false', $url);
                            $url = add_query_arg('rstr_response', 'error_options', $url);
                            wp_safe_redirect($url);
                        }
                    } else {
                        $url = add_query_arg('save_settings', 'false', $url);
                        $url = add_query_arg('rstr_response', 'error_form', $url);
                        wp_safe_redirect($url);
                    }
                } else {
                    $url = add_query_arg('save_settings', 'false', $url);
                    $url = add_query_arg('rstr_response', 'error_nonce', $url);
                    wp_safe_redirect($url);
                }
            }
        }

        public function notices__saved()
        {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html__('Settings saved.', 'cf-geoplugin')
            );
        }

        public function notices__error()
        {
            if (isset($_GET['rstr_response'])) :
                if ($_GET['rstr_response'] == 'error_nonce') {
                    printf(
                        '<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
                        esc_html__('NONCE ERROR!', 'cf-geoplugin'),
                        esc_html__('Nonce is incorrect or has expired. Please refresh the page and try again. Unable to save settings.', 'cf-geoplugin')
                    );
                } elseif ($_GET['rstr_response'] == 'error_form') {
                    printf(
                        '<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
                        esc_html__('FORM ERROR!', 'cf-geoplugin'),
                        esc_html__('The form was not submitted regularly. Unable to save settings.', 'cf-geoplugin')
                    );
                } elseif ($_GET['rstr_response'] == 'error_options') {
                    printf(
                        '<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
                        esc_html__('FORM ERROR!', 'cf-geoplugin'),
                        esc_html__('The form was not submitted regularly. Unable to save settings.', 'cf-geoplugin')
                    );
                } else {
                    return;
                } endif;
        }

        /* Add admin pages */
        public function add_pages()
        {
            // Only admins
            if (!(current_user_can('administrator') || current_user_can('editor'))) {
                return;
            }

            $this->add_menu_page(
                __('Geo Controller', 'cf-geoplugin'),
                __('Geo Controller', 'cf-geoplugin'),
                'manage_options',
                CFGP_NAME,
                'main_page__callback',
                'dashicons-location-alt',
                59
            );

            if (CFGP_Options::get('enable_gmap', false)) {
                $this->add_submenu_page(
                    CFGP_NAME,
                    __('Google Map', 'cf-geoplugin'),
                    __('Google Map', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_NAME . '-google-map',
                    'google_map__callback'
                );
            }

            if (CFGP_Options::get('enable_defender', 1)) {
                $this->add_submenu_page(
                    CFGP_NAME,
                    __('Site Protection', 'cf-geoplugin'),
                    __('Site Protection', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_NAME . '-defender',
                    'defender__callback'
                );
            }

            if (CFGP_Options::get('enable_banner', false)) {
                $this->add_submenu_page(
                    CFGP_NAME,
                    __('Geo Banner', 'cf-geoplugin'),
                    __('Geo Banner', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_U::admin_url('edit.php?post_type=' . CFGP_NAME . '-banner')
                );
            }

            $this->add_submenu_page(
                CFGP_NAME,
                __('Postcode', 'cf-geoplugin'),
                __('Postcodes', 'cf-geoplugin'),
                'manage_options',
                CFGP_U::admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-postcode&post_type=' . CFGP_NAME . '-banner')
            );
            $this->add_submenu_page(
                CFGP_NAME,
                __('Debug Mode', 'cf-geoplugin'),
                __('Debug Mode', 'cf-geoplugin'),
                'manage_options',
                CFGP_NAME . '-debug',
                'debug__callback'
            );
            $this->add_submenu_page(
                CFGP_NAME,
                __('Settings', 'cf-geoplugin'),
                __('Settings', 'cf-geoplugin'),
                'manage_options',
                CFGP_NAME . '-settings',
                'settings__callback'
            );

            if (CFGP_License::activated()) {
                $this->add_submenu_page(
                    CFGP_NAME,
                    __('License', 'cf-geoplugin'),
                    __('License', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_NAME . '-activate',
                    'license__callback'
                );
            } else {
                $this->add_submenu_page(
                    CFGP_NAME,
                    __('Activate Unlimited', 'cf-geoplugin'),
                    '<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_NAME . '-activate',
                    'license__callback'
                );
            }

            if (CFGP_Options::get('enable_seo_redirection', 1)) {
                global $submenu;

                $this->seo_redirection_page = $this->add_menu_page(
                    __('SEO Redirection', 'cf-geoplugin'),
                    __('SEO Redirection', 'cf-geoplugin'),
                    'manage_options',
                    CFGP_NAME . '-seo-redirection',
                    'seo_redirection__callback',
                    'dashicons-location',
                    59
                );
                $this->add_action("load-{$this->seo_redirection_page}", 'add_seo_redirection_page_screen_option');

                $submenu[CFGP_NAME . '-seo-redirection'] = [
                    [
                        __('SEO Redirection', 'cf-geoplugin'),
                        'manage_options',
                        CFGP_U::admin_url('/admin.php?page=cf-geoplugin-seo-redirection'),
                    ],
                    [
                        __('Add New', 'cf-geoplugin'),
                        'manage_options',
                        CFGP_U::admin_url('/admin.php?page=cf-geoplugin-seo-redirection&action=new&nonce='.wp_create_nonce(CFGP_NAME.'-seo-new')),
                    ],
                    [
                        __('Import/Export', 'cf-geoplugin'),
                        'manage_options',
                        CFGP_U::admin_url('/admin.php?page=cf-geoplugin-seo-redirection&action=import&nonce='.wp_create_nonce(CFGP_NAME.'-seo-import-csv')),
                    ],
                ];
            }
        }

        public function add_seo_redirection_page_screen_option()
        {
            $screen = get_current_screen();

            if (!is_object($screen) || $screen->id != $this->seo_redirection_page) {
                return;
            }

            add_screen_option('per_page', [
                'label'   => __('Devices per page', 'cf-geoplugin'),
                'default' => 20,
                'min'     => 5,
                'max'     => 1000,
                'option'  => 'cfgp_seo_redirection_per_page',
            ]);
        }

        /*
         * Set global screen option
         */
        public function set_screen_option($status, $option, $value)
        {
            if ('cfgp_seo_redirection_per_page' == $option) {
                return $value;
            }
        }

        public function main_page__callback()
        {
            CFGP_U::include_once([
                CFGP_INC . '/filters/main_page.php',
                CFGP_INC . '/settings/main_page.php',
            ]);
        }

        public function google_map__callback()
        {
            CFGP_U::include_once(CFGP_INC . '/settings/google_map.php');
        }

        public function defender__callback()
        {
            CFGP_U::include_once(CFGP_INC . '/settings/defender.php');
        }

        public function seo_redirection__callback()
        {
            wp_enqueue_media();
            CFGP_U::include_once([
                CFGP_INC . '/filters/seo_redirection_form.php',
                CFGP_INC . '/filters/seo_redirection_import.php',
                CFGP_INC . '/filters/seo_redirection_table.php',
                CFGP_INC . '/settings/seo_redirection.php',
            ]);
        }

        public function debug__callback()
        {
            CFGP_U::include_once(CFGP_INC . '/settings/debug.php');
        }

        public function settings__callback()
        {
            CFGP_U::include_once([
                CFGP_INC . '/filters/settings.php',
                CFGP_INC . '/filters/settings-rest.php',
                CFGP_INC . '/settings/settings.php',
            ]);
        }

        public function license__callback()
        {
            CFGP_U::include_once([
                CFGP_INC . '/filters/license.php',
                CFGP_INC . '/settings/license.php',
            ]);
        }

        /*
         * Instance
         * @verson    1.0.0
         */
        public static function instance()
        {

            if (!is_admin()) {
                return;
            }
            $class    = self::class;
            $instance = CFGP_Cache::get($class);

            if (!$instance) {
                $instance = CFGP_Cache::set($class, new self());
            }

            return $instance;
        }
    }
endif;
