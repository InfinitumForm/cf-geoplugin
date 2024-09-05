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

if(!class_exists('CFGP_Public', false)) : class CFGP_Public extends CFGP_Global{
	
	public function __construct(){
		if(CFGP_Options::get('enable_css', 0)){
			$this->add_action('wp_head', 'css_suppport', 1);
			$this->add_action('admin_head', 'css_suppport', 1);
			
			$this->add_action('wp_footer', 'css_js_suppport', 900);
			$this->add_action('admin_footer', 'css_js_suppport', 900);
			
			$this->add_action('wp_ajax_cfgp_display_control_css', array($this, 'get_generated_css'));
			$this->add_action('wp_ajax_nopriv_cfgp_display_control_css', array($this, 'get_generated_css'));
		}
		
		if(CFGP_Options::get('enable_js', 0)){
			$this->add_action('wp_head', 'javascript_support', 1);
			$this->add_action('admin_head', 'javascript_support', 1);
		}		

		if(is_admin()) return;
		
		if(CFGP_Options::get('hide_http_referrer_headers', 0)){
			$this->add_action('wp_head', 'hide_http_referrer_headers', 1);
		}
		
		$this->add_action('wp_head', 'append_geo_tags', 1);
		
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
		
		$this->add_action('wp_loaded', 'output_buffer_start', 100);
		$this->add_action('shutdown', 'output_buffer_end', 100);
	}
	
	/*
	 * Enqueue Scripts
	 * @verson    1.0.0
	 */
	public function enqueue_scripts($page) {
		$min = (CFGP_U::dev_mode() ? '' : '.min');
		// Public shortcode style
		wp_register_style( CFGP_NAME . '-public', CFGP_ASSETS . '/css/style-public' . $min . '.css', 1, (string)CFGP_VERSION );
		// Public currency converter shortcode
		wp_register_style( CFGP_NAME . '-public-cc', CFGP_ASSETS . '/css/style-cc' . $min . '.css', 1, (string)CFGP_VERSION );
		// Public Twism Script
		wp_register_script( CFGP_NAME . '-maps', CFGP_ASSETS . '/js/jquery.maps' . $min . '.js', array('jquery'), (string)CFGP_VERSION );
		wp_localize_script(CFGP_NAME . '-maps', 'CFGP_MAP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'cache' => (CFGP_Options::get('enable_cache', 0) ? '1' : '0'),
			'maps' => CFGP_ASSETS . '/maps'
		));
		// Public JavaScript for the shortcodes
		wp_register_script( CFGP_NAME . '-public', CFGP_ASSETS . '/js/script-public' . $min . '.js', array('jquery'), (string)CFGP_VERSION );
		wp_localize_script(CFGP_NAME . '-public', 'CFGP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'cache' => (CFGP_Options::get('enable_cache', 0) ? '1' : '0'),
			'cache_shortcode_url' => (CFGP_U::is_rest_enabled() ? get_rest_url( null, 'cf-geoplugin/v1/cache/shortcode/') : null),
			'cache_banner_url' => (CFGP_U::is_rest_enabled() ? get_rest_url( null, 'cf-geoplugin/v1/cache/banner/') : null),
			'rest_enabled' => (CFGP_U::is_rest_enabled() ? '1' : '0')
		));
		// Public Shortcode for the currency converter
		wp_register_script( CFGP_NAME . '-public-cc', CFGP_ASSETS . '/js/script-cc' . $min . '.js', array('jquery'), (string)CFGP_VERSION );
		wp_localize_script(CFGP_NAME . '-public-cc', 'CFGP_CC', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'loading_gif' => apply_filters( 'cfgp/public/js/loading_image_url', (CFGP_ASSETS . '/images/loading.gif'), (CFGP_ASSETS . '/images/loading.gif') )
		));
	}
	
	/*
	 * CSS Plugin support
	 * @verson    1.0.1
	 */
	public function css_suppport() {	
		?>
<!-- <?php esc_html_e('Geo Controller CSS Classes', 'cf-geoplugin'); ?> -->
<style media="all" id="cfgp-display-control-css" data-nonce="<?php echo esc_attr( wp_create_nonce( 'cfgeo-process-css-cache-ajax' ) ); ?>"><?php $this->get_generated_css(); ?></style>
	<?php }
	
	/*
	 * Get generated CSS code in HTML and AJAX
	 * @verson    1.0.0
	 */
	public function get_generated_css() {
		$is_ajax = ( sanitize_text_field($_REQUEST['action'] ?? '') == 'cfgp_display_control_css' );
		
		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		
		if(empty($CFGEO)) return;
		
		$css_show = $css_hide = [];
		
		$allowed_css = apply_filters( 'cfgp/public/css/allowed', array(
			'country',
			'country_code',
			'region',
			'city',
			'continent',
			'continent_code',
			'currency',
			'base_currency'
		));

		foreach($CFGEO as $key=>$geo){
			if( empty($geo) || !in_array($key, $allowed_css,true)!==false ) continue;
			$geo = sanitize_title($geo);
			$css_show[$geo]= '.cfgeo-show-in-' . $geo;
			$css_hide[$geo]= '.cfgeo-hide-from-' . $geo;
		}
		
		if( $CFGEO['is_tor'] ) {
			$css_show['is_tor']= '.cfgeo-show-in-tor';
			$css_hide['is_tor']= '.cfgeo-hide-from-tor';
		} else {
			$css_show['is_tor']= '.cfgeo-hide-from-tor';
			$css_hide['is_tor']= '.cfgeo-show-in-tor';
		}
		
		$css_show = apply_filters('cfgp/public/css/show', $css_show);
		$css_hide = apply_filters('cfgp/public/css/hide', $css_hide);

		ob_start('trim', 0, PHP_OUTPUT_HANDLER_REMOVABLE);

		if( !empty($css_show) ) :
		if( $is_ajax ) {
			header('Content-type: text/css', true);
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		}
?>*[class="cfgeo-show-in-"],*[class*="cfgeo-show-in-"],*[class^="cfgeo-show-in-"]{display: none;}<?php echo esc_attr( join(',', $css_hide) ); ?>{display:none !important;}<?php echo esc_attr( join(',', $css_show) ); ?>{display:block !important;}<?php do_action('cfgp/public/css'); ?><?php
		endif;
		
		$css = '';
		if (ob_get_level()) {
			$css = ob_get_contents();
			ob_end_clean();
		}
		
		echo apply_filters( 'cfgp/public/css', $css, $css_show, $css_hide, $allowed_css);
		
		if( $is_ajax ) {
			exit;
		}
	}
	
	/*
	 * Load CSS via AJAX for cache purposes
	 * @verson    1.0.0
	 */
	public function css_js_suppport () { ?>
<script>
/* <![CDATA[ */
(function(doc){
	async function cfgp_display_control_css () {
		var css = doc.getElementById('cfgp-display-control-css'),
			css_original = css.innerHTML;
		if( css ) {
			const response = await fetch('<?php echo esc_url( admin_url('/admin-ajax.php') ); ?>', {
				method: 'POST',
				headers: {
					'Cache-Control': 'no-cache, no-store, must-revalidate', 
					'Pragma': 'no-cache', 
					'Expires': '0'
				},
				body: new URLSearchParams({
					action: 'cfgp_display_control_css',
					nonce: css.dataset.nonce
				})
			}).catch((error) => {
				console.error('<?php
					echo esc_attr( esc_html__('The geo controller could not update the CSS.', 'cf-geoplugin') );
				?>');
				
				setTimeout(function(){
					css.innerHTML = css_original;
					css.ajaxError = true;
				}, 10);
				
				return;
			});
			
			css.innerHTML = await response.text();
			css.ajaxError = false;
		}
		return css;
	}
	cfgp_display_control_css().then(function(data){
		if( data.ajaxError ) {
			data.dataset.ajax='error';
		} else {
			data.dataset.ajax='loaded';
		}
	});
}(document));
/* ]]> */
</script>
	<?php }
	
	/*
	 * Hide HTTP referrer
	 * @verson    1.0.0
	 */
	public function hide_http_referrer_headers(){ ?><meta name="referrer" content="no-referrer"/><?php }
	
	/*
	 * JavaScript Plugin support
	 * @verson    1.0.0
	 */
	public function javascript_support() {
		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		if(empty($CFGEO)) return;
		?>
<!-- <?php esc_html_e('CF Geoplugin JavaScript Objects', 'cf-geoplugin'); ?> -->
<script id="cfgp-display-control-js" type="text/javascript">
/* <![CDATA[ */
	window.wp = window.wp || {};
	window.wp.geo = window.wp.geo || {};
	if(typeof cf == 'undefined') var cf = {};
	cf.geoplugin = {url:window.location.href,host:window.location.hostname,protocol:window.location.protocol.replace(/\:/g,'')
	<?php

		$exclude = array_map('trim', apply_filters( 'cfgp/public/js/exclude', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,error,status,runtime,error_message')));
		$js = [];
		
		$CFGEO = array_merge($CFGEO,array(
			'flag' => '',
			'key' => CFGP_U::KEY()
		));
		
		if( isset( $CFGEO['country_code'] ) && !empty( $CFGEO['country_code'] ) )
		{
			$CFGEO = array_merge($CFGEO,array(
				'flag' => apply_filters(
					'cfgp/javascript_support/flag', 
					CFGP_ASSETS . '/flags/4x3/'.strtolower($CFGEO['country_code']) . '.svg',
					array(
						'root' => CFGP_ASSETS . '/flags',
						'path' => CFGP_ASSETS . '/flags/4x3',
						'file' => strtolower($CFGEO['country_code']) . '.svg',
						'filename' => strtolower($CFGEO['country_code']),
						'extension' => 'svg',
						'url' => CFGP_ASSETS . '/flags/4x3/'.strtolower($CFGEO['country_code']) . '.svg'
					)
				)
			));
		}
		
		foreach($CFGEO as $name=>$value)
		{
			if(in_array($name, $exclude, true) === false){
				$js[]=sprintf('%1$s:"%2$s"', $name, esc_attr($value));
			}
		}
		
		$js = apply_filters('cfgp/public/js/objects', $js);
		
		echo wp_kses_post(',' . join(',', $js));
	?>}
	window.cfgeo = cf.geoplugin;
	window.wp.geo = window.cfgeo;
<?php if(defined('WP_CF_GEO_DEBUG') && WP_CF_GEO_DEBUG === true) : ?>
	console.log({' <?php esc_attr_e('CF Geoplugin JavaScript Objects', 'cf-geoplugin'); ?>':window.wp.geo});
<?php endif; ?>
/* ]]> */
</script>
	<?php }
	
	/*
	 * Add Geo Tag
	 * @verson    2.0.0
	 */
	public function append_geo_tags() {
		$post = get_post();

		if($post && is_object($post) && in_array($post->post_type, CFGP_Options::get('enable_geo_tag', [])))
		{
			$geo_data = apply_filters( 'cfgp/public/geo_tags', array(
				'geo.enable'	=> get_post_meta( $post->ID, 'cfgp-geotag-enable',	true ),
				'geo.address' 	=> get_post_meta( $post->ID, 'cfgp-dc-title',		true ),
				'geo.region'	=> get_post_meta( $post->ID, 'cfgp-region',			true ),
				'geo.placename'	=> get_post_meta( $post->ID, 'cfgp-placename',		true ),
				'geo.latitude'	=> get_post_meta( $post->ID, 'cfgp-latitude',		true ),
				'geo.longitude'	=> get_post_meta( $post->ID, 'cfgp-longitude',		true )
			), $post);

			$enable = $geo_data['geo.enable'];
			
			unset($geo_data['geo.enable']);
			
			$geo_data = array_map('sanitize_text_field', $geo_data);

			if( $enable )
			{
				if( !empty( $geo_data['geo.region'] ) && !empty( $geo_data['geo.placename'] ) )
				{
					printf(
						'<meta name="geo.region" content="%s-%s" />' . PHP_EOL,
						esc_attr($geo_data['geo.region']),
						esc_attr($geo_data['geo.placename'])
					);
				}
				if( !empty( $geo_data['geo.address'] ) )
				{
					printf(
						'<meta name="DC.title" content="%s" />' . PHP_EOL,
						esc_attr($geo_data['geo.address'])
					);
				}
				if( !empty( $geo_data['geo.placename'] ) )
				{
					printf(
						'<meta name="geo.placename" content="%s" />' . PHP_EOL,
						esc_attr($geo_data['geo.placename'])
					);
				}
				if( !empty( $geo_data['geo.longitude'] ) && !empty( $geo_data['geo.latitude'] ) )
				{
					printf(
						'<meta name="geo.position" content="%s;%s" />' . PHP_EOL,
						esc_attr($geo_data['geo.latitude']),
						esc_attr($geo_data['geo.longitude'])
					);
					
					printf(
						'<meta name="ICBM" content="%s;%s" />' . PHP_EOL,
						esc_attr($geo_data['geo.latitude']),
						esc_attr($geo_data['geo.longitude'])
					);
				}
			}
		}
	}
	
	/*
	 * Output buffer start
	 * @verson    1.0.0
	 */
	public function output_buffer_start() {
		ob_start(array(&$this, 'output_buffer_callback'), 0, PHP_OUTPUT_HANDLER_REMOVABLE);
	}

	/*
	 * Output buffer end
	 * @verson    1.0.0
	 */
	public function output_buffer_end() {
		ob_get_clean();
	}
	
	/*
	 * Output buffer callback
	 * @verson    1.0.0
	 */
	public function output_buffer_callback($content) {
		
		// Let's do a tags
		if($API = CFGP_U::api(false, CFGP_Defaults::API_RETURN))
		{
			$remove_tags = array(
				'error',
				'error_message',
				'postcode'
			);
			foreach(apply_filters('cfgp/render/tags', $API) as $key => $value)
			{
				if(in_array($key, $remove_tags)) continue;
				$content = str_replace('%%'.$key.'%%', $value??'', $content);
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