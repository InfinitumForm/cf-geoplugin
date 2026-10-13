<?php

/**
 * @wordpress-plugin
 *
 * Geo Controller
 *
 * Plugin Name:       Geo Controller
 * Plugin URI:        https://wpgeocontroller.com/
 * Description:       Unlock the power of location-based functionality of WordPress - the ultimate all-in-one geolocation plugin for WordPress.
 * Version:           9.0.3
 * Requires at least: 6.0
 * Requires PHP:      7.0
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
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
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

// Start calculate runtime
if (!defined('CFGP_START_RUNTIME')) {
    define('CFGP_START_RUNTIME', microtime());
}

// Library version
if (!defined('CFGP_LIBRARY_VERSION')) {
    define('CFGP_LIBRARY_VERSION', '1.0.1');
}

// Database version
if (!defined('CFGP_DATABASE_VERSION')) {
    define('CFGP_DATABASE_VERSION', '1.0.3');
}
// Globals
global $cfgp_version;

/*
 * Main plugin constants
 */
$CFGEO = [];

// Main plugin file
if (!defined('CFGP_FILE')) {
    define('CFGP_FILE', __FILE__);
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
$CFGP_Requirements = new CFGP_Requirements(['file' => CFGP_FILE]);

if ($CFGP_Requirements->passes()) :
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


/*!
 * HEY, DEVELOPER! 👋
 *
 * Looking to extend Geo Controller or integrate it into your PHP code?
 * You're in the right place.
 *
 * YOUR STARTING POINT:
 * /inc/classes/Utilities.php
 *
 * Meet CFGP_U — your go-to utility class.
 *
 * Need geolocation data? It's just one call away:
 *
 *     CFGP_U::api();                 // Get all available geo data.
 *     CFGP_U::api('country_code');   // Get the visitor's country code.
 *     CFGP_U::api('city');           // Get the visitor's city.
 *
 * WANT TO EXPLORE MORE?
 *
 * Take a look inside /inc/classes/ — you'll find the plugin's
 * core classes, organized by purpose.
 *
 * Geo Controller also provides WordPress actions and filters,
 * so you can extend its behavior without modifying core files.
 *
 * DOCUMENTATION:
 * https://wpgeocontroller.com/documentation/advanced-usage/php-integration
 *
 * Found something worth improving? Want to contribute to our docs?
 * We'd love to hear from you:
 *
 * wpgeocontroller@gmail.com
 *
 * Build something awesome. Happy coding!
 *
 * — The Geo Controller Team
 */

