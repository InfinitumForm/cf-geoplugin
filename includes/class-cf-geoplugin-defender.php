<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Plugin Defender
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( !class_exists( 'CF_Geoplugin_Defender' ) ) :
class CF_Geoplugin_Defender extends CF_Geoplugin_Global
{
    // Header informations
    private $header = 'HTTP/1.0 403 Forbidden';

    function __construct()
    {
        $this->add_action( 'init', 'protect' );
    }

    // Protect site from visiting
    public function protect()
    {
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
        if( $this->check() )
        {
			if( function_exists('http_response_code') && version_compare(PHP_VERSION, '5.4', '>=') ) {
				http_response_code(403);
			} else {
				header( $this->header, true, 403 );
			}
			
            if( isset( $CF_GEOPLUGIN_OPTIONS['block_country_messages'] ) ) die( wpautop( html_entity_decode( stripslashes( $CF_GEOPLUGIN_OPTIONS['block_country_messages'] ) ) ) );
            else die();
            exit;
        }

        if( isset( $CF_GEOPLUGIN_OPTIONS['enable_spam_ip'] ) && $CF_GEOPLUGIN_OPTIONS['enable_spam_ip'] && isset( $CF_GEOPLUGIN_OPTIONS['enable_defender'] ) && $CF_GEOPLUGIN_OPTIONS['enable_defender'] && self::access_level( $CF_GEOPLUGIN_OPTIONS['license_sku'] ) > 0 )
        {
            $CFGEO = $GLOBALS['CFGEO'];
            $url = add_query_arg( 'ip', $CFGEO['ip'], $GLOBALS['CFGEO_API_CALL']['spam-checker'] );
            $response = $this->curl_get( $url );
            
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
                    
                    die( wpautop( html_entity_decode( stripslashes( $CF_GEOPLUGIN_OPTIONS['block_country_messages'] ) ) ) );
                    exit;
                }
            }
        }
    }

    // Check what to do with user
    public function check()
    {
		if(parent::is_bot()) return false;
		
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
        if( $CF_GEOPLUGIN_OPTIONS['enable_defender'] == 0 ) return false;
        $flag = false;

        $ips = explode( ',', $CF_GEOPLUGIN_OPTIONS['block_ip'] );
        if( !empty( $ips ) && isset( $CFGEO['ip'] ) && !empty( $CFGEO['ip'] ) && in_array( $CFGEO['ip'], $ips, true ) !== false ) $flag = true;

        if( $flag === false )
        {
            $geo = array(
                'country_code'  => array_map( 'trim', explode( "]|[", $CF_GEOPLUGIN_OPTIONS['block_country'] ) ),
                'region_code'   => array_map( 'trim', explode( "]|[", $CF_GEOPLUGIN_OPTIONS['block_region'] ) ),
                'city'          => array_map( 'trim', explode( "]|[", $CF_GEOPLUGIN_OPTIONS['block_city'] ) )
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


}
endif;