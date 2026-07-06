<?php

/**
 * Uninstall plugin and clean everything
 *
 * @link              http://infinitumform.com/
 *
 * @package           CF_Geoplugin
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Plugin name
if (!defined('CFGP_NAME')) {
    define('CFGP_NAME', 'cf-geoplugin');
}

global $wpdb;

// Delete options
if (get_option(CFGP_NAME)) {
    delete_option(CFGP_NAME);
}

if (get_option(CFGP_NAME . '-ID')) {
    delete_option(CFGP_NAME . '-ID');
}

if (get_option(CFGP_NAME. '-activation')) {
    delete_option(CFGP_NAME . '-activation');
}

if (get_option(CFGP_NAME . '-deactivation')) {
    delete_option(CFGP_NAME . '-deactivation');
}

if (get_option(CFGP_NAME . '-license')) {
    delete_option(CFGP_NAME . '-license');
}

if (get_option(CFGP_NAME . '-rest')) {
    delete_option(CFGP_NAME . '-rest');
}

if (get_option(CFGP_NAME . '-version')) {
    delete_option(CFGP_NAME . '-version');
}

if (get_option(CFGP_NAME . '-db-version')) {
    delete_option(CFGP_NAME . '-db-version');
}

if (get_option(CFGP_NAME . '-library-version')) {
    delete_option(CFGP_NAME . '-library-version');
}

if (get_option(CFGP_NAME . '-reviewed')) {
    delete_option(CFGP_NAME . '-reviewed');
}

if (false !== get_option(CFGP_NAME . '-w3tc-dynamic-security-key', false)) {
    delete_option(CFGP_NAME . '-w3tc-dynamic-security-key');
}

if (get_option(CFGP_NAME . '-woo-transition')) {
    delete_option(CFGP_NAME . '-woo-transition');
}

if (get_option(CFGP_NAME . '-postcode_children')) {
    delete_option(CFGP_NAME . '-postcode_children');
}

if (get_option(CFGP_NAME . '_is_localhost_127.0.1.1')) {
    delete_option(CFGP_NAME . '_is_localhost_127.0.1.1');
}

if (get_option(CFGP_NAME . '_dimiss_notice_plugin_support')) {
    delete_option(CFGP_NAME . '_dimiss_notice_plugin_support');
}

// Fix WooCommerce after our plugin
if ('cf_geoplugin' === get_option('woocommerce_default_customer_address')) {
    update_option('woocommerce_default_customer_address', 'geolocation');
}

// Delete MySQL tables
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Expected plugin-owned custom table cleanup during uninstall.
$wpdb->query(sprintf('DROP TABLE IF EXISTS %scfgp_rest_access_token', $wpdb->prefix));
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Expected plugin-owned custom table cleanup during uninstall.
$wpdb->query(sprintf('DROP TABLE IF EXISTS %scfgp_seo_redirection', $wpdb->prefix));
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Expected plugin-owned custom table cleanup during uninstall.
$wpdb->query(sprintf('DROP TABLE IF EXISTS %scfgp_cache', $wpdb->prefix));

// Remove plugins cache
if (is_multisite() && is_main_site() && is_main_network()) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct uninstall cleanup query removes only plugin transient and settings rows from multisite metadata storage.
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
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct uninstall cleanup query removes only plugin transient and settings rows from options storage.
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
