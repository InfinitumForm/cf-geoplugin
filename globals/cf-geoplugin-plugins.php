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

// Include WooCommerce integratin
if( file_exists( CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php' ) )
{
	include_once CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php';
	if( class_exists( 'CF_Geoplugin_Woocommerce' ) )
	{
		new CF_Geoplugin_Woocommerce;
		$debug->save( 'WooCommerce integration loaded' );
	}
	else $debug->save( 'WooCommerce integration not loaded - Class does not exists' );
}
else $debug->save(  'WooCommerce integration not loaded - File does not exists' );

// Include CF_Geoplugin_Wooplatnica integration
if( file_exists( CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php' ) )
{
	include_once CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php';
	if( class_exists( 'CF_Geoplugin_Woocommerce' ) && class_exists( 'CF_Geoplugin_Wooplatnica' ) )
	{
		new CF_Geoplugin_Wooplatnica;
		$debug->save( 'Wooplatnica integration loaded' );
	}
	else $debug->save( 'Wooplatnica integration not loaded - Class does not exists' );
}
else $debug->save(  'Wooplatnica integration not loaded - File does not exists' );