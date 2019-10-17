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
		
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if(isset($CF_GEOPLUGIN_OPTIONS['enable_cache']) ? $CF_GEOPLUGIN_OPTIONS['enable_cache'] : 0) {
			$this->add_action( 'wp_ajax_cfgeo_cache', 'ajax_fix_cache' );
			$this->add_action( 'wp_ajax_nopriv_cfgeo_cache', 'ajax_fix_cache' );
			
			$this->add_action( 'wp_ajax_cfgeo_banner_cache', 'ajax_fix_banner_cache' );
			$this->add_action( 'wp_ajax_nopriv_cfgeo_banner_cache', 'ajax_fix_banner_cache' );
			
			$this->add_filter( 'the_content', 'enable_cache' );
		}
	}
	
	public function enable_cache( $content ) {
		if(preg_match('/\[cfgeo(.*?)\]/i', $content))
		{
			$content = preg_replace('/\[cfgeo(.*?)\]/i','[cfgeo$1 cache]',$content);
		}
		return $content;
	}

	
	public function ajax_fix_cache(){
		if(wp_verify_nonce( $_REQUEST['cfgeo_nonce'], 'cfgeo-process-cache-ajax' ) !== false)
		{
			$CFGEO = $GLOBALS['CFGEO'];
			exit(json_encode($CFGEO));
		}
		wp_die();
	}
	
	public function ajax_fix_banner_cache(){
		if(wp_verify_nonce( $_REQUEST['cfgeo_nonce'], 'cfgeo-process-cache-ajax' ) !== false)
		{
			$html = isset($_REQUEST['post_html']) && !empty($_REQUEST['post_html']) ? trim( stripslashes( wp_filter_post_kses($_REQUEST['post_html']) ) ) : NULL;
			
			$attr = array(
				'id'				=>	isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0,
				'posts_per_page'	=>	isset($_REQUEST['post_posts_per_page']) ? intval($_REQUEST['post_posts_per_page'] ): 10,
				'class'				=>	isset($_REQUEST['post_class']) ? sanitize_html_class($_REQUEST['post_class']) : NULL
			);
			$attrs = array();
			
			foreach($attr as $a=>$b)
			{
				if($b !== '')
				{
					$attrs[] = $a . '="' . $b . '"'; 
				}
			}
			
			
			if($attrs){
				if($html)
					echo do_shortcode('[cfgeo_banner ' . join(' ', $attrs) . ']' . $html . '[/cfgeo_banner]');
				else
					echo do_shortcode('[cfgeo_banner ' . join(' ', $attrs) . ']');
			}

		}
		wp_die();
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
			wp_register_style( CFGP_NAME . '-public', CFGP_ASSETS . '/css/cf-geoplugin-public.css', array(), CFGP_VERSION );

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
	
	public function initialize_plugin_javascript(){
		$CFGEO = $GLOBALS['CFGEO']; ?>
<!-- <?php _e('CF Geoplugin JavaScript Plugin',CFGP_NAME); ?> -->
<script>
/* <![CDATA[ */
	window.wp = window.wp || {};
	window.wp.geo = window.wp.geo || {};
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
	window.wp.geo = window.cfgeo;
<?php if(defined('WP_CF_GEO_DEBUG') && WP_CF_GEO_DEBUG === true) : ?>
	console.log({'Geoplugin Header Load':window.wp.geo});
<?php endif; ?>
/* ]]> */
</script>

	<?php }

	public function cfgp_geo_tag()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$post = get_post();

		if($post && is_object($post) && isset( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) && in_array( (property_exists($post, 'post_type') ? $post->post_type : NULL), $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) )
		{
			$geo_data = apply_filters( 'cf_geoplugin_geo_tag_data', array(
				'geo.enable'	=> get_post_meta( $post->ID, 'cfgp-geotag-enable', true ),
				'geo.address' 	=> get_post_meta( $post->ID, 'cfgp-dc-title', true ),
				'geo.region'	=> get_post_meta( $post->ID, 'cfgp-region', true ),
				'geo.placename'	=> get_post_meta( $post->ID, 'cfgp-placename', true ),
				'geo.latitude'	=> get_post_meta( $post->ID, 'cfgp-latitude', true ),
				'geo.longitude'	=> get_post_meta( $post->ID, 'cfgp-longitude', true ),
			));

			if( $geo_data['geo.enable'] )
			{
				if( !empty( $geo_data['geo.region'] ) && !empty( $geo_data['geo.placename'] ) )
				{
					printf( '<meta name="geo.region" content="%s-%s" />', $geo_data['geo.region'], $geo_data['geo.placename'] );
				}
				if( !empty( $geo_data['geo.address'] ) )
				{
					printf( '<meta name="DC.title" content="%s" />', $geo_data['geo.address'] );
				}
				if( !empty( $geo_data['geo.placename'] ) )
				{
					printf( '<meta name="geo.placename" content="%s" />', $geo_data['geo.placename'] );
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