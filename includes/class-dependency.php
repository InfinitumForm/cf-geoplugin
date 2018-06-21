<?php
class CF_GEO_D
{
	public $IP = '0.0.0.0';
	public $SERVER_IP = '0.0.0.0';
	public $BLACKLIST_IP = array();
	public $PROXY = false;
	public $ACTIVATED = false;
	public $DEFENDER_ACTIVATED = false;
	private static $is_proxy = false;
	
	function __construct(){
		$this->IP = $this->ip();
		$this->SERVER_IP = $this->ip_server();
		$this->BLACKLIST_IP = $this->ip_blocked();
		$this->PROXY = $this->proxy();
		$this->ACTIVATED = $this->check_activation();
		$this->DEFENDER_ACTIVATED = $this->check_defender_activation();	
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
			"hostname"	=>	trim(preg_replace("/(https?)(:\/\/)?/i","",$domain),'/'),
		);
	}
	
	/**
	 * Get content via cURL
	 *
	 * @since    4.0.4
	 */
	public static function curl_get($url){
		$t = new CF_GEO_D;
		// Call cURL
		$output=false;
		if(function_exists('curl_version')!==false)
		{
			$cURL = curl_init();
				curl_setopt($cURL,CURLOPT_URL, $url);
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, ((bool) get_option("cf_geo_enable_ssl")));
				curl_setopt($cURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, (int)get_option("cf_geo_connection_timeout"));
				if($t->PROXY){
					curl_setopt($cURL, CURLOPT_PROXY, get_option("cf_geo_enable_proxy_ip"));
					curl_setopt($cURL, CURLOPT_PROXYPORT, get_option("cf_geo_enable_proxy_port"));
					$username=get_option("cf_geo_enable_proxy_username");
					$password=get_option("cf_geo_enable_proxy_password");
					if(!empty($username)){
						curl_setopt($cURL, CURLOPT_PROXYUSERPWD, $username.":".$password);
					}
				}
				curl_setopt($cURL, CURLOPT_TIMEOUT, (int)get_option("cf_geo_timeout"));
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
	private function proxy(){
		$proxy = get_option("cf_geo_enable_proxy");
		return ((!empty($proxy) && $proxy=='true') ? true : false);
	}
	
	/**
	 * Detect server IP address
	 *
	 * @since    4.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   $string Server IP
	 */
	private function ip_server(){
		$proxy = $this->proxy();
		if($proxy) $_SERVER['SERVER_ADDR'] = get_option("cf_geo_enable_proxy_ip");
	
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
				return gethostbyname(php_uname("n"));
			else 
			{
				$ifconfig = shell_exec('/sbin/ifconfig eth0');
				preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
				if(isset($match[1]) && !empty($match[1]))
				return $match[1];
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
	private function ip_blocked($list=array()){
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
	private function ip()
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
		// OK, this is the end :(
		return '0.0.0.0';
	}
	
	/**
	 * Check is activated
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $bool
	 */
	private function check_defender_activation()
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
	private function check_activation()
	{
		if(get_option('cf_geo_license') == 1 && get_option('cf_geo_license_key') && get_option('cf_geo_license_id') || $this->check_defender_activation()) 
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
		// Validate
		if(get_option('cf_geo_license') == 1 && get_option('cf_geo_license_key') && get_option('cf_geo_license_id')) :
			$D = new CF_GEO_D;
			$url = $D->URL();
			$ch = curl_init(get_option('cf_geo_store') . '/wp-admin/admin-ajax.php');
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'action' 		=> 'license_key_validate',
					'license_key' 	=> get_option('cf_geo_license_key'),
					'sku' 			=> get_option('cf_geo_license_sku'),
					'store_code' 	=> get_option('cf_geo_store_code'),
					'domain' 		=> $url->hostname,
					'activation_id'	=> get_option('cf_geo_license_id')
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
				if($license->error)
				{
					update_option('cf_geo_license', 0, true);
					return false;
				}
			}
			return true;
		endif;
		return false;
	}
}