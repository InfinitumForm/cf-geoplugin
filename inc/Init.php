<?php
/**
 * Certificate setup
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
			'CFGP_Widgets'	                // Widgets class
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
			CFGP_CLASS . '/Shortcodes.php',				// Shortcodes class
			CFGP_CLASS . '/Defender.php',				// Defender class
			CFGP_CLASS . '/Public.php',					// Public class
			CFGP_CLASS . '/Plugins.php',				// Plugins class
			CFGP_CLASS . '/REST.php',					// REST class
			CFGP_CLASS . '/Widgets.php'					// Widgets class
		));
		foreach($includes as $include){
			include_once $include;
		}
		// Dynamic action
		do_action('cfgp/init/dependencies');
	}
	
	/**
	 * Run plugin actions and filters
	 * @since     8.0.0
	 */
	public static function run() {
		$instance = self::instance();
		// Dynamic run
		do_action('cfgp/init/run');
	}
	
	/**
	 * Load translations
	 * @since     8.0.0
	 */
	public function textdomain() {
		$locale = apply_filters( 'cfgp_plugin_locale', get_locale(), CFGP_NAME );
		if ( $loaded = load_textdomain( CFGP_NAME, CFGP_ROOT . '/languages/' . $locale . '.mo' ) ) {
			return $loaded;
		} else {
			load_plugin_textdomain( CFGP_NAME, false, CFGP_ROOT . '/languages' );
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
			
			$database_version = '1.0.0';
			
			// Get global variables
			global $wpdb;
			
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
			
			$charset_collate = $wpdb->get_charset_collate();
			
			## Create database table for the REST tokens
			$rest_tokens_table = $wpdb->prefix . CFGP_Defaults::TABLE['rest_tokens'];
			if($wpdb->get_var( "SHOW TABLES LIKE '{$rest_tokens_table}'" ) != $rest_tokens_table) 
			{
				dbDelta("
				CREATE TABLE {$rest_tokens_table} (
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
				add_option(CFGP_NAME . '-db-version', $database_version, false);
			}
			
			## Create database table for the SEO redirection
			$seo_redirection_table = $wpdb->get_blog_prefix() . CFGP_Defaults::TABLE['seo_redirection'];
			if($wpdb->get_var( "SHOW TABLES LIKE '{$seo_redirection_table}'" ) != $seo_redirection_table) 
			{
				dbDelta("
				CREATE TABLE {$seo_redirection_table} (
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
		});
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