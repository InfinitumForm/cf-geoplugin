<?php

/**
 * Fired during plugin deactivation
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_Deactivator {

	/**
	 * Deactivate Plugin
	 *
	 * @since    4.0.0
	 */
	public static function deactivate() {
		session_destroy();
		wp_clear_scheduled_hook('cf_geo_validate');
	}

}
