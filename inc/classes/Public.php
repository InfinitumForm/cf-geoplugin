<?php
/**
 * General Public functionality
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       8.0.0
 *
 */
 
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Public')) :
class CFGP_Public extends CFGP_Global{
	
	public function __construct(){
		if(is_admin()) return;
		
		if(CFGP_Options::get('enable_css', 0)){
			$this->add_action('wp_head', 'css_suppport', 1);
		}
		
		if(CFGP_Options::get('enable_js', 0) || is_admin()){
			$this->add_action('wp_head', 'javascript_support', 1);
		}
		
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
		
		$this->add_action('wp_loaded', 'output_buffer_start', 100);
		$this->add_action('shutdown', 'output_buffer_end', 100);
	}
	
	public function enqueue_scripts($page) {
		wp_enqueue_style( CFGP_NAME . '-public', CFGP_ASSETS . '/css/style-public.css', 1, (string)CFGP_VERSION );
		
		wp_enqueue_script( CFGP_NAME . '-public', CFGP_ASSETS . '/js/script-public.js', array('jquery'), (string)CFGP_VERSION );
		wp_localize_script(CFGP_NAME . '-public', 'CFGP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'cache' => (CFGP_Options::get('enable_cache', 0) ? '1' : '0')
		));
	}
	
	/*
	 * CSS Plugin support
	 * @verson    1.0.0
	 */
	public function css_suppport() {
		$CFGEO = CFGP_U::api();
		
		if(empty($CFGEO)) return;
		
		$css_show = $css_hide = array();
		
		$allowed_css = array(
			'country',
			'country_code',
			'region',
			'city',
			'continent',
			'continent_code',
			'currency',
			'base_currency'
		);

		foreach($CFGEO as $key=>$geo){
			if( empty($geo) || !in_array($key, apply_filters( 'cfgp/public/css/allowed', $allowed_css),true)!==false ) continue;
			$geo = sanitize_title($geo);
			$css_show[$geo]= '.cfgeo-show-in-' . $geo;
			$css_hide[$geo]= '.cfgeo-hide-from-' . $geo;
		}
		
		$css_show = apply_filters('cfgp/public/css/show', $css_show);
		$css_hide = apply_filters('cfgp/public/css/hide', $css_hide);

		if( !empty($css_show) ) :		
		?>
<!-- <?php _e('CF Geo Plugin CSS Classes', CFGP_NAME); ?> -->
<style media="all" id="cf-geoplugin-display-control" data-nonce="<?php echo wp_create_nonce( 'cfgeo-process-css-cache-ajax' ); ?>">*[class="cfgeo-show-in-"],*[class*="cfgeo-show-in-"],*[class^="cfgeo-show-in-"]{display: none;}<?php echo join(',', $css_hide); ?>{display:none !important;} <?php echo join(',', $css_show); ?>{display:block !important;}<?php do_action('cfgp/public/css'); ?></style>
		<?php endif;
	}
	
	/*
	 * JavaScript Plugin support
	 * @verson    1.0.0
	 */
	public function javascript_support() {
		$CFGEO = CFGP_U::api();
		if(empty($CFGEO)) return;
		?>
<!-- <?php _e('CF Geoplugin JavaScript Objects',CFGP_NAME); ?> -->
<script>
/* <![CDATA[ */
	window.wp = window.wp || {};
	window.wp.geo = window.wp.geo || {};
	if(typeof cf == 'undefined') var cf = {};
	cf.geoplugin = {url:window.location.href,host:window.location.hostname,protocol:window.location.protocol.replace(/\:/g,'')
	<?php

		$exclude = array_map('trim', apply_filters( 'cfgp/public/js/exclude', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,error,status,runtime,error_message')));
		$js = array();
		
		$CFGEO = array_merge($CFGEO,array(
			'flag' => ''
		));
		
		if( isset( $CFGEO['country_code'] ) && !empty( $CFGEO['country_code'] ) )
		{
			$CFGEO = array_merge($CFGEO,array(
				'flag' => CFGP_ASSETS . '/flags/4x3/'.strtolower($CFGEO['country_code']) . '.svg'
			));
		}
		
		foreach($CFGEO as $name=>$value)
		{
			if(in_array($name, $exclude, true) === false){
				$js[]=sprintf('%1$s:"%2$s"',$name,esc_attr($value));
			}
		}
		
		$js = apply_filters('cfgp/public/js/objects', $js);
		
		echo ',' . join(',', $js);
	?>}
	window.cfgeo = cf.geoplugin;
	window.wp.geo = window.cfgeo;
<?php if(defined('WP_CF_GEO_DEBUG') && WP_CF_GEO_DEBUG === true) : ?>
	console.log({' <?php esc_attr_e('CF Geoplugin JavaScript Objects',CFGP_NAME); ?>':window.wp.geo});
<?php endif; ?>
/* ]]> */
</script>

	<?php }
	
	// Output buffer start
	public function output_buffer_start() {
		ob_start(array(&$this, 'output_buffer_callback'), 0, PHP_OUTPUT_HANDLER_REMOVABLE);
	}

	// Output buffer end
	public function output_buffer_end() {
		ob_get_clean();
	}
	
	// Output buffer callback
	public function output_buffer_callback($content) {
		
		// Let's do a tags
		if($API = CFGP_U::api())
		{
			$remove_tags = array(
				'error',
				'error_message',
				'postcode'
			);
			foreach(apply_filters('cfgp/render/tags', $API) as $key => $value)
			{
				if(in_array($key, $remove_tags)) continue;
				$content = str_replace('%%'.$key.'%%', $value, $content);
			}
		}
		
		return $content;
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