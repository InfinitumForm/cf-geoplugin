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
	private static $country_region_data;
	private static $countries;
	
	// Get Country Region Data From Options or JSON
	static function get_country_region_data(){
		
		if(!empty(self::$country_region_data) ){
			return self::$country_region_data;
		}
		
		$path = CFGP_LIBRARY . '/country-region-data.json';
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
		self::$country_region_data = json_decode( $JSON, true );
		return self::$country_region_data;
	}
	
	// Get Country Data
	static function get_countries( $value = 'country_name', $id = 'country_code' ){

		if( !empty(self::$countries) ){
			return self::$countries;
		}

		if($data = self::get_country_region_data())
		{
			self::$countries = wp_list_pluck( $data, $value, $id );
			return self::$countries;
		}
		return array();
	}
	
	// Get regions by country
	static function get_regions( $country ){
		if(!empty($country) && $data = self::get_country_region_data())
		{
			$country = strtolower($country);
			foreach ($data as $key => $fetch) {
			   if ($fetch['country_code'] === $country) {
				   return $fetch['regions'];
			   }
			   if (strtolower($fetch['country_name']) === $country) {
				   return $fetch['regions'];
			   }
		   } 
		}
		return array();
	}
	
	// Get regions by country
	static function get_cities( $country_code ){
		if(!empty($country_code))
		{
			$country_code = strtolower($country_code);

			$path = CFGP_LIBRARY . "/cities/{$country_code}/{$country_code}.json";
			if(file_exists($path)){
				$JSON = '';
				$fh = fopen($path,'r');
					while ($line = fgets($fh)){$JSON.=$line;}
				fclose($fh);
				if( empty($JSON) ) {
					return false;
				}
				$data = json_decode( $JSON, true );
				sort($data);
				
				return $data;
			}
		}
		return array();
	}
	
	
	
	/*
	 * DEVELOPER FUNCTION
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
			@mkdir(CFGP_LIBRARY . '/cities', '0777');
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
						@mkdir(CFGP_LIBRARY . '/cities/'.$fetch['country_code'], '0777', true);
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