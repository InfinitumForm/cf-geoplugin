<?php
/**
 * Defender
 *
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
		global $cfgp_cache;
		
		$ip = CFGP_U::api('ip');
		
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

        if( CFGP_Options::get('enable_spam_ip', 0) && CFGP_Options::get('enable_defender', 0) && CFGP_License::level( CFGP_Options::get('license_sku') ) > 0 )
        {			
			$response = false;
			
			if($ip)
			{
				$url = add_query_arg( 'ip', $ip, CFGP_U::API['spam-checker'] );
				$response = $this->curl_get( $url );
            }
			
            if( $response !== false )
            {
                $response = json_decode( $response, true );
                if( isset( $response['return'] ) && $response['return'] === true && isset( $response['error'] ) && $response['error'] === false )
                {
					if( function_exists('http_response_code') && version_compare(PHP_VERSION, '5.4', '>=') ) {
						http_response_code(403);
					} else {
						header( $this->header, true, 403 );
					}
                    
                    die( wpautop( html_entity_decode( stripslashes( CFGP_Options::get('block_country_messages') ) ) ) );
                }
            }
        }
    }

    // Check what to do with user
    public function check()
    {
		if(CFGP_U::is_bot()) return false;
		
        if( CFGP_Options::get('enable_defender', 0) == 0 ) return false;
        $flag = false;

        $ips = preg_split( '/[,;\n|]+/', CFGP_Options::get('block_ip') );
		$ips = array_map( 'trim', $ips );
        if( in_array( CFGP_U::api('ip'), $ips, true ) !== false ) $flag = true;

        if( $flag === false )
        {
            $geo = array(
                'country_code'  => array_map( 'trim', explode( "]|[", CFGP_Options::get('block_country') ) ),
                'region_code'   => array_map( 'trim', explode( "]|[", CFGP_Options::get('block_region') ) ),
                'city'          => array_map( 'trim', explode( "]|[", CFGP_Options::get('block_city') ) )
            );

            $country_check = $this->check_user_by_country( $geo['country_code'] );
            if( $country_check || empty( $geo['country_code'][0] ) )
            {
                if( $this->check_user_by_city( $geo['city'] ) && ( $this->check_user_by_region( $geo['region_code'] ) || empty( $geo['region_code'][0] ) ) ) return true;
                elseif( empty( $geo['city'][0] ) && $this->check_user_by_region( $geo['region_code'] ) ) return true;
                elseif( empty( $geo['city'][0] ) && empty( $geo['region_code'][0] ) && $country_check ) return true;
            }
        }

        return $flag;
    }
	
	/**
	 * Check user's postcode for defender or seo redirection
	 */
	public function check_user_by_postcode( $postcode )
	{
		if( is_array( $postcode ) )
		{
			$postcode = array_map( 'strtolower', $postcode );
			if( isset( $postcode[0] ) && !empty( $postcode[0] ) && in_array( sanitize_title_with_dashes( CFGP_U::api('postcode') ), $postcode, true ) ) return true;
		}
		elseif( is_string( $postcode ) )
		{
			if( !empty( $postcode ) && strtolower( $postcode ) === sanitize_title_with_dashes(CFGP_U::api('postcode') ) ) return true;
		}

		return false;
	}


	/**
	 * Check user's city for defender or seo redirection
	 */
	public function check_user_by_city( $city )
	{
		global $cfgp_cache;
		if( is_array( $city ) )
		{
			$city = array_map( 'strtolower', $city );
			if( isset( $city[0] ) && !empty( $city[0] ) && in_array( sanitize_title_with_dashes( CFGP_U::api('city') ), $city, true ) ) return true;
		}
		elseif( is_string( $city ) )
		{
			if( !empty( $city ) && strtolower( $city ) === sanitize_title_with_dashes(CFGP_U::api('city') ) ) return true;
		}

		return false;
	}

	/**
	 * Check user's region for defender or seo redirection
	 */
	public function check_user_by_region( $region )
	{
		global $cfgp_cache;
		if( is_array( $region ) )
		{
			if( isset( $region[0] ) && !empty( $region[0] ) )
			{
				$region = array_map( 'strtolower', $region );
				// Supports region code and region name
				if( in_array( strtolower( CFGP_U::api('region_code') ), $region, true ) ) return true; 
				if( in_array( sanitize_title_with_dashes( CFGP_U::api('region') ), $region, true ) ) return true;
			}
		}
		elseif( is_string( $region ) )
		{
			if( !empty( $region ) )
			{
				// Supports region code and region name
				if( strtolower( $region ) === strtolower( CFGP_U::api('region_code') ) ) return true; 
				if( strtolower( $region ) === sanitize_title_with_dashes( CFGP_U::api('region') ) ) return true;
			}
		}

		return false;
	}

	/**
	 * Check user's country for defender or seo redirection
	 */
	public function check_user_by_country( $country )
	{
		global $cfgp_cache;

		if( is_array( $country ) )
		{
			if( isset( $country[0] ) && !empty( $country[0] ) )
			{
				$country = array_map( 'strtolower', $country );
				// Supports country code and name
				if( in_array( strtolower( CFGP_U::api('country_code') ), $country, true ) ) return true;
				if( in_array( sanitize_title_with_dashes( CFGP_U::api('country') ), $country, true ) ) return true;
			}
		}
		elseif( is_string( $country ) )
		{
			if( !empty( $country ) )
			{
				// Supports country code and name
				if( strtolower( $country ) === strtolower( CFGP_U::api('country_code') ) ) return true;
				if( strtolower( $country ) === sanitize_title_with_dashes( CFGP_U::api('country') ) ) return true;
			}
		}

		return false;
	}
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		
		if(!is_admin()) {
			return;
		}
		
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