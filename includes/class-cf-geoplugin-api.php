<?php

if (!defined('WPINC'))
{
	die("Don't mess with us.");
}

/**
 * CF Geo Plugin API
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */

if (!class_exists('CF_Geoplugin_API')):
class CF_Geoplugin_API extends CF_Geoplugin_Global
{
	/**
	 * Available API calls.
	 *
	 * @since    7.0.0
	 * @access   private
	 * @var      array
	 */
	private $url = array(
		'api' 					=> 'https://cdn-cfgeoplugin.com/api6.0/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}&base_convert={CURRENCY}',
		'api_alternate' 		=> 'http://159.203.47.151/api6.0/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}&base_convert={CURRENCY}',
		'dns' 					=> 'https://cdn-cfgeoplugin.com/api6.0/dns.php?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}',
		'dns_alternate' 		=> 'http://159.203.47.151/api6.0/dns.php?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}',
	);
	
	/**
	 * Geoplugin default return fields.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      array      $fields
	 */
	private $fields = array(
		'ipAddress' 			=> '',
		'ipVersion' 			=> '',
		'ipNumber' 				=> '',
		'countryCode' 			=> '',
		'countryName' 			=> '',
		'regionName' 			=> '',
		'regionCode' 			=> '',
		'cityName' 				=> '',
		'continent' 			=> '',
	//	'continentCode' 		=> '',
		'address' 				=> '',
	//	'areaCode'				=> '',
	//	'dmaCode'				=> '',
		'latitude' 				=> 0,
		'longitude' 			=> 0,
		'timezone' 				=> '',
		'locale'				=> '',
		'currency' 				=> '',
		'base_currency'			=> '',
	//	'currencySymbol' 		=> '',
	//	'currencyConverter' 	=> 0,
		'currency_symbol' 		=> '',
		'currency_converter' 	=> 0,
		'base_currency_symbol' 	=> '',
		'referer' 				=> '',
		'refererIP' 			=> '',
		'timestamp' 			=> '',
		'timestampReadable' 	=> '',
		'currentTime' 			=> '',
		'currentDate' 			=> '',
		'current_time' 			=> '',
		'current_date' 			=> '',
		'error' 				=> '',
		'message' 				=> '',
		'runtime' 				=> '',
		'credit' 				=> '',
		'status' 				=> '',
		'version' 				=> CFGP_VERSION,
		'lookup' 				=> 0,
		'available_lookup' 		=> 0,
		'in_eu'					=> 0,
		'is_vat'				=> 0,
		'vat_rate'				=> 0,
		'gps'					=> 0,
		'accuracy_radius'		=> 0,
	);
	// PRIVATE OPTION
	private $option = array();
	
	private $return = array();
	
	/**
	 * Run API
	 *
	 * @since    7.0.0
	 */
	public function run($options=array()){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$option=array(
			'ip'			=>	CFGP_IP,
			'base_currency'	=>	( isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) ? $CF_GEOPLUGIN_OPTIONS['base_currency'] : 'USD' ),
			'debug'			=>	false
		);

		// replace default options
		if (version_compare(PHP_VERSION, '7.0.0', '>='))
			$this->option=array_replace($option, $options);
		else
			$this->option=array_merge($option, $options);
		
