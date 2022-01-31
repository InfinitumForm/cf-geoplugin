<?php
/**
 * Initialize settings
 *
 * @version       8.0.0
 *
 */
 
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Init')) :
final class CFGP_Init{
	
	private function __construct(){
		// Do translations
		add_action('plugins_loaded', array(&$this, 'textdomain'));
		
		// Call main classes
		$classes = apply_filters('cfgp/init/classes', array(
			'CFGP_Cache',					// Register cache
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
			'CFGP_Notifications'	        // Notifications class
		));
		
		// REST class
		if(CFGP_Options::get('enable_rest',0)){
			$classes = array_merge($classes, array('CFGP_REST'));
		}
		
		// SEO Redirection class
		if(CFGP_Options::get('enable_seo_redirection',0)){
			$classes = array_merge($classes, array('CFGP_SEO', 'CFGP_SEO_Redirection'));
		}
		
		$classes = apply_filters('cfgp/init/included/classes', $classes);
		
		foreach($classes as $class){
			if( method_exists($class, 'instance') ){
				$class::instance();
			}
		}
		
		// Delete expired transients
		self::delete_expired_transients();
		
		// Synchronize with old version of the plugin
		CFGP_Options::sync_with_the_old_version_of_the_plugin();
		
		// Disable plugin updates
		/*
		 * NOTE: Right now is not used
		 *
		 * add_filter( 'site_transient_update_plugins', array($this, 'disable_plugin_updates') );
		 */
		
		// Dynamic action
		do_action('cfgp/init', $this);
	}
	
	/**
	 * Run dry plugin dependencies
	 * @since     8.0.0
	 */
	public static function dependencies(){
		// Enqueue Scripts
		add_action( 'wp_enqueue_scripts', array('CFGP_Init', 'wp_enqueue_scripts') );
		
		// Include file classes
		$includes = apply_filters('cfgp/init/include_classes', array(
			CFGP_CLASS . '/Statistic.php',				// Plugin statistic
			CFGP_CLASS . '/Cache.php',					// Memory control class
			CFGP_CLASS . '/OS.php',						// Operating System info and tool class
			CFGP_CLASS . '/Defaults.php',				// Default values, data
			CFGP_CLASS . '/Utilities.php',				// Utilities
			CFGP_CLASS . '/Library.php',				// Library, data
			CFGP_CLASS . '/Form.php',					// Form class
			CFGP_CLASS . '/Options.php',				// Plugin option class
			CFGP_CLASS . '/Global.php',					// Global class
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
			CFGP_CLASS . '/Notifications.php'			// Notifications class
		));
		
		// Allow deprecated class
		if( defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS ) {
			array_push($includes, CFGP_CLASS . '/CF_Geoplugin.php');
		}
		
		// Include all
		foreach($includes as $include){
			if( file_exists($include) ) {
				include_once $include;
			}
		}
		
		// Adding Important REST Endpoints
		if( CFGP_U::is_rest_enabled() ) {
			add_action('cfgp/init/run', array('CFGP_REST', 'rest_api_init_v1_return'));
		}
		
		// Dynamic action
		do_action('cfgp/init/dependencies');
	}
	
	/**
	 * Run plugin actions and filters
	 * @since     8.0.0
	 */
	public static function run() {
		// Include plugin
		$instance = self::instance();
		// Dynamic run
		do_action('cfgp/init/run');
	}
	
	/**
	 * Register database tables
	 * @since     8.0.0
	 */
	public static function wpdb_tables(){
		global $wpdb;
		// Seo redirection table
		$wpdb->cfgp_seo_redirection   = $wpdb->get_blog_prefix() . 'cfgp_seo_redirection';
		// REST token table
		$wpdb->cfgp_rest_access_token = $wpdb->get_blog_prefix() . 'cfgp_rest_access_token';
	}
	
