<?php
/**
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @package           CF_Geoplugin
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Geo Plugin
 * Plugin URI:        http://cfgeoplugin.com/
 * Description:       Create Dynamic Content, Banners and Images on Your Website Based On Visitor Geo Location By Using Shortcodes With CF Geo Plugin.
 * Version:           7.12.0
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin
 * Domain Path:       /languages
 * Network:           true
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

// Find is localhost or not
if ( ! defined( 'CFGP_LOCAL' ) ) {
	if(isset($_SERVER['REMOTE_ADDR'])) {
		define('CFGP_LOCAL', in_array($_SERVER['REMOTE_ADDR'], array(
			'127.0.0.1',
			'::1',
			'localhost'
		)));
	} else {
		define('CFGP_LOCAL', false);
	}
}

/**
 * DEBUG MODE
 *
 * This is need for plugin debugging.
 */
if ( defined( 'WP_DEBUG' ) ){
	if(WP_DEBUG === true || WP_DEBUG === 1)
	{
		if ( ! defined( 'WP_CF_GEO_DEBUG' ) ) define( 'WP_CF_GEO_DEBUG', true );
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

// Find wp-admin file path
if ( strrpos(WP_CONTENT_DIR, '/wp-content/', 1) !== false) {
    $WP_ADMIN_DIR = substr(WP_CONTENT_DIR, 0, -10) . 'wp-admin';
} else {
    $WP_ADMIN_DIR = substr(WP_CONTENT_DIR, 0, -11) . '/wp-admin';
}
if (!defined('WP_ADMIN_DIR')) define('WP_ADMIN_DIR', $WP_ADMIN_DIR);


// First initialization
$GLOBALS['CFGEO'] = array();
$GLOBALS['CF_GEOPLUGIN_OPTIONS'] = array();

/*
 * API calls used inside CF Geo Plugin.
 *
 * @since      7.11.0
 * @privacy    https://cfgeoplugin.com/privacy-policy/
 */
$GLOBALS['CFGEO_API_CALL'] = apply_filters( 'cf_geoplugin_api_calls', array(
	// Standard CF Geo Plugin API URLs
	'main'			=>	'https://cdn-cfgeoplugin.com/api/index.php',
	'dns'			=>	'https://cdn-cfgeoplugin.com/api/dns.php',
	'authenticate'	=>	'https://cdn-cfgeoplugin.com/api/authenticate.php',
	'spam-checker'	=>	'https://cdn-cfgeoplugin.com/api/spam-checker.php',
	'converter'		=>	'https://cdn-cfgeoplugin.com/api/convert.php',
	// 3rd party Covid-19 free API call
	'covid-api'		=>	'https://api.covid19api.com',
	// 3rd party IPFY free API call for finding real IP address on the local machines
	'ipfy'			=>	'https://api.ipify.org',
	'smartIP'		=>	'https://smart-ip.net/myip',
	'indent'		=>	'https://ident.me'
));

// Main website
if ( ! defined( 'CFGP_STORE' ) )		define( 'CFGP_STORE', 'https://cfgeoplugin.com');
// Main plugin file
if ( ! defined( 'CFGP_FILE' ) )			define( 'CFGP_FILE', __FILE__ );
// Plugin root
if ( ! defined( 'CFGP_ROOT' ) )			define( 'CFGP_ROOT', rtrim(plugin_dir_path(CFGP_FILE), '/') );
// Globals directory
if ( ! defined( 'CFGP_GLOBALS' ) )		define( 'CFGP_GLOBALS', CFGP_ROOT . '/globals' );
// Shell
if ( ! defined( 'CFGP_SHELL' ) )		define( 'CFGP_SHELL', CFGP_ROOT . '/shell' );
// Require plugin general setup
include_once CFGP_GLOBALS . '/cf-geoplugin-setup.php';
// Includes directory
if ( ! defined( 'CFGP_INCLUDES' ) )		define( 'CFGP_INCLUDES', CFGP_ROOT . '/includes' );
// Includes directory
if ( ! defined( 'CFGP_ADMIN' ) )		define( 'CFGP_ADMIN', CFGP_ROOT . '/admin' );
// Plugin URL root
if ( ! defined( 'CFGP_URL' ) )			define( 'CFGP_URL', rtrim(plugin_dir_url( CFGP_FILE ), '/') );
// Assets URL
if ( ! defined( 'CFGP_ASSETS' ) )		define( 'CFGP_ASSETS', CFGP_URL.'/assets' );
// Plugin name
if ( ! defined( 'CFGP_NAME' ) )			define( 'CFGP_NAME', 'cf-geoplugin');
// Plugin metabox prefix
if ( ! defined( 'CFGP_METABOX' ) )		define( 'CFGP_METABOX', 'cf_geo_metabox_');
// Plugin session prefix (controlled by version)
if ( ! defined( 'CFGP_PREFIX' ) )		define( 'CFGP_PREFIX', 'cf_geo_'.preg_replace("/[^0-9]/Ui",'',CFGP_VERSION).'_');
// Timestamp
if( ! defined( 'CFGP_TIME' ) )			define( 'CFGP_TIME', time() );
// if PHP_VERSION missing
if( ! defined( 'PHP_VERSION' ) && function_exists('phpversion') )
	define( 'PHP_VERSION', phpversion());

// PHP_VERSION_ID is available as of PHP 5.2.7, if our version is lower than that, then emulate it
if (!defined('PHP_VERSION_ID')) {
    $php_version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
	$php_version = NULL;
}
// Fix missing PHP SESSION constant PHP_SESSION_NONE (this is bug on the some Nginx servers)
if (!defined('PHP_SESSION_NONE')) {
	define('PHP_SESSION_NONE', -1);
}
// Check if is multisite installation
if( ! defined( 'CFGP_MULTISITE' ) && defined( 'WP_ALLOW_MULTISITE' ) && WP_ALLOW_MULTISITE && defined( 'MULTISITE' ) && MULTISITE )			
{
	define( 'CFGP_MULTISITE', WP_ALLOW_MULTISITE );
}

if( ! defined( 'CFGP_MULTISITE' ) )			
{
    // New safer approach
    if( !function_exists( 'is_plugin_active_for_network' ) )
		include WP_ADMIN_DIR . '/includes/plugin.php';

	if(file_exists(WP_ADMIN_DIR . '/includes/plugin.php'))
		define( 'CFGP_MULTISITE', is_plugin_active_for_network( CFGP_ROOT . '/cf-geoplugin.php' ) );
}

if( ! defined( 'CFGP_MULTISITE' ) ) define( 'CFGP_MULTISITE', false );

// Requirements
include_once CFGP_GLOBALS . '/cf-geoplugin-requirements.php';
$CF_Geoplugin_Requirements_Check = new CF_Geoplugin_Requirements_Check(array('file' => CFGP_FILE));

if($CF_Geoplugin_Requirements_Check->passes()) :
	// PHP7 DEPRECATED FUNCTION SUPPORT
	include_once CFGP_ROOT.'/includes/php7.x.php';
	// Include privacy policy
	include_once CFGP_ROOT.'/globals/cf-geoplugin-privacy.php';
	// Include debug class
	include_once CFGP_INCLUDES . '/class-cf-geoplugin-debug.php';
	// Activate session
	include_once CFGP_GLOBALS . '/cf-geoplugin-session.php';
	// Our debug object to global variables
	if( !isset( $GLOBALS['debug'] ) ) $GLOBALS['debug'] = new CF_Geoplugin_Debug;
	// Check cURL
	if( !function_exists( 'curl_init' ) ) CF_Geoplugin_Debug::log( 'cURL Status: Disabled' );
	else CF_Geoplugin_Debug::log( 'cURL Status: Enabled' );
	// Include hook class
	include_once CFGP_INCLUDES . '/class-cf-geoplugin-admin-notice.php';
	// Get locale setup
	include_once CFGP_INCLUDES . '/class-cf-geoplugin-locale.php';
	// Get globals
	include_once CFGP_INCLUDES . '/class-cf-geoplugin-global.php';
	// Define important constants
	include_once CFGP_GLOBALS . '/cf-geoplugin-global.php';
	// Define API services
	include_once CFGP_GLOBALS . '/cf-geoplugin-api.php';
	// Include Converter Widget
	include_once CFGP_GLOBALS . '/cf-geoplugin-includes.php';
	// Include Plugin integrations
	include_once CFGP_GLOBALS . '/cf-geoplugin-plugins.php';
	// Include Activation Control
	include_once CFGP_GLOBALS . '/cf-geoplugin-activation.php';

	/*
	* When everything is constructed and builded, just load plugin properly
	* @since 7.0.0
	*/
	function CF_Geoplugin_Invoke(){
		if(class_exists('CF_Geoplugin_Load')) :
			return CF_Geoplugin_Load::cf_geoplugin();
		endif;
	}
	/*
	* Do old function name support (lowercase)
	* @since 6.0.0
	*/
	if(!function_exists('cf_geoplugin')) :
		function cf_geoplugin(){ return CF_Geoplugin_Invoke(); }
	endif;

	// Load plugin properly
	CF_Geoplugin_Load::load_plugin();
	CF_Geoplugin_Debug::log( 'Function "CF_Geoplugin_Load::load_plugin()" is loaded.' );

	// Plugin is loaded
	if(add_action('init', 'CF_Geoplugin', 2, 0)){
		CF_Geoplugin_Debug::log( 'Function "CF_Geoplugin()" is loaded.' );
	} else CF_Geoplugin_Debug::log( 'Function "CF_Geoplugin()" is not loaded and plugin can\'t start.' );

	// Add privacy policy
	add_action( 'admin_init', 'cf_geoplugin_privacy_policy' );
endif;