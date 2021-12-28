<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WordPress Geo Plugin
 * Plugin URI:        https://cfgeoplugin.com/
 * Description:       Create Dynamic Content, Banners and Images on Your Website Based On Visitor Geo Location By Using Shortcodes With CF Geo Plugin.
 * Version:           8.0.0
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin
 * Domain Path:       /languages
 * Network:           true
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
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Library version
if ( ! defined( 'CFGP_LIBRARY_VERSION' ) ) define( 'CFGP_LIBRARY_VERSION', '1.0.0');

// Database version
if ( ! defined( 'CFGP_DATABASE_VERSION' ) ) define( 'CFGP_DATABASE_VERSION', '1.0.0');

// Globals
global $cfgp_version;

/*
 * Main plugin constants
 */
$CFGEO = array();

// Main plugin file
if ( ! defined( 'CFGP_FILE' ) ) define( 'CFGP_FILE', __FILE__ );

/*
 * Require plugin general setup
 */
include_once __DIR__ . '/constants.php';

/*
 * Requirements
 */
include_once CFGP_CLASS . '/Requirements.php';

/*
 * Check requiremant
 */
$CFGP_Requirements = new CFGP_Requirements(array('file' => CFGP_FILE));
if($CFGP_Requirements->passes()) :
	// Dynamic action
	do_action('cfgp/before_plugin_setup');
	// Initializing class
	include_once CFGP_INC . '/Init.php';
	// Register database tables
	CFGP_Init::wpdb_tables();
	// Include dependencies
	CFGP_Init::dependencies();
	// Plugin activation
	CFGP_Init::activation();
	// Plugin deactivation
	CFGP_Init::deactivation();
	// Run plugin
	CFGP_Init::run();
	// Run plugin debug
	CFGP_Init::debug();
	// Dynamic action
	do_action('cfgp/after_plugin_setup');
endif;