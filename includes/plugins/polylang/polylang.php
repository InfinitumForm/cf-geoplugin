<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Monarch integration
 *
 * @since      7.6.7
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CF_Geoplugin_Polylang' ) ):
class CF_Geoplugin_Polylang extends CF_Geoplugin_Global{
	function __construct(){
		
	}
	
	public function switch_language(){
		$page_id = $this->get_current_page_ID();
		$post_type = $this->get_post_type();
		$current_language = pll_current_language('locale');
		
		if(pll_is_translated_post_type($post_type))
		{
			
		}
	}
}
endif;