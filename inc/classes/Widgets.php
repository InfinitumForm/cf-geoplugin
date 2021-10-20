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

if(!class_exists('CFGP_Widgets')) :
class CFGP_Widgets extends CFGP_Global {
	private function __construct(){
		// Call main classes
		$classes = apply_filters('cfgp/widget/classes', array(
			'CFGP_Widget_Currency_Converter',
		));
		
		global $class;
		
		foreach($classes as $i => $class){
			
			if(!class_exists($class)) {
				$filename = str_replace('CFGP_Widget_', '', $class) . '.php';
				if(file_exists(CFGP_INC . "/widgets/{$filename}")) {
					include_once CFGP_INC . "/widgets/{$filename}";
				}
			}
			
			if(class_exists($class)) {
				add_action( 'widgets_init', function(){
					global $class;
					if($class){
						register_widget( $class );
					}
				} );
			}
			
		}
	}
	
	/* 
	 * Instance
	 * @verson    8.0.0
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