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
	}
	
	
	/**
	 * Main CF GeoPlugin Shortcode
	 *
	 * @since      1.0.0
	 * @version    7.0.0
	*/
	public function cf_geoplugin($atts, $content='')
	{
		$CFGEO = $GLOBALS['CFGEO'];
		$array = shortcode_atts( array(
			'return' 	=>  'ip',
			'ip'		=>	false,
			'default'	=>	'',
			'exclude'	=>	false,
			'include'	=>	false,
        ), $atts );
		
		$return 	= $array['return'];
		$ip 		= $array['ip'];
		$default 	= $array['default'];
		$exclude 	= $array['exclude'];
		$include 	= $array['include'];
		
		if($ip!==false)
		{
			$CFGEO_API = new CF_Geoplugin_API;
			$CFGEO = $CFGEO_API->run(array('ip' => $ip));
		}
		
		if(!empty($content))
		{			
			if(!empty($exclude) || !empty($include)) {
				if($this->recursive_array_search($exclude, $CFGEO)) return '';
				else if($this->recursive_array_search($include, $CFGEO)) return $content;
				else return '';
			}
				else return __('CF GEOPLUGIN NOTICE: -Please define "include" or "exclude" attributes inside your shortcode on this shortcode mode.', CFGP_NAME);
		}
		else
			return (isset($CFGEO[$return]) ? $CFGEO[$return] : $default);
	}
	
	/**
	 * EXPERIMENTAL Generate Shortcodes by type
	 *
	 * @since    7.0.0
	 */
	public function shortcode_automat_setup(){
		$CFGEO = $GLOBALS['CFGEO'];
		
		include_once CFGP_INCLUDES . '/class-cf-geoplugin-shortcode-automat.php';
		$exclude = array_map('trim', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter'));
		
		$generate=array();
		foreach($CFGEO as $key => $value )
		{
			if(in_array($key, $exclude, true) === false)
			{
				$generate['cfgeo_' . $key]=$value;
			}
		}
		
		$sc = new CF_Geoplugin_Shortcode_Automat( $generate );
    	$sc->generate();
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
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	isset( $CFGEO['country_code'] ) ? $CFGEO['country_code'] : '',
        ), $atts );
		
		$id = mt_rand(11111,99999);
		
		if(strpos($arg['size'], '%')!==false || strpos($arg['size'], 'in')!==false || strpos($arg['size'], 'pt')!==false || strpos($arg['size'], 'em')!==false)
			$size = $arg['size'];
		else
			$size = str_replace("px","",$arg['size']).'px';
		
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
		
		if($arg['css']!=false){
			$css = NULL;

			$ss = array();
			$csss = array_map('trim',explode(";", $arg['css']));
			foreach($csss as $val){
				if(!empty($val)){
					$val = array_map('trim',explode(":", $val));
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
		
		if($arg['class']!=false){
			$classes = explode(" ", $arg['class']);
			$cc = array();
			foreach($classes as $val){
				if(!empty($val)) $cc[]=$val;
			}
			if(count($cc)>0)
				$class=' '.join(" ", $cc);
			else
				$class='';
		}else
			$class='';
		
		if($img_format===true)
		{
			$address = $CFGEO['address'];
			if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag.'.svg'))
				return sprintf('<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="cf-geo-flag-%s">', CFGP_ASSETS.'/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
			else
				return '';
		}
		else
			return sprintf('<span class="flag-icon flag-icon-%s%s" id="cf-geo-flag-%s"%s></span>', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
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
			initMaps = document.getElementsByClassName('CF_GeoPlugin_Google_Map_Shortcode'),
			i,
			e;
			
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
			var url = 'https://maps.googleapis.com/maps/api/js?key=<?php echo isset( $CF_GEOPLUGIN_OPTIONS['map_api_key'] ) ? esc_attr($CF_GEOPLUGIN_OPTIONS['map_api_key']) : ''; ?>',
				head = document.getElementsByTagName('head')[0],
				script = document.createElement("script");
			
			position = position || 0;
			
			script.src = url;
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
		if( typeof $this != 'undefined' ) $this.maps.event.addDomListener(window, 'load', CF_GeoPlugin_Google_Map_Shortcode);
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
	
		$str='<div class="CF_GeoPlugin_Google_Map_Shortcode" style="width:'.esc_attr($att->width).'; height:'.esc_attr($att->height).'"';
		$str.=' data-zoom="'.esc_attr($att->zoom).'"';
		$str.=' data-draggable="'.esc_attr($att->draggable).'"';
		$str.=' data-scaleControl="'.esc_attr($att->scaleControl).'"';
		$str.=' data-mapTypeControl="'.esc_attr($att->mapTypeControl).'"';
		$str.=' data-navigationControl="'.esc_attr($att->navigationControl).'"';
		$str.=' data-scrollwheel="'.esc_attr($att->scrollwheel).'"';
		
		$str.=' data-lat="'.esc_attr(!empty($att->lat)?$att->lat:$att->latitude).'"';
		$str.=' data-lng="'.esc_attr(!empty($att->lng)?$att->lng:$att->longitude).'"';
		
		if(!empty($att->title)) $str.=' data-title="'.esc_attr($att->title).'"';
		if(!empty($att->address)) $str.=' data-address="'.esc_attr($att->address).'"';
		if(!empty($att->pointer)) $str.=' data-pointer="'.esc_attr($att->pointer).'"';
		if(!empty($content)) $str.=' data-infoMaxWidth="'.esc_attr($att->infoMaxWidth).'"';
		if(!empty($att->locations)) $str.=' data-locations="'.esc_attr($att->locations).'"';
		
		$str.= '>'.$content.'</div>';		
		
		$this->add_action( 'wp_footer', 'CF_GeoPlugin_Google_Map_Shortcode_Script' );
		$this->add_action( 'admin_footer', 'CF_GeoPlugin_Google_Map_Shortcode_Script' );

		return trim( $str );
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
		$ID = base_convert(mt_rand(1000000000,PHP_INT_MAX), 10, 36); // Let's made this realy hard
	
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
				'separator'	=> ''
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
 

		$symbol_from = mb_convert_encoding( $symbols[ $from ], 'UTF-8' );
		$symbol_to = mb_convert_encoding( $symbols[ $to ], 'UTF-8' );

		$content = filter_var( $content, FILTER_SANITIZE_NUMBER_FLOAT,  FILTER_FLAG_ALLOW_FRACTION );

		if( $from === $to )
		{
			return $this->generate_converter_output( $content, $symbol_to, $atts['align'], $atts['separator'] );
		}

		$api_params = array(
			'from'		=> $from,
			'to'		=> $to,
			'amount'	=> $content
		);
		$api_url = add_query_arg( $api_params, 'http://cdn-cfgeoplugin.com/api6.0/convert.php' );

		$result = $this->curl_get( $api_url );

		$result = json_decode( $result, true );
		if( ( isset( $result['error'] ) && $result['error'] == true ) || ( !isset( $result['return'] ) || $result['return'] == false ) ) return $this->generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );

		if( !isset( $result['to_amount'] ) || empty( $result['to_amount'] ) ) return $this->generate_converter_output( $content, $symbol_from, $atts['align'], $atts['separator'] );

		return $this->generate_converter_output( $result['to_amount'], $symbol_to, $atts['align'], $atts['separator'] );
	}
}
endif;