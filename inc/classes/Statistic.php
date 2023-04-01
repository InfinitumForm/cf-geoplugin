<?php
/*
 * Plugin Statistic
 *
 * This sends only basic plugin statistic information for the developers.
 * When you uninstall the plugin, this data is deleted from our database.
 * We only collect plugin ID, URL, version and plugin settings.
 * This helps us compare the most commonly used options and improve our services.
 * That's all we need.
 *
 * If you have any concerns, please contact us:
 *
 * Email: infinitumform@gmail.com
 * Subject: PRIVACY CONCERN
 *
 * @since     8.0.0
 * @verson    1.0.0
 */
 
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Anonymous_Statistic', false)) : class CFGP_Anonymous_Statistic {
	
	// API call
	protected static $url = 'http://159.203.47.151/plugin_stat/index.php';
	
	// Send data on activation
	public static function activation ($data = '') {
		return self::remote_request(self::$url, array(
			'plugin_name' => CFGP_NAME,
			'domain' => home_url('/'),
			'plugin_id' => get_option(CFGP_NAME . '-ID'),
			'plugin_version' => CFGP_VERSION,
			'data' => (!empty($data) ? json_encode($data) : ''),
			'action' => 'activation'
		));
	}
	
	// Send data on deactivation
	public static function deactivation () {
		return self::remote_request(self::$url, array(
			'plugin_name' => CFGP_NAME,
			'domain' => home_url('/'),
			'plugin_id' => get_option(CFGP_NAME . '-ID'),
			'plugin_version' => CFGP_VERSION,
			'action' => 'deactivation'
		));
	}
	
	// Send data on uninstall
	public static function uninstall () {
		return self::remote_request(self::$url, array(
			'plugin_name' => CFGP_NAME,
			'domain' => home_url('/'),
			'plugin_id' => get_option(CFGP_NAME . '-ID'),
			'action' => 'uninstall'
		));
	}
	
	// PRIVATE: Request
	protected static function remote_request ($url, $data=NULL, $method = 'GET') {
		// cURL method
		if(function_exists('curl_init'))
		{
			// Get method
			if($method == 'GET') {
				if(!empty($data) && is_array($data)) {
					$data = http_build_query($data);
					$url = $url . ( (strpos($url, '?') !== false) ? '&' : '?' ) . $data;
				}
			}
			// Parse URL
		//	$url_parts=parse_url($url);
			// Initialize cURL
			$curl = curl_init();
				// Set URL
				curl_setopt($curl, CURLOPT_URL, $url);
				// Send POST data
				if($method == 'POST') {
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
				}
				// Setup
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
				curl_setopt($curl, CURLOPT_TIMEOUT, 3);
				// Accept
				curl_setopt($curl, CURLOPT_HTTPHEADER, array(
					'Accept: application/json',
		//			"Host:{$url_parts['host']}"
				));
				
				// Return data
				$output=curl_exec($curl);
			curl_close($curl);
		} else {
			// WP remote
			if($method == 'POST') {
				$output = wp_remote_post(
					$url,
					array(
						'body' => $data,
						'headers' => array(
							'Accept' => 'application/json'
						)
					)
				);
			} else {
				
				if(!empty($data) && is_array($data)) {
					$data = http_build_query($data);
					$url = $url . ( (strpos($url, '?') !== false) ? '&' : '?' ) . $data;
				}
				
				$output = wp_remote_get(
					$url,
					array(
						'headers' => array(
							'Accept' => 'application/json'
						)
					)
				);
			}
		}

		// Output
		if($output !== false) {
			return json_decode($output);
		}
		
		// Fail
		return false;
	}
} endif;