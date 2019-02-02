<?php
/*
 * Allow developers to use plugin data inside PHP
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 5.0.0
 * @improved 7.0.0
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


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