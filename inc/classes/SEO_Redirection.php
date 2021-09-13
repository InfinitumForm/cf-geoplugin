<?php
/**
 * Main SEO Redirection class
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

if (!class_exists('CFGP_SEO_Redirection')):
class CFGP_SEO_Redirection extends CFGP_Global
{
	public function __construct()
	{
		// Stop on ajax
		if(wp_doing_ajax()){
			return;
		}
		// Stop if is admin
		if(is_admin()){
			return;
		}
		// Prevent redirection using GET parametter
		if(isset($_GET['geo']) && ($_GET['geo'] === false || $_GET['geo'] === 'false')){
			return;
		}
		if(isset($_REQUEST['stop_redirection']) && ($_REQUEST['stop_redirection'] === true || $_REQUEST['stop_redirection'] === 'true')){
			return;
		}
		/**
		 * Fire WordPress redirecion ASAP
		 =======================================*/
	//	/* 01 */ $this->add_action( 'muplugins_loaded',		'seo_redirection', 1);
	//	/* 02 */ $this->add_action( 'plugins_loaded',		'seo_redirection', 1);
	//	/* 03 */ $this->add_action( 'send_headers',			'seo_redirection', (PHP_INT_MAX-1));
		/* 01 */ $this->add_action( 'template_redirect',	'seo_redirection', 1);
	}
	
	/*
	 * Redirection for the enthire website
	 */
	public function seo_redirection(){
		global $wpdb;
		
		// Stop if API have error
		if(CFGP_U::api('error')){
			return;
		}
		
		$country = CFGP_U::api('country');
		$country_code = CFGP_U::api('country_code');
		
		if( $country || $country_code )
		{
			$region = CFGP_U::api('region');
			$region_code = CFGP_U::api('region_code');
			
			$city = CFGP_U::api('city');
			$postcode = CFGP_U::api('region_code');
			
			$where = $where_relative = array();
			
			if($country || $country_code)
			{
				if($country) $where[]=$wpdb->prepare('TRIM(LOWER(country)) = %s', strtolower($country));
				if($country_code) $where[]=$wpdb->prepare('TRIM(LOWER(country)) = %s', strtolower($country_code));
			}
			
			if($region || $region_code)
			{
				if($region){
					$where[]=$wpdb->prepare('TRIM(LOWER(region)) = %s', strtolower($region));
					$where_relative[]=$wpdb->prepare('TRIM(region) = %s', sanitize_title($region));
				}
				if($region_code) $where[]=$wpdb->prepare('TRIM(LOWER(region)) = %s', strtolower($region_code));
			}
			
			if($city){
				$where[]=$wpdb->prepare('TRIM(LOWER(city)) = %s', strtolower($city));
				$where_relative[]=$wpdb->prepare('TRIM(city) = %s', sanitize_title($city));
			}
			
			if($postcode) $where[]=$wpdb->prepare('TRIM(LOWER(postcode)) = %s', strtolower($postcode));
			
			if(!empty($where)) {
				$where_exact = ' AND (' . join(' AND ', $where) . ')';
				$where_relative = ' AND (' . join(' OR ', $where) . (!empty($where_relative) ? ' OR ' . join(' OR ', $where_relative) : '' ) . ')';
			} else {
				$where_exact = '';
				$where_relative = '';
			}
			
			$table = $wpdb->get_blog_prefix() . CFGP_Defaults::TABLE['seo_redirection'];
			$fields = "
				TRIM(url) AS url,
				TRIM(LOWER(country)) AS country,
				TRIM(LOWER(region)) AS region,
				TRIM(LOWER(city)) AS city,
				TRIM(LOWER(postcode)) AS postcode,
				http_code AS http_code,
				only_once
			";
			
			$exact_redirects = $wpdb->get_results("SELECT {$fields} FROM {$table} WHERE active = 1{$where_exact}", ARRAY_A );
			
			if( !empty($exact_redirects) )
			{
				foreach( $exact_redirects as $redirect )
				{
					$this->do_redirection( $redirect );
				}
			}
			else
			{
				$relative_redirects = $wpdb->get_results("SELECT {$fields} FROM {$table} WHERE active = 1{$where_relative}", ARRAY_A );
				if( !empty($relative_redirects) )
				{
					foreach( $relative_redirects as $redirect )
					{
						$this->do_redirection( $redirect );
					}
				}
			}
		}
	}
	
	/*
	 * Get current URL or match current URL
	 */
	private static function current_url ($url = NULL, $avoid_protocol = false) {
		$get_url = CFGP_U::get_url();
		
		if( $avoid_protocol )
		{
			if(!empty($url)) {
				$url = preg_replace('/(https?\:\/\/)/i', '', $url);
			}
			$get_url = preg_replace('/(https?\:\/\/)/i', '', $get_url);
		}

		if(empty($url)) {
			return $get_url;
		} else {
			$url = rtrim($url, '/');
			$get_url = rtrim($get_url, '/');
			
			if(strtolower($url) == strtolower($get_url)) {
				return $url;
			}
		}
		
		return false;
	}

	/*
	 * Redirection for the enthire website
	 */
	private function do_redirection($redirect){
		$country_check = $this->check_user_by_country( $redirect['country'] );

		$country_empty = false;
		$region_empty = false;
		$city_empty = false;
		$postcode_empty = false;
		
		if( $this->is_object_empty($redirect, 'country') ) $country_empty = true;
		if( $this->is_object_empty($redirect, 'region') ) $region_empty = true;
		if( $this->is_object_empty($redirect, 'city') ) $city_empty = true;
		if( $this->is_object_empty($redirect, 'postcode') ) $postcode_empty = true;

		if( isset( $redirect['url'] ) && filter_var($redirect['url'], FILTER_VALIDATE_URL) && ( $country_check || $country_empty ) )
		{			
			if( !$postcode_empty && $this->check_user_by_postcode( $redirect['postcode'] ) )
			{
				if($this->control_redirection( $redirect )) CFGP_U::redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( $postcode_empty && $this->check_user_by_city( $redirect['city'] ) && ( $this->check_user_by_region( $redirect['region'] ) || $region_empty ) )
			{
				if($this->control_redirection( $redirect )) CFGP_U::redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( $city_empty && $this->check_user_by_region( $redirect['region'] ) ) 
			{
				if($this->control_redirection( $redirect )) CFGP_U::redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( $region_empty && $city_empty && $country_check && $postcode_empty ) 
			{
				if($this->control_redirection( $redirect )) CFGP_U::redirect( $redirect['url'], $redirect['http_code'] );
			}
		}
	}
	
	/*
	 * Redirection control
	 */
	private function control_redirection( $redirect )
	{	
		// Forbid infinity loop
		if(self::current_url( $redirect['url'], true ))
		{
			return false;
		}
	
		// Redirect only once
		if(isset( $redirect['only_once'] ) && $redirect['only_once'] == 1)
		{
			
			if(isset($redirect['page_id']) && isset($redirect['ID']) && !empty($redirect['page_id'])) {
				$cookie_name = apply_filters(
					'cfgp/seo/control_redirection/cookie/page/',
					'__cfgp_seo_' . md5($redirect['page_id'] . '_once_' . $redirect['ID']),
					$redirect['page_id'],
					$redirect['ID']
				);
			} else {
				$cookie_name = apply_filters(
					'cfgp/seo/control_redirection/cookie/name',
					'__cfgp_seo_' . md5($redirect['url']),
					$redirect['url']
				);
			}
			
			$expire = apply_filters(
				'cfgp/seo/control_redirection/cookie/expire',
				(YEAR_IN_SECONDS*2),
				time()
			);
			
			if(isset($redirect['page_id']) && isset($redirect['ID']) && !empty($redirect['page_id'])) {
				$expire = apply_filters(
					'cfgp/seo/control_redirection/cookie/expire/page/' . $redirect['page_id'],
					$expire,
					CFGP_TIME
				);
			}
			
			if(isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])){
				return false;
			} else {
				CFGP_U::setcookie($cookie_name, (CFGP_TIME.'_'.$expire), $expire);
			}
		}
		
		return true;
	}
	
	/*
	 * Test is object empty or not
	 */
	private function is_object_empty($obj,$name){
		return ( ( isset( $obj[$name][0] ) && empty( $obj[$name][0] ) ) || ( empty( $obj[$name] ) ) );
	}
	
	
	/**
	 * Check user's postcode for defender or seo redirection
	 */
	private function check_user_by_postcode( $postcode )
	{
		if( is_array( $postcode ) )
		{
			$postcode = array_map( 'strtolower', $postcode );
			if( isset( $postcode[0] ) && !empty( $postcode[0] ) && in_array( sanitize_title( CFGP_U::api('postcode')), $postcode, true ) ) return true;
		}
		elseif( is_string( $postcode ) )
		{
			if( !empty( $postcode ) && strtolower( $postcode ) === strtolower(sanitize_title(CFGP_U::api('postcode'))) ) return true;
		}

		return false;
	}


	/**
	 * Check user's city for defender or seo redirection
	 */
	private function check_user_by_city( $city )
	{
		if( is_array( $city ) )
		{
			$city = array_map( 'strtolower', $city );
			if( isset( $city[0] ) && !empty( $city[0] ) && in_array( sanitize_title(CFGP_U::api('city')), $city, true ) ) return true;
		}
		elseif( is_string( $city ) )
		{
			if( !empty( $city ) && strtolower( $city ) === strtolower(sanitize_title(CFGP_U::api('city'))) ) return true;
		}

		return false;
	}

	/**
	 * Check user's region for defender or seo redirection
	 */
	private function check_user_by_region( $region )
	{
		if( is_array( $region ) )
		{
			if( isset( $region[0] ) && !empty( $region[0] ) )
			{
				$region = array_map( 'strtolower', $region );
				// Supports region code and region name
				if(in_array( strtolower( CFGP_U::api('region_code') ), $region, true ) ) return true; 
				if(in_array( sanitize_title(CFGP_U::api('region')), $region, true ) ) return true;
			}
		}
		elseif( is_string( $region ) )
		{
			if( !empty( $region ) )
			{
				// Supports region code and region name
				if( strtolower( $region ) === strtolower(CFGP_U::api('region_code')) ) return true; 
				if( strtolower( $region ) === strtolower(sanitize_title(CFGP_U::api('region'))) ) return true;
			}
		}

		return false;
	}

	/**
	 * Check user's country for defender or seo redirection
	 */
	private function check_user_by_country( $country )
	{
		if( is_array( $country ) )
		{
			if( isset( $country[0] ) && !empty( $country[0] ) )
			{
				$country = array_map( 'strtolower', $country );
				// Supports country code and name
				if( in_array( strtolower(CFGP_U::api('country_code')), $country, true ) ) return true;
				if( in_array( sanitize_title(CFGP_U::api('country')), $country, true ) ) return true;
			}
		}
		elseif( is_string( $country ) )
		{
			if( !empty( $country ) )
			{
				// Supports country code and name
				if( strtolower( $country ) === strtolower(CFGP_U::api('country_code')) ) return true;
				if( strtolower( $country ) === strtolower(sanitize_title(CFGP_U::api('country'))) ) return true;
			}
		}

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