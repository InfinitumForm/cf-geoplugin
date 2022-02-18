<?php
/**
 * Shortcodes
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes
 */
if(!class_exists('CFGP_Shortcodes')) :
class CFGP_Shortcodes extends CFGP_Global {
	function __construct(){
		// Standard shortcode
		$this->add_shortcode('cfgeo', 'cf_geoplugin');
		$this->add_shortcode('cfgeo_flag', 'generate_flag');
		
		// Deprecated shortcodes
		if( defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS ) {
			$this->add_shortcode('cf_geo', 'cf_geoplugin');
			$this->add_shortcode('cf_geo_flag', 'generate_flag');
		}
		
		// Google Map shortcode
		if( CFGP_Options::get('enable_gmap', 0) ) {
			// Official Google Map Shortcode
			$this->add_shortcode( 'cfgeo_map', 'google_map' );
			// Deprecated shortcode
			if( defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS ) {
				$this->add_shortcode( 'cf_geo_map', 'google_map' );
			}
		}
		
		// Geo Banner
		if( CFGP_Options::get('enable_banner', 0) ) {
			// Official Geo Banner Shortcode
			$this->add_shortcode( 'cfgeo_banner', 'geo_banner' );
		}
		
		// Currency converter
		$this->add_shortcode( 'cfgeo_converter', 'cfgeo_converter' );
		
		// Full converter shortcode
		$this->add_shortcode( 'cfgeo_full_converter', 'cfgeo_full_converter' );
		$this->add_action( 'wp_ajax_cfgeo_full_currency_converter', 'ajax__cfgeo_full_currency_converter' );
		
		// Escape shortcodes
		$this->add_shortcode( 'escape_shortcode', 'cfgeo_escape_shortcode' );

		// IS VAT
		$this->add_shortcode( 'cfgeo_is_vat', 'is_vat' );

		// IS NOT VAT
		$this->add_shortcode( 'cfgeo_is_not_vat', 'is_not_vat' );
		
		// IN EU
		$this->add_shortcode( 'cfgeo_in_eu', 'in_eu' );
		
		// NOT IN EU
		$this->add_shortcode( 'cfgeo_not_in_eu', 'not_in_eu' );
		
		// IS PROXY
		$this->add_shortcode( 'cfgeo_is_proxy', 'is_proxy' );
		
		// IS NOT PROXY
		$this->add_shortcode( 'cfgeo_is_not_proxy', 'is_not_proxy' );
		
		// GPS
		$this->add_shortcode( 'cfgeo_gps', 'cfgeo_gps' );
		
		// Beta shortcodes
		if(CFGP_Options::get_beta('enable_simple_shortcode')) {
			$this->add_action( 'wp_loaded', 'shortcode_automat_setup' );
			
			$this->add_shortcode( 'geo',          'cf_geoplugin' );
			$this->add_shortcode( 'country_flag', 'generate_flag' );
			$this->add_shortcode( 'is_vat',       'is_vat' );
			$this->add_shortcode( 'is_not_vat',   'is_not_vat' );
			$this->add_shortcode( 'in_eu',        'in_eu' );
			$this->add_shortcode( 'not_in_eu',    'not_in_eu' );
			$this->add_shortcode( 'is_proxy',     'is_proxy' );
			$this->add_shortcode( 'is_not_proxy', 'is_not_proxy' );
			$this->add_shortcode( 'gps',          'cfgeo_gps' );
		}
		
		
		// AJAX - Fix shortcode cache
		$this->add_action('wp_ajax_cf_geoplugin_shortcode_cache', 'ajax__shortcode_cache');
		$this->add_action('wp_ajax_nopriv_cf_geoplugin_shortcode_cache', 'ajax__shortcode_cache');
		
	}
	
