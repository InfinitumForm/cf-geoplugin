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
		
		$findIP=array();
		$blacklistIP = self::blocked( array( self::server() ) );
		
		// Enable cloudflare
		if (CFGP_Options::get('enable_cloudflare', false) && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			$findIP[]='HTTP_CF_CONNECTING_IP';
		}
		
		$findIP=apply_filters( 'cfgp/ip/constants', array_merge($findIP, array(
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
				$ip=$_SERVER[$http];
			}
			
			// check in getenv() for any case
			if(empty($ip) && function_exists('getenv'))
			{
				$ip = getenv($http);
			}
			
			// Check if here is multiple IP's
			if(!empty($ip) && preg_match('/([,;]+)/', $ip))
			{
				$ips=str_replace(';',',',$ip);
				$ips=explode(',',$ips);
				$ips=array_map('trim',$ips);
				
				$ipf=array();
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
				$ips=array_map('trim',$ips);
				
				$ipf=array();
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
			'https://api.ipify.org',
			'https://smart-ip.net/myip',
			'https://ident.me'
		));
		if(stristr(PHP_OS, 'WIN') !== false)
		{
			if(function_exists('shell_exec'))
			{
				foreach($external_servers as $server) {
					$ip = shell_exec('powershell.exe -InputFormat none -ExecutionPolicy Unrestricted -NoProfile -Command "(Invoke-WebRequest '.$server.').Content.Trim()"');
					if(self::filter($ip)!==false)
					{
						return CFGP_Cache::set('IP', $ip);
					}
				}
			}
		}
		else
		{
			if(function_exists('shell_exec'))
			{
				foreach($external_servers as $server) {
					$ip = shell_exec('curl '.$server.'##*( )');
					if(self::filter($ip)!==false)
					{
						return CFGP_Cache::set('IP', $ip);
					}
				}
			}
		}
		
		// let's try the last thing, why not?
		if( CFGP_U::is_connected() )
		{
			$result = NULL;
			
			if(function_exists('file_get_contents'))
			{
				$context = CFGP_U::set_stream_context( array( 'Accept: application/json' ), 'GET' );
				$result = @file_get_contents( 'https://api.ipify.org/?format=json', false, $context );
			}
			if($result)
			{
				$result = json_decode($result);
				if(isset($result->ip))
				{
					$ip = $result->ip;
					if(self::filter($ip)!==false)
					{
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
	public static function blocked($list=array())
	{
		
		if($ip_blocked = CFGP_Cache::get('IP-blocked')){
			return $ip_blocked;
		}
		
		$blacklist=apply_filters('cfgp/ip/blacklist', array(
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
		));
		
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
		if(!empty($blacklistIP)) $blacklistIP=array_map('trim', $blacklistIP);
		
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
		if($proxy) $_SERVER['SERVER_ADDR'] = CFGP_Options::get('proxy_ip');
	
		$findIP=apply_filters( 'cfgp/ip/server_constants', array(
			'SERVER_ADDR',
			'LOCAL_ADDR',
			'SERVER_NAME',
		));
		
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
				$ip = (isset($_SERVER['SERVER_NAME']) && function_exists('gethostbyname') ? gethostbyname($_SERVER['SERVER_NAME']) : '');
			}
			// Check if IP is real and valid
			if(self::validate_any($ip))
			{
				return CFGP_Cache::set('IP-server', $ip);
			}
		}
		// Running CLI
		if(stristr(PHP_OS, 'WIN'))
		{
			if(function_exists('shell_exec'))
			{
				if(file_exists(CFGP_SHELL . '/win_find_server_ip.cmd') && is_executable(CFGP_SHELL . '/win_find_server_ip.cmd'))
				{
					if($ips = shell_exec(CFGP_SHELL . '/win_find_server_ip.cmd'))
					{
						$ips = preg_split('/[\s\n\r]+/', $ips);
						$ips = array_filter($ips);
						
						if(!empty($ips))
						{
							$ip = end($ips);
							if(self::validate_any($ip) !== false) {
								return CFGP_Cache::set('IP-server', $ip);
							}
						}
					}
				}
			}
		}
		else 
		{
			if(function_exists('shell_exec'))
			{
				if(file_exists(CFGP_SHELL . '/unix_find_server_ip.sh') && is_executable(CFGP_SHELL . '/unix_find_server_ip.sh'))
				{
					if($ips = shell_exec(CFGP_SHELL . '/unix_find_server_ip.sh'))
					{
						$ips = preg_split('/[\s\n\r]+/', $ips);
						$ips = array_filter($ips);
						
						if(!empty($ips))
						{
							$ip = end($ips);
							if(self::validate_any($ip) !== false)
								return CFGP_Cache::set('IP-server', $ip);
						}
					}
				}
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
		if(NULL !== $proxy) return $proxy;
		
		$proxy = false;
		
		// Check is proxy using HTTP headers
		$proxy_headers = apply_filters('cfgp/ip/proxy_headers', array(
			'HTTP_VIA', 'VIA', 'Proxy-Connection', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED_FOR_IP', 'X-PROXY-ID', 'MT-PROXY-ID', 'X-TINYPROXY', 'X_FORWARDED_FOR', 'FORWARDED_FOR', 'X_FORWARDED', 'FORWARDED', 'CLIENT-IP', 'CLIENT_IP', 'PROXY-AGENT', 'HTTP_X_CLUSTER_CLIENT_IP', 'FORWARDED_FOR_IP', 'HTTP_PROXY_CONNECTION'
		));
		foreach($proxy_headers as $header){
			if (isset($_SERVER[$header])) {
				$proxy = true;
				break;
			}
		}
		
		// Use Internet or Unix domain socket connection to check proxy
		if($proxy === false && function_exists('fsockopen')) {
			$proxy_ports = apply_filters('cfgp/ip/proxy_ports', array(80,81,8080,443,1080,6588,3128));
			foreach($proxy_ports as $test_port) {
				if(@fsockopen(self::get(), $test_port, $errno, $errstr, 1)) {
					$proxy = true;
					break;
				}
			}
		}
		
		return $proxy;
	}
	
	/**
	 * Check is IP valid or not
	 *
	 * @since	1.3.5
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  (string) IP address or (bool) false
	 */
	public static function filter($ip, $blacklistIP=array())
	{
		do_action('cfgp/ip/filter/before', $ip, $blacklistIP);
		
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
		
		do_action('cfgp/ip/filter/after', $ip, $blacklistIP);
		
		return false;
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