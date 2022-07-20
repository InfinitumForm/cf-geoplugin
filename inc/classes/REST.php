<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Generate Texteditor Shortcode Buttons
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 */
if(!class_exists('CFGP_REST')) :
class CFGP_REST extends CFGP_Global {

	private static $return = array(
		'error' => true,
		'code' => 400,
		'error_message' => 'Bad Request!'
	);
	
	/*
	 * Main REST namespace
	 */
	const NAMESPACE = 'cf-geoplugin';
	
	/*
	 * REST namespace version 1
	 */
	const NAMESPACE_V1 = 'cf-geoplugin/v1';
	
	/*
	 * Default API mode
	 */
	protected $default_api_mode = 'ajax';
	
	/*
	 * Main construct
	 */
	private function __construct(){
	
		if( CFGP_Options::get('rest_api_mode', $this->default_api_mode) === 'ajax' )
		{
			if(CFGP_License::level() > 4)
			{
				/*
				 * AJAX: Request access token
				 */
				$this->add_action( 'wp_ajax_cf_geoplugin_authenticate',        'ajax__authenticate' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'ajax__authenticate' );
				
				/*
				 * AJAX: Lookup IP address
				 */
				$this->add_action( 'wp_ajax_cf_geoplugin_lookup',        'ajax__lookup' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup', 'ajax__lookup' );
			}
			else
			{
				/*
				 * AJAX: License error
				 */
				$this->add_action( 'wp_ajax_cf_geoplugin_authenticate',        'ajax__license_error' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'ajax__license_error' );
				$this->add_action( 'wp_ajax_cf_geoplugin_lookup',              'ajax__license_error' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup',       'ajax__license_error' );
			}
		}
		else if( CFGP_Options::get('rest_api_mode', $this->default_api_mode) === 'rest' )
		{
			
		}
		
		/*
		 * AJAX: Generate secret key
		 */
		$this->add_action( 'wp_ajax_cfgp_rest_generate_secret_key', 'ajax__generate_secret_key' );
		
		/*
		 * AJAX: Delete access token
		 */
		$this->add_action( 'wp_ajax_cfgp_rest_delete_access_token', 'ajax__delete_access_token' );
	}
	
	#################################### REST VERSION ####################################
	
	/*
	 * Request access token
	 * https://somesite.com/wp-admin/admin-ajax.php?action=cf_geoplugin_authenticate
	 *
	 * Accept POST/GET:
	 * @pharam    string  action            -API action
	 * @pharam    string  api_key           -API KEY
	 * @pharam    string  secret_key        -API SECRET KEY
	 * @pharam    string  app_name          -Name of the external app
	 *
	 * JSON:
	 * @return    bool    error             -true/false
	 * @return    string  error_message     -Return only when error exists
	 * @return    int     code              -Allways exists
	 * @return    string  access_token      -Return only when authentication is successful
	 * @return    string  message           -Return only when authentication is successful
	 */
	public function rest__authenticate( WP_REST_Request $request ) {
		
	}
	
	################################## END REST VERSION ##################################

/*======================*/
	
	#################################### AJAX VERSION ####################################
	
	/*
	 * Request access token
	 * https://somesite.com/wp-admin/admin-ajax.php?action=cf_geoplugin_authenticate
	 *
	 * Accept POST/GET:
	 * @pharam    string  action            -API action
	 * @pharam    string  api_key           -API KEY
	 * @pharam    string  secret_key        -API SECRET KEY
	 * @pharam    string  app_name          -Name of the external app
	 *
	 * JSON:
	 * @return    bool    error             -true/false
	 * @return    string  error_message     -Return only when error exists
	 * @return    int     code              -Allways exists
	 * @return    string  access_token      -Return only when authentication is successful
	 * @return    string  message           -Return only when authentication is successful
	 */
	public function ajax__authenticate(){
		$allowed = array('api_key','secret_key','app_name');
		$GET = array();
		foreach($allowed as $field){
			if(isset($_REQUEST[$field])) {
				$GET[$field] = CFGP_U::request_string($field);
			}
		}
		$allowed = NULL;
		
		if(count($GET) === 3)
		{
			$api_key = get_option(CFGP_NAME . '-ID');
			$secret_key = CFGP_REST::get('secret_key');
			if($api_key == $GET['api_key'])
			{
				if($secret_key == $GET['secret_key'])
				{
					$app_name = sanitize_title($GET['app_name']);
					
					global $wpdb;
					$get_token = $wpdb->get_row($wpdb->prepare(
						"SELECT ID, token, lookup FROM {$wpdb->cfgp_rest_access_token} WHERE secret_key = %s AND app_name = %s",
						$secret_key,
						$app_name
					));
					
					if(isset($get_token->token)){
						$access_token = $get_token->token;
						$wpdb->update(
							$wpdb->cfgp_rest_access_token,
							array(
								'lookup' => (intval($get_token->lookup)+1),
							),
							array(
								'ID' => $get_token->ID
							),
							array(
								'%d'
							),
							array(
								'%d'
							)
						);
					} else {
						$access_token = CFGP_U::generate_token(mt_rand(mt_rand(10,20),32)) .'_'. CFGP_U::generate_token(mt_rand(10,32));
						$wpdb->insert(
							$wpdb->cfgp_rest_access_token,
							array(
								'secret_key' => $secret_key,
								'token' => $access_token,
								'app_name' => $app_name,
								'app_name_original' => $GET['app_name']
							),
							array(
								'%s',
								'%s',
								'%s',
								'%s'
							)
						);
					}
					
					wp_send_json(array_merge(self::$return, array(
						'error' => false,
						'error_message' => NULL,
						'access_token' => $access_token,
						'code' => 200
					)), 200);
				}
				else
				{
					wp_send_json(array_merge(self::$return, array(
						'error_message' => __('Secret key is invalid.', CFGP_NAME)
					)), 400);
				}
			}
			else
			{
				wp_send_json(array_merge(self::$return, array(
					'error_message' => __('API key is invalid.', CFGP_NAME)
				)), 400);
			}
		}
		else
		{
			wp_send_json(array_merge(self::$return, array(
				'error_message' => __('Required fields are not defined.', CFGP_NAME)
			)), 400);
		}
	}
	
	/*
	 * Lookup IP address
	 * https://somesite.com/wp-admin/admin-ajax.php?action=cf_geoplugin_lookup
	 *
	 * Accept POST/GET:
	 * @pharam    string  action            -API action
	 * @pharam    string  api_key           -API KEY
	 * @pharam    string  access_token      -API Access token
	 * @pharam    string  ip                -IP
	 * @pharam    string  base_currency     -Base currency
	 *
	 * JSON:
	 * @return    bool    error             -true/false
	 * @return    string  error_message     -Return only when error exists
	 * @return    int     code              -Allways exists
	 * @return    Geo informations
	 */
	public function ajax__lookup(){
		$allowed = array('api_key','access_token','ip','base_currency');
		
		$GET = array();
		foreach($allowed as $field){
			$GET[$field] = CFGP_U::request_string($field);
		}
		$allowed = NULL;
		
		$api_key = get_option(CFGP_NAME . '-ID');
		$secret_key = CFGP_REST::get('secret_key');
		
		if($api_key == $GET['api_key'])
		{
			global $wpdb;
			
			$confirm_token = $wpdb->get_row($wpdb->prepare(
				"SELECT ID, token, lookup FROM {$wpdb->cfgp_rest_access_token} WHERE secret_key = %s AND token = %s",
				$secret_key,
				$GET['access_token']
			));
			
			if(isset($confirm_token->ID))
			{
				$api = CFGP_API::lookup($GET['ip'], $GET);
				
				$wpdb->update(
					$wpdb->cfgp_rest_access_token,
					array(
						'lookup' => (intval($confirm_token->lookup)+1),
					),
					array(
						'ID' => $confirm_token->ID
					),
					array(
						'%d'
					),
					array(
						'%d'
					)
				);
				
				wp_send_json(array_merge($api, self::$return, array(
					'error' => false,
					'error_message' => NULL,
					'code' => 200
				)), 200);
			}
			else
			{
				wp_send_json(array_merge(self::$return, array(
					'error_message' => __('Access token is invalid.', CFGP_NAME)
				)), 400);
			}
		}
		else
		{
			wp_send_json(array_merge(self::$return, array(
				'error_message' => __('API key is invalid.', CFGP_NAME)
			)), 400);
		}
	}
	
	/*
	 * AJAX RESPONSE: Bad license error
	 */
	public function ajax__license_error(){
		wp_send_json(array_merge(self::$return, array(
			'error_message' => __('You do not have the appropriate CF GeoPlugin license. REST API is allowed only for the BUSINESS LICENSE.', CFGP_NAME)
		)), 400);
	}
	
	/*
	 * Generate REST Secret key
	 */
	public function ajax__generate_secret_key(){
		if(wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-secret-key') !== false)
		{
			$secret_key = CFGP_U::generate_token(5) .'_'. CFGP_U::generate_token(28) .'_'. CFGP_U::generate_token(8);
			self::set('secret_key', $secret_key);
			
			// Delete all access tokens
			global $wpdb;
			$wpdb->query($wpdb->prepare(
				"DELETE FROM {$wpdb->cfgp_rest_access_token} WHERE secret_key NOT LIKE %s",
				$secret_key
			));
			
			echo $secret_key;
		}
		else
		{
			echo 'ERROR!';
		}
		exit;
	}
	
	/*
	 * Delete access token from database
	 */
	public function ajax__delete_access_token(){
		if(wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-token-remove') !== false)
		{
			global $wpdb;
			$wpdb->query($wpdb->prepare(
				"DELETE FROM {$wpdb->cfgp_rest_access_token} WHERE ID = %d",
				CFGP_U::request_int('token_id')
			));
			echo 1;
		}
		else
		{
			echo -1;
		}
		exit;
	}
	
	################################## END AJAX VERSION ##################################
	
/*======================*/
	
	#################################### CACHE CONTROL ####################################
	
	/*
	 *  Adding Important internal REST Endpoints for AJAX calls
	 *
	 *  @version     1.0.0
	 *  @endpoint    json      /wp-json/{self::NAMESPACE_V1}/return
	 */
	public static function rest_api_init_v1_return(){
		add_action( 'rest_api_init', function (){
			
			$namespace = self::NAMESPACE_V1;
			$routes = array();
			
			// Return individual responses
			foreach(CFGP_U::api(false, CFGP_Defaults::API_RETURN) as $key => $value) {
				
				if( in_array(
					$key,
					array('error', 'error_message', 'lookup', 'status', 'runtime', 'zip', 'timezoneName')
				) ) {
					continue;
				}
				
				register_rest_route( $namespace, '/return/'.$key, array(
					'methods' => array('GET', 'POST'),
					'permission_callback' => '__return_true',
					'callback' => function( $data ) use ( $value ) {
						
						if( !isset($_REQUEST['key']) || (isset($_REQUEST['key']) && CFGP_U::KEY() !== $_REQUEST['key']) ) {
							return new WP_REST_Response(array(
								'error' => true,
								'code' => 'not_authorized',
								'error_message' => __('You are not authorized to access this information.', CFGP_NAME),
								'status' => 404
							));
						}
						
						return new WP_REST_Response(array(
							'response' => $value,
							'error' => CFGP_U::api('error'),
							'error_message' => CFGP_U::api('error_message'),
							'lookup' => CFGP_U::api('available_lookup'),
							'status' => CFGP_U::api('status'),
							'runtime' => CFGP_U::api('runtime')
						) );
					},
				), array(), true );
				
				$routes[] = home_url('/wp-json/' . self::NAMESPACE_V1 . '/return/'.$key);
				
			}
			
			// Return complete JSON response
			register_rest_route( $namespace, '/return', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( $data ) use ( $routes ) {
					
					if( !isset($_REQUEST['key']) || (isset($_REQUEST['key']) && CFGP_U::KEY() !== $_REQUEST['key']) ) {
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', CFGP_NAME),
							'status' => 404
						));
					}
			
					$callback = array_merge(
						CFGP_U::api(false, CFGP_Defaults::API_RETURN),
						array(
							'routes' => $routes
						)
					);
					
					foreach($callback as $key => $value) {
						if( in_array(
							$key,
							array('zip', 'timezoneName')
						) ) {
							unset($callback[$key]);
						}
					}
					
					return new WP_REST_Response($callback);
				},
			), array(), true );
			
			// Fix Shortcode cache
			register_rest_route( $namespace, '/cache/shortcode', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( $data )	{
					
					if( !isset($_REQUEST['key']) || (isset($_REQUEST['key']) && CFGP_U::KEY() !== $_REQUEST['key']) ) {
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', CFGP_NAME),
							'status' => 404
						));
					}
					
					$shortcode = trim(CFGP_U::request_string('shortcode'));
					
					if( empty($shortcode) || CFGP_U::request_string('action') != 'cf_geoplugin_shortcode_cache') {
						return new WP_REST_Response(
							array(
								'response' => NULL,
								'error' => true,
								'error_message' => __('Important parameters are missing!', CFGP_NAME),
								'status' => 404
							)
						);
					}
					
					$options = unserialize(urldecode(base64_decode(sanitize_text_field(CFGP_U::request_string('options')))));
					
					$attr = array();
					if(!empty($options) && is_array($options))
					{
						foreach($options as $key => $value) {
							if(!is_numeric($key)) {
								$attr[] = $key . '="' . esc_attr($value) . '"';
							} else {
								$attr[] = $value;
							}
						}
					}
					
					$attr = (!empty($attr) ? ' ' . join(' ', $attr) : '');
					
					if($default = CFGP_U::request_string('default')) {
						$content = urldecode(base64_decode(sanitize_text_field($default)));
						$content = trim($content);
						$default = $content;
					} else {
						$default = $content = '';
					}
					
					$attr = str_replace(' cache', '', $attr) . ' no_cache';
					
					$return = array();
					
					if( !in_array($shortcode, array(
						'cfgeo_flag',
						'cfgeo_converter',
						'cfgeo_is_vat',
						'cfgeo_is_not_vat',
						'cfgeo_in_eu',
						'cfgeo_not_in_eu',
						'cfgeo_is_proxy',
						'cfgeo_is_not_proxy',
						'cfgeo_gps',
						'cfgeo_map'
					)) && preg_match('/cfgeo_([a-z_]+)/i', $shortcode, $match) ) {
						$return['response'] = do_shortcode('[cfgeo return="' . $match[1] . '"' . $attr . ']');
					} else {
						if(empty($default)) {
							$return['response'] = do_shortcode('[' . $shortcode . $attr . ']');
						} else {
							$return['response'] =  do_shortcode('[' . $shortcode . $attr . ']' . $content . '[/' . $shortcode . ']');
						}
					}
					
					return new WP_REST_Response( array_merge(
						$return,
						array(
							'error' => false,
							'error_message' => '',
							'status' => 200
						)
					) );
				},
				
			), array(), true );
			
			
			// Fix Banner cache
			register_rest_route( $namespace, '/cache/banner', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( $data )	{
					
					if( !isset($_REQUEST['key']) || (isset($_REQUEST['key']) && CFGP_U::KEY() !== $_REQUEST['key']) ) {
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', CFGP_NAME),
							'status' => 404
						));
					}
					
					// Stop on the bad request
					if( CFGP_U::request_string('action') != 'cf_geoplugin_banner_cache' ) {
						return new WP_REST_Response(
							array(
								'response' => NULL,
								'error' => true,
								'error_message' => __('Important parameters are missing!', CFGP_NAME),
								'status' => 404
							)
						);
					}
					
					$return=array(
						'response' => NULL
					);
					
					$setup = array(
						'id'				=>	CFGP_U::request_int('id'),
						'posts_per_page'	=>	CFGP_U::request_int('posts_per_page'),
						'class'				=>	sanitize_text_field(CFGP_U::request_string('class'))
					);
					
					$cont = urldecode(base64_decode(sanitize_text_field(CFGP_U::request_string('default'))));
					
					// Stop if ID is not good
					if( ! (intval($setup['id']) > 0) ) {
						$return['response'] = $cont;
						return new WP_REST_Response( array_merge(
							$return,
							array(
								'error' => false,
								'error_message' => '',
								'status' => 200
							)
						) );
					}
					
					// Reassign taxonomy to post meta
					foreach(array(
						'cf-geoplugin-country' => 'cfgp-banner-location-country',
						'cf-geoplugin-region' => 'cfgp-banner-location-region',
						'cf-geoplugin-city' => 'cfgp-banner-location-city'
					) as $get_post_terms=>$update_post_meta) {
						if($all_terms = wp_get_post_terms($setup['id'], $get_post_terms, array('fields' => 'all'))) {
							$tax_collection=array();
							foreach($all_terms as $i=>$fetch)
							{
								$tax_collection[]=$fetch->slug;
							}
							if( !empty($tax_collection) ) {
								update_post_meta($setup['id'], $update_post_meta, $tax_collection);
							} else {
								delete_post_meta($setup['id'], $update_post_meta);
							}
							wp_set_post_terms( $setup['id'], '', $get_post_terms );
							$tax_collection = NULL;
						}
					}
					
					$exact = CFGP_U::request_int('exact');
					
					$posts_per_page = absint($setup['posts_per_page']);
				
					global $wpdb;
					
					$country = CFGP_U::api('country_code');
					$country_sql = '%"' . $wpdb->esc_like(esc_sql($country)) . '"%';
					
					$region = CFGP_U::api('region');
					$region_sql = '%"' . $wpdb->esc_like(esc_sql(sanitize_title( CFGP_U::transliterate($region) ))) . '"%';
					
					$city = CFGP_U::api('city');
					$city_sql = '%"' . $wpdb->esc_like(esc_sql(sanitize_title( CFGP_U::transliterate($city) ))) . '"%';
					
					$post = $wpdb->get_row( $wpdb->prepare("
						SELECT
							`banner`.`ID`,
							`banner`.`post_title`,
							`banner`.`post_content`
						FROM
							`{$wpdb->posts}` AS `banner`
						WHERE
							`banner`.`ID` = %d
						AND
							`banner`.`post_type` = 'cf-geoplugin-banner'
						AND
							`post_status` = 'publish'
						AND
							IF(
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country'),
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `c` WHERE `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country' AND `c`.`meta_value` LIKE %s),
								1
							)
						AND
							IF(
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region'),
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `r` WHERE `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region' AND `r`.`meta_value` LIKE %s),
								1
							)
						AND
							IF(
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city'),
								EXISTS(SELECT 1 FROM `{$wpdb->postmeta}` `s` WHERE `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city' AND `s`.`meta_value` LIKE %s),
								1
							)
						LIMIT 1
					",
					absint($setup['id']),
					$country_sql,
					$region_sql,
					$city_sql
					) );
					
					$content = '';
					$save = NULL;
					
					if($post) {
						$post->post_content = do_shortcode($post->post_content);
						$post->post_content = CFGP_U::the_content($post->post_content);
						$save=$post->post_content;
					}
					
					// Return banner
					if(!empty($save)){
						$content = CFGP_U::fragment_caching($save, false);
					}
					
					// Format defaults
					if(!empty($cont) && empty($content)) {
						$content = do_shortcode($cont);
						$content = CFGP_U::the_content($content);
					}
					
					$return['response'] = $content;
					
					return new WP_REST_Response( array_merge(
						$return,
						array(
							'error' => false,
							'error_message' => '',
							'status' => 200
						)
					) );
				},
				
			), array(), true );
		} );
	}
	
	################################## END CACHE CONTROL ##################################
	
/*======================*/	
	
	##################################### ASSETS AREA #####################################
	
	/*
	 * Get plugin license
	 *
	 * @pharam   (string)   $name          If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default       Default values
	 *
	 * @return   (mixed)                   plugin option/s
	 */
	public static function get($name = false, $default = NULL)
	{
		// Get cache
		$get_option = CFGP_Cache::get('rest');
		
		// If cache is empty, get from the database
		if( !$get_option ){
			$get_option = CFGP_Cache::set(
				'rest',
				wp_parse_args(
					( CFGP_NETWORK_ADMIN ? get_site_option( CFGP_NAME . '-rest' ) : get_option( CFGP_NAME . '-rest' ) ),
					CFGP_Defaults::REST
				)
			);
		}
		
		// Search and return
		if($get_option) {
			if( $name === false ){
				return apply_filters( 'cfgp/rest/get', $get_option, $default);
			} else {
				if(isset($get_option[$name])) {
					// Return values
					return apply_filters( 'cfgp/rest/get', ((!empty($get_option[$name]) || $get_option[$name] === 0) ? $get_option[$name] : $default), $default);
				}
			}
		}
		
		// Show default
		return apply_filters( 'cfgp/rest/get', $default, $default);
	}
	
	/*
	 * Set plugin license
	 *
	 * @pharam   (string|array)   $name_or_array       array of option name and values or just single option name
	 * @pharam   (string)         $value               if single option name is set, this is the value
	 *
	 * @return   (array)                               plugin options
	 */
	public static function set($name_or_array=array(), $value=NULL)
	{
		
		// Get plugin options
		$options = self::get();
		
		// Get default keys
		$filter = apply_filters('cfgp/rest/set/filter', array_keys(CFGP_Defaults::REST));
		
		// Collect and set new values
		if(!empty($name_or_array))
		{
			if(is_array($name_or_array))
			{				
				foreach($name_or_array as $key => $val) {
					if(in_array($key, $filter) !== false) {
						$options[$key] = CFGP_Options::sanitize($val);
					}
				}
			}
			else if(!is_numeric($name_or_array) && is_string($name_or_array))
			{
				if(in_array($name_or_array, $filter) !== false) {
					$options[$name_or_array] = CFGP_Options::sanitize($value);
				}
			}
		}
		
		// Return on the bad data
		if(empty($options)) return false;
		
		// Save new options
		if(CFGP_NETWORK_ADMIN){
			update_site_option(CFGP_NAME . '-rest', $options, false);
		}else{
			update_option(CFGP_NAME . '-rest', $options, false);
		}
		
		// Save to cache
		CFGP_Cache::set('rest', $options);
		
		return apply_filters( 'cfgp/rest/set', $options, CFGP_Defaults::REST, $name_or_array, $value);
	}
	
	/**
	 * Sanitize string or array
	 * This functionality do automatization for the certain type of data expected in this plugin
	 *
	 * @pharam   (string|array)   $str
	 *
	 * @return   (string|array)   sanitized options
	 */
	public static function sanitize( $str ){
		if( is_array($str) )
		{
			$data = array();
			foreach($str as $key => $obj)
			{
				$data[$key]=CFGP_Options::sanitize( $obj ); 
			}
			return $data;
		}
		else
		{			
			if(is_numeric($str))
			{
				if(intval( $str ) == $str)
					$str = intval( $str );
				else if(floatval($str) == $str)
					$str = floatval( $str );
				else
					$str = sanitize_text_field( $str );
			}
			else if(is_bool($str))
			{
				$str = $str ? true : false;
			}
			else if(!is_bool($str) && in_array(strtolower($str), array('true','false'), true))
			{
				$str = ( strtolower($str) == 'true' );
			}
			else
			{
				$str = html_entity_decode($str);
				if(preg_match('/<\/?[a-z][\s\S]*>/i', $str))
				{
					$str = wp_kses($str, wp_kses_allowed_html('post'));
				} else {
					$str = sanitize_text_field( $str );
				}
			}
		}
		
		return $str;
	}
	
	
	/*
	 * Check is database table exists
	 * @verson    1.0.0
	*/
	public static function table_exists($dry = false) {
		static $cache = NULL;
		global $wpdb;
		
		if(NULL === $cache || $dry) {
			if($wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->cfgp_rest_access_token}'" ) != $wpdb->cfgp_rest_access_token) {
				if( $dry ) {
					return false;
				}
				
			//	error_log(sprintf(__('The database table "%s" not exists! You can try to reactivate the WordPress Geo Plugin to correct this error.', CFGP_NAME), $wpdb->cfgp_seo_redirection));
				
				$cache = false;
			} else {
				if( $dry ) {
					return true;
				}
				
				$cache = true;
			}
		}
		
		return $cache;
	}
	
	/*
	 * Install missing tables
	 * @verson    1.0.0
	*/
	public static function table_install() {
		if( !self::table_exists(true) ) {
			global $wpdb;
			
			// Include important library
			if(!function_exists('dbDelta')){
				require_once ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php';
			}
			
			// Install table
			$charset_collate = $wpdb->get_charset_collate();
			dbDelta("
			CREATE TABLE IF NOT EXISTS {$wpdb->cfgp_rest_access_token} (
				ID bigint(20) NOT NULL AUTO_INCREMENT,
				`secret_key` varchar(45) NOT NULL,
				`token` varchar(65) NOT NULL,
				`app_name` varchar(255) NOT NULL,
				`app_name_original` varchar(255) NOT NULL,
				`date_created` timestamp NOT NULL DEFAULT current_timestamp(),
				`active` int(1) NOT NULL DEFAULT 1,
				`lookup` bigint(32) NOT NULL DEFAULT 1,
				PRIMARY KEY (ID),
				UNIQUE KEY `token` (`token`),
				UNIQUE KEY `app_name` (`app_name`),
				KEY `secret_key` (`secret_key`)
			) {$charset_collate}
			");
		}
	}
	
	################################### END ASSETS AREA ###################################	
		
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