<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Hooks, actions and other helpers
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */

if(!class_exists('CF_Geoplugin_Global')) :
class CF_Geoplugin_Global
{
	// Instance
	private static $instance = NULL;

	// All available options
	public $default_options = array(
		'id'						=> NULL,
		'enable_beta'				=>	1,
		'enable_beta_shortcode'		=>	1,
		'enable_beta_seo_csv'		=>	1,
		'enable_seo_redirection'	=>	1,
		'enable_flag'				=>	1,
		'enable_defender'			=>	1,
		'enable_gmap'				=>	0,
		'enable_cache'				=>	0,
		'enable_banner'				=>	1,
		'enable_cloudflare'			=>	0,
		'enable_dns_lookup'			=>	0,
		'enable_update'				=>	0,
		'enable_dashboard_widget'	=>	1,
		'enable_advanced_dashboard_widget'	=>	1,
		'enable_rest'				=>	1,
		'proxy_ip'					=>	'',
		'proxy_port'				=>	'',
		'proxy'						=>	0,
		'proxy_username'			=>	'',
		'proxy_password'			=>	'',
		'enable_ssl'				=>	0,
		'timeout'					=>	5,
		'map_api_key'				=>	'',
		'map_zoom'					=>	8,
		'map_scrollwheel'			=>	1,
		'map_navigationControl'		=>	1,
		'map_scaleControl'			=>	1,
		'map_mapTypeControl'		=>	1,
		'map_draggable'				=>	0,
		'map_width'					=>	'100%',
		'map_height'				=>	'400px',
		'map_infoMaxWidth'			=>	200,
		'map_latitude'				=>	'',
		'map_longitude'				=>	'',
		'block_country'				=>	'',
		'block_region'				=>	'',
		'block_ip'					=>	'',
		'block_city'				=>	'',
		'block_country_messages'	=>	'',
		'license_key'				=>	'',
		'license_id'				=>	'',
		'license_expire'			=>	'',
		'license_expire_date'		=>	'',
		'license_url'				=>	'',
		'license_sku'				=>	'',
		'license_expired'			=>	'',
		'license_status'			=>	'',
		'license'					=>	0,
		'store'						=>	'https://cfgeoplugin.com',
		'store_code'				=>	'YR5pv3FU8l78v3N',
		'redirect_enable'			=>	0,
		'redirect_disable_bots'		=>	0,
		'redirect_country'			=>	'',
		'redirect_region'			=>	'',
		'redirect_city'				=>	'',
		'redirect_url'				=>	'',
		'measurement_unit'			=>	'km',
		'redirect_http_code'		=>	302,
		'base_currency'				=>	'USD',
		'enable_woocommerce'		=>	0,
		'woocommerce_active'		=>	0,
		'rest_secret'				=>	'',
		'rest_token'				=>	array(),
		'rest_token_info'			=>	array(),
		'plugin_activated'			=>	NULL,
		'enable_spam_ip'			=> 0,
		'first_plugin_activation'	=> 1,
		'log_errors'				=> 0,
		'enable_seo_posts'			=> array('post', 'page'),
		'enable_geo_tag'			=> array('post', 'page'),
	);

	// Deprecated options
	public $deprecated_options = array(
		'cf_geo_enable_seo_redirection',
		'cf_geo_enable_flag',	
		'cf_geo_enable_defender',
		'cf_geo_enable_gmap',
		'cf_geo_enable_banner',
		'cf_geo_enable_cloudflare',
		'cf_geo_enable_dns_lookup',
		'cf_geo_enable_proxy_ip',
		'cf_geo_enable_proxy_port',
		'cf_geo_enable_proxy',
		'cf_geo_enable_proxy_username',
		'cf_geo_enable_proxy_password',
		'cf_geo_enable_ssl',
		'cf_geo_connection_timeout',
		'cf_geo_timeout',
		'cf_geo_map_zoom',
		'cf_geo_map_scrollwheel',
		'cf_geo_map_navigationControl',
		'cf_geo_map_scaleControl',
		'cf_geo_map_mapTypeControl',
		'cf_geo_map_draggable',
		'cf_geo_map_width',
		'cf_geo_map_height',
		'cf_geo_map_infoMaxWidth',
		'cf_geo_map_latitude',
		'cf_geo_map_longitude',
		'cf_geo_license_key',
		'cf_geo_license_id',
		'cf_geo_license_expire',
		'cf_geo_license_expire_date',
		'cf_geo_license_url',
		'cf_geo_license_expired',
		'cf_geo_license_status',
		'cf_geo_license',
		'cf_geo_store',
		'cf_geo_store_code',
		'cf_geo_auto_update'
	);
	
	// Display license names
	public $license_names = array();
	
	// Available HTTP codes
	public $http_codes = array();
	
	// Database tables
	const TABLE = array(
		'seo_redirection' 	=> 'cf_geo_seo_redirection',
		'rest_secret' 		=> 'cf_geo_rest_secret',
		'rest_token' 		=> 'cf_geo_rest_token'
	);
	
	// Define license codes
	const BASIC_LICENSE 		= 'CFGEO1M';
	const PERSONAL_LICENSE 		= 'CFGEOSWL';
	const FREELANCER_LICENSE 	= 'CFGEO3WL';
	const BUSINESS_LICENSE 		= 'CFGEODWL';
	const DEVELOPER_LICENSE 	= 'CFGEODEV';
	
	// PRIVATE - is proxy true/false (internal check)
	private static $is_proxy = false;
	
	function __construct(){		
		$this->license_names = array(
			self::BASIC_LICENSE			=> __('UNLIMITED Basic License (1 month)',CFGP_NAME),
			self::PERSONAL_LICENSE		=> __('UNLIMITED Personal License',CFGP_NAME),
			self::FREELANCER_LICENSE	=> __('UNLIMITED Freelancer License',CFGP_NAME),
			self::BUSINESS_LICENSE		=> __('UNLIMITED Business License',CFGP_NAME)
		);
		
		$this->default_options = apply_filters( 'cf_geoplugin_default_settings', $this->default_options);
		
		$this->http_codes = apply_filters( 'cf_geoplugin_http_codes', array(
			301 => __( '301 - Moved Permanently', CFGP_NAME ),
			302 => __( '302 - Found (Moved temporarily)', CFGP_NAME ),
			303 => __( '303 - See Other', CFGP_NAME ),
			307 => __( '307 - Temporary Redirect (since HTTP/1.1)', CFGP_NAME ),
			308 => __( '308 - Permanent Redirect', CFGP_NAME ),
			404 => __( '404 - Not Found (not recommended)', CFGP_NAME )
		));
		
		if( CFGP_DEV_MODE )
		{
			$this->license_names[CF_Geoplugin_Global::DEVELOPER_LICENSE] = __('UNLIMITED Developer License', CFGP_NAME);
		}
	}
	
