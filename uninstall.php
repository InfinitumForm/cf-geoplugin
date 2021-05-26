<?php
/**
 * Uninstall plugin and clean everything
 *
 * @link              http://infinitumform.com/
 * @package           CF_Geoplugin
 */
 
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
// Plugin name
if (!defined('CFGP_NAME')) define('CFGP_NAME', 'cf-geoplugin');

// Delete options
if(get_option(CFGP_NAME)) {
	delete_option(CFGP_NAME);
}
if(get_option(CFGP_NAME . '-ID')) {
	delete_option(CFGP_NAME . '-ID');
}
if(get_option(CFGP_NAME. '-activation')) {
	delete_option(CFGP_NAME . '-activation');
}
if(get_option(CFGP_NAME . '-deactivation')) {
	delete_option(CFGP_NAME . '-deactivation');
}
if(get_option(CFGP_NAME . '-version')) {
	delete_option(CFGP_NAME . '-version');
}