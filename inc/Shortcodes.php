<?php
/**
 * Shortcodes
 *
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes Automat
 */
if(!class_exists('CFGP_Shortcodes_Automat')) :
class CFGP_Shortcodes_Automat extends CFGP_Global
{
    protected $settings = array();

    public function  __construct( $settings = array() )
    {
        $this->settings = $settings;
    }

    public function __call( $name, $arguments )
    {
        if( in_array( $name, array_keys( $this->settings ) ) )
        {
            return $this->settings[$name];
        }
    }

    public function generate()
    {
        foreach( $this->settings as $shortcode => $option )
        {
            $this->add_shortcode( $shortcode, $shortcode );
        }
    }
}
endif;

/**
 * Shortcodes
 */
if(!class_exists('CFGP_Shortcodes')) :
class CFGP_Shortcodes extends CFGP_Global {
	function __construct(){
		// Standard shortcode
		$this->add_shortcode('cfgeo', 'cf_geoplugin');
		if(CFGP_Options::get('enable_flag', 0)){
			$this->add_shortcode('cfgeo_flag', 'generate_flag');
		}
		
		// Beta shortcodes
		if(CFGP_Options::get_beta('enable_simple_shortcode')) {
			$this->add_shortcode('geo', 'cf_geoplugin');
			$this->add_action( 'wp_loaded', 'shortcode_automat_setup' );
			
			if(CFGP_Options::get('enable_flag', 0)){
				$this->add_shortcode('country_flag', 'generate_flag');
			}
		}
		
		// Google Map shortcode
		if( CFGP_Options::get_beta('enable_gmap', 0) ) {
			// Official Google Map Shortcode
			$this->add_shortcode( 'cfgeo_map', 'google_map' );
		}
		
		// Geo Banner
		if( CFGP_Options::get_beta('enable_banner', 0) ) {
			// Official Google Map Shortcode
			$this->add_shortcode( 'cfgeo_banner', 'geo_banner' );
		}
		
	}
	
	/**
	 * Main CF GeoPlugin Shortcode
	 *
	 * @since      1.0.0
	 * @version    7.0.0
	*/
	public function cf_geoplugin($atts, $content='')
	{		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		global $cfgp_cache;
		
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
		
		$nonce = wp_create_nonce( 'cfgeo-process-cache-ajax' );
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
			if (CFGP_Options::get('enable_dns_lookup', 0)) {
				$CFGEO = array_merge($CFGEO, CFGP_API::instance(true)->get('dns', $ip));
			}
		} else {
			$CFGEO = $cfgp_cache->get('API');
		}
		
		if(!empty($content))
		{			
			// Include/ Exclude functionality for the content
			if(!empty($exclude) || !empty($include)) {
				// Include
				if(!empty($include))
				{
					if(CFGP_U::recursive_array_search($include, $CFGEO))
					{
						if($cache)
						{
							return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' 
								. do_shortcode($content) 
							. '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
						}
						else
						{
							return do_shortcode($content);
						}
						
					}
					else
					{
						return self::__wrap($default, $cache, false);
					}
				}
				// Exclude
				if(!empty($exclude))
				{
					if(CFGP_U::recursive_array_search($exclude, $CFGEO))
					{
						return self::__wrap($default, $cache, false);
					}
					else
					{
						
						if($cache)
						{
							return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' 
								. do_shortcode($content) 
							. '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
						}
						else
						{
							return do_shortcode($content);
						}
					}
				}
			}
			else
			{
				if($cache)
					return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' 
						. __('CF GEOPLUGIN NOTICE: -Please define "include" or "exclude" attributes inside your shortcode on this shortcode mode.', CFGP_NAME)
					. '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				else
					return __('CF GEOPLUGIN NOTICE: -Please define "include" or "exclude" attributes inside your shortcode on this shortcode mode.', CFGP_NAME);
			}
		}
		else
		{
			// Include/ Exclude functionality for the geo informations
			if(!empty($exclude) || !empty($include)) {
				// Include
				if(!empty($include))
				{
					if(CFGP_U::recursive_array_search($include, $CFGEO))
					{
						if($cache)
						{
							if(isset($CFGEO[$return]))
							{
								return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><span class="cfgeo-replace" data-key="' . $return . '" data-nonce="' . $nonce . '">' 
									. $CFGEO[$return] 
								. '</span><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
							}
							else
							{
								return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' . $default . '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
							}
						}
						else
						{
							if(isset($CFGEO[$return]))
							{
								return $CFGEO[$return];
							}
							else
							{
								return $default;
							}
						}
					}
					else
					{
						return self::__wrap($default, $cache, false);
					}
				}
				// Exclude
				if(!empty($exclude))
				{
					if(CFGP_U::recursive_array_search($exclude, $CFGEO))
					{
						return self::__wrap($default, $cache, false);
					}
					else
					{
						if($cache)
						{
							if(isset($CFGEO[$return]))
							{
								return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><span class="cfgeo-replace" data-key="' . $return . '" data-nonce="' . $nonce . '">' 
									. $CFGEO[$return] 
								. '</span><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
							}
							else
							{
								return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' . $default . '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
							}
						}
						else
						{
							if(isset($CFGEO[$return]))
							{
								return $CFGEO[$return];
							}
							else
							{
								return $default;
							}
						}
					}
				}
			}
		}
		
