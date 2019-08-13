<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Wooplatnica integration
 *
 * Force all conversions to RSD
 *
 * @since      7.0.0
 * @version    7.7.2
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 * @update     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CF_Geoplugin_Wooplatnica' ) ):
class CF_Geoplugin_Wooplatnica extends CF_Geoplugin_Global
{
	function __construct()
    {
		$this->wooplatnica();
    }
	
	function wooplatnica(){
		if ( is_plugin_active( 'wooplatnica/wooplatnica.php' ) ) {
			 $this->add_filter('wooplatnica_cena', 'conversion');
		}
	}
	
	function conversion($total) {
		return do_shortcode("[cfgeo_converter auto=1 no-symbol=1]{$total}[/cfgeo_converter]");
	}
}
endif;