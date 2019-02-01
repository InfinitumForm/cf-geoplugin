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


/* Register important constants */
if(class_exists('CF_Geoplugin_Global')) :
	// Lets use debug
	$debug = $GLOBALS['debug'];
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

	$debug->save( 'Global Class Loaded' );
	$debug->save( 'Current options:' );
	$debug->save( $options );
	$options = NULL;

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
			private $init = NULL;
			// Instance
			private static $instance = null;
			
			function __construct(){
				$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
				$this->register_activation_hook(CFGP_FILE, 'activate');
				$this->register_deactivation_hook(CFGP_FILE, 'deactivate');
				$this->init = $this->run();
			}
			
			public static function cfgp($a){
				$cfg = self::get_instance();
				return $cfg->init;
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

/* Let's allow PHP integrations */
if(!class_exists('CF_Geoplugin')) :
	class CF_Geoplugin{
		
		private $init = NULL;
		function __construct($options=array()){
			if(isset($_SESSION[CFGP_PREFIX . 'php_api_session']) && !(isset($options['ip']) && $_SESSION[CFGP_PREFIX . 'php_api_session']['ip'] !== $options['ip']))
			{
				$_SESSION[CFGP_PREFIX . 'php_api_session']['current_time'] = date('H:i:s', CFGP_TIME);
				$_SESSION[CFGP_PREFIX . 'php_api_session']['current_date'] = date('F j, Y', CFGP_TIME);
				$this->init = $_SESSION[CFGP_PREFIX . 'php_api_session'];
				$GLOBALS['debug']->save( 'Run from custom PHP API session:' );
				$GLOBALS['debug']->save( $_SESSION[CFGP_PREFIX . 'php_api_session'] );
			}
			else
			{
				// Include internal library
				if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-library.php'))
				{
					include_once CFGP_INCLUDES . '/class-cf-geoplugin-library.php';
				}
				else $GLOBALS['debug']->save( 'Library not included - Files does not exists' );
				
				if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-api.php'))
				{
					include_once CFGP_INCLUDES . '/class-cf-geoplugin-api.php';
					if(class_exists('CF_Geoplugin_API')){
						// Run API
						$CFGEO_API = new CF_Geoplugin_API();
						$GLOBALS['debug']->save( 'Custom API class loaded' );
						$this->init = $CFGEO_API->run($options);
						$GLOBALS['debug']->save( 'Custom API returned data:' );
						$GLOBALS['debug']->save( $this->init );
						$_SESSION[CFGP_PREFIX . 'php_api_session'] = $this->init;
						$GLOBALS['debug']->save( 'Saved into custom PHP API session:' );
						$GLOBALS['debug']->save( $_SESSION[CFGP_PREFIX . 'php_api_session'] );
					}
					else $GLOBALS['debug']->save( 'Custom API class not loaded - Class does not exists' );
				}
				else $GLOBALS['debug']->save( 'Custom API class not loaded - File does not exists' );
			}
		}
		
		public function get(){			
			return (object) $this->init;
		}
	}
endif;