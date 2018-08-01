<?php
/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link      http://cfgeoplugin.com/
 * @since     7.0.0
 * @package   CF_Geoplugin
 * @author    Ivijan-Stefan Stipic 
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
delete_option( 'cf_geoplugin' );
delete_option( 'cf_geoplugin_dismissed_notices' );

// Geo Banner data
$posts = get_posts( array( 'post_type'  =>  'cf-geoplugin-banner' ) );
foreach( $posts as $post )
{
	wp_delete_post( $post->ID, true );
}

$taxonomy_list = array(
    'cf-geoplugin-country',
	'cf-geoplugin-region',
	'cf-geoplugin-city'
);

foreach($taxonomy_list as $taxonomy)
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
	if ( is_array($terms) && count($terms) > 0 ){
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
    }
}

// Delete table from database
$table_name = $wpdb->prefix . 'cf_geo_seo_redirection';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );