<?php
/*
 * Session control
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 7.0.0
 * @improved 7.5.4
*/

// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Clear CF Geo Plugin Session */
if(!function_exists('clear_cf_geoplugin_session')) :
	function clear_cf_geoplugin_session(){
		if(isset($_SESSION[CFGP_PREFIX . 'session_expire']))
		{
			foreach($_SESSION as $key => $val)
			{
				if(strpos($key, CFGP_PREFIX) !== false)
				{
					unset($_SESSION[ $key ]);
				}
			}
			$_SESSION[CFGP_PREFIX . 'session_expire'] = (CFGP_TIME + (60 * CFGP_SESSION));
			return true;
		}
		return false;
	}
endif;

/* Start CF Geo Plugin Session */
if(!function_exists('CF_Geoplugin_Session')) :
	function CF_Geoplugin_Session()
	{
		/**
		 * Start sessions if not exists
		 *
		 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
		 */
		if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
			if(function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				session_start(array(
				  'cache_limiter' => 'private_no_expire',
				  'read_and_close' => false,
			   ));
			}
		}
		else if (version_compare(PHP_VERSION, '5.4.0') >= 0)
		{
			if (function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				session_cache_limiter('private_no_expire');
				session_start();
			}
		}
		else
		{
			if(session_id() == '') {
				if(version_compare(PHP_VERSION, '4.0.0') >= 0){
					session_cache_limiter('private_no_expire');
				}
				session_start();
			}
		}
		/**
		 * Clear session on the certain time
		 *
		 * This is importnat to avoid bugs regarding accuracy
		 *
		 * @author     Ivijan-Stefan Stipic  <creativform@gmail.com>
		 */
		if(isset($_SESSION[CFGP_PREFIX . 'session_expire']))
		{
			if(CFGP_TIME > $_SESSION[CFGP_PREFIX . 'session_expire'])
			{
				clear_cf_geoplugin_session();
			}
		}
		else $_SESSION[CFGP_PREFIX . 'session_expire'] = (CFGP_TIME + (60 * CFGP_SESSION));

		return $_SESSION[CFGP_PREFIX . 'session_expire'];
	}
endif;
CF_Geoplugin_Session();
