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

if(!class_exists('CFGP_Taxonomy')) :
class CFGP_Taxonomy extends CFGP_Global {
	
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
					'show_ui'			=> false,
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
					'show_ui'			=> false,
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
					'show_ui'			=> false,
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
						'name_field_description'=> __('The postcode name must be written in the original international format.',CFGP_NAME),
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
						'slug_field_description'=> __('The “slug” is the URL-friendly version of the postcode. It is usually all lowercase and contains only letters, numbers, and hyphens.',CFGP_NAME),
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
	
	// Add fields to taxonomy postcodes on the new page
	function add_postcode_fields( $taxonomy ) { ?>
<div class="form-field term-country-wrap">
	<label for="country"><?php _e('Country',CFGP_NAME); ?></label>
	<?php CFGP_Form::select_countries(array('name'=>'country', 'class'=>'cfgp_select2')); ?>
	<p><?php _e('Select the country where this postcode is from.',CFGP_NAME); ?></p>
</div>
<div class="form-field term-city-wrap">
	<label for="city"><?php _e('City name (optional)',CFGP_NAME); ?></label>
	<?php CFGP_Form::input('text', array('name'=>'city')); ?>
	<p><?php _e('Add city name for this postcode.',CFGP_NAME); ?></p>
</div>
	<?php }
	
	// Add fields to taxonomy postcodes on the edit page
	function edit_postcode_fields( $term, $taxonomy ) {
		$country = get_term_meta( $term->term_id, 'country', true );
		$city = get_term_meta( $term->term_id, 'city', true );
	?>
<tr class="form-field term-country-wrap">
	<th>
		<label for="country"><?php _e('Country',CFGP_NAME); ?></label>
	</th>
	<td>
		<?php CFGP_Form::select_countries(array('name'=>'country', 'class'=>'cfgp_select2'), $country); ?>
		<p class="description"><?php _e('Select the country where this postcode is from.',CFGP_NAME); ?></p>
	</td>
</tr>
<tr class="form-field term-city-wrap">
	<th>
		<label for="city"><?php _e('City name (optional)',CFGP_NAME); ?></label>
	</th>
	<td>
		<?php CFGP_Form::input('text', array('name'=>'city', 'value'=>$city)); ?>
		<p class="description"><?php _e('Add city name for this postcode.',CFGP_NAME); ?></p>
	</td>
</tr>
	<?php }
	
	// Add custom column to taxonomy postcodes in table
	function place_column_postcode_fields( $columns ) {
		if(isset($columns['posts'])) {
			unset($columns['posts']);
		}
		$columns['country'] = __('Country',CFGP_NAME);
		$columns['city'] = __('City',CFGP_NAME);
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
				strtolower(sanitize_text_field( $_POST[ 'country' ] ))
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
				sanitize_title( $_POST[ 'city' ] )
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
}
endif;