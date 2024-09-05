<?php
/*
 * Plugin setup
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since      8.0.0
*/

if (!defined('WPINC')) {
    die("Don't mess with us.");
}
if (!defined('ABSPATH')) {
    exit;
}

global $WP_ADMIN_DIR, $WP_ADMIN_URL;

// Find wp-admin file path
if (!defined('WP_ADMIN_DIR')) {
	define('WP_ADMIN_DIR', ABSPATH . 'wp-admin');
}

$WP_ADMIN_DIR = WP_ADMIN_DIR;
if( function_exists('wp_admin') ) {
	$WP_ADMIN_URL = wp_admin('/');
}

// Enable or disable REST
if (!defined('CFGP_REST_ENABLED')) {
    define('CFGP_REST_ENABLED', true);
}

// Main website
if (!defined('CFGP_STORE')) {
    define('CFGP_STORE', 'https://wpgeocontroller.com');
}

// Main website code
if (!defined('CFGP_STORE_CODE')) {
    define('CFGP_STORE_CODE', 'YR5pv3FU8l78v3N'); // DON'T TOUCH!!!
}

// Plugin root
if (!defined('CFGP_ROOT')) {
    define('CFGP_ROOT', rtrim(plugin_dir_path(CFGP_FILE) , DIRECTORY_SEPARATOR));
}

// Shell
if (!defined('CFGP_SHELL')) {
    define('CFGP_SHELL', CFGP_ROOT . DIRECTORY_SEPARATOR . 'shell');
}

// Library
if (!defined('CFGP_LIBRARY')) {
    define('CFGP_LIBRARY', CFGP_ROOT . DIRECTORY_SEPARATOR . 'library');
}

// Includes directory
if (!defined('CFGP_INC')) {
    define('CFGP_INC', CFGP_ROOT . DIRECTORY_SEPARATOR . 'inc');
}

// Classes directory
if (!defined('CFGP_CLASS')) {
    define('CFGP_CLASS', CFGP_INC . DIRECTORY_SEPARATOR . 'classes');
}

// Plugins directory
if (!defined('CFGP_PLUGINS')) {
    define('CFGP_PLUGINS', CFGP_INC . DIRECTORY_SEPARATOR . 'plugins');
}

// Limit ( for the information purposes )
if (!defined('CFGP_LIMIT')) {
    define('CFGP_LIMIT', 1000);
}

// Developer license ( enable developer license support )
if (!defined('CFGP_DEV_MODE')) {
    define('CFGP_DEV_MODE', false);
}

// Session expire in % minutes
if (!defined('CFGP_SESSION')) {
    define('CFGP_SESSION', 60); // 30 minutes
    
}

// W3 total cache setup
if (!defined('W3TC_DYNAMIC_SECURITY')) {
    define('W3TC_DYNAMIC_SECURITY', 'cfgeo_' . md5(get_bloginfo('url')));
}

// Disable email notifications
if (!defined('CFGP_DISABLE_NOTIFICATION')) {
    define('CFGP_DISABLE_NOTIFICATION', true);
}

// Disable notification for the "License will expire soon"
if (!defined('CFGP_DISABLE_NOTIFICATION_EXPIRE_SOON')) {
    define('CFGP_DISABLE_NOTIFICATION_EXPIRE_SOON', true);
}

// Disable notification for the "Lookup expired"
if (!defined('CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRED')) {
    define('CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRED', true);
}

// Disable notification for the "Lookup expires soon"
if (!defined('CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON')) {
    define('CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON', true);
}

// Plugin URL root
if (!defined('CFGP_URL')) {
    define('CFGP_URL', rtrim(plugin_dir_url(CFGP_FILE) , '/'));
}

// Assets URL
if (!defined('CFGP_ASSETS')) {
    define('CFGP_ASSETS', CFGP_URL . '/assets');
}

// Plugin name
if (!defined('CFGP_NAME')) {
    define('CFGP_NAME', 'cf-geoplugin');
}

// Allow deprecated methods (Enabled for some next few versions)
if (!defined('CFGP_ALLOW_DEPRECATED_METHODS')) {
    define('CFGP_ALLOW_DEPRECATED_METHODS', true);
}

// Current plugin version ( if change, clear also session cache )
global $cfgp_version;

if (function_exists('get_file_data') && $plugin_data = get_file_data(CFGP_FILE, array(
    'Version' => 'Version'
) , false)) {
    $cfgp_version = (string)$plugin_data['Version'];
}

if (!$cfgp_version && preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i', file_get_contents(CFGP_FILE) , $v)) {
    $cfgp_version = (string)$v[1];
}

if (!defined('CFGP_VERSION')) {
    define('CFGP_VERSION', $cfgp_version);
}

// Plugin metabox prefix
if (!defined('CFGP_METABOX')) {
    define('CFGP_METABOX', 'cf_geo_metabox_');
}

// Plugin session prefix (controlled by version)
if (!defined('CFGP_PREFIX')) {
    define('CFGP_PREFIX', 'cf_geo_' . preg_replace("/[^0-9]/Ui", '', CFGP_VERSION) . '_');
}

// Timestamp
if (!defined('CFGP_TIME')) {
    define('CFGP_TIME', time());
}

// Time Format
if (!defined('CFGP_TIME_FORMAT')) {
    define('CFGP_TIME_FORMAT', get_option('time_format'));
}

// Date Format
if (!defined('CFGP_DATE_FORMAT')) {
    define('CFGP_DATE_FORMAT', get_option('date_format'));
}

// Date Format
if (!defined('CFGP_DATE_TIME_FORMAT')) {
    define('CFGP_DATE_TIME_FORMAT', CFGP_DATE_FORMAT . ' ' . CFGP_TIME_FORMAT);
}

// if PHP_VERSION missing
if (!defined('PHP_VERSION') && function_exists('phpversion')) {
    define('PHP_VERSION', phpversion());
}

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
if (!defined('CFGP_MULTISITE') && defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE && defined('MULTISITE') && MULTISITE) {
    define('CFGP_MULTISITE', WP_ALLOW_MULTISITE);
}
if (!defined('CFGP_MULTISITE')) {
    // New safer approach
    if (!function_exists('is_plugin_active_for_network')) {
        include WP_ADMIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php';
    }

    if (file_exists(WP_ADMIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php')) {
        define('CFGP_MULTISITE', (function_exists('is_plugin_active_for_network') ? is_plugin_active_for_network(CFGP_ROOT . DIRECTORY_SEPARATOR . 'cf-geoplugin.php') : false));
    }
}
if (!defined('CFGP_MULTISITE')) {
    define('CFGP_MULTISITE', false);
}

// Check is network admin
if (!defined('CFGP_NETWORK_ADMIN')) {
    define('CFGP_NETWORK_ADMIN', (function_exists('is_network_admin') && is_network_admin()));
}

// Check is defender activated
if (!defined('CFGP_DEFENDER_ACTIVATED')) {
    define('CFGP_DEFENDER_ACTIVATED', false); //- DEBUG
}