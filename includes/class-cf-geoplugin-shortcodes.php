<?php
/**
 * The file that defines the shortcodes into this plugin
 *
 * This function create shortcodes inside plugin
 *
 * @link      http://cfgeoplugin.com/
 * @since      5.6.2
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_Shortcodes {
	
	private $reference;
	
	function __construct($reference){
		
		$this->reference=$reference;
		
		add_shortcode( 'cf_geo', array($this,'cf_geo_shortcode') );
		
		if($this->reference->defender===true){
			if($cf_geo_enable_flag=='true' && !is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
			else if(is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
		}
		else
		{
			if(is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
		}
		
		add_shortcode( 'cf_geo_map', array($this,'cf_geo_map_shortcode') );
		add_shortcode( 'cf_geo_banner', array($this,'cf_geo_banner_shortcode') );
		
		$this->reference->loader->add_filter( 'wpcf7_form_elements', $this, 'cf7_support' );
	}
	
	/**
	 * CF Geo Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_banner_shortcode( $atts, $cont )
	{ 
       $array = shortcode_atts( array(
			'id'				=>	0,
			'posts_per_page'	=>	1,
			'class'				=>	''
        ), $atts );
		
		$id				=	$array['id'];
		$posts_per_page	=	$array['posts_per_page'];
		$class			=	$array['class'];
		
		$country = sanitize_title(isset($_SESSION[$this->reference->prefix.'country_code']) ? $_SESSION[$this->reference->prefix.'country_code'] : do_shortcode('[cf_geo return="country_code"]'));
		$country_name = sanitize_title(isset($_SESSION[$this->reference->prefix.'country']) ? $_SESSION[$this->reference->prefix.'country'] : do_shortcode('[cf_geo return="country"]'));
		$region = sanitize_title(isset($_SESSION[$this->reference->prefix.'region']) ? $_SESSION[$this->reference->prefix.'region'] : do_shortcode('[cf_geo return="region"]'));
		$city = sanitize_title(isset($_SESSION[$this->reference->prefix.'city']) ? $_SESSION[$this->reference->prefix.'city'] : do_shortcode('[cf_geo return="city"]'));
		
		$args = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	$posts_per_page,
		  'post_status'		=> 'publish',
		  'force_no_results' => true,
		  'tax_query'		=> array(
				'relation'	=> 'OR',
				array(
					'taxonomy'	=> 'cf-geoplugin-country',
					'field'		=> 'slug',
					'terms'		=> array($country, $country_name, $region, $city),
				)
			)
		);
		if($id>0) $args['post__in'] = array($id);
		
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
			}wp_reset_postdata();
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
	 * CF Geo Flag Shortcode
	 *
	 * @since    4.3.0
	 */
	private function is_flag( $flag, $atts ) {
		if(is_array($flag))
		{
			foreach ( $atts as $key => $value )
				if ( $value === $flag && is_int( $key ) ) return true;
		}
		return false;
	}
	public function cf_geo_flag_shortcode( $atts ){
		
		$img_format = ($this->is_flag('img', $atts) || $this->is_flag('image', $atts) ? true : false);
		
		$arg = shortcode_atts( array(
			'size' 		=>  '128',
			'type' 		=>  0,
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	(isset($_SESSION[$this->reference->prefix.'country_code']) ? $_SESSION[$this->reference->prefix.'country_code'] : do_shortcode('[cf_geo return="country_code"]')),
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
		
		if($arg['css']!=false)
			$css = $arg['css'];
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
			$address = do_shortcode('[cf_geo return="address"]');
			return sprintf('<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="cf-geo-flag-%s">', CFGP_URL.'/public/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
		}
		else
			return sprintf('<span class="flag-icon flag-icon-%s%s" id="cf-geo-flag-%s"%s></span>', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
	}
	
	/**
	 * CF Geo Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_shortcode( $atts, $content ){
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
		
		
		if($this->reference->defender===true)
		{
			$exclude 	= $array['exclude'];
			$include 	= $array['include'];
		}
		else
		{
			$exclude 	= false;
			$include 	= false;
		}
		
		if($ip!==false)
		{
			$gp=new CF_Geoplugin_API(array(
				'ip'	=>	$ip,
			));
			$gpReturn=$gp->returns;
			
			if($exclude!==false && !empty($exclude))
			{
				$recursive_exclude = $this->reference->recursive_array_search($exclude,((array)$gpReturn));
				
				if($recursive_exclude!==false && !empty($recursive_exclude))
					return '';
				else
				{
					return $this->reference->the_content($content);
				}
			}
			else if($include!==false && !empty($include))
			{
				$recursive_include = $this->recursive_array_search($include,((array)$gpReturn));
				
				if($recursive_include!==false && !empty($recursive_include))
				{
					return $this->reference->the_content($content);
				}
				else
					return '';
			}
			else{
				return (!empty($gpReturn[$return])?$gpReturn[$return]:$default);
			}
		}
		else
		{
			if(
				isset($return) && !empty($return) && empty($include) && empty($exclude) &&
				isset($_SESSION[$this->reference->prefix.'ip']) && !empty($_SESSION[$this->reference->prefix.'ip']) &&
				isset($_SESSION[$this->reference->prefix.'city']) && !empty($_SESSION[$this->reference->prefix.'city']) &&
				isset($_SESSION[$this->reference->prefix.'state']) && !empty($_SESSION[$this->reference->prefix.'state']) &&
				isset($_SESSION[$this->reference->prefix.'status']) && in_array($_SESSION[$this->reference->prefix.'status'],array(200,301,302,303)) &&
				$_SESSION[$this->reference->prefix.'ip'] == CF_Geoplugin::ip()
			)
			{
				if(isset($_SESSION[$this->reference->prefix.$return]) && !empty($_SESSION[$this->reference->prefix.$return])){
					return $_SESSION[$this->reference->prefix.$return];
				}else
					return $default;
			}
			else
			{
				// INCLUDE CF GEOPLUGIN
				$gp=new CF_Geoplugin_API(array(
					'ip' =>	$ip,
				));
				$gpReturn=$gp->returns;
				
				foreach($gpReturn as $name=>$value){
					$_SESSION[$this->reference->prefix.$name]=(empty($value)?'':$value);
				}
				
				if($exclude!==false && !empty($exclude))
				{
					$recursive_exclude = $this->reference->recursive_array_search($exclude,$gpReturn);
					if($recursive_exclude!==false && !empty($recursive_exclude))
						return '';
					else
					{
						return $this->reference->the_content($content);
					}
				}
				else if($include!==false && !empty($include))
				{
					$recursive_include = $this->reference->recursive_array_search($include,$gpReturn);
					
					// var_dump($recursive_include);
					
					if($recursive_include!==false && !empty($recursive_include))
					{
						return $this->reference->the_content($content);
					}
					else
						return '';
				}
				else
					return (!empty($gpReturn[$return])?$gpReturn[$return]:$default);
			}
		}
	}
	
	/**
	 * Google Map Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_map_shortcode( $atts ){
		$GID=mt_rand(99,9999).mt_rand(999,99999);
		extract(shortcode_atts( array(
			'latitude'			=>  (isset($_SESSION[$this->reference->prefix.'latitude']) ? $_SESSION[$this->reference->prefix.'latitude'] : do_shortcode('[cf_geo return="latitude"]')),
			'longitude'			=>	(isset($_SESSION[$this->reference->prefix.'longitude']) ? $_SESSION[$this->reference->prefix.'longitude'] : do_shortcode('[cf_geo return="longitude"]')),

			'zoom'				=>	get_option("cf_geo_map_zoom"),
			'width' 			=>	get_option("cf_geo_map_width"),
			'height'			=>	get_option("cf_geo_map_height"),
			
			'scrollwheel'		=>	get_option("cf_geo_map_scrollwheel"),
			'navigationControl'	=>	get_option("cf_geo_map_navigationControl"),
			'mapTypeControl'	=>	get_option("cf_geo_map_mapTypeControl"),
			'scaleControl'		=>	get_option("cf_geo_map_scaleControl"),
			'draggable'			=>	get_option("cf_geo_map_draggable"),
			
			'infoMaxWidth'		=>	get_option("cf_geo_map_infoMaxWidth"),
			
			'title'				=>	(isset($_SESSION[$this->reference->prefix.'address']) ? $_SESSION[$this->reference->prefix.'address'] : do_shortcode('[cf_geo return="address"]')),
			'address'			=>	(isset($_SESSION[$this->reference->prefix.'city']) ? $_SESSION[$this->reference->prefix.'city'] : do_shortcode('[cf_geo return="city"]'))
        ), $atts ));
		
		$KEY = get_option("cf_geo_map_api_key");
		ob_start();
	?>
    <div id="cf_geo_gmap_<?php echo $GID; ?>" style="width:<?php echo $width; ?>;height:<?php echo $height; ?>;"></div>
	<script>
      function initMap_<?php echo $GID; ?>(){
        var mapCanvas = document.getElementById("cf_geo_gmap_<?php echo $GID; ?>");
	<?php
		if(!empty($content))
		{
			$defender = new CF_Geoplugin_Defender;
			$enable=$defender->enable;
			echo '
			var contentString = \''.$content.($enable==false?'<p><small style="font-size:10px;">'.do_shortcode('[cf_geo return="credit"]').'</small></p>':'').'\';
			var infowindow = new google.maps.InfoWindow({
				content: contentString,
				maxWidth: '.$infoMaxWidth.'
			});
			';
		}
	?>
		/*	function showLatitude(position) {
				return position.coords.latitude;
			}
			function showLongitude(position) {
				return position.coords.longitude;
			}
			if (navigator.geolocation)
				var position = new google.maps.LatLng(navigator.geolocation.getCurrentPosition(showLatitude),navigator.geolocation.getCurrentPosition(showLongitude));
			else*/
			var position = new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>),
                mapOptions = {
                    center: position,

                    scrollwheel: <?php echo ((int)$scrollwheel>0?'true':'false'); ?>,
                    navigationControl: <?php echo ((int)$navigationControl>0?'true':'false'); ?>,
                    mapTypeControl: <?php echo ((int)$mapTypeControl>0?'true':'false'); ?>,
                    scaleControl: <?php echo ((int)$scaleControl>0?'true':'false'); ?>,
                    draggable: <?php echo ((int)$draggable>0?'true':'false'); ?>,

                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    zoom: <?php echo (int)$zoom; ?>,
                },
                map = new google.maps.Map(mapCanvas, mapOptions),
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    <?php echo (!empty($title)?'title:"'.$title.'",':''); ?>
                });
	<?php
		if(!empty($content))
		{
			echo '
			marker.addListener("click", function() {
				infowindow.open(map, marker);
			});
			';
		}
	?>
	  }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?<?php echo (!empty($KEY) ? 'key='.rawurlencode(trim($KEY)).'&' : ''); ?>callback=initMap_<?php echo $GID; ?>" async defer></script>
	<?php
    	return ob_get_clean();
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