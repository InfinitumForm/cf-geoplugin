<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Wooplatnica integration
 *
 * Force all conversions to RSD
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( !class_exists( 'CF_Geoplugin_Wooplatnica' ) ):
class CF_Geoplugin_Wooplatnica extends CF_Geoplugin_Global
{
	function __construct()
    {
        $this->add_action( 'init', 'wooplatnica', 999 );
    }
	
	function wooplatnica(){
		if ( is_plugin_active( 'wooplatnica/wooplatnica.php' ) ) {
			 $this->add_filter('wooplatnica_cena', 'conversion');
		}
	}
	
	function conversion($total) {
		$from=get_woocommerce_currency_symbol();
		return do_shortcode("[cfgeo_converter from={$from} to=RSD]{$total}[/cfgeo_converter]");
	}
}
endif;