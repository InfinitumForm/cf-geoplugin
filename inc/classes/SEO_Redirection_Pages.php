<?php
/**
 * SEO Redirection for Pages class
 *
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
		
		$this->metabox=CFGP_Metabox::instance()->metabox;
		
		/**
		 * Fire WordPress redirecion ASAP
		 =======================================*/
		/* 01 */ $this->add_action( 'plugins_loaded',		'seo_redirection', 1);
		/* 02 */ $this->add_action( 'wp',					'seo_redirection', 1);
		/* 03 */ $this->add_action( 'send_headers',			'seo_redirection', 1);
		/* 04 */ $this->add_action( 'posts_selection',		'seo_redirection', 1);
		/* 05 */ $this->add_action( 'template_redirect',	'seo_redirection', 1);
	}
	
	public function seo_redirection(){
		if(!is_admin()){
			
			$current_page = CFGP_U::get_page();
			
			if(!$current_page) {
				return;
			}
			
			$cookie_name = apply_filters("cfgp/metabox/seo_redirection/only_once/cookie_name/{$current_page->ID}", '__cfgp_seo_' . md5($current_page->ID . '_once'), $current_page->ID);
			
			if(isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])){
				return;
			}
			
			$enable_seo_posts = CFGP_Options::get('enable_seo_posts',array());
			if(empty($enable_seo_posts) || is_array($enable_seo_posts) && in_array(get_post_type($current_page), $enable_seo_posts) === false){
				return;
			}
			
			$seo_redirection = get_post_meta($current_page->ID, $this->metabox, true);
			
			$current_country = array_filter(array(CFGP_U::api('country'), CFGP_U::api('country_code')));
			$current_country = array_map('strtolower', $current_country);
			
			$current_region = array_filter(array(CFGP_U::api('region'), CFGP_U::api('region_code')));
			$current_region = array_map('strtolower', $current_region);
			
			$current_city = strtolower(CFGP_U::api('city'));
			$current_postcode = strtolower(CFGP_U::api('region_code'));
			
			foreach($seo_redirection as $data) {
				
				if((isset($data['active']) ? $data['active'] : 1) !== 1) {
					continue;
				}
				
				if(isset($data['exclude_country']) ? $data['exclude_country'] : false) {
					$data['country']=array();
				}
				if(isset($data['exclude_region']) ? $data['exclude_region'] : false) {
					$data['region']=array();
				}
				if(isset($data['exclude_city']) ? $data['exclude_city'] : false) {
					$data['city']=array();
				}
				if(isset($data['exclude_postcode']) ? $data['exclude_postcode'] : false) {
					$data['postcode']=array();
				}
				
				$country 	= array_map('strtolower', isset($data['country']) ? $data['country'] : array());
				$region 	= array_map('strtolower', isset($data['region']) ? $data['region'] : array());
				$city 		= array_map('strtolower', isset($data['city']) ? $data['city'] : array());
				$postcode 	= array_map('strtolower', isset($data['postcode']) ? $data['postcode'] : array());
				
				$url 		= (isset($data['url']) ? $data['url'] : '');
				$http_code 	= (isset($data['http_code']) ? $data['http_code'] : 302);
				
				$search_type = (isset($data['search_type']) ? $data['search_type'] : 'exact');
				
				// Let's check number of 
				
				// Search by values
				$redirect = array();
				
				if(empty($current_country)) {
					foreach($country as $c){
						if(!empty($r) && in_array($c, $current_country) !== false){
							$redirect[] = 1;
							break;
						}
					}
				}
				
				if(empty($current_region)) {
					foreach($region as $r){
						if(!empty($r) && in_array($r, $current_region) !== false){
							$redirect[] = 1;
							break;
						}
					}
				}
				
				if(!empty($current_city) && !empty($city) && in_array($current_city, $city) !== false){
					$redirect[] = 1;
				}
				
				if(!empty($current_postcode) && !empty($postcode) && in_array($current_postcode, $postcode) !== false){
					$redirect[] = 1;
				}
				
				if($search_type == 'exact') {
					//-EXACT MATCH
					
					
				} else {
					//-RELATIVE MATCH
				}
				
				/*
				if(isset($data['only_once']) ? $data['only_once'] : 0) {	
					$expire = apply_filters('cfgp/metabox/seo_redirection/only_once/cookie_expire', (YEAR_IN_SECONDS*2), CFGP_TIME);
					CFGP_U::setcookie ($cookie_name, (CFGP_TIME.'_'.$expire), $expire);
				}
				*/
				
			}
			
		//	echo '<pre>', var_dump($seo_redirection), '</pre>';
		}
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