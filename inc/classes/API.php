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
	
	public function __construct( $dry_run = false ){
		try {
			$this->host = CFGP_U::get_host(true);
			
			if($dry_run !== true)
			{
				// Collect geo data & DNS
				$return = $this->get('geo');
				if (CFGP_Options::get('enable_dns_lookup', 0)) {
					$return = array_merge($return, $this->get('dns'));
				}
				$return = array_merge(
					apply_filters( 'cfgp/api/default/fields', CFGP_Defaults::API_RETURN),
					$return
				);

				// Clear cache
				CFGP_Cache::delete('transfer_dns_records');

				$return = apply_filters( 'cfgp/api/results', $return, CFGP_Defaults::API_RETURN);

				// Save API data to array
				CFGP_Cache::set('API', $return);
			}
		} catch (Exception $e) {
			throw new ErrorException('CFGP ERROR: ' . $e->getMessage());
		}
	}
	
	
	/**
	 * Fetch new geo informations
	 *
	 * @since    8.0.0
	 */
	public static function lookup($ip, $property = array()){
		if($ip = CFGP_IP::filter($ip)) {
			$return = self::instance(true)->get('geo', $ip, $property);
			if (CFGP_Options::get('enable_dns_lookup', 0)) {
				$return = array_merge($return, self::instance(true)->get('dns', $ip));
				CFGP_Cache::delete('transfer_dns_records');
			}
			
			return $return;
		}
		
		return false;
	}
	
	/**
	 * Get geo informations
	 *
	 * @since    8.0.0
	 */
	public function get($name, $ip = NULL, $property = array()){
		
		$default_fields = apply_filters( 'cfgp/api/default/fields', CFGP_Defaults::API_RETURN);
		
		if(!empty($ip)) {
			if(CFGP_IP::filter($ip) === false){
				return $default_fields;
			}
		} else {
			$ip = CFGP_IP::get();
		}
				
		if(empty($ip)) {
			return $default_fields;
		}

		$ip_slug = str_replace('.', '_', $ip );
		
		
		if(isset($property['base_currency'])) {
			$base_currency = $property['base_currency'];
		} else {
			$base_currency = CFGP_Options::get('base_currency', 'USD');
		}
		
	//	delete_transient("cfgp-api-{$ip_slug}"); //-DEBUG
	//	delete_transient("cfgp-api-{$ip_slug}-dns"); //-DEBUG
		
		$return = array();
		
		switch($name)
		{
			case 'geo':				
				// Get cached data
				if($transient = get_transient("cfgp-api-{$ip_slug}"))
				{					
					$return = $transient['geo'];
					
					$return['current_time']= date(get_option('time_format'), CFGP_TIME);
					$return['current_date']= date(get_option('date_format'), CFGP_TIME);
					
					if( $lookup = get_transient('cfgp-api-available-lookup-' . $this->host) ) {
						$return['lookup']=$lookup;
					}
					
					// Save in the session DNS host
					CFGP_Cache::set('transfer_dns_records', self::fix_dns_host($transient['dns']));
				}
				
				// Get new data
				if(empty($return))
				{
					$pharams = array(
						'{IP}' => $ip,
						'{SIP}' => CFGP_IP::server(),
						'{TIME}' => CFGP_TIME,
						'{HOST}' => $this->host,
						'{VERSION}' => CFGP_VERSION,
						'{M}' => get_bloginfo("admin_email"),
						'{P}' => get_option('cf_geo_defender_api_key'), // we need to keep in track some old activation keys
						'{CURRENCY}' => $base_currency,
						'{REVERSE}' => (CFGP_Options::get('enable_dns_lookup') && CFGP_License::level() >= 1 ? 1 : 0)
					);
				
					$url = str_replace(
						array_keys($pharams),
						array_map('urlencode', array_values($pharams)),
						CFGP_Defaults::API['main'].'?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}&base_convert={CURRENCY}&reverse={REVERSE}'
					);
					
					$return = CFGP_U::curl_get($url);
				
					// Fix data and save to cache
					if (!empty($return))
					{						
						// Convert and merge
						$return = json_decode($return, true);
						$return = apply_filters('cfgp/api/get/geodata', $return);

						$return = array_merge($default_fields, $return);
	
						if($return['error']===true) {
							return $return;
						}
	
						// Cloud Flare country code
						if (CFGP_Options::get('enable_cloudflare') && isset($_SERVER["HTTP_CF_IPCOUNTRY"]) && !empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
							$return['countryCode'] = $_SERVER["HTTP_CF_IPCOUNTRY"];
						}
						
						// Fix currency
						$currency_name = CFGP_Defaults::CURRENCY_NAME;
						$currency_symbol = CFGP_Defaults::CURRENCY_SYMBOL;
						
						if(empty($return['currency'])) {
							$return['currency'] = $this->get_currency($return['countryCode']);
						}
						
						// Fix currency symbol
						if(empty($return['currencySymbol'])) {
							if(isset($currency_symbol[$return['currency']])){
								$return['currencySymbol'] = $currency_symbol[$return['currency']];
							}
						}
						if(empty($return['currency_symbol'])) {
							$return['currency_symbol'] = $return['currencySymbol'];
						}
						
						// Fix currency name
						if(empty($return['currency_name'])) {
							if(isset($currency_name[$return['currency']])){
								$return['currency_name'] = $currency_name[$return['currency']];
							}
						}
						
						// Fix base currency
						if(empty($return['base_convert'])) {
							$return['base_convert'] = $base_currency;
						}
						if(empty($return['base_currency'])) {
							$return['base_currency'] = $return['base_convert'];
						}
						
						// Fix base currency symbol
						if(empty($return['base_currency_symbol'])) {
							if(isset($currency_symbol[$return['base_convert']])){
								$return['base_currency_symbol'] = $currency_symbol[$return['base_convert']];
							}
						}
						
						// Fix base currency name
						if(empty($return['base_currency_name'])) {
							if(isset($currency_name[$return['base_convert']])){
								$return['base_currency_name'] = $currency_name[$return['base_convert']];
							}
						}
						
						// Fix Currency converter
						if(empty($return['currency_converter'])) {
							$return['currency_converter'] = $return['currencyConverter'];
						}
						
						// Fix Locale
						if(empty($return['locale'])) {
							$return['locale'] = self::country2locale($return['countryCode']);
						}
						
						// Fix contitnet
						if(empty($return['continent'])) {
							$return['continent'] = CFGP_U::array_find_parent(CFGP_Defaults::COUNTRY_REGION_LIST, $return['countryCode']);
						}
						
						// Fix continent code
						if(empty($return['continentCode'])) {
							$continent_code = array_flip(CFGP_Defaults::CONTINENT_LIST);
							if(isset($continent_code[$return['continent']])) {
								$return['continentCode'] = $continent_code[$return['continent']];
							}
						}
						if(empty($return['continent_code'])) {
							$return['continent_code'] = $return['continentCode'];
						}
						
						// Add distance
						$m_accuracy = (!empty($return['accuracy']) ? floatval($return['accuracy']) : 1);
						switch(CFGP_Options::get('measurement_unit', 'km'))
						{
							case 'km':
								$m_unit = __('km',CFGP_NAME);
								break;
							case 'mile':
								$m_unit = __('mile',CFGP_NAME);
								$m_accuracy = number_format(abs($m_accuracy * 0.621371), 2);
								break;
						}
						$return['accuracy_radius'] = $m_accuracy.$m_unit;
						
						// Save in the session DNS host
						CFGP_Cache::set('transfer_dns_records', self::fix_dns_host($return));
						$DNS = CFGP_Cache::get('transfer_dns_records');
						
						// Render response
						$return = apply_filters( 'cfgp/api/render/response', array(
							'ip' => ($return['ipAddress'] ?? NULL),
							'ip_version' => ($return['ipVersion'] ?? NULL),
							'ip_number' => ($return['ipNumber'] ?? NULL),
							'ip_dns' => NULL,
							'ip_dns_host' => NULL,
							'ip_dns_provider' => NULL,
							'isp' => NULL,
							'isp_organization' => NULL,
							'isp_as' => NULL,
							'isp_asname' => NULL,
							'country_code_numeric' => ($return['countryNumericCode'] ?? NULL),
							'country_code' => ($return['countryCode'] ?? NULL),
							'country' => ($return['countryName'] ?? NULL),
							'region' => ($return['regionName'] ?? NULL), //regionCode
							'region_code' => ($return['regionCode'] ?? NULL),
							'state' => ($return['regionName'] ?? NULL), // deprecated
							'city' => ($return['cityName'] ?? NULL),
							'continent' => ($return['continent'] ?? NULL),
							'continent_code' => ($return['continentCode'] ?? NULL),
							'zip' => ($return['zip'] ?? NULL),
							'postcode' => ($return['zip'] ?? NULL),
							'address' => ($return['address'] ?? NULL),
							'latitude' => ($return['latitude'] ?? NULL),
							'longitude' => ($return['longitude'] ?? NULL),
							'timezone' => ($return['timezone'] ?? NULL),
							'locale' => ($return['countryCode'] ?? NULL),
							'timezoneName' => ($return['timezone'] ?? NULL), // deprecated
							'currency' => ($return['currency'] ?? NULL),
							'currency_symbol' => ($return['currency_symbol'] ?? NULL),
							'currency_name' => ($return['currency_name'] ?? NULL),
							'base_currency' => ($return['base_currency'] ?? NULL),
							'base_currency_symbol' => ($return['base_currency_symbol'] ?? NULL),
							'base_currency_name' => ($return['base_currency_name'] ?? NULL),
							'currency_converter' => ($return['currency_converter'] ?? NULL),
							'host' => ($return['referer'] ?? NULL),
							'ip_host' => ($return['refererIP'] ?? NULL),
							'timestamp' => ($return['timestamp'] ?? NULL),
							'timestamp_readable' => ($return['timestampReadable'] ?? NULL),
							'current_time' => date(get_option('time_format'), CFGP_TIME),
							'current_date' => date(get_option('date_format'), CFGP_TIME),
							'version' => CFGP_VERSION,
							'is_proxy' => ( ($return['proxy'] ?? NULL) ? 1 : (CFGP_IP::is_proxy() ? 1 : 0) ),
							'is_vat' => ($return['isVAT'] ?? NULL) ? 1 : 0,
							'vat_rate'	=> ($return['VATrate'] ?? NULL),
							'in_eu'	=> ($return['inEU'] ?? NULL) ? 1 : 0,
							'gps'	=> ($return['gps'] ?? NULL) ? 1 : 0,
							'accuracy_radius' => ($return['accuracy_radius'] ?? NULL),
							'runtime' => abs($return['runtime'] ?? 0),
							'status' => ($return['status'] ?? NULL),
							'lookup' => ($return['available_lookup'] ?? CFGP_LIMIT),
							'error' => ($return['error'] ?? NULL),
							'error_message' => ($return['message'] ?? NULL),
							'credit' => ($return['credit'] ?? NULL),
						), $return);

						// Save lookup to session
						if(is_numeric($return['lookup']) && $return['lookup'] <= CFGP_LIMIT) {
							set_transient('cfgp-api-available-lookup-' . $this->host, $return['lookup'], (DAY_IN_SECONDS*2));
						} else if(
							($return['lookup'] == 'unlimited' || $return['lookup'] == 'lifetime') 
							&& get_transient('cfgp-api-available-lookup-' . $this->host)
						) {
							delete_transient('cfgp-api-available-lookup-' . $this->host);
						}
						
						// Save to session
						set_transient("cfgp-api-{$ip_slug}", array(
							'geo' => (array)$return,
							'dns' => (array)$DNS
						), (MINUTE_IN_SECONDS * CFGP_SESSION));
					}
				}
				break;
				
			case 'dns':			
				$pharams = array(
					'{IP}' => $ip,
					'{SIP}' => CFGP_IP::server(),
					'{HOST}' => $this->host,
					'{VERSION}' => CFGP_VERSION,
					'{P}' => get_option('cf_geo_defender_api_key')
				);
			
				$url = str_replace(
					array_keys($pharams),
					array_map('urlencode', array_values($pharams)),
					CFGP_Defaults::API['dns'].'?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}'
				);
				
				if($transient = get_transient("cfgp-api-{$ip_slug}-dns"))
				{					
					$return = apply_filters( 'cfgp/api/dns/render/response', $transient);
					CFGP_Cache::delete('transfer_dns_records');
				}
				
				if(empty($return))
				{
					$return = CFGP_U::curl_get($url);
					
					// Fix data and save to cache
					if (!empty($return))
					{						
						// Convert and merge
						$return = json_decode($return, true);
						$return = apply_filters('cfgp/api/dns/get', $return);
						
						$return = array_merge(array(
							'ip_dns' => NULL,
							'ip_dns_host' => NULL,
							'ip_dns_provider' => NULL,
							'isp' => NULL,
							'isp_organization' => NULL,
							'isp_as' => NULL,
							'isp_asname' => NULL
						), $return);
						
						if($return['error']===true) return $return;
						
						$get = CFGP_Cache::get('transfer_dns_records');
						CFGP_Cache::delete('transfer_dns_records');
						
						$return = array_merge($return, array(
							'isp' => (isset($get['isp']) ? $get['isp'] : NULL),
							'org' => (isset($get['org']) ? $get['org'] : NULL),
							'as' => (isset($get['as']) ? $get['as'] : NULL),
							'asname' => (isset($get['asname']) ? $get['asname'] : NULL),
							'dns' => (isset($get['reverse']) ? $get['reverse'] : $return['dns']),
						));
						
						$return = apply_filters( 'cfgp/api/dns/render/response', array(
							'ip_dns' => $return['dns'],
							'ip_dns_host' => $return['host'],
							'ip_dns_provider' => $return['provider'],
							'isp' => $return['isp'],
							'isp_organization' => $return['org'],
							'isp_as' => $return['as'],
							'isp_asname' => $return['asname']
						));
						
						// Save to session
						set_transient("cfgp-api-{$ip_slug}-dns", (array)$return, (MINUTE_IN_SECONDS * CFGP_SESSION));
					}
				}
				break;
		}
		
		return $return;
	}
	
	public static function remove_cache(){
		global $wpdb;
		
		// Remove plugins cache
		if ( is_multisite() && is_main_site() && is_main_network() ) {
			$wpdb->query("DELETE FROM
				`{$wpdb->sitemeta}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_cfgp-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_cfgp-api-%'
			)");
			} else {
			$wpdb->query("DELETE FROM
				`{$wpdb->options}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_cfgp-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_timeout_cfgp-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_cfgp-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_cfgp-api-%'
			)");
		}
		
		CFGP_Cache::delete( 'API' );
	}
	
	public static function country2locale($code)
    {
        # http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes
        $arr = apply_filters('cfgp/api/country_to_locale', CFGP_Defaults::COUNTRY_TO_LOCALE);
        #----
        $code = strtolower($code);
        if ($code == 'eu')
        {
            return 'en_GB';
        }
        elseif ($code == 'ap')
        { # Asia Pacific
            return 'en_US';
        }
        elseif ($code == 'cs')
        {
            return 'sr_RS';
        }
        #----
        if ($code == 'uk')
        {
            $code = 'gb';
        }
        if (array_key_exists($code, $arr))
        {
            if (strpos($arr[$code], ',') !== false)
            {
                $new = explode(',', $arr[$code]);
                $loc = array();
                foreach ($new as $key => $val)
                {
                    $loc[] = $val . '_' . strtoupper($code);
                }
                return implode(',', $loc); # string; comma-separated values 'en_GB,ga_GB,cy_GB,gd_GB,kw_GB'
                
            }
            else
            {
                return $arr[$code] . '_' . strtoupper($code); # string 'en_US'
                
            }
        }
        return 'en_US';
    }
	
	/**
	 * Get currency by country
	 *
	 * @since    6.0.0
	 */
	protected function get_currency($find) {
		$res = CFGP_U::array_find_deep(CFGP_Defaults::CURRENCY_BY_COUNTRY, $find);
		if(isset($res[0]))
			return $res[0];
		else
			return '';
    }
	
	/**
	 * Fix DNS host data
	 *
	 * @since    6.0.0
	 */
	protected function fix_dns_host($result){
		$return = array();
		
		if(isset($result['reverse']))
		{
			$noIP		=	str_replace($result['ipNumber'],'',$result['reverse']);
			$split		=	explode('.',$noIP);
			$clean		=	array_filter($split);
			$keys		=	array_keys($clean);
			$lastKey	=	end($keys);
			
			if(empty($lastKey) || $lastKey===0){}else
			{
				$provider = 'https://'.$split[($lastKey-1)].'.'.$split[$lastKey];
				if (filter_var($provider, FILTER_VALIDATE_URL) !== false) {
					// Get Provider
					$return['provider'] = $provider;
					// Get Host
					$return['host'] = $split[($lastKey-1)].'.'.$split[$lastKey];
				}
			}
			
			if(isset($result['reverse']) && !empty($result['reverse'])){
				$return['dns'] = $result['reverse'];
			}
			
		}
		return array_merge(array(
			'isp' => (isset($result['isp']) ? $result['isp'] : NULL),
			'org' => (isset($result['org']) ? $result['org'] : NULL),
			'as' => (isset($result['as']) ? $result['as'] : NULL),
			'asname' => (isset($result['asname']) ? $result['asname'] : NULL),
		), $return);
	}
	
	
	/*
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		$instance = CFGP_Cache::get(self::class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set(self::class, new self());
		}
		return $instance;
	}
}
endif;