<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Public functions
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Public')) :
class CF_Geoplugin_Public extends CF_Geoplugin_Global
{
	public function run(){
		$this->add_action( 'init', 'run_style' );
		$this->add_action( 'wp_head', 'initialize_plugin_javascript', 1 );
		$this->add_action( 'admin_head', 'initialize_plugin_javascript', 1 );
		$this->add_action( 'wp_head', 'cfgp_geo_tag' );
	}
	
	public function run_style(){
		$this->add_action( 'wp_enqueue_scripts', 'register_style' );
		$this->add_action( 'wp_enqueue_scripts', 'register_scripts' );
		$this->add_action( 'admin_enqueue_scripts', 'register_style' );
	}
	
	public function register_style($page){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		wp_register_style(
			CFGP_NAME.'-flag',
			CFGP_ASSETS . '/css/flag-icon.min.css',
			1,
			CFGP_VERSION,
			'all'
		);
		if(!is_admin()){
			if( $CF_GEOPLUGIN_OPTIONS['enable_woocommerce'] && $CF_GEOPLUGIN_OPTIONS['woocommerce_active'] )
        	{
				wp_register_style(
					CFGP_NAME.'-woocommerce',
					CFGP_ASSETS . '/css/cf-geoplugin-woocommerce.css',
					array('woocommerce-general'),
					CFGP_VERSION,
					'all'
				);
				wp_enqueue_style( CFGP_NAME . '-woocommerce' );
			}
			
			wp_register_style( CFGP_NAME . '-widget-converter', CFGP_ASSETS . '/css/cf-geoplugin-widget-converter.css', array(), CFGP_VERSION );

		}
		
	}

	public function register_scripts()
	{
		if( !is_admin() )
		{
			wp_register_script( CFGP_NAME . '-js-public', CFGP_ASSETS . '/js/cf-geoplugin-public.js', array( 'jquery' ), CFGP_VERSION, true );
			wp_localize_script(
				CFGP_NAME . '-js-public',
				'CFGP_PUBLIC',
				array(
					'ajax_url'			=> self_admin_url( 'admin-ajax.php' ),
					'loading_gif'		=> esc_url( CFGP_ASSETS . '/images/double-ring-loader.gif' )
				)
			);
			wp_enqueue_script( CFGP_NAME . '-js-public' );

		} 
	}
	
	public function initialize_plugin_javascript(){ $CFGEO = $GLOBALS['CFGEO']; ?>
<!-- <?php _e('CF Geoplugin JavaScript Plugin',CFGP_NAME); ?> -->
<script>
/* <![CDATA[ */
	if(typeof cf == 'undefined') var cf = {};	
	cf.geoplugin = {url:window.location.href,host:window.location.hostname,protocol:window.location.protocol.replace(/\:/g,''),<?php
		$exclude = array_map('trim', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,error,status,runtime,error_message'));
		$sprintf = array();
		if( isset( $CFGEO['country_code'] ) && !empty( $CFGEO['country_code'] ) )
		{
			$CFGEO = array_merge($CFGEO,array(
				'flag' => CFGP_ASSETS.'/flags/4x3/'.strtolower($CFGEO['country_code']).'.svg'
			));
		}
		foreach($CFGEO as $name=>$value)
		{
			if(in_array($name, $exclude, true) === false){
				$sprintf[]=sprintf('%1$s:"%2$s"',$name,esc_attr($value));
			}
		}
		echo join(',',$sprintf);
	?>}
	window.cfgeo = cf.geoplugin;
/* ]]> */
</script>

	<?php }

	public function cfgp_geo_tag()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$post = get_post();
		
		if( isset( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) && in_array( $post->post_type, $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) )
		{
			$geo_data = array(
				'geo.enable'	=> get_post_meta( $post->ID, 'cfgp-geotag-enable', true ),
				'geo.address' 	=> get_post_meta( $post->ID, 'cfgp-dc-title', true ),
				'geo.region'	=> get_post_meta( $post->ID, 'cfgp-region', true ),
				'geo.placename'	=> get_post_meta( $post->ID, 'cfgp-placename', true ),
				'geo.latitude'	=> get_post_meta( $post->ID, 'cfgp-latitude', true ),
				'geo.longitude'	=> get_post_meta( $post->ID, 'cfgp-longitude', true ),
			);

			if( $geo_data['geo.enable'] )
			{
				if( !empty( $geo_data['geo.region'] ) && !empty( $geo_data['geo.placename'] ) )
				{
					printf( '<meta name="geo.region" content="%s-%s" />', $geo_data['geo.region'], $geo_data['geo.placename'] );
				}
				if( !empty( $geo_data['geo.address'] ) )
				{
					printf( '<meta name="geo.placename" content="%s" />', $geo_data['geo.address'] );
				}
				if( !empty( $geo_data['geo.longitude'] ) && !empty( $geo_data['geo.latitude'] ) )
				{
					printf( '<meta name="geo.position" content="%s;%s" />', $geo_data['geo.latitude'], $geo_data['geo.longitude'] );
					printf( '<meta name="ICBM" content="%s;%s" />', $geo_data['geo.latitude'], $geo_data['geo.longitude'] );
				}
			}
		}
	}
}
endif;