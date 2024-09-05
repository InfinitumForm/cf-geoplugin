<?php
/**
 * Register custom taxonomy
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

if(!class_exists('CFGP_Taxonomy', false)) : class CFGP_Taxonomy extends CFGP_Global {
	
	function __construct(){
		$this->add_action( 'init', 'register' );
		
		$this->add_action( 'cf-geoplugin-postcode_add_form_fields', 'add_postcode_fields' );
		$this->add_action( 'cf-geoplugin-postcode_edit_form_fields', 'edit_postcode_fields', 10, 2 );
		
		$this->add_filter( 'manage_edit-cf-geoplugin-postcode_columns', 'place_column_postcode_fields' );
		$this->add_filter( 'manage_cf-geoplugin-postcode_custom_column', 'column_postcode_fields',10,3);
		
		$this->add_action( 'created_cf-geoplugin-postcode', 'save_postcode_fields' );
		$this->add_action( 'edited_cf-geoplugin-postcode', 'save_postcode_fields' );
	}
	
	public function register(){
		if(!taxonomy_exists( 'cf-geoplugin-country' ))
		{
			register_taxonomy(
				'cf-geoplugin-country', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Countries', 'cf-geoplugin'),
						'singular_name' 		=> __('Country', 'cf-geoplugin'),
						'menu_name' 			=> __('Countries', 'cf-geoplugin'),
						'all_items' 			=> __('All Countries', 'cf-geoplugin'),
						'edit_item' 			=> __('Edit Country', 'cf-geoplugin'),
						'view_item' 			=> __('View Country', 'cf-geoplugin'),
						'update_item' 			=> __('Update Country', 'cf-geoplugin'),
						'add_new_item' 			=> __('Add New Country', 'cf-geoplugin'),
						'new_item_name' 		=> __('New Country Name', 'cf-geoplugin'),
						'parent_item' 			=> __('Parent Country', 'cf-geoplugin'),
						'parent_item_colon' 	=> __('Parent Country', 'cf-geoplugin'),
					),
					'hierarchical'		=> true,
					'show_ui'			=> false,
					'public'		 	=> false,
					'label'          	=> __('Countries', 'cf-geoplugin'),
					'singular_label' 	=> __('Country', 'cf-geoplugin'),
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
						'name' 					=> __('Regions', 'cf-geoplugin'),
						'singular_name' 		=> __('Region', 'cf-geoplugin'),
						'menu_name' 			=> __('Regions', 'cf-geoplugin'),
						'all_items' 			=> __('All Regions', 'cf-geoplugin'),
						'edit_item' 			=> __('Edit Region', 'cf-geoplugin'),
						'view_item' 			=> __('View Region', 'cf-geoplugin'),
						'update_item' 			=> __('Update Region', 'cf-geoplugin'),
						'add_new_item' 			=> __('Add New Region', 'cf-geoplugin'),
						'new_item_name' 		=> __('New Region Name', 'cf-geoplugin'),
						'parent_item' 			=> __('Parent Region', 'cf-geoplugin'),
						'parent_item_colon' 	=> __('Parent Region', 'cf-geoplugin'),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> false,
					'public'		 	=> false,
					'label'          	=> __('Regions', 'cf-geoplugin'),
					'singular_label' 	=> __('Region', 'cf-geoplugin'),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false,
					
					'show_in_quick_edit'=> false,
					'show_admin_column' => false,
					'show_tagcloud'		=> false,
					'show_in_rest'		=> false,
					'show_in_menu'		=> false,
					'publicly_queryable'=> false,
					'meta_box_cb'		=> false
				)
			);
		}
		
		
		if(!taxonomy_exists( 'cf-geoplugin-city' ))
		{
			register_taxonomy(
				'cf-geoplugin-city', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Cities', 'cf-geoplugin'),
						'singular_name' 		=> __('City', 'cf-geoplugin'),
						'menu_name' 			=> __('Cities', 'cf-geoplugin'),
						'all_items' 			=> __('All Cities', 'cf-geoplugin'),
						'edit_item' 			=> __('Edit City', 'cf-geoplugin'),
						'view_item' 			=> __('View City', 'cf-geoplugin'),
						'update_item' 			=> __('Update City', 'cf-geoplugin'),
						'add_new_item' 			=> __('Add New City', 'cf-geoplugin'),
						'new_item_name' 		=> __('New City Name', 'cf-geoplugin'),
						'parent_item' 			=> __('Parent City', 'cf-geoplugin'),
						'parent_item_colon' 	=> __('Parent City', 'cf-geoplugin'),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> false,
					'public'		 	=> false,
					'label'          	=> __('Cities', 'cf-geoplugin'),
					'singular_label' 	=> __('City', 'cf-geoplugin'),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false,
					
					'show_in_quick_edit'=> false,
					'show_admin_column' => false,
					'show_tagcloud'		=> false,
					'show_in_rest'		=> false,
					'show_in_menu'		=> false,
					'publicly_queryable'=> false,
					'meta_box_cb'		=> false
				)
			);
		}
		
		
		if(!taxonomy_exists( 'cf-geoplugin-postcode' ))
		{
			register_taxonomy(
				'cf-geoplugin-postcode', 'cf-geoplugin-banner',
				array(
					'labels'			=> array(
						'name' 					=> __('Postcodes', 'cf-geoplugin'),
						'name_field_description'=> __('The postcode name must be written in the original international format.', 'cf-geoplugin'),
						'singular_name' 		=> __('Postcode', 'cf-geoplugin'),
						'menu_name' 			=> __('Postcodes', 'cf-geoplugin'),
						'all_items' 			=> __('All Postcodes', 'cf-geoplugin'),
						'edit_item' 			=> __('Edit Postcode', 'cf-geoplugin'),
						'view_item' 			=> __('View Postcode', 'cf-geoplugin'),
						'update_item' 			=> __('Update Postcode', 'cf-geoplugin'),
						'add_new_item' 			=> __('Add New Postcode', 'cf-geoplugin'),
						'new_item_name' 		=> __('New Postcode Name', 'cf-geoplugin'),
						'parent_item' 			=> __('Parent Postcode', 'cf-geoplugin'),
						'parent_item_colon' 	=> __('Parent Postcode', 'cf-geoplugin'),
						'slug_field_description'=> __('The “slug” is the URL-friendly version of the postcode. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'cf-geoplugin'),
					),
					'hierarchical'   	=> true,
					'show_ui'			=> true,
					'public'		 	=> false,
					'label'          	=> __('Postcodes', 'cf-geoplugin'),
					'singular_label' 	=> __('Postcode', 'cf-geoplugin'),
					'rewrite'        	=> true,
					'query_var'			=> false,
					'show_tagcloud'		=> false,
					'show_in_nav_menus'	=> false,
					
					'show_in_quick_edit'=> false,
					'show_admin_column' => false,
					'show_tagcloud'		=> false,
					'show_in_rest'		=> false,
					'show_in_menu'		=> false,
					'publicly_queryable'=> false,
					'meta_box_cb'		=> false
				)
			);
		}
	}
	
	// Add fields to taxonomy postcodes on the new page
	function add_postcode_fields( $taxonomy ) { ?>
<div class="form-field term-country-wrap">
	<label for="country"><?php esc_html_e('Country', 'cf-geoplugin'); ?></label>
	<?php CFGP_Form::select_countries(array('name'=>'country', 'class'=>'cfgp_select2')); ?>
	<p><?php esc_html_e('Select the country where this postcode is from.', 'cf-geoplugin'); ?></p>
</div>
<div class="form-field term-city-wrap">
	<label for="city"><?php esc_html_e('City name (optional)', 'cf-geoplugin'); ?></label>
	<?php CFGP_Form::input('text', array('name'=>'city')); ?>
	<p><?php esc_html_e('Add city name for this postcode.', 'cf-geoplugin'); ?></p>
</div>
	<?php }
	
	// Add fields to taxonomy postcodes on the edit page
	function edit_postcode_fields( $term, $taxonomy ) {
		$country = get_term_meta( $term->term_id, 'country', true );
		$city = get_term_meta( $term->term_id, 'city', true );
	?>
<tr class="form-field term-country-wrap">
	<th>
		<label for="country"><?php esc_html_e('Country', 'cf-geoplugin'); ?></label>
	</th>
	<td>
		<?php CFGP_Form::select_countries(array('name'=>'country', 'class'=>'cfgp_select2'), $country); ?>
		<p class="description"><?php esc_html_e('Select the country where this postcode is from.', 'cf-geoplugin'); ?></p>
	</td>
</tr>
<tr class="form-field term-city-wrap">
	<th>
		<label for="city"><?php esc_html_e('City name (optional)', 'cf-geoplugin'); ?></label>
	</th>
	<td>
		<?php CFGP_Form::input('text', array('name'=>'city', 'value'=>$city)); ?>
		<p class="description"><?php esc_html_e('Add city name for this postcode.', 'cf-geoplugin'); ?></p>
	</td>
</tr>
	<?php }
	
	// Add custom column to taxonomy postcodes in table
	function place_column_postcode_fields( $columns ) {
		if(isset($columns['posts'])) {
			unset($columns['posts']);
		}
		$columns['country'] = __('Country', 'cf-geoplugin');
		$columns['city'] = __('City', 'cf-geoplugin');
		return $columns;
	}
	
	// Add custom column value to taxonomy postcodes in table
	public function column_postcode_fields ($content, $column_name, $term_id) {
		switch ($column_name) {
			case 'country':
				$country_code = get_term_meta( $term_id, 'country', true );
				$countries = CFGP_Library::get_countries();
				$content = $countries[$country_code] ?? '-';
				break;
			case 'city':
				$content = get_term_meta( $term_id, 'city', true ) ?? '-';
				break;
		}
		return $content;
	}
	
	// Add fields to taxonomy postcodes in the database
	function save_postcode_fields( $term_id ) {
		
		if(isset($_POST[ 'country' ])) {
			update_term_meta(
				$term_id,
				'country',
				CFGP_U::strtolower(sanitize_text_field( $_POST[ 'country' ] ))
			);
		}
		
		if(isset($_POST[ 'city' ])) {
			update_term_meta(
				$term_id,
				'city',
				sanitize_text_field( $_POST[ 'city' ] )
			);
			update_term_meta(
				$term_id,
				'city_slug',
				sanitize_title( sanitize_text_field( $_POST[ 'city' ] ) )
			);
		}
		
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
} endif;