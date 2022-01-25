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