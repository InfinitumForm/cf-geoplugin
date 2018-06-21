<?php

/**
 * Fired during plugin activation
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_Activator {
	/* Prepare all setup */
	private static $setup = array(
		'cf_geo_enable_seo_redirection'	=>	'true',
		'cf_geo_enable_flag'			=>	'true',
		'cf_geo_enable_defender'		=>	'true',
		'cf_geo_enable_gmap'			=>	'false',
		'cf_geo_enable_banner'			=>	'true',
		'cf_geo_enable_cloudflare'		=>	'false',
		'cf_geo_enable_dns_lookup'		=>	'false',
		'cf_geo_enable_proxy_ip'		=>	'',
		'cf_geo_enable_proxy_port'		=>	'',
		'cf_geo_enable_proxy'			=>	'false',
		'cf_geo_enable_proxy_username'	=>	'',
		'cf_geo_enable_proxy_password'	=>	'',
		'cf_geo_enable_ssl'				=>	'false',
		'cf_geo_connection_timeout'		=>	'9',
		'cf_geo_timeout'				=>	'9',
		'cf_geo_map_zoom'				=>	'8',
		'cf_geo_map_scrollwheel'		=>	'1',
		'cf_geo_map_navigationControl'	=>	'1',
		'cf_geo_map_scaleControl'		=>	'1',
		'cf_geo_map_mapTypeControl'		=>	'1',
		'cf_geo_map_draggable'			=>	'0',
		'cf_geo_map_width'				=>	'100%',
		'cf_geo_map_height'				=>	'400px',
		'cf_geo_map_infoMaxWidth'		=>	'200',
		'cf_geo_map_latitude'			=>	'',
		'cf_geo_map_longitude'			=>	'',
		'cf_geo_license_key'			=>	'',
		'cf_geo_license_id'				=>	'',
		'cf_geo_license_expire'			=>	'',
		'cf_geo_license_expire_date'	=>	'',
		'cf_geo_license_url'			=>	'',
		'cf_geo_license_expired'		=>	'',
		'cf_geo_license_status'			=>	'',
		'cf_geo_license'				=>	'0',
		'cf_geo_store'					=>	'https://cfgeoplugin.com',
		'cf_geo_store_code'				=>	'YR5pv3FU8l78v3N'
	);
	/**
	 * First setup for plugin
	 *
	 * @since    4.0.0
	 */
	public static function activate() {
		foreach(self::$setup as $name => $default)
		{
			$check = get_option($name);
			if(empty($check) || is_null($check)) 
			{
				update_option($name, esc_attr($default));
			}
		}
		
		if (! wp_next_scheduled ( 'cf_geo_validate' )) {
			wp_schedule_event(time(), 'twicedaily', 'cf_geo_validate');
		}
	}

}