		// Return geo information
		if(isset($CFGEO[$return]))
		{
			if($cache)
			{
				return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><span class="cfgeo-replace" data-key="' . $return . '" data-nonce="' . $nonce . '">' 
					. $CFGEO[$return] 
				. '</span><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
			}
			else return $CFGEO[$return];
		}
		
		if($cache)
			return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' . $default . '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
		else
			return $default;
	}
	
	/**
	 * EXPERIMENTAL Generate Shortcodes by type
	 *
	 * @since    7.0.0
	 */
	public function shortcode_automat_setup($atts){
		global $cfgp_cache;
		$CFGEO = $cfgp_cache->get('API');

		$nonce = wp_create_nonce( 'cfgeo-process-cache-ajax' );
		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_Options::get('enable_cache', 0)) $cache = true;
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		$exclude = array_map('trim', explode(',','gps,is_vat,is_proxy,is_mobile,in_eu,state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter'));
		
		$generate=array();
		foreach($CFGEO as $key => $value )
		{
			if(in_array($key, $exclude, true) === false)
			{
				$generate['cfgeo_' . $key]=($cache ? '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><span class="cfgeo-replace" data-key="' . $key . '" data-nonce="' . $nonce . '">' . $value . '</span><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : $value);
			}
		}
		
		$sc = new CFGP_Shortcodes_Automat( $generate );
		$sc->generate();
	}
	
	
	/* Content wrapper */
	private static function __wrap($content, $cache = false, $shortcode = true) {
		if($cache)
		{
			$str = '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->';
			if($shortcode === true) {
				$str.= do_shortcode($content);
			} else {
				$str.= $content;
			}
			$str.= '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
			return $str;
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
	
	
	/**
	 * CF Geo Flag Shortcode
	 *
	 * @since    4.3.0
	 */
	public function generate_flag( $atts ){
		global $cfgp_cache;
		
		wp_enqueue_style( CFGP_NAME . '-flag' );
		
		$img_format = (CFGP_U::shortcode_has_argument('img', $atts) || CFGP_U::shortcode_has_argument('image', $atts) ? true : false);
		
		$arg = shortcode_atts( array(
			'size' 		=>  '128',
			'type' 		=>  0,
			'ip' 		=>  false,
			'id' 		=>  false,
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	isset( $CFGEO['country_code'] ) ? $CFGEO['country_code'] : '',
			'exclude'	=>	false,
			'include'	=>	false,
        ), $atts );
		
		$exclude 	= $arg['exclude'];
		$include 	= $arg['include'];
		
		if( !empty($ip) ) {
			$CFGEO = CFGP_API::instance(true)->get('geo', $ip);
			if (CFGP_Options::get('enable_dns_lookup', 0)) {
				$CFGEO = array_merge($CFGEO, CFGP_API::instance(true)->get('dns', $ip));
			}
		} else {
			$CFGEO = $cfgp_cache->get('API');
		}
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!CFGP_U::recursive_array_search($include, $CFGEO)) return '';
			}
			
			if(!empty($exclude))
			{
				if(CFGP_U::recursive_array_search($exclude, $CFGEO)) return '';
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
		
		$flag = trim(strtolower($arg['country_code']));
		
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
			if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag.'.svg'))
				return sprintf('<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="%s"><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->', CFGP_ASSETS.'/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
			else
				return '';
		}
		else
			return sprintf('<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><span class="flag-icon flag-icon-%s%s" id="%s"%s></span><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
	}
	
	/**
	 * Geo Banner Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function geo_banner( $atts, $cont )
	{
		global $cfgp_cache;
		
		wp_enqueue_style( CFGP_NAME . '-public' );
		
		$CFGEO = $cfgp_cache->get('API');
		
		$cache = CFGP_U::is_attribute_exists('cache', $atts);
		if(CFGP_U::is_attribute_exists('no_cache', $atts)) $cache = false;
		
		$nonce = NULL;
		
		if($cache){
			$nonce = wp_create_nonce( 'cfgeo-process-cache-ajax' );
		}
		
		if(!CFGP_Settings::get('enable_banner', 0)) return '';
		$ID = CFGP_U::generate_token(16); // Let's made this realy hard
	
		$array = shortcode_atts( array(
			'id'				=>	$ID,
			'posts_per_page'	=>	1,
			'class'				=>	''
		), $atts );
		
		$id				=	intval($array['id']);
		$posts_per_page	=	intval($array['posts_per_page']);
		$class			=	sanitize_html_class($array['class']);
		
		$country 		= sanitize_title(isset($CFGEO['country_code']) 	? $CFGEO['country_code']	: do_shortcode('[cfgeo return="country_code"]'));
		$country_name 	= sanitize_title(isset($CFGEO['country']) 		? $CFGEO['country']			: do_shortcode('[cfgeo return="country"]'));
		$region 		= sanitize_title(isset($CFGEO['region']) 		? $CFGEO['region']			: do_shortcode('[cfgeo return="region"]'));
		$region_code	= sanitize_title(isset($CFGEO['region_code'])) 	? $CFGEO['region_code']		: do_shortcode('[cfgeo return="region_code"]');
		$city 			= sanitize_title(isset($CFGEO['city']) 			? $CFGEO['city']			: do_shortcode('[cfgeo return="city"]'));
		$postcode 		= sanitize_title(isset($CFGEO['postcode']) 		? $CFGEO['postcode']		: do_shortcode('[cfgeo return="postcode"]'));
		
		if(empty($cont) && $banner_default = get_post_meta( $id, CFGP_METABOX . 'banner_default', true ) )
		{
			$cont = $banner_default;
		}
		
		$tax_query = array();

		if(!empty($country) || !empty($country_name))
		{
			$tax_query[]=array(
				'taxonomy'	=> 'cf-geoplugin-country',
				'field'		=> 'slug',
				'terms'		=> array_filter(array($country, $country_name)),
			);
		}

		if(!empty($region) || !empty($region_code))
		{
			$tax_query[]=array(
				'taxonomy'	=> 'cf-geoplugin-region',
				'field'		=> 'slug',
				'terms'		=> array_filter(array($region, $region_code)),
			);
		}

		if(!empty($city))
		{
			$tax_query[]=array(
				'taxonomy'	=> 'cf-geoplugin-city',
				'field'		=> 'slug',
				'terms'		=> array($city),
			);
		}

		if(!empty($postcode))
		{
			$tax_query[]=array(
				'taxonomy'	=> 'cf-geoplugin-postcode',
				'field'		=> 'slug',
				'terms'		=> array($postcode),
			);
		}

		if(count($tax_query) > 1)
		{
			$tax_query['relation']='OR';
		}

		$args = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	(int) $posts_per_page,
		  'post_status'		=> 'publish',
		  'force_no_results' => true,
		  'tax_query'		=> $tax_query
		);

		if($id > 0) $args['post__in'] = array($id);
		
		$classes	=	(empty($class) ? array() : array_map("trim",explode(" ", $class)));
		$classes[]	=	'cf-geoplugin-banner';
		if($cache)	$classes[]	=	'cf-geoplugin-banner-cached';
		
		$queryBanner = new WP_Query( $args );
		
		if ( $queryBanner->have_posts() )
		{
			$save=array();
			while ( $queryBanner->have_posts() )
			{
				$queryBanner->the_post();
				
				$post_id = get_the_ID();
				$content = get_the_content();
				$content = do_shortcode($content);
				$content = apply_filters('the_content', $content);
				
				$classes[]	=	'cf-geoplugin-banner-'.$post_id;
				
				$save[]='
				' . ( $cache ? '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' : NULL) 
				. '<div id="cf-geoplugin-banner-'.$post_id.'" class="'.join(' ',get_post_class($classes, $post_id)).'"'
				
				. ($cache ? ' data-id="' . $post_id . '"' : NULL)
				. ($cache ? ' data-posts_per_page="' . esc_attr($posts_per_page) . '"' : NULL)
				. ($cache ? ' data-class="' . esc_attr($class) . '"' : NULL)
				. ($cache ? ' data-nonce="' . esc_attr($nonce) . '"' : NULL)
				
				. '>' . $content . '</div>' 
				. ( $cache ? '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : NULL) . '
				';
				$classes	= NULL;
			}
			wp_reset_postdata();
			if(count($save) > 0){ return trim(join("\r\n",$save)); }
		}
		
		if(!empty($cont))
		{
			$content = do_shortcode($cont);
			$content = apply_filters('the_content', $content);
			
			if( $cache )
			{
				return '
				<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->
				<div id="cf-geoplugin-banner-' . $id . '" class="' . join(' ', $classes) . ' cf-geoplugin-banner-cached" data-id="' . $id . '" data-posts_per_page="' . esc_attr($posts_per_page) . '" data-class="' . esc_attr($class) . '" data-nonce="' . esc_attr($nonce) . '">' . $content . '</div>
				<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
				';
			}
			else
				return $content;
		}
		else 
		{
			if( $cache )
			{
				return '
				<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->
				<div id="cf-geoplugin-banner-' . $id . '" class="' . join(' ', $classes) . '" data-id="' . $id . '" data-posts_per_page="' . esc_attr($posts_per_page) . '" data-class="' . esc_attr($class) . '" data-nonce="' . esc_attr($nonce) . '"></div>
				<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
				';
			}
			else
				return '';
		}
	}	
	
	/**
	 * Google Map Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function google_map( $atts, $content = '' )
	{
		global $cfgp_cache;
		$CFGEO = $cfgp_cache->get('API');

		$att = (object)shortcode_atts( array( 
			'latitude'				=>	CFGP_Options::get('map_latitude', (isset( $CFGEO['latitude'] ) ? $CFGEO['latitude'] : '')),
			'longitude'				=> 	CFGP_Options::get('map_longitude', (isset( $CFGEO['longitude'] ) ? $CFGEO['longitude'] : '')),
			
			'zoom'					=>	CFGP_Options::get('map_zoom'),
			'width'					=>	CFGP_Options::get('map_width'),
			'height'				=> 	CFGP_Options::get('map_height'),

			'scrollwheel'			=>	CFGP_Options::get('map_scrollwheel'),
			'navigationControl'		=>	CFGP_Options::get('map_navigationControl'),
			'mapTypeControl'		=>	CFGP_Options::get('map_mapTypeControl'),
			'scaleControl'			=>	CFGP_Options::get('map_scaleControl'),
			'draggable'				=>	CFGP_Options::get('map_draggable'),
			
			'infoMaxWidth'			=>	CFGP_Options::get('map_infoMaxWidth'),

			'title'					=>	isset( $CFGEO['address'] ) ? $CFGEO['address'] : '' ,
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

		return '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc --><div class="CF_GeoPlugin_Google_Map_Shortcode" style="width:'.esc_attr($att->width).'; height:'.esc_attr($att->height).'"'.join(' ', $attributes).'>'.do_shortcode($content).'</div><!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
	}
	
	/*
	* GOOGLE MAP SCRIPT - SHORTCODE PART
	* @author Ivijan-Stefan Stipic
	**/
	public function google_map_shortcode_script()
	{
	?>
    <!-- <?php echo W3TC_DYNAMIC_SECURITY; ?> mfunc -->
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
    <!-- /mfunc <?php echo W3TC_DYNAMIC_SECURITY; ?> -->
	<?php
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