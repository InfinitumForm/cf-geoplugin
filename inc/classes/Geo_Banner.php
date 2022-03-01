<?php
/**
 * Register custom post type
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Geo_Banner')) :
class CFGP_Geo_Banner extends CFGP_Global {
	
	function __construct(){
		$this->add_action('init', 'register');
		
		$this->add_filter('manage_posts_columns', 'columns_banner');
		$this->add_action('manage_posts_custom_column', 'columns_banner_content', 10, 2);
		$this->add_action('delete_post', 'delete_post', 10);
		$this->add_action('add_meta_boxes', 'add_meta_boxes', 1);
		$this->add_action('save_post', 'save_post');
		
		$this->add_action('wp_ajax_cf_geoplugin_banner_cache', 'ajax__geoplugin_banner_cache');
		$this->add_action('wp_ajax_nopriv_cf_geoplugin_banner_cache', 'ajax__geoplugin_banner_cache');
		
		$this->add_filter( 'single_template', 'add_custom_single_template', 20, 1 );
	}
	
	/* 
	 * Register elementor geo banner page
	 * @verson    1.0.0
	 */
	public function add_custom_single_template( $template ) {
		global $post;
		if( ($post->post_type === 'cf-geoplugin-banner') && file_exists(CFGP_PLUGINS . '/elementor/page/cfgp-banner.php') ){
			$template = CFGP_PLUGINS . '/elementor/page/cfgp-banner.php';
		}
		return $template;
	}
	
	/**
     * AJAX - Fix cache on cached websites
     */
	public function ajax__geoplugin_banner_cache(){
		global $wpdb;
		
		$setup = array(
			'id'				=>	CFGP_U::request_int('id'),
			'posts_per_page'	=>	CFGP_U::request_int('posts_per_page'),
			'class'				=>	sanitize_text_field(CFGP_U::request_string('class'))
		);
		
		$cont = urldecode(base64_decode(sanitize_text_field(CFGP_U::request_string('default'))));
		
		// Stop if ID is not good
		if( ! (intval($setup['id']) > 0) ) {
			return $cont;
		}
		
		// Reassign taxonomy to post meta
		foreach(array(
			'cf-geoplugin-country' => 'cfgp-banner-location-country',
			'cf-geoplugin-region' => 'cfgp-banner-location-region',
			'cf-geoplugin-city' => 'cfgp-banner-location-city'
		) as $get_post_terms=>$update_post_meta) {
			if($all_terms = wp_get_post_terms($setup['id'], $get_post_terms, array('fields' => 'all'))) {
				$tax_collection=array();
				foreach($all_terms as $i=>$fetch)
				{
					$tax_collection[]=$fetch->slug;
				}
				if( !empty($tax_collection) ) {
					update_post_meta($setup['id'], $update_post_meta, $tax_collection);
				} else {
					delete_post_meta($setup['id'], $update_post_meta);
				}
				wp_set_post_terms( $setup['id'], '', $get_post_terms );
				$tax_collection = NULL;
			}
		}
		
		$exact = CFGP_U::request_int('exact');
		
		$posts_per_page = absint($setup['posts_per_page']);
		
		$country = CFGP_U::api('country_code');
		$country_sql = '%"' . $wpdb->esc_like(esc_sql($country)) . '"%';
		
		$region = CFGP_U::api('region');
		$region_sql = '%"' . $wpdb->esc_like(esc_sql(sanitize_title( CFGP_U::transliterate($region) ))) . '"%';
		
		$city = CFGP_U::api('city');
		$city_sql = '%"' . $wpdb->esc_like(esc_sql(sanitize_title( CFGP_U::transliterate($city) ))) . '"%';

		$post = $wpdb->get_row( $wpdb->prepare("
SELECT
	`banner`.`ID`,
	`banner`.`post_title`,
	`banner`.`post_content`
FROM
	`{$wpdb->posts}` AS `banner`
WHERE
	`banner`.`ID` = %d
AND
	`banner`.`post_type` = 'cf-geoplugin-banner'
AND
	`post_status` = 'publish'
AND
	IF(
		EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country' AND `c`.`meta_value` LIKE %s),
        1
    )
AND
	IF(
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region' AND `r`.`meta_value` LIKE %s),
        1
    )
AND
	IF(
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city' AND `s`.`meta_value` LIKE %s),
        1
    )
LIMIT 1
		",
		absint($setup['id']),
		$country_sql,
		$region_sql,
		$city_sql
		) );
		
		$content = '';
		$save = NULL;
		
		if($post) {
			$post->post_content = do_shortcode($post->post_content);
			$post->post_content = CFGP_U::the_content($post->post_content);
			$save=$post->post_content;
		}
		
		// Return banner
		if(!empty($save)){
			$content = $save;
		}
		
		// Format defaults
		if(!empty($cont) && empty($content)) {
			$cont = do_shortcode($cont);
			$content = CFGP_U::the_content($cont);
		}
		
		echo $content; exit;
	}
	
	/**
     * Register post type
     */
	public function register(){
		
		$elementor_support = [];
		if( is_plugin_active('elementor/elementor.php') ) {
			if( $es_support = get_option('elementor_cpt_support') ) {
				$elementor_support = $es_support;
			}
		}
		
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
			'publicly_queryable'	=> in_array('cf-geoplugin-banner', $elementor_support), 
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
					CFGP_U::setcookie($key, CFGP_TIME . '', (CFGP_TIME-((365 * DAY_IN_SECONDS) * 2)));
					unset($_COOKIE[$key]);
				}
			}
		}
	}
	
	/**
	 * Hook for the post save/update
	 */
	public function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$post_type = get_post_type($post_id);
		
		if( $post_type !== 'cf-geoplugin-banner' ) {
			return;
		}
		
		update_post_meta( $post_id, 'cfgp-banner-default', wp_kses_post(CFGP_U::request('cfgp-banner-default-content', NULL)) );
		delete_post_meta( $post_id, CFGP_METABOX . 'banner_default' );
		
		if( $country = CFGP_Options::sanitize( CFGP_U::request('cfgp-banner-location-country', array()) ) ) {
			update_post_meta( $post_id, 'cfgp-banner-location-country', $country);
		} else {
			delete_post_meta( $post_id, 'cfgp-banner-location-country' );
		}
		
		if( $region = CFGP_Options::sanitize( CFGP_U::request('cfgp-banner-location-region', array()) ) ) {
			update_post_meta( $post_id, 'cfgp-banner-location-region', $region);
		} else {
			delete_post_meta( $post_id, 'cfgp-banner-location-region' );
		}
		
		if( $city = CFGP_Options::sanitize( CFGP_U::request('cfgp-banner-location-city', array()) ) ) {
			update_post_meta( $post_id, 'cfgp-banner-location-city', $city);
		} else {
			delete_post_meta( $post_id, 'cfgp-banner-location-city' );
		}
		
		wp_set_post_terms( $post_id, '', 'cf-geoplugin-country' );
		wp_set_post_terms( $post_id, '', 'cf-geoplugin-region' );
		wp_set_post_terms( $post_id, '', 'cf-geoplugin-city' );
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
				$print=array();
				
				foreach(array(
					__('Countries',CFGP_NAME)	=>	'country',
					__('Regions',CFGP_NAME)		=>	'region',
					__('Cities',CFGP_NAME)		=>	'city'
				) as $name=>$field)
				{
					$get_post_meta = get_post_meta($post_ID, "cfgp-banner-location-{$field}", true);
					
					if(!empty($get_post_meta))
					{
						$print[]='<li><strong>' . $name . ':</strong><br>';
							$print[]=join("<br>", $get_post_meta);
						$print[]='<li>';
					}
				}
				
				if(empty($print))
				{
					// list taxonomies
					foreach(array(
						__('Countries',CFGP_NAME)	=>	'cf-geoplugin-country',
						__('Regions',CFGP_NAME)		=>	'cf-geoplugin-region',
						__('Cities',CFGP_NAME)		=>	'cf-geoplugin-city'
					) as $name=>$taxonomy)
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
				CFGP_NAME . '-banner-default-content',					// Unique ID
				__('Geo Banner default content', CFGP_NAME),			// Box title
				'add_meta_box__default_content',						// Content callback, must be of type callable
				'cf-geoplugin-banner',									// Post type
				'advanced',
				'high'
			);
			
			$this->add_meta_box(
				CFGP_NAME . '-banner-sc',			// Unique ID
				__( 'Shortcodes', CFGP_NAME ),		// Box title
				'add_meta_box__shortcode',			// Content callback, must be of type callable
				'cf-geoplugin-banner',				// Post type
				'advanced',
				'high'
			);
			$this->add_meta_box(
				CFGP_NAME . '-banner-settings',		// Unique ID
				__( 'Settings', CFGP_NAME ),		// Box title
				'add_meta_box__settings',			// Content callback, must be of type callable
				'cf-geoplugin-banner',				// Post type
				'side'
			);
		}
		
		return;
	}
	
	/**
     * Default banner content
     */
	public function add_meta_box__default_content( $post ) {
		$banner = get_post_meta( $post->ID, 'cfgp-banner-default', true ); 
		if( !$banner ) {
			$banner = get_post_meta( $post->ID, CFGP_METABOX . 'banner_default', true );
		}
		?>
<p style="color:#550000;"><?php _e( 'This content is shown only when the selected location is not found. This means that anyone who is not from the set location will see this content.', CFGP_NAME ); ?></p>
<?php wp_editor( $banner, 'cfgp-banner-default-content', $settings = array('textarea_name'=>'cfgp-banner-default-content') );
	}
	
	/**
     * Geo banner settings
     */
	public function add_meta_box__settings( $post )
	{
		$country_code = array();

		// Get old taxonomies from the prevous version and merge with new one
		$taxonomy_list = array(
			__('Select Country',CFGP_NAME)	=>	array(
				'taxonomy' => 'cf-geoplugin-country',
				'post_meta' => 'cfgp-banner-location',
				'field' => 'country',
				'function' => 'select_countries'
			),
			__('Select Region',CFGP_NAME)	=>	array(
				'taxonomy' => 'cf-geoplugin-region',
				'post_meta' => 'cfgp-banner-location',
				'field' => 'region',
				'function' => 'select_regions'
			),
			__('Select City',CFGP_NAME)		=>	array(
				'taxonomy' => 'cf-geoplugin-city',
				'post_meta' => 'cfgp-banner-location',
				'field' => 'city',
				'function' => 'select_cities'
			)
		);
		
		echo '<div class="cfgp-country-region-city-multiple-form">';
		// list taxonomies
		foreach($taxonomy_list as $name=>$option)
		{
			// list all terms
			$all_terms = wp_get_post_terms($post->ID, $option['taxonomy'], array('fields' => 'all'));
			$data=array();
			foreach($all_terms as $i=>$fetch)
			{
				$data[]=$fetch->slug;
			}
			
			$get_post_meta = get_post_meta($post->ID, "{$option['post_meta']}-{$option['field']}", true);
			
			if(!empty($get_post_meta)){
				$data = array_merge($data, $get_post_meta);
			}
			
			if('cf-geoplugin-country' == $option['taxonomy']){
				$country_code = $data;
			}
			
			printf(
				'<p class="post-attributes-label-wrapper cfgp-banner-label-wrapper-%s">%s%s</p>',
				$option['field'],
				sprintf(
					'<label for="%s">%s</label>',
					$option['taxonomy'],
					$name
				),
				CFGP_Form::{$option['function']}(array(
					'name'=>"{$option['post_meta']}-{$option['field']}",
					'id' => $option['taxonomy'],
					'country_code' => $country_code
				), $data, true, false)
			);
		}
		echo '</div>';
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
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
	
}
endif;