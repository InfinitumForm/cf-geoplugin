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
        $this->add_action( 'send_headers', 'protect' );
    }

    // Protect site from visiting
    public function protect()
    {
        if( $this->check() && !is_admin() )
        {
            $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
            header( $this->header );
            if( isset( $CF_GEOPLUGIN_OPTIONS['block_country_messages'] ) ) die( html_entity_decode( stripslashes( $CF_GEOPLUGIN_OPTIONS['block_country_messages'] ) ) );
            else die();
            exit;
        }
    }

    // Check what to do with user
    public function check()
    {
		return false;
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
        if( $CF_GEOPLUGIN_OPTIONS['enable_defender'] == 0 ) return false;
        $flag = false;

        $ips = explode( ',', $CF_GEOPLUGIN_OPTIONS['block_ip'] );
        if( !empty( $ips ) && in_array( $CFGEO['ip'], $ips, true ) !== false ) $flag = true;

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