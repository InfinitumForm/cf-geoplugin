<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

include_once plugin_dir_path(__FILE__).'admin/includes/cf-geoplugin-transitions.php';

//session_destroy();
$delete = array(
	'cf_geo_enable_seo_redirection'	=>	'',
	'cf_geo_enable_flag'			=>	'',
	'cf_geo_enable_defender'		=>	'',
	'cf_geo_enable_gmap'			=>	'',
	'cf_geo_enable_banner'			=>	'',
	'cf_geo_enable_cloudflare'		=>	'',
	'cf_geo_enable_dns_lookup'		=>	'',
	'cf_geo_enable_proxy_ip'		=>	'',
	'cf_geo_enable_proxy_port'		=>	'',
	'cf_geo_enable_proxy'			=>	'',
	'cf_geo_enable_proxy_username'	=>	'',
	'cf_geo_enable_proxy_password'	=>	'',
	'cf_geo_enable_ssl'				=>	'',
	'cf_geo_connection_timeout'		=>	'',
	'cf_geo_timeout'				=>	'',
	'cf_geo_map_zoom'				=>	'',
	'cf_geo_map_scrollwheel'		=>	'',
	'cf_geo_map_navigationControl'	=>	'',
	'cf_geo_map_scaleControl'		=>	'',
	'cf_geo_map_mapTypeControl'		=>	'',
	'cf_geo_map_draggable'			=>	'',
	'cf_geo_map_width'				=>	'',
	'cf_geo_map_height'				=>	'',
	'cf_geo_map_infoMaxWidth'		=>	'',
	'cf_geo_map_latitude'			=>	'',
	'cf_geo_map_longitude'			=>	'',
	'cf_geo_license_key'			=>	'',
	'cf_geo_license_id'				=>	'',
	'cf_geo_license_expire'			=>	'',
	'cf_geo_license_expire_date'	=>	'',
	'cf_geo_license_url'			=>	'',
	'cf_geo_license_expired'		=>	'',
	'cf_geo_license_status'			=>	'',
	'cf_geo_license_sku'			=>	'',
	'cf_geo_license'				=>	'',
	'cf_geo_store'					=>	'',
	'cf_geo_store_code'				=>	''
);
if(function_exists("delete_option"))
{
	foreach($delete as $name=>$v)
	{
		delete_option($name);
	}
}
// Delete terms

if(function_exists("get_pages"))
{
	$delete = get_pages( array( 'post_type' => 'cf-geoplugin-banner' ) );
	if(function_exists("wp_delete_post"))
	{
		foreach( $delete as $del ) {
			// Delete's each post.
			wp_delete_post( $del->ID, true);
			// Set to False if you want to send them to Trash.
		}
	}
}
// Delete Taxonomy
$taxonomy_list = array(
	'cf-geoplugin-country',
	'cf-geoplugin-region',
	'cf-geoplugin-city'
);

foreach($taxonomy_list as $taxonomy)
{
	$terms = cf_geo_get_terms(array(
		'taxonomy'		=> $taxonomy,
		'hide_empty'	=> false
	));
	if ( is_array($terms) && count($terms) > 0 ){
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
	}
}

wp_clear_scheduled_hook('cf_geo_validate');