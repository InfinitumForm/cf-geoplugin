<?php
/**
 * Uninstall plugin and clean everything
 *
 * @link              http://infinitumform.com/
 * @package           CF_Geoplugin
 */
 
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Plugin name
if (!defined('CFGP_NAME')) define('CFGP_NAME', 'cf-geoplugin');

global $wpdb;

// Delete options
if(get_option(CFGP_NAME)) {
	delete_option(CFGP_NAME);
}
if(get_option(CFGP_NAME . '-ID')) {
	delete_option(CFGP_NAME . '-ID');
}
if(get_option(CFGP_NAME. '-activation')) {
	delete_option(CFGP_NAME . '-activation');
}
if(get_option(CFGP_NAME . '-deactivation')) {
	delete_option(CFGP_NAME . '-deactivation');
}
if(get_option(CFGP_NAME . '-license')) {
	delete_option(CFGP_NAME . '-license');
}
if(get_option(CFGP_NAME . '-rest')) {
	delete_option(CFGP_NAME . '-rest');
}
if(get_option(CFGP_NAME . '-db-version')) {
	delete_option(CFGP_NAME . '-db-version');
}
if(get_option(CFGP_NAME . '-reviewed')) {
	delete_option(CFGP_NAME . '-reviewed');
}

// Delete MySQL tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cfgp_rest_access_token" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cfgp_seo_redirection" );

// Plugin statistic - remove
$statistic = rtrim(plugin_dir_path(__FILE__), '/') . '/inc/classes/Statistic.php';
if( file_exists($statistic) ) {
	include_once $statistic;
	CFGP_Anonymous_Statistic::uninstall();
}

// Remove plugins cache
if ( is_multisite() && is_main_site() && is_main_network() ) {
	$wpdb->query("DELETE FROM
		`{$wpdb->sitemeta}`
	WHERE (
			`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_cfgp-%'
		OR
			`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_cfgp-%'
		OR
			`{$wpdb->sitemeta}`.`option_name` LIKE 'woocommerce_cfgp_method_%'
		OR
			`{$wpdb->sitemeta}`.`option_name` LIKE 'woocommerce_cf_geoplugin_%'
	)");
	} else {
	$wpdb->query("DELETE FROM
		`{$wpdb->options}`
		WHERE (
				`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_cfgp-%'
			OR
				`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_timeout_cfgp-%'
			OR
				`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_cfgp-%'
			OR
				`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_cfgp-%'
			OR
				`{$wpdb->sitemeta}`.`option_name` LIKE 'woocommerce_cfgp_method_%'
			OR
				`{$wpdb->sitemeta}`.`option_name` LIKE 'woocommerce_cf_geoplugin_%'
	)");
}