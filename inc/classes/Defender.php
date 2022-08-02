<?php
/**
 * Defender
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Defender')) :
class CFGP_Defender extends CFGP_Global {
	function __construct()
    {
        $this->add_action( 'init', 'protect', 1);
    }
	
	// Protect site from visiting
    public function protect()
    {
		
		$ip = CFGP_U::api('ip');
		
		if(
			is_admin()
			&& CFGP_U::request_bool('save_defender')
			&& wp_verify_nonce(sanitize_text_field($_REQUEST['nonce']), CFGP_NAME.'-save-defender') !== false
			&& isset( $_POST['block_proxy'] ) 
		) {
			CFGP_U::set_defender_cookie();
		}
		
		if( CFGP_U::request_string('cfgp_admin_access') === CFGP_U::ID() ) {
			CFGP_U::set_defender_cookie();
		}
		
		if(empty($ip) || CFGP_U::api('error')) return;
		
        if( $this->check() )
        {
			if( function_exists('http_response_code') ) {
				http_response_code(403);
			} else {
				header( 'HTTP/1.0 403 Forbidden', true, 403 );
			}
			
            die( wpautop( html_entity_decode( stripslashes( CFGP_Options::get('block_country_messages') ) ) ) );
        }

        if(
			CFGP_Options::get('enable_spam_ip', 0) 
			&& CFGP_Options::get('enable_defender', 0) 
			&& CFGP_License::level( CFGP_Options::get('license_sku') ) > 0 
		)
        {
			if( CFGP_U::api('is_spam') ) {
				if( function_exists('http_response_code') && version_compare(PHP_VERSION, '5.4', '>=') ) {
					http_response_code(403);
				} else {
					header( $this->header, true, 403 );
				}
				
				die( wpautop( html_entity_decode( stripslashes( CFGP_Options::get('block_country_messages') ) ) ) );
			}
        }
    }

    // Check what to do with user
    public function check()
    {		
		// Defender is disabled ???
        if( CFGP_Options::get('enable_defender', 0) == 0 ) {
			return false;
		}
		
		// Bots need to see this website
		if(CFGP_U::is_bot()) {
			return false;
		}

		// Browser is allow to access to website
		if(CFGP_U::check_defender_cookie()) {
			return false;
		}
		
		// Let's block proxy
		if(CFGP_Options::get('block_proxy', 0) && CFGP_U::api('is_proxy') == 1) {
			return true;
		}

		// Explode all IP's and block them... Yeah baby!
        $ips = preg_split( '/[,;\n|]+/', CFGP_Options::get('block_ip') );
		$ips = array_map( 'trim', $ips );
		$ips = array_filter( $ips );
        if( in_array( CFGP_U::api('ip'), $ips, true ) !== false ){
			return true;
		} 
		
		// Get countries
		$block_country = CFGP_Options::get('block_country');
		if(!empty($block_country) && !is_array($block_country) && preg_match('/\]|\[/', $block_country)){
			$block_country = explode(']|[', $block_country);
			$block_country = array_map('trim', $block_country);
			$block_country = array_filter($block_country);
		}
		
		// Get regions
		$block_region = CFGP_Options::get('block_region');
		if(!empty($block_region) && !is_array($block_region) && preg_match('/\]|\[/', $block_region)){
			$block_region = explode(']|[', $block_region);
			$block_region = array_map('trim', $block_region);
			$block_region = array_filter($block_region);
		}
		
		// Get cities
		$block_city = CFGP_Options::get('block_city');
		if(!empty($block_city) && !is_array($block_city) && preg_match('/\]|\[/', $block_city)){
			$block_city = explode(']|[', $block_city);
			$block_city = array_map('trim', $block_city);
			$block_city = array_filter($block_city);
		}
		
		// Generate redirection mode
		$mode = array( NULL, 'country', 'region', 'city' );
		$mode = $mode[ count( array_filter( array_map(
			function($obj) {
				return !empty($obj);
			},
			array(
				$block_country,
				$block_region,
				$block_city
			)
		) ) ) ];
		
		// Switch mode
		switch ( $mode ) {
			case 'country':
				if( CFGP_U::check_user_by_country($block_country) ) {
					return true;
				}
				break;
			case 'region':
				if(
					CFGP_U::check_user_by_region($block_region) 
					&& CFGP_U::check_user_by_country($block_country) 
				) {
					return true;
				}
				break;
			case 'city':
				if( 
					CFGP_U::check_user_by_city($block_city) 
					&& CFGP_U::check_user_by_region($block_region) 
					&& CFGP_U::check_user_by_country($block_country) 
				) {
					return true;
				}
				break;
		}
		
		// Hey, we are all good. Right?
        return false;
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