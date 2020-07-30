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
	function clear_cf_geoplugin_session($clear = false){

		if($clear !== false)
		{
			if(isset($_SESSION) && !empty($_SESSION))
			{
				foreach($_SESSION as $key => $val)
				{
					if(strpos($key, CFGP_PREFIX) !== false)
					{
						unset($_SESSION[ $key ]);
					}
				}
				$_SESSION[CFGP_PREFIX . 'session_expire'] = (CFGP_SESSION <= 0 ? 0 : (CFGP_TIME + (60 * CFGP_SESSION)));
				$_SESSION[CFGP_PREFIX . 'session_setup'] = CFGP_SESSION;
			}

			do_action('clear_cf_geoplugin_session');
			
			return true;
		}
		else
		{
			if(isset($_SESSION) && !empty($_SESSION))
			{
				if(isset($_SESSION[CFGP_PREFIX . 'session_expire']) || (isset($_SESSION[CFGP_PREFIX . 'session_setup']) && $_SESSION[CFGP_PREFIX . 'session_setup'] != CFGP_SESSION) || CFGP_SESSION < 1)
				{
					foreach($_SESSION as $key => $val)
					{
						if(strpos($key, CFGP_PREFIX) !== false)
						{
							unset($_SESSION[ $key ]);
						}
					}
					$_SESSION[CFGP_PREFIX . 'session_expire'] = (CFGP_SESSION <= 0 ? 0 : (CFGP_TIME + (60 * CFGP_SESSION)));
					$_SESSION[CFGP_PREFIX . 'session_setup'] = CFGP_SESSION;
					
					return true;
				}
			}
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
		if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
			if(function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				session_start(apply_filters( 'cf_geoplugin_php7_session_options', array(
				  'cache_limiter' => 'private_no_expire',
				  'read_and_close' => false
			   )));
			}
		}
		else if (version_compare(PHP_VERSION, '5.4.0', '>=') && version_compare(PHP_VERSION, '7.0.0', '<'))
		{
			if (function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				session_cache_limiter('private_no_expire');
				session_start();
			}
		}
		else
		{
			if(session_id() == '') {
				if(version_compare(PHP_VERSION, '4.0.0', '>=')){
					session_cache_limiter('private_no_expire');
				}
				session_start();
			}
		}
		
		// Clear session
		if(isset($_GET['cf_geoplugin_clear_session']) && $_GET['cf_geoplugin_clear_session'] == 'true'){
			clear_cf_geoplugin_session(true);
			
			$protocol = 'http' . (((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '') . '://';  
			$CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			
			if(strpos($CurPageURL, '?cf_geoplugin_clear_session') !== false){
				$CurPageURL = str_replace('?cf_geoplugin_clear_session=true', '', $CurPageURL);
				if(header('Location: ' . $CurPageURL)) exit;
			} else if(strpos($CurPageURL, '&cf_geoplugin_clear_session') !== false){
				$CurPageURL = str_replace('&cf_geoplugin_clear_session=true', '', $CurPageURL);
				if(header('Location: ' . $CurPageURL)) exit;
			}
		}

		/**
		 * Clear session on the certain time
		 *
		 * This is importnat to avoid bugs regarding accuracy
		 *
		 * @author     Ivijan-Stefan Stipic  <creativform@gmail.com>
		 */
		
		if(isset($_SESSION[CFGP_PREFIX . 'session_setup']) && $_SESSION[CFGP_PREFIX . 'session_setup'] !== CFGP_SESSION || CFGP_SESSION <= 0)
		{
			clear_cf_geoplugin_session();
		}
		 
		if(isset($_SESSION[CFGP_PREFIX . 'session_expire']))
		{
			if(CFGP_TIME > $_SESSION[CFGP_PREFIX . 'session_expire'])
			{
				clear_cf_geoplugin_session();
			}
		}
		else{
			$_SESSION[CFGP_PREFIX . 'session_expire'] = (CFGP_TIME + (60 * CFGP_SESSION));
			$_SESSION[CFGP_PREFIX . 'session_setup'] = CFGP_SESSION;
		}

		return $_SESSION[CFGP_PREFIX . 'session_expire'];
	}
endif;

// Let's run session anytime
CF_Geoplugin_Session();

// We must keep session running on all levels (safe mode)
add_action('init', 'CF_Geoplugin_Session');
add_action('after_setup_theme', 'CF_Geoplugin_Session');
add_action('wp_loaded', 'CF_Geoplugin_Session');
add_action('send_headers', 'CF_Geoplugin_Session');
add_action('template_redirect', 'CF_Geoplugin_Session');