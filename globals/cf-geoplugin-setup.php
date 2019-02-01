<?php
/*
 * Plugin setup
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 7.5.4
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


// Current plugin version ( if change, clear also session cache )
if ( ! defined( 'CFGP_VERSION' ) )			define( 'CFGP_VERSION', '7.5.6');
// Limit ( for the information purposes )
if ( ! defined( 'CFGP_LIMIT' ) )			define( 'CFGP_LIMIT', 300);
// Developer license ( enable developer license support )
if( ! defined( 'CFGP_DEV_MODE' ) )			define( 'CFGP_DEV_MODE', false );
// Session expire in % minutes
if( ! defined( 'CFGP_SESSION' ) )			define( 'CFGP_SESSION', 5 ); // 5 minutes