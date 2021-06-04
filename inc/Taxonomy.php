<?php
/**
 * Register custom taxonomy
 *
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Taxonomy')) :
class CFGP_Taxonomy extends CFGP_Global {
	
	function __construct(){
		$this->add_action( 'registered_taxonomy', 'register' );
		$this->add_action( 'plugins_loaded', 'load' );
		$this->add_action( 'nav_menu_meta_box_object', 'fix_menu_metaboxes' );
	}
	
	public function fix_menu_metaboxes( $tax ){
		if ( $tax->name === 'cf-geoplugin-country' ) {
			$tax->labels->name = "Product Categories";
		}
		CFGP_U::dump($tax->labels);
		return $tax;
	}
	
	public function register(){
		if(!taxonomy_exists( 'cf-geoplugin-country' ))
		{
			register_taxonomy(
				'cf-geoplugin-country', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Countries',CFGP_NAME),
						'singular_name' 		=> __('Country',CFGP_NAME),
						'menu_name' 			=> __('Countries',CFGP_NAME),
						'all_items' 			=> __('All Countries',CFGP_NAME),
						'edit_item' 			=> __('Edit Country',CFGP_NAME),
						'view_item' 			=> __('View Country',CFGP_NAME),
						'update_item' 			=> __('Update Country',CFGP_NAME),
						'add_new_item' 			=> __('Add New Country',CFGP_NAME),
						'new_item_name' 		=> __('New Country Name',CFGP_NAME),
						'parent_item' 			=> __('Parent Country',CFGP_NAME),
						'parent_item_colon' 	=> __('Parent Country',CFGP_NAME),
					),
					'hierarchical'		=> true,
					'show_ui'			=> true,
					'public'		 	=> false,
					'label'          	=> __('Countries',CFGP_NAME),
					'singular_label' 	=> __('Country',CFGP_NAME),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false
				)
			);
		}
		
		if(!taxonomy_exists( 'cf-geoplugin-region' ))
		{
			register_taxonomy(
				'cf-geoplugin-region', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Regions',CFGP_NAME),
						'singular_name' 		=> __('Region',CFGP_NAME),
						'menu_name' 			=> __('Regions',CFGP_NAME),
						'all_items' 			=> __('All Regions',CFGP_NAME),
						'edit_item' 			=> __('Edit Region',CFGP_NAME),
						'view_item' 			=> __('View Region',CFGP_NAME),
						'update_item' 			=> __('Update Region',CFGP_NAME),
						'add_new_item' 			=> __('Add New Region',CFGP_NAME),
						'new_item_name' 		=> __('New Region Name',CFGP_NAME),
						'parent_item' 			=> __('Parent Region',CFGP_NAME),
						'parent_item_colon' 	=> __('Parent Region',CFGP_NAME),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> true,
					'public'		 	=> false,
					'label'          	=> __('Regions',CFGP_NAME),
					'singular_label' 	=> __('Region',CFGP_NAME),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false
				)
			);
		}
		
		
		if(!taxonomy_exists( 'cf-geoplugin-city' ))
		{
			register_taxonomy(
				'cf-geoplugin-city', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Cities',CFGP_NAME),
						'singular_name' 		=> __('City',CFGP_NAME),
						'menu_name' 			=> __('Cities',CFGP_NAME),
						'all_items' 			=> __('All Cities',CFGP_NAME),
						'edit_item' 			=> __('Edit City',CFGP_NAME),
						'view_item' 			=> __('View City',CFGP_NAME),
						'update_item' 			=> __('Update City',CFGP_NAME),
						'add_new_item' 			=> __('Add New City',CFGP_NAME),
						'new_item_name' 		=> __('New City Name',CFGP_NAME),
						'parent_item' 			=> __('Parent City',CFGP_NAME),
						'parent_item_colon' 	=> __('Parent City',CFGP_NAME),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> true,
					'public'		 	=> false,
					'label'          	=> __('Cities',CFGP_NAME),
					'singular_label' 	=> __('City',CFGP_NAME),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false
				)
			);
		}
		
		
		if(!taxonomy_exists( 'cf-geoplugin-postcode' ))
		{
			register_taxonomy(
				'cf-geoplugin-postcode', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Postcodes',CFGP_NAME),
						'singular_name' 		=> __('Postcode',CFGP_NAME),
						'menu_name' 			=> __('Postcodes',CFGP_NAME),
						'all_items' 			=> __('All Postcodes',CFGP_NAME),
						'edit_item' 			=> __('Edit Postcode',CFGP_NAME),
						'view_item' 			=> __('View Postcode',CFGP_NAME),
						'update_item' 			=> __('Update Postcode',CFGP_NAME),
						'add_new_item' 			=> __('Add New Postcode',CFGP_NAME),
						'new_item_name' 		=> __('New Postcode Name',CFGP_NAME),
						'parent_item' 			=> __('Parent Postcode',CFGP_NAME),
						'parent_item_colon' 	=> __('Parent Postcode',CFGP_NAME),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> true,
					'public'		 	=> false,
					'label'          	=> __('Postcodes',CFGP_NAME),
					'singular_label' 	=> __('Postcode',CFGP_NAME),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false
				)
			);
		}
	}
	
	// Load data into plugin
	public function load()
	{
		$this->install_country_terms( 'country-list', 'cf-geoplugin-country' );
	}
	
	// Install info about countries into DB
    public function install_country_terms( $option = false, $taxonomy = false )
    {
		global $wp_version;
        if( $option === false || ! in_array( $option, array('region-country-list','region-list','country-list') ) )
        {
            return;
        }

        // Set installation list
        $set = array(
            'region-country-list'       => CFGP_Defaults::COUNTRY_REGION_LIST,
            'region-list'               => CFGP_Defaults::CONTINENT_LIST,
            'country-list'              => CFGP_Defaults::COUNTRY_LIST
        );

        // Set taxonomy or use global
        if( $taxonomy !== false ) $this->taxonomy = $taxonomy;

		if ( version_compare( $wp_version, '4.6', '>=' ) )
		{
			$all_terms = get_terms(array(
				'taxonomy'		=> $this->taxonomy,
				'hide_empty'	=> false
			));
		}
		else
		{
			$all_terms = $this->get_terms(array(
				'taxonomy'		=> $this->taxonomy,
				'hide_empty'	=> false
			));
		}

        if( (is_array( $all_terms ) && count( $all_terms )>0 ) || empty( $this->taxonomy ) || $this->taxonomy === false )
        {
            return;
        }

        $return = array();

        foreach( $set[ $option ] as $region => $array )
        {
            // Set parent
            if( in_array( $option, array( 'region_list', 'country_list' ) ) !== false ) $arg['description'] = $array;

            if( is_string( $array ) )
            {
                $term = wp_insert_term( $region, $this->taxonomy, array( 'description' => $array ) );
            }
            else
            {
                $term = wp_insert_term( $region, $this->taxonomy);
            }

            $return[ $region ] = $term;

            // Set child
            if( is_array( $array ) && count( $array ) > 0 && is_array( $term ) )
            {
                foreach( $array as $code => $country )
                {
                    $return[ $country ] = wp_insert_term(
                        $country,
                        $this->taxonomy,
                        array(
                            'parent'        => $term['term_id'],
                            'description'   => $code
                        )
                    );
                }
            }
        }
        return $return;
    }
	
	/**
    * Alias of get_terms() functionality for lower versions of wordpress
    * @link      https://developer.wordpress.org/reference/functions/get_terms/
    * @version   1.0.0
    */
    public function get_terms( $args = array(), $deprecated = '' ) 
    {
		global $wpdb;
	 
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
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		global $cfgp_cache;
		$class = self::class;
		$instance = $cfgp_cache->get($class);
		if ( !$instance ) {
			$instance = $cfgp_cache->set($class, new self());
		}
		return $instance;
	}
}
endif;