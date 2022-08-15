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
 * Some library comes from https://mainfacts.com/ and https://lite.ip2location.com/ip-address-ranges-by-country as IP adress, counry names etc.
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Library')) :
class CFGP_Library {
	
	/*
	 * Ajax functionality for the select2 search
	 */
	public static function ajax__select2_locations() {
		// Get search keywords
		$search = CFGP_U::strtolower(sanitize_text_field($_REQUEST['search'] ?? ''));
		$type = sanitize_text_field($_REQUEST['type'] ?? '');
		// Set country codes
		$country_codes = ($_REQUEST['country_codes'] ?? NULL);
		if( !is_array($country_codes) ){
			$country_codes = NULL;
		} else {
			$country_codes = array_map('trim', $country_codes);
			$country_codes = array_filter($country_codes);
			$country_codes = array_unique($country_codes);
		}
		// Pagination
		$page = absint($_REQUEST['page'] ?? 1);
		$per_page = 20;
		$offset = 0;
		$more = 0;
		if($page > 1){
			$offset = ($per_page*$page);
		}
		// Collect results
		$results = [];
		// Switch type of search
		switch($type) {
			// country
			case 'country':
				if($countries = self::get_countries()) {
					foreach($countries as $country_code=>$country_name) {
						if( 
							empty($search) 
							|| strpos(CFGP_U::strtolower($country_code), $search) !== false 
							|| strpos(CFGP_U::strtolower($country_name), $search) !== false 
						) {
							$results[$country_code]=array(
								'id' => $country_code,
								'text' => $country_name
							);
						}
					}
					$countries = NULL;
				}
			break;
			// region
			case 'region':
				if ( $country_codes ) {
					if($regions = self::get_regions($country_codes)) {
						foreach( $regions as $region_real_code => $region_name ){
							$region_code = sanitize_title( $region_name );
							if(
								empty($search) 
								|| strpos(CFGP_U::strtolower($region_name), $search) !== false 
								|| strpos(CFGP_U::strtolower($region_code), $search) !== false 
								|| strpos(CFGP_U::strtolower($region_real_code), $search) !== false 
							) {
								$results[$region_code]=array(
									'id' => $region_code,
									'text' => $region_name
								);
							}
						}
					}
				}
			break;
			// city
			case 'city':
				if ( $country_codes ) {
					if($cities = self::get_cities($country_codes)) {
						foreach( $cities as $city ){
							$city_code = sanitize_title( CFGP_U::transliterate($city) );
							if( 
								empty($search) 
								|| strpos(CFGP_U::strtolower($city), $search) !== false 
								|| strpos(CFGP_U::strtolower($city_code), $search) !== false 
							) {
								$results[$city_code]=array(
									'id' => $city_code,
									'text' => $city
								);
							}
						}
					}
				}
			break;
			// postcode
			case 'postcode':
				if ( $country_codes ) {
					if($postcodes = self::get_postcodes($country_codes)) {
						foreach( $postcodes as $city_name => $postcode ){
							$postcode_code = absint($postcode);
							if( 
								empty($search) 
								|| strpos(CFGP_U::strtolower($postcode), $search) !== false 
								|| strpos(CFGP_U::strtolower($city_name), $search) !== false 
							) {
								$results[$postcode_code]=array(
									'id' => $postcode_code,
									'text' => $postcode . ' - ' . $city_name
								);
							}
						}
					}
				}
			break;
		}
		
		sort($results);
		
		$more = count($results);
		$results = array_slice($results, $offset, $per_page);
		
		// Return data
		wp_send_json(array(
			'results'=>$results,
			'pagination'=>array(
				'more' => ($more > ($offset+$per_page))
			)
		));
		
		// exit for any case
		exit;
	}
	
	
	/*
	 * Get Country Data by API
	 */
	public static function get_countries( $json = false ){
		static $country_data = [];
		
		if( $data = ($country_data ?? NULL) ){
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		if( $data = (CFGP_DB_Cache::get('library/get_countries') ?? NULL) ){
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		$response = wp_remote_get(
			CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'countries'],
			array(
				'Content-Type' => 'application/json; charset=utf-8'
			)
		);
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$data = json_decode( $response['body'] );
			$data_array = (array)$data->countries;
			$data = json_encode($data_array);
			
			$country_data = $data;
			if( !empty($data) ) {
				CFGP_DB_Cache::set('library/get_countries', $data, DAY_IN_SECONDS);
			}
			
			if($json === false){
				if($data_array){
					$tr = array();
					foreach($data_array as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr, $data_array);
				}
			}
			
			return $data;
		}
		
		if($json === false){
			return array();
		}
		
		return '{}';
	}
		
	
	/*
	 * Get regions by country from API
	 */
	public static function get_regions ($countries, $json=false) {
		static $regions_data;
		
		if(empty($countries)) {
			if($json === false){
				return array();
			}
			return '{}';
		}
		
		$countries = array_map('trim', $countries);
		$countries = array_filter($countries);
		$countries = array_map(array('CFGP_U', 'strtolower'), $countries);
		$countries = join(',', $countries);
		
		
		if( $data = ($regions_data[$countries] ?? NULL) ){
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		if( $data = CFGP_DB_Cache::get('library/get_regions/' . $countries) ) {
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		$response = wp_remote_get(
			CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'regions'] . '/' . $countries,
			array(
				'Content-Type' => 'application/json; charset=utf-8'
			)
		);
		
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$response = json_decode( $response['body'], true );
			$response = $response['regions'];
			
			$data_array = array();
			
			foreach( (array)$response as $country_code => $regions ) {
				foreach( (array)$regions as $region ) {
					if( in_array($region, $data_array) === false ) {
						$data_array[] = mb_convert_encoding($region,'HTML-ENTITIES','UTF-8');
					}
				}
			}
			
			$data = json_encode($data_array);
			
			$regions_data[$countries] = $data;
			if( !empty($data) ) {
				CFGP_DB_Cache::set('library/get_regions/' . $countries, $data, HOUR_IN_SECONDS);
			}
			
			if($json === false){
				if($data_array){
					$tr = array();
					foreach($data_array as $k=>$v){
						$tr[ CFGP_U::strtolower($k) ]=$v;
					}
					$data = $tr; unset($tr, $data_array);
				}
			}
			
			return $data;
		}
		
		if($json === false){
			return array();
		}
		return '{}';
	}
	
