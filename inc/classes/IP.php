<?php
/**
 * IP control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_IP')) :
class CFGP_IP extends CFGP_Global {
	
	/**
	 * Get client IP address (high level lookup)
	 *
	 * @since	1.3.5
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $string Client IP
	 */
	public static function get()
	{
		if($ip = CFGP_Cache::get('IP')) return $ip;
		
		$findIP=[];
		$blacklistIP = self::blocked();
		
		// Enable cloudflare
		if (
			CFGP_Options::get('enable_cloudflare', false) 
			&& isset($_SERVER['HTTP_CF_CONNECTING_IP']) 
			&& !empty($_SERVER['HTTP_CF_CONNECTING_IP'])
		) {
			$findIP[]='HTTP_CF_CONNECTING_IP';
		}
		
		$findIP=apply_filters( 'cfgp/ip/constants', array_merge($findIP, array(
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_FOR', // X-Forwarded-For: <client>, <proxy1>, <proxy2> client = client ip address; https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP', // Private LAN address
			'REMOTE_ADDR', // Most reliable way, can be tricked by proxy so check it after proxies
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED', // Forwarded: by=<identifier>; for=<identifier>; host=<host>; proto=<http|https>; https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
			'HTTP_CLIENT_IP', // Shared Interner services - Very easy to manipulate and most unreliable way
		)) );
		
		$ip = '';
		// start looping
		
		foreach($findIP as $http)
		{
			if(empty($http)) continue;
			
			// Check in $_SERVER
			if (isset($_SERVER[$http]) && !empty($_SERVER[$http])){
				$ip = wp_unslash( sanitize_text_field( $_SERVER[$http] ) );
			}
			
			// check in getenv() for any case
			if(empty($ip) && function_exists('getenv'))
			{
				$ip = wp_unslash( sanitize_text_field( getenv($http) ) );
			}
			
			// Check if here is multiple IP's
			if(!empty($ip) && preg_match('/([,;]+)/', $ip))
			{
				$ips=str_replace(';',',',$ip);
				$ips=explode(',',$ips);
				$ips=array_map('wp_unslash',$ips);
				$ips=array_map('trim',$ips);
				$ips=array_filter($ips);
				
				$ipf=[];
				foreach($ips as $ipx)
				{
					if(self::filter($ipx, $blacklistIP) !== false)
					{
						$ipf[]=$ipx;
					}
				}
				
				$ipMAX=count($ipf);
				if($ipMAX>0)
				{
					if($ipMAX > 1)
					{
						if('HTTP_X_FORWARDED_FOR' == $http)
						{
							return CFGP_Cache::set('IP', $ipf[0]);
						}
						else
						{
							return CFGP_Cache::set('IP', end($ipf));
						}
					}
					else
						return CFGP_Cache::set('IP', $ipf[0]);
				}
				
				$ips = $ipf = $ipx = $ipMAX = NULL;
			}
			// Check if IP is real and valid
			if(self::filter($ip, $blacklistIP)!==false)
			{
				return CFGP_Cache::set('IP', $ip);
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
				$ips=array_map('wp_unslash',$ips);
				$ips=array_map('sanitize_text_field',$ips);
				
				$ipf=[];
				foreach($ips as $ipx)
				{
					if(self::filter($ipx, $blacklistIP)!==false)
					{
						$ipf[]=$ipx;
					}
				}
				
				$ipMAX=count($ipf);
				if($ipMAX>0)
				{
					/*if($ipMAX > 1)
						return end($ipf);
					else*/
					return CFGP_Cache::set('IP', $ipf[0]);
				}
				
				$ips = $ipf = $ipx = $ipMAX = NULL;
			}
		}
		
		// Let's ask server?
		$external_servers = apply_filters('cfgp/ip/external_servers', array(
			'https://ident.me',
			'https://api.my-ip.io/v1/ip',
			'https://api.ipify.org'
		));
		
		// We have already cached this IP?
		if( $ip = CFGP_DB_Cache::get('localhost-ip') ) {
			return CFGP_Cache::set('IP', $ip);
		}
		
		// let's try the last thing, why not?
		if( CFGP_U::is_connected() )
		{
			$result = NULL;
			foreach($external_servers as $server) {
				$response = wp_remote_get($server);
				if ( is_array( $response ) && !is_wp_error( $response ) ) {
					$ip = $response['body'];
					if(self::filter($ip)!==false) {
						CFGP_DB_Cache::set('localhost-ip', $ip, DAY_IN_SECONDS);
						return CFGP_Cache::set('IP', $ip);
					}
				}
			}
		}
		
		// OK, this is the end :(
		return false;
	}
	
	/**
	 * List of blacklisted IP's
	 *
	 * @since   4.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @pharam  $list - array of bad IP's  IP => RANGE or IP
	 * @return  $array of blacklisted IP's
	 */
	public static function blocked($list=[])
	{
		
		if($ip_blocked = CFGP_Cache::get('IP-blocked')){
			return $ip_blocked;
		}
		
		$blacklist=apply_filters('cfgp/ip/blacklist', array(
			// Localhost IP must be the first one
			'0.0.0.0'		=>	8,
			'127.0.0.0'		=>	8,
			// Server IP address must be blacklisted
			self::server()	=> 	0,
			// Universal IP addresses
			'10.0.0.0'		=>	8,
			'100.64.0.0'	=>	10,
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
			'255.255.255.0'	=>	255
		));
		
		if(!empty($list) && is_array($list)){
			$blacklist = array_merge($blacklist);
		}
		
		$blacklistIP=[];
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
				$breakIP = explode('.', $key);
				$lastNum = ((int)end($breakIP));
				array_pop($breakIP);
				$connectIP=join('.', $breakIP).'.';
				
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
		$blacklistIP=array_map('trim', $blacklistIP);
		$blacklistIP=array_filter($blacklistIP);
		
		return CFGP_Cache::set('IP-blocked', $blacklistIP);
	}
	
	/**
	 * Detect server IP address
	 *
	 * @since    4.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   $string Server IP
	 */
	public static function server(){
		
		if($ip_server = CFGP_Cache::get('IP-server')){
			return $ip_server;
		}
		
		$proxy = CFGP_U::proxy();
		if($proxy){
			$_SERVER['SERVER_ADDR'] = CFGP_Options::get('proxy_ip');
		}
	
		$findIP=apply_filters( 'cfgp/ip/server_constants', array(
			'SERVER_ADDR',
			'SERVER_NAME',
			'LOCAL_ADDR'
		));
		
		$ip = '';
		// start looping
		foreach($findIP as $http)
		{
			// Check in $_SERVER
			if (isset($_SERVER[$http]) && !empty($_SERVER[$http])){
				$ip=wp_unslash( sanitize_text_field( $_SERVER[$http] ) );
			}
			
			if(empty($ip) && function_exists("getenv"))
			{
				$ip = wp_unslash( sanitize_text_field( getenv($http) ) );
			}
			// Check if here is multiple IP's
			if($http == 'SERVER_NAME')
			{
				$ip = sanitize_text_field( isset($_SERVER['SERVER_NAME']) && function_exists('gethostbyname') ? gethostbyname($_SERVER['SERVER_NAME']) : '' );
			}
			// Check if IP is real and valid
			if(self::validate_any($ip))
			{
				return CFGP_Cache::set('IP-server', $ip);
			}
		}
		
		if (version_compare(PHP_VERSION, '5.3.0', '>=') && function_exists('gethostname')) {
			$gethostname = preg_replace(array('~https?:\/\/~','~^w{3}\.~'),'',gethostbyname(gethostname()));
			return CFGP_Cache::set('IP-server', $gethostname);
		} else if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('php_uname')) {
			$gethostbyname = preg_replace(array('~https?:\/\/~','~^w{3}\.~'),'',gethostbyname(php_uname("n")));
			return CFGP_Cache::set('IP-server', $gethostbyname);
		} else {
			$hostname = preg_replace(array('~https?:\/\/~','~^w{3}\.~'),'',gethostbyname(trim(`hostname`)));
			return CFGP_Cache::set('IP-server', $hostname);
		}
		
		return false;
	}
	
	/*
	 * Validate any IP address
	 */
	public static function validate_any( $ip ){
		
		$ip = str_replace(array("\r", "\n", "\r\n", "\s", PHP_EOL), '', $ip);
		
		do_action('cfgp/ip/validate_any', $ip);
		
		if(function_exists("filter_var") && !empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
		{
			return $ip;
		}
		else if(!empty($ip) && preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip))
		{
			return $ip;
		}
		
		return false;
	}
	
	/**
	 * Detect is client using proxy
	 *
	 * @since    8.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   (bool)   true/false
	 */
	public static function is_proxy(){
		static $proxy = NULL;
		
		// Return cached proxy
		if(NULL !== $proxy) {
			return $proxy;
		}
		
		$proxy = false;
		
		// Check is proxy using HTTP headers
		$proxy_headers = apply_filters('cfgp/ip/proxy_headers', array('HTTP_X_REAL_IP','HTTP_X_PROXY_ID','CLIENT_IP','FORWARDED','FORWARDED_FOR','FORWARDED_FOR_IP','VIA','X_FORWARDED','X_FORWARDED_FOR','HTTP_CLIENT_IP','HTTP_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED_FOR_IP','HTTP_PROXY_CONNECTION','HTTP_VIA','HTTP_X_FORWARDED','Proxy-Connection','X-PROXY-ID','MT-PROXY-ID','X-TINYPROXY','PROXY-AGENT','CLIENT-IP','HTTP_X_CLUSTER_CLIENT_IP','HTTP_X_FORWARDED_FOR'));
		
		foreach($proxy_headers as $header){
			if (isset($_SERVER[$header])) {
				$proxy = true;
				break;
			}
		}
		
		do_action('cfgp/ip/is_proxy', $proxy);
		
		return $proxy;
	}
	
	/**
	 * Detect is running on the local machine
	 *
	 * @since    8.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   (bool)   true/false
	 */
	public static function is_localhost() {
		// Cloudflare
		if( CFGP_Options::get('enable_cloudflare', false)
			&& isset($_SERVER['HTTP_CF_CONNECTING_IP'])
			&& !empty($_SERVER['HTTP_CF_CONNECTING_IP'])
		) {
			return false;
		}

		// Set cache name
		$cache_name = '__is_localhost';
		
		// Return cached result
		if (NULL !== ($is_localhost = CFGP_Cache::get($cache_name, NULL))) {
			return $is_localhost;
		}

		// Get Remote address properly
		if (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
			$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		} else if (filter_has_var(INPUT_ENV, 'REMOTE_ADDR')) {
			$ip = filter_input(INPUT_ENV, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		} else {
			$ip = null;
		}

		$localhost = false;

		if(!empty($ip)) {
			// Check for localhost IP addresses
			if (in_array($ip, array('127.0.0.1', '::1'))) {
				$localhost = true;
			}

			// Check for private network ranges
			$private_ip_patterns = array(
				'~^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$~',
				'~^172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3}$~',
				'~^192\.168\.\d{1,3}\.\d{1,3}$~',
				'~^fc00:~',
				'~^fd00:~'
			);

			foreach ($private_ip_patterns as $pattern) {
				if (preg_match($pattern, $ip)) {
					$localhost = true;
					break;
				}
			}
		}

		// Cache the result
		CFGP_Cache::set($cache_name, $localhost);

		return $localhost;
	}
	
	/**
	 * Check is IP valid or not
	 *
	 * @since	1.3.5
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  (string) IP address or (bool) false
	 */
	public static function filter($ip, $blacklistIP=[])
	{
		do_action('cfgp/ip/filter', $ip, $blacklistIP);
		$ip = rest_is_ip_address($ip);
		return (!empty($ip) && in_array($ip, $blacklistIP, true)===false) ? $ip : false;
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