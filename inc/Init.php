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
	
	public function __construct(){
		// Do translations
		add_action('plugins_loaded', array(&$this, 'textdomain'));
		
		// Call main classes
		$classes = apply_filters('cfgp/init/classes', array(
			'CFGP_Taxonomy',			// Register Taxonomy
			'CFGP_Geo_Banner',			// Register Post Type
			'CFGP_Metabox',				// Metabox class
			'CFGP_API',					// Main API class
			'CFGP_Media',				// Media class
			'CFGP_Settings',			// Settings class
			'CFGP_Admin',				// Admin class
			'CFGP_Help',				// Contextual help class
			'CFGP_Shortcodes',			// Settings class
			'CFGP_Defender',			// Defender class
			'CFGP_Public',				// Public class
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
			$class::instance();
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
			CFGP_INC . '/OS.php',				// Operating System info and tool class
			CFGP_INC . '/Defaults.php',			// Default values, data
			CFGP_INC . '/Utilities.php',		// Utilities
			CFGP_INC . '/Library.php',			// Library, data
			CFGP_INC . '/Form.php',				// Form class
			CFGP_INC . '/Options.php',			// Plugin option class
			CFGP_INC . '/Global.php',			// Global class
			CFGP_INC . '/Admin.php',			// Admin option class
			CFGP_INC . '/Help.php',				// Contextual help class
			CFGP_INC . '/IP.php',				// IP class
			CFGP_INC . '/License.php',			// License class
			CFGP_INC . '/Media.php',			// Media class
			CFGP_INC . '/Taxonomy.php',			// Taxonomy class
			CFGP_INC . '/Geo_Banner.php',		// Post Type class
			CFGP_INC . '/Metabox.php',			// Metabox class
			CFGP_INC . '/API.php',				// API class
			CFGP_INC . '/SEO.php',				// SEO class
			CFGP_INC . '/SEO_Redirection.php',	// SEO Redirection class
			CFGP_INC . '/SEO_Table.php',		// SEO Table class
			CFGP_INC . '/Settings.php',			// Settings class
			CFGP_INC . '/Shortcodes.php',		// Shortcodes class
			CFGP_INC . '/Defender.php',			// Defender class
			CFGP_INC . '/Public.php',			// Public class
			CFGP_INC . '/REST.php',				// REST class
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
		if ( $loaded = load_textdomain( CFGP_NAME, CFGP_ROOT . '/languages' . '/' . $locale . '.mo' ) ) {
			return $loaded;
		} else {
			load_plugin_textdomain( CFGP_NAME, FALSE, CFGP_ROOT . '/languages' );
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
				update_option(CFGP_NAME . '-activation', $activation);
			} else {
				add_option(CFGP_NAME . '-activation', array(date('Y-m-d H:i:s')));
			}

			// Generate unique ID
			if(!get_option(CFGP_NAME . '-ID')) {
				add_option(CFGP_NAME . '-ID', 'cfgp_'.CFGP_U::generate_token(55).'_'.CFGP_U::generate_token(4));
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
				add_option(CFGP_NAME . '-db-version', $database_version);
			}
			
			## Create database table for the SEO redirection
			$seo_redirection_table = $wpdb->prefix . CFGP_Defaults::TABLE['seo_redirection'];
			if($wpdb->get_var( "SHOW TABLES LIKE '{$seo_redirection_table}'" ) != $seo_redirection_table) 
			{
				dbDelta("
				CREATE TABLE {$seo_redirection_table} (
					ID int(11) NOT NULL AUTO_INCREMENT,
					`only_once` tinyint(1) NOT NULL DEFAULT 0,
					`country` varchar(100) NOT NULL,
					`region` varchar(100) NOT NULL,
					`city` varchar(100) NOT NULL,
					`postcode` varchar(100) NOT NULL,
					`url` varchar(100) NOT NULL,
					`http_code` smallint(3) NOT NULL DEFAULT 302,
					`active` tinyint(1) NOT NULL DEFAULT 1,
					`date` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (ID)
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
				update_option(CFGP_NAME . '-deactivation', $deactivation);
			} else {
				add_option(CFGP_NAME . '-deactivation', array(date('Y-m-d H:i:s')));
			}
		});
	}
	
	/* 
	 * Instance
	 * @verson    8.0.0
	 */
	public static function instance() {
		global $cfgp_cache;
		$class = self::class;
		$instance = $cfgp_cache->get($class);
		if ( !$instance ) {
			$instance = $cfgp_cache->set($class, new self());
		}
		return $instance;
	}
}
endif;