<?php
/*
 * Register important constants and setup globals
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 7.0.0
 * @improved 7.5.4
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Lets use debug
$debug = $GLOBALS['debug'];

/* Register important constants */
if(class_exists('CF_Geoplugin_Global')) :
	// Include hook class
	$hook = new CF_Geoplugin_Global;
	// Global variable for geoplugin options
	$GLOBALS['CF_GEOPLUGIN_OPTIONS']=$hook->get_option();
	// Fix session time
	if(isset($GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated']) && empty($GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated'])){
		$GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated'] = CFGP_TIME;
		$hook->update_option('plugin_activated', $GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated']);
	} else {
		if(empty($GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated']))
		{
			$GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated'] = CFGP_TIME;
			$hook->update_option('plugin_activated', $GLOBALS['CF_GEOPLUGIN_OPTIONS']['plugin_activated']);
		}
	}
	// fix ID if missing
	if(isset($GLOBALS['CF_GEOPLUGIN_OPTIONS']['id']) && empty($GLOBALS['CF_GEOPLUGIN_OPTIONS']['id'])){
		$GLOBALS['CF_GEOPLUGIN_OPTIONS']['id'] = md5($hook->generate_token(32));
		$hook->update_option('id', $GLOBALS['CF_GEOPLUGIN_OPTIONS']['id']);
	} else {
		if(empty($GLOBALS['CF_GEOPLUGIN_OPTIONS']['id']))
		{
			$GLOBALS['CF_GEOPLUGIN_OPTIONS']['id'] = md5($hook->generate_token(32));
			$hook->update_option('id', $GLOBALS['CF_GEOPLUGIN_OPTIONS']['id']);
		}
	}

	// Client IP address
	if ( ! defined( 'CFGP_IP' ) ) 					define( 'CFGP_IP', $hook->ip() );
	// Server IP address
	if ( ! defined( 'CFGP_SERVER_IP' ) ) 			define( 'CFGP_SERVER_IP', $hook->ip_server() );
	// Proxy enabled
	if ( ! defined( 'CFGP_PROXY' ) ) 				define( 'CFGP_PROXY', $hook->proxy() );
	// License key activation true/false
	if ( ! defined( 'CFGP_ACTIVATED' ) ) 			define( 'CFGP_ACTIVATED', $hook->check_activation() );
	// Defender true/false
	if ( ! defined( 'CFGP_DEFENDER_ACTIVATED' ) ) 	define( 'CFGP_DEFENDER_ACTIVATED', $hook->check_defender_activation() );

	$options = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
	
	// GDRP - Protect users private data
	foreach( array( 'map_api_key', 'proxy_ip', 'proxy_port', 'proxy_username', 'proxy_password' ) as $i => $opt ) if( isset( $options[ $opt ] ) ) unset( $options[ $opt ] );

if($debug && property_exists($debug, 'save'))
{
	$debug->save( 'Global Class Loaded' );
	$debug->save( 'Current options:' );
	$debug->save( $options );
	$options = NULL;
}
	$hook = NULL;
	// Include main class
	include CFGP_INCLUDES . '/class-cf-geoplugin.php';
	/*
	* CF Geoplugin Loader
	* @since 7.0.0
	*/
	if( !class_exists( 'CF_Geoplugin_Load' ) && class_exists('CF_Geoplugin_Init')) :
		class CF_Geoplugin_Load extends CF_Geoplugin_Init
		{
			// Instance
			private static $instance = null;
			private $CF_GEOPLUGIN_OPTIONS;
			
			function __construct(){
				$this->CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
			}
			
			public static function cf_geoplugin(){
				$instance = self::get_instance();
				$CF_GEOPLUGIN_OPTIONS = $instance->CF_GEOPLUGIN_OPTIONS;
				return $instance->run();
			}
			
			public static function load_plugin(){
				$instance = self::get_instance();
				$CF_GEOPLUGIN_OPTIONS = $instance->CF_GEOPLUGIN_OPTIONS;
				$instance->register_activation_hook(CFGP_FILE, 'activate');
				$instance->register_deactivation_hook(CFGP_FILE, 'deactivate');
			}
			
			/**
			 * Get singleton instance of global class
			 * @since     7.4.0
			 * @version   7.4.0
			 */
			public static function get_instance()
			{
				if( NULL === self::$instance )
				{
					self::$instance = new self();
				}
			
				return self::$instance;
			}
		}
	endif;
endif;