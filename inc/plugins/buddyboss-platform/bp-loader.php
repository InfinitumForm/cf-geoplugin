<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * BuddyBoss integrations
 *
 * @since      8.4.2
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__buddyboss_platform', false ) ): 
class CFGP__Plugin__buddyboss_platform extends CFGP_Global{
	
	private function __construct(){
		
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