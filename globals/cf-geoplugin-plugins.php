<?php
/*
 * Plugin integrations
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 7.5.7
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Include important function
if(!function_exists('is_plugin_active'))
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Include WooCommerce integratin
if( file_exists( CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php' ) )
{
	include_once CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php';
	if( is_plugin_active('woocommerce/woocommerce.php') )
	{
		new CF_Geoplugin_Woocommerce;
		$debug->save( 'WooCommerce integration loaded' );
	}
}
else $debug->save(  'WooCommerce integration not loaded - File does not exists' );

// Include CF_Geoplugin_Wooplatnica integration
if( file_exists( CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php' ) )
{
	include_once CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php';
	if( is_plugin_active('wooplatnica/wooplatnica.php') )
	{
		new CF_Geoplugin_Wooplatnica;
		$debug->save( 'Wooplatnica integration loaded' );
	}
}
else $debug->save(  'Wooplatnica integration not loaded - File does not exists' );

// Include CF_Geoplugin_Monarch integration
if( file_exists( CFGP_INCLUDES . '/plugins/monarch/monarch.php' ) )
{
	include_once CFGP_INCLUDES . '/plugins/monarch/monarch.php';
	if( is_plugin_active('monarch/monarch.php') )
	{
		new CF_Geoplugin_Monarch;
		$debug->save( 'Monarch integration loaded' );
	}
	else $debug->save( 'Monarch integration not loaded - Class does not exists' );
}
else $debug->save(  'Monarch integration not loaded - File does not exists' );