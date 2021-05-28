<?php
/**
 * Utilities
 *
 * Main global classes with active hooks
 *
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_U')) :
class CFGP_U {
	private static $user;
	
	/*
	 * Get user
	 * @return        object/null
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function get_user($user_id_or_email=NULL){
		
		// If REQUEST is made
		if(isset($_REQUEST['cfgp_user']) && empty($user_id_or_email))
		{
			if($user = get_user_by('ID', absint($_REQUEST['cfgp_user']))) {
				return $user;
			} else {
				return NULL;
			}
		}
		
		// If function is called
		if($user_id_or_email)
		{
			if(is_numeric($user_id_or_email) && $user = get_user_by('ID', absint($user_id_or_email)))
			{
				self::$user = $user;
			}
			else if (!filter_var($user_id_or_email, FILTER_VALIDATE_EMAIL) && $user = get_user_by('email', $user_id_or_email))
			{
				self::$user = $user;
			}
		}
		
		// Automatic find
		if(empty(self::$user))
		{
			if(is_author())
			{
				global $current_user;
				
				if($current_user && $user = get_user_by('ID', $current_user->ID))
				{
					self::$user = $user;
				}
				else if($author_id = get_query_var( 'author' ))
				{
					self::$user = get_user_by( 'id', $author_id );
				}
				else if($author_name = get_query_var( 'author_name' ))
				{
					self::$user = get_user_by( 'slug', $author_name );
				}
			}
			else if(is_user_logged_in())
			{
				if($user = wp_get_current_user())
				{
					self::$user = $user;
				}
			}
		}
		
		return self::$user;
	}
	
	/**
	 * Get content via cURL
	 *
	 * @since    4.0.4
	 */
	public static function curl_get( $url, $headers = '', $new_params = array(), $json = false )
	{
		global $cfgp_cache;
		
		$cache_name = 'cfgp-curl_get-'.md5(serialize(array($url, $headers, $new_params, $json)));
		if($cache = $cfgp_cache->get($cache_name)){
			return $cache;
		}
		
		if( empty( $headers ) )
		{
			$headers = array( 'Accept: application/json' );
		}

		// Define proxy if set
		if( !defined( 'WP_PROXY_HOST' ) && $proxy_ip = CFGP_Options::get('proxy_ip', false))
		{
			define( 'WP_PROXY_HOST', $proxy_ip );
		}
		if( !defined( 'WP_PROXY_PORT' ) && $proxy_port = CFGP_Options::get('proxy_port', false))
		{
			define( 'WP_PROXY_PORT', $proxy_port );
		}
		if( !defined( 'WP_PROXY_USERNAME' ) && $proxy_username = CFGP_Options::get('proxy_username', false) )
		{
			define( 'WP_PROXY_USERNAME', $proxy_username );
		}
		if( !defined( 'WP_PROXY_PASSWORD' ) && $proxy_password = CFGP_Options::get('proxy_password', false) )
		{
			define( 'WP_PROXY_PASSWORD', $proxy_password );
		}


		$output = false;

		$default_params = array(
			'timeout'	=> CFGP_Options::get('timeout', 5),
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

		if($json !== false) $output = json_decode($output, true);
		
		$cfgp_cache->set($cache_name, $output);
		
		return $output;
	}
	
	/*
	 * Decode content
	 * @return        string
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function decode(string $content){
		$content = rawurldecode($content);
		$content = htmlspecialchars_decode($content);
		$content = html_entity_decode($content);
		$content = strtr($content, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
		return $content;
	}
	
	/*
	 * Get image source URL by post
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function get_attachment_image_src_by_post($post, $size='thumbnail', $icon = false){
		$attachment_id = get_post_thumbnail_id($post);
		if($attachment_id) {
			$src = wp_get_attachment_image_src($attachment_id, $size, $icon);
			if($src && isset($src[0])){
				return $src[0];
			}
		}
		
		return NULL;
	}
	
	/* 
	 * Generate unique token
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function generate_token(int $length=16){
		if(function_exists('openssl_random_pseudo_bytes') || function_exists('random_bytes'))
		{
			if (version_compare(PHP_VERSION, '7.0.0', '>='))
				return substr(str_rot13(bin2hex(random_bytes(ceil($length * 2)))), 0, $length);
			else
				return substr(str_rot13(bin2hex(openssl_random_pseudo_bytes(ceil($length * 2)))), 0, $length);
		}
		else
		{
			return substr(str_replace(['.',' ','_'],mt_rand(1000,9999),uniqid('t'.microtime())), 0, $length);
		}
	}
	
	/*
	 * Return plugin informations
	 * @return        array/object
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function plugin_info(array $fields = []) {
		
		$cache_name = CFGP_NAME . '-plugin_info-' . md5(serialize($fields));
		
		if($cache = wp_cache_get($cache_name, CFGP_NAME)) return $cache;
		
        if ( is_admin() ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once( WP_ADMIN_DIR . '/includes/plugin-install.php' );
			}
			/** Prepare our query */
			//donate_link
			//versions
			$plugin_data = plugins_api( 'plugin_information', [
				'slug' => CFGP_NAME,
				'fields' => array_merge([
					'active_installs' => false,           // rounded int
					'added' => false,                     // date
					'author' => false,                    // a href html
					'author_block_count' => false,        // int
					'author_block_rating' => false,       // int
					'author_profile' => false,            // url
					'banners' => false,                   // array( [low], [high] )
					'compatibility' => false,            // empty array?
					'contributors' => false,              // array( array( [profile], [avatar], [display_name] )
					'description' => false,              // string
					'donate_link' => false,               // url
					'download_link' => false,             // url
					'downloaded' => false,               // int
					// 'group' => false,                 // n/a 
					'homepage' => false,                  // url
					'icons' => false,                    // array( [1x] url, [2x] url )
					'last_updated' => false,              // datetime
					'name' => false,                      // string
					'num_ratings' => false,               // int
					'rating' => false,                    // int
					'ratings' => false,                   // array( [5..0] )
					'requires' => false,                  // version string
					'requires_php' => false,              // version string
					// 'reviews' => false,               // n/a, part of 'sections'
					'screenshots' => false,               // array( array( [src],  ) )
					'sections' => false,                  // array( [description], [installation], [changelog], [reviews], ...)
					'short_description' => false,        // string
					'slug' => false,                      // string
					'support_threads' => false,           // int
					'support_threads_resolved' => false,  // int
					'tags' => false,                      // array( )
					'tested' => false,                    // version string
					'version' => false,                   // version string
					'versions' => false,                  // array( [version] url )
				], $fields)
			]);
		 	
			wp_cache_set($cache_name, $plugin_data, CFGP_NAME);
			
			return $plugin_data;
		}
    }
	
	/*
	 * Set cookie
	 * @verson    1.0.0
	*/
	public static function setcookie ($name, $val, $time = 0){
		if( !headers_sent() ) {
			
			setcookie( $name, $val, (time()+absint($time)), COOKIEPATH, COOKIE_DOMAIN );
			
			if(CFGP_Options::get('cache-support', 'yes') == 'yes') {
				self::cache_flush();
			}
		}
	}
	
	/*
	 * Flush Cache
	 * @verson    1.0.0
	*/
	public static function cache_flush () {
		global $post, $user;
		
		// Standard cache
		header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		if(function_exists('nocache_headers')) {
			nocache_headers();
		}
		
		// Flush WP cache
		if (function_exists('w3tc_flush_all')) {
			wp_cache_flush();
		}
		
		// W3 Total Cache
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		}
		
		// WP Fastest Cache
		if (function_exists('wpfc_clear_all_cache')) {
			wpfc_clear_all_cache(true);
		}
		
		// Clean stanrad WP cache
		if($post && function_exists('clean_post_cache')) {
			clean_post_cache( $post );
		}
		
		if($user && function_exists('clean_post_cache')) {
			clean_user_cache( $user );
		}
	}
	
	
	/*
	 * Get current URL
	 * @verson    1.0.0
	*/
	public static function get_current_url()
	{
		global $wp;
		return add_query_arg( [], home_url( $wp->request ) );
	}
	
	/**
	 * Get real Hostname
	 *
	 * @since    6.0.1
	 **/
	public static function get_host($clean=false){
		$hostInfo = self::parse_url();
		if($clean)
			return str_replace('www.','',strtolower($hostInfo['domain']));
		else
			return strtolower($hostInfo['domain']);
	}
	
	/**
	 * Parse URL
	 * @verson    1.0.0
	 */
	public static function parse_url(){
		global $cfgp_cache;
		
		$parse_url = $cfgp_cache->get('parse_url');
		
		if(!$parse_url) {
			$http = 'http'.( self::is_ssl() ?'s':'');
			$domain = preg_replace('%:/{3,}%i','://',rtrim($http,'/').'://'.$_SERVER['HTTP_HOST']);
			$domain = rtrim($domain,'/');
			$url = preg_replace('%:/{3,}%i','://',$domain.'/'.(isset($_SERVER['REQUEST_URI']) && !empty( $_SERVER['REQUEST_URI'] ) ? ltrim($_SERVER['REQUEST_URI'], '/'): ''));
				
			$parse_url = $cfgp_cache->set('parse_url', [
				'method'	=>	$http,
				'home_fold'	=>	str_replace($domain,'',home_url()),
				'url'		=>	$url,
				'domain'	=>	$domain,
			]);
		}
		
		return $parse_url;
	}
	
	/*
	 * CHECK IS SSL
	 * @return	true/false
	 */
	public static function is_ssl($url = false)
	{
		global $cfgp_cache;

		$ssl = $cfgp_cache->get('is_ssl');

		if($url !== false && is_string($url)) {
			return (preg_match('/(https|ftps)/Ui', $url) !== false);
		} else if(empty($ssl)) {
			if(
				( is_admin() && defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ===true )
				|| (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
				|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
				|| (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
				|| (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
				|| (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
			) {
				$ssl = $cfgp_cache->set('is_ssl', true);
			}
		}
		return $ssl;
	}
	
	/*
	* Check is block editor screen
	* @since     8.0.0
	*/
	public static function is_editor()
	{
		global $cfgp_cache;

		$is_editor = $cfgp_cache->get('is_editor');

		if(empty($is_editor)) {
			if (version_compare(get_bloginfo( 'version' ), '5.0', '>=')) {
				if(!function_exists('get_current_screen')){
					include_once ABSPATH  . '/wp-admin/includes/screen.php';
				}
				$get_current_screen = get_current_screen();
				if(is_callable(array($get_current_screen, 'is_block_editor')) && method_exists($get_current_screen, 'is_block_editor')) {
					$is_editor = $cfgp_cache->set('is_editor', $get_current_screen->is_block_editor());
				}
			} else {
				$is_editor = $cfgp_cache->set('is_editor', ( isset($_GET['action']) && isset($_GET['post']) && $_GET['action'] == 'edit' && is_numeric($_GET['post']) ) );
			}
		}

		return $is_editor;
	}
	
	/*
	 * CHECK INTERNET CONNECTION
	 * @since	7.0.0
	 * @return	true/false
	 */
	public static function is_connected()
	{
		global $cfgp_cache;
		
		if($cfgp_cache->get('is_connected')){
			return true;
		}
		
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
					return $cfgp_cache->set('is_connected', true);
				}
			}
		}

		// OK you not have connection - boohooo
		return false;
	}
	
	/**
	 * Detect is proxy enabled
	 *
	 * @since    4.0.0
	 * @return   $bool true/false
	 */
	public static function proxy(){
		return (CFGP_Options::get('proxy', false) ? true : false);
	}
	
	/**
	 * Check is bot, search engine or crawler
	 *
	 * @since    7.7.6
	 **/
	public static function is_bot($ip = false)
	{
		// Search by IP
		if(empty($ip)) {
			$ip = CFGP_IP::get();
		}
		
		$bots = apply_filters( 'cf_geoplugin_bot_ip_list', array(
			'65.214.45.143',	// Ask
			'65.214.45.148',	// Ask
			'66.235.124.192',	// Ask
			'66.235.124.7',		// Ask
			'66.235.124.101',	// Ask
			'66.235.124.193',	// Ask
			'66.235.124.73',	// Ask
			'66.235.124.196',	// Ask
			'66.235.124.74',	// Ask
			'63.123.238.8',		// Ask
			'202.143.148.61',	// Ask
			
			'66.249.66.1',		// Google
			
			'157.55.33.18',		// Bing
			'123.125.66.120',	// Baidu
			'141.8.142.60',		// Yandex
			
			'72.94.249.34',		// DuckDuckGo
			'72.94.249.35',		// DuckDuckGo
			'72.94.249.36',		// DuckDuckGo
			'72.94.249.37',		// DuckDuckGo
			'72.94.249.38',		// DuckDuckGo
			
			'68.180.228.178'	// Yahoo
		));
		
		if($ip && in_array($ip, $bots, true)) return true;
		
		
		// Get by user agent (wide range)
		if(isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']))
		{
			return (preg_match('/rambler|abacho|acoi|accona|aspseek|altavista|estyle|scrubby|lycos|geona|ia_archiver|alexa|sogou|skype|facebook|duckduckbot|duckduck|twitter|pinterest|linkedin|skype|naver|bing|google|yahoo|duckduckgo|yandex|baidu|baiduspider|teoma|xing|java\/1.7.0_45|bot|crawl|slurp|spider|mediapartners|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']) ? true : false);
		}
		
		return false;
	}
	
	/**
	 * PRIVATE: Set stream context
	 * @since	1.3.5
	 */
	public static function set_stream_context( $header = array(), $method = 'POST', $content = '' )
	{	
		$header = array_merge( array( 'Content-Type: application/x-www-form-urlencoded' ), $header );
		
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
	
	/**
	 * Find value in deep assoc array
	 *
	 * @since    7.0.0
	 **/
	public static function array_find_deep($array, $search, $keys = array())
	{
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$sub = self::array_find_deep($value, $search, array_merge($keys, array($key)));
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
	 * Find parent from assoc array
	 *
	 * @since    7.0.0
	 **/
	public static function array_find_parent($array, $needle, $parent = NULL) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$pass = $parent;
				if (is_string($key)) {
					$pass = $key;
				}
				$found = self::array_find_parent($value, $needle, $pass);
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
	 * Recursive Array Search
	 *
	 * @since    4.2.0
	 * @version  1.3.1
	 */
	public static function recursive_array_search($needle,$haystack) {
		if(!empty($needle) && !empty($haystack) && is_array($haystack))
		{
			foreach($haystack as $key=>$value)
			{
				if(is_array($value)===true)
				{
					return self::recursive_array_search($needle,$value);
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
	
	/*
	 * Check is attribute exists in the shortcodes
	*/
	public static function is_attribute_exists($find, $atts) {
		
		if(is_array($atts))
		{
			foreach($atts as $key => $val)
			{
				if(is_numeric($key))
				{
					if($val === $find) return true;
				}
				else
				{
					if($key === $find) return true;
				}
			}
		}
		
		return false;
	}
	
	/*
	 * Print country flag
	 */
	public static function admin_country_flag($country_code = '', $size='21px'){
		global $cfgp_cache;
		
		if(empty($country_code))
		{
			$API = $cfgp_cache->get('API');
			$country_code = $API['country_code'];
		}
		
		$flag_slug = trim(strtolower($country_code));
		
		$md5 = md5($flag_slug.$size);
		
		if($cache = $cfgp_cache->get("admin_country_flag_{$md5}")) {
			return $cache;
		}
				
		$flag = '';
		if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag_slug.'.svg')) {
			$flag = sprintf('<img src="%s" alt="%s" style="max-width:%s;">', CFGP_ASSETS.'/flags/4x3/'.$flag_slug.'.svg', $flag_slug, $size);
		}
		
		$cfgp_cache->set("admin_country_flag_{$md5}", $flag);
		
		return $flag;
	}
	
	/*
	 * Request Integer
	 */
	public static function request_int($name, $default=0, $session = false, $session_name = NULL){
		
		if(!$session_name) $session_name = $name;
		
		if( $session === true )
		{
			if($return = wp_cache_get($session_name, 'cfgp')){
				return $return;
			}
		}
		
		$return = absint(filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT, array(
			'options'=>array(
				'default'=>filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT, array(
					'options'=>array(
						'default'=>$default
					)
				))
			)
		)));
		
		if( $session === true )
		{
			wp_cache_set($session_name, $return, 'cfgp');
		}
		
		return $return;
	}
	
	/*
	 * Request Float
	 */
	public static function request_float($name, $default=0, $session = false, $session_name = NULL){
		if(!$session_name) $session_name = $name;
		
		if( $session === true )
		{
			if($return = wp_cache_get($session_name, 'cfgp')){
				return $return;
			}
		}
		
		$return = floatval(filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, array(
			'options'=>array(
				'default'=>filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_FLOAT, array(
					'options'=>array(
						'default'=>$default
					)
				))
			)
		)));
		
		if( $session === true )
		{
			wp_cache_set($session_name, $return, 'cfgp');
		}
		
		return $return;
	}
	
	/*
	 * Request string
	 */
	public static function request_string($name, $default=NULL, $session = false, $session_name = NULL){
		if(!$session_name) $session_name = $name;
		
		if( $session === true )
		{
			if($return = wp_cache_get($session_name, 'cfgp')){
				return $return;
			}
		}
		
		$return = sanitize_text_field(filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING, array(
			'options'=>array(
				'default'=>filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING, array(
					'options'=>array(
						'default'=>$default
					)
				))
			)
		)));
		
		if( $session === true )
		{
			wp_cache_set($session_name, $return, 'cfgp');
		}
		
		return $return;
	}
	
	/*
	 * Request Emain
	 */
	public static function request_email($name, $default = NULL){
		return sanitize_email(filter_input(INPUT_POST, $name, FILTER_SANITIZE_EMAIL, array(
			'options'=>array(
				'default'=>filter_input(INPUT_GET, $name, FILTER_SANITIZE_EMAIL, array(
					'options'=>array(
						'default'=>$default
					)
				))
			)
		)));
	}
	
	/*
	 * Request Array
	 */
	public static function request_array($name, $sanitize = 'sanitize_text_field', $default = array()){
		$request = isset( $_REQUEST[$name] ) ? ((array)$_REQUEST[$name]) : $default;
		$request = array_map($sanitize, $request);		
		return $request;
	}
	
	/*
	 * Request Bool
	 */
	public static function request_bool($name){
		return (isset($_REQUEST[$name]) && $_REQUEST[$name] == 'true');
	}
	
	/*
	 * Returns API fields
	 */
	public static function api($name = false, $default = '') {
		global $cfgp_cache;
		if(empty($name)) {
			return $cfgp_cache->get('API');
		} else {
			return isset($cfgp_cache->get('API')[$name]) ? $cfgp_cache->get('API')[$name] : $default;
		}
	}
	
	public static function dump(){
		if(func_num_args() === 1)
		{
			$a = func_get_args();
			echo '<pre class="cfgp-dump">', var_dump( $a[0] ), '</pre>';
		}
		else if(func_num_args() > 1)
			echo '<pre class="cfgp-dump">', var_dump( func_get_args() ), '</pre>';
		else
			throw Exception('You must provide at least one argument to this function.');
	}
}
endif;