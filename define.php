<?php if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ) die( "Don't mess with us." );

if ( ! defined( 'CFGP_ROOT' ) )				define( 'CFGP_ROOT', plugin_dir_path(CFGP_FILE) );
if ( ! defined( 'CFGP_INCLUDES' ) )			define( 'CFGP_INCLUDES', __DIR__.'/includes' );
if ( ! defined( 'CFGP_URL' ) )				define( 'CFGP_URL', plugin_dir_url( CFGP_FILE ) );
if ( ! defined( 'CFGP_VERSION' ) )			define( 'CFGP_VERSION', '6.0.3');
if ( ! defined( 'CFGP_NAME' ) )				define( 'CFGP_NAME', 'cf-geoplugin');
if ( ! defined( 'CFGP_METABOX' ) )			define( 'CFGP_METABOX', 'cf_geo_metabox_');
if ( ! defined( 'CFGP_PREFIX' ) )			define( 'CFGP_PREFIX', 'cf_geo_'.preg_replace("/[^0-9]/Ui",'',CFGP_VERSION).'_');
if ( ! defined( 'CFGP_PREMIUM_PRICE' ) )	define( 'CFGP_PREMIUM_PRICE', '32');
if ( ! defined( 'CFGP_STORE' ) )			define( 'CFGP_STORE', 'https://cfgeoplugin.com'); //-If you touch you will break your site. DON'T TOUCH!
if ( ! defined( 'CFGP_STORE_CODE' ) )		define( 'CFGP_STORE_CODE', 'YR5pv3FU8l78v3N'); //-If you touch you will break your site. DON'T TOUCH!

// Define PHP version for older servers
if(!defined('PHP_VERSION'))
{
	define('PHP_VERSION', phpversion());
}
if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', (($version[0] * 10000) + ($version[1] * 100) + $version[2]));
}
if (PHP_VERSION_ID < 50207) {
	if(!(isset($version))) $version = explode('.', PHP_VERSION);
	
	if(!defined('PHP_MAJOR_VERSION'))		define( 'PHP_MAJOR_VERSION',   $version[0]);
	if(!defined('PHP_MINOR_VERSION'))		define( 'PHP_MINOR_VERSION',   $version[1]);
	if(!defined('PHP_RELEASE_VERSION'))		define( 'PHP_RELEASE_VERSION', $version[2]);
	
}