	/**
	 * Get singleton instance of global class
	 * @since     7.4.0
	 * @version   7.4.0
	 */
	public static function get_instance()
	{
		if( NULL === self::$instance )
		{
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	/**
	 * Get singleton instance of global class
	 * @since     7.6.3
	 */
	public static function get_http_codes()
	{
		$inst = self::get_instance();
		return $inst->http_codes;
	}
	
	/**
	 * Get license name
	 */
	public static function license_name($sku){
		$init = self::get_instance();
		$license = $init->license_names;
		
		if($sku === true)
			return $license;
		
		if(isset($license[$sku]))
			return $license[$sku];
		
		return '-';
	}
	
	/*
	 * Access level
	 * 0 - Free
	 * 1 - Basic
	 * 2 - Personal
	 * 3 - Freelancer
	 * 4 - Business
	 * 5 - Developer
	*/
	public static function access_level($level)
	{
		$instance = self::get_instance();
		if($instance->check_defender_activation()) return 100;
		
		
		$check = array_flip(array(
			0,
			self::BASIC_LICENSE,
			self::PERSONAL_LICENSE,
			self::FREELANCER_LICENSE,
			self::BUSINESS_LICENSE,
			self::DEVELOPER_LICENSE
		));
		
		if(is_array($level))
		{
			if(isset($level['license']) && isset($level['license_sku']))
			{
				if($level['license'])
				{
					if(isset($check[$level['license_sku']]))
						return $check[$level['license_sku']];
				}
			}
		}
		else
		{			
			if(isset($check[$level]))
				return $check[$level];
		}
		return 0;
	} 

	/*
	 * Start Admin notice
	*/
	public static function notice()
	{
		if( class_exists( 'CF_Geoplugin_Notice' ) )
		{
			return CF_Geoplugin_Notice::instance();
		}
		
		return false;
	} 
	
	/*
	 * Hook Get Options
	*/
	public function get_option($option_name='', $default=false){
		// return default option on default:TRUE
		if($default===true)
		{
			return $this->default_options;
		}
		
		// Let's get options
		if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
			$options = get_option( 'cf_geoplugin' );
		else
			$options = get_site_option( 'cf_geoplugin' );
		
		// If options are empty get default - wee nedd it for normal function or merge new settings
		if(empty($options)){
			$options = $this->default_options;
		} else {
			$options = wp_parse_args($options, $this->default_options);
		}
		
		// Get data by option name
		if( !empty($option_name) ) {
			if(isset($options[$option_name])) {
				return $options[$option_name]; // Return single searched value
			} else {
				return $default; // Return default if field is not set yet
			}
		} else {
			// Return all options if option name is not defined
			return apply_filters( 'cf_geoplugin_settings', $options);
		}
	}
	/*
	 * Hook Update Options
	*/
	public function update_option($option_name, $value){
		if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
			$options = get_option('cf_geoplugin');
		else
			$options = get_site_option( 'cf_geoplugin' );
			
		if($options)
		{
			$option_name = preg_replace(array('/[^0-9a-z_-]/i'), array(''), $option_name);
			
			if(empty($option_name))
				return false;
			
			$options[$option_name] = self::sanitize( $value );

			if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
				update_option('cf_geoplugin', $options, true);
			else 
				update_site_option('cf_geoplugin', $options);
		
			return $options;
		}
		else // Add options to WP DB if not exists
		{
			if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) ) 
			{
				update_option( 'cf_geoplugin', $this->default_options );
				return get_option( 'cf_geoplugin' );
			}
			else 
			{
				update_site_option( 'cf_geoplugin', $this->default_options );
				return get_site_option( 'cf_geoplugin' );
			}
		}
		return false;
	}
	
	/**
	 * Sanitize string or array
	 *
	 * This functionality do automatization for the certain type of data expected in this plugin
	 */
	public static function sanitize( $str ){
		if( is_array($str) )
		{
			$data = array();
			foreach($str as $key => $obj)
			{
				$data[$key]=self::sanitize( $obj ); 
			}
			return $data;
		}
		else
		{
			$str = trim( $str );
			
			if(empty($str) && $str != 0)
				return NULL;
			else if(is_numeric($str))
			{
				if(intval( $str ) == $str)
					$str = intval( $str );
				else if(floatval($str) == $str)
					$str = floatval( $str );
				else
					$str = sanitize_text_field( $str );
			}
			else if(!is_bool($str) && in_array(strtolower($str), array('true','false'), true))
			{
				$str = ( strtolower($str) == 'true' );
			}
			else
			{
				$str = sanitize_text_field( $str );
			}
			
			return $str;
		}
	}
	
	/*
	 * Hook Delete Options
	*/
	public function delete_option($option_name){
		if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
			$options = get_option('cf_geoplugin');
		else
			$options = get_site_option( 'cf_geoplugin' );
		
		if($options)
		{
			if(isset($options[$option_name]))
			{
				unset($options[$option_name]);
				if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
					update_option('cf_geoplugin', $options, true);
				else
					update_site_option('cf_geoplugin', $options);
				return true;
			}
		}
		return false;
	}
	
	/*
	 * Hook for register_activation_hook()
	*/
	public function register_uninstall_hook($file, $function){		
		if(!is_array($function))
			$function = array(&$this, $function);	
		
		register_uninstall_hook( $file, $function );
	}
	
	/*
	 * Hook for register_deactivation_hook()
	*/
	public function register_deactivation_hook($file, $function){	
		if(!is_array($function))
			$function = array(&$this, $function);		
		
		register_deactivation_hook( $file, $function );
	}
	
	/*
	 * Hook for register_activation_hook()
	*/
	public function register_activation_hook($file, $function){
		if(!is_array($function))
			$function = array(&$this, $function);
			
		register_activation_hook( $file, $function );
	}
	/* 
	 * Hook for add_action()
	*/
	public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = array(&$this, $function_to_add);
			