	/**
	 * Load translations
	 * @since     8.0.0
	 */
	public function textdomain() {
		if ( is_textdomain_loaded( CFGP_NAME ) ) {
			unload_textdomain( CFGP_NAME );
		}
		
		// Get locale
		$locale = apply_filters( 'cfgp_plugin_locale', get_locale(), CFGP_NAME );
		
		// We need standard file
		$mofile = sprintf( '%s-%s.mo', CFGP_NAME, $locale );
		
		// Check first inside `/wp-content/languages/plugins`
		$domain_path = path_join( WP_LANG_DIR, 'plugins' );
		$loaded = load_textdomain( CFGP_NAME, path_join( $domain_path, $mofile ) );
		
		// Or inside `/wp-content/languages`
		if ( ! $loaded ) {
			$loaded = load_textdomain( CFGP_NAME, path_join( WP_LANG_DIR, $mofile ) );
		}
		
		// Or inside `/wp-content/plugin/cf-geoplugin/languages`
		if ( ! $loaded ) {
			$domain_path = CFGP_ROOT . '/languages';
			$loaded = load_textdomain( CFGP_NAME, path_join( $domain_path, $mofile ) );
			
			// Or load with only locale without prefix
			if ( ! $loaded ) {
				$loaded = load_textdomain( CFGP_NAME, path_join( $domain_path, "{$locale}.mo" ) );
			}

			// Or old fashion way
			if ( ! $loaded && function_exists('load_plugin_textdomain') ) {
				load_plugin_textdomain( CFGP_NAME, false, $domain_path );
			}
		}
		
		
	}
	
