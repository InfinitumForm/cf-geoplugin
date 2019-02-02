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

if( file_exists( CFGP_INCLUDES . '/widgets/currency-converter.php' ) )
{
	include_once CFGP_INCLUDES . '/widgets/currency-converter.php';
	$debug->save( 'Widget converter class loaded' );
}
else $debug->save( 'Widget converter class not loaded - File does not exists' );

// Include Google Map Widget
if( file_exists( CFGP_INCLUDES . '/widgets/google-map.php' ) )
{
	include_once CFGP_INCLUDES . '/widgets/google-map.php';
	$debug->save( 'Widget google map class loaded' );
}
else $debug->save( 'Widget google map class not loaded - File does not exists' );
