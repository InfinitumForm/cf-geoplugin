<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WordPress Geo Plugin
 * Plugin URI:        https://cfgeoplugin.com/
 * Description:       Create Dynamic Content, Banners and Images on Your Website Based On Visitor Geo Location By Using Shortcodes With CF Geo Plugin.
<<<<<<< HEAD
 * Version:           7.13.7
=======
 * Version:           8.0.0
>>>>>>> Version_8xx
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL-2.0+
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

<<<<<<< HEAD
// Find is localhost or not
if ( ! defined( 'CFGP_LOCAL' ) ) {
	if(isset($_SERVER['REMOTE_ADDR'])) {
		define('CFGP_LOCAL', in_array($_SERVER['REMOTE_ADDR'], array(
			'127.0.0.1',
			'::1',
			'localhost'
		)));
	} else {
		define('CFGP_LOCAL', false);
	}
}

/**
 * DEBUG MODE
 *
 * This is need for plugin debugging.
 */
if ( defined( 'WP_DEBUG' ) ) {
	if(WP_DEBUG === true || WP_DEBUG === 1)
	{
		if ( ! defined( 'WP_CF_GEO_DEBUG' ) ) define( 'WP_CF_GEO_DEBUG', true );
	}
}
=======
// Library version
if ( ! defined( 'CFGP_LIBRARY_VERSION' ) ) define( 'CFGP_LIBRARY_VERSION', '1.0.0');
>>>>>>> Version_8xx

// Database version
if ( ! defined( 'CFGP_DATABASE_VERSION' ) ) define( 'CFGP_DATABASE_VERSION', '1.0.0');

// Globals
global $cfgp_version;

/*
 * Main plugin constants
 */
<<<<<<< HEAD
$GLOBALS['CFGEO_API_CALL'] = apply_filters( 'cf_geoplugin_api_calls', array(
	// Standard CF Geo Plugin API URLs
	'main'			=>	'http://cdn-cfgeoplugin.com.dedi1855.your-server.de/index.php',
	'dns'			=>	'http://cdn-cfgeoplugin.com.dedi1855.your-server.de/dns.php',
	'authenticate'	=>	'http://cdn-cfgeoplugin.com.dedi1855.your-server.de/authenticate.php',
	'spam-checker'	=>	'http://cdn-cfgeoplugin.com.dedi1855.your-server.de/spam-checker.php',
	'converter'		=>	'http://cdn-cfgeoplugin.com.dedi1855.your-server.de/convert.php',
	// 3rd party Covid-19 free API call
	'covid-api'		=>	'https://api.covid19api.com',
	// 3rd party IPFY free API call for finding real IP address on the local machines
	'ipfy'			=>	'https://api.ipify.org',
	'smartIP'		=>	'https://smart-ip.net/myip',
	'indent'		=>	'https://ident.me'
));
=======
$CFGEO = array();
>>>>>>> Version_8xx

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

<<<<<<< HEAD
	// Add privacy policy
	add_action( 'admin_init', 'cf_geoplugin_privacy_policy' );
	
	// We  must close session but first we must also collect data
	add_action('wp_loaded', function(){
		session_write_close();
	}, 99999);
=======
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
>>>>>>> Version_8xx
endif;