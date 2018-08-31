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
            global $CF_GEOPLUGIN_OPTIONS;
            header( $this->header );
            $block_message = $CF_GEOPLUGIN_OPTIONS['block_country_messages'];
            die( html_entity_decode( stripslashes( $block_message ) ) );
            exit;
        }
    }

    // Check what to do with user
    public function check()
    {
        global $CF_GEOPLUGIN_OPTIONS, $CFGEO;
        if( $CF_GEOPLUGIN_OPTIONS['enable_defender'] == 0 ) return false;
        $flag = false;

        $ips = explode( ',', $CF_GEOPLUGIN_OPTIONS['block_ip'] );
        if( !empty( $ips ) && in_array( $CFGEO['ip'], $ips, true ) !== false ) $flag = true;

        if( $flag === false )
        {
            $geo_data = array(
                'country_code'  => $CF_GEOPLUGIN_OPTIONS['block_country'],
                'region_code'   => $CF_GEOPLUGIN_OPTIONS['block_region'],
                'city'          => $CF_GEOPLUGIN_OPTIONS['block_city']
            );
            foreach( $geo_data as $field => $check )
            {
                if( !empty( $check ) )
                {
                    $geo = array_map( 'trim', explode( "]|[", $check ) );
                    $geo = array_map( 'strtolower', $geo );
                    if( !empty( $geo ) && $geo !== false && in_array( strtolower( $CFGEO[$field] ), $geo, true ) !== false ) $flag = true;
                }
            }
        }

        return $flag;
    }


}
endif;