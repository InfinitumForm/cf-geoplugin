<?php
/**
 * SEO Redirection for Pages class
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

if (!class_exists('CFGP_SEO_Redirection_Pages')):
class CFGP_SEO_Redirection_Pages extends CFGP_Global
{
	private $metabox;
	
	public function __construct()
	{
		// Prevent redirection for the crawlers and bots
		if(CFGP_Options::get('redirect_disable_bots', 0) && CFGP_U::is_bot()){
			return;
		}
		// Prevent redirection using GET parametter
		if(isset($_GET['geo']) && ($_GET['geo'] === false || $_GET['geo'] === 'false')){
			return;
		}
		// Prevent using REQUEST
		if(isset($_REQUEST['stop_redirection']) && ($_REQUEST['stop_redirection'] === true || $_REQUEST['stop_redirection'] === 'true')){
			return;
		}
		// Stop on ajax
		if(wp_doing_ajax()){
			return;
		}
		// Stop if is admin
		if(is_admin()){
			return;
		}
		
		// Prevent by custom filter
		$API = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		$stop_redirection_filter = apply_filters('cfgp/seo/stop_redirection', false, $API);
		if( $stop_redirection_filter ){
			if(CFGP_U::recursive_array_search($stop_redirection_filter, $API, true)){
				return;
			}
		}
		$this->metabox=CFGP_Metabox::instance()->metabox;
		
		/**
		 * Fire WordPress redirecion ASAP
		 =======================================*/
	//	/* 01 */ $this->add_action( 'plugins_loaded',		'seo_redirection', 1);
	//	/* 02 */ $this->add_action( 'wp',					'seo_redirection', 1);
	//	/* 03 */ $this->add_action( 'send_headers',			'seo_redirection', 1);
	//	/* 04 */ $this->add_action( 'posts_selection',		'seo_redirection', 1); /* DANGER: Out of memory */
		/* 05 */ $this->add_action( 'template_redirect',	'seo_redirection', 1);
	}
	
	public function seo_redirection(){
		if(!is_admin()){
			
			// Stop if API have error
			if(CFGP_U::api('error')){
				return;
			}
			
			$current_page = CFGP_U::get_page();
			
			if(!$current_page) {
				return;
			}
			
			$enable_seo_posts = CFGP_Options::get('enable_seo_posts',array());
			if(empty($enable_seo_posts) || is_array($enable_seo_posts) && in_array(get_post_type($current_page), $enable_seo_posts) === false){
				return;
			}
			
			$seo_redirection = get_post_meta($current_page->ID, $this->metabox, true);
			
			$strtolower = (function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower');
			
			$current_country = array_filter(array(
				sanitize_title(CFGP_U::transliterate(CFGP_U::api('country'))),
				CFGP_U::transliterate(CFGP_U::api('country')),
				CFGP_U::api('country'),
				CFGP_U::api('country_code')
			));
			$current_country = array_map($strtolower, $current_country);
			
			$current_region = array_filter(array(
				sanitize_title(CFGP_U::transliterate(CFGP_U::api('region'))),
				CFGP_U::transliterate(CFGP_U::api('region')),
				CFGP_U::api('region'),
				CFGP_U::api('region_code')
			));
			$current_region = array_map($strtolower, $current_region);
			
			$current_city = array_filter(array(
				sanitize_title(CFGP_U::transliterate(CFGP_U::api('city'))),
				CFGP_U::transliterate(CFGP_U::api('city')),
				CFGP_U::api('city')
			));
			$current_city = array_map($strtolower, $current_city);
			
			$current_postcode = array_filter(array(CFGP_U::api('region_code')));
			$current_postcode = array_map($strtolower, $current_postcode);
			
			if($seo_redirection && is_array($seo_redirection))
			{
				foreach($seo_redirection as $data) {
					
					$cookie_name = apply_filters("cfgp/metabox/seo_redirection/only_once/cookie_name/{$current_page->ID}", '__cfgp_seo_' . md5(serialize($data)), $data, $current_page->ID);
					
					if(isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])){
						continue;
					}
					
					$url = (isset($data['url']) ? $data['url'] : '');
					
					if($url && self::current_url($url, true)){
						continue;
					}
					
					if((isset($data['active']) ? $data['active'] : 1) !== 1) {
						continue;
					}
					
					$country 	= array_map($strtolower, (isset($data['country']) ? $data['country'] : array()));
					$region 	= array_map($strtolower, (isset($data['region']) ? $data['region'] : array()));
					$city 		= array_map($strtolower, (isset($data['city']) ? $data['city'] : array()));
					$postcode 	= array_map($strtolower, (isset($data['postcode']) ? $data['postcode'] : array()));
					
					if(isset($data['exclude_country']) ? $data['exclude_country'] : false) {
						$country=array();
					}
					$country = array_map( array('CFGP_U', 'transliterate'), $country );
					
					if(isset($data['exclude_region']) ? $data['exclude_region'] : false) {
						$region=array();
					}
					$region = array_map( array('CFGP_U', 'transliterate'), $region );
					
					if(isset($data['exclude_city']) ? $data['exclude_city'] : false) {
						$city=array();
					}
					$city = array_map( array('CFGP_U', 'transliterate'), $city );
					
					if(isset($data['exclude_postcode']) ? $data['exclude_postcode'] : false) {
						$postcode=array();
					}
					
					$http_code 	= (isset($data['http_code']) ? $data['http_code'] : 302);
					
					$search_type = (isset($data['search_type']) ? $data['search_type'] : 'exact');
					
					// Let's check number of 
					
					// Search by values
					$redirect = array();
					
					if(!empty($current_country)) {
						foreach($country as $c){
							if(!empty($c) && in_array($c, $current_country) !== false){
								$redirect[] = 1;
								break;
							}
						}
					}
					
					if(!empty($current_region)) {
						foreach($region as $r){
							if(!empty($r) && in_array($r, $current_region) !== false){
								$redirect[] = 1;
								break;
							}
						}
					}
					
					if(!empty($current_city)) {
						foreach($city as $ct){
							if(!empty($ct) && in_array($ct, $current_city) !== false){
								$redirect[] = 1;
								break;
							}
						}
					}
					
					if(!empty($current_postcode)) {
						foreach($postcode as $pc){
							if(!empty($pc) && in_array($pc, $current_postcode) !== false){
								$redirect[] = 1;
								break;
							}
						}
					}
					
					$redirect = count($redirect);

					if( $redirect > 0 )
					{
						// Redirect only once
						if(isset($data['only_once']) ? $data['only_once'] : 0) {
							$expire = apply_filters('cfgp/metabox/seo_redirection_pages/only_once/cookie_expire', (YEAR_IN_SECONDS*2), CFGP_TIME);
							CFGP_U::setcookie ($cookie_name, (CFGP_TIME.'_'.$expire), $expire);
						}

						// Redirections
						CFGP_U::redirect( $url, $http_code );
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