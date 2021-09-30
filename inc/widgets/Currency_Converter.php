<?php
/**
 * Widgets settings
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Widget_Currency_Converter')) :
class CFGP_Widget_Currency_Converter extends WP_Widget {
	
	// The construct part  
	function __construct() {
	parent::__construct(
		'CFGP_Widget_Currency_Converter', 
		__('Currency Converter', CFGP_NAME), 
		array( 'description' => __( 'Convert any currency.', CFGP_NAME ), ) 
	);
	}
	  
	// Creating widget front-end
	public function widget( $args, $instance ) {
	 
	}
			  
	// Creating widget Backend 
	public function form( $instance ) {
	 
	}
		  
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
	 
	}
}
endif;