	/**
	 * Escape shortcodes for the internal docummentation purposes
	 *
	 * @since      7.4.3
	 * @version    7.4.3
	*/
	public function cfgeo_escape_shortcode($attr, $content='', $tag){
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		if(!empty($content)){
			$content = preg_replace('%\[(.*?)\]%i','&lsqb;$1&rsqb;',$content);
		}
		
		return self::__cache($tag, $content, (array)$attr, $content, $cache);
	}
	
	
	/**
	 * Main CF GeoPlugin Shortcode
	 *
	 * @since      1.0.0
	 * @version    7.0.0
	*/
	public function cf_geoplugin($atts, $content='', $tag)
	{		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $atts) ? true : false);
		
		$array = shortcode_atts( array(
			'return' 	=>  'ip',
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $atts );

		$return 	= $array['return'];
		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if($cache){
			wp_enqueue_style( CFGP_NAME . '-public' );
			wp_enqueue_script( CFGP_NAME . '-public' );
		}
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
			if (CFGP_Options::get('enable_dns_lookup', 0)) {
				$CFGEO = array_merge($CFGEO, CFGP_API::instance(true)->get('dns', $ip));
			}
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($content))
		{
			$content = trim($content);
			
			// Include/ Exclude functionality for the content
			if(!empty($exclude) || !empty($include)) {
				// Include
				if(!empty($include))
				{
					if(CFGP_U::recursive_array_search($include, $CFGEO, $relative_match))
					{
						return self::__cache($tag, do_shortcode($content), (array)$array, $content, $cache);
					}
					else
					{
						return self::__cache($tag, $default, (array)$array, $content, $cache);
					}
				}
				// Exclude
				if(!empty($exclude))
				{
					if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match))
					{
						return self::__cache($tag, $default, (array)$array, $content, $cache);
					}
					else
					{
						return self::__cache($tag, do_shortcode($content), (array)$array, $content, $cache);
					}
				}
			}
			else
			{
				return CFGP_U::fragment_caching(__('CF GEOPLUGIN NOTICE: -Please define "include" or "exclude" attributes inside your shortcode on this shortcode mode.', CFGP_NAME), $cache);
			}
		}
		else
		{
			// Include/ Exclude functionality for the geo informations
			if(!empty($exclude) || !empty($include)) {
				// Include
				if(!empty($include))
				{
					if(CFGP_U::recursive_array_search($include, $CFGEO, $relative_match))
					{
						if(isset($CFGEO[$return]))
						{
							return self::__cache($tag, $CFGEO[$return], (array)$array, $content, $cache);
						}
					}
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
				// Exclude
				if(!empty($exclude))
				{
					if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match))
					{
						return self::__cache($tag, $default, (array)$array, $content, $cache);
					}
					else
					{
						if(isset($CFGEO[$return]))
						{
							return self::__cache($tag, $CFGEO[$return], (array)$array, $content, $cache);
						}
						else
						{
							return self::__cache($tag, $default, (array)$array, $content, $cache);
						}
					}
				}
			}
		}
		
		// Return geo information
		if(isset($CFGEO[$return]))
		{
			return self::__cache($tag, $CFGEO[$return], (array)$array, $content, $cache);
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	/**
	 * EXPERIMENTAL Generate Shortcodes by type
	 *
	 * @since    7.0.0
	 */
	public function shortcode_automat_setup($atts){
		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);

		if(empty($CFGEO)){
			return;
		}
		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		if($cache){
			wp_enqueue_style( CFGP_NAME . '-public' );
			wp_enqueue_script( CFGP_NAME . '-public' );
		}
		
		$exclude = array_map('trim', explode(',','gps,is_vat,is_proxy,is_mobile,in_eu,state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter'));
		
		$generate=array();
		foreach($CFGEO as $key => $value )
		{
			if(in_array($key, $exclude, true) === false)
			{
				$generate['cfgeo_' . $key] = self::__cache('cfgeo_' . $key, $value, $atts, '', $cache);
			}
		}
		
		$sc = new CFGP_Shortcodes_Automat( $generate );
		$sc->generate();
	}
	
	/**
	 * CF Geo Flag Shortcode
	 *
	 * @since    4.3.0
	 */
	public function generate_flag( $atts, $content='', $tag ){
		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $atts) ? true : false);
		
		wp_enqueue_style( CFGP_NAME . '-flag' );
		
		if($cache){
			wp_enqueue_style( CFGP_NAME . '-public' );
			wp_enqueue_script( CFGP_NAME . '-public' );
		}
		
		$img_format = (CFGP_U::is_attribute_exists('img', $atts) || CFGP_U::is_attribute_exists('image', $atts) ? true : false);
		
		$arg = shortcode_atts( array(
			'size' 		=>  '128',
			'type' 		=>  0,
			'ip' 		=>  false,
			'id' 		=>  false,
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	CFGP_U::api('country_code'),
			'exclude'	=>	false,
			'include'	=>	false,
        ), $atts );
		
		if($img_format && $cache) {
			$arg = array_merge($arg, array('img'));
		}
		
		$exclude 	= $arg['exclude'];
		$include 	= $arg['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
			if (CFGP_Options::get('enable_dns_lookup', 0)) {
				$CFGEO = array_merge($CFGEO, CFGP_API::instance(true)->get('dns', $ip));
			}
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, '', (array)$arg, '', $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, '', (array)$arg, '', $cache);
				}
			}
		}
		
		if(empty($arg['id']))
			$id = 'cf-geo-flag-' . CFGP_U::generate_token(10);
		else
			$id = $arg['id'];
		
		if(strpos($arg['size'], '%')!==false || strpos($arg['size'], 'in')!==false || strpos($arg['size'], 'pt')!==false || strpos($arg['size'], 'em')!==false)
			$size = $arg['size'];
		else
			$size = str_replace('px','',$arg['size']).'px';
		
		if((int)$arg['type']>0)
			$type=' flag-icon-squared';
		else
			$type='';
		
		$flag = trim(strtolower($arg['country']));
		
		if($img_format===true){
			if(strpos($arg['css'], 'max-width') === false) $arg['css'].=' max-width:'.$size;
		}else{
			if(strpos($arg['css'], 'font-size') === false) $arg['css'].=' font-size:'.$size;
		}
		
		if( !empty($arg['css']) ){
			$css = NULL;

			$ss = array();
			$csss = array_map('trim',explode(';', $arg['css']));
			foreach($csss as $val){
				if(!empty($val)){
					$val = array_map('trim',explode(':', $val));
					if(isset($val[1])) $ss[$val[0]]=$val[1];
				}
			}
			
			if(count($ss)>0)
			{
				$scss = array();
				foreach($ss as $key=>$val) $scss[]=sprintf('%s:%s',$key,$val); 
				$css = join(';',$scss);
			}
			
		}
		else
			$css='';
		
		if( !empty($arg['class']) ){
			$classes = explode(" ", $arg['class']);
			$cc = array();
			foreach($classes as $val){
				if(!empty($val)) $cc[]=$val;
			}
			if(count($cc)>0)
				$class=' '.join(" ", $cc);
			else
				$class='';
		} else $class='';

		if($img_format===true)
		{
			$address = $CFGEO['address'];
			if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag.'.svg')) {
				return self::__cache(
					$tag,
					sprintf(
						'<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="%s">',
						CFGP_ASSETS.'/flags/4x3/' . $flag . '.svg',
						$address,
						$address,
						$size,
						$css,
						$class,
						$id
					),
					(array)$arg,
					'',
					$cache
				);
			} else {
				return self::__cache($tag, '', (array)$arg, '', $cache);
			}
		} else {
			return self::__cache(
				$tag,
				sprintf(
					'<span class="flag-icon flag-icon-%s%s" id="%s"%s></span>',
					$flag.$type,
					$class,
					$id,
					(!empty($css) ? ' style="'.$css.'"' : '')
				),
				(array)$arg,
				'',
				$cache
			);
		}
	}
	
	/**
	 * Geo Banner Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function geo_banner( $setup, $cont='', $tag )
	{
		global $wpdb;
		
		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		
		// Cache control
		$cache = CFGP_U::is_attribute_exists('cache', $setup);
		if(CFGP_Options::get('enable_cache', 0)){
			$cache = true;
		}
		if(CFGP_U::is_attribute_exists('no_cache', $setup)) {
			$cache = false;
		}
		if($cache) {
			wp_enqueue_style( CFGP_NAME . '-public' );
			wp_enqueue_script( CFGP_NAME . '-public' );
		}
		
		// Exact controls
		$exact = true;
		if(CFGP_U::is_attribute_exists('relative', $setup)) {
			$exact = false;
		}
		if(CFGP_U::is_attribute_exists('any', $setup)) {
			$exact = false;
		}
		if(CFGP_U::is_attribute_exists('exact', $setup)) {
			$exact = true;
		}
		
		$ID = CFGP_U::generate_token(16); // Let's made this realy hard
	
		$setup = shortcode_atts(array(
			'id'				=>	-1,
			'posts_per_page'	=>	1,
			'class'				=>	''
		), $setup);
		
		// Stop if ID is not good
		if( ! (intval($setup['id']) > 0) ) {
			return $cont;
		} else {
			$setup['id'] = absint($setup['id']);
		}
		
		// Get defaults
		if( empty($cont) ) {
			$banner_default = get_post_meta( $setup['id'], 'cfgp-banner-default', true ); 
			
			if( !$banner_default ) {
				$banner_default = get_post_meta( $setup['id'], CFGP_METABOX . 'banner_default', true );
			}
			
			if( $banner_default ){
				$cont = $banner_default;
			}
		}
		
		$class		= sanitize_html_class($setup['class']);
		$classes	= (empty($class) ? array() : array_map('trim',explode(' ', $class)));
		$classes[]	= 'cf-geoplugin-banner';
		
		if($cache != false){
			$classes[] = 'cache';
		}
		
		$posts_per_page = absint($setup['posts_per_page']);
		
		$country = CFGP_U::api('country_code');
		$country_sql = '%"' . esc_sql($country) . '"%';
		
		$region = CFGP_U::api('region');
		$region_sql = '%"' . esc_sql($region) . '"%';
		
		$city = CFGP_U::api('city');
		$city_sql = '%"' . esc_sql($city) . '"%';

		$post = $wpdb->get_row( $wpdb->prepare("
SELECT
	`banner`.`ID`,
	`banner`.`post_title`,
	`banner`.`post_content`
FROM
	`{$wpdb->posts}` AS `banner`
WHERE
	`banner`.`ID` = %d
AND
	`banner`.`post_type` = 'cf-geoplugin-banner'
AND
	`post_status` = 'publish'
AND
	IF(
		EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country' AND `c`.`meta_value` LIKE %s),
        1
    )
AND
	IF(
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region' AND `r`.`meta_value` LIKE %s),
        1
    )
AND
	IF(
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city'),
        EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city' AND `s`.`meta_value` LIKE %s),
        1
    )
LIMIT 1
		",
		absint($setup['id']),
		$country_sql,
		$region_sql,
		$city_sql
		) );
		
		// Let's list it
		$save = NULL;
		if( $post ) {
			$post->post_content = do_shortcode($post->post_content);
			$post->post_content = apply_filters('the_content', $post->post_content);
			
			$save='<div id="cf-geoplugin-banner-'.$post->ID.'" class="'.join(' ',get_post_class($classes, $post->ID)).' cf-geoplugin-banner-'.$post->ID.'"'
			
				. ($cache ? ' data-id="' . $post->ID . '"' : '')
				. ($cache ? ' data-posts_per_page="' . esc_attr($posts_per_page) . '"' : '')
				. ($cache ? ' data-class="' . esc_attr($class) . '"' : '')
				. ($cache ? ' data-exact="' . ($exact ? 1 : 0) . '"' : '')
				. ($cache ? ' data-default="' . esc_attr(base64_encode(urlencode($cont))) . '"' : '')
			
			. '>' . $post->post_content . '</div>';
			
			$classes = NULL;
		}
		
		// Return banner
		if(!empty($save)){
			return CFGP_U::fragment_caching($save, $cache);
		}
		
		// Format defaults
		if(!empty($cont)) {
			$cont = do_shortcode($cont);
			$cont = apply_filters('the_content', $cont);
		}
		
		// Return defaults
		return CFGP_U::fragment_caching(
			'<div id="cf-geoplugin-banner-'.$setup['id'].'" class="'.join(' ',get_post_class($classes, $setup['id'])).' cf-geoplugin-banner-'.$setup['id'].'"'
			
				. ($cache ? ' data-id="' . $setup['id'] . '"' : '')
				. ($cache ? ' data-posts_per_page="' . esc_attr($posts_per_page) . '"' : '')
				. ($cache ? ' data-class="' . esc_attr($class) . '"' : '')
				. ($cache ? ' data-exact="' . ($exact ? 1 : 0) . '"' : '')
				. ($cache ? ' data-default="' . esc_attr(base64_encode(urlencode($cont))) . '"' : '')
			
			. '>' . $cont . '</div>',
			$cache
		);
	}

	
	/**
	 * Google Map Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function google_map( $atts, $content = '', $tag )
	{
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		$att = (object)shortcode_atts( array( 
			'latitude'				=>	CFGP_Options::get('map_latitude', CFGP_U::api('latitude')),
			'longitude'				=> 	CFGP_Options::get('map_longitude', CFGP_U::api('longitude')),
			
			'zoom'					=>	CFGP_Options::get('map_zoom'),
			'width'					=>	CFGP_Options::get('map_width'),
			'height'				=> 	CFGP_Options::get('map_height'),

			'scrollwheel'			=>	CFGP_Options::get('map_scrollwheel'),
			'navigationControl'		=>	CFGP_Options::get('map_navigationControl'),
			'mapTypeControl'		=>	CFGP_Options::get('map_mapTypeControl'),
			'scaleControl'			=>	CFGP_Options::get('map_scaleControl'),
			'draggable'				=>	CFGP_Options::get('map_draggable'),
			
			'infoMaxWidth'			=>	CFGP_Options::get('map_infoMaxWidth'),

			'title'					=>	CFGP_U::api('address'),
			'address'				=>	'',
			'pointer'				=>  '',
		), $atts );
		
		

		$content = trim($content);
		
		$attributes = array();
		$attributes[]='data-zoom="'.esc_attr($att->zoom).'"';
		$attributes[]='data-draggable="'.esc_attr($att->draggable).'"';
		$attributes[]='data-scaleControl="'.esc_attr($att->scaleControl).'"';
		$attributes[]='data-mapTypeControl="'.esc_attr($att->mapTypeControl).'"';
		$attributes[]='data-navigationControl="'.esc_attr($att->navigationControl).'"';
		$attributes[]='data-scrollwheel="'.esc_attr($att->scrollwheel).'"';
		$attributes[]='data-lat="'.esc_attr(!empty($att->lat)?$att->lat:$att->latitude).'"';
		$attributes[]='data-lng="'.esc_attr(!empty($att->lng)?$att->lng:$att->longitude).'"';
		
		if(!empty($att->title))		$attributes[]='data-title="'.esc_attr($att->title).'"';
		if(!empty($att->address))	$attributes[]='data-address="'.esc_attr($att->address).'"';
		if(!empty($att->pointer))	$attributes[]='data-pointer="'.esc_attr($att->pointer).'"';
		if(!empty($content))		$attributes[]='data-infoMaxWidth="'.esc_attr($att->infoMaxWidth).'"';
		if(!empty($att->locations))	$attributes[]='data-locations="'.esc_attr($att->locations).'"';
		
		$this->add_action( 'wp_footer', 'google_map_shortcode_script' );
		$this->add_action( 'admin_footer', 'google_map_shortcode_script' );

		return CFGP_U::fragment_caching('<div class="CF_GeoPlugin_Google_Map_Shortcode" style="width:'.esc_attr($att->width).'; height:'.esc_attr($att->height).'"'.join(' ', $attributes).'>'.do_shortcode($content).'</div>', $cache);
	}
	
	/*
	* GOOGLE MAP SCRIPT - SHORTCODE PART
	* @author Ivijan-Stefan Stipic
	**/
	public function google_map_shortcode_script() { ?>
	<script>
	/**
	* GOOGLE MAP SCRIPT
	* @author Ivijan-Stefan Stipic
	**/
	function CF_GeoPlugin_Google_Map_Shortcode()
	{
		// init
		var MAP = {
				init : [],
				marker : [],
				infoWindow : []
			},
			initMaps = document.getElementsByClassName('CF_GeoPlugin_Google_Map_Shortcode'), i, e;
			
		for(i=0; i<initMaps.length; i++)
		{
			// Main initializations for the map and the setup
			var init = initMaps[i],
				content = (initMaps[i].innerHTML!='' ? initMaps[i].innerHTML : false),
				classes = initMaps[i].className,
				target = {
					lat: (typeof init.dataset.lat != 'undefined' ? parseFloat(init.dataset.lat) : 0.0),
					lng: (typeof init.dataset.lng != 'undefined' ? parseFloat(init.dataset.lng) : 0.0)
				},
				options = {
					center: target,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
			
			// Empty div before map is builded
			if(content)
			{
				initMaps[i].innerHTML = '';
			}
			
			// Add active statemant to map
			initMaps[i].className = classes.concat(' active');
			
			// Collect all "data-" attributes
			for(option in init.dataset)
			{
				if(['zoom', 'draggable', 'scrollwheel', 'navigationControl', 'mapTypeControl', 'scaleControl'].indexOf(option) > -1)
				{
					if(parseInt(init.dataset[option]) == init.dataset[option]){
						if('zoom' == option)
							options[option] = parseInt(init.dataset[option]);
						else
							options[option] = (parseInt(init.dataset[option]) === 1 ? true : false);
					} else if(parseFloat(init.dataset[option]) == init.dataset[option]){
						options[option] = parseFloat(init.dataset[option]);
					} else {
						options[option] = init.dataset[option];
					}
				}
			}

			// Build and call Google Map
			MAP.init[i] = new google.maps.Map(init, options);
			
			// Add multi locations
			if(typeof init.dataset.locations != 'undefined'){
				var getLocations = init.dataset.locations.split('|'), a, collectLocations=[];
				if(getLocations){
					for(a = 0; a < getLocations.length; a++){
						var cp = getLocations[a].split(',');

						collectLocations[a] = [cp[0], parseFloat(cp[1]), parseFloat(cp[2])];
					}
				}
			}

			// Initialize markers and other informations
			var markerOptions = {
				position: target,
				map: MAP.init[i],
				animation: google.maps.Animation.DROP
			};
			
			// Put custom pointer
			if(typeof init.dataset.pointer != 'undefined'){
				markerOptions.icon = {
					url : init.dataset.pointer,
					labelOrigin: new google.maps.Point(20, 50),
					size: new google.maps.Size(40, 40),
					origin: new google.maps.Point(0, 0),
					anchor: new google.maps.Point(20, 40),
					class : "cf-geoplugin-google-map-icon"
				};
			}
			
			// Put custom title
			if(typeof init.dataset.title != 'undefined'){
				markerOptions.title = init.dataset.title;
			}
			// Set address
			if(typeof init.dataset.address != 'undefined'){
				markerOptions.label = {
					color : '#cc0000',
					fontWeight: 'bold',
					text : init.dataset.address,
					class : "cf-geoplugin-google-map-labels"
				};
			}

			// Create marker
			MAP.marker[i]= new google.maps.Marker(markerOptions);

			// Open popup if data exists
			if(content)
			{
				MAP.infoWindow[i]= new google.maps.InfoWindow({
					content: content,
					maxWidth: init.dataset.infoMaxWidth
				});
			}
		}
		
		// Let's collect all and put into addListener for the actions
		for(e = 0; e < MAP.infoWindow.length; e++)
		{
			(function(index) {
				MAP.marker[index].addListener("click", function() {
					MAP.infoWindow[index].open(MAP.init[index], MAP.marker[index]);
				});
			})(e);
		}
	}

	(function(position, callback){
		
		if( typeof google != 'undefined' )
		{
			if(typeof callback == 'function') {
				callback(google,{});
			}
		}
		else
		{
			var url = '//maps.googleapis.com/maps/api/js?key=<?php echo CFGP_Options::get('map_api_key'); ?>',
				head = document.getElementsByTagName('head')[0],
				script = document.createElement("script");
			
			position = position || 0;
			
			script.src = url + (typeof CF_GeoPlugin_Google_Map_GeoTag != 'undefined' ? '&libraries=places' : ''); /* One of the Gutenberg BUG fixing */
			script.type = 'text/javascript';
			script.charset = 'UTF-8';
			script.async = true;
			script.defer = true;
			head.appendChild(script);
			head.insertBefore(script,head.childNodes[position]);		
			script.onload = function(){
				if(typeof callback == 'function') {
					callback(google, script);
				}
			};
			script.onerror = function(){
				if(typeof callback == 'function') {
					callback(undefined, script);
				}
			};
		}
	}(0, function($this){
		if( typeof $this != 'undefined' ){
			$this.maps.event.addDomListener(window, 'load', CF_GeoPlugin_Google_Map_Shortcode);
			/* One of the Gutenberg BUG fixing */
			if(typeof CF_GeoPlugin_Google_Map_GeoTag != 'undefined') $this.maps.event.addDomListener(window, 'load', CF_GeoPlugin_Google_Map_GeoTag);
		}
	}));
	</script>
	<?php }
	
	/**
	 * Converter shortcode
	 * 
	 * @since 7.4.0
	 */
	public function cfgeo_converter( $atts, $content = '', $tag )
	{
		if( empty( $content ) ) return '';
		
		if(CFGP_U::api('currency_converter', 0) == 0) return $content;
		
		$atts = shortcode_atts(
			array(
				'from'	=> (CFGP_Options::get('base_currency') ? strtoupper( CFGP_Options::get('base_currency') ) : 'USD'),
				'to'	=> (CFGP_U::api('currency') ? strtoupper( CFGP_U::api('currency') ) : 'USD'),
				'align'	=> 'R',
				'separator'	=> '',
				'no-symbol' => 0,
				'auto' => 0
			), 
			$atts, 
			'cfgeo_converter'
		);
		$symbols = CFGP_Defaults::CURRENCY_SYMBOL;
		$find_symbol = preg_replace('%([^a-zA-Z]+)%i','',$content);

		$from = strtoupper( $atts['from'] );
		if(!empty($find_symbol))
		{
			$find_symbol = strtoupper( $find_symbol );
			if(isset($symbols[ $find_symbol ]))
			{
				$from = strtoupper( $find_symbol );
			}
		}

		$to = strtoupper( $atts['to'] );
		
		$atts['align'] = strtoupper( $atts['align'] );
		if( !isset( $symbols[ $from ] ) || !isset( $symbols[ $to ] ) ) return $content;

		if(function_exists('mb_convert_encoding'))
		{
			$symbol_from = mb_convert_encoding( $symbols[ $from ], 'UTF-8' );
			$symbol_to = mb_convert_encoding( $symbols[ $to ], 'UTF-8' );
		}
		else
		{
			$symbol_from = CFGP_U::mb_convert_encoding( $symbols[ $from ] );
			$symbol_to = CFGP_U::mb_convert_encoding( $symbols[ $to ] );
		}
		$content = filter_var( $content, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		if( $from === $to )
		{
			return CFGP_U::generate_converter_output( $content, $symbol_to, $atts['align'], $atts['separator'] );
		}
		
		if( CFGP_Options::get('base_currency') && CFGP_U::api('currency') && CFGP_U::api('currency_converter') && strtoupper( CFGP_Options::get('base_currency') ) == $from && CFGP_U::api('currency') == $to )
		{
			if(preg_match('/([0-9\.\,]+)/i',$content, $match))
			{
				$match[0] = strtr($match[0],',','.');
				$amount = floatval($match[0]);
				$currency_converter = CFGP_U::api('currency_converter');
				
				if(empty($currency_converter) || !is_numeric($currency_converter)){
					$content = number_format($content, 2);
					if($atts['no-symbol'] == 1)
					{
						return CFGP_U::generate_converter_output( $content, '', $atts['align'], $atts['separator'] );
					}
					return CFGP_U::generate_converter_output( $content, $symbols[CFGP_U::api('base_currency')], $atts['align'], $atts['separator'] );
				}
				
				$total = number_format(($currency_converter * $amount), 2);
				if($atts['no-symbol'] == 1)
				{
					return $total;
				}
				return CFGP_U::generate_converter_output( $total, $symbol_to, $atts['align'], $atts['separator'] );
			}
			else
			{
				$content = number_format($content, 2);
				if($atts['no-symbol'] == 1)
				{
					return CFGP_U::generate_converter_output( $content, '', $atts['align'], $atts['separator'] );
				}
				return CFGP_U::generate_converter_output( $content, $symbols[CFGP_U::api('base_currency')], $atts['align'], $atts['separator'] );
			}
		}
		else
		{
			$api_params = array(
				'from'		=> $from,
				'to'		=> $to,
				'amount'	=> $content
			);
			$api_url = add_query_arg( $api_params, CFGP_Defaults::API['converter'] );

			$result = CFGP_U::curl_get( $api_url );

			$result = json_decode( $result, true );
			if( ( isset( $result['error'] ) && $result['error'] == true ) || ( !isset( $result['return'] ) || $result['return'] == false ) ) return CFGP_U::generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );

			if( !isset( $result['to_amount'] ) || empty( $result['to_amount'] ) ){
				if($atts['no-symbol'] == 1)
				{
					return $result['to_amount'];
				}
				return CFGP_U::generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );
			}
			if($atts['no-symbol'] == 1)
			{
				return $result['to_amount'];
			}
			return CFGP_U::generate_converter_output( $result['to_amount'], $symbol_to, $atts['align'], $atts['separator'] );
		}
	}
	
	/**
	 * Full converter shortcode
	 * 
	 * @since 7.4.2
	 */
	public function cfgeo_full_converter( $atts, $content = '', $tag )
	{
		wp_enqueue_style( CFGP_NAME . '-public-cc' );
		wp_enqueue_script( CFGP_NAME . '-public-cc' );
		
		$currency_symbols = CFGP_Defaults::CURRENCY_SYMBOL;

		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);

		$instance = shortcode_atts(
			array(
				'title'	=> __( 'Currency converter', CFGP_NAME ),
				'before_title' => '',
				'after_title'	=> '',
				'amount'	=> 1,
				'from'		=> __( 'From', CFGP_NAME ),
				'to'		=> __( 'To', CFGP_NAME ),
				'convert'	=> __( 'Convert', CFGP_NAME ),
			), 
			$atts, 
			'cfgeo_full_converter'
		);
		?>
		<div class="cfgp-container-fluid">
			<div class="cfgp-currency-converter-card">
				<div class="cfgp-currency-converter-card-body">
					<?php 
						$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? esc_html( $instance['title'] ) : '';
						echo html_entity_decode($instance['before_title']);
						printf( '%s', apply_filters( 'widget_title', $title ) ); 
						echo html_entity_decode ($instance['after_title']);
					?>
					<div class="cfgp-row">
						<div class="cfgp-col-12">
						<form action="<?php admin_url( 'admin-ajax.php?action=cfgeo_full_currency_converter' ); ?>" class="cfgp-currency-form" method="post" autocomplete="off">
							<div class="form-group cfgp-form-group cfgp-form-group-amount">
								<?php 
									$label_amount = sprintf( '%s-%s', 'cfgp-currency-amount', CFGP_U::generate_token(5) );
									$amount = ( isset( $instance['amount'] ) && !empty( $instance['amount'] ) ) ? esc_html( $instance['amount'] ) : esc_html__( 'Amount', CFGP_NAME );
								?>
								<label class="form-label cfgp-form-label" for="<?php echo $label_amount; ?>"><?php echo $amount ?></label>
								<input type="text" name="cfgp_currency_amount" class="form-control cfgp-form-control" id="<?php echo $label_amount; ?>" placeholder="<?php echo $amount; ?>" autocomplete="off">
							</div>
							
							<?php $label_from = sprintf( '%s-%s', 'cfgp-currency-from', CFGP_U::generate_token(5) ); ?>
							<div class="form-group cfgp-form-group cfgp-form-group-from">
								<label class="form-label cfgp-form-label" for="<?php echo $label_from; ?>"><?php echo ( isset( $instance['from'] ) && !empty( $instance['from'] ) ) ? esc_html( $instance['from'] ) : esc_html__( 'From', CFGP_NAME ); ?></label>
								<select name="cfgp_currency_from" class="form-control cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-from" id="<?php echo $label_from; ?>" data-show-subtext="true">
									<?php
										foreach( $currency_symbols as $key => $countries )
										{
											$selected = '';
											if( CFGP_Options::get('base_currency') == $key ) $selected = ' selected';

											$symbol = '';
											if( isset( $currency_symbols[ $key ] ) && !empty( $currency_symbols[ $key ] ) ) $symbol = sprintf( '- %s', $currency_symbols[ $key ] );
											printf( '<option value="%s" %s>%s %s</option>', $key, $selected, $key, $symbol );
										}
									?>
								</select>
							</div>
	
							<?php $label_to = sprintf( '%s-%s', 'cfgp-currency-to', CFGP_U::generate_token(5) ); ?>
							<div class="form-group cfgp-form-group cfgp-form-group-to">
								<label class="form-label cfgp-form-label" for="<?php echo $label_to; ?>"><?php echo ( isset( $instance['to'] ) && !empty( $instance['to'] ) ) ? esc_html( $instance['to'] ) : esc_html__( 'To', CFGP_NAME ); ?></label>
								<select name="cfgp_currency_to" class="form-control cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-to" id="<?php echo $label_to; ?>" data-show-subtext="true">
									<?php
										foreach( $currency_symbols as $key => $countries )
										{
											$selected = '';
											if( isset( $CFGEO['currency'] ) && $CFGEO['currency'] == $key ) $selected = ' selected';

											$symbol = '';
											if( isset( $currency_symbols[ $key ] ) && !empty( $currency_symbols[ $key ] ) ) $symbol = sprintf( '- %s', $currency_symbols[ $key ] );
											printf( '<option value="%s" %s>%s %s</option>', $key, $selected, $key, $symbol );
										}
									?>
								</select>
							</div>
							<div class="cfgp-form-group cfgp-form-group-result">
								<p class="cfgp-currency-converted"></p>
							</div>
							<div class="cfgp-form-group cfgp-form-group-submit">
								<button type="submit" class="button submit cfgp-btn cfgp-btn-calculate"><?php esc_html_e( $instance['convert'], CFGP_NAME ); ?></button>
								<button type="button" class="button submit cfgp-btn cfgp-exchange-currency">&#8646;</button> 
							</div>
                            <?php wp_nonce_field( 'cfgeo_full_currency_converter', 'cfgeo_currency_converter_nonce__' ); ?>
						</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Ajax call for currency conversion
	 */
	public function ajax__cfgeo_full_currency_converter()
	{
		if( !isset( $_REQUEST['cfgeo_currency_converter_nonce__'] ) || !wp_verify_nonce( $_REQUEST['cfgeo_currency_converter_nonce__'], 'cfgeo_full_currency_converter' ) )
		{
			$this->show_conversion_card_message( 'error_direct' );
			wp_die();
		}

		$amount = filter_var( $_REQUEST['cfgp_currency_amount'], FILTER_SANITIZE_NUMBER_FLOAT,  FILTER_FLAG_ALLOW_FRACTION );

		if( empty( $amount ) )
		{
			$this->show_conversion_card_message( 'error_user' );
			wp_die();
		}

		$amount = str_replace( '-', '', $amount );
		$api_params = array(
			'from'		=> strtoupper( $_REQUEST['cfgp_currency_from'] ),
			'to'		=> strtoupper( $_REQUEST['cfgp_currency_to'] ),
			'amount'	=> $amount
		);
		$api_url = add_query_arg( $api_params, CFGP_Defaults::API['converter'] );

		$result = CFGP_U::curl_get( $api_url );
		
		$result = json_decode( $result, true );

		if( isset( $result['return'] ) )
		{
			if( $result['return'] == false ) $this->show_conversion_card_message( 'error_api' );
			else
			{
				$this->show_conversion_card_message( 'success', $result );
			}
		}
		else $this->show_conversion_card_message( 'error_api' );
		exit;
	}
	
	/**
	 * Show conversion card message
	 */
	public function show_conversion_card_message( $message_type, $result = array() )
	{
		$class = 'cfgp-error';
		switch( $message_type )
		{
			case 'error_direct':
				$message = '<b>' . esc_html__( 'Direct access is forbidden!', CFGP_NAME ) . '</b>';
			break;
			case 'error_user': 
				$message = '<b>' . esc_html__( 'Please enter valid decimal or integer format.', CFGP_NAME ) . '</b>';
			break;
			case 'error_api':
				$message = '<b>' . esc_html__( 'Sorry currently we are not able to do conversion. Please try again later.', CFGP_NAME ) . '</b>';
			break;
			case 'success':
				$class = 'cfgp-success';
				
				if( !isset( $result['from_amount'] ) || empty( $result['from_amount'] ) ) 
				{
					$result['from_amount'] = '1';
					$result['to_amount'] = '1';
				}
				if( !isset( $result['to_amount'] ) || empty( $result['to_amount'] ) )
				{
					$result['from_amount'] = '1';
					$result['to_amount'] = '1';
				}
		
				if( !isset( $result['from_name'] ) || empty( $result['from_name'] ) ) $result['from_name'] = esc_html__( 'Undefined', CFGP_NAME );
				if( !isset( $result['to_name'] ) || empty( $result['to_name'] ) ) $result['to_name'] = esc_html__( 'Undefined', CFGP_NAME );;
		
				if( !isset( $result['from_code'] ) || empty( $result['from_code'] ) ) $result['from_code'] = 'X';
				if( !isset( $result['to_code'] ) || empty( $result['to_code'] ) ) $result['to_code'] = 'X';

				$message = sprintf( '<p class="cfgp-currency-results-amount"><span class="cfgp-currency-results-amount-current">%s %s</span><span class="cfgp-currency-results-amount-separator"> = </span><span class="cfgp-currency-results-amount-converted">%s %s</span></p><p class="cfgp-currency-results-info">%s &rarr; %s</p>', $result['from_amount'], $result['from_code'], $result['to_amount'], $result['to_code'], $result['from_name'], $result['to_name'] );
			break;
			default:
				$message = '<b>' . esc_html__( 'Sorry currently we are not able to do conversion. Please try again later.', CFGP_NAME ) . '</b>';
			break;
		}
		?>
		<div class="cfgp-card <?php echo esc_attr($class); ?>">
			<div class="cfgp-card-body">
				<p class="cfgp-card-text"><?php echo $message; ?></p>
			</div>
		</div>
		<?php
	}
	
	// IS PROXY
	public function is_proxy($attr, $content='', $tag){
		
		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
		}
		
		if(isset($CFGEO['is_proxy']) && $CFGEO['is_proxy'])
		{
			return self::__cache($tag, $content, (array)$array, $default, $cache);
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	// IS NOT PROXY
	public function is_not_proxy($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
		}
		
		if(isset($CFGEO['is_proxy']))
		{
			if(!$CFGEO['is_proxy'])
			{
				return self::__cache($tag, $content, (array)$array, $default, $cache);
			}
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	// GPS
	public function cfgeo_gps($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
		}
		
		if(isset($CFGEO['gps']) && $CFGEO['gps'])
		{
			return self::__cache($tag, $content, (array)$array, $default, $cache);
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	// IN EU
	public function in_eu($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
		}
		
		if(isset($CFGEO['in_eu']) && $CFGEO['in_eu'])
		{
			return self::__cache($tag, $content, (array)$array, $default, $cache);
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	// NOT IN EU
	public function not_in_eu($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
		}
		
		if(isset($CFGEO['in_eu']))
		{
			if(!$CFGEO['in_eu'])
			{
				return self::__cache($tag, $content, (array)$array, $default, $cache);
			}
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	// IS VAT
	public function is_vat($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)){
					return self::__cache($tag, $default, (array)$array, $content, $cache);
				}
			}
		}
		
		if(isset($CFGEO['is_vat']) && $CFGEO['is_vat'])
		{
			return self::__cache($tag, $content, (array)$array, $default, $cache);
		}
		
		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	
	// IS NOT VAT
	public function is_not_vat($attr, $content='', $tag){

		$cache = CFGP_U::is_attribute_exists('cache', $attr);
		if(CFGP_U::is_attribute_exists('no_cache', $attr)) $cache = false;
		$relative_match = (CFGP_U::is_attribute_exists('relative_match', $attr) ? true : false);
		
		$array = shortcode_atts( array(
			'ip'		=>	false,
			'default'	=>	NULL,
			'exclude'	=>	false,
			'include'	=>	false
        ), $attr );

		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
		} else {
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO, $relative_match)) {
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO, $relative_match)) {
					return self::__cache($tag, $content, (array)$array, $default, $cache);
				}
			}
		}
		

		if(isset($CFGEO['is_vat']) && !$CFGEO['is_vat'])
		{
			return self::__cache($tag, $content, (array)$array, $default, $cache);
		}

		return self::__cache($tag, $default, (array)$array, $content, $cache);
	}
	
	/* Content wrapper DEPRECATED */
	private static function __wrap($content, $cache = false, $shortcode = true) {
		if($cache)
		{
			if($shortcode === true) {
				$str = do_shortcode($content);
			} else {
				$str = $content;
			}
			return CFGP_U::fragment_caching($str, $cache);
		}
		else
		{
			if($shortcode === true) {
				return do_shortcode($content);
			} else {
				return $content;
			}
		}
	}
	
	/* Cache content wrapper */
	private static function __cache($shortcode, $content, $options=array(), $default = '', $cache = false) {
		if( $cache ) {
			$shortcode = esc_attr($shortcode);
			$shortcode = trim($shortcode);
			return sprintf(
				'<span class="cf-geoplugin-shortcode cf-geoplugin-shortcode-%1$s cache" data-type="%1$s" data-options="%2$s" data-default="%3$s" data-nonce="%4$s">%5$s</span>',
				esc_attr($shortcode),
				esc_attr(base64_encode(urlencode(serialize($options)))),
				esc_attr(base64_encode(urlencode($default))),
				wp_create_nonce( 'cfgeo-process-cache-ajax' ),
				$content
			);
		} else {
			return $content;
		}
	}

	/**
	 * Fix cache for the shortcode
	 * 
	 * @since 7.4.0
	 */
	public function ajax__shortcode_cache(){
		
		$shortcode = trim(CFGP_U::request_string('shortcode'));

	//	if( !(strpos($shortcode, 'cfgeo') !== false) ) echo 'false', exit;
		
		$options = unserialize(urldecode(base64_decode(sanitize_text_field(CFGP_U::request_string('options')))));
		
		
		$attr = array();
		if(!empty($options) && is_array($options))
		{
			foreach($options as $key => $value) {
				if(!is_numeric($key)) {
					$attr[] = $key . '="' . esc_attr($value) . '"';
				} else {
					$attr[] = $value;
				}
			}
		}
		
		$attr = (!empty($attr) ? ' ' . join(' ', $attr) : '');
		
		if($default = CFGP_U::request_string('default')) {
			$content = urldecode(base64_decode(sanitize_text_field($default)));
			$content = trim($defaucontentlt);
			$default = $content;
		} else {
			$default = $content = '';
		}
		
		$attr = str_replace(' cache', '', $attr) . ' no_cache';

		if( !in_array($shortcode, array(
			'cfgeo_flag',
			'cfgeo_converter',
			'cfgeo_is_vat',
			'cfgeo_is_not_vat',
			'cfgeo_in_eu',
			'cfgeo_not_in_eu',
			'cfgeo_is_proxy',
			'cfgeo_is_not_proxy',
			'cfgeo_gps',
			'cfgeo_map'
		)) && preg_match('/cfgeo_([a-z_]+)/i', $shortcode, $match) ) {
			echo do_shortcode('[cfgeo return="' . $match[1] . '"' . $attr . ']');
		} else {
			if(empty($default)) {
				echo do_shortcode('[' . $shortcode . $attr . ']');
			} else {
				echo do_shortcode('[' . $shortcode . $attr . ']' . $content . '[/' . $shortcode . ']');
			}
		}
		
		exit;
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