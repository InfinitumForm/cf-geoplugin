<?php
/**
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @package           CF_Geoplugin
 *
 * @wordpress-plugin
 * Plugin Name:       CF Geo Plugin
 * Plugin URI:        http://cfgeoplugin.com/
 * Description:       Create Dynamic Content, Banners and Images on Your Website Based On Visitor Geo Location By Using Shortcodes With CF GeoPlugin.
 * Version:           7.0.3
 * Author:            Ivijan-Stefan Stipic
 * Author URI:        https://linkedin.com/in/ivijanstefanstipic
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin
 * Domain Path:       /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// First initialization
$CFGEO = array();
$GLOBALS['CFGEO'] = $CFGEO;

/**
 * DEBUG MODE
 *
 * This is need for plugin debugging.
 */
if ( defined( 'WP_DEBUG' ) ){
	if(WP_DEBUG === true || WP_DEBUG === 1)
	{
		define( 'WP_CF_GEO_DEBUG', true );
	}
}
if ( defined( 'WP_CF_GEO_DEBUG' ) ){
	if(WP_CF_GEO_DEBUG === true || WP_CF_GEO_DEBUG === 1)
	{
		error_reporting( E_ALL );
		if(function_exists('ini_set'))
		{
			ini_set('display_startup_errors',1);
			ini_set('display_errors',1);
		}
	}
}

// Main plugin file
if ( ! defined( 'CFGP_FILE' ) )				define( 'CFGP_FILE', __FILE__ );
// Current plugin version
if ( ! defined( 'CFGP_VERSION' ) )			define( 'CFGP_VERSION', '7.0.3');
// Plugin root
if ( ! defined( 'CFGP_ROOT' ) )				define( 'CFGP_ROOT', rtrim(plugin_dir_path(CFGP_FILE), '/') );
// Includes directory
if ( ! defined( 'CFGP_INCLUDES' ) )			define( 'CFGP_INCLUDES', CFGP_ROOT . '/includes' );
// Includes directory
if ( ! defined( 'CFGP_ADMIN' ) )			define( 'CFGP_ADMIN', CFGP_ROOT . '/admin' );
// Plugin URL root
if ( ! defined( 'CFGP_URL' ) )				define( 'CFGP_URL', rtrim(plugin_dir_url( CFGP_FILE ), '/') );
// Assets URL
if ( ! defined( 'CFGP_ASSETS' ) )			define( 'CFGP_ASSETS', CFGP_URL.'/assets' );
// Plugin name
if ( ! defined( 'CFGP_NAME' ) )				define( 'CFGP_NAME', 'cf-geoplugin');
// Plugin metabox prefix
if ( ! defined( 'CFGP_METABOX' ) )			define( 'CFGP_METABOX', 'cf_geo_metabox_');
// Plugin session prefix (controlled by version)
if ( ! defined( 'CFGP_PREFIX' ) )			define( 'CFGP_PREFIX', 'cf_geo_'.preg_replace("/[^0-9]/Ui",'',CFGP_VERSION).'_');
// Main website
if ( ! defined( 'CFGP_STORE' ) )			define( 'CFGP_STORE', 'https://cfgeoplugin.com');
// Limit
if ( ! defined( 'CFGP_LIMIT' ) )			define( 'CFGP_LIMIT', 300);
// Developer license
if( ! defined( 'CFGP_DEV_MODE' ) )			define( 'CFGP_DEV_MODE', false );

/**
 * Session controll
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
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
		$minutes = 5;
		if(isset($_SESSION[CFGP_PREFIX . 'session_expire']))
		{
			if(time() > $_SESSION[CFGP_PREFIX . 'session_expire'])
			{
				foreach($_SESSION as $key => $val)
				{
					if(strpos($key, CFGP_PREFIX) !== false)
					{
						unset($_SESSION[ $key ]);
					}
				}
				$_SESSION[CFGP_PREFIX . 'session_expire'] = (time() + (60 * $minutes));
			}
		}
		else $_SESSION[CFGP_PREFIX . 'session_expire'] = (time() + (60 * $minutes));
		
		return $_SESSION[CFGP_PREFIX . 'session_expire'];
	}
endif;
CF_Geoplugin_Session();

// Include hook class
include CFGP_INCLUDES . '/class-cf-geoplugin-admin-notice.php';
include CFGP_INCLUDES . '/class-cf-geoplugin-locale.php';
include CFGP_INCLUDES . '/class-cf-geoplugin-global.php';
// Define important constants
if(class_exists('CF_Geoplugin_Global')) :
	// Include hook class
	$hook = new CF_Geoplugin_Global;
	// Global variable for geoplugin options
	$CF_GEOPLUGIN_OPTIONS = $hook->get_option();
	$GLOBALS['CF_GEOPLUGIN_OPTIONS']=$CF_GEOPLUGIN_OPTIONS;
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
	$hook = NULL;
	// Include main class
	 include CFGP_INCLUDES . '/class-cf-geoplugin.php';
	/*
	* Parameter Redirection Loader - Final Class
	* @since 7.0.0
	*/
	if(class_exists('CF_Geoplugin_Init')) :
		class CF_Geoplugin_Load extends CF_Geoplugin_Init
		{
			function __construct(){
				global $CF_GEOPLUGIN_OPTIONS;
				$this->register_activation_hook(CFGP_FILE, 'activate');
				$this->register_deactivation_hook(CFGP_FILE, 'deactivate');
				
				if(isset($CF_GEOPLUGIN_OPTIONS['enable_update']) && $CF_GEOPLUGIN_OPTIONS['enable_update']) $this->add_filter('auto_update_plugin', 'auto_update');
				$this->run();
			}
		}
	endif;
endif;

/*
* Allow developers to use plugin data inside PHP 
* @since 5.0.0
* @improved 7.0.0
*/
if(!class_exists( 'CF_Geoplugin' ) && class_exists( 'CF_Geoplugin_API' )) :
	class CF_Geoplugin
	{
		private $int;
		
		function __construct($options = array()){
			$api = new CF_Geoplugin_API;
			$this->int = $api->run($options);
		}
		
		public function get(){
			return (object) $this->int;
		}
	}
endif;

/*
* When everything is constructed and builded, just load plugin properly
* @since 7.0.0
*/
function CF_Geoplugin(){
	if(class_exists('CF_Geoplugin_Load')) :
		return new CF_Geoplugin_Load();
	endif;
}
/*
* Do old function name support (lowercase)
* @since 6.0.0
*/
if(!function_exists('cf_geoplugin')) :
	function cf_geoplugin(){ return CF_Geoplugin(); }
endif;

// Plugin is loaded
CF_Geoplugin();

// Globals for all folks. Why not?
$CF_GEO = $CF_Geo = $cf_geo = $_GLOBAL['CF_GEO'] = $_GLOBAL['CF_Geo'] = $_GLOBAL['cf_geo'] = (object) $CFGEO;