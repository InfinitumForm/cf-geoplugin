<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Shortcodes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Shortcodes')) :
class CF_Geoplugin_Shortcodes extends CF_Geoplugin_Global
{
	public function run()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if( $CF_GEOPLUGIN_OPTIONS['enable_beta'] === 1 &&  $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode'] === 1 ){
			// EXPERIMENTAL Generate Shortcodes by type
			$this->add_action( 'wp_loaded', 'shortcode_automat_setup' );
		}
		
		// Deprecated CF GeoPlugin shortcode but supported for now
		$this->add_shortcode('cf_geo', 'cf_geoplugin');
		if( $CF_GEOPLUGIN_OPTIONS['enable_beta'] === 1  &&  $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode'] === 1 ){
			// EXPERIMENTAL BUT SUPPORTED
			if ( !shortcode_exists( 'geo' ) ) $this->add_shortcode('geo', 'cf_geoplugin');
		}
		
		// Official CF GeoPlugin shortcode
		$this->add_shortcode('cfgeo', 'cf_geoplugin');
		
		if($CF_GEOPLUGIN_OPTIONS['enable_flag']){
			// Deprecated flag shortcode
			$this->add_shortcode('cf_geo_flag', 'generate_flag');
			if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']){
				// EXPERIMENTAL BUT SUPPORTED
				if ( !shortcode_exists( 'country_flag' ) ) $this->add_shortcode('country_flag', 'generate_flag');
			}
			// Official CF GeoPlugin flag shortcode
			$this->add_shortcode('cfgeo_flag', 'generate_flag');
		}

		//$this->add_action( 'wp_head', 'CF_GeoPlugin_Google_Map_Shortcode_Script' );
		if( isset( $CF_GEOPLUGIN_OPTIONS['enable_gmap'] ) && $CF_GEOPLUGIN_OPTIONS['enable_gmap'] ) {
			// Deprecated Google Map shortcode
			$this->add_shortcode( 'cf_geo_map', 'google_map' );
			// Official Google Map Shortcode
			$this->add_shortcode( 'cfgeo_map', 'google_map' );
		}

		if( isset( $CF_GEOPLUGIN_OPTIONS['enable_banner'] ) && $CF_GEOPLUGIN_OPTIONS['enable_banner'] ) {
			// Deprecated Banner shortcode
			$this->add_shortcode( 'cf_geo_banner', 'geo_banner' );
			// Official Banner shortcode
			$this->add_shortcode( 'cfgeo_banner', 'geo_banner' );
		}
		
		// We need CF7 shortcode support
		$this->add_filter( 'wpcf7_form_elements', 'cf7_support' );

		// Converter shortcode
		$this->add_shortcode( 'cfgeo_converter', 'cfgeo_converter' );
		
		// Escape shortcodes
		if ( !shortcode_exists( 'escape_shortcode' ) ) $this->add_shortcode( 'escape_shortcode', 'cfgeo_escape_shortcode' );

		// Full converter shortcode
		$this->add_shortcode( 'cfgeo_full_converter', 'cfgeo_full_converter' );
		$this->add_action( 'wp_ajax_cfgeo_full_currency_converter', 'cfgeo_full_currency_converter' );
	}
	
	
	/**
	 * Escape shortcodes for the internal docummentation purposes
	 *
	 * @since      7.4.3
	 * @version    7.4.3
	*/
	public function cfgeo_escape_shortcode($attr, $content=''){
		
		if(!empty($content)){
			$content = preg_replace('%\[(.*?)\]%i','&lsqb;$1&rsqb;',$content);
		}
		
		return $content;
	}
	
	
	/**
	 * Main CF GeoPlugin Shortcode
	 *
	 * @since      1.0.0
	 * @version    7.0.0
	*/
	public function cf_geoplugin($atts, $content='')
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$cache = isset($atts['cache']) || in_array('cache',$atts);
		if(isset($atts['no_cache']) || in_array('no_cache',$atts)) $cache = false;
		
		$CFGEO = $GLOBALS['CFGEO'];
		$array = shortcode_atts( array(
			'return' 	=>  'ip',
			'ip'		=>	false,
			'default'	=>	'',
			'exclude'	=>	false,
			'include'	=>	false
        ), $atts );

		$return 	= $array['return'];
		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if( !empty($ip) )
		{
			$CFGEO_API = new CF_Geoplugin_API;
			$CFGEO = $CFGEO_API->run(array('ip' => $ip));
		}
		
		if(!empty($content))
		{			
			if(!empty($exclude) || !empty($include)) {
				if(!empty($include))
				{
					if($this->recursive_array_search($include, $CFGEO)) return do_shortcode($content);
					else return '';
				}
				
				if(!empty($exclude))
				{
					if($this->recursive_array_search($exclude, $CFGEO)) return '';
					else return do_shortcode($content);
				}
			} else return __('CF GEOPLUGIN NOTICE: -Please define "include" or "exclude" attributes inside your shortcode on this shortcode mode.', CFGP_NAME);
		}
		else
		{
			if(!empty($exclude) || !empty($include)) {
				if(!empty($include))
				{
					if($this->recursive_array_search($include, $CFGEO)) return (isset($CFGEO[$return]) ? ($cache ? '<span class="cfgeo-replace" data-key="'.$return.'">' . $CFGEO[$return] . '</span>' : $CFGEO[$return]) : $default);
					else return '';
				}
				
				if(!empty($exclude))
				{
					if($this->recursive_array_search($exclude, $CFGEO)) return '';
					else return (isset($CFGEO[$return]) ? ($cache ? '<span class="cfgeo-replace" data-key="'.$return.'">' . $CFGEO[$return] . '</span>' : $CFGEO[$return]) : $default);
				}
			}
		}
		
		return (isset($CFGEO[$return]) ? ($cache ? '<span class="cfgeo-replace" data-key="'.$return.'">' . $CFGEO[$return] . '</span>' : $CFGEO[$return]) : $default);
	}
	
	/**
	 * EXPERIMENTAL Generate Shortcodes by type
	 *
	 * @since    7.0.0
	 */
	public function shortcode_automat_setup($atts){
		$CFGEO = $GLOBALS['CFGEO'];
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$cache = isset($atts['cache']) || in_array('cache',$atts);
		if(isset($CF_GEOPLUGIN_OPTIONS['enable_cache']) ? $CF_GEOPLUGIN_OPTIONS['enable_cache'] : 0) $cache = true;
		if(isset($atts['no_cache']) || in_array('no_cache',$atts)) $cache = false;
		
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-shortcode-automat.php'))
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-shortcode-automat.php';
			
			if(class_exists('CF_Geoplugin_Shortcode_Automat'))
			{
				$exclude = array_map('trim', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter'));
				
				$generate=array();
				foreach($CFGEO as $key => $value )
				{
					if(in_array($key, $exclude, true) === false)
					{
						$generate['cfgeo_' . $key]=($cache ? '<span class="cfgeo-replace" data-key="'.$key.'">' . $value . '</span>' : $value);
					}
				}
				
				$sc = new CF_Geoplugin_Shortcode_Automat( $generate );
				$sc->generate();
			}
		}
	}
	
	/**
	 * CF Geo Flag Shortcode
	 *
	 * @since    4.3.0
	 */
	public function generate_flag( $atts ){
		$CFGEO = $GLOBALS['CFGEO'];
		wp_enqueue_style( CFGP_NAME . '-flag' );
		$img_format = ($this->shortcode_has_argument('img', $atts) || $this->shortcode_has_argument('image', $atts) ? true : false);
		
		$arg = shortcode_atts( array(
			'size' 		=>  '128',
			'type' 		=>  0,
			'id' 		=>  false,
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	isset( $CFGEO['country_code'] ) ? $CFGEO['country_code'] : '',
			'exclude'	=>	false,
			'include'	=>	false,
        ), $atts );
		
		$exclude 	= $arg['exclude'];
		$include 	= $arg['include'];
		
		if(!empty($exclude) || !empty($include)) {
			if(!empty($include))
			{
				if(!$this->recursive_array_search($include, $CFGEO)) return '';
			}
			
			if(!empty($exclude))
			{
				if($this->recursive_array_search($exclude, $CFGEO)) return '';
			}
		}
		
		if(empty($arg['id']))
			$id = 'cf-geo-flag-' . parent::generate_token(10);
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
			if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag.'.svg'))
				return sprintf('<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="%s">', CFGP_ASSETS.'/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
			else
				return '';
		}
		else
			return sprintf('<span class="flag-icon flag-icon-%s%s" id="%s"%s></span>', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
	}

	/*
	* GOOGLE MAP SCRIPT - SHORTCODE PART
	* @author Ivijan-Stefan Stipic
	**/
	public function CF_GeoPlugin_Google_Map_Shortcode_Script()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
	?>
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
			var url = '//maps.googleapis.com/maps/api/js?key=<?php echo isset( $CF_GEOPLUGIN_OPTIONS['map_api_key'] ) ? esc_attr($CF_GEOPLUGIN_OPTIONS['map_api_key']) : ''; ?>',
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
	<?php
	}

	/**
	 * Google Map Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function google_map( $atts, $content = '' )
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];

		$att = (object)shortcode_atts( array( 
			'latitude'				=>	(isset($CF_GEOPLUGIN_OPTIONS['map_latitude']) && !empty($CF_GEOPLUGIN_OPTIONS['map_latitude']) ? $CF_GEOPLUGIN_OPTIONS['map_latitude'] : (isset( $CFGEO['latitude'] ) ? $CFGEO['latitude'] : '')),
			'longitude'				=> 	(isset($CF_GEOPLUGIN_OPTIONS['map_longitude']) && !empty($CF_GEOPLUGIN_OPTIONS['map_longitude']) ? $CF_GEOPLUGIN_OPTIONS['map_longitude'] : (isset( $CFGEO['longitude'] ) ? $CFGEO['longitude'] : '')),
			
			'zoom'					=>	$CF_GEOPLUGIN_OPTIONS['map_zoom'],
			'width'					=>	$CF_GEOPLUGIN_OPTIONS['map_width'],
			'height'				=> 	$CF_GEOPLUGIN_OPTIONS['map_height'],

			'scrollwheel'			=>	$CF_GEOPLUGIN_OPTIONS['map_scrollwheel'],
			'navigationControl'		=>	$CF_GEOPLUGIN_OPTIONS['map_navigationControl'],
			'mapTypeControl'		=>	$CF_GEOPLUGIN_OPTIONS['map_mapTypeControl'],
			'scaleControl'			=>	$CF_GEOPLUGIN_OPTIONS['map_scaleControl'],
			'draggable'				=>	$CF_GEOPLUGIN_OPTIONS['map_draggable'],
			
			'infoMaxWidth'			=>	$CF_GEOPLUGIN_OPTIONS['map_infoMaxWidth'],

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
		
		$this->add_action( 'wp_footer', 'CF_GeoPlugin_Google_Map_Shortcode_Script' );
		$this->add_action( 'admin_footer', 'CF_GeoPlugin_Google_Map_Shortcode_Script' );

		return '<div class="CF_GeoPlugin_Google_Map_Shortcode" style="width:'.esc_attr($att->width).'; height:'.esc_attr($att->height).'"'.join(' ', $attributes).'>'.$content.'</div>';
	}
		
	
	/**
	 * Geo Banner Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function geo_banner( $atts, $cont )
	{ 
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];

		if( !isset( $CF_GEOPLUGIN_OPTIONS['enable_banner'] ) || !$CF_GEOPLUGIN_OPTIONS['enable_banner'] ) return '';
		$ID = parent::generate_token(16); // Let's made this realy hard
	
		$array = shortcode_atts( array(
			'id'				=>	$ID,
			'posts_per_page'	=>	1,
			'class'				=>	''
		), $atts );
		
		$id				=	$array['id'];
		$posts_per_page	=	$array['posts_per_page'];
		$class			=	$array['class'];
		
		$country 		= sanitize_title(isset($CFGEO['country_code']) 	? $CFGEO['country_code']	: do_shortcode('[cfgeo return="country_code"]'));
		$country_name 	= sanitize_title(isset($CFGEO['country']) 		? $CFGEO['country']			: do_shortcode('[cfgeo return="country"]'));
		$region 		= sanitize_title(isset($CFGEO['region']) 		? $CFGEO['region']			: do_shortcode('[cfgeo return="region"]'));
		$region_code	= sanitize_title(isset($CFGEO['region_code'])) 	? $CFGEO['region_code']		: do_shortcode('[cfgeo return="region_code"]');
		$city 			= sanitize_title(isset($CFGEO['city']) 			? $CFGEO['city']			: do_shortcode('[cfgeo return="city"]'));
		
		$args = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	(int) $posts_per_page,
		  'post_status'		=> 'publish',
		  'force_no_results' => true,
		  'tax_query'		=> array(
				'relation'	=> 'OR',
				array(
					'taxonomy'	=> 'cf-geoplugin-country',
					'field'		=> 'slug',
					'terms'		=> array($country, $country_name),
				),
				array(
					'taxonomy'	=> 'cf-geoplugin-region',
					'field'		=> 'slug',
					'terms'		=> array($region, $region_code),
				),
				array(
					'taxonomy'	=> 'cf-geoplugin-city',
					'field'		=> 'slug',
					'terms'		=> array($city),
				)
			)
		);
		if($id > 0) $args['post__in'] = array($id);
		
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
				
				$classes	=	(empty($class) ? array() : array_map("trim",explode(" ", $class)));
				$classes[]	=	'cf-geoplugin-banner';
				$classes[]	=	'cf-geoplugin-banner-'.$post_id;
				
				$save[]='
				<div id="cf-geoplugin-banner-'.$post_id.'" class="'.join(' ',get_post_class($classes, $post_id)).'">
					'.$content.'
				</div>
				';
				$classes	= NULL;
			}
			wp_reset_postdata();
			if(count($save)>0){ return join("\r\n",$save); }
		}
		
		if(!empty($cont))
		{
			$content = do_shortcode($cont);
			$content = apply_filters('the_content', $content);
			return $content;
		}
		else 
			return '';
	}
	
	/**
	 * Add support for Contact Form 7
	 *
	 * @since    4.0.0
	 */
	public function cf7_support( $form ) {
		return do_shortcode( $form );
	}

	/**
	 * Converter shortcode
	 * 
	 * @since 7.4.0
	 */
	public function cfgeo_converter( $atts, $content = '' )
	{
		if( empty( $content ) ) return '';

		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
		$atts = shortcode_atts(
			array(
				'from'	=> isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) && !empty( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) ? strtoupper( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) : 'USD',
				'to'	=> isset( $CFGEO['currency'] ) && !empty( $CFGEO['currency'] ) ? strtoupper( $CFGEO['currency'] ) : 'USD',
				'align'	=> 'R',
				'separator'	=> '',
				'no-symbol' => 0,
				'auto' => 0
			), 
			$atts, 
			'cfgeo_converter'
		);
		$symbols = CF_Geplugin_Library::CURRENCY_SYMBOL;
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
			$symbol_from = CF_Geoplugin_Global::mb_convert_encoding( $symbols[ $from ] );
			$symbol_to = CF_Geoplugin_Global::mb_convert_encoding( $symbols[ $to ] );
		}
		$content = filter_var( $content, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		if( $from === $to )
		{
			return $this->generate_converter_output( $content, $symbol_to, $atts['align'], $atts['separator'] );
		}

		if( isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) && isset( $CFGEO['currency_converter'] ) && strtoupper( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) == $from && $CFGEO['currency'] == $to )
		{
			if(preg_match('/([0-9\.\,]+)/i',$content, $match))
			{
				$match[0] = strtr($match[0],',','.');
				$amount = floatval($match[0]);
				$currency_converter = $CFGEO['currency_converter'];
				
				if(empty($currency_converter) || !is_numeric($currency_converter)){
					$content = number_format($content, 2);
					if($atts['no-symbol'] == 1)
					{
						return $this->generate_converter_output( $content, '', $atts['align'], $atts['separator'] );
					}
					return $this->generate_converter_output( $content, $symbols[$CFGEO['base_currency']], $atts['align'], $atts['separator'] );
				}
				
				$total = number_format(($currency_converter * $amount), 2);
				if($atts['no-symbol'] == 1)
				{
					return $total;
				}
				return $this->generate_converter_output( $total, $symbol_to, $atts['align'], $atts['separator'] );
			}
			else
			{
				$content = number_format($content, 2);
				if($atts['no-symbol'] == 1)
				{
					return $this->generate_converter_output( $content, '', $atts['align'], $atts['separator'] );
				}
				return $this->generate_converter_output( $content, $symbols[$CFGEO['base_currency']], $atts['align'], $atts['separator'] );
			}
		}
		else
		{
			$api_params = array(
				'from'		=> $from,
				'to'		=> $to,
				'amount'	=> $content
			);
			$api_url = add_query_arg( $api_params, 'http://cdn-cfgeoplugin.com/api6.0/convert.php' );

			$result = $this->curl_get( $api_url );

			$result = json_decode( $result, true );
			if( ( isset( $result['error'] ) && $result['error'] == true ) || ( !isset( $result['return'] ) || $result['return'] == false ) ) return $this->generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );

			if( !isset( $result['to_amount'] ) || empty( $result['to_amount'] ) ){
				if($atts['no-symbol'] == 1)
				{
					return $result['to_amount'];
				}
				return $this->generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );
			}
			if($atts['no-symbol'] == 1)
			{
				return $result['to_amount'];
			}
			return $this->generate_converter_output( $result['to_amount'], $symbol_to, $atts['align'], $atts['separator'] );
		}
	}

	/**
	 * Full converter shortcode
	 * 
	 * @since 7.4.2
	 */
	public function cfgeo_full_converter( $atts, $content = '' )
	{
		wp_enqueue_style( CFGP_NAME . '-widget-converter' );
		$currency_symbols = CF_Geplugin_Library::CURRENCY_SYMBOL;

		$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

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
		<div class="cfgp-container-fluid mt-3 w-100">
			<div class="cfgp-card w-100 text-white bg-info">
				<div class="cfgp-card-body">
					<?php 
						$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? esc_html( $instance['title'] ) : '';
						echo $instance['before_title'];
						printf( '%s', apply_filters( 'widget_title', $title ) ); 
						echo $instance['after_title'];
					?>
					<div class="cfgp-row">
						<div class="cfgp-col-12">
						<form action="<?php self_admin_url( 'admin-ajax.php?action=cfgeo_full_currency_converter' ); ?>" class="cfgp-currency-form" method="post">
							<div class="cfgp-form-group cfgp-form-group-amount">
								<?php 
									$label_amount = sprintf( '%s-%s', 'cfgp-currency-amount', $this->generate_token(5) );
									$amount = ( isset( $instance['amount'] ) && !empty( $instance['amount'] ) ) ? esc_html( $instance['amount'] ) : esc_html__( 'Amount', CFGP_NAME );
								?>
								<label class="cfgp-form-label" for="<?php echo $label_amount; ?>"><?php echo $amount ?></label>
								<input type="text" name="cfgp_currency_amount" class="cfgp-form-control" id="<?php echo $label_amount; ?>" placeholder="<?php echo $amount; ?>">
							</div>
							
							<?php $label_from = sprintf( '%s-%s', 'cfgp-currency-from', $this->generate_token(5) ); ?>
							<div class="cfgp-form-group cfgp-form-group-from">
								<label class="cfgp-form-label" for="<?php echo $label_from; ?>"><?php echo ( isset( $instance['from'] ) && !empty( $instance['from'] ) ) ? esc_html( $instance['from'] ) : esc_html__( 'From', CFGP_NAME ); ?></label>
								<select name="cfgp_currency_from" class="cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-from" id="<?php echo $label_from; ?>" data-show-subtext="true">
									<?php
										foreach( $currency_symbols as $key => $countries )
										{
											$selected = '';
											if( isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) && $CF_GEOPLUGIN_OPTIONS['base_currency'] == $key ) $selected = ' selected';

											$symbol = '';
											if( isset( $currency_symbols[ $key ] ) && !empty( $currency_symbols[ $key ] ) ) $symbol = sprintf( '- %s', $currency_symbols[ $key ] );
											printf( '<option value="%s" %s>%s %s</option>', $key, $selected, $key, $symbol );
										}
									?>
								</select>
							</div>
	
							<?php $label_to = sprintf( '%s-%s', 'cfgp-currency-to', $this->generate_token(5) ); ?>
							<div class="cfgp-form-group cfgp-form-group-to">
								<label class="cfgp-form-label" for="<?php echo $label_to; ?>"><?php echo ( isset( $instance['to'] ) && !empty( $instance['to'] ) ) ? esc_html( $instance['to'] ) : esc_html__( 'To', CFGP_NAME ); ?></label>
								<select name="cfgp_currency_to" class="cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-to" id="<?php echo $label_to; ?>" data-show-subtext="true">
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
								<?php wp_nonce_field( 'cfgeo_full_currency_converter' ); ?>
								<p class="cfgp-currency-converted"></p>
							</div>
							<div class="cfgp-form-group cfgp-form-group-submit">
								<button type="submit" class="button submit cfgp-btn cfgp-btn-calculate"><?php esc_html_e( $instance['convert'], CFGP_NAME ); ?></button>
								<button type="button" class="button submit cfgp-btn cfgp-exchange-currency">&#8646;</button> 
							</div>
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
	public function cfgeo_full_currency_converter()
	{
		if( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'cfgeo_full_currency_converter' ) )
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
		$api_url = add_query_arg( $api_params, 'http://cdn-cfgeoplugin.com/api6.0/convert.php' );

		$result = $this->curl_get( $api_url );
		
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
		wp_die();
	}

	/**
	 * Show conversion card message
	 */
	public function show_conversion_card_message( $message_type, $result = array() )
	{
		$card_type = 'bg-danger';

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
				$card_type = 'bg-secondary';
			break;
			default:
				$message = '<b>' . esc_html__( 'Sorry currently we are not able to do conversion. Please try again later.', CFGP_NAME ) . '</b>';
			break;
		}
		?>
		<div class="card w-100 text-white <?php echo esc_attr( $card_type ); ?>">
			<div class="card-body text-center">
				<p class="card-text"><?php echo $message; ?></p>
			</div>
		</div>
		<?php
	}
}
endif;