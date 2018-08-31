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
	// All available options
	public $default_options = array(
		'enable_beta'			=>	1,
		'enable_beta_shortcode'	=>	1,
		'enable_beta_seo_csv'	=>	1,
		'enable_seo_redirection'=>	1,
		'enable_flag'			=>	1,
		'enable_defender'		=>	1,
		'enable_gmap'			=>	0,
		'enable_banner'			=>	1,
		'enable_cloudflare'		=>	0,
		'enable_dns_lookup'		=>	0,
		'enable_update'			=>	1,
		'enable_rest'			=>	1,
		'proxy_ip'				=>	'',
		'proxy_port'			=>	'',
		'proxy'					=>	0,
		'proxy_username'		=>	'',
		'proxy_password'		=>	'',
		'enable_ssl'			=>	0,
		'connection_timeout'	=>	9,
		'timeout'				=>	9,
		'map_api_key'			=>	'',
		'map_zoom'				=>	8,
		'map_scrollwheel'		=>	1,
		'map_navigationControl'	=>	1,
		'map_scaleControl'		=>	1,
		'map_mapTypeControl'	=>	1,
		'map_draggable'			=>	0,
		'map_width'				=>	'100%',
		'map_height'			=>	'400px',
		'map_infoMaxWidth'		=>	200,
		'map_latitude'			=>	'',
		'map_longitude'			=>	'',
		'block_country'			=>	'',
		'block_region'			=>	'',
		'block_ip'				=>	'',
		'block_city'			=>	'',
		'block_country_messages'=>	'',
		'license_key'			=>	'',
		'license_id'			=>	'',
		'license_expire'		=>	'',
		'license_expire_date'	=>	'',
		'license_url'			=>	'',
		'license_sku'			=>	'',
		'license_expired'		=>	'',
		'license_status'		=>	'',
		'license'				=>	0,
		'store'					=>	'https://cfgeoplugin.com',
		'store_code'			=>	'YR5pv3FU8l78v3N',
		'redirect_enable'		=>	0,
		'redirect_country'		=>	'',
		'redirect_region'		=>	'',
		'redirect_city'			=>	'',
		'redirect_url'			=>	'',
		'measurement_unit'		=>	'km',
		'redirect_http_code'	=>	302,
		'base_currency'			=>	'USD',
		'enable_woocommerce'	=>	0,
		'woocommerce_active'	=>	0,
		'rest_secret'			=>	'',
		'rest_token'			=>	array(),
		'rest_token_info'		=>	array()
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
	
	// Database tables
	const TABLE = array(
		'seo_redirection' 	=> 'cf_geo_seo_redirection',
		'rest_secret' 		=> 'cf_geo_rest_secret',
		'rest_token' 		=> 'cf_geo_rest_token'
	);
	
	// Define license codes
	const BASIC_LICENSE = 'CFGEO1M';
	const PERSONAL_LICENSE = 'CFGEOSWL';
	const FREELANCER_LICENSE = 'CFGEO3WL';
	const BUSINESS_LICENSE = 'CFGEODWL';
	const DEVELOPER_LICENSE = 'CFGEODEV';
	
	// PRIVATE - is proxy true/false (internal check)
	private static $is_proxy = false;
	
	function __construct(){
		
	}
	
	/*
	 * Access level
	 * 0 - Free
	 * 1 - Basic
	 * 2 - Personal
	 * 3 - Freelancer
	 * 4 - Business
	*/
	public static function access_level($level)
	{
		$instance = new CF_Geoplugin_Global;
		if($instance->check_defender_activation()) return 100;
		
		$check = array_flip(array(
			0,
			self::BASIC_LICENSE,
			self::PERSONAL_LICENSE,
			self::FREELANCER_LICENSE,
			self::BUSINESS_LICENSE,
			self::DEVELOPER_LICENSE
		));
		
		if(isset($check[$level]))
			return $check[$level];
		
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
		if( !CFGP_MULTISITE )
			$options = get_option('cf_geoplugin');
		else
			$options = get_site_option( 'cf_geoplugin' );
			
		if($options)
		{
			if(!empty($option_name) && isset($options[$option_name])) {
				 return $options[$option_name];
			} else {
				return $options;
			}
		}
		
		if($default===true)
		{
			return $this->default_options;
		}
		else if(!empty($option_name) && isset($this->default_options[$option_name]))
		{
			return $this->default_options[$option_name];
		}
		
		return $default;
	}
	/*
	 * Hook Update Options
	*/
	public function update_option($option_name, $value){
		if( !CFGP_MULTISITE )
			$options = get_option('cf_geoplugin');
		else
			$options = get_site_option( 'cf_geoplugin' );
		if($options)
		{
			if(is_array($value))
				$options[$option_name] = $value;
			else
				$options[$option_name] = trim($value);
		
			if( !CFGP_MULTISITE )
				update_option('cf_geoplugin', $options, true);
			else 
				update_site_option('cf_geoplugin', $options);
		
			return $options;
		}
		else // Add options to WP DB if not exists
		{
			if( !CFGP_MULTISITE ) 
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
	
	/*
	 * Hook Delete Options
	*/
	public function delete_option($option_name){
		if( !CFGP_MULTISITE )
			$options = get_option('cf_geoplugin');
		else
			$options = get_site_option( 'cf_geoplugin' );
		
		if($options)
		{
			if(isset($options[$option_name]))
			{
				unset($options[$option_name]);
				if( !CFGP_MULTISITE )
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
	public function post($name, $option="string", $default=''){
		$option = trim((string)$option);
		if(isset($_POST[$name]) && !empty($_POST[$name]))
		{        
			if(is_array($_POST[$name]))
				$is_array=true;
			else
				$is_array=false;
			
			$sanitize = array(
				'email'     =>    FILTER_SANITIZE_STRING,
				'string'    =>    FILTER_SANITIZE_STRING,
				'bool'      =>    FILTER_SANITIZE_STRING,
				'int'       =>    FILTER_SANITIZE_NUMBER_INT,
				'float'     =>    FILTER_SANITIZE_NUMBER_FLOAT,
				'html'      =>    FILTER_SANITIZE_SPECIAL_CHARS,
				'encoded'   =>    FILTER_SANITIZE_ENCODED,
				'url'       =>    FILTER_SANITIZE_URL,
				'none'      =>    'none',
				'false'     =>    'none'
			);
			
			if(is_numeric($option))
				$sanitize[$option]='none';
			
			
			if($sanitize[$option] == 'none')
			{
				if($is_array)
					$input = array_map("trim",$_POST[$name]);
				else
					$input = trim($_POST[$name]);
			}
			else
			{
				if($is_array)
				{
					$input = filter_input(INPUT_POST, $name, $sanitize[$option], FILTER_REQUIRE_ARRAY);
				}
				else
				{
					$input = filter_input(INPUT_POST, $name, $sanitize[$option]);
				}
			}
			
			switch($option)
			{
				default:
				case 'string':
				case 'html':
					$set=array(
						'options' => array('default' => $default)
					);
					if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_SANITIZE_STRING, $set);
				break;
				case 'encoded':
					return (!empty($input)?$input:$default);
				break;
				case 'url':
					$set=array(
						'options' => array('default' => $default)
					);
					if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_VALIDATE_URL, $set);
				break;
				case 'email':
					$set=array(
						'options' => array('default' => $default)
					);
					if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_VALIDATE_EMAIL, $set);
				break;
				case 'int':
					$set=array(
						'options' => array('default' => $default, 'min_range' => 0)
					);
					if($is_array) $set['flags']=FILTER_FLAG_ALLOW_OCTAL | FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_VALIDATE_INT, $set);
				break;
				case 'float':
					$set=array(
						'options' => array('default' => $default)
					);
					if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_VALIDATE_FLOAT, $set);
				break;
				case 'bool':
					$set=array(
						'options' => array('default' => $default)
					);
					if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
					
					return filter_var($input, FILTER_VALIDATE_BOOLEAN, $set);
				break;
				case 'none':
					return $input;
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
	public function get($name, $option="string", $default=''){
        $option = trim((string)$option);
        if(isset($_GET[$name]) && !empty($_GET[$name]))
        {           
            if(is_array($_GET[$name]))
                $is_array=true;
            else
                $is_array=false;
            
            $sanitize = array(
                'email'     =>    FILTER_SANITIZE_STRING,
                'string'    =>    FILTER_SANITIZE_STRING,
                'bool'      =>    FILTER_SANITIZE_STRING,
                'int'       =>    FILTER_SANITIZE_NUMBER_INT,
                'float'     =>    FILTER_SANITIZE_NUMBER_FLOAT,
                'html'      =>    FILTER_SANITIZE_SPECIAL_CHARS,
                'encoded'   =>    FILTER_SANITIZE_ENCODED,
                'url'       =>    FILTER_SANITIZE_URL,
                'none'      =>    'none',
                'false'     =>    'none'
            );
            
            if(is_numeric($option))
                $sanitize[$option]='none';
            
            
            if($sanitize[$option] == 'none')
            {
                if($is_array)
                    $input = array_map("trim",$_GET[$name]);
                else
                    $input = trim($_GET[$name]);
            }
            else
            {
                if($is_array)
                {
                    $input = filter_input(INPUT_GET, $name, $sanitize[$option], FILTER_REQUIRE_ARRAY);
                }
                else
                {
                    $input = filter_input(INPUT_GET, $name, $sanitize[$option]);
                }
            }
            
            switch($option)
            {
                default:
                case 'string':
                case 'html':
                    $set=array(
                        'options' => array('default' => $default)
                    );
                    if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_SANITIZE_STRING, $set);
                break;
                case 'encoded':
                    return (!empty($input)?$input:$default);
                break;
                case 'url':
                    $set=array(
                        'options' => array('default' => $default)
                    );
                    if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_VALIDATE_URL, $set);
                break;
                case 'email':
                    $set=array(
                        'options' => array('default' => $default)
                    );
                    if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_VALIDATE_EMAIL, $set);
                break;
                case 'int':
                    $set=array(
                        'options' => array('default' => $default, 'min_range' => 0)
                    );
                    if($is_array) $set['flags']=FILTER_FLAG_ALLOW_OCTAL | FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_VALIDATE_INT, $set);
                break;
                case 'float':
                    $set=array(
                        'options' => array('default' => $default)

                    );
                    if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_VALIDATE_FLOAT, $set);
                break;
                case 'bool':
                    $set=array(
                        'options' => array('default' => $default)
                    );
                    if($is_array) $set['flags']=FILTER_REQUIRE_ARRAY;
                    
                    return filter_var($input, FILTER_VALIDATE_BOOLEAN, $set);
                break;
                case 'none':
                    return $input;
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
			$url = "http://" . $url;
		}
		return $url;
	}
	
	/**
	* Get Custom Post Data from forms
	* @autor    Ivijan-Stefan Stipic
	* @since    5.0.0
	* @version  7.0.0
	**/
	function get_post_meta($name, $id=false, $single=true){
		global $post_type, $post, $wp_query;
		
		$name=trim($name);
		$prefix=CFGP_METABOX;
		$data=NULL;
	
		
		if($id!==false && !empty($id) && $id > 0)
			$getMeta=get_post_meta((int)$id, $prefix.$name, $single);
		else if(isset($wp_query->post) && isset($wp_query->post->ID))
			$getMeta=get_post_meta((int)$wp_query->post->ID, $prefix.$name, $single);
		else if(NULL!==get_the_id() && false!==get_the_id() && get_the_id() > 0)
			$getMeta=get_post_meta(get_the_id(),$prefix.$name, $single);
		else if(isset($post->ID) && $post->ID > 0)
			$getMeta=get_post_meta($post->ID,$prefix.$name, $single);
		else if(isset($wp_query->post) && isset($wp_query->post->ID))
			$getMeta=get_post_meta((int)$wp_query->post->ID, $prefix.$name, $single);
		else if('page' == get_option( 'show_on_front' ))
			$getMeta=get_post_meta(get_option( 'page_for_posts' ),$prefix.$name, $single);
		else if(is_home() || is_front_page() || get_queried_object_id() > 0)
			$getMeta=get_post_meta(get_queried_object_id(),$prefix.$name, $single);
		else if(isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit')
			$getMeta=get_post_meta((int)$_GET['post'], $prefix.$name, $single);
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
		$http = 'http'.((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off' || $_SERVER['SERVER_PORT']==443)?'s':'');
		$domain = str_replace(array("\\","//",":/"),array("/","/",":///"),$http.'://'.$_SERVER['HTTP_HOST']);
		$url = str_replace(array("\\","//",":/"),array("/","/",":///"),$domain.'/'.$_SERVER['REQUEST_URI']);
			
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
	public static function curl_get($url){
		$G = new CF_Geoplugin_Global;
		$options = $G->get_option();
		// Call cURL
		$output=false;
		if(function_exists('curl_version')!==false)
		{
			$cURL = curl_init();
				curl_setopt($cURL,CURLOPT_URL, $url);
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, ((bool) $options["enable_ssl"]));
				curl_setopt($cURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, (int)$options["connection_timeout"]);
				if($G->proxy()){
					curl_setopt($cURL, CURLOPT_PROXY, $options["proxy_ip"]);
					curl_setopt($cURL, CURLOPT_PROXYPORT, $options["proxy_port"]);
					$username=$options["proxy_username"];
					$password=$options["proxy_password"];
					if(!empty($username)){
						curl_setopt($cURL, CURLOPT_PROXYUSERPWD, $username.":".$password);
					}
				}
				curl_setopt($cURL, CURLOPT_TIMEOUT, (int)$options["timeout"]);
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			$output=curl_exec($cURL);
			curl_close($cURL);
		}
		else
		{
			$output=file_get_contents($url);
		}
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
				if (version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('gethostname'))
					return gethostbyname(gethostname());
				else if(version_compare(PHP_VERSION, '5.3.0') < 0 && function_exists('php_uname'))
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
				else if (version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('gethostname'))
					return gethostbyname(gethostname());
				else if(version_compare(PHP_VERSION, '5.3.0') < 0 && function_exists('php_uname'))
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
		if(is_array($list) && count($list)>0)
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
				$breakIP = explode(".",$key);
				$lastNum = ((int)end($breakIP));
				array_pop($breakIP);
				$connectIP=join(".",$breakIP).'.';
				
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
		if(count($blacklistIP)>0) $blacklistIP=array_map("trim",$blacklistIP);
		
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
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && !empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		// check any protocols
		$findIP=array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
			'HTTP_PROXY_CONNECTION',
			'HTTP_FORWARDED_FOR_IP',
			'FORWARDED_FOR_IP',
			'CLIENT_IP',
			'FORWARDED',
			'X_FORWARDED',
			'FORWARDED_FOR',
			'X_FORWARDED_FOR',
			'VIA',
			'HTTP_VIA',
			'BAN_CHECK_IP',
			'HTTP_X_FORWARDED_HOST',
		);
		// Stop all special-use addresses and blacklisted addresses
		// IP => RANGE
		$blacklistIP=$this->ip_blocked( array( $this->ip_server() ) );
		$ip = '';
		// start looping
		foreach($findIP as $http)
		{
			// Check in $_SERVER
			if (isset($_SERVER[$http]) && !empty($_SERVER[$http])){
				$ip=$_SERVER[$http];
			}
			// check in getenv() for any case
			if(empty($ip) && function_exists("getenv"))
			{
				$ip = getenv($http);
			}
			// Check if here is multiple IP's
			if(!empty($ip))
			{
				$ips=str_replace(";",",",$ip);
				$ips=explode(",",$ips);
				$ips=array_map("trim",$ips);
				
				$ipMAX=count($ips);
				if($ipMAX>0)
				{
					if($ipMAX > 1)
						$ip=end($ips);
					else
						$ip=$ips[0];
				}
				
				$ips = $ipMAX = NULL;
			}
			// Check if IP is real and valid
			if(function_exists("filter_var") && !empty($ip) && in_array($ip, $blacklistIP,true)===false && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
			{
				return $ip;
			}
			else if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip) && !empty($ip) && in_array($ip, $blacklistIP,true)===false)
			{
				return $ip;
			}
		}
		// let's try hacking into apache?
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )  && in_array($headers['X-Forwarded-For'], $blacklistIP,true)===false){
				
				// Well Somethimes can be tricky to find IP if have more then one
				$ips=str_replace(";",",",$headers['X-Forwarded-For']);
				$ips=explode(",",$ips);
				$ips=array_map("trim",$ips);
				
				$ipMAX=count($ips);
				if($ipMAX>0)
				{
					if($ipMAX > 1)
						return end($ips);
					else
						return $ips[0];
				}
				$ips = $ipMAX = NULL;
			}
		}
		// let's try the last thing, why not?
		
		if(self::is_connected())
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($ch, CURLOPT_URL, 'https://api.ipify.org?format=json');
			$result=curl_exec($ch);
			curl_close($ch);
			
			if($result)
			{
				$result = json_decode($result);
				if(isset($result->ip))
				{
					$ip = $result->ip;
					if(function_exists("filter_var") && !empty($ip) && in_array($ip, $blacklistIP,true)===false && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
					{
						return $ip;
					}
					else if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip) && !empty($ip) && in_array($ip, $blacklistIP,true)===false)
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
	 * Check is activated (this is deprecated and once removed)
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $bool
	 */
	public function check_defender_activation()
	{
		$data=get_option("cf_geo_defender_api_key");
		$data=trim($data);
		if(!empty($data) && strlen($data)>2){
			$parse=explode("-", $data);
			$parse=array_map("trim",$parse);
			if(
				$parse[0]==str_rot13("PS") && 
				$parse[3]==str_rot13("TRB") && 
				is_numeric($parse[1]) && 
				is_numeric($parse[2]) && 
				(int)$parse[2]>(int)$parse[1] && 
				strlen((int)$parse[1])===8 && 
				strlen((int)$parse[2])>=9 &&
				strlen((int)$parse[2])<=14
			){
				return true;
			}
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
		if($options['license'] == 1 && $options['license_key'] && $options['license_id'] || $this->check_defender_activation()) 
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
		$instance = new CF_Geoplugin_Global;
		// Validate
		$CF_GEOPLUGIN_OPTIONS = $instance->get_option();
		if($CF_GEOPLUGIN_OPTIONS['license'] == 1 && $CF_GEOPLUGIN_OPTIONS['license_key'] && $CF_GEOPLUGIN_OPTIONS['license_id']) :
			$ch = curl_init($CF_GEOPLUGIN_OPTIONS['store'] . '/wp-admin/admin-ajax.php');
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'action' 		=> 'license_key_validate',
					'license_key' 	=> $CF_GEOPLUGIN_OPTIONS['license_key'],
					'sku' 			=> $CF_GEOPLUGIN_OPTIONS['license_sku'],
					'store_code' 	=> $CF_GEOPLUGIN_OPTIONS['store_code'],
					'domain' 		=> self::get_host(),
					'activation_id'	=> $CF_GEOPLUGIN_OPTIONS['license_id']
				));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			$response = curl_exec($ch);
			curl_close($ch);
		
			if($response)
			{
				$license = json_decode($response);
				if(isset($license->error) && $license->error === true)
				{
					$this->update_option('license', 0, true);
					return false;
				}
			}
			return true;
		endif;
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
		} else if( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||
			(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) )
		{
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
		// If Google fail, we are in big trouble
		$connected = @fsockopen("www.google.com", 443);
		
		if ($connected){
			fclose($connected);
			return true;
		}
		
		// Maby Google have SSL problem
		$connected = @fsockopen("www.google.com", 80);
		
		if ($connected){
			fclose($connected);
			return true;
		}
		
		// Facebook can be a backup plan
		$connected = @fsockopen("www.facebook.com", 443);
		
		if ($connected){
			fclose($connected);
			return true;
		}
		
		// ...and maby SSL fail
		$connected = @fsockopen("www.facebook.com", 80);
		
		if ($connected){
			fclose($connected);
			return true;
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
	 * @version  1.3.0
	 */
	private function recursive_array_search($needle,$haystack) {
		if(!empty($needle) && is_array($haystack) && count($haystack)>0)
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
	 * Get real Hostname
	 *
	 * @since    6.0.1
	 **/
	public static function get_host(){
		$homeURL = get_home_url();
		$hostInfo = parse_url($homeURL);
		return strtolower($hostInfo['host']);
	}
	
	/**
	 * Show status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public static function runtime_status_icon($runtime, $class='')
	{
		if(round($runtime)<=0){
			echo '<span class="fa fa-battery-full '.$class.'" title="'.__('Exellent',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 1){
			echo '<span class="fa fa-battery-three-quarters '.$class.'" title="'.__('Perfect',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 2){
			echo '<span class="fa fa-battery-half '.$class.'" title="'.__('Good',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) == 3){
			echo '<span class="fa fa-battery-quarter '.$class.'" title="'.__('Week',CFGP_NAME).'"></span>';
		}
		else if(round($runtime) >= 4){
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
		$time = time();
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
		$time_units      = abs(floor((time() - $time_stamp) / $divisor));
	
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
			if (version_compare(PHP_VERSION, '7.0.0') >= 0)
				return str_rot13(bin2hex(random_bytes($length)));
			else
				return str_rot13(bin2hex(openssl_random_pseudo_bytes($length)));
		}
		else
		{
			return md5(str_replace(array('.',' ','_'),mt_rand(1000,9999),uniqid('t'.microtime())));
		}
	}
}
endif;