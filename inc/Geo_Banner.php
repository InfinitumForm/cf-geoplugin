<?php
/**
 * Register custom post type
 *
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Geo_Banner')) :
class CFGP_Geo_Banner extends CFGP_Global {
	
	function __construct(){
		$this->add_action('registered_post_type', 'register');
		$this->add_filter('manage_posts_columns', 'columns_banner');
		$this->add_action('manage_posts_custom_column', 'columns_banner_content', 10, 2);
		$this->add_action('delete_post', 'delete_post', 10);
		$this->add_action('add_meta_boxes', 'add_meta_boxes', 1);
	}
	
	/**
     * Register post type
     */
	public function register(){
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
			'show_ui'           	=> (CFGP_Options::get('enable_banner', 1) ? true : false),
			'query_var'         	=> true,
			'hierarchical'      	=> false,
			'menu_position'     	=> 20,
			'capability_type'   	=> 'post',
			'supports'          	=> array( 'title', 'editor', 'tags' ),
			'menu_icon' 			=> 'dashicons-pressthis',
			'show_in_menu'			=> false
		);
		
		if(!post_type_exists('cf-geoplugin-banner')) {
			register_post_type( 'cf-geoplugin-banner', $projects );
			flush_rewrite_rules();
		}
	}
	
	/**
	 * Hook for the post delete
	 */
	public function delete_post($id){
		// Remove cookie if they exists
		if(isset($_COOKIE) && !empty($_COOKIE))
		{
			$cookie_name = '__cfgp_seo_' . $id . '_once_';
			foreach($_COOKIE as $key => $value)
			{
				if(strpos($key, $cookie_name) !== false)
				{
					CFGP_U::setcookie($key, time() . '', (time()-((365 * DAY_IN_SECONDS) * 2)));
					unset($_COOKIE[$key]);
				}
			}
		}
	}
	
	/**
	 * Banner Head
	 *
	 * @since    4.0.0
	 */
	public function columns_banner($column_name) {
		$url=CFGP_U::parse_url();
		if(strpos($url['url'],'post_type=cf-geoplugin-banner')!==false)
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
		$url=CFGP_U::parse_url();
		if(strpos($url['url'],'post_type=cf-geoplugin-banner')!==false)
		{
			if ($column_name == 'cf_geo_banner_shortcode')
			{
				echo '<ul>';
				echo '<li><strong>' . __('Standard',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="' . $post_ID . '"]</code></li>';
				echo '<li><strong>' . __('Advanced',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="' . $post_ID . '"]' . __('Default content', CFGP_NAME) . '[/cfgeo_banner]</code></li>';
				echo '</ul>';
			}
			else if ($column_name == 'cf_geo_banner_locations')
			{
				// get all taxonomies
				$taxonomy_list = array(
					__('Countries',CFGP_NAME)	=>	'cf-geoplugin-country',
					__('Regions',CFGP_NAME)		=>	'cf-geoplugin-region',
					__('Cities',CFGP_NAME)		=>	'cf-geoplugin-city',
					__('Postcodes',CFGP_NAME)	=>	'cf-geoplugin-postcode'
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
						$edit_link = get_edit_term_link( $fetch->term_id, $taxonomy, 'cf-geoplugin-banner' );
						$part[]='<a href="' . esc_url($edit_link) . '">' . $fetch->name . ( !empty($fetch->description) ? ' (' . $fetch->description . ')' : NULL ) . '</a>';
					}
					if(count($part)>0)
					{
						$print[]='<li><strong>' . $name . ':</strong><br>';
							$print[]=join(",<br>", $part);
						$print[]='<li>';
					}
				}
				// print terms
				if(count($print)>0)
				{
					echo '<ul>'.join("\r\n", $print).'</ul>';
				}
				else
				{
					echo '( ' . __('undefined',CFGP_NAME) . ' )';
				}
			}
		}
	}
	
	/**
     * Add meta boxes
     */
	public function add_meta_boxes(){
		$screen = get_current_screen();
		if(isset( $screen->post_type ) && $screen->post_type === 'cf-geoplugin-banner'){
			$this->add_meta_box(
				CFGP_NAME . '-banner-sc',								// Unique ID
				__( 'Geo Banner Shortcode', CFGP_NAME ),				// Box title
				'add_meta_box__shortcode',								// Content callback, must be of type callable
				'cf-geoplugin-banner',									// Post type
				'advanced',
				'high'
			);
		}
		
		return;
	}
	
	/**
     * Geo banner shortcode metabox
     */
    public function add_meta_box__shortcode( $post )
    {
        echo '<ul>';
        echo '<li><strong>' . __('Standard',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]</code></li>';
        echo '<li><strong>' . __('Advanced',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]' . __('Default content',CFGP_NAME) . '[/cfgeo_banner]</code></li>';
		echo '<li><strong>' . __('Enable Cache',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'" cache]' . __('Default content',CFGP_NAME) . '[/cfgeo_banner]</code></li>';
        echo '</ul>';
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