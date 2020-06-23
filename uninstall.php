<?php
/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link      http://cfgeoplugin.com/
 * @since     7.0.0
 * @package   CF_Geoplugin
 * @author    Goran Zivkovic 
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wp_version, $wpdb;

/**
* Alias of get_terms() functionality for lower versions of wordpress
* @link      https://developer.wordpress.org/reference/functions/get_terms/
* @version   1.0.0
*/
function cf_geo_get_terms( $args = array(), $deprecated = '' ) 
{ 
	$term_query = new WP_Term_Query();
 
	/*
	 * Legacy argument format ($taxonomy, $args) takes precedence.
	 *
	 * We detect legacy argument format by checking if
	 * (a) a second non-empty parameter is passed, or
	 * (b) the first parameter shares no keys with the default array (ie, it's a list of taxonomies)
	 */
	$_args = wp_parse_args( $args );
	$key_intersect  = array_intersect_key( $term_query->query_var_defaults, (array) $_args );
	$do_legacy_args = $deprecated || empty( $key_intersect );
 
	if ( $do_legacy_args ) {
		$taxonomies = (array) $args;
		$args = wp_parse_args( $deprecated );
		$args['taxonomy'] = $taxonomies;
	} else {
		$args = wp_parse_args( $args );
		if ( isset( $args['taxonomy'] ) && null !== $args['taxonomy'] ) {
			$args['taxonomy'] = (array) $args['taxonomy'];
		}
	}
 
	if ( ! empty( $args['taxonomy'] ) ) {
		foreach ( $args['taxonomy'] as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.' ) );
			}
		}
	}
 
	$terms = $term_query->query( $args );
 
	// Count queries are not filtered, for legacy reasons.
	if ( ! is_array( $terms ) ) {
		return $terms;
	}
 
	/**
	 * Filters the found terms.
	 *
	 * @since 2.3.0
	 * @since 4.6.0 Added the `$term_query` parameter.
	 *
	 * @param array         $terms      Array of found terms.
	 * @param array         $taxonomies An array of taxonomies.
	 * @param array         $args       An array of cf_geo_get_terms() arguments.
	 * @param WP_Term_Query $term_query The WP_Term_Query object.
	 */
	return apply_filters( 'get_terms', $terms, $term_query->query_vars['taxonomy'], $term_query->query_vars, $term_query );
}

// Destroy options
if( !function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if( !is_plugin_active_for_network( plugins_url( 'cf-geoplugin.php', __FILE__ ) ) )
{
	delete_option( 'cf_geoplugin' );
	delete_option( 'cf_geoplugin_dismissed_notices' );
	delete_option( 'woocommerce_cf_geoplugin_conversion' );
	delete_option( 'woocommerce_cf_geoplugin_conversion_rounded' );
	delete_option( 'woocommerce_cf_geoplugin_conversion_rounded_option' );
	delete_option( 'woocommerce_cf_geoplugin_conversion_adjust' );
	delete_option( 'woocommerce_cf_geoplugin_conversion_in_admin' );
}
else
{
	$blog_ids = get_sites();
	$current_blog = get_current_blog_id();
	foreach( $blog_ids as $b )
	{
		switch_to_blog( $b->blog_id );		
		
		if(function_exists('delete_site_option'))
		{
			delete_site_option( 'cf_geoplugin' );
			delete_site_option( 'cf_geoplugin_dismissed_notices' );
			delete_site_option( 'woocommerce_cf_geoplugin_conversion' );
			delete_site_option( 'woocommerce_cf_geoplugin_conversion_rounded' );
			delete_site_option( 'woocommerce_cf_geoplugin_conversion_rounded_option' );
			delete_site_option( 'woocommerce_cf_geoplugin_conversion_adjust' );
			delete_site_option( 'woocommerce_cf_geoplugin_conversion_in_admin' );
		}
		
		if(function_exists('delete_blog_option'))
		{
			delete_blog_option( $b->blog_id, 'cf_geoplugin' );
			delete_blog_option( $b->blog_id, 'cf_geoplugin_dismissed_notices' );
			delete_blog_option( $b->blog_id, 'woocommerce_cf_geoplugin_conversion' );
			delete_blog_option( $b->blog_id, 'woocommerce_cf_geoplugin_conversion_rounded' );
			delete_blog_option( $b->blog_id, 'woocommerce_cf_geoplugin_conversion_rounded_option' );
			delete_blog_option( $b->blog_id, 'woocommerce_cf_geoplugin_conversion_adjust' );
			delete_blog_option( $b->blog_id, 'woocommerce_cf_geoplugin_conversion_in_admin' );
		}
	}
	switch_to_blog( $current_blog );
}

// Geo Banner data
$args = array(
	'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
	'post_type'  =>  'cf-geoplugin-banner',
	'offset' => 0,
	'posts_per_page' => -1
);
$posts = get_posts( $args );
if( !empty( $posts ) && is_array( $posts ) )
{
	foreach( $posts as $i => $post )
	{
		delete_post_meta( $post->ID, 'cf-geoplugin-country' );
		delete_post_meta( $post->ID, 'cf-geoplugin-region' );
		delete_post_meta( $post->ID, 'cf-geoplugin-city' );
		delete_post_meta( $post->ID, 'cf-geoplugin-redirect_url' );
		delete_post_meta( $post->ID, 'cf-geoplugin-http_code' );
		delete_post_meta( $post->ID, 'cf-geoplugin-seo_redirect' );
		wp_delete_post( $post->ID, true );
	}
}

// Taxonomy list
$taxonomy_list = array(
    'cf-geoplugin-country',
	'cf-geoplugin-region',
	'cf-geoplugin-city'
);

foreach($taxonomy_list as $i => $taxonomy)
{
    if ( version_compare( $wp_version, '4.6', '>=' ) )
    {
        $terms = get_terms(array(
            'taxonomy'		=> $taxonomy,
            'hide_empty'	=> false
        ));
    }
    else
    {
        $terms = cf_geo_get_terms(array(
            'taxonomy'		=> $taxonomy,
            'hide_empty'	=> false
        ));
    }
	if ( is_array( $terms ) && !empty( $terms ) ){
		foreach ( $terms as $i => $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
    }
}

// Delete table from database
$table_names = array(
	'cf_geo_seo_redirection',
	'cf_geo_rest_secret',
	'cf_geo_rest_token',
);
foreach( $table_names as $i => $name ) $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$name};" );

// Delete redirection data
$meta_keys = array(
	'cf_geo_metabox_redirection',
	'cf_geo_metabox_country',
	'cf_geo_metabox_region',
	'cf_geo_metabox_city',
	'cf_geo_metabox_redirect_url',
	'cf_geo_metabox_http_code',
	'cf_geo_metabox_seo_redirection'
);
foreach( $meta_keys as $i => $key )
{
	delete_post_meta_by_key( $key );
}