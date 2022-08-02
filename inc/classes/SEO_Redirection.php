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
	private $seo_redirection_cache = NULL;
	
	public function __construct()
	{
		// Is database table not exists
		if( !CFGP_SEO_Table::table_exists() ) {
			return;
		}
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
		
		/**
		 * Fire WordPress redirecion ASAP
		 *
		 * Here we have a couple of options to consider.
		 * This is a list of actions that can serve:
		 *
		 * 01 $this->add_action( 'muplugins_loaded',	'seo_redirection', 1);
		 * 02 $this->add_action( 'plugins_loaded',		'seo_redirection', 1);
		 * 03 $this->add_action( 'send_headers',		'seo_redirection', 1);
		 * 04 $this->add_action( 'template_redirect',	'seo_redirection', 1);
		 */
		
		switch( CFGP_Options::get('redirect_mode', 2) ) {
			default:
			case 1:
				$this->add_action( 'template_redirect',	'seo_redirection', 1);
				break;
				
			case 2:
				$this->add_action( 'send_headers',	'seo_redirection', 1);
				$this->add_action( 'template_redirect',	'seo_redirection', 1);
				break;
				
			case 3:
				$this->add_action( 'wp',	'seo_redirection', 1);
				$this->add_action( 'send_headers',	'seo_redirection', 1);
				$this->add_action( 'template_redirect',	'seo_redirection', 1);
				break;
		}
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
		
		$relative_redirects = $exact_redirects = NULL;
		
		if( empty($this->seo_redirection_cache) )
		{
		
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
					$where[]=$wpdb->prepare(
						"TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`country`)) IN( %s, %s, %s, %s )",
						CFGP_U::strtolower($country_code),
						sanitize_title(CFGP_U::transliterate($country)),
						CFGP_U::strtolower(CFGP_U::transliterate($country)),
						CFGP_U::strtolower($country)
					);
					
				}
				
				if($region || $region_code)
				{
					$where[]=$wpdb->prepare(
						"TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`region`)) IN( %s, %s, %s, %s )",
						sanitize_title(CFGP_U::transliterate($region)),
						CFGP_U::strtolower(CFGP_U::transliterate($region)),
						CFGP_U::strtolower($region),
						CFGP_U::strtolower($region_code)
					);
				}
				
				if($city){
					$where[]=$wpdb->prepare(
						"TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`city`)) IN( %s, %s, %s )",
						sanitize_title(CFGP_U::transliterate($city)),
						CFGP_U::strtolower(CFGP_U::transliterate($city)),
						CFGP_U::strtolower($city)
					);
				}
				
				if($postcode) {
					$where[]=$wpdb->prepare("TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`postcode`)) = %s", strtolower($postcode));
				}
				
				if(!empty($where)) {
					$where_exact = ' AND (' . join(' AND ', $where) . ')';
					$where_relative = ' AND (' . join(' OR ', $where) . ')';
				} else {
					$where_exact = $where_relative = '';
				}
				
				$fields = "
					TRIM(`{$wpdb->cfgp_seo_redirection}`.`url`) AS `url`,
					TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`country`)) AS `country`,
					TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`region`)) AS `region`,
					TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`city`)) AS `city`,
					TRIM(LOWER(`{$wpdb->cfgp_seo_redirection}`.`postcode`)) AS `postcode`,
					`{$wpdb->cfgp_seo_redirection}`.`http_code` AS `http_code`,
					`{$wpdb->cfgp_seo_redirection}`.`only_once` AS `only_once`
				";
				
				$query = apply_filters(
					'cfgp/seo/redirection/query/exact',
					"SELECT {$fields} FROM `{$wpdb->cfgp_seo_redirection}` WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 1{$where_exact}"
				);
				$exact_redirects = $wpdb->get_results($query, ARRAY_A );
				
				if( empty($exact_redirects) ) {
					$query = apply_filters(
						'cfgp/seo/redirection/query/relative',
						"SELECT {$fields} FROM `{$wpdb->cfgp_seo_redirection}` WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 1{$where_relative}"
					);
					$relative_redirects = $wpdb->get_results($query, ARRAY_A );
				}
				
				$this->seo_redirection_cache = array(
					'relative_redirects' => $relative_redirects,
					'exact_redirects' => $exact_redirects
				);
			}
		} else {
			$relative_redirects = ($this->seo_redirection_cache['relative_redirects'] ?? NULL);
			$exact_redirects = ($this->seo_redirection_cache['exact_redirects'] ?? NULL);
		}
		
		
		if( !empty($exact_redirects) ) {
			foreach( $exact_redirects as $redirect )
			{
				if( $this->do_redirection( $redirect ) ) {
					exit;
				}
			}
		} else if( !empty($relative_redirects) ) {
			foreach( $relative_redirects as $redirect )
			{
				if( $this->do_redirection( $redirect ) ) {
					exit;
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
		// Do redirection
		$do_redirection = false;
		// Generate redirection mode
		$mode = array( NULL, 'country', 'region', 'city', 'postcode' );
		$mode = $mode[ count( array_filter( array_map(
			function($obj) {
				return !empty($obj);
			},
			array(
				$redirect['country'],
				$redirect['region'],
				$redirect['city'],
				$redirect['postcode']
			)
		) ) ) ];
		// Switch mode
		switch ( $mode ) {
			case 'country':
				if( CFGP_U::check_user_by_country($redirect['country']) ) {
					$do_redirection = true;
				}
				break;
			case 'region':
				if(
					CFGP_U::check_user_by_region($redirect['regions']) 
					&& CFGP_U::check_user_by_country($redirect['country']) 
				) {
					$do_redirection = true;
				}
				break;
			case 'city':
				if( 
					CFGP_U::check_user_by_city($redirect['city']) 
					&& CFGP_U::check_user_by_region($redirect['region']) 
					&& CFGP_U::check_user_by_country($redirect['country']) 
				) {
					$do_redirection = true;
				}
				break;
			case 'postcode':
				if( 
					CFGP_U::check_user_by_city($redirect['city']) 
					&& CFGP_U::check_user_by_region($redirect['region']) 
					&& CFGP_U::check_user_by_country($redirect['country'])
					&& CFGP_U::check_user_by_postcode($redirect['postcode']) 
				) {
					$do_redirection = true;
				}
				break;
		}
		// Let's redirect
		if( $do_redirection && $this->control_redirection( $redirect )) {
			return CFGP_U::redirect( $redirect['url'], $redirect['http_code'] );
		}
		// End
		return false;
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
				CFGP_TIME
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