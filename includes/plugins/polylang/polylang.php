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
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
	}
	
	public function switch_language(){
		$CFGEO = $GLOBALS['CFGEO'];

		$page_id = $this->get_current_page_ID();
		$post_type = $this->get_post_type();
		$current_language = pll_current_language('locale');

		$language = substr( $CFGEO['locale'], 0, 2 );
		
		if($page_id && pll_is_translated_post_type($post_type))
		{
			if( $translated_page_id = pll_get_post($page_id, $current_language) )
			{
				if($translated_page_id != $page_id){
					
				}
			}
		}
	}
}
endif;