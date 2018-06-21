<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    4.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cf-geoplugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
