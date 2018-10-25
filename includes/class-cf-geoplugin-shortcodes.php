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

		// Deprecated Google Map shortcode
		$this->add_shortcode( 'cf_geo_map', 'google_map' );
		// Official Google Map Shortcode
		$this->add_shortcode( 'cfgeo_map', 'google_map' );
		
		// Deprecated Banner shortcode
		$this->add_shortcode( 'cf_geo_banner', 'geo_banner' );
		// Official Banner shortcode
		$this->add_shortcode( 'cfgeo_banner', 'geo_banner' );
		
		// We need CF7 shortcode support
		$this->add_filter( 'wpcf7_form_elements', 'cf7_support' );
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
			'country' 	=>	$CFGEO['country_code'],
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
			return sprintf('<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="cf-geo-flag-%s">', CFGP_ASSETS.'/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
		}
		else
			return sprintf('<span class="flag-icon flag-icon-%s%s" id="cf-geo-flag-%s"%s></span>', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
	}

	/**
	 * Google Map Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function google_map( $atts, $content = '' )
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];

		$GID = base_convert(mt_rand(1000000000,PHP_INT_MAX), 10, 36); // Let's made this realy hard
		extract( shortcode_atts( array( 
			'latitude'				=>	$CF_GEOPLUGIN_OPTIONS['map_latitude'],
			'longitude'				=> 	$CF_GEOPLUGIN_OPTIONS['map_longitude'],
			
			'zoom'					=>	$CF_GEOPLUGIN_OPTIONS['map_zoom'],
			'width'					=>	$CF_GEOPLUGIN_OPTIONS['map_width'],
			'height'				=> 	$CF_GEOPLUGIN_OPTIONS['map_height'],

			'scrollwheel'			=>	$CF_GEOPLUGIN_OPTIONS['map_scrollwheel'],
			'navigationControl'		=>	$CF_GEOPLUGIN_OPTIONS['map_navigationControl'],
			'mapTypeControl'		=>	$CF_GEOPLUGIN_OPTIONS['map_mapTypeControl'],
			'scaleControl'			=>	$CF_GEOPLUGIN_OPTIONS['map_scaleControl'],
			'draggable'				=>	$CF_GEOPLUGIN_OPTIONS['map_draggable'],
			
			'infoMaxWidth'			=>	$CF_GEOPLUGIN_OPTIONS['map_infoMaxWidth'],

			'title'					=>	$CFGEO['address'],
			'address'				=>	$CFGEO['city']
		), $atts ));

		$key = $CF_GEOPLUGIN_OPTIONS['map_api_key'];

		if( empty( $latitude ) )
		{
			$latitude = $CFGEO['latitude'];
		}
		if( empty( $longitude ) )
		{
			$longitude = $CFGEO['longitude'];
		}
		ob_start();
		?>
		<div id = "cfgeo_google_map_<?php echo $GID; ?>" style = "width:<?php echo $width; ?>;height:<?php echo $height; ?>"></div>
		<script>
			function initMap_<?php echo $GID; ?>()
			{
				<?php
					if( !empty( $content ) )
					{
						$content = str_replace(array('"'), array('\\"'), $content);
						$content = preg_replace(array("/(\n|\r\n)/","/(\t|\t+)/"), array('\n', ''), $content);
						$content = trim($content,'\n');
						
						echo '
							var contentString = "'. $content .'";
							var infoWindow = new google.maps.InfoWindow({
								content: contentString,
								maxWidth: '. $infoMaxWidth .'
							});
						';
					}
				?>
				var mapCanvas = document.getElementById('cfgeo_google_map_<?php echo $GID; ?>');
				var position = new google.maps.LatLng( <?php echo $latitude; ?>, <?php echo $longitude; ?> ),
					mapOptions = {
						center: position,
						
						scrollwheel: <?php echo ( (int)$scrollwheel === 1 ? 'true' : 'false' ); ?>,
						navigationControl: <?php echo ( (int)$navigationControl === 1 ? 'true' : 'false' ); ?>,
						mapTypeControl: <?php echo ( (int)$mapTypeControl === 1 ? 'true' : 'false' ); ?>,
						scaleControl: <?php echo ( (int)$scaleControl === 1 ? 'true' : 'false' ); ?>,
						draggable: <?php echo ( (int)$draggable === 1 ? 'true' : 'false' ); ?>,

						mapTypeId: google.maps.MapTypeId.ROADMAP,
						zoom: <?php echo (int)$zoom; ?> 
					},
					map = new google.maps.Map( mapCanvas, mapOptions ),
					marker = new google.maps.Marker({
						position: position,
						map: map,
						<?php echo (!empty($title) ? 'title:"'.$title.'",':''); ?>
					});

					<?php
						if( !empty( $content ) )
						{
							echo '
							marker.addListener("click", function() {
								infoWindow.open(map, marker);
							});
							';
						}
					?>
			}
		</script>
		<script src="https://maps.googleapis.com/maps/api/js?<?php echo (!empty($key) ? 'key='.rawurlencode(trim($key)).'&' : ''); ?>callback=initMap_<?php echo $GID; ?>" async defer></script>
		<?php

		return ob_get_clean();
	}
	
	
	/**
	 * Geo Banner Shortcode
	 * 
	 * @since		7.0.0
	 */
	public function geo_banner( $atts, $cont )
	{ 
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];

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
}
endif;