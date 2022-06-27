<?php
/**
 * Main API class
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

if(!class_exists('CFGP_API')) :
class CFGP_API extends CFGP_Global {
	private $host;
	public function __construct( $dry_run = false ) {
		try {
			// Set host
			$this->host = CFGP_U::get_host(true);
			
			// Collect geo data
			$return = $this->get();
			
			// Fix response
			if( !is_array($return) ) {
				$return = [];
			}
			
			// Add filter
			$return = array_merge(
				apply_filters( 'cfgp/api/default/fields', CFGP_Defaults::API_RETURN),
				$return
			);
			
			// Merge all
			$return = apply_filters( 'cfgp/api/results', $return, CFGP_Defaults::API_RETURN);

			// Save API data to array
			CFGP_Cache::set('API', $return);
		
		} catch (Exception $e) {
			throw new ErrorException('CFGP ERROR: ' . $e->getMessage());
		}
	}
	
	
	/**
	 * Get geo informations
	 *
	 * @since    8.0.0
	 */
	public function get($ip = NULL, $property = array()) {
		// Default fields
		$default_fields = apply_filters( 'cfgp/api/default/fields', CFGP_Defaults::API_RETURN);
		
		// Get IP
		if(!empty($ip)) {
			if(CFGP_IP::filter($ip) === false){
				return $default_fields;
			}
		} else {
			$ip = CFGP_IP::get();
		}
		
		// If there is no IP return defaults
		if( empty($ip) ) {
			return $default_fields;
		}

		// generate IP like a slug
		$ip_slug = str_replace( array('.', ':'), '_', $ip );
		
		// DNS control
		$check_dns = false;
		if(isset($property['dns']) && $property['dns']) {
			$ip_slug = $ip_slug . '_dns';
			$check_dns = true;
		}
		
		// Get base currency
		if(isset($property['base_currency']) && $property['base_currency']) {
			$base_currency = $property['base_currency'];
		} else if( CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0) ) {
			$base_currency = (get_option('woocommerce_currency') ?? CFGP_Options::get('base_currency', 'USD'));
		} else {
			$base_currency = CFGP_Options::get('base_currency', 'USD');
		}
		
		// Default returns
		$return = array();
		
		if($transient = CFGP_DB_Cache::get("cfgp-api-{$ip_slug}"))
		{					
			$return = $transient;
			
			$return['current_time']= date(CFGP_TIME_FORMAT, CFGP_TIME);
			$return['current_date']= date(CFGP_DATE_FORMAT, CFGP_TIME);
			$return['browser']= CFGP_Browser::instance()->getBrowser();
			$return['browser_version']= CFGP_Browser::instance()->getVersion();
			$return['platform']= CFGP_Browser::instance()->getPlatform();
			$return['is_mobile']= (CFGP_Browser::instance()->isMobile() ? 1 : 0);
			
			if( $lookup = CFGP_DB_Cache::get('cfgp-api-available-lookup-' . $this->host) ) {
				$return['lookup']=$lookup;
			}
		}
		
		// Get new data
		if(empty($return))
		{
			// Build query
			$pharams = apply_filters('cfgp/api/get/curl/pharams', array(
				'ip' => $ip,
				'server_ip' => CFGP_IP::server(),
				'timestamp' => CFGP_TIME,
				'referer' => $this->host,
				'email' => get_bloginfo('admin_email'),
				'license' => get_option('cf_geo_defender_api_key'), // we need to keep in track some old activation keys
				'base_convert' => $base_currency,
				'dns' => (CFGP_Options::get('enable_dns_lookup') && CFGP_License::level() >= 1 ? 'true' : 'false'),
				'version' => CFGP_VERSION,
				'wp_version' => get_bloginfo( 'version' )
			));
			// Build URL
			$url = CFGP_Defaults::API['main'] . '?' . http_build_query($pharams, '', (ini_get('arg_separator.output') ?? '&amp;'), PHP_QUERY_RFC3986);
			// Fetch new informations
			$response = CFGP_U::curl_get($url);
			// Fix data and save to cache
			if (!empty($response))
			{
				// Convert and merge
				$response = apply_filters(
					'cfgp/api/get/geodata',
					array_merge($default_fields, $response),
					$response,
					$default_fields
				);

				// If there is a error, display it
				if(($response['error'] ?? NULL)===true) {
					return $response;
				}
				
				// Add browser data
				$response = array_merge($response, array(
					'browser' => CFGP_Browser::instance()->getBrowser(),
					'browser_version' => CFGP_Browser::instance()->getVersion(),
					'platform' => CFGP_Browser::instance()->getPlatform(),
					'is_mobile' => (CFGP_Browser::instance()->isMobile() ? 1 : 0)
				));
				
				// Fix proxy
				if ( empty( $response['proxy'] ) ) {
					$response['is_proxy'] = (CFGP_IP::is_proxy() ? 1 : 0);
				} else {
					$response['is_proxy'] = $response['proxy'];
				}
				
				
				$return = $response;
			}
		}
		
		// Return
		return $return;
	}
	

	
	/*
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance( $dry_run = false ) {
		$instance = CFGP_Cache::get(self::class . ($dry_run ? '_Dry' : NULL));
		if ( !$instance ) {
			$instance = CFGP_Cache::set(
				self::class . ($dry_run ? '_Dry' : NULL),
				new self( $dry_run )
			);
		}
		return $instance;
	}
}
endif;