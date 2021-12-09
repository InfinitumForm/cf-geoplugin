<?php
/**
 * Utilities
 *
 * Main global classes with active hooks
 *
 * @link            http://infinitumform.com/
 * @since           8.0.0
 * @package         cf-geoplugin
 * @author          Ivijan-Stefan Stipic
 * @version       	3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_U')) :
class CFGP_U {
	private static $user;
	
	/*
	 * Get HTTP codes
	 * @return        object/null
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function get_http_codes(){
		return apply_filters( 'cfgp_http_codes', array(
			301 => __( '301 - Moved Permanently', CFGP_NAME ),
			302 => __( '302 - Found (Moved temporarily)', CFGP_NAME ),
			303 => __( '303 - See Other', CFGP_NAME ),
			307 => __( '307 - Temporary Redirect (since HTTP/1.1)', CFGP_NAME ),
			308 => __( '308 - Permanent Redirect', CFGP_NAME ),
			404 => __( '404 - Not Found (not recommended)', CFGP_NAME )
		));
	}
	
	/*
	 * Get HTTP code name
	 * @return        object/null
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function get_http_code_name($code){
		$code = (int)$code;
		$http_codes = self::get_http_codes();
		return (isset($http_codes[$code]) ? $http_codes[$code] : NULL);
	}
	
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
			if(is_numeric($user_id_or_email) && $user = get_user_by('ID', absint($user_id_or_email))) {
				self::$user = $user;
			}
			else if (!filter_var($user_id_or_email, FILTER_VALIDATE_EMAIL) && $user = get_user_by('email', $user_id_or_email)) {
				self::$user = $user;
			}
		}
		
		// Automatic find
		if(empty(self::$user))
		{
			if(is_author())
			{
				global $current_user;
				
				if($current_user && $user = get_user_by('ID', $current_user->ID)) {
					self::$user = $user;
				}
				else if($author_id = get_query_var( 'author' )) {
					self::$user = get_user_by( 'id', $author_id );
				}
				else if($author_name = get_query_var( 'author_name' )) {
					self::$user = get_user_by( 'slug', $author_name );
				}
			}
			else if(is_user_logged_in())
			{
				if($user = wp_get_current_user()) {
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
		
		$cache_name = 'cfgp-curl_get-'.md5(serialize(array($url, $headers, $new_params, $json)));
		if($cache = CFGP_Cache::get($cache_name)){
			return $cache;
		}
		
		if( empty( $headers ) ) {
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
			if( is_wp_error( $output ) || empty( $output ) ) {
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
		
		if( empty( $output ) ) {
			return false;
		}

		if($json !== false) {
			$output = json_decode($output, true);
		}
		
		CFGP_Cache::set($cache_name, $output);
		
		return $output;
	}
	
	/**
	 * POST content via cURL
	 *
	 * @since    4.0.4
	 */
	public static function curl_post( $url, $post_data = array(), $headers = '', $new_params = array(), $json = false )
	{
		
		$cache_name = 'cfgp-curl_post-'.md5(serialize(array($url, $headers, $new_params, $json)));
		if($cache = CFGP_Cache::get($cache_name)){
			return $cache;
		}
		
		if( empty( $headers ) ) {
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
			'method'	=> 'POST',
			'timeout'	=> CFGP_Options::get('timeout', 5),
			'headers'	=> $headers,
			'body'		=> $post_data
		);

		$default_params = wp_parse_args( $new_params, $default_params );

		$request = wp_remote_post( esc_url_raw( $url ), $default_params );

		if( !is_wp_error( $request ) )
		{
			$output = wp_remote_retrieve_body( $request );
			if( is_wp_error( $output ) || empty( $output ) ) {
				$output = false;
			}
		}

		if( empty( $output ) )
		{
			if(function_exists('file_get_contents'))
			{
				$context = self::set_stream_context( $headers, 'POST', http_build_query($post_data) );
				$output = @file_get_contents( $url, false, $context );
			}
		}
		
		if( empty( $output ) ) {
			return false;
		}

		if($json !== false) {
			$output = json_decode($output, true);
		}
		
		CFGP_Cache::set($cache_name, $output);
		
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
			if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
				return substr(str_rot13(bin2hex(random_bytes(ceil($length * 2)))), 0, $length);
			} else {
				return substr(str_rot13(bin2hex(openssl_random_pseudo_bytes(ceil($length * 2)))), 0, $length);
			}
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
	public static function plugin_info(array $fields = [], $slug = false) {
		
		$cache_name = CFGP_NAME . '-plugin_info-' . md5(serialize($fields) . ($slug!==false ? $slug : CFGP_NAME));
		
		if($cache = CFGP_Cache::get($cache_name)) {
			return $cache;
		}
		
        if ( is_admin() ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once( WP_ADMIN_DIR . '/includes/plugin-install.php' );
			}
			/** Prepare our query */
			//donate_link
			//versions
			$plugin_data = plugins_api( 'plugin_information', [
				'slug' => ($slug!==false ? $slug : CFGP_NAME),
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
		 	
			CFGP_Cache::set($cache_name, $plugin_data);
			
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
	 * @verson    2.0.0
	*/
	public static function cache_flush () {
		global $post, $user, $w3_plugin_totalcache;

		// Standard cache
		header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');

		// Set nocache headers
		if(function_exists('nocache_headers')) {
			nocache_headers();
		}

		// Flush WP cache
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}

		// W3 Total Cache
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		} else if( $w3_plugin_totalcache ) {
			$w3_plugin_totalcache->flush_all();
		}

		// WP Fastest Cache
		if (function_exists('wpfc_clear_all_cache')) {
			wpfc_clear_all_cache(true);
		}

		// WP Rocket
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}

		// WP Super Cache
		if(function_exists( 'prune_super_cache' ) && function_exists( 'get_supercache_dir' )) {
			prune_super_cache( get_supercache_dir(), true );
		}

		// Cache Enabler.
		if (function_exists( 'clear_site_cache' )) {
			clear_site_cache();
		}

		// Clean stanrad WP cache
		if($post && function_exists('clean_post_cache')) {
			clean_post_cache( $post );
		}

		// Comet Cache
		if(class_exists('comet_cache') && method_exists('comet_cache', 'clear')) {
			comet_cache::clear();
		}

		// Clean user cache
		if($user && function_exists('clean_user_cache')) {
			clean_user_cache( $user );
		}
	}
	
	/*
	 * Safe and SEO redirections to new location
	 * @verson    1.0.0
	*/
	public static function redirect($location, int $status=302, bool $safe=NULL){
		$status = absint($status);
		
		// Prevent AJAX
		if(defined('DOING_AJAX') && DOING_AJAX){
			return false;
		}
		
		// Validate URL
		if (!filter_var($location, FILTER_VALIDATE_URL)){
			return false;
		}
		
		// Automatic switch to safe redirection
		if( NULL === $safe ){
			$safe = (strpos($location, self::parse_url()['domain']) !== false);
		}
		
		// Check good status code
		if ($safe && ($status < 300 || 399 < $status) ) {
			new Exception( __( 'HTTP redirect status code must be a redirection code, 3xx.' ) );
			return false;
		}
		
		// Cache control
		if( CFGP_Options::get('cache-support', 'yes') == 'yes' ) {
			self::cache_flush();
		}
		
		// Disable referrer
		if( CFGP_Options::get('hide_http_referrer_headers', 0) ) {
			header('Referrer-Policy: no-referrer');
		}
		
		if (!headers_sent())
		{			
			if(function_exists('wp_redirect'))
			{
				// Emulate wp_safe_redirect()
				if($safe) {
					$location = wp_validate_redirect( $location, apply_filters( 'cfgp/safe_redirect/fallback', site_url(), $status ) );
				}
				// Do redirection
				return wp_redirect( $location, $status, CFGP_NAME );
			}
			else
			{
				// Windows server need some nice touch
				global $is_IIS;
				if ( ! $is_IIS && function_exists('status_header') && defined('PHP_SAPI') && 'cgi-fcgi' !== PHP_SAPI ) {
					status_header( $status ); // This causes problems on IIS and some FastCGI setups.
				}
				// Inform application who redirects
				header('X-Redirect-By: ' . CFGP_NAME);
				// Standard redirect
				header("Location: {$location}", true, $status);
				// Optional workaround for an IE bug (thanks Olav)
				header('Connection: close');
				
				return true;
			}
		}
		else
		{
			die('<meta http-equiv="refresh" content="time; URL=' . esc_url($location) . '" />');
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
		if(CFGP_IP::is_localhost()) {
			return 'localhost';
		}
			
		$hostInfo = self::parse_url();
		if($clean) {
			return preg_replace('/https?:\/\/|w{3}\./i','',strtolower($hostInfo['domain']));
		} else {
			return strtolower($hostInfo['domain']);
		}
	}
	
	/**
	 * Parse URL
	 * @verson    1.0.0
	 */
	public static function parse_url(){
		
		$parse_url = CFGP_Cache::get('parse_url');
		
		if(!$parse_url) {
			$http = 'http'.( self::is_ssl() ?'s':'');
			$domain = preg_replace('%:/{3,}%i','://',rtrim($http,'/').'://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
			$domain = rtrim($domain,'/');
			$url = preg_replace('%:/{3,}%i','://',$domain.'/'.(isset($_SERVER['REQUEST_URI']) && !empty( $_SERVER['REQUEST_URI'] ) ? ltrim($_SERVER['REQUEST_URI'], '/'): ''));
				
			$parse_url = CFGP_Cache::set('parse_url', array(
				'method'	=>	$http,
				'home_fold'	=>	str_replace($domain,'',home_url()),
				'url'		=>	$url,
				'domain'	=>	$domain,
			));
		}
		
		return $parse_url;
	}
	
	/**
	 * Get URL
	 * @verson    1.0.0
	 */
	public static function get_url(){
		
		$current_url = CFGP_Cache::get('current_url');
		
		if(!$current_url) {
			$url = self::parse_url();
			$url = $url['url'];
				
			$current_url = CFGP_Cache::set('current_url', $url);
		}
		
		return $current_url;
	}
	
	/*
	 * CHECK IS SSL
	 * @return	true/false
	 */
	public static function is_ssl($url = false)
	{

		$ssl = CFGP_Cache::get('is_ssl');

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
				$ssl = CFGP_Cache::set('is_ssl', true);
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

		$is_editor = CFGP_Cache::get('is_editor');

		if(empty($is_editor)) {
			if (version_compare(get_bloginfo( 'version' ), '5.0', '>=')) {
				if(!function_exists('get_current_screen')){
					include_once ABSPATH  . '/wp-admin/includes/screen.php';
				}
				$get_current_screen = get_current_screen();
				if(is_callable(array($get_current_screen, 'is_block_editor')) && method_exists($get_current_screen, 'is_block_editor')) {
					$is_editor = CFGP_Cache::set('is_editor', $get_current_screen->is_block_editor());
				}
			} else {
				$is_editor = CFGP_Cache::set('is_editor', ( isset($_GET['action']) && isset($_GET['post']) && $_GET['action'] == 'edit' && is_numeric($_GET['post']) ) );
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
		
		if(CFGP_Cache::get('is_connected')){
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
					return CFGP_Cache::set('is_connected', true);
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
		
		$range = apply_filters( 'cfgp/crawler/ip/range', array(
			// Google
			'64.18.0.0'			=>	'64.18.15.255',
			'64.233.160.0'		=>	'64.233.191.255',
			'66.249.64.0'		=>	'66.249.95.255 ',
			'66.102.0.0'		=>	'66.102.15.255',
			'72.14.192.0'		=>	'72.14.255.255',
			'74.125.0.0'		=>	'74.125.255.255',
			'108.177.8.0'		=>	'108.177.15.255',
			'172.217.0.0'		=>	'172.217.31.255',
			'173.194.0.0'		=>	'173.194.255.255',
			'207.126.144.0'		=>	'207.126.159.255',
			'209.85.128.0'		=>	'209.85.255.255',
			'216.58.192.0'		=>	'216.58.223.255',
			'216.239.32.0'		=>	'216.239.63.255',
			// MSN
			'64.4.0.0'			=>	'64.4.63.255 ',
			'65.52.0.0'			=>	'65.55.255.255 ',
			'131.253.21.0'		=>	'131.253.47.255',
			'157.54.0.0'		=>	'157.60.255.255',
			'207.46.0.0'		=>	'207.46.255.255',
			'207.68.128.0'		=>	'207.68.207.255',
			// Yahoo
			'8.12.144.0'		=>	'8.12.144.255 ',
			'66.196.64.0'		=>	'66.196.127.255 ',
			'66.228.160.0'		=>	'66.228.191.255 ',
			'67.195.0.0'		=>	'67.195.255.255 ',
			'68.142.192.0'		=>	'68.142.255.255 ',
			'72.30.0.0'			=>	'72.30.255.255',
			'74.6.0.0'			=>	'74.6.255.255',
			'98.136.0.0'		=>	'98.139.255.255',
			'202.160.176.0'		=>	'202.160.191.255',
			'209.191.64.0'		=>	'209.191.127.255',
			// Bing
			'104.146.0.0'		=>	'104.146.63.255',
			'104.146.100.0'		=>	'104.146.113.255',
			// Yandex
			'100.43.64.0'		=>	'100.43.79.255',
			'100.43.80.0'		=>	'100.43.83.255',
			// Baidu
			'103.6.76.0'		=>	'103.6.79.255',
			'104.193.88.0'		=>	'104.193.91.255',
			'106.12.0.0'		=>	'106.13.255.255',
			'115.231.36.136'	=>	'115.231.36.159',
			'39.156.69.79',
			'220.181.38.148',
			// DuckDuckGo
			'50.16.241.113'		=>	'50.16.241.117',
			'54.208.100.253'	=>	'54.208.102.37',
			'72.94.249.34'		=>	'72.94.249.38',
			'23.21.227.69',
			'40.88.21.235',
			'50.16.247.234',
			'52.204.97.54',
			'52.5.190.19',
			'54.197.234.188',
			'107.21.1.8',
			// Sogou
			'118.191.216.42'	=>	'118.191.216.57',
			'119.28.109.132',
			// Ask
			'65.214.45.143'		=>	'65.214.45.148',
			'66.235.124.7',
			'66.235.124.101',
			'66.235.124.193',
			'66.235.124.73',
			'66.235.124.196',
			'66.235.124.74',
			'63.123.238.8',
			'202.143.148.61',
			// Pinterest
			'54.236.1.1'		=>	'54.236.1.255',
			'54.82.14.182',
			'54.81.171.36',
			'23.20.24.147',
			'54.237.150.66',
			'54.237.197.55',
			'54.211.68.214',
			'54.234.164.192',
			'50.16.155.205',
			'23.20.84.153',
			'54.224.131.213',
			// Facebook
			'69.63.176.0'		=>	'69.63.176.21',
			'69.63.184.0'		=>	'69.63.184.21',
			'66.220.144.0'		=>	'66.220.144.21',
			'69.63.176.0'		=>	'69.63.176.20',
			'31.13.24.0'		=>	'31.13.24.21',
			'31.13.64.0'		=>	'31.13.64.18',
			'69.171.224.0'		=>	'69.171.224.19',
			'74.119.76.0'		=>	'74.119.76.22',
			'103.4.96.0'		=>	'103.4.96.22',
			'173.252.64.0'		=>	'173.252.64.18',
			'204.15.20.0'		=>	'204.15.20.22',
			// Twitter
			'199.59.156.0'		=>	'199.59.156.255',
			// Linkedin
			'144.2.22.0'		=>	'144.2.22.24',
			'144.2.224.0'		=>	'144.2.224.24',
			'144.2.225.0'		=>	'144.2.225.24',
			'144.2.228.0'		=>	'144.2.228.24',
			'144.2.229.0'		=>	'144.2.229.24',
			'144.2.233.0'		=>	'144.2.233.24',
			'144.2.237.0'		=>	'144.2.237.24',
			'216.52.16.0'		=>	'216.52.16.24',
			'216.52.17.0'		=>	'216.52.17.24',
			'216.52.18.0'		=>	'216.52.18.24',
			'216.52.20.0'		=>	'216.52.20.24',
			'216.52.21.0'		=>	'216.52.21.24',
			'216.52.22.0'		=>	'216.52.22.24',
			'65.156.227.0'		=>	'65.156.227.24',
			'8.39.53.0'			=>	'8.39.53.24'
		));
		
		$ip2long = sprintf('%u', ip2long($ip));
			
		if($ip2long !== false)
		{
			foreach($range as $start => $end)
			{
				$end = sprintf('%u', ip2long($end));
				$start = sprintf('%u', ip2long($start));
				
				$is_key = ($start === false || $start == 0);
				
				if($end === false || $end == 0) continue;
				
				if(is_numeric($start) && $is_key && $end == $ip2long)
				{
					return true;
				}
				else
				{
					if(!$is_key && $ip2long >= $start && $ip2long <= $end)
					{
						return true;
					}
				}
			}
		}
		
		
		// Get by user agent (wide range)
		if(isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']))
		{
			return (preg_match('/rambler|abacho|ac(oi|cona)|aspseek|al(tavista|exa)|estyle|scrubby|lycos|geona|ia_archiver|sogou|facebook|duckduck(bot|go)?|twitter|pinterest|linkedin|skype|naver|bing(bot)?|google|ya(hoo|ndex)|baidu(spider)?|teoma|xing|java\/1\.7\.0_45|crawl|slurp|spider|mediapartners|\sbot\s|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']) ? true : false);
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
			} elseif (strtolower($value) === strtolower($search)) {
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
			} else if (strtolower($key) === strtolower($needle)) {
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
	public static function recursive_array_search($needle, $haystack, $relative = false) {
		if(!empty($needle) && !empty($haystack) && is_array($haystack))
		{
			foreach($haystack as $key=>$value)
			{
				if(is_array($value)===true)
				{
					return self::recursive_array_search($needle, $value, $relative);
				}
				else
				{
					/* ver 1.1.0 */
					$value = trim($value);
					$needed = array_filter(array_map('trim',explode(',',$needle)));
					foreach($needed as $need)
					{
						if($relative === true) {							
							if(stripos($value, $need, 0) !== false)
							{
								return $value;
							}
						} else {
							if(strtolower($need) == strtolower($value))
							{
								return $value;
							}
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
					if($val === $find) {
						return true;
					}
				}
				else
				{
					if($key === $find) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/*
	 * Print country flag
	 */
	public static function admin_country_flag($country_code = '', $size='21px'){
		
		if(empty($country_code))
		{
			$API = CFGP_Cache::get('API');
			$country_code = $API['country_code'];
		}
		
		$flag_slug = trim(strtolower($country_code));
		
		$md5 = md5($flag_slug.$size);
		
		if($cache = CFGP_Cache::get("admin_country_flag_{$md5}")) {
			return $cache;
		}
				
		$flag = '';
		if(file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag_slug.'.svg')) {
			$flag = sprintf('<img src="%s" alt="%s" style="max-width:%s;">', CFGP_ASSETS.'/flags/4x3/'.$flag_slug.'.svg', $flag_slug, $size);
		}
		
		CFGP_Cache::set("admin_country_flag_{$md5}", $flag);
		
		return $flag;
	}
	
	/*
	 * Request Integer
	 */
	public static function request_int($name, $default=0, $session = false, $session_name = NULL){
		
		if(!$session_name) $session_name = $name;
		
		if( $session === true )
		{
			if($return = CFGP_Cache::get($session_name)){
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
			CFGP_Cache::set($session_name, $return);
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
			if($return = CFGP_Cache::get($session_name)){
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
			CFGP_Cache::set($session_name, $return);
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
			if($return = CFGP_Cache::get($session_name)){
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
			CFGP_Cache::set($session_name, $return);
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
		$request = isset( $_REQUEST[$name] ) && !empty( $_REQUEST[$name] ) ? ((array)$_REQUEST[$name]) : $default;
		$request = CFGP_Options::sanitize($request);
		return $request;
	}
	
	/*
	 * Request Bool
	 */
	public static function request_bool($name){
		return (isset($_REQUEST[$name]) && $_REQUEST[$name] == 'true');
	}
	
	/*
	 * Request
	 */
	public static function request($name, $default = ''){
		return (isset($_REQUEST[$name]) ?  $_REQUEST[$name] : $default);
	}
	
	/*
	 * Returns API fields
	 */
	public static function api($name = false, $default = '') {
		if(empty($name)) {
			return CFGP_Cache::get('API');
		} else {
			return isset(CFGP_Cache::get('API')[$name]) ? CFGP_Cache::get('API')[$name] : $default;
		}
	}
	
	/*
	 * Next level of var_dump()
	 */
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
	
	/*
	 * Fragment cache
	 */
	public static function fragment_caching($str, $cache = false, $wrap_before = '', $wrap_after = ''){
		if(W3TC_DYNAMIC_SECURITY && function_exists('w3tc_flush_all') && $cache)
		{
			return sprintf('<!-- mfunc %2$s -->%1$s<!-- /mfunc %2$s -->', $wrap_before.$str.$wrap_after, W3TC_DYNAMIC_SECURITY);
		}
		return $str;
	}
	
	
	/**
	* Get current page ID
	* @autor    Ivijan-Stefan Stipic
	* @since    1.0.7
	* @version  2.0.0
	******************************************************************/
	public static function get_page_ID(){
		global $post, $wp;

		if($current_page_id = CFGP_Cache::get('current_page_id')){
			return $current_page_id;
		}

		if($id = self::get_page_ID__private__wp_query())
			return CFGP_Cache::set('current_page_id', $id);
		else if($id = self::get_page_ID__private__get_the_id())
			return CFGP_Cache::set('current_page_id', $id);
		else if(!is_null($post) && isset($post->ID) && !empty($post->ID))
			return CFGP_Cache::set('current_page_id', $post->ID);
		else if($post = self::get_page_ID__private__GET_post())
			return CFGP_Cache::set('current_page_id', $post);
		else if($p = self::get_page_ID__private__GET_p())
			return CFGP_Cache::set('current_page_id', $p);
		else if($page_id = self::get_page_ID__private__GET_page_id())
			return CFGP_Cache::set('current_page_id', $page_id);
		else if($wp && isset($wp->request) && function_exists('get_page_by_path') && ($current_page=get_page_by_path($wp->request)))
			$page_id = CFGP_Cache::set('current_page_id', $current_page->ID);
		else if(!is_admin() && $id = self::get_page_ID__private__query())
			return $id;
		else if($id = self::get_page_ID__private__page_for_posts())
			return CFGP_Cache::set('current_page_id', get_option( 'page_for_posts' ));

		return false;
	}

	// Get page ID by using get_the_id() function
	protected static function get_page_ID__private__get_the_id(){
		if(function_exists('get_the_id'))
		{
			if($id = get_the_id()) return $id;
		}
		return false;
	}

	// Get page ID by wp_query
	protected static function get_page_ID__private__wp_query(){
		global $wp_query;
		return ((!is_null($wp_query) && isset($wp_query->post) && isset($wp_query->post->ID) && !empty($wp_query->post->ID)) ? $wp_query->post->ID : false);
	}

	// Get page ID by GET[post] in edit mode
	protected static function get_page_ID__private__GET_post(){
		return ((isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'edit') && (isset($_GET['post']) && is_numeric($_GET['post'])) ? absint($_GET['post']) : false);
	}

	// Get page ID by GET[page_id]
	protected static function get_page_ID__private__GET_page_id(){
		return ((isset($_GET['page_id']) && is_numeric($_GET['page_id'])) ? absint($_GET['page_id']) : false);
	}

	// Get page ID by GET[p]
	protected static function get_page_ID__private__GET_p(){
		return ((isset($_GET['p']) && is_numeric($_GET['p'])) ? absint($_GET['p']) : false);
	}

	// Get page ID by OPTION[page_for_posts]
	protected static function get_page_ID__private__page_for_posts(){
		$page_for_posts = get_option( 'page_for_posts' );
		return (!is_admin() && 'page' == get_option( 'show_on_front' ) && $page_for_posts ? absint($page_for_posts) : false);
	}

	// Get page ID by mySQL query
	protected static function get_page_ID__private__query(){
		global $wpdb;
		$actual_link = rtrim($_SERVER['REQUEST_URI'], '/');
		$parts = self::explode('/', $actual_link);
		if(!empty($parts))
		{
			$slug = end($parts);
			if(!empty($slug))
			{
				if($post_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts}
						WHERE
							`post_status` = %s
						AND
							`post_name` = %s
						AND
							TRIM(`post_name`) <> ''
						LIMIT 1",
						'publish',
						sanitize_title($slug)
					)
				))
				{
					return CFGP_Cache::set('current_page_id', absint($post_id));
				}
			}
		}

		return false;
	}
	/**
	* END Get current page ID
	*****************************************************************/
	
	
	
	/**
	* Get current page object
	* @autor    Ivijan-Stefan Stipic
	* @since    8.0.0
	* @version  1.0.0
	******************************************************************/
	public static function get_page() {
		global $wp;
		
		$current_page = CFGP_Cache::get('get_page');
		
		// Get page by path
		if(!$current_page && isset($wp->request)) {
			$current_page =  get_page_by_path($wp->request);
		}
		
		// Get post by ID
		if(!$current_page) {
			$current_page = get_post(isset($wp->query_vars['p']) ? absint($wp->query_vars['p']) : NULL);
		}
		
		// Get page by ID
		if(!$current_page) {
			$current_page = get_post(isset($wp->query_vars['page_id']) ? absint($wp->query_vars['page_id']) : NULL);
		}
		
		// Get post by date/time
		if(
			!$current_page
			&& (
				isset($wp->query_vars['name'])
				|| isset($wp->query_vars['year'])
				|| isset($wp->query_vars['monthnum'])
				|| isset($wp->query_vars['day'])
				|| isset($wp->query_vars['hour'])
				|| isset($wp->query_vars['minute'])
				|| isset($wp->query_vars['second'])
			)
		) {
			
			$attr = array();
			if(isset($wp->query_vars['name'])) {
				$attr['name'] = $wp->query_vars['name'];
			}
			
			if(
				isset($wp->query_vars['year'])
				|| isset($wp->query_vars['monthnum'])
				|| isset($wp->query_vars['day'])
				|| isset($wp->query_vars['hour'])
				|| isset($wp->query_vars['minute'])
				|| isset($wp->query_vars['second'])
			){
				$attr['date_query'] = array();
				
				if(isset($wp->query_vars['year'])){
					$attr['date_query']['year']= $wp->query_vars['year'];
				}
				
				if(isset($wp->query_vars['monthnum'])){
					$attr['date_query']['month']= $wp->query_vars['monthnum'];
				}
				
				if(isset($wp->query_vars['day'])){
					$attr['date_query']['day']= $wp->query_vars['day'];
				}
				
				if(isset($wp->query_vars['hour'])){
					$attr['date_query']['hour']= $wp->query_vars['hour'];
				}
				
				if(isset($wp->query_vars['minute'])){
					$attr['date_query']['minute']= $wp->query_vars['minute'];
				}
				
				if(isset($wp->query_vars['second'])){
					$attr['date_query']['second']= $wp->query_vars['second'];
				}
			}
			
			$page = get_posts($attr);
			if($page) {
				$current_page = $page[0];
			}
		}
		
		// Get page by GET pharam
		if(!$current_page) {
			$current_page = get_post(isset($_GET['page_id']) ? absint($_GET['page_id']) : NULL);
		}
		
		// Get post by GET pharam
		if(!$current_page) {
			$current_page = get_post(isset($_GET['p']) ? absint($_GET['p']) : NULL);
		}

		return CFGP_Cache::set('get_page', $current_page);
	}
	
	/**
	 * Check user's city for defender or seo redirection
	 */
	public static function check_user_by_city( $city )
	{
		if( is_array( $city ) )
		{
			$city = array_map( 'sanitize_title', $city );
			if( isset( $city[0] ) && !empty( $city[0] ) && in_array(sanitize_title(self::api('city')), $city, true ) ) {
				return true;
			}
		}
		elseif( is_string( $city ) )
		{
			if( !empty( $city ) && sanitize_title( $city ) === sanitize_title(self::api('city')) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check user's region for defender or seo redirection
	 */
	public static function check_user_by_region( $region )
	{
		if( is_array( $region ) )
		{
			if( isset( $region[0] ) && !empty( $region[0] ) )
			{
				$region = array_map( 'sanitize_title', $region );
				// Supports region code and region name
				if(in_array( sanitize_title( self::api('region_code') ), $region, true ) ) {
					return true;
				}
				if(in_array( sanitize_title(self::api('region')), $region, true ) ) {
					return true;
				}
			}
		}
		elseif( is_string( $region ) )
		{
			if( !empty( $region ) )
			{
				// Supports region code and region name
				if( sanitize_title( $region ) === sanitize_title(self::api('region_code')) ) {
					return true;
				}
				if( sanitize_title( $region ) === sanitize_title(self::api('region')) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check user's country for defender or seo redirection
	 */
	public static function check_user_by_country( $country )
	{
		if( is_array( $country ) )
		{
			if( isset( $country[0] ) && !empty( $country[0] ) )
			{
				$country = array_map( 'sanitize_title', $country );
				// Supports country code and name
				if( in_array( sanitize_title(self::api('country_code')), $country, true ) ) {
					return true;
				}
				if( in_array( sanitize_title(self::api('country')), $country, true ) ) {
					return true;
				}
			}
		}
		elseif( is_string( $country ) )
		{
			if( !empty( $country ) )
			{
				// Supports country code and name
				if( sanitize_title( $country ) === sanitize_title(self::api('country_code')) ) {
					return true;
				}
				if( sanitize_title( $country ) === sanitize_title(self::api('country')) ) {
					return true;
				}
			}
		}

		return false;
	}
	
	/**
	 * Check user's postcode for defender or seo redirection
	 */
	public static function check_user_by_postcode( $postcode )
	{
		if( is_array( $postcode ) )
		{
			$postcode = array_map( 'sanitize_title', $postcode );
			if( isset( $postcode[0] ) && !empty( $postcode[0] ) && in_array(sanitize_title(self::api('postcode')), $postcode, true) ) {
				return true;
			}
		}
		elseif( is_string( $postcode ) )
		{
			if( !empty( $postcode ) && sanitize_title( $postcode ) === sanitize_title(self::api('postcode')) ) {
				return true;
			}
		}

		return false;
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
	 * Generate convert outoput
	 */
	public static function generate_converter_output( $amount, $symbol, $position = 'L', $separator = '' )
	{
		if( strtoupper( $position ) === 'L' || strtoupper( $position ) == 'LEFT' ) return sprintf( '%s%s%s', $symbol, $separator, $amount );
		else return sprintf( '%s%s%s', $amount, $separator, $symbol );
	}
	
	/*
	 * Check is plugin active
	 */
	public static function is_plugin_active($plugin)
	{
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		return is_plugin_active($plugin);
	}
	
	/*
	 * Hook for the admin URL
	 * @author        Ivijan-Stefan Stipic
	 * @version       2.0.0
	 * @since         7.11.3
	*/
	public static function admin_url( $str = '' )
	{
		if(defined('CFGP_MULTISITE') && CFGP_MULTISITE && self::is_network_admin())
		{
			return self_admin_url($str);
		}

		return admin_url($str);
	}
	
	/*
	 * Hook is network admin
	 * @author        Ivijan-Stefan Stipic
	 * @return        boolean true/false
	*/
	public static function is_network_admin() {
		return function_exists('is_network_admin') && is_network_admin();
	}
	
}
endif;