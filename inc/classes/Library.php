<?php
/**
 * Main API class
 *
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Library')) :
class CFGP_Library {
	private static $country_region_data_json;
	private static $country_region_data;
	
	/*
	 * Get Country Region Data
	 */
	public static function get_country_region_data($json=false){
		
		if($json) {
			if(!empty(self::$country_region_data) ){
				return self::$country_region_data;
			}
		} else {
			if(!empty(self::$country_region_data) ){
				return self::$country_region_data;
			}
		}
		
		$path = apply_filters('cfgp/library/country_region_data/path', CFGP_LIBRARY . '/country-region-data.min.json');
		if(!file_exists($path)){
			return false;
		}
		$JSON = '';
		$fh = fopen($path,'r');
			while ($line = fgets($fh)){$JSON.=$line;}
		fclose($fh);
		if( empty($JSON) ) {
			return false;
		}
		
		if($json) {
			self::$country_region_data_json = $JSON;
			return self::$country_region_data_json;
		} else {
			self::$country_region_data = json_decode( $JSON, true );
			return self::$country_region_data;
		}
	}
	
	/*
	 * Get Country Data
	 */
	public static function get_countries( $value = 'country_name', $id = 'country_code', $json = false ){

		if($data = self::get_country_region_data($json)) {
			return wp_list_pluck( $data, $value, $id );
		}
		if($json === false){
			return array();
		}
		return '{}';
	}
	
	/*
	 * Get regions by country
	 */
	public static function get_regions( $country, $json = false ){
		if(!empty($country) && $data = self::get_country_region_data())
		{
			$country = strtolower($country);
			foreach ($data as $key => $fetch) {
				if ($fetch['country_code'] === $country || strtolower($fetch['country_name']) === $country) {
					$regions = $fetch['regions'];
					if($json){
						$regions = json_encode($regions);
					}
					return $regions;
				}
			} 
		}
		return array();
	}
	
	/*
	 * Get cities by country
	 */
	public static function get_cities( $country_code, $json = false ){
		if(!empty($country_code))
		{
			$country_code = strtolower($country_code);
			
			$file_base = CFGP_LIBRARY . '/cities';
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
				$JSON = '';
				$fh = fopen($file['path'],'r');
					while ($line = fgets($fh)){$JSON.=$line;}
				fclose($fh);
				if( empty($JSON) ) {
					if($json === false){
						return array();
					}
					return '{}';
				}
				if($json === false){
					$data = json_decode( $JSON, true );
					sort($data);
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
			foreach(self::get_countries() as $country_code => $country){
				$regions=array();
				foreach(self::get_regions($country_code) as $region){
					$regions[strtolower(sanitize_title($region['region']))] = $region['region'];
				}
				$cities=array();
				foreach(self::get_cities($country_code) as $city){
					$cities[strtolower(sanitize_title($city))] = $city;
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
	 * Genera library from the city database
	 * and save to the nasted folders
	 *
	 * Used only for the development
	 */
	protected static function generate_city_from_library(){
		
		
		$path = CFGP_LIBRARY . '/cities.json';
		$cr_data = self::get_country_region_data();
		
		$JSON = '';
		$fh = fopen($path,'r');
			while ($line = fgets($fh)){$JSON.=$line;}
		fclose($fh);
		
		if( empty($JSON) ) {
			return false;
		}
		
		$city_data = json_decode( $JSON, true );

		$array = array();
		
		if(!is_dir(CFGP_LIBRARY . '/cities')){
			@mkdir(CFGP_LIBRARY . '/cities', '0755');
		}
		if(!file_exists(CFGP_LIBRARY . '/cities/index.php')){
			@touch(CFGP_LIBRARY . '/cities/index.php');
		}

		foreach($cr_data as $i=>$fetch){
			$cities = array();
			foreach($city_data as $city)
			{
				if(strtoupper($fetch['country_code']) == strtoupper($city['country'])){
					if(!is_dir(CFGP_LIBRARY . '/cities/'.$fetch['country_code'])){
						@mkdir(CFGP_LIBRARY . '/cities/'.$fetch['country_code'], '0755', true);
					}
					
					$cities[]=$city['name'];
				}
			}
			
			if(!empty($cities)){
				if(!file_exists(CFGP_LIBRARY . '/cities/'.$fetch['country_code'].'/'.$fetch['country_code'].'.json')){
					@touch(CFGP_LIBRARY . '/cities/'.$fetch['country_code'].'/'.$fetch['country_code'].'.json');
				}
				if(!file_exists(CFGP_LIBRARY . '/cities/'.$fetch['country_code'].'/index.php')){
					@touch(CFGP_LIBRARY . '/cities/'.$fetch['country_code'].'/index.php');
				}
				file_put_contents(CFGP_LIBRARY . '/cities/'.$fetch['country_code'].'/'.$fetch['country_code'].'.json', json_encode($cities));
				$array[$fetch['country_code']]= $cities;
			}
			
		}
		return $array;
	}
}
endif;