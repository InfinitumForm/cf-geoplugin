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
if( !class_exists( 'CFGP__Plugin__wooplatnica' ) ):
class CFGP__Plugin__wooplatnica extends CFGP_Global
{
	function __construct()
    {
		$this->add_filter('wooplatnica_cena', 'conversion');
    }
	
	function conversion($total) {
		if(CFGP_U::api('currency') == 'RSD' && CFGP_U::api('base_currency') == 'RSD') {
			return $total;
		}
		
		return do_shortcode("[cfgeo_converter to='RSD' auto=1 no-symbol=1]{$total}[/cfgeo_converter]");
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