<?php

/**
 * Initialize settings
 *
 * @version       8.0.0
 */

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Init', false)) : final class CFGP_Init
{
    private function __construct()
    {
        // Do translations
        add_action('plugins_loaded', [&$this, 'textdomain']);

        // Include Traits
        include_once CFGP_INC . '/traits/cache.php';

        // Call main classes
        $classes = apply_filters('cfgp/init/classes', [
            'CFGP_Cache',					// Register cache
            'CFGP_DB_Cache',				// Register database cache
            'CFGP_API',						// Main API class
            'CFGP_Taxonomy',				// Register Taxonomy
            'CFGP_Metabox',					// Metabox class
            'CFGP_Geo_Banner',				// Register Post Type
            'CFGP_Media',					// Media class
            'CFGP_Settings',				// Settings class
            'CFGP_Admin',					// Admin class
            'CFGP_Help',					// Contextual help class
            'CFGP_Shortcodes',				// Settings class
            'CFGP_Defender',				// Defender class
            'CFGP_Public',					// Public class
            'CFGP_Plugins',					// Plugins class
            'CFGP_SEO_Redirection_Pages',	// SEO redirection for the individual pages
            'CFGP_Widgets',	                // Widgets class
            'CFGP_Notifications',	        // Notifications class
        ]);

        // REST class
        if (CFGP_Options::get('enable_rest', 0)) {
            $classes = array_merge($classes, ['CFGP_REST']);
        }

        // SEO Redirection class
        if (CFGP_Options::get('enable_seo_redirection', 0)) {
            $classes = array_merge($classes, ['CFGP_SEO', 'CFGP_SEO_Redirection']);
        }

        // Menus class
        if (CFGP_Options::get('enable_menus_control', 1)) {
            $classes = array_merge($classes, ['CFGP_Menus']);
        }

        // Remove some classes in the special cases
        $remove_classes = apply_filters('cfgp/init/included/classes/remove', [
            'CFGP_Menus',
            'CFGP_SEO_Redirection',
            'CFGP_SEO_Redirection_Pages',
            'CFGP_Defender',
            'CFGP_Help',
            'CFGP_Notifications',
        ]);

        if (
            !is_admin()
            && (
                (isset($_REQUEST['action']) && $_REQUEST['action'] == 'elementor')
                || (isset($_REQUEST['elementor-preview']) && $_REQUEST['elementor-preview'] > 0)
                || (
                    (isset($_REQUEST['preview']) && $_REQUEST['preview'] == 'true')
                    && (isset($_REQUEST['preview_id']) && $_REQUEST['preview_id'] > 0)
                )
            )
        ) {
            $classes = array_filter($classes, function ($c) use ($remove_classes) {
                return !in_array($c, $remove_classes, true);
            });
        }

        $classes = apply_filters('cfgp/init/included/classes', $classes);

        foreach ($classes as $class) {
            if (method_exists($class, 'instance')) {
                $class::instance();
            }
        }

        // Delete expired transients
        self::delete_expired_transients();

        // Dynamic action
        do_action('cfgp/init', $this);
    }

    /**
     * Run dry plugin dependencies
     *
     * @since     8.0.0
     */
    public static function dependencies()
    {
        // Enqueue Scripts
        add_action('wp_enqueue_scripts', ['CFGP_Init', 'wp_enqueue_scripts']);

        // Include file classes
        $includes = apply_filters('cfgp/init/include_classes', [
            CFGP_CLASS . '/Cache.php',					// Memory control class
            CFGP_CLASS . '/Cache_DB.php',				// Cache control class
            CFGP_CLASS . '/OS_Helper.php',				// Client Hints helper
			CFGP_CLASS . '/OS.php',						// Operating System info and tool class
            CFGP_CLASS . '/Browser.php',				// Browser class
            CFGP_CLASS . '/Defaults.php',				// Default values, data
            CFGP_CLASS . '/Bots.php',					// Bots
			CFGP_CLASS . '/Utilities.php',				// Utilities
            CFGP_CLASS . '/Library.php',				// Library, data
            CFGP_CLASS . '/Form.php',					// Form class
            CFGP_CLASS . '/Options.php',				// Plugin option class
            CFGP_CLASS . '/Global.php',					// Global class
            CFGP_CLASS . '/Debug.php',					// Plugin debug
            CFGP_CLASS . '/Admin.php',					// Admin option class
            CFGP_CLASS . '/Help.php',					// Contextual help class
            CFGP_CLASS . '/IP.php',						// IP class
            CFGP_CLASS . '/License.php',				// License class
            CFGP_CLASS . '/Media.php',					// Media class
            CFGP_CLASS . '/Taxonomy.php',				// Taxonomy class
            CFGP_CLASS . '/Geo_Banner.php',				// Post Type class
            CFGP_CLASS . '/API.php',					// API class
            CFGP_CLASS . '/Metabox.php',				// Metabox class
            CFGP_CLASS . '/SEO.php',					// SEO class
            CFGP_CLASS . '/SEO_Redirection.php',		// SEO Redirection class
            CFGP_CLASS . '/SEO_Redirection_Pages.php',	// SEO Redirection for pages class
            CFGP_CLASS . '/SEO_Redirection_Table.php',	// SEO Table class
            CFGP_CLASS . '/Settings.php',				// Settings class
            CFGP_CLASS . '/Shortcodes_Automat.php',		// Shortcodes Automat class
            CFGP_CLASS . '/Shortcodes.php',				// Shortcodes class
            CFGP_CLASS . '/Defender.php',				// Defender class
            CFGP_CLASS . '/Public.php',					// Public class
            CFGP_CLASS . '/Plugins.php',				// Plugins class
            CFGP_CLASS . '/REST.php',					// REST class
            CFGP_CLASS . '/Widgets.php',				// Widgets class
            CFGP_CLASS . '/Menus.php',					// Menus class
            CFGP_CLASS . '/Notifications.php',			// Notifications class
        ]);

        // Allow deprecated class
        if (defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS) {
			array_push($includes, CFGP_CLASS . '/CF_Geoplugin.php');
        }

        // Fix path on the Windows
        if ('\\' === DIRECTORY_SEPARATOR) {
            $includes = array_map(function ($path) {
                return str_replace('/', DIRECTORY_SEPARATOR, $path);
            }, $includes);
        }

        // Include all
        foreach ($includes as $include) {
            if (file_exists($include)) {
                include_once $include;
            }
        }

        // Adding Important REST Endpoints
        if (CFGP_U::is_rest_enabled()) {
            add_action('cfgp/init/run', ['CFGP_REST', 'rest_api_init_v1_return']);
        }

        // Dynamic action
        do_action('cfgp/init/dependencies');
    }

    /**
     * Run plugin actions and filters
     *
     * @since     8.0.0
     */
    public static function run()
    {
        // Include plugin
        $instance = self::instance();
        // After current theme is setup
        add_action('after_setup_theme', [$instance, 'check_theme_supports']);
        // Dynamic run
        do_action('cfgp/init/run');
    }

    /**
     * Check theme supports and do filters
     *
     * @since     8.5.3
     */
    public function check_theme_supports()
    {
        // Check if theme supports menus
        add_filter('cfgp/current_theme_supports/menus', function ($enabled) {
            if ($enabled) {
                $enabled = current_theme_supports('menus');
            }

            return $enabled;
        });
        // Check if theme supports widgets
        add_filter('cfgp/current_theme_supports/widgets', function ($enabled) {
            if ($enabled) {
                $enabled = current_theme_supports('widgets');
            }

            return $enabled;
        });
    }

    /**
     * Register database tables
     *
     * @since     8.0.0
     */
    public static function wpdb_tables()
    {
        global $wpdb;
        // Seo redirection table
        $wpdb->cfgp_seo_redirection = $wpdb->get_blog_prefix() . 'cfgp_seo_redirection';
        // REST token table
        $wpdb->cfgp_rest_access_token = $wpdb->get_blog_prefix() . 'cfgp_rest_access_token';
        // REST token table
        $wpdb->cfgp_cache = $wpdb->get_blog_prefix() . 'cfgp_cache';
    }

    /**
     * Load translations
     *
     * @since     8.0.0
     */
    public function textdomain()
    {
        // Get locale
        $locale = apply_filters('cfgp_plugin_locale', get_locale(), 'cf-geoplugin');

        // We need standard file in the format 'cfgeoplugin-en_US.mo'
        $mofile = sprintf('cf-geoplugin-%s.mo', $locale);

        // Check first inside `/wp-content/languages/plugins`
        $domain_path = path_join(WP_LANG_DIR, 'plugins');
        $loaded      = load_textdomain('cf-geoplugin', path_join($domain_path, $mofile));

        // Or inside `/wp-content/languages`
        if (!$loaded) {
            $loaded = load_textdomain('cf-geoplugin', path_join(WP_LANG_DIR, $mofile));
        }

        // Or inside `/wp-content/plugins/cf-geoplugin/languages`
        if (!$loaded) {
            $domain_path = CFGP_ROOT . DIRECTORY_SEPARATOR . 'languages';
            $loaded      = load_textdomain('cf-geoplugin', path_join($domain_path, $mofile));

            // Or load with only locale without prefix
            if (!$loaded) {
                $loaded = load_textdomain('cf-geoplugin', path_join($domain_path, "{$locale}.mo"));
            }

            // Or old fashioned way
            if (!$loaded && function_exists('load_plugin_textdomain')) {
                load_plugin_textdomain('cf-geoplugin', false, $domain_path);
            }
        }
    }

    /**
     * Run debugging script
     *
     * @since     8.0.0
     */
    public static function debug()
    {
        // Disable all debugs
        if (defined('CFGP_DEBUG_DISABLE') && CFGP_DEBUG_DISABLE === true) {
            return;
        }

        if (defined('CFGP_DEBUG_CACHE') && CFGP_DEBUG_CACHE === true) {
            add_action('wp_footer', function () {
                if (is_user_logged_in() && current_user_can('administrator')) {
                    CFGP_Cache::debug();
                }
            });
            add_action('admin_footer', function () {
                if (is_user_logged_in() && current_user_can('administrator')) {
                    CFGP_Cache::debug();
                }
            });
        }
    }

    /**
     * Enqueue Scripts
     *
     * @since     8.0.0
     */
    public static function wp_enqueue_scripts()
    {
        wp_register_style(
            CFGP_NAME.'-flag',
            CFGP_ASSETS . '/css/flag-icon.min.css',
            1,
            CFGP_VERSION,
            'all'
        );
    }

    /**
     * Run script on the plugin activation
     *
     * @since     8.0.0
     */
    public static function activation()
    {
        return CFGP_Global::register_activation_hook(function () {
            if (!current_user_can('activate_plugins')) {
                return;
            }
            // clear old cache
            CFGP_U::flush_plugin_cache();

            // Let's reload textdomain
            if (is_textdomain_loaded(CFGP_NAME)) {
                unload_textdomain(CFGP_NAME);
            }

            // Add activation date
            if ($activation = get_option(CFGP_NAME . '-activation')) {
                $activation[] = date('Y-m-d H:i:s');
                $activation   = array_slice($activation, -30);
                update_option(CFGP_NAME . '-activation', $activation, false);
            } else {
                add_option(CFGP_NAME . '-activation', [date('Y-m-d H:i:s')], false);
            }

            // Generate unique ID
            if (!get_option(CFGP_NAME . '-ID')) {
                add_option(CFGP_NAME . '-ID', 'cfgp_'.CFGP_U::generate_token(55).'_'.CFGP_U::generate_token(4), false);
            }
            // Install databases
            self::install_update_database();
            // Update plugin version
            update_option(CFGP_NAME . '-version', CFGP_VERSION, false);
        });
    }

    /**
     * Run script on the plugin upgrade
     *
     * @todo: https://wordpress.stackexchange.com/questions/144870/wordpress-update-plugin-hook-action-since-3-9
     *
     * @since     8.0.0
     */
    public static function upgrade()
    {
        add_action('plugins_loaded', function () {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            if (CFGP_VERSION !== get_option(CFGP_NAME . '-version', CFGP_VERSION)) {
                // Get global variables
                global $wpdb;
                // clear old cache
                CFGP_U::flush_plugin_cache();

                // Let's reload textdomain
                if (is_textdomain_loaded(CFGP_NAME)) {
                    unload_textdomain(CFGP_NAME);
                }

                // Generate unique ID
                if (!get_option(CFGP_NAME . '-ID')) {
                    add_option(CFGP_NAME . '-ID', 'cfgp_'.CFGP_U::generate_token(55).'_'.CFGP_U::generate_token(4), false);
                }
                // Install databases
                self::install_update_database();
                // Update plugin version
                update_option(CFGP_NAME . '-version', CFGP_VERSION, false);

                // WP Refresh
                if (wp_safe_redirect(CFGP_U::get_url())) {
                    exit;
                }
            }
        }, 1);
    }

    /**
     * Run script on the plugin deactivation
     *
     * @since     8.0.0
     */
    public static function deactivation()
    {
        return CFGP_Global::register_deactivation_hook(function () {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            // Let's unload textdomain
            if (is_textdomain_loaded(CFGP_NAME)) {
                unload_textdomain(CFGP_NAME);
            }

            // Add deactivation date
            if ($deactivation = get_option(CFGP_NAME . '-deactivation')) {
                $deactivation[] = date('Y-m-d H:i:s');
                $deactivation   = array_slice($deactivation, -30);
                update_option(CFGP_NAME . '-deactivation', $deactivation, false);
            } else {
                add_option(CFGP_NAME . '-deactivation', [date('Y-m-d H:i:s')], false);
            }
        });
    }

    /**
     * Install Database
     *
     * @since     8.2.8
     */
    public static function install_update_database()
    {
        global $wpdb;
        // Database control
        $current_db_version = get_option(CFGP_NAME . '-db-version');

        if (empty($current_db_version) || version_compare($current_db_version, CFGP_DATABASE_VERSION, '!=')) {
            // Include important library
            if (!function_exists('dbDelta')) {
                require_once ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php';
            }
            // Get database collate
            $charset_collate = $wpdb->get_charset_collate();

            ## Create database table for the REST tokens
            CFGP_REST::table_install();
            ## Create database table for the Cache
            CFGP_DB_Cache::table_install();

            ## Create database table for the SEO redirection if plugin is new
            CFGP_SEO_Table::table_install();

            // Update database version
            update_option(CFGP_NAME . '-db-version', CFGP_DATABASE_VERSION, false);
        }
    }

    /**
     * Delete Expired Geo Controller Transients
     *
     * Direct database call is discouraged but WordPress not provide good solution for this case
     *
     * @since     8.0.0
     */
    private static function delete_expired_transients($force_db = false)
    {
        global $wpdb;

        if (!$force_db && wp_using_ext_object_cache()) {
            return;
        }

        $last_execution = get_transient('delete_expired_transients_last_run');

        if (false === $last_execution || (time() - $last_execution) > 300) {
            set_transient('delete_expired_transients_last_run', time(), 300);

            $wpdb->query(
                $wpdb->prepare(
                    "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
					WHERE a.option_name LIKE %s
					AND a.option_name NOT LIKE %s
					AND b.option_name = CONCAT( '_transient_timeout_cfgp-', SUBSTRING( a.option_name, 12 ) )
					AND b.option_value < %d",
                    $wpdb->esc_like('_transient_cfgp-') . '%',
                    $wpdb->esc_like('_transient_timeout_cfgp-') . '%',
                    $wpdb->esc_like(CFGP_TIME)
                )
            );

            if (!is_multisite()) {
                // Single site stores site transients in the options table.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
						WHERE a.option_name LIKE %s
						AND a.option_name NOT LIKE %s
						AND b.option_name = CONCAT( '_site_transient_timeout_cfgp-', SUBSTRING( a.option_name, 17 ) )
						AND b.option_value < %d",
                        $wpdb->esc_like('_site_transient_cfgp-') . '%',
                        $wpdb->esc_like('_site_transient_timeout_cfgp-') . '%',
                        $wpdb->esc_like(CFGP_TIME)
                    )
                );
            } elseif (is_multisite() && is_main_site() && is_main_network()) {
                // Multisite stores site transients in the sitemeta table.
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE a, b FROM {$wpdb->sitemeta} a, {$wpdb->sitemeta} b
						WHERE a.meta_key LIKE %s
						AND a.meta_key NOT LIKE %s
						AND b.meta_key = CONCAT( '_site_transient_timeout_cfgp-', SUBSTRING( a.meta_key, 17 ) )
						AND b.meta_value < %d",
                        $wpdb->esc_like('_site_transient_cfgp-') . '%',
                        $wpdb->esc_like('_site_transient_timeout_cfgp-') . '%',
                        $wpdb->esc_like(CFGP_TIME)
                    )
                );
            }
        } else {
            return;
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
