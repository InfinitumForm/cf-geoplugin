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
	}
	
	public function run_style(){
		$this->add_action( 'wp_enqueue_scripts', 'register_style' );
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
		$CFGEO = array_merge($CFGEO,array(
			'flag' => CFGP_ASSETS.'/flags/4x3/'.strtolower($CFGEO['country_code']).'.svg'
		));
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
}
endif;