		return add_action( (string)$tag, $function_to_add, (int)$priority, (int)$accepted_args );
	}
	
	/* 
	 * Hook for add_filter()
	*/
	public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = array(&$this, $function_to_add);
			
		return add_filter( (string)$tag, $function_to_add, (int)$priority, (int)$accepted_args );
	}
	
	/* 
	 * Hook for remove_filter()
	*/
	public function remove_filter($tag, $function_to_remove, $priority = 10){
		if(!is_array($function_to_remove))
			$function_to_remove = array(&$this, $function_to_remove);
			
		return remove_filter( (string)$tag, $function_to_remove, (int)$priority );
	}
	
	/* 
	 * Hook for add_shortcode()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_shortcode($tag, $function_to_add){
		if(!is_array($function_to_add))
			$function_to_add = array(&$this, $function_to_add);
			
		return add_shortcode( $tag, $function_to_add );
	}
	
	/* 
	* Generate and clean POST
	* @name          POST name
	* @option        string, int, float, bool, html, encoded, url, email
	* @default       default value
	*/
	public function post($name, $option='string', $default=''){
		$option = trim((string)$option);
		if(isset($_POST[$name]) && !empty($_POST[$name]))
		{        
			if(is_array($_POST[$name]))
				$is_array=true;
			else
				$is_array=false;
			
			if( is_numeric( $option ) || empty( $option ) ) return $default;
			else $input = $_POST[$name];
			
			switch($option)
			{
				default:
					if($is_array) return array_map( 'sanitize_text_field', $input );
					
					return sanitize_text_field( $input );
				break;
				case 'encoded':
					return (!empty($input)?$input:$default);
				break;
				case 'url':
					if($is_array) return array_map( 'esc_url', $input );
			
					return esc_url( $input );
				break;
				case 'url_raw':
					if($is_array) return array_map( 'esc_url_raw', $input );
		
					return esc_url_raw( $input );
				break;
				case 'email':
					if($is_array) return array_map( 'sanitize_email', $input );
					
					return sanitize_email( $input );
				break;
				case 'int':
					if($is_array) return array_map( 'absint', $input );
					
					return absint( $input );
				break;
				case 'float':
					if($is_array) return array_map( 'floatval', $input );
					
					return floatval( $input );
				break;
				case 'bool':
					if($is_array) return array_map( 'boolval', $input );
					
					return boolval( $input );
				break;
				case 'html_class':
					if( $is_array ) return array_map( 'sanitize_html_class', $input );

					return sanitize_html_class( $input );
				break;
				case 'title':
					if( $is_array ) return array_map( 'sanitize_title', $input );

					return sanitize_title( $input );
				break;
				case 'user':
					if( $is_array ) return array_map( 'sanitize_user', $input );

					return sanitize_user( $input );
				break;
				case 'no_html':
					if( $is_array ) return array_map( 'wp_filter_nohtml_kses', $input );

					return wp_filter_nohtml_kses( $input );
				break;
				case 'post':
					if( $is_array ) return array_map( 'wp_filter_post_kses', $input );

					return wp_filter_post_kses( $input );
				break;
			}
		}
		else
		{
			return $default;
		}
	}

	
	/* 
	* Generate and clean GET
	* @name          GET name
	* @option        string, int, float, bool, html, encoded, url, email
	* @default       default value
	*/
	public function get($name, $option='string', $default=''){
        $option = trim((string)$option);
        if(isset($_GET[$name]) && !empty($_GET[$name]))
        {           
            if(is_array($_GET[$name]))
                $is_array=true;
            else
                $is_array=false;
            
            if( is_numeric( $option ) || empty( $option ) ) return $default;
            else $input = $_GET[$name];
            
            switch($option)
            {
                default:
                    if($is_array) return array_map( 'sanitize_text_field', $input );
                    
                    return sanitize_text_field( $input );
                break;
                case 'encoded':
                    return (!empty($input)?$input:$default);
                break;
				case 'url':
					if($is_array) return array_map( 'esc_url', $input );
			
					return esc_url( $input );
				break;
				case 'url_raw':
					if($is_array) return array_map( 'esc_url_raw', $input );
		
					return esc_url_raw( $input );
				break;
                case 'email':
                    if($is_array) return array_map( 'sanitize_email', $input );
                    
                    return sanitize_email( $input );
                break;
                case 'int':
                    if($is_array) return array_map( 'absint', $input );
                    
                    return absint( $input );
                break;
                case 'float':
					if($is_array) return array_map( 'floatval', $input );
                    
                    return floatval( $input );
                break;
                case 'bool':
                    if($is_array) return array_map( 'boolval', $input );
                    
                    return boolval( $input );
				break;
				case 'html_class':
					if( $is_array ) return array_map( 'sanitize_html_class', $input );

					return sanitize_html_class( $input );
				break;
				case 'title':
					if( $is_array ) return array_map( 'sanitize_title', $input );

					return sanitize_title( $input );
				break;
				case 'user':
					if( $is_array ) return array_map( 'sanitize_user', $input );

					return sanitize_user( $input );
				break;
				case 'no_html':
					if( $is_array ) return array_map( 'wp_filter_nohtml_kses', $input );

					return wp_filter_nohtml_kses( $input );
				break;
				case 'post':
					if( $is_array ) return array_map( 'wp_filter_post_kses', $input );

					return wp_filter_post_kses( $input );
				break;
            }
        }
        else
        {
            return $default;
        }
    }
	
	/* 
	* Generate and clean $_REQUEST
	* @name          $_REQUEST name
	* @option        string, int, float, bool, html, encoded, url, email
	* @default       default value
	*/
	public function request($name, $option='string', $default=''){
        $option = trim((string)$option);
        if(isset($_REQUEST[$name]) && !empty($_REQUEST[$name]))
        {           
            if(is_array($_REQUEST[$name]))
                $is_array=true;
            else
                $is_array=false;
            
            if( is_numeric( $option ) || empty( $option ) ) return $default;
            else $input = $_REQUEST[$name];
            
            switch($option)
            {
                default:
                    if($is_array) return array_map( 'sanitize_text_field', $input );
                    
                    return sanitize_text_field( $input );
                break;
                case 'encoded':
                    return (!empty($input)?$input:$default);
                break;
				case 'url':
					if($is_array) return array_map( 'esc_url', $input );
			
					return esc_url( $input );
				break;
				case 'url_raw':
					if($is_array) return array_map( 'esc_url_raw', $input );
		
					return esc_url_raw( $input );
				break;
                case 'email':
                    if($is_array) return array_map( 'sanitize_email', $input );
                    
                    return sanitize_email( $input );
                break;
                case 'int':
                    if($is_array) return array_map( 'absint', $input );
                    
                    return absint( $input );
                break;
                case 'float':
					if($is_array) return array_map( 'floatval', $input );
                    
                    return floatval( $input );
                break;
                case 'bool':
                    if($is_array) return array_map( 'boolval', $input );
                    
                    return boolval( $input );
				break;
				case 'html_class':
					if( $is_array ) return array_map( 'sanitize_html_class', $input );

					return sanitize_html_class( $input );
				break;
				case 'title':
					if( $is_array ) return array_map( 'sanitize_title', $input );

					return sanitize_title( $input );
				break;
				case 'user':
					if( $is_array ) return array_map( 'sanitize_user', $input );

					return sanitize_user( $input );
				break;
				case 'no_html':
					if( $is_array ) return array_map( 'wp_filter_nohtml_kses', $input );

					return wp_filter_nohtml_kses( $input );
				break;
				case 'post':
					if( $is_array ) return array_map( 'wp_filter_post_kses', $input );

					return wp_filter_post_kses( $input );
				break;
            }
        }
        else
        {
            return $default;
        }
    }
	
	// Get full URL
	public function full_url($s, $use_forwarded_host = false) {
		return $this->url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
	}
	
	// Get origin URL
	public function url_origin($s, $use_forwarded_host = false) {
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on' );
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . ( ( $ssl ) ? 's' : '' );
		$port = $s['SERVER_PORT'];
		$port = ( (!$ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
		$host = ( $use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST']) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : NULL );
		$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
		return $protocol . '://' . $host;
	}
	
	// Link plugin simple link default set http
	public function addhttp($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://{$url}";
		}
		return $url;
	}
	
	/**
	* Get current page ID
	* @autor    Ivijan-Stefan Stipic
	* @since    7.6.0
	* @version  1.0.0
	**/
	function get_current_page_ID(){
		global $post, $wp_query;
		
		if(!is_null($wp_query) && isset($wp_query->post) && isset($wp_query->post->ID) && !empty($wp_query->post->ID))
			return $wp_query->post->ID;
		else if(function_exists('get_the_id') && !empty(get_the_id()))
			return get_the_id();
		else if(!is_null($post) && isset($post->ID) && !empty($post->ID))
			return $post->ID;
		else if('page' == get_option( 'show_on_front' ) && !empty(get_option( 'page_for_posts' )))
			return get_option( 'page_for_posts' );
		else if((is_home() || is_front_page()) && !empty(get_queried_object_id()))
			return get_queried_object_id();
		else if(isset($_GET['post']) && !empty($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit' && intval($_GET['post']) == $_GET['post'])
			return (int)$_GET['post'];
		else if(!is_admin() && isset($_GET['p']) && !empty($_GET['p']) && intval($_GET['p']) == $_GET['p'])
			return (int)$_GET['p'];
		
		return false;
	}
	
	/**
	* Get Custom Post Data from forms
	* @autor    Ivijan-Stefan Stipic
	* @since    5.0.0
	* @version  8.0.0
	**/
	function get_post_meta($name, $id=false, $single=true){
		global $post, $wp_query;
		
		$name=trim($name);
		$prefix=CFGP_METABOX;
		$data=NULL;
		
		$id = ((!empty($id) && intval($id) == $id) ? intval($id) : $this->get_current_page_ID());
	
		
		if( $id )
			$getMeta=get_post_meta($id, $prefix.$name, $single);
		else
			$getMeta=false;
		
		return (!empty($getMeta)?$getMeta:NULL);
	}

	
	/**
	 * Get real URL
	 *
	 * @since    4.0.0
	 */
	public static function URL(){
		$CF_Geoplugin_Global = self::get_instance();
		$http = 'http'.( $CF_Geoplugin_Global->is_ssl() ?'s':'');
		$domain = preg_replace('%:/{3,}%i','://',rtrim($http,'/').'://'.$_SERVER['HTTP_HOST']);
		$domain = rtrim($domain,'/');
		$url = preg_replace('%:/{3,}%i','://',$domain.'/'.(isset($_SERVER['REQUEST_URI']) && !empty( $_SERVER['REQUEST_URI'] ) ? ltrim($_SERVER['REQUEST_URI'], '/'): ''));
			
		return (object) array(
			"method"	=>	$http,
			"home_fold"	=>	str_replace($domain,'',home_url()),
			"url"		=>	$url,
			"domain"	=>	$domain,
			"hostname"	=>	self::get_host(),
		);
	}
	
	/**
	 * Get content via cURL
	 *
	 * @since    4.0.4
	 */
	public static function curl_get( $url, $headers = '', $new_params = array() )
	{
		$G = self::get_instance();
		$options = $G->get_option();
		
		if( empty( $headers ) )
		{
			$headers = array( 'Accept: application/json' );
		}

		// Define proxy if set
		if( isset( $options['proxy_ip'] ) && !empty( $options['proxy_ip'] ) && !defined( 'WP_PROXY_HOST' ) )
		{
			define( 'WP_PROXY_HOST', $options['proxy_ip'] );
		}
		if( isset( $options['proxy_port'] ) && !empty( $options['proxy_port'] ) && !defined( 'WP_PROXY_PORT' ) )
		{
			define( 'WP_PROXY_PORT', $options['proxy_port'] );
		}
		if( isset( $options['proxy_username'] ) && !empty( $options['proxy_username'] ) && !defined( 'WP_PROXY_USERNAME' ) )
		{
			define( 'WP_PROXY_USERNAME', $options['proxy_username'] );
		}
		if( isset( $options['proxy_password'] ) && !empty( $options['proxy_password'] ) && !defined( 'WP_PROXY_PASSWORD' ) )
		{
			define( 'WP_PROXY_PASSWORD', $options['proxy_password'] );
		}


		$output = false;

		$default_params = array(
			'timeout'	=> (int)$options["timeout"],
			'headers'	=> $headers,
		);

		$default_params = wp_parse_args( $new_params, $default_params );

		$request = wp_remote_get( esc_url_raw( $url ), $default_params );

		if( !is_wp_error( $request ) )
		{
			$output = wp_remote_retrieve_body( $request );
			if( is_wp_error( $output ) || empty( $output ) )
			{
				$output = false;
			}
		}

		if( empty( $output ) )
		{
			if(function_exists('file_get_contents'))
			{
				$context = self::set_stream_context( $headers );
				$output = @file_get_contents( $url, false, $context );
			}
		}
		
		if( empty( $output ) ) return false;

		return $output;
	}
	
	/**
	 * Detect is proxy enabled
	 *
	 * @since    4.0.0
	 * @return   $bool true/false
	 */
	public function proxy(){
		$proxy = $this->get_option("proxy");
		return ($proxy === true ? true : false);
	}
	
	/**
	 * Detect server IP address
	 *
	 * @since    4.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   $string Server IP
	 */
	public function ip_server(){
		$proxy = $this->proxy();
		if($proxy) $_SERVER['SERVER_ADDR'] = $this->get_option("proxy_ip");
	
		$findIP=array(
			'SERVER_ADDR',
			'LOCAL_ADDR',
			'SERVER_NAME',
		);
		
		$ip = '';
		// start looping
		foreach($findIP as $http)
		{
			// Check in $_SERVER
			if (isset($_SERVER[$http]) && !empty($_SERVER[$http])){
				$ip=$_SERVER[$http];
			}
			
			if(empty($ip) && function_exists("getenv"))
			{
				$ip = getenv($http);
			}
			// Check if here is multiple IP's
			if($http == 'SERVER_NAME')
			{
				$ip = gethostbyname($_SERVER['SERVER_NAME']);
			}
			// Check if IP is real and valid
			if(function_exists("filter_var") && !empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
			{
				return $ip;
			}
			else if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip) && !empty($ip))
			{
				return $ip;
			}
		}
		// Running CLI
		if(!empty($ip))
		{
			if(stristr(PHP_OS, 'WIN'))
			{
				if (version_compare(PHP_VERSION, '5.3.0', '>=') && function_exists('gethostname'))
					return gethostbyname(gethostname());
				else if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('php_uname'))
					return gethostbyname(php_uname("n"));
				else
					return gethostbyname(trim(`hostname`));
			}
			else 
			{
				if(function_exists('shell_exec')){
					$ip = shell_exec("/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'");
					return $ip;
				}
				else if (version_compare(PHP_VERSION, '5.3.0', '>=') && function_exists('gethostname'))
					return gethostbyname(gethostname());
				else if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('php_uname'))
					return gethostbyname(php_uname("n"));
				else
					return gethostbyname(trim(`hostname`));
			}
		}
		return '0.0.0.0';
	}
	
	/**
	 * List of blacklisted IP's
	 *
	 * @since   4.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @pharam  $list - array of bad IP's  IP => RANGE or IP
	 * @return  $array of blacklisted IP's
	 */
	public function ip_blocked($list=array()){
		$blacklist=array(
			'0.0.0.0'		=>	8,
			'10.0.0.0'		=>	8,
			'100.64.0.0'	=>	10,
			'127.0.0.0'		=>	8,
			'169.254.0.0'	=>	16,
			'172.16.0.0'	=>	12,
			'192.0.0.0'		=>	24,
			'192.0.2.0'		=>	24,
			'192.88.99.0'	=>	24,
			'192.168.0.0'	=>	8,
			'192.168.1.0'	=>	255,
			'198.18.0.0'	=>	15,
			'198.51.100.0'	=>	24,
			'203.0.113.0'	=>	24,
			'224.0.0.0'		=>	4,
			'240.0.0.0'		=>	4,
			'255.255.255.0'	=>	255,
		);
		if(!empty($list) && is_array($list))
		{
			foreach($list as $k => $v){
				$blacklist[$k]=$v;
			}
		}
		
		$blacklistIP=array();
		foreach($blacklist as $key=>$num)
		{
			// if address is not in range
			if(is_int($key))
			{
				$blacklistIP[]=$num;
			}
			// addresses in range
			else
			{
				// Parse IP and extract last number for mathing
				$breakIP = explode(".", $key);
				$lastNum = ((int)end($breakIP));
				array_pop($breakIP);
				$connectIP=join(".", $breakIP).'.';
				
				if($lastNum>=$num)
				{
					$blacklistIP[]=$key;
				}
				else
				{
					for($i=$lastNum; $i<=$num; $i++)
					{
						$blacklistIP[]=$connectIP.$i;
					}
				}
				$breakIP = $lastNum = $connectIP = NULL;
			}
		}
		if(!empty($blacklistIP)) $blacklistIP=array_map("trim", $blacklistIP);
		
		return $blacklistIP;
	}
	
		/**
	 * Get client IP address (high level lookup)
	 *
	 * @since	4.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $string Client IP
	 */
	public function ip()
	{
		if ($this->get_option('enable_cloudflare') && isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && !empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		// check any protocols
		$findIP=array();
		if ($this->get_option('enable_cloudflare') && isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && !empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$findIP[]='HTTP_CF_CONNECTING_IP';
		}
		$findIP=array_merge($findIP, array(
			'HTTP_X_FORWARDED_FOR', // X-Forwarded-For: <client>, <proxy1>, <proxy2> client = client ip address; https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
			'HTTP_FORWARDED_FOR', 
			'HTTP_FORWARDED', // Forwarded: by=<identifier>; for=<identifier>; host=<host>; proto=<http|https>; https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP', // Private LAN address
			'REMOTE_ADDR', // Most reliable way, can be tricked by proxy so check it after proxies
			'HTTP_CLIENT_IP', // Shared Interner services - Very easy to manipulate and most unreliable way
		));
		// Stop all special-use addresses and blacklisted addresses
		// IP => RANGE
		$blacklistIP=$this->ip_blocked( array( $this->ip_server() ) );
		$ip = '';
		// start looping
		
		foreach($findIP as $http)
		{
			if(empty($http)) continue;
			
			// Check in $_SERVER
			if (isset($_SERVER[$http]) && !empty($_SERVER[$http])){
				$ip=$_SERVER[$http];
			}
			
			// check in getenv() for any case
			if(empty($ip) && function_exists('getenv'))
			{
				$ip = getenv($http);
			}
			
			// Check if here is multiple IP's
			if(!empty($ip))
			{
				$ips=str_replace(';',',',$ip);
				$ips=explode(',',$ips);
				$ips=array_map('trim',$ips);
				
				$ipf=array();
				foreach($ips as $ipx)
				{
					if($this->filter_ip($ipx, $blacklistIP) !== false)
					{
						$ipf[]=$ipx;
					}
				}
				
				$ipMAX=count($ipf);
				if($ipMAX>0)
				{
					if($ipMAX > 1)
						return end($ipf);
					else
						return $ipf[0];
				}
				
				$ips = $ipf = $ipx = $ipMAX = NULL;
			}
			// Check if IP is real and valid
			if($this->filter_ip($ip, $blacklistIP)!==false)
			{
				return $ip;
			}
		}
		// let's try hacking into apache?
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (
				array_key_exists( 'X-Forwarded-For', $headers ) 
				&& filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )  
				&& in_array($headers['X-Forwarded-For'], $blacklistIP,true)===false
			){
				
				// Well Somethimes can be tricky to find IP if have more then one
				$ips=str_replace(';',',',$headers['X-Forwarded-For']);
				$ips=explode(',',$ips);
				$ips=array_map('trim',$ips);
				
				$ipf=array();
				foreach($ips as $ipx)
				{
					if($this->filter_ip($ipx, $blacklistIP)!==false)
					{
						$ipf[]=$ipx;
					}
				}
				
				$ipMAX=count($ipf);
				if($ipMAX>0)
				{
					if($ipMAX > 1)
						return end($ipf);
					else
						return $ipf[0];
				}
				
				$ips = $ipf = $ipx = $ipMAX = NULL;
			}
		}
		// let's try the last thing, why not?
		if( self::is_connected() )
		{
			$result = $this->curl_get( 'https://api.ipify.org?format=json' );
			
			if( empty( $result ) )
			{
				$context = self::set_stream_context( array( 'Accept: application/json' ), 'GET' );
				$result = @file_get_contents( 'https://api.ipify.org?format=json', false, $context );
			}

			if($result)
			{
				$result = json_decode($result);
				if(isset($result->ip))
				{
					$ip = $result->ip;
					if($this->filter_ip($ip)!==false)
					{
						return $ip;
					}
				}
			}
		}
		// OK, this is the end :(
		return '0.0.0.0';
	}
	
	/**
	 * Check is IP valid or not
	 *
	 * @since	7.2.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  (string) IP address or (bool) false
	 */
	public function filter_ip($ip, $blacklistIP=array())
	{
		if(empty($blacklistIP)){
			$blacklistIP=$this->ip_blocked( array( $this->ip_server() ) );
		}
		
		if(
			function_exists('filter_var') 
			&& !empty($ip) 
			&& in_array($ip, $blacklistIP,true)===false 
			&& filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false
		) {
			return $ip;
		} else if(
			preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip) 
			&& !empty($ip) 
			&& in_array($ip, $blacklistIP,true)===false
		) {
			return $ip;
		}
		
		return false;
	}
	
	/**
	 * Check is activated (this is deprecated and once will be removed)
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $bool
	 */
	public function check_defender_activation()
	{
		$data=get_option('cf_geo_defender_api_key');
		$data=trim($data);
		if(!empty($data) && strlen($data)>2){
			$parse=explode('-', $data);
			$parse=array_map('trim',$parse);
			if(
				$parse[0]==str_rot13('PS') && 
				$parse[3]==str_rot13('TRB') && 
				is_numeric($parse[1]) && 
				is_numeric($parse[2]) && 
				(int)$parse[2]>(int)$parse[1] && 
				strlen((int)$parse[1])===8 && 
				strlen((int)$parse[2])>=9 &&
				strlen((int)$parse[2])<=14
			) return true;
		}
		return false;
	}
	
	
	/**
	 * Check is activated
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $bool
	 */
	public function check_activation()
	{
		$options = $this->get_option();
		if(($options['license'] == 1 && $options['license_key'] && $options['license_id']) || $this->check_defender_activation()) 
			return true;
		return false;
	}
	
	/**
	 * Check plugin validation
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $bool
	 */
	public static function validate()
	{
		$instance = self::get_instance();
		CF_Geoplugin_Debug::log( '------------ Validation started ------------' );
		// Validate
		$CF_GEOPLUGIN_OPTIONS = $instance->get_option();
		if($CF_GEOPLUGIN_OPTIONS['license'] == 1 && $CF_GEOPLUGIN_OPTIONS['license_key'] && $CF_GEOPLUGIN_OPTIONS['license_id']) :
			$url = $CF_GEOPLUGIN_OPTIONS['store'] . '/wp-admin/admin-ajax.php';
			$data = array(
				'action' 		=> 'license_key_validate',
				'license_key' 	=> $CF_GEOPLUGIN_OPTIONS['license_key'],
				'sku' 			=> $CF_GEOPLUGIN_OPTIONS['license_sku'],
				'store_code' 	=> $CF_GEOPLUGIN_OPTIONS['store_code'],
				'domain' 		=> self::get_host(true),
				'activation_id'	=> $CF_GEOPLUGIN_OPTIONS['license_id']
			);
			CF_Geoplugin_Debug::log( 'cURL license validation send data:' );
			CF_Geoplugin_Debug::log( json_encode( $data ) );
			$url = sprintf( '%s?%s', $url, ltrim( http_build_query( $data ), '?' ) );
			$response = $instance->curl_get( $url );

			if( empty( $response ) )
			{
				$context = self::set_stream_context( array( 'Accept: application/json' ), 'GET', http_build_query( $data ) );
				$response = @file_get_contents( $url, false, $context );
			}

			if($response)
			{
				$license = json_decode($response);
				CF_Geoplugin_Debug::log( 'cURL license validation returned data:' );
				CF_Geoplugin_Debug::log( json_decode( $response ) );
				if(isset($license->error) && $license->error === true)
				{
					$instance->update_option('license', 0, true);
					CF_Geoplugin_Debug::log( 'Validation status: error' );
					return false;
				}
			}
			CF_Geoplugin_Debug::log( 'Validation status: license valid' );
			return true;

		endif;
		CF_Geoplugin_Debug::log( 'Validation status: license invalid' );
		return false;
	}
	
	/*
	 * CHECK IS SSL
	 * @since	7.0.0
	 * @return	true/false
	 */
	public function is_ssl($url = false)
	{
		if($url !== false && is_string($url)) {
			return (preg_match('/(https|ftps)/Ui', $url) !== false);
		} else if( is_admin() && defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ===true ) {
			return true;
		} else {
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
				return true;
			else if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				return true;
			else if(!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
				return true;
			else if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
				return true;
			else if(isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
				return true;
			else if(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
				return true;
		}
		return false;
	}

	/*
	 * CHECK INTERNET CONNECTION
	 * @since	7.0.0
	 * @return	true/false
	 */
	public static function is_connected()
	{
		// List connections
		$urls = array(
			'www.google.com',
			'www.facebook.com'
		);
		foreach($urls as $url)
		{
			// list ports
			foreach(array(443,80) as $port)
			{
				$connected = fsockopen($url, $port);
				if ($connected !== false){
					fclose($connected);
					return true;
				}
			}
		}
		
		// OK you not have connection - boohooo
		return false;
	}
	
	/**
	 * Find parent from assoc array
	 *
	 * @since    7.0.0
	 **/
	public function array_find_parent($array, $needle, $parent = NULL) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$pass = $parent;
				if (is_string($key)) {
					$pass = $key;
				}
				$found = $this->array_find_parent($value, $needle, $pass);
				if ($found !== false) {
					return $found;
				}
			} else if ($key === $needle) {
				return $parent;
			}
		}
	
		return false;
	}
	
	/**
	 * Find value in deep assoc array
	 *
	 * @since    7.0.0
	 **/
	public function array_find_deep($array, $search, $keys = array())
	{
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$sub = $this->array_find_deep($value, $search, array_merge($keys, array($key)));
				if (count($sub)) {
					return $sub;
				}
			} elseif ($value === $search) {
				return array_merge($keys, array($key));
			}
		}

		return array();
	}
	
	/**
	 * Recursive Array Search
	 *
	 * @since    4.2.0
	 * @version  1.3.1
	 */
	public function recursive_array_search($needle,$haystack) {
		if(!empty($needle) && !empty($haystack) && is_array($haystack))
		{
			foreach($haystack as $key=>$value)
			{
				if(is_array($value)===true)
				{
					return $this->recursive_array_search($needle,$value);
				}
				else
				{
					/* ver 1.1.0 */
					$value = trim($value);
					$needed = array_filter(array_map('trim',explode(',',$needle)));
					foreach($needed as $need)
					{
						if(strtolower($need)==strtolower($value))
						{
							return $value;
						}
					}
				}
			}
		}
		return false;
    }
	
	/**
	 * Check is bot, search engine or crawler
	 *
	 * @since    7.7.6
	 **/
	public static function is_bot() {
		
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			return (preg_match('/rambler|abacho|acoi|accona|aspseek|altavista|estyle|scrubby|lycos|geona|ia_archiver|alexa|sogou|skype|facebook|twitter|pinterest|linkedin|naver|bing|google|yahoo|duckduckgo|yandex|baidu|teoma|xing|java\/1.7.0_45|bot|crawl|slurp|spider|mediapartners|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']) ? true : false);
		}
		
		return true;
	}
	
	/**
	 * Get real Hostname
	 *
	 * @since    6.0.1
	 **/
	public static function get_host($clean=false){
		$homeURL = get_home_url();
		$hostInfo = parse_url($homeURL);
		if($clean)
			return str_replace('www.','',strtolower($hostInfo['host']));
		else
			return strtolower($hostInfo['host']);
	}
	
	/**
	 * Show status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public static function runtime_status_icon($runtime, $class='')
	{
		if(round($runtime)<=1){
			echo '<span class="fa fa-battery-full '.$class.'" title="'.__('Exellent',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 2){
			echo '<span class="fa fa-battery-three-quarters '.$class.'" title="'.__('Perfect',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 3){
			echo '<span class="fa fa-battery-half '.$class.'" title="'.__('Good',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 4){
			echo '<span class="fa fa-battery-quarter '.$class.'" title="'.__('Week',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) >= 5){
			echo '<span class="fa fa-battery-empty '.$class.'" title="'.__('Bad',CFGP_NAME).'"></span>';
		}
	}
	
	/**
	 * Lookup status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public static function lookup_status_icon($lookup, $class='')
	{
		if($lookup == 'unlimited'){
			echo '<span class="fa fa-check '.$class.'" title="'.__('UNLIMITED',CFGP_NAME).'"></span>';
		}
		else if($lookup <= CFGP_LIMIT && $lookup > (CFGP_LIMIT/2)){
			echo '<span class="fa fa-hourglass-start '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/2) && $lookup > (CFGP_LIMIT/3)){
			echo '<span class="fa fa-hourglass-halp '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/3)){
			echo '<span class="fa fa-hourglass-end '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
	}
	
	/**
	 * Check if shortcode contain certain argument
	 *
	 * @since    7.0.0
	 **/
	public function shortcode_has_argument( $argument, $attributes ) {
		if(is_string($argument) && is_array($attributes))
		{
			foreach ( $attributes as $key => $value )
				if ( $value === $argument && is_int( $key ) ) return true;
		}
		return false;
	}
	
	public function get_time_ago($time_stamp)
	{
		$time = CFGP_TIME;
		$time_difference = $time - $time_stamp;
	
		if ($time_difference >= 60 * 60 * 24 * 365.242199)
		{
			/*
			 * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 365.242199 days/year
			 * This means that the time difference is 1 year or more
			 */
			$divisor = 60 * 60 * 24 * 365.242199;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('year','years',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
		elseif ($time_difference >= 60 * 60 * 24 * 30.4368499)
		{
			/*
			 * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 30.4368499 days/month
			 * This means that the time difference is 1 month or more
			 */
			$divisor = 60 * 60 * 24 * 30.4368499;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('month','months',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
		elseif ($time_difference >= 60 * 60 * 24 * 7)
		{
			/*
			 * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 7 days/week
			 * This means that the time difference is 1 week or more
			 */
			$divisor = 60*60*24*7;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('week','weeks',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
		elseif ($time_difference >= 60 * 60 * 24)
		{
			/*
			 * 60 seconds/minute * 60 minutes/hour * 24 hours/day
			 * This means that the time difference is 1 day or more
			 */
			$divisor = 60*60*24;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('day','days',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
		elseif ($time_difference >= 60 * 60)
		{
			/*
			 * 60 seconds/minute * 60 minutes/hour
			 * This means that the time difference is 1 hour or more
			 */
			$divisor = 60 * 60;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('hour','hours',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
		else
		{
			/*
			 * 60 seconds/minute
			 * This means that the time difference is a matter of minutes
			 */
			$divisor = 60;
			return $this->get_time_ago_string($time_stamp, $divisor, _n('minute','minutes',abs(floor(($time - $time_stamp) / $divisor)), CFGP_NAME));
		}
	}
	
	private function get_time_ago_string($time_stamp, $divisor, $time_unit)
	{
		$time_units      = abs(floor((CFGP_TIME - $time_stamp) / $divisor));
	
		settype($time_units, 'string');
	
		if ($time_units === '0')
		{
			return 'less than 1 ' . $time_unit;
		}
		elseif ($time_units === '1')
		{
			return '1 ' . $time_unit;
		}
		else
		{
			/*
			 * More than "1" $time_unit. This is the "plural" message.
			 */
			// TODO: This pluralizes the time unit, which is done by adding "s" at the end; this will not work for i18n!
			return $time_units . ' ' . $time_unit;
		}
	}
	
	public function analyse_file($file, $capture_limit_in_kb = 10) {
		// capture starting memory usage
		$output['peak_mem']['start']    = memory_get_peak_usage(true);
	
		// log the limit how much of the file was sampled (in Kb)
		$output['read_kb']                 = $capture_limit_in_kb;
		
		// read in file
		$fh = fopen($file, 'r');
			$contents = fread($fh, ($capture_limit_in_kb * 1024)); // in KB
		fclose($fh);
		
		// specify allowed field delimiters
		$delimiters = array(
			'comma'     => ',',
			'semicolon' => ';',
			'tab'         => "\t",
			'pipe'         => '|',
			'colon'     => ':'
		);
		
		// specify allowed line endings
		$line_endings = array(
			'rn'         => "\r\n",
			'n'         => "\n",
			'r'         => "\r",
			'nr'         => "\n\r"
		);
		
		// loop and count each line ending instance
		foreach ($line_endings as $key => $value) {
			$line_result[$key] = substr_count($contents, $value);
		}
		
		// sort by largest array value
		asort($line_result);
		
		// log to output array
		$output['line_ending']['results']     = $line_result;
		$output['line_ending']['count']     = end($line_result);
		$output['line_ending']['key']         = key($line_result);
		$output['line_ending']['value']     = $line_endings[$output['line_ending']['key']];
		$lines = explode($output['line_ending']['value'], $contents);
		
		// remove last line of array, as this maybe incomplete?
		array_pop($lines);
		
		// create a string from the legal lines
		$complete_lines = implode(' ', $lines);
		
		// log statistics to output array
		$output['lines']['count']     = count($lines);
		$output['lines']['length']     = strlen($complete_lines);
		
		// loop and count each delimiter instance
		foreach ($delimiters as $delimiter_key => $delimiter) {
			$delimiter_result[$delimiter_key] = substr_count($complete_lines, $delimiter);
		}
		
		// sort by largest array value
		asort($delimiter_result);
		
		// log statistics to output array with largest counts as the value
		$output['delimiter']['results']     = $delimiter_result;
		$output['delimiter']['count']         = end($delimiter_result);
		$output['delimiter']['key']         = key($delimiter_result);
		$output['delimiter']['value']         = $delimiters[$output['delimiter']['key']];
		
		// capture ending memory usage
		$output['peak_mem']['end'] = memory_get_peak_usage(true);
		return $output;
	}
	
	/*
	* Generate token
	*/
	public function generate_token($length=16){
		if(function_exists('openssl_random_pseudo_bytes') || function_exists('random_bytes'))
		{
			if (version_compare(PHP_VERSION, '7.0.0', '>='))
				return substr(str_rot13(bin2hex(random_bytes(ceil($length * 2)))), 0, $length);
			else
				return substr(str_rot13(bin2hex(openssl_random_pseudo_bytes(ceil($length * 2)))), 0, $length);
		}
		else
		{
			return substr(str_replace(array('.',' ','_'),mt_rand(1000,9999),uniqid('t'.microtime())), 0, $length);
		}
	}

	/**
	* Alias of get_terms() functionality for lower versions of wordpress
	* @link      https://developer.wordpress.org/reference/functions/get_terms/
	* @version   1.0.0
	*/
	public function cf_geo_get_terms( $args = array(), $deprecated = '' ) 
	{ 
		$term_query = new WP_Term_Query();
	
		/*
		* Legacy argument format ($taxonomy, $args) takes precedence.
		*
		* We detect legacy argument format by checking if
		* (a) a second non-empty parameter is passed, or
		* (b) the first parameter shares no keys with the default array (ie, it's a list of taxonomies)
		*/
		$_args = wp_parse_args( $args );
		$key_intersect  = array_intersect_key( $term_query->query_var_defaults, (array) $_args );
		$do_legacy_args = $deprecated || empty( $key_intersect );
	
		if ( $do_legacy_args ) {
			$taxonomies = (array) $args;
			$args = wp_parse_args( $deprecated );
			$args['taxonomy'] = $taxonomies;
		} else {
			$args = wp_parse_args( $args );
			if ( isset( $args['taxonomy'] ) && null !== $args['taxonomy'] ) {
				$args['taxonomy'] = (array) $args['taxonomy'];
			}
		}
	
		if ( ! empty( $args['taxonomy'] ) ) {
			foreach ( $args['taxonomy'] as $taxonomy ) {
				if ( ! taxonomy_exists( $taxonomy ) ) {
					return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.' ) );
				}
			}
		}
	
		$terms = $term_query->query( $args );
	
		// Count queries are not filtered, for legacy reasons.
		if ( ! is_array( $terms ) ) {
			return $terms;
		}
	
		/**
		 * Filters the found terms.
		 *
		 * @since 2.3.0
		 * @since 4.6.0 Added the `$term_query` parameter.
		 *
		 * @param array         $terms      Array of found terms.
		 * @param array         $taxonomies An array of taxonomies.
		 * @param array         $args       An array of cf_geo_get_terms() arguments.
		 * @param WP_Term_Query $term_query The WP_Term_Query object.
		 */
		return apply_filters( 'get_terms', $terms, $term_query->query_vars['taxonomy'], $term_query->query_vars, $term_query );
	}

	/**
	 * Check user's city for defender or seo redirection
	 */
	public function check_user_by_city( $city )
	{
		$CFGEO = $GLOBALS['CFGEO'];
		if( is_array( $city ) )
		{
			$city = array_map( 'strtolower', $city );
			if( isset( $city[0] ) && !empty( $city[0] ) && isset( $CFGEO['city'] ) && in_array( sanitize_title_with_dashes( $CFGEO['city'] ), $city, true ) ) return true;
		}
		elseif( is_string( $city ) )
		{
			if( !empty( $city ) && isset( $CFGEO['city'] ) && strtolower( $city ) === sanitize_title_with_dashes($CFGEO['city'] ) ) return true;
		}

		return false;
	}

	/**
	 * Check user's region for defender or seo redirection
	 */
	public function check_user_by_region( $region )
	{
		$CFGEO = $GLOBALS['CFGEO'];
		if( is_array( $region ) )
		{
			if( isset( $region[0] ) && !empty( $region[0] ) )
			{
				$region = array_map( 'strtolower', $region );
				// Supports region code and region name
				if( isset( $CFGEO['region_code'] ) && in_array( strtolower( $CFGEO['region_code'] ), $region, true ) ) return true; 
				if( isset( $CFGEO['region'] ) && in_array( sanitize_title_with_dashes( $CFGEO['region'] ), $region, true ) ) return true;
			}
		}
		elseif( is_string( $region ) )
		{
			if( !empty( $region ) )
			{
				// Supports region code and region name
				if( isset( $CFGEO['region_code'] ) && strtolower( $region ) === strtolower( $CFGEO['region_code'] ) ) return true; 
				if( isset( $CFGEO['region'] ) && strtolower( $region ) === sanitize_title_with_dashes( $CFGEO['region'] ) ) return true;
			}
		}

		return false;
	}

	/**
	 * Check user's country for defender or seo redirection
	 */
	public function check_user_by_country( $country )
	{
		$CFGEO = $GLOBALS['CFGEO'];

		if( is_array( $country ) )
		{
			if( isset( $country[0] ) && !empty( $country[0] ) )
			{
				$country = array_map( 'strtolower', $country );
				// Supports country code and name
				if( isset( $CFGEO['country_code'] ) && in_array( strtolower( $CFGEO['country_code'] ), $country, true ) ) return true;
				if( isset( $CFGEO['country'] ) && in_array( sanitize_title_with_dashes( $CFGEO['country'] ), $country, true ) ) return true;
			}
		}
		elseif( is_string( $country ) )
		{
			if( !empty( $country ) )
			{
				// Supports country code and name
				if( isset( $CFGEO['country_code'] ) && strtolower( $country ) === strtolower( $CFGEO['country_code'] ) ) return true;
				if( isset( $CFGEO['country'] ) && strtolower( $country ) === sanitize_title_with_dashes( $CFGEO['country'] ) ) return true;
			}
		}

		return false;
	}

	/**
	 * Set stream context
	 */
	public static function set_stream_context( $header = array(), $method = 'POST', $content = '' )
	{
		$options = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

		$header = array_merge( array( 'Content-Type: application/x-www-form-urlencoded' ), $header );
		
		if( $options['proxy'] )
		{
			$proxy_host = $options['proxy_ip'];
			$proxy_port = $options['proxy_port'];
			$proxy_username = $options['proxy_username'];
			$proxy_password = $options['proxy_password'];
			if( !empty( $proxy_username ) && !empty( $proxy_password ) )
			{
				$auth = base64_encode( $proxy_username . ':' . $proxy_password );
				return stream_context_create(
					array(
						'http' => array(
							'method'  			=> $method,
							'proxy' 			=> "tcp://{$proxy_host}:{$proxy_port}",
							'request_fulluri' 	=> true,
							'header' 			=> array_merge( array( "Proxy-Authorization: Basic {$auth}" ), $header ),
							'content'			=> $content
						)
					)
				);
			}
			else // Proxy authentication is not required
			{ 
				return stream_context_create(
					array(
						'http' => array(
							'method'  			=> $method,
							'proxy' 			=> "tcp://{$proxy_host}:{$proxy_port}",
							'request_fulluri' 	=> true,
							'header' 			=> $header,
							'content'			=> $content
						)
					)
				);
			}
		}
		else
		{
			return stream_context_create(
				array(
					'http' => array(
						'method'  	=> $method,
						'header' 	=> $header,
						'content'	=> $content	
					)
				)
			);
		}
	}

	/**
	 * Auto update plugin
	 */
	public function plugin_auto_update()
	{
		if( !class_exists( 'WP_Upgrader' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/class-wp-upgrader.php' ) );
		if( !class_exists( 'Plugin_Upgrader' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/class-plugin-upgrader.php' ) );
		if( !class_exists( 'WP_Upgrader_Skin' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/class-wp-upgrader-skin.php' ) );
		if( !class_exists( 'Plugin_Upgrader_Skin' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/class-plugin-upgrader-skin.php' ) );
		if( !function_exists( 'show_messages' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/misc.php' ) );
		if( !function_exists( 'request_filesystem_credentials' ) ) require_once( path_join( ABSPATH, 'wp-admin/includes/file.php' ) );

		$Updater = new Plugin_Upgrader();

		$Updater->upgrade( plugin_basename( CFGP_FILE ) );
	}

	/**
	 * Generate convert outoput
	 */
	public function generate_converter_output( $amount, $symbol, $position = 'L', $separator = '' )
	{
		if( strtoupper( $position ) === 'L' || strtoupper( $position ) == 'LEFT' ) return sprintf( '%s%s%s', $symbol, $separator, $amount );
		else return sprintf( '%s%s%s', $amount, $separator, $symbol );
	}
	
	/**
	 * Replacemant for the mb_convert_encoding - Setup for the UCS-4
	 */
	public static function mb_convert_encoding($string, $from='UTF-8', $to='UCS-4'){
		return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($m) {
			$char = current($m);
			$utf = iconv( $from, $to, $char);
			return sprintf('&#x%s;', ltrim(strtoupper(bin2hex($utf)), '0'));
		}, $string);
	}
	
	/**
	 * Get post type
	 */
	public static function get_post_type ($find = false) {
		global $post, $parent_file, $typenow, $current_screen, $pagenow;
		
		$post_type = NULL;
		
		if($post && (property_exists($post, 'post_type') || method_exists($post, 'post_type')))
			$post_type = $post->post_type;
		
		if(empty($post_type) && !empty($current_screen) && (property_exists($current_screen, 'post_type') || method_exists($current_screen, 'post_type')) && !empty($current_screen->post_type))
			$post_type = $current_screen->post_type;
		
		if(empty($post_type) && !empty($typenow))
			$post_type = $typenow;
			
		if(empty($post_type) && function_exists('get_current_screen'))
			$post_type = get_current_screen();
		
		if(empty($post_type) && isset($_REQUEST['post']) && !empty($_REQUEST['post']) && function_exists('get_post_type') && $get_post_type = get_post_type((int)$_REQUEST['post']))
			$post_type = $get_post_type;
			
		if(empty($post_type) && isset($_REQUEST['post_type']) && !empty($_REQUEST['post_type']))
			$post_type = sanitize_key($_REQUEST['post_type']);
	
		if(empty($post_type) && in_array($pagenow, array('edit.php', 'post-new.php')))
			$post_type = 'post';
		
		if(is_array($find))
		{
			return in_array($post_type, $find, true);
		}
		else if(is_string($find))
		{
			return ($post_type === $find);
		}
		
		return $post_type;
	}
	
	public static function is_plugin_active($plugin)
	{
		if(!function_exists('is_plugin_active'))
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		return is_plugin_active($plugin);
	}
}
endif;