	/*
	 * Get cities by country from API
	 */	
	public static function get_cities ($countries, $json=false) {
		static $cities_data;
		
		if(empty($countries)) {
			if($json === false){
				return array();
			}
			return '{}';
		}
		
		$countries = array_map('trim', $countries);
		$countries = array_filter($countries);
		$countries = array_map(array('CFGP_U', 'strtolower'), $countries);
		$countries = join(',', $countries);
		
		
		if( $data = ($cities_data[$countries] ?? NULL) ) {
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		if( $data = CFGP_DB_Cache::get('library/get_cities/' . $countries) ) {
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[CFGP_U::strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
		
		$response = wp_remote_get(
			CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'cities'] . '/' . $countries,
			array(
				'Content-Type' => 'application/json; charset=utf-8'
			)
		);
		
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$response = json_decode( $response['body'], true );
			$response = $response['cities'];
			
			$data_array = array();
			
			foreach( (array)$response as $country_code => $cities ) {
				foreach( (array)$cities as $city ) {
					if( in_array($city, $data_array) === false ) {
						$data_array[] = mb_convert_encoding($city,'HTML-ENTITIES','UTF-8');
					}
				}
			}
			
			$data = json_encode($data_array);
			
			$cities_data[$countries] = $data;
			if( !empty($data) ) {
				CFGP_DB_Cache::set('library/get_cities/' . $countries, $data, HOUR_IN_SECONDS);
			}
			
			if($json === false){
				if($data_array){
					$tr = array();
					foreach($data_array as $k=>$v){
						$tr[ CFGP_U::strtolower($k) ]=$v;
					}
					$data = $tr; unset($tr, $data_array);
				}
			}
			
			return $data;
		}
		
		if($json === false){
			return array();
		}
		return '{}';
	}
	
	
	/*
	 * Get postcode by country
	 */
	public static function get_postcodes( $country_code, $json = false ) {
		static $country_postcode_data = array();
		
		$collection = array();
		
		if(!empty($country_code))
		{
			$file_base = CFGP_LIBRARY . DIRECTORY_SEPARATOR . 'postcodes';
			
			if(is_array($country_code)) {
				$country_codes = array_map(array('CFGP_U', 'strtolower'), $country_code);
				
				foreach($country_codes as $country_code){
					$country_code = CFGP_U::strtolower($country_code);
					
					if(strlen($country_code) > 2){
						continue;
					}
				
					if( isset($country_postcode_data[$country_code]) ) {
						$collection = array_merge($collection, $country_postcode_data[$country_code]);
					} else {
						
						$term_collection=array();
						if( $get_terms = get_terms(array(
							'taxonomy'		=> 'cf-geoplugin-postcode',
							'meta_key'		=> 'country',
							'meta_value'	=> $country_code,
							'hide_empty'	=> false
						)) ) {
							if( !is_wp_error($get_terms) && is_array($get_terms) ){
								foreach( $get_terms as $term ){
									$term_collection[ get_term_meta( $term->term_id, 'city', true ) ?? get_term_meta( $term->term_id, 'country', true ) ?? $term->slug ] = $term->name;
								}
							}
						}
						
						$file_path = DIRECTORY_SEPARATOR . "{$country_code}";
						$file_name = DIRECTORY_SEPARATOR . "{$country_code}.json";
						
						$file = apply_filters('cfgp/library/postcodes/path', array(
							'path' => "{$file_base}{$file_path}{$file_name}",
							'file_base' => $file_base,
							'file_path' => $file_path,
							'file_name' => $file_name,
							'country_code' => $country_code
						));
						
						$file = apply_filters("cfgp/library/postcodes/path/{$country_code}", $file);
						
						if(isset($file['path']) && file_exists($file['path'])){
							$data = '';
							$fh = fopen($file['path'],'r');
								while (($line = stream_get_line($fh, 1024)) !== false){
									$data.=$line;
									fflush($fh);
								}
							fclose($fh); unset($fh);
							
							if( !empty($data) ) {
								$data = json_decode( $data, true );
								$collection = array_merge($collection, array_merge($term_collection, $data));
							}
						}
						
						if(!empty($collection)) {
							$country_postcode_data[$country_code] = $collection;
						} else if (!empty($term_collection)) {
							$country_postcode_data[$country_code] = $term_collection;
							$collection = array_merge($collection, $country_postcode_data[$country_code]);
						}
					}
				}
			} else {
				$country_code = CFGP_U::strtolower($country_code);
				if(strlen($country_code) > 2){
					return array();
				}
				
				if( isset($country_postcode_data[$country_code]) ) {
					$collection = $country_postcode_data[$country_code];
				} else {
					if( $get_terms = get_terms(array(
						'taxonomy'		=> 'cf-geoplugin-postcode',
						'meta_key'		=> 'country',
						'meta_value'	=> $country_code,
						'hide_empty'	=> false
					)) ) {
						if( !is_wp_error($get_terms) && is_array($get_terms) ){
							foreach( $get_terms as $term ){
								$collection[ get_term_meta( $term->term_id, 'city', true ) ?? get_term_meta( $term->term_id, 'country', true ) ?? $term->slug ] = $term->name;
							}
						}
					}
					
					$file_path = DIRECTORY_SEPARATOR . "{$country_code}";
					$file_name = DIRECTORY_SEPARATOR . "{$country_code}.json";
					
					$file = apply_filters('cfgp/library/postcodes/path', array(
						'path' => "{$file_base}{$file_path}{$file_name}",
						'file_base' => $file_base,
						'file_path' => $file_path,
						'file_name' => $file_name,
						'country_code' => $country_code
					));
					
					$file = apply_filters("cfgp/library/postcodes/path/{$country_code}", $file);
					
					if(isset($file['path']) && file_exists($file['path'])){
						$data = '';
						$fh = fopen($file['path'],'r');
							while (($line = stream_get_line($fh, 1024)) !== false){
								$data.=$line;
								fflush($fh);
							}
						fclose($fh); unset($fh);
						
						if( !empty($data) ) {
							$collection = array_merge($collection, json_decode( $data, true ));
						}
					}
					
					if( !empty($collection) ) {
						$country_postcode_data[$country_code] = $collection;
					}
				}
			}
		}
		
		if(isset($data)){
			$data = NULL;
		}

		if(!empty($collection)) {
			$collection = array_unique($collection);
		}
		
		$collection = apply_filters('cfgp/library/postcodes/collection', $collection);
		
		if($json === true) {
			return json_encode($collection);
		}
		
		return $collection;
	}
	
	/*
	 * Get postcode by country code and city name
	 */
	public static function get_postcode( $country_code, $city ){
		if(strlen($country_code) > 2){
			return NULL;
		}
				
		if( $postcode = CFGP_Cache::get('cfgeo/libraray/get_postcode') ) {
			return $postcode;
		}
		
		if( $country_code && $city && ($postcodes = self::get_postcodes($country_code)) ) {
			if( isset($postcodes[$city]) ) {
				return CFGP_Cache::set('cfgeo/libraray/get_postcode', $postcodes[$city]);
			}
		}
		
		$postcodes = NULL;
		
		return $postcodes;
	}
	
	/*
	 * Get cities by country
	 */
	public static function all_geodata( $json = false ){
		$geodata = get_option(CFGP_NAME . '-all-geodata', array());
		
		if(empty($geodata) || CFGP_LIBRARY_VERSION != get_option(CFGP_NAME . '-library-version', ''))
		{
			$geodata = array();
			foreach(self::get_countries() as $country_code => $country){
				$regions=array();
				foreach(self::get_regions($country_code) as $region){
					$regions[sanitize_title($region)] = $region;
				}
				$cities=array();
				foreach(self::get_cities($country_code) as $city){
					$cities[sanitize_title($city)] = $city;
				}
				$geodata[$country_code]=array(
					'region' => $regions,
					'city' => $cities
				);
			}
			update_option(CFGP_NAME . '-all-geodata', $geodata, false);
			update_option(CFGP_NAME . '-library-version', CFGP_LIBRARY_VERSION, false);
		}
		
		if($json){
			return json_encode($geodata);
		} else {
			return $geodata;
		}
	}
	
}
endif;