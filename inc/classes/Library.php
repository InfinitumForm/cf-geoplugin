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
	 * Get Country Data
	 */
	public static function get_countries( $json = false ){
		static $country_data = [];
		
		if( $data = ($country_data ?? NULL) ){
				
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
				}
			}
			
			return $data;
		}
	
		$file_base = CFGP_LIBRARY;
		$file_path = '/';
		$file_name = 'countries.json';
		
		$file = apply_filters('cfgp/library/countries/path', array(
			'path' => "{$file_base}{$file_path}{$file_name}",
			'file_base' => $file_base,
			'file_path' => $file_path,
			'file_name' => $file_name
		));
			
		if(isset($file['path']) && file_exists($file['path'])){
			$data = '';
			$fh = fopen($file['path'],'r');
				while ($line = fgets($fh)){
					$data.=$line;
					fflush($fh);
				}
			fclose($fh); unset($fh);
			
			if( empty($data) ) {
				if($json === false){
					return array();
				}
				return '{}';
			}
			
			$country_data = $data;
			
			if($json === false){
				$data = json_decode( $data, true );
				if($data){
					$tr = array();
					foreach($data as $k=>$v){
						$tr[strtolower($k)]=$v;
					}
					$data = $tr; unset($tr);
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
	 * Get regions by country
	 */
	public static function get_regions( $country_code, $json = false ){
		static $country_region_data = array();
		
		$collection = array();
		
		if(!empty($country_code))
		{
			$file_base = CFGP_LIBRARY . '/regions';
			
			if(is_array($country_code)) {
				$country_codes = array_map('strtolower', $country_code);
				
				foreach($country_codes as $country_code){
					$country_code = strtolower($country_code);
				
					if( isset($country_region_data[$country_code]) ) {
						$collection = array_merge($collection, $country_region_data[$country_code]);
					} else {
						$file_path = "/{$country_code}";
						$file_name = "/{$country_code}.json";
						
						$file = apply_filters('cfgp/library/regions/path', array(
							'path' => "{$file_base}{$file_path}{$file_name}",
							'file_base' => $file_base,
							'file_path' => $file_path,
							'file_name' => $file_name,
							'country_code' => $country_code
						));
						
						$file = apply_filters("cfgp/library/regions/path/{$country_code}", $file);
						
						if(isset($file['path']) && file_exists($file['path'])){
							$data = '';
							$fh = fopen($file['path'],'r');
								while ($line = fgets($fh)){
									$data.=$line;
									fflush($fh);
								}
							fclose($fh); unset($fh);
							
							if( !empty($data) ) {
								$country_region_data[$country_code] = json_decode( $data, true );
								$collection = array_merge($collection, $country_region_data[$country_code]);
							}
						}
					}
				}
			} else {
				$country_code = strtolower($country_code);
				
				if( isset($country_region_data[$country_code]) ) {
					$collection = $country_region_data[$country_code];
				} else {
					$file_path = "/{$country_code}";
					$file_name = "/{$country_code}.json";
					
					$file = apply_filters('cfgp/library/regions/path', array(
						'path' => "{$file_base}{$file_path}{$file_name}",
						'file_base' => $file_base,
						'file_path' => $file_path,
						'file_name' => $file_name,
						'country_code' => $country_code
					));
					
					$file = apply_filters("cfgp/library/regions/path/{$country_code}", $file);
					
					if(isset($file['path']) && file_exists($file['path'])){
						$data = '';
						$fh = fopen($file['path'],'r');
							while ($line = fgets($fh)){
								$data.=$line;
								fflush($fh);
							}
						fclose($fh); unset($fh);
						
						if( !empty($data) ) {
							$collection = json_decode( $data, true );
							$country_region_data[$country_code] = $collection;
						}
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
		
		if($json === true) {
			return json_encode($collection);
		}
		
		return $collection;
	}
	
	
	/*
	 * Get cities by country
	 */	
	public static function get_cities( $country_code, $json = false ){
		static $country_city_data = array();
		
		$collection = array();
		
		if(!empty($country_code))
		{
			$file_base = CFGP_LIBRARY . '/cities';
			
			if(is_array($country_code)) {
				$country_codes = array_map('strtolower', $country_code);
				
				foreach($country_codes as $country_code){
					$country_code = strtolower($country_code);
				
					if( isset($country_city_data[$country_code]) ) {
						$collection = array_merge($collection, $country_city_data[$country_code]);
					} else {
						$file_path = "/{$country_code}";
						$file_name = "/{$country_code}.json";
						
						$file = apply_filters('cfgp/library/cities/path', array(
							'path' => "{$file_base}{$file_path}{$file_name}",
							'file_base' => $file_base,
							'file_path' => $file_path,
							'file_name' => $file_name,
							'country_code' => $country_code
						));
						
						$file = apply_filters("cfgp/library/cities/path/{$country_code}", $file);
						
						if(isset($file['path']) && file_exists($file['path'])){
							$data = '';
							$fh = fopen($file['path'],'r');
								while ($line = fgets($fh)){
									$data.=$line;
									fflush($fh);
								}
							fclose($fh); unset($fh);
							
							if( !empty($data) ) {
								$country_city_data[$country_code] = json_decode( $data, true );
								$collection = array_merge($collection, $country_city_data[$country_code]);
							}
						}
					}
				}
			} else {
				$country_code = strtolower($country_code);
				
				if( isset($country_city_data[$country_code]) ) {
					$collection = $country_city_data[$country_code];
				} else {
					$file_path = "/{$country_code}";
					$file_name = "/{$country_code}.json";
					
					$file = apply_filters('cfgp/library/cities/path', array(
						'path' => "{$file_base}{$file_path}{$file_name}",
						'file_base' => $file_base,
						'file_path' => $file_path,
						'file_name' => $file_name,
						'country_code' => $country_code
					));
					
					$file = apply_filters("cfgp/library/cities/path/{$country_code}", $file);
					
					if(isset($file['path']) && file_exists($file['path'])){
						$data = '';
						$fh = fopen($file['path'],'r');
							while ($line = fgets($fh)){
								$data.=$line;
								fflush($fh);
							}
						fclose($fh); unset($fh);
						
						if( !empty($data) ) {
							$collection = json_decode( $data, true );
							$country_city_data[$country_code] = $collection;
						}
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
		
		if($json === true) {
			return json_encode($collection);
		}
		
		return $collection;
	}
	
	
	/*
	 * Get postcode by country code and city name
	 */
	public static function get_postcode( $country_code, $city ){
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
	 * Get postcode by country
	 */
	public static function get_postcodes( $country_code, $json = false ){
		if(!empty($country_code))
		{
			$country_code = strtolower($country_code);
			
			$file_base = CFGP_LIBRARY . '/postcodes';
			$file_path = "/{$country_code}";
			$file_name = "/{$country_code}.json";
			
			$file = apply_filters('cfgp/library/postcodes/path', array(
				'path' => "{$file_base}{$file_path}{$file_name}",
				'file_base' => $file_base,
				'file_path' => $file_path,
				'file_name' => $file_name,
				'country_code' => $country_code
			));
			
			$file = apply_filters("cfgp/library/postcodes/path/{$country_code}", $file);
			
			if(isset($file['path']) && file_exists($file['path'])){
				$JSON = '';
				$fh = fopen($file['path'],'r');
					while ($line = fgets($fh)){
						$JSON.=$line;
						fflush($fh);
					}
				fclose($fh); unset($fh);
				
				if( empty($JSON) ) {
					if($json === false){
						return array();
					}
					return '{}';
				}
				if($json === false){
					$data = json_decode( $JSON, true );
				}
				return $data;
			}
		}
		
		if($json === false){
			return array();
		}
		
		return '{}';
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
	
	
	/*
	 * PROTECTED DEVELOPER FUNCTION
	 * Genera library from the region and city database
	 * and save to the nasted folders
	 *
	 * Used only for the development
	 */
	public static function generate_city_from_library(){
		
		$cr_data = array();
		$request = wp_remote_get( 'https://storage.ip-api.com/data/cities.json' );
		if( !is_wp_error( $request ) ) {
			$JSON = wp_remote_retrieve_body( $request );
			$cr_data = json_decode( $JSON, true );
		}

		$array = array();
		
		if(!is_dir(CFGP_LIBRARY . '/cities')){
			@mkdir(CFGP_LIBRARY . '/cities', '0755');
		}
		if(!file_exists(CFGP_LIBRARY . '/cities/index.php')){
			@touch(CFGP_LIBRARY . '/cities/index.php');
		}

		foreach($cr_data as $country_code=>$city_data){
			$country_code = strtolower($country_code);
			$cities = array();
			
			foreach($city_data as $region_code => $cities_lib)
			{
				$cities = array_merge($cities, $cities_lib);
			}
			
			if(!empty($cities)){
				
				$cities = array_unique($cities);
				sort($cities);
				
				if(!is_dir(CFGP_LIBRARY . '/cities/'.$country_code)){
					@mkdir(CFGP_LIBRARY . '/cities/'.$country_code, '0755', true);
				}				
				if(!file_exists(CFGP_LIBRARY . '/cities/'.$country_code.'/'.$country_code.'.json')){
					@touch(CFGP_LIBRARY . '/cities/'.$country_code.'/'.$country_code.'.json');
				}
				if(!file_exists(CFGP_LIBRARY . '/cities/'.$country_code.'/index.php')){
					@touch(CFGP_LIBRARY . '/cities/'.$country_code.'/index.php');
				}
				file_put_contents(CFGP_LIBRARY . '/cities/'.$country_code.'/'.$country_code.'.json', json_encode($cities));
				
				$array[$country_code] = $cities;
			}
		}
		return $array;
	}
	
	public static function generate_region_from_library(){
		
		$cr_data = array();
		$request = wp_remote_get( 'https://storage.ip-api.com/data/regions.json' );
		if( !is_wp_error( $request ) ) {
			$JSON = wp_remote_retrieve_body( $request );
			$cr_data = json_decode( $JSON, true );
		}

		$array = array();
		
		if(!is_dir(CFGP_LIBRARY . '/regions')){
			@mkdir(CFGP_LIBRARY . '/regions', '0755');
		}
		if(!file_exists(CFGP_LIBRARY . '/regions/index.php')){
			@touch(CFGP_LIBRARY . '/regions/index.php');
		}

		foreach($cr_data as $country_code=>$regions){
			$country_code = strtolower($country_code);
			
			if(!empty($regions)){
				
				$regions = array_unique($regions);
				sort($regions);
				
				if(!is_dir(CFGP_LIBRARY . '/regions/'.$country_code)){
					@mkdir(CFGP_LIBRARY . '/regions/'.$country_code, '0755', true);
				}				
				if(!file_exists(CFGP_LIBRARY . '/regions/'.$country_code.'/'.$country_code.'.json')){
					@touch(CFGP_LIBRARY . '/regions/'.$country_code.'/'.$country_code.'.json');
				}
				if(!file_exists(CFGP_LIBRARY . '/regions/'.$country_code.'/index.php')){
					@touch(CFGP_LIBRARY . '/regions/'.$country_code.'/index.php');
				}
				file_put_contents(CFGP_LIBRARY . '/regions/'.$country_code.'/'.$country_code.'.json', json_encode($regions));
				$array[$country_code] = $regions;
			}
		}
		return $array;
	}
}
endif;