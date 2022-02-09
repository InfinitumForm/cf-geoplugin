<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * WooCommerce integration
 *
 * @since      8.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__wordpress_seo' ) ):
class CFGP__Plugin__wordpress_seo extends CFGP_Global
{
	private function __construct()
    {
		$this->add_action('wpseo_register_extra_replacements', 'register_vars');
	}
	
	/* 
	 * Register custom variables
	 * @verson    1.0.0
	 */
	public function register_vars (){
		foreach(CFGP_U::api(false, CFGP_Defaults::API_RETURN) as $key=>$value) {
			wpseo_register_var_replacement("%%{$key}%%", function () use ($value) {
				return $value;
			}, 'advanced');
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