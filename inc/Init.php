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
		add_action('plugins_loaded', [&$this, 'textdomain']);
		// Call main classes
		$classes = apply_filters('cfgp/init/classes', array(
			'CFGP_Taxonomy',			// Register Taxonomy
			'CFGP_Geo_Banner',			// Register Post Type
			'CFGP_API',					// Main API class
			'CFGP_SEO',					// Main SEO class
			'CFGP_Settings',			// Settings class
			'CFGP_Shortcodes',			// Settings class
			'CFGP_Defender',			// Defender class
		));		
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
			CFGP_INC . '/Options.php',			// Plugin option class
			CFGP_INC . '/Global.php',			// Global class
			CFGP_INC . '/IP.php',				// IP class
			CFGP_INC . '/License.php',			// License class
			CFGP_INC . '/Taxonomy.php',			// Taxonomy class
			CFGP_INC . '/Geo_Banner.php',		// Post Type class
			CFGP_INC . '/API.php',				// API class
			CFGP_INC . '/SEO.php',				// SEO class
			CFGP_INC . '/Settings.php',			// Settings class
			CFGP_INC . '/Shortcodes.php',		// Shortcodes class
			CFGP_INC . '/Defender.php',			// Defender class
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
		$inst = self::instance();
		// Dynamic run
		do_action('cfgp/init/dependencies', $inst);
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