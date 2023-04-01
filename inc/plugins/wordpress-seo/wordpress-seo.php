<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * WooCommerce integration
 *
 * @since      8.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__wordpress_seo', false ) ):
class CFGP__Plugin__wordpress_seo extends CFGP_Global
{
	private $remove = 'state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,is_proxy,is_mobile,in_eu,is_vat,gps,error,error_message,lookup,status,runtime,accuracy_radius,credit,official_url,available_lookup,limited,limit,license_hash,request_url';
	private $excluded;
	
	private function __construct()
    {
		$this->add_action('wpseo_register_extra_replacements', 'register_vars');
	}
	
	/* 
	 * Register custom variables
	 * @verson    1.0.0
	 */
	public function register_vars(){
		foreach(CFGP_U::api(false, CFGP_Defaults::API_RETURN) as $key=>$value) {
			if( in_array($key, $this->get_excluded()) !== false ) {
				continue;
			}
			wpseo_register_var_replacement("%%{$key}%%", function () use ($value) {
				return $value;
			}, 'advanced');
		}
	}
	
	/**
	 * Excluded shortcodes
	 *
	 * @since    4.0.0
	 */
	public function get_excluded(){
		if(!$this->excluded) {
			$this->excluded = array_filter(array_map('trim', explode(',', $this->remove)));
		}
		return apply_filters('cfgeo_wpseo_excluded', $this->excluded);
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