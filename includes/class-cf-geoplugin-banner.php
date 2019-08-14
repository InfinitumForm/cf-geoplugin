<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Taxonomies, CPT and shortcodes.
 * 
 * @since       7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( ! class_exists( 'CF_Geoplugin_Banner' ) ) :
Class CF_Geoplugin_Banner extends CF_Geoplugin_Global
{    
    private $taxonomy = false;

    // Construct all
    function __construct()
    {
        $this->add_action( 'init', 'register_banner' );
        $this->add_action( 'init', 'register_baner_taxonomy' );
		$this->add_filter('manage_posts_columns', 'columns_banner_head');
		$this->add_action('manage_posts_custom_column', 'columns_banner_content', 10, 2);
    }
	
	/**
	 * Banner Head
	 *
	 * @since    4.0.0
	 */
	public function columns_banner_head($column_name) {
		$url=$this->url();
		$url=strtolower($url->url);
		if(strpos($url,'post_type=cf-geoplugin-banner')!==false)
		{
			$column_name['cf_geo_banner_shortcode'] = __('Shortcode',CFGP_NAME);
			$column_name['cf_geo_banner_locations'] = __('Locations',CFGP_NAME);
		}
		return $column_name;
	}
	
	/**
	 * Banner head content
	 *
	 * @since    4.0.0
	 */
	public function columns_banner_content($column_name, $post_ID) {
		$url=$this->url();
			$url=strtolower($url->url);
		if(strpos($url,'post_type=cf-geoplugin-banner')!==false)
		{
			if ($column_name == 'cf_geo_banner_shortcode')
			{
				echo '<ul>';
				echo '<li><strong>' . __('Standard',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post_ID.'"]</code></li>';
				echo '<li><strong>' . __('Advanced',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post_ID.'"]' . __('Default content',CFGP_NAME) . '[/cfgeo_banner]</code></li>';
				echo '</ul>';
			}
			else if ($column_name == 'cf_geo_banner_locations')
			{
				// get all taxonomies
				$taxonomy_list = array(
					__('Countries',CFGP_NAME)	=>	'cf-geoplugin-country',
					__('Regions',CFGP_NAME)		=>	'cf-geoplugin-region',
					__('Cities',CFGP_NAME)		=>	'cf-geoplugin-city'
				);
				$print=array();
				// list taxonomies
				foreach($taxonomy_list as $name=>$taxonomy)
				{
					// list all terms
					$all_terms = wp_get_post_terms($post_ID, $taxonomy, array("fields" => "all"));
					$part=array();
					foreach($all_terms as $i=>$fetch)
					{
						$edit_link = esc_url( get_edit_term_link( $fetch->term_id, $taxonomy, 'cf-geoplugin-banner' ) );
						$part[]='<a href="'.$edit_link.'">'.$fetch->name.' ('.$fetch->description.')</a>';
					}
					if(count($part)>0)
					{
						$print[]='<li><strong>'.$name.':</strong><br>';
							$print[]=join(",<br>",$part);
						$print[]='<li>';
					}
				}
				// print terms
				if(count($print)>0)
				{
					echo '<ul>'.join("\r\n",$print).'</ul>';
				}
				else
				{
					echo '( ' . __('undefined',CFGP_NAME) . ' )';
				}
			}
		}
	}

    // Register banner CPT
    public function register_banner()
    {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

		$enable_banner = $CF_GEOPLUGIN_OPTIONS['enable_banner'];

		$projects   = array(
			'labels'				=> array(
				'name'               		=> __( 'Geo Banner',CFGP_NAME ),
				'singular_name'      		=> __( 'Geo Banner',CFGP_NAME ),
				'add_new'            		=> __( 'Add New Banner',CFGP_NAME),
				'add_new_item'       		=> __( "Add New Banner",CFGP_NAME),
				'edit_item'          		=> __( "Edit Banner",CFGP_NAME),
				'new_item'           		=> __( "New Banner",CFGP_NAME),
				'view_item'          		=> __( "View Banner",CFGP_NAME),
				'search_items'       		=> __( "Search Banner",CFGP_NAME),
				'not_found'          		=> __( 'No Banner Found',CFGP_NAME),
				'not_found_in_trash' 		=> __( 'No Banner Found in Trash',CFGP_NAME),
				'parent_item_colon'  		=> '',
				'featured_image'	 		=> __('Banner Image',CFGP_NAME),
				'set_featured_image'		=> __('Select Banner Image',CFGP_NAME),
				'remove_featured_image'		=> __('Remove Banner Image',CFGP_NAME),
				'use_featured_image'		=> __('Use Banner Image',CFGP_NAME),
				'insert_into_item'			=> __('Insert Into Banner',CFGP_NAME)
			),
			'public'            	=> true,
			'exclude_from_search'	=> true,
			'publicly_queryable'	=> false, 
			'show_in_nav_menus'   	=> false,
			'show_ui'           	=> $enable_banner ? true : false,
			'query_var'         	=> true,
			'rewrite'           	=> array( 'slug' => 'banner' ),
			'hierarchical'      	=> false,
			'menu_position'     	=> 100,
			'capability_type'   	=> "post",
			'supports'          	=> array( 'title', 'editor', /*'thumbnail',*/ 'tags' ),
		//	'menu_icon'         	=> plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/cf-geo-banner-25x25.png',
			'menu_icon' 			=> 'dashicons-pressthis',
			'show_in_menu'			=> false
		);
		register_post_type( 'cf-geoplugin-banner', $projects );
    }

    // Register banner taxonomies
    public function register_baner_taxonomy()
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
            'region-country-list'       => CF_Geplugin_Library::COUNTRY_REGION_LIST,
            'region-list'               => CF_Geplugin_Library::CONTINENT_LIST,
            'country-list'              => CF_Geplugin_Library::COUNTRY_LIST
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
			$all_terms = $this->cf_geo_get_terms(array(
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
    public function cf_geo_get_terms( $args = array(), $deprecated = '' ) 
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
}
endif;