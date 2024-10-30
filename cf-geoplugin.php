<?php
/**
 * @wordpress-plugin
 *
 * Geo Controller
 *
 * Plugin Name:       Geo Controller
 * Plugin URI:        https://wpgeocontroller.com/
 * Description:       Unlock the power of location-based functionality of WordPress – The ultimate all-in-one geolocation plugin for WordPress.
 * Version:           8.7.7
 * Requires at least: 6.0
 * Requires PHP:      7.0
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin
 * Domain Path:       /languages
 * Network:           true
 *
 * Copyright (C) 2015-2022 Ivijan-Stefan Stipic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Start calculate runtime
if ( ! defined( 'CFGP_START_RUNTIME' ) ){
	define( 'CFGP_START_RUNTIME', microtime());
}

// Library version
if ( ! defined( 'CFGP_LIBRARY_VERSION' ) ){
	define( 'CFGP_LIBRARY_VERSION', '1.0.1');
}
// Database version
if ( ! defined( 'CFGP_DATABASE_VERSION' ) ){
	define( 'CFGP_DATABASE_VERSION', '1.0.3');
}
// Globals
global $cfgp_version;

/*
 * Main plugin constants
 */
$CFGEO = [];

// Main plugin file
if ( ! defined( 'CFGP_FILE' ) ) {
	define( 'CFGP_FILE', __FILE__ );
}

/*
 * Require plugin general setup
 */
include_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

/*
 * Requirements
 */
include_once CFGP_CLASS . DIRECTORY_SEPARATOR . 'Requirements.php';

/*
 * Check requiremant
 */
$CFGP_Requirements = new CFGP_Requirements(array('file' => CFGP_FILE));
if($CFGP_Requirements->passes()) :
	// Dynamic action
	do_action('cfgp/before_plugin_setup');
	// Initializing class
	include_once CFGP_INC . DIRECTORY_SEPARATOR . 'Init.php';
	// Register database tables
	CFGP_Init::wpdb_tables();
	// Include dependencies
	CFGP_Init::dependencies();
	// Plugin activation
	CFGP_Init::activation();
	// Plugin deactivation
	CFGP_Init::deactivation();
	// Plugin upgrade
	CFGP_Init::upgrade();
	// Run plugin
	CFGP_Init::run();
	// Run plugin debug
	CFGP_Init::debug();
	// Dynamic action
	do_action('cfgp/after_plugin_setup');
endif;

/*
 * HEY!!! YOU NEED HELP AROUND THE PLUGIN IN PHP?
 *
 * I believe you need the CFGP_U class located in /inc/classes/Utilities.php
 *
 * There is a set of very useful functions and the most important and most commonly 
 * used is CFGP_U::api() which returns an array of geo objects or an individual object
 * such as CFGP_U::api('country_code').
 *
 * Within the /inc/classes folder are all the classes of our plugin and the file names
 * are named according to their purpose. Everywhere you have some action hooks or
 * filters you can hook up to.
 *
 * If you want to help document our plugin, contact us at office@infinitumform.com
 *
 * Happy coding! Cheers!
 */