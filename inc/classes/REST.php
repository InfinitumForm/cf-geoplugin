<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Generate Texteditor Shortcode Buttons
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 */
if(!class_exists('CFGP_REST', false)) : class CFGP_REST extends CFGP_Global {

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
	 * https://somesite.com/wp-json/cf-geoplugin/v1/authenticate
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
	public function rest__authenticate ( WP_REST_Request $request ) {
		
	}
	
	/*
	 * Lookup IP address
	 * https://somesite.com/wp-json/cf-geoplugin/v1/lookup
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
	public function rest__lookup ( WP_REST_Request $request ) {
		
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
		$GET = [];
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
						'error_message' => __('Secret key is invalid.', 'cf-geoplugin')
					)), 400);
				}
			}
			else
			{
				wp_send_json(array_merge(self::$return, array(
					'error_message' => __('API key is invalid.', 'cf-geoplugin')
				)), 400);
			}
		}
		else
		{
			wp_send_json(array_merge(self::$return, array(
				'error_message' => __('Required fields are not defined.', 'cf-geoplugin')
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
		
		$GET = [];
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
					'error_message' => __('Access token is invalid.', 'cf-geoplugin')
				)), 400);
			}
		}
		else
		{
			wp_send_json(array_merge(self::$return, array(
				'error_message' => __('API key is invalid.', 'cf-geoplugin')
			)), 400);
		}
	}
	
	/*
	 * AJAX RESPONSE: Bad license error
	 */
	public function ajax__license_error(){
		wp_send_json(array_merge(self::$return, array(
			'error_message' => __('You do not have the appropriate CF GeoPlugin license. REST API is allowed only for the BUSINESS LICENSE.', 'cf-geoplugin')
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
			
			echo esc_html($secret_key);
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
			
			// Return complete JSON response
			register_rest_route( $namespace, '/return', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( WP_REST_Request $data ) {
					
					if( CFGP_U::KEY() !== $data->get_param( 'key' ) ) {
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
							'status' => 404
						));
					}
			
					$callback = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
					
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
				'args' => array(
					'key' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					)
				)
			), [], true );
			
			// Return individual responses			
			register_rest_route( $namespace, '/return/(?P<pharam>[\w\d\-_]+)', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( WP_REST_Request $data ) {
					
					if( CFGP_U::KEY() !== $data->get_param( 'key' ) ) {
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
						'key' => CFGP_U::KEY(),
							'status' => 404
						));
					}
					
					return new WP_REST_Response(array(
						'response' => CFGP_U::api( $data->get_param( 'pharam' ) ?? '' ),
						'error' => CFGP_U::api('error'),
						'error_message' => CFGP_U::api('error_message'),
						'lookup' => CFGP_U::api('available_lookup'),
						'status' => CFGP_U::api('status'),
						'runtime' => CFGP_U::api('runtime')
					) );
				},
				'args' => array(
					'name' => array(
						'validate_callback' => function($param, $request, $key) {
							return (preg_match( '/[a-z0-9\-_]+/', $param ) !== false);
						},
						'pharam' => true
					),
					'key' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					)
				)
			), [], true );
			
			// Fix Shortcode cache
			register_rest_route( $namespace, '/cache/shortcode', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( WP_REST_Request $data ) {
					
					header('Cache-Control: max-age=900, must-revalidate');

					$transient_id = sanitize_text_field($data->get_param('nonce'));
					$type = sanitize_text_field($data->get_param('type'));
					
					// Stop on the bad request
					if( CFGP_U::request_string('action') != 'cf_geoplugin_shortcode_cache' ) {
						header_remove('Cache-Control');
						return new WP_REST_Response(
							array(
								'response' => NULL,
								'error' => true,
								'error_message' => __('You are not authorized to access this information!', 'cf-geoplugin'),
								'status' => 403
							)
						);
					}

					// Check if the transient exists
					if ($data = get_transient('cfgp-' . $transient_id)) {
						$content   = wp_kses_post($data['content']);
						$default   = wp_kses_post($data['default']);
						$shortcode = sanitize_text_field($data['shortcode']);
						$options   = $data['options']; // sanitization is below
						$post_id   = sanitize_text_field($data['post_id']);
						$hash      = sanitize_text_field($data['hash']);
						$key       = sanitize_text_field($data['key']);
			
						// Secret Key do not match
						if( CFGP_U::CACHE_KEY() !== $key ) {
							delete_transient('cfgp-' . $transient_id);
							header_remove('Cache-Control');
							return new WP_REST_Response(array(
								'error' => true,
								'code' => 'not_authorized',
								'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
								'status' => 403
							));
						}

						// If shortcode and transient do not match
						if ($hash !== $transient_id || $shortcode !== $type) {
							delete_transient('cfgp-' . $transient_id);
							header_remove('Cache-Control');
							return new WP_REST_Response(array(
								'response' => null,
								'error' => true,
								'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
								'status' => 400
							));
						}

						// Build shortcode attributes and sanitize them
						$attr = [];
						if (!empty($options) && is_array($options)) {
							foreach ($options as $key => $value) {
								$value = sanitize_text_field($value);
								if (!is_numeric($key)) {
									$attr[] = sanitize_key($key) . '="' . esc_attr($value) . '"';
								} else {
									$attr[] = esc_attr($value);
								}
							}
						}
						
						$attr = (!empty($attr) ? ' ' . join(' ', $attr) : '');
						$attr = str_replace(' cache', '', $attr) . ' no_cache';

						// Build a new shortcode with geo data
						$output = $content;
						
						// For the flags
						if ($shortcode === 'cfgeo_flag') {
							$shortcode_str = '[cfgeo_flag' . $attr . ']';
						}
						// For the advanced shortcode
						elseif (preg_match('/cfgeo_([a-z_]+)/i', $shortcode, $match)) {
							$shortcode_str = '[cfgeo return="' . esc_attr($match[1]) . '"' . $attr . ']';
						}
						// For the standard shortcodes
						else {
							$shortcode_str = empty($default)
								? '[' . $shortcode . $attr . ']'
								: '[' . $shortcode . $attr . ']' . $content . '[/' . $shortcode . ']';
						}

						// Apply wp_kses to the processed shortcode string
						$output = wp_kses(
							do_shortcode($shortcode_str),
							CFGP_U::allowed_html_tags_for_page()
						);

						return new WP_REST_Response(array(
							'response' => $output,
							'error' => false,
							'error_message' => '',
							'status' => 200
						));
					}
					// If the transient does not exist, return an error
					header_remove('Cache-Control');
					return new WP_REST_Response(array(
						'response' => null,
						'error' => true,
						'error_message' => __('No cached content found.', 'cf-geoplugin'),
						'status' => 400
					));
				},
				'args' => array(
					'nonce' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					),
					'type' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					),
					'action' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					)
				)
			));

			
			
			// Fix Banner cache
			register_rest_route( $namespace, '/cache/banner', array(
				'methods' => array('GET', 'POST'),
				'permission_callback' => '__return_true',
				'callback' => function( WP_REST_Request $data )	{
					
					header('Cache-Control: max-age=900, must-revalidate');
					
					// Stop on the bad request
					if( CFGP_U::request_string('action') != 'cf_geoplugin_banner_cache' ) {
						header_remove('Cache-Control');
						return new WP_REST_Response(
							array(
								'response' => NULL,
								'error' => true,
								'error_message' => __('You are not authorized to access this information!', 'cf-geoplugin'),
								'status' => 403
							)
						);
					}
					
					// Stop if transient not exists
					$transient_id = CFGP_U::request_string('nonce');
					$data = get_transient('cfgp-' . $transient_id);
					if( !$data ) {
						header_remove('Cache-Control');
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
							'status' => 403
						));
					}
					
					// Stop if hidden key not exists
					if( sanitize_text_field($data['key']) !== CFGP_U::CACHE_KEY() ) {
						delete_transient('cfgp-' . $transient_id);
						header_remove('Cache-Control');
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
							'status' => 400
						));
					}
					
					// Let's keep proper transient
					if( sanitize_text_field($data['hash']) !== $transient_id ) {
						delete_transient('cfgp-' . $transient_id);
						header_remove('Cache-Control');
						return new WP_REST_Response(array(
							'error' => true,
							'code' => 'not_authorized',
							'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
							'status' => 400
						));
					}
					
					
					$return=array(
						'response' => NULL
					);
					
					$setup = array(
						'id'				=>	absint(sanitize_text_field($data['id'])),
						'posts_per_page'	=>	absint(sanitize_text_field($data['posts_per_page'])),
						'class'				=>	sanitize_text_field($data['class'])
					);
					
					$cont = sanitize_textarea_field($data['content']);
					
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
							$tax_collection=[];
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
						LEFT JOIN
							`{$wpdb->postmeta}` AS `c` ON `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country'
						LEFT JOIN
							`{$wpdb->postmeta}` AS `r` ON `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region'
						LEFT JOIN
							`{$wpdb->postmeta}` AS `s` ON `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city'
						WHERE
							`banner`.`ID` = %d
							AND `banner`.`post_type` = 'cf-geoplugin-banner'
							AND `post_status` = 'publish'
							AND (`c`.`meta_value` LIKE %s OR `c`.`meta_value` IS NULL)
							AND (`r`.`meta_value` LIKE %s OR `r`.`meta_value` IS NULL)
							AND (`s`.`meta_value` LIKE %s OR `s`.`meta_value` IS NULL)
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
				'args' => array(
					'nonce' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					),
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					),
					'action' => array(
						'validate_callback' => function($param, $request, $key) {
							return !empty($param);
						},
						'required' => true
					)
				)
			), [], true );
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
	public static function set($name_or_array=[], $value=NULL)
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
			$data = [];
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
					$str = wp_kses(($str ?? ''), wp_kses_allowed_html('post'));
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
				
			//	error_log(sprintf(__('The database table "%s" not exists! You can try to reactivate the Geo Controller to correct this error.', 'cf-geoplugin'), $wpdb->cfgp_seo_redirection));
				
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