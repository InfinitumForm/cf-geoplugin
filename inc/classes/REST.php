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
	
	private function __construct(){
		if(CFGP_License::level() > 4)
		{
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
			$this->add_action( 'wp_ajax_cf_geoplugin_authenticate', 'authenticate' );
			$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'authenticate' );
			
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
			$this->add_action( 'wp_ajax_cf_geoplugin_lookup', 'lookup' );
			$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup', 'lookup' );
		}
		else
		{
			$this->add_action( 'wp_ajax_cf_geoplugin_authenticate', 'license_error' );
			$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'license_error' );
			$this->add_action( 'wp_ajax_cf_geoplugin_lookup', 'license_error' );
			$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup', 'license_error' );
		}
		
		// AJAX Generate secret key
		$this->add_action( 'wp_ajax_cfgp_rest_generate_secret_key', 'generate_secret_key' );
		// AJAX Delete access token
		$this->add_action( 'wp_ajax_cfgp_rest_delete_access_token', 'delete_access_token' );
	}
	
	/*
	 *  Adding Important REST Endpoints
	 *  @version     1.0.0
	 *  @endpoint    json      /wp-json/cf-geoplugin/v1/return
	 */
	public static function rest_api_init_v1_return(){
		add_action( 'rest_api_init', function (){
			
			$namespace = 'cf-geoplugin/v1';
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
						return new WP_REST_Response(array(
							'response' => $value,
							'error' => CFGP_U::api('error'),
							'error_message' => CFGP_U::api('error_message'),
							'lookup' => CFGP_U::api('lookup'),
							'status' => CFGP_U::api('status'),
							'runtime' => CFGP_U::api('runtime')
						) );
					},
				), array(), true );
				
				$routes[] = home_url('/wp-json/cf-geoplugin/v1/return/'.$key);
				
			}
			
			// Return complete JSON response
			register_rest_route( $namespace, '/return', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( $data ) use ( $routes ) {
			
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
					
					if(empty($default)) {
						$return['response'] = do_shortcode('[' . $shortcode . $attr . ']');
					} else {
						$return['response'] =  do_shortcode('[' . $shortcode . $attr . ']' . $content . '[/' . $shortcode . ']');
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
			
			
			// Fix Shortcode cache
			register_rest_route( $namespace, '/cache/banner', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( $data )	{
					
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
					
					$exact = CFGP_U::request_int('exact');
					
					$posts_per_page = absint($setup['posts_per_page']);
				
					// Main query
					$query = array(
						'post_type'		=> 'cf-geoplugin-banner',
						'posts_per_page'	=>	$posts_per_page,
						'post_status'		=> 'publish',
						'post__in' => array($setup['id']),
						'force_no_results' => true,
						'meta_query' => array(),
						'tax_query' => array()
					);
					
					$country = CFGP_U::api('country_code');
					$region = CFGP_U::api('region');
					$city = CFGP_U::api('city');
					
					if($country){
						// Search by meta
						$query['meta_query'][]=array(
							'key' => 'cfgp-banner-location-country',
							'value' => '"'.strtolower($country).'"',
							'compare' => 'LIKE',
						);
						// Search by taxonomy
						$query['tax_query'][]=array(
							'taxonomy'	=> 'cf-geoplugin-country',
							'field'		=> 'slug',
							'terms'		=> array($country),
						);
					}
					
					if($region){
						// Search by meta
						$query['meta_query'][]=array(
							'key' => 'cfgp-banner-location-region',
							'value' => '"'.strtolower(sanitize_title($country)).'"',
							'compare' => 'LIKE',
						);
						// Search by taxonomy
						$query['tax_query'][]=array(
							'taxonomy'	=> 'cf-geoplugin-region',
							'field'		=> 'slug',
							'terms'		=> array($region),
						);
					}
					
					if($city){
						// Search by meta
						$query['meta_query'][]=array(
							'key' => 'cfgp-banner-location-city',
							'value' => '"'.strtolower(sanitize_title($city)).'"',
							'compare' => 'LIKE',
						);
						// Search by taxonomy
						$query['tax_query'][]=array(
							'taxonomy'	=> 'cf-geoplugin-city',
							'field'		=> 'slug',
							'terms'		=> array($city),
						);
					}
					
					// Relative or exact search
					if(!empty($query['meta_query'])){
						$query['meta_query']['relation'] = ($exact ? 'AND' : 'OR');
					}
					
					// Tax query
					if(!empty($query['tax_query'])){
						$query['tax_query']['relation'] = 'OR';
					}
					
					// Search by tax (DEPRECATED)
					$meta_query = $query['meta_query'];
					unset($query['meta_query']);
					
					$posts = get_posts( $query );
					
					// Search by term
					if(!$posts) {
						unset($query['tax_query']);
						$query['meta_query'] = $meta_query;
						$meta_query = NULL;
						$posts = get_posts( $query );
					}
					
					$content = '';
					$save = array();
					
					foreach($posts as $post) {
						$post_id = $post->ID;
						$post_content = $post->post_content;
						$post_content = do_shortcode($post_content);
						$post_content = apply_filters('the_content', $post_content);
						
						$save[]=$post_content;
					}
					
					$save = array_filter($save);
					
					// Return banner
					if(!empty($save)){
						$content = CFGP_U::fragment_caching(trim(join(PHP_EOL, $save)), true);
					}
					
					// Format defaults
					if(!empty($cont) && empty($content)) {
						$content = do_shortcode($cont);
						$content = apply_filters('the_content', $content);
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
	public function lookup(){
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
	public function authenticate(){
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
	
	// Bad license error
	public function license_error(){
		wp_send_json(array_merge(self::$return, array(
			'error_message' => __('You do not have the appropriate CF GeoPlugin license. REST API is allowed only for the BUSINESS LICENSE.', CFGP_NAME)
		)), 400);
	}
	
	// Generate REST Secret key
	public function generate_secret_key(){
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
	
	public function delete_access_token(){
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
	
	/*
	 * Get plugin license
	 *
	 * @pharam   (string)   $name                        If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default                     Default values
	 *
	 * @return   (array|string|int|bloat|bool)           plugin option/s
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