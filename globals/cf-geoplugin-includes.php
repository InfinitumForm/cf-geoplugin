<?php
/**
 * Main Plugin Includes
 *
 * @since      7.5.7
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */

// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Lets use debug
$debug = $GLOBALS['debug'];

if( file_exists( CFGP_INCLUDES . '/widgets/currency-converter.php' ) )
{
	include_once CFGP_INCLUDES . '/widgets/currency-converter.php';
	if($debug && property_exists($debug, 'save'))
		$debug->save( 'Widget converter class loaded' );
}
else
{	
	if($debug && property_exists($debug, 'save'))
		$debug->save( 'Widget converter class not loaded - File does not exists' );
}

// Include Google Map Widget
if( file_exists( CFGP_INCLUDES . '/widgets/google-map.php' ) )
{
	include_once CFGP_INCLUDES . '/widgets/google-map.php';
	if($debug && property_exists($debug, 'save'))
		$debug->save( 'Widget google map class loaded' );
}
else {
	if($debug && property_exists($debug, 'save'))
		$debug->save( 'Widget google map class not loaded - File does not exists' );
}