		return apply_filters( 'cf_geoplugin_api_response', $this->render(), $this->fields );
	}
	
	/**
	 * Render all data properly
	 *
	 * @since    7.0.0
	 */
	private function render()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$geodata = $this->get_geodata($this->option['ip']);
		CF_Geoplugin_Debug::log( 'IP used for lookup: '. $this->option['ip'] );
		if($geodata!==false)
		{
			
			$provider=$this->get_dns($geodata->ipAddress);
			
			$lng = (isset($geodata->longitude) ? $geodata->longitude : 0);
			$lat = (isset($geodata->latitude) ? $geodata->latitude : 0);
			
			$countryCode = $geodata->countryCode;
			
			$url=self::URL();
			$url=strtolower($url->url);
			

			$currency = $this->get_currency($countryCode);
			$ipv = $geodata->ipVersion;
			
			$currency_symbol = CF_Geplugin_Library::CURRENCY_SYMBOL;
			if(isset($currency_symbol[$currency]))
				$currency_symbol = $currency_symbol[$currency];
			else
				$currency_symbol = isset($geodata->currencySymbol) ? $geodata->currencySymbol : NULL;
			
			$continent = empty($geodata->continent) ? $this->array_find_parent(CF_Geplugin_Library::COUNTRY_REGION_LIST, $countryCode) : $geodata->continent;
			$continentCode =  isset($geodata->continentCode) ? $geodata->continentCode : NULL;
			if(empty($continentCode)){
				$continentCodeArr = array_flip(CF_Geplugin_Library::CONTINENT_LIST);
				$continentCode = isset($continentCodeArr[$continent]) ? $continentCodeArr[$continent] : '';
			}
			
			$m_unit = __('km',CFGP_NAME);
			$m_accuracy = isset( $geodata->accuracy ) ? $geodata->accuracy : 1;
			
			if(isset($CF_GEOPLUGIN_OPTIONS['measurement_unit']))
			{
				switch($CF_GEOPLUGIN_OPTIONS['measurement_unit'])
				{
					case 'km':
						$m_unit = __('km',CFGP_NAME);
						break;
					case 'mile':
						$m_unit = __('mile',CFGP_NAME);
						$m_accuracy = number_format(abs($m_accuracy * 0.621371), 2);
						break;
				}
			}
			
			return apply_filters( 'cf_geoplugin_api_render_response', array(
                'ip' => $geodata->ipAddress,
                'ip_version' => $ipv,
                'ip_dns' => $provider->dns,
                'ip_dns_host' => $provider->host,
                'ip_dns_provider' => $provider->provider,
                'ip_number' => $geodata->ipNumber,
                'country_code' => $countryCode,
                'country' => $geodata->countryName,
                'region' => $geodata->regionName, //regionCode
                'region_code' => $geodata->regionCode,
                'state' => $geodata->regionName, // deprecated
                'city' => $geodata->cityName,
                'continent' => $continent,
                'continent_code' => $continentCode,
            //    'continentCode' => $continentCode, // deprecated
                'address' => $geodata->address,
                'area_code' => (isset($geodata->areaCode) ? $geodata->areaCode : NULL),
            //   'areaCode' => $geodata->areaCode, // deprecated
                'dma_code' => (isset($geodata->dmaCode) ? $geodata->dmaCode : NULL),
            //    'dmaCode' => $geodata->dmaCode, // deprecated
                'latitude' => $lat,
                'longitude' => $lng,
                'timezone' => $geodata->timezone,
				'locale' => CF_Geoplugin_Locale::country2locale($countryCode),
                'timezoneName' => $geodata->timezone, // deprecated
                'currency' => $currency,
                'currency_symbol' => $currency_symbol,
				'base_currency' => $CF_GEOPLUGIN_OPTIONS['base_currency'],
				'base_currency_symbol' => CF_Geplugin_Library::CURRENCY_SYMBOL[$CF_GEOPLUGIN_OPTIONS['base_currency']],
            //    'currencySymbol' => $currency_symbol, // deprecated
                'currency_converter' => (!empty($geodata->currencyConverter) ? $geodata->currencyConverter : 0),
            //    'currencyConverter' => (!empty($geodata->currencyConverter) ? $geodata->currencyConverter : 0), // deprecated
                'host' => $geodata->referer,
                'ip_host' => $geodata->refererIP,
                'timestamp' => $geodata->timestamp,
                'timestamp_readable' => $geodata->timestampReadable,
                'current_time' => $geodata->currentTime,
                'current_date' => $geodata->currentDate,
                'version' => CFGP_VERSION,
				'is_vat' => isset( $geodata->isVAT ) ? (int)$geodata->isVAT : 0,
				'vat_rate'	=> isset( $geodata->VATrate ) ? (float)$geodata->VATrate : 0,
				'in_eu'	=> isset( $geodata->inEU ) ? (int)$geodata->inEU : 0,
				'gps'	=> isset( $geodata->gps ) ? ($geodata->gps ? 1 : 0) : 0,
				'accuracy_radius' => $m_accuracy.$m_unit,
				'runtime' => abs($geodata->runtime),
                'status' => empty($geodata->status) ? (!empty($geodata->ipAddress) ? 200 : 404) : $geodata->status,
				'lookup' => $geodata->available_lookup,
				'error' => $geodata->error,
                'error_message' => $geodata->message,
				'credit' => $geodata->credit,
            ), $this->fields);
		}
		
		return $this->fields;
	}
	
	/**
	 *Get currency by country
	 *
	 * @since    6.0.0
	 */
	protected function get_currency($find) {
        $a =
		$res = $this->array_find_deep(CF_Geplugin_Library::CURRENCY_BY_COUNTRY, $find);
		if(isset($res[0]))
			return $res[0];
		else
			return '';
    }
	
	/**
	 * Get geo informations
	 *
	 * @since    7.0.0
	 */
	private function get_geodata($ip = false)
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$this->check_validations();
		// Current or custom IP
		$ip = ($ip !== false ? $ip : CFGP_IP);
		if (isset($_SESSION[CFGP_PREFIX . 'api_session']) && isset($_SESSION[CFGP_PREFIX . 'api_session']['ipAddress']) && $_SESSION[CFGP_PREFIX . 'api_session']['ipAddress'] == $ip && $this->option['debug'] === false)
		{
			$_SESSION[CFGP_PREFIX . 'api_session']['currentTime'] = date('H:i:s', CFGP_TIME);
			$_SESSION[CFGP_PREFIX . 'api_session']['currentDate'] = date('F j, Y', CFGP_TIME);
			return (object)apply_filters('cf_geoplugin_api_get_geodata', $_SESSION[CFGP_PREFIX . 'api_session']);
		}
	
		$api = get_option('cf_geo_defender_api_key');
		if (!in_array($ip, $this->ip_blocked()))
		{
			$result = $this->fields;
	
			// Configure GET function
	
			$urlReplace = array_map("rawurlencode", array(
				$ip,
				CFGP_SERVER_IP,
				CFGP_TIME,
				self::get_host(true),
				CFGP_VERSION,
				get_bloginfo("admin_email"),
				$api,
				($this->option['base_currency'] && strlen($this->option['base_currency']) === 3 ? strtoupper($this->option['base_currency']) : $CF_GEOPLUGIN_OPTIONS['base_currency']),
			));
			$urlPharams = array(
				'{IP}',
				'{SIP}',
				'{TIME}',
				'{HOST}',
				'{VERSION}',
				'{M}',
				'{P}',
				'{CURRENCY}',
			);
			$url = str_replace($urlPharams, $urlReplace, $this->url['api']);
	
			// Get content from URL
	
			$return = self::curl_get($url);
			// Return objects from JSON data
	
			if ($return != false)
			{
				$return = json_decode($return, true);
				if (is_array($return)) $result = array_merge($result, $return);
				
				$result = apply_filters('cf_geoplugin_api_get_geodata', $result);
				
				if (isset($_SERVER["HTTP_CF_IPCOUNTRY"]) && $CF_GEOPLUGIN_OPTIONS['enable_cloudflare']) {
					$result['countryCode'] = $_SERVER["HTTP_CF_IPCOUNTRY"];
				}
				if($this->option['debug'] === false) $_SESSION[CFGP_PREFIX . 'api_session'] = $result;
				return (object)$result;
			}
			else
			{
				$url = str_replace($urlPharams , $urlReplace, $this->url['api_alternate']);
	
				// Get content from URL
	
				$return = self::curl_get($url);
	
				// Return objects from JSON data
	
				if ($return != false)
				{
					$return = json_decode($return, true);
					if (is_array($return)) $result = array_merge($result, $return);
					
					$result = apply_filters('cf_geoplugin_api_get_geodata', $result);
					
					if (isset($_SERVER["HTTP_CF_IPCOUNTRY"]) && $CF_GEOPLUGIN_OPTIONS['enable_cloudflare']) {
						$result['countryCode'] = $_SERVER["HTTP_CF_IPCOUNTRY"];
					}
					if($this->option['debug'] === false) $_SESSION[CFGP_PREFIX . 'api_session'] = $result;
					return (object)$result;
				}
				else return false;
			};
		}
	
		return false;
	}

	
	/**
	 * Get DNS informations
	 *
	 * @since    7.0.0
	 */
	private function get_dns($ip=false)
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$return = array(
			"ip" => $ip,
			"provider" => '',
			"host" => '',
			"dns" => '',
			"error" => true
		);
		
		if(!CFGP_ACTIVATED)
		{
			return (object)$return;
		}
		
		if ($CF_GEOPLUGIN_OPTIONS["enable_dns_lookup"] && self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) >= 1)
		{
			$ip = ($ip !== false ? $ip : CFGP_IP);
			$api = get_option('cf_geo_defender_api_key');
			
			if (isset($_SESSION[CFGP_PREFIX . 'api_dns_session']) && isset($_SESSION[CFGP_PREFIX . 'api_dns_session']['ip']) && $_SESSION[CFGP_PREFIX . 'api_dns_session']['ip'] == $ip && $this->option['debug'] === false)
			{
				return (object)apply_filters('cf_geoplugin_api_get_dns', $_SESSION[CFGP_PREFIX . 'api_dns_session']);
			}

			$urlReplace = array_map("rawurlencode", array(
				$ip,
				CFGP_SERVER_IP,
				self::get_host(true),
				CFGP_VERSION,
				$api
			));
			$urlPharams = array(
				'{IP}',
				'{SIP}',
				'{HOST}',
				'{VERSION}',
				'{P}'
			);
			$url = str_replace($urlPharams, $urlReplace, $this->url['dns']);
			
			$data = self::curl_get($url);
			if ($data !== false)
			{
				$data = json_decode($data, true);
				$return = array_merge($return, $data);
			}
			else
			{
				$url = str_replace($urlPharams, $urlReplace, $this->url['dns_alternate']);
				
				$data = self::curl_get($url);
				
				if ($data !== false)
				{
					$data = json_decode($data, true);
					$return = array_merge($return, $data);
				}
			}
			
			$return = apply_filters('cf_geoplugin_api_get_dns', $return);

			if($this->option['debug'] === false) $_SESSION[CFGP_PREFIX . 'api_dns_session'] = $return;
		}
		
		return (object)$return;
	}
	
	private function check_validations()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if(CFGP_ACTIVATED)
		{
			$expire = $CF_GEOPLUGIN_OPTIONS['license_expire'];
			if($expire > 0 && CFGP_TIME > $expire)
			{
				$CF_GEOPLUGIN_OPTIONS['license'] = 0;
				
				if( !(defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE) )
					update_option('cf_geoplugin', $CF_GEOPLUGIN_OPTIONS, true);
				else
					update_site_option('cf_geoplugin', $CF_GEOPLUGIN_OPTIONS);

				$GLOBALS['CF_GEOPLUGIN_OPTIONS']=$CF_GEOPLUGIN_OPTIONS;
			}
		}
	}
}
endif;