	/**
	 * Run debugging script
	 * @since     8.0.0
	 */
	public static function debug() {
		// Disable all debugs
		if ( defined( 'CFGP_DEBUG_DISABLE' ) && CFGP_DEBUG_DISABLE === true ) return;
		
		if ( defined( 'CFGP_DEBUG_CACHE' ) && CFGP_DEBUG_CACHE === true ) {
			add_action('wp_footer', function(){
				if(is_user_logged_in() && current_user_can('administrator')) {
					CFGP_Cache::debug();
				}
			});
			add_action('admin_footer', function(){
				if(is_user_logged_in() && current_user_can('administrator')) {
					CFGP_Cache::debug();
				}
			});
		}
	}
	
	
	/**
	 * Enqueue Scripts
	 * @since     8.0.0
	 */
	public static function wp_enqueue_scripts() {
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
	 * @since     8.0.0
	 */
	public static function activation() {
		return CFGP_Global::register_activation_hook(function(){
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			
			// Get global variables
			global $wpdb;
			
			// clear old cache
			CFGP_U::flush_plugin_cache();
			
			// Include important library
			if(!function_exists('dbDelta')){
				require_once ABSPATH . '/wp-admin/includes/upgrade.php';
			}
			
			// Add activation date
			if($activation = get_option(CFGP_NAME . '-activation')) {
				$activation[] = date('Y-m-d H:i:s');
				update_option(CFGP_NAME . '-activation', $activation, false);
			} else {
				add_option(CFGP_NAME . '-activation', array(date('Y-m-d H:i:s')), false);
			}

			// Generate unique ID
			if(!get_option(CFGP_NAME . '-ID')) {
				add_option(CFGP_NAME . '-ID', 'cfgp_'.CFGP_U::generate_token(55).'_'.CFGP_U::generate_token(4), false);
			}
			
			// Database control
			$current_db_version = get_option(CFGP_NAME . '-db-version');
			if( empty($current_db_version) || version_compare($current_db_version, CFGP_DATABASE_VERSION, '!=') )
			{
				// Get database collate
				$charset_collate = $wpdb->get_charset_collate();
				## Create database table for the REST tokens
				if($wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->cfgp_rest_access_token}'" ) != $wpdb->cfgp_rest_access_token) 
				{
					dbDelta("
					CREATE TABLE {$wpdb->cfgp_rest_access_token} (
						ID bigint(20) NOT NULL AUTO_INCREMENT,
						`secret_key` varchar(45) NOT NULL,
						`token` varchar(65) NOT NULL,
						`app_name` varchar(255) NOT NULL,
						`app_name_original` varchar(255) NOT NULL,
						`date_created` timestamp NOT NULL DEFAULT current_timestamp(),
						`active` int(1) NOT NULL DEFAULT 1,
						`lookup` bigint(32) NOT NULL DEFAULT 1,
						PRIMARY KEY (ID),
						UNIQUE KEY `token` (`token`),
						UNIQUE KEY `app_name` (`app_name`),
						KEY `secret_key` (`secret_key`)
					) {$charset_collate}
					");
				}
				
				## Create database table for the SEO redirection
				if($wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->cfgp_seo_redirection}'" ) != $wpdb->cfgp_seo_redirection) 
				{
					dbDelta("
					CREATE TABLE {$wpdb->cfgp_seo_redirection} (
						ID int(11) NOT NULL AUTO_INCREMENT,
						`only_once` tinyint(1) NOT NULL DEFAULT 0,
						`country` varchar(100) DEFAULT NULL,
						`region` varchar(100) DEFAULT NULL,
						`city` varchar(100) DEFAULT NULL,
						`postcode` varchar(100) DEFAULT NULL,
						`url` tinytext NOT NULL,
						`http_code` smallint(3) NOT NULL DEFAULT 302,
						`active` tinyint(1) NOT NULL DEFAULT 1,
						`date` timestamp NOT NULL DEFAULT current_timestamp(),
						PRIMARY KEY (ID),
						KEY `country` (`country`),
						KEY `region` (`region`),
						KEY `city` (`city`),
						KEY `postcode` (`postcode`)
					) {$charset_collate}
					");
				}
				
				// Update database version
				update_option(CFGP_NAME . '-db-version', CFGP_DATABASE_VERSION, false);
			}
			
			// Plugin statistic
			CFGP_Anonymous_Statistic::activation( CFGP_Options::get() );
		});
	}
	
	/**
	 * Run script on the plugin deactivation
	 * @since     8.0.0
	 */
	public static function deactivation() {
		return CFGP_Global::register_deactivation_hook(function(){
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			
			// Add deactivation date
			if($deactivation = get_option(CFGP_NAME . '-deactivation')) {
				$deactivation[] = date('Y-m-d H:i:s');
				update_option(CFGP_NAME . '-deactivation', $deactivation, false);
			} else {
				add_option(CFGP_NAME . '-deactivation', array(date('Y-m-d H:i:s')), false);
			}
			
			// Plugin statistic
			CFGP_Anonymous_Statistic::deactivation();
		});
	}
	
	/**
	 * Delete Expired CF Geo Plugin Transients
	 * @since     8.0.0
	 */
	private static function delete_expired_transients( $force_db = false ) {
		global $wpdb;
	 
		if ( ! $force_db && wp_using_ext_object_cache() ) {
			return;
		}
	 
		$wpdb->query(
			$wpdb->prepare(
				"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_cfgp-', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %d",
				$wpdb->esc_like( '_transient_cfgp-' ) . '%',
				$wpdb->esc_like( '_transient_timeout_cfgp-' ) . '%',
				CFGP_TIME
			)
		);
	 
		if ( ! is_multisite() ) {
			// Single site stores site transients in the options table.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
					WHERE a.option_name LIKE %s
					AND a.option_name NOT LIKE %s
					AND b.option_name = CONCAT( '_site_transient_timeout_cfgp-', SUBSTRING( a.option_name, 17 ) )
					AND b.option_value < %d",
					$wpdb->esc_like( '_site_transient_cfgp-' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_cfgp-' ) . '%',
					CFGP_TIME
				)
			);
		} elseif ( is_multisite() && is_main_site() && is_main_network() ) {
			// Multisite stores site transients in the sitemeta table.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM {$wpdb->sitemeta} a, {$wpdb->sitemeta} b
					WHERE a.meta_key LIKE %s
					AND a.meta_key NOT LIKE %s
					AND b.meta_key = CONCAT( '_site_transient_timeout_cfgp-', SUBSTRING( a.meta_key, 17 ) )
					AND b.meta_value < %d",
					$wpdb->esc_like( '_site_transient_cfgp-' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_cfgp-' ) . '%',
					CFGP_TIME
				)
			);
		}
	}
	
	/**
	 * Disable Geo Plugin updates
	 * @since     8.0.0
	 */
	function disable_plugin_updates( $value ) {
		if ( isset($value) && is_object($value) ) {
			$plugin = dirname(CFGP_FILE).'/'.basename(CFGP_FILE);
			if ( isset( $value->response[$plugin] ) ) {
				unset( $value->response[$plugin] );
			}
		}
		return $value;
	}
	
	/* 
	 * Instance
	 * @verson    8.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;