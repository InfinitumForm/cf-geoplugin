<?php
/**
 * License control
 *
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_License')) :
class CFGP_License extends CFGP_Global{
	
	/*
	 * License names
	 */
	public function name($sku=false){
		$license_names = array(
			CFGP_Defaults::BASIC_LICENSE			=> __('UNLIMITED Basic License (1 month)',CFGP_NAME),
			CFGP_Defaults::PERSONAL_LICENSE			=> __('UNLIMITED Personal License (1 year)',CFGP_NAME),
			CFGP_Defaults::PERSONAL_LICENSE_4Y		=> __('UNLIMITED Personal License (4 years)',CFGP_NAME),
			CFGP_Defaults::FREELANCER_LICENSE		=> __('UNLIMITED Freelancer License (1 year)',CFGP_NAME),
			CFGP_Defaults::FREELANCER_LICENSE_4Y	=> __('UNLIMITED Freelancer License (4 years)',CFGP_NAME),
			CFGP_Defaults::BUSINESS_LICENSE			=> __('UNLIMITED Business License (1 year)',CFGP_NAME),
			CFGP_Defaults::BUSINESS_LICENSE_4Y		=> __('UNLIMITED Business License (4 years)',CFGP_NAME),
			CFGP_Defaults::LIFETIME_LICENSE			=> __('UNLIMITED Lifetime License',CFGP_NAME),
		);
		
		if( CFGP_DEV_MODE )
		{
			$license_names[CFGP_Defaults::DEVELOPER_LICENSE] = __('UNLIMITED Developer License', CFGP_NAME);
		}
		
		$license_names = apply_filters('cfgp/license/names', $license_names);
		
		if($sku)
		{
			if(isset($license_names[$sku])) {
				return apply_filters('cfgp/license/name', $license_names[$sku]);
			} else {
				return false;
			}
		}

		return $license_names;
	}
	
	/*
	 * Access level
	*/
	public static function level($level = 0){		
		$return = 0;
		
		if($level==0){
			$level=CFGP_Options::get();
		}
		
		$levels=array_flip(array(
			0		=> 0,
			1		=> CFGP_Defaults::BASIC_LICENSE,
			2		=> CFGP_Defaults::PERSONAL_LICENSE,
			3		=> CFGP_Defaults::PERSONAL_LICENSE_4Y,
			4		=> CFGP_Defaults::FREELANCER_LICENSE,
			5		=> CFGP_Defaults::FREELANCER_LICENSE_4Y,
			6		=> CFGP_Defaults::BUSINESS_LICENSE,
			7		=> CFGP_Defaults::BUSINESS_LICENSE_4Y,
			1000	=> CFGP_Defaults::LIFETIME_LICENSE,
			3000	=> CFGP_Defaults::DEVELOPER_LICENSE
		));
		
		if(is_array($level))
		{
			if(isset($level['license']) && isset($level['license_sku']))
			{
				if($level['license'])
				{
					if(isset($levels[$level['license_sku']])) {
						$return = $levels[$level['license_sku']];
					}
				}
			}
		}
		else
		{			
			if(isset($levels[$level])) {
				$return = $levels[$level];
			}
		}
		
		return $return;
	}
	
	/*
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		global $cfgp_cache;
		$class = self::class;
		$instance = $cfgp_cache->get($class);
		if ( !$instance ) {
			$instance = $cfgp_cache->set($class, new self());
		}
		return $instance;
	}
}
endif;