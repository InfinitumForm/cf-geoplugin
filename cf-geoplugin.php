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
 * Version:           6.0.7
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ) die( "Don't mess with us." );

// Define main file
if ( ! defined( 'CFGP_FILE' ) )		define( 'CFGP_FILE', __FILE__ );
if ( ! defined( 'CFGP_VERSION' ) )	define( 'CFGP_VERSION', '6.0.7');

/**
 * DEBUG MODE
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
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

/**
 * Define main constants
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
include_once plugin_dir_path(CFGP_FILE).'define.php';

// Check SSL
if(!function_exists('cf_geo_is_ssl')) {
	function cf_geo_is_ssl($url = false)
    {
		if($url !== false && is_string($url)) {
			return (preg_match('/(https|ftps)/Ui', $url) !== false);
		} else if( is_admin() && defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ===true ) {
			return true;
		} else if( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||
			(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) )
		{
			return true;
		}
		return false;
    }
}

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

/**
 * Start and define all dependency
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
include_once CFGP_INCLUDES.'/class-dependency.php';
if(class_exists('CF_GEO_D')) {
	$cfgeod = new CF_GEO_D;
	if ( ! defined( 'CFGP_IP' ) ) 					define('CFGP_IP', $cfgeod->IP );
	if ( ! defined( 'CFGP_SERVER_IP' ) ) 			define('CFGP_SERVER_IP', $cfgeod->SERVER_IP );
	if ( ! defined( 'CFGP_PROXY' ) ) 				define('CFGP_PROXY', $cfgeod->PROXY );
	if ( ! defined( 'CFGP_ACTIVATED' ) ) 			define('CFGP_ACTIVATED', $cfgeod->ACTIVATED );
	if ( ! defined( 'CFGP_DEFENDER_ACTIVATED' ) ) 	define('CFGP_DEFENDER_ACTIVATED', $cfgeod->DEFENDER_ACTIVATED );
	$cfgeod = NULL;
	// Update dependency
	if(!get_option('cf_geo_store'))			 update_option('cf_geo_store', CFGP_STORE, true);
	if(!get_option('cf_geo_store_code'))	 update_option('cf_geo_store_code', CFGP_STORE_CODE, true);
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf-geoplugin-activator.php
 */
function activate_cf_geoplugin() {
	require_once plugin_dir_path( CFGP_FILE ) . 'includes/class-cf-geoplugin-activator.php';
	CF_Geoplugin_Activator::activate();
}
register_activation_hook( CFGP_FILE, 'activate_cf_geoplugin' );
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf-geoplugin-deactivator.php
 */
function deactivate_cf_geoplugin() {
	require_once plugin_dir_path( CFGP_FILE ) . 'includes/class-cf-geoplugin-deactivator.php';
	CF_Geoplugin_Deactivator::deactivate();
}
register_deactivation_hook( CFGP_FILE, 'deactivate_cf_geoplugin' );
/**
* Get Custom Post Data from forms
* @autor    Ivijan-Stefan Stipic
* @since    5.0.0
* @version  1.0.1
**/
if ( ! function_exists( 'CF_Geoplugin_Metabox_GET' ) ) :
	function CF_Geoplugin_Metabox_GET($name, $id=false, $single=false){
		global $post_type, $post;
		
		$name=trim($name);
		$prefix=CFGP_METABOX;
		$data=NULL;
	
		if($id!==false && !empty($id) && $id > 0)
			$getMeta=get_post_meta((int)$id, $prefix.$name, $single);
		else if(NULL!==get_the_id() && false!==get_the_id() && get_the_id() > 0)
			$getMeta=get_post_meta(get_the_id(),$prefix.$name, $single);
		else if(isset($post->ID) && $post->ID > 0)
			$getMeta=get_post_meta($post->ID,$prefix.$name, $single);
		else if('page' == get_option( 'show_on_front' ))
			$getMeta=get_post_meta(get_option( 'page_for_posts' ),$prefix.$name, $single);
		else if(is_home() || is_front_page() || get_queried_object_id() > 0)
			$getMeta=get_post_meta(get_queried_object_id(),$prefix.$name, $single);
		else
			$getMeta=false;
		
		return (!empty($getMeta)?$getMeta:NULL);
	}
endif;

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if(!class_exists('CF_Geoplugin'))
{
	require plugin_dir_path( CFGP_FILE ) . 'includes/class-cf-geoplugin.php';
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.0.0
 */
function cf_geoplugin() {
	if(class_exists('CF_Geoplugin'))
	{
		$plugin = new CF_Geoplugin();
		$plugin->run();
		if(!CFGP_ACTIVATED)
		{
			if(
				isset($_GET['page']) && (
					$_GET['page'] == 'cf-geoplugin-activate' ||
					$_GET['page'] == 'cf-geoplugin-settings'
				)
			){} else add_action( 'plugins_loaded', 'cf_geo_activation' );
		}
		function cf_geo_activation() {
			$lookup = do_shortcode('[cf_geo return="lookup"]');
			if(is_numeric($lookup) && (
					((int)$lookup) <= 300 && ((int)$lookup) >= 295
					|| ((int)$lookup) <= 200 && ((int)$lookup) >= 195
					|| ((int)$lookup) <= 100
				)
			){
				add_action( 'admin_notices', 'cf_geo_activation_notice__error' );
			}
		}
	
		function cf_geo_activation_notice__error() {
			$title = __( 'CF GEO PLUGIN', CFGP_NAME );
			$lookup = (int)do_shortcode('[cf_geo return="lookup"]');
			if($lookup && $lookup > 50)
				$class = 'notice notice-warning is-dismissible';
			else
				$class = 'notice notice-error is-dismissible';
				
			$message1 = sprintf(
				__('You currently using free version of plugin with a limited number of lookups.<br>Each free version of this plugin is limited to %1$s lookups per day and you have only %2$s lookups available for today. If you want to have unlimited lookup, please enter your license key.<br>If you are unsure and do not understand what this is about, read %3$s.<br><br>Also, before any action don\'t forget to read and agree with %4$s and %5$s.',CFGP_NAME),
				
				'<strong>300</strong>',
				'<strong>'.$lookup.'</strong>',
				'<strong><a href="https://cfgeoplugin.com/new-plugin-new-features-new-success/" target="_blank">' . __('this article',CFGP_NAME) . '</a></strong>',
				'<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy',CFGP_NAME) . '</a></strong>',
				'<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions',CFGP_NAME) . '</a></strong>'
			);
			$message2 = '<a href="' . admin_url('/admin.php?page=cf-geoplugin-activate') . '" class="button button-primary">' . __('Activate Unlimited',CFGP_NAME) . '</a>';

	
			printf( '<div class="%1$s"><h3>%2$s</h3><p>%3$s</p><p><strong>%4$s</strong></p></div>', esc_attr( $class ), esc_html( $title ),  $message1, $message2); 
		}
		
		// Global Variable
		$GLOBALS['cf_geo'] = $GLOBALS['CF_Geo'] = $GLOBALS['CF_GEO'] = $CF_GEO = $plugin->get();
	}
}
cf_geoplugin();