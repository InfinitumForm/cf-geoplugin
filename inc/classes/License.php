<?php
/**
 * License control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.1
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_License')) :
class CFGP_License extends CFGP_Global{
	
	private static $activated = NULL;
	
	/*
	 * License names
	 */
	public static function name($sku=false){
		$license_names = array(
			CFGP_Defaults::BASIC_LICENSE			=> __('UNLIMITED Basic License (1 month)', 'cf-geoplugin'),
			CFGP_Defaults::PERSONAL_LICENSE			=> __('UNLIMITED Personal License (1 year)', 'cf-geoplugin'),
			CFGP_Defaults::PERSONAL_LICENSE_4Y		=> __('UNLIMITED Personal License (4 years)', 'cf-geoplugin'),
			CFGP_Defaults::FREELANCER_LICENSE		=> __('UNLIMITED Freelancer License (1 year)', 'cf-geoplugin'),
			CFGP_Defaults::FREELANCER_LICENSE_4Y	=> __('UNLIMITED Freelancer License (4 years)', 'cf-geoplugin'),
			CFGP_Defaults::BUSINESS_LICENSE			=> __('UNLIMITED Business License (1 year)', 'cf-geoplugin'),
			CFGP_Defaults::BUSINESS_LICENSE_4Y		=> __('UNLIMITED Business License (4 years)', 'cf-geoplugin'),
			CFGP_Defaults::LIFETIME_LICENSE			=> __('UNLIMITED Lifetime License', 'cf-geoplugin'),
		);
		
		if( CFGP_DEV_MODE )
		{
			$license_names[CFGP_Defaults::DEVELOPER_LICENSE] = __('UNLIMITED Developer License', 'cf-geoplugin');
		}
		
		$license_names = apply_filters('cfgp/license/names', $license_names);
		
		if($sku)
		{
			if(isset($license_names[$sku])) {
				return apply_filters('cfgp/license/name', $license_names[$sku]);
			} else {
				return false;
			}
		}

		return $license_names;
	}
	
	/*
	 * Get product informations
	 */
	public static function get_product_data(){
		$response = CFGP_DB_Cache::get('cfgp-get-product-data');
		if(!$response) {
			if($return = CFGP_U::curl_get(CFGP_STORE . '/wp-admin/admin-ajax.php?action=cfgp_get_product_data', '', [], false))
			{
				if($return['error'] === false && $return['lenght'] > 0){
					$response = $return['products'];
					
					if( !empty($response) ) {
						$reorder = [];
						foreach(array(
							CFGP_Defaults::BASIC_LICENSE,
							CFGP_Defaults::PERSONAL_LICENSE,
							CFGP_Defaults::PERSONAL_LICENSE_4Y,
							CFGP_Defaults::FREELANCER_LICENSE,
							CFGP_Defaults::FREELANCER_LICENSE_4Y,
							CFGP_Defaults::BUSINESS_LICENSE,
							CFGP_Defaults::BUSINESS_LICENSE_4Y,
							CFGP_Defaults::LIFETIME_LICENSE,
							CFGP_Defaults::DEVELOPER_LICENSE
						) as $sku) {
							foreach($response as $product){
								if( $product['sku'] === $sku ) {
									$reorder[]=$product;
									break;
								}
							}
						}
						
						if( !empty($reorder) ){
							$response = $reorder;
						}
					}
					
					CFGP_DB_Cache::set('cfgp-get-product-data', $response, DAY_IN_SECONDS);
				}
			}
		}
		
		if(empty($response)) {
			$response = self::get_static_product_data();
		}
		
		// Developer License
		if( CFGP_DEV_MODE )
		{
			$response = array_merge($response, array(
			array(
				'id' => 1030,
				'title' => 'Developer License',
				'slug' => 'developer-license',
				'sku' => 'CFGEODEV',
				'url' => 'https://github.com/CreativForm/wordpress-geoplugin',
				'price' => array(
					'amount' => 1500,
					'regular' => 1500,
					'sale' => 0,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
				
			)));
		}

		return empty($response) ? [] : $response;
	}
	
	
	/*
	 * Access level
	*/
	public static function level($level = 0){		
		$return = 0;
		
		if($level==0){
			$level=self::get();
		}
		
		$levels=array_flip(array(
			0		=> 0,
			1		=> CFGP_Defaults::BASIC_LICENSE,
			2		=> CFGP_Defaults::PERSONAL_LICENSE,
			3		=> CFGP_Defaults::PERSONAL_LICENSE_4Y,
			4		=> CFGP_Defaults::FREELANCER_LICENSE,
			5		=> CFGP_Defaults::FREELANCER_LICENSE_4Y,
			6		=> CFGP_Defaults::BUSINESS_LICENSE,
			7		=> CFGP_Defaults::BUSINESS_LICENSE_4Y,
			1000	=> CFGP_Defaults::LIFETIME_LICENSE,
			3000	=> CFGP_Defaults::DEVELOPER_LICENSE
		));
		
		if( CFGP_U::api('available_lookup') === 'lifetime' ) {
			return 1000;
		}
		
		if(is_array($level))
		{
			if($level['status'])
			{
				if(isset($levels[$level['sku']])) {
					$return = $levels[$level['sku']];
				}
			}
		}
		else
		{			
			if(isset($levels[$level])) {
				$return = $levels[$level];
			}
		}
		
		return $return;
	}
	
	/*
	 * Check if plugin is licensed
	 *
	 * @return  true/false
	 * 
	 +======================================================================================+
	 | NOTICE FOR HACKERS AND DEVELOPERS:                                                   |
	 | ----------------------------------                                                   |
	 | If you try to hack this function to activate the hidden functionality of the plugin, |
	 | you can try but the lookup will definitely remain limited.                           |
	 | Our server knows if you have activated the license or not, so there will be a lot of |
	 | problems when trying to force activation.                                            |
	 |                                                                                      |
	 | The license for this plugin is not expensive in general.                             |
	 | Otherwise, if you need a job, let me know.                                           |
	 |                                                                                      |
	 | I am planning to develop a special protection WordPress plugin that will bring a lot |
	 | of work, and for that I need developers who also know how to hack.                   |
	 |                                                                                      |
	 | Because only a hacker can write good protection against hackers. ;)                  |
	 |                                                                                      |
	 | I'm expecting you.                                                                   |
	 |                                                                                      |
	 | Cheers!                                                                              |
	 |                                                                                      |
	 | Contact: https://wpgeocontroller.com/get-involved                                        |
	 +======================================================================================+
	 */
	public static function activated(){
		
		if(self::$activated !== NULL){
			return self::$activated;
		}
		
		if( CFGP_Defaults::LIFETIME_LICENSE === self::get('sku') ) {
			self::$activated = true;
			return self::$activated;
		}
		
		if( CFGP_U::api('available_lookup') === 'lifetime' ) {
			self::$activated = true;
			return self::$activated;
		}
		
		if( self::expired() ) {
			self::$activated = false;
			return self::$activated;
		}
		
		foreach(array_keys(CFGP_Defaults::LICENSE) as $license_field){
			if($license_field != 'expired' && empty(self::get($license_field))){
				self::$activated = false;
				return self::$activated;
			}
		}
		
		self::$activated = true;
		return self::$activated;
	}
	
	/*
	 * Is license expired
	 * @return  true/false
	 */
	public static function expired(){
		static $expired = NULL;
		
		if(NULL === $expired) {
			if( CFGP_Defaults::LIFETIME_LICENSE === self::get('sku') ) {
				$expired = false;
			} else {
				$expired = ((int)self::expire_date('YmdH') < (int)date('YmdH'));
			}
		}
		
		return $expired;
	}
	
	/*
	 * Return expire date in proper format with fix for the 32bit PHP
	 */
	public static function expire_date($format = ''){
		static $expire_date = NULL;
		
		if(empty($format)){
			$format = get_option('date_format');
		}
		
		if(isset($expire_date[$format])) {
			return $expire_date[$format];
		}
		
		$generate_date = function($format){
			$ex_date = false;
			if(self::get('expire') > 0) {
				if((int)self::get('expire') < 0){
					$date = new DateTime(self::get('expire_date'));
					$ex_date = $date->format($format);
				} else {
					$ex_date = date($format, (int)self::get('expire'));
				}
			}
			return $ex_date;
		};
		
		if(NULL === $expire_date) {
			$expire_date = [];
		}
		
		$expire_date[$format] = $generate_date($format);
		
		return $expire_date[$format];
	}
	
	/*
	 * Print API response errors notice
	 */
	public static function print_response_errors(){
		$response = CFGP_DB_Cache::get('cfgp-license-response-errors');

		if($response) 
		{
			$error_label = array(
				'license_key' 	=> __('License Key', 'cf-geoplugin'),
				'activation_id' => __('Activation ID', 'cf-geoplugin'),
				'domain' 		=> __('Domain', 'cf-geoplugin'),
				'sku' 			=> __('SKU', 'cf-geoplugin'),
				'store_code'	=> __('Store Code', 'cf-geoplugin'),
				'no_connection'	=> __('No Connection', 'cf-geoplugin'),
				'api_error'		=> __('API Error', 'cf-geoplugin'),
				'input_field'	=> __('Input Field', 'cf-geoplugin')
			);
			ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE); ?>
			
			<?php foreach($response as $key => $obj): ?>
                <?php if(is_numeric($key)) : $code = self::response_error_code($key); ?>
                    <h3><?php _e('Licensing Error', 'cf-geoplugin'); ?>: <?php echo wp_kses_post($code['message'] ?? ''); ?></h3>
                    <p><?php echo esc_html( $code['info'] ); ?></p>
                <?php else: ?>
                	<h3><?php _e('Licensing Error', 'cf-geoplugin'); ?>: <?php echo wp_kses_post($error_label[$key] ?? ''); ?></h3>
                    <ol>
                    <?php foreach($obj as $message): ?>
                        <li><?php echo wp_kses_post( $message ?? '' ); ?></li>
                    <?php endforeach; ?>
                    </ol>
                    <?php if($key == 'license_key'): ?>
                        <p><?php _e('You must enter a valid license key in order to continue with licensing your plugin installation.', 'cf-geoplugin');?></p>
                        <p><?php _e('Second reason why this may happen can be that you must choose a valid "License Type". If you purchase a "Personal License" and get a license key, you must enter that license key and choose license type to validate your key. If the key does not match your type you are not able to finish activation.', 'cf-geoplugin');?></p>
                    <?php elseif($key == 'sku'): ?>
                        <p><?php _e('One of the reasons why this may happen can be that you must choose a valid "License Type". If you purchase a "Personal License" and get a license key, you must enter that license key and choose license type to validate your key. If the key does not match your type you are not able to finish activation.', 'cf-geoplugin');?></p>
                    <?php endif; ?>
                <?php endif; ?>
			<?php endforeach; ?>
			<?php
			CFGP_DB_Cache::delete('cfgp-license-response-errors');
			
			$notice = '';
			if (ob_get_level()) {
				$notice = ob_get_contents();
				ob_end_clean();
			}
			
			printf('<div class="notice notice-error is-dismissible">%s</div>', $notice);
		}
		
		return;
	}
	
	/*
	 * Print API response success notice
	 */
	public static function print_response_success(){
		$response = CFGP_DB_Cache::get('cfgp-license-response-success');

		if($response) 
		{
			ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE); ?>
            <h3><?php _e('Activation succeeded', 'cf-geoplugin'); ?></h3>
            <p><?php echo wp_kses_post( $response ?? '' ); ?></p>
			<?php
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			
			$notice = '';
			if (ob_get_level()) {
				$notice = ob_get_contents();
				ob_end_clean();
			}
			
			printf('<div class="notice notice-success is-dismissible">%s</div>', $notice);
		}
		
		return;
	}
	
	/*
	 * Activate plugin
	 */
	public static function activate($license_key, $sku){
		
		if(empty($license_key) || empty($sku)) {
			$input_field_error = [];
			
			if(empty($sku)) {
				$input_field_error = array_merge($input_field_error, array(
					__('You have not selected your license.', 'cf-geoplugin'))
				);
			}
			if(empty($license_key)) {
				$input_field_error = array_merge($input_field_error, array(
					__('License key field is empty.', 'cf-geoplugin'))
				);	
			}
			$input_field_error = array_merge($input_field_error, array(
				__('Make sure you select the license you purchased and enter the correct license key.', 'cf-geoplugin'))
			);
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			CFGP_DB_Cache::set('cfgp-license-response-errors', array(
				'input_field' => $input_field_error
			), DAY_IN_SECONDS);
			return;
		}
		
		$request_pharams = array(
			'license_key' => $license_key,
			'sku' => $sku,
			'action' => 'license_key_activate',
		//	'store_code' => CFGP_STORE_CODE,
			'domain' => CFGP_U::get_host(true)
		);
		
		$request_url = CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'authenticate'] . '?' . http_build_query(
			$request_pharams,
			'',
			(ini_get('arg_separator.output') ?? '&amp;'),
			PHP_QUERY_RFC3986
		);

		$response = CFGP_U::curl_get( $request_url );

		if(empty($response)){
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			CFGP_DB_Cache::set('cfgp-license-response-errors', array(
				'no_connection' => array(
					__('Unable to connect to server.', 'cf-geoplugin')
				)
			), DAY_IN_SECONDS);
			return false;
		}
		
		if( isset( $response['error'] ) && $response['error'] == true )
		{
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			
			if(isset($response['errors_raw']) && !empty($response['errors_raw'])){
				$response['errors'] = $response['errors_raw'];
			}
			
			CFGP_DB_Cache::set(
				'cfgp-license-response-errors',
				(
					isset($response['errors']) ? $response['errors'] : array(
						'api_error' => array_merge(array(
							$response['message']
						), array(
							__('Make sure you select the license you purchased and enter the correct license key.', 'cf-geoplugin')
						))
					)
				),
				DAY_IN_SECONDS
			);
			
			return false;
		}
		else
		{
			// Clear errors if exists
			CFGP_DB_Cache::delete('cfgp-license-response-errors');
			// Update license
			$update = array(
				'key' => $response['the_key'],
				'id' => $response['activation_id'],
				'expire' => $response['expire'],
				'expire_date' => $response['expire_date'],
				'url' => $response['url'],
				'sku' => $response['sku'],
				'expired' => $response['has_expired'],
				'status' => ($response['status'] == 'active')
			);
			
			self::set($update);
			CFGP_DB_Cache::set('cfgp-license-response-success', $response['message'], DAY_IN_SECONDS);
			// Clear special API cache
			CFGP_API::remove_cache();
			CFGP_DB_Cache::flush();
			return true;
		}
	}
	
	/*
	 * Deactivate plugin
	 */
	public static function deactivate(){
			
		$post_data = array(
			'license_key' => self::get('key'),
			'activation_id' => self::get('id'),
			'sku' => self::get('sku'),
			'action' => 'license_key_deactivate',
			'store_code' => CFGP_STORE_CODE,
			'domain' => CFGP_U::get_host(true)
		);
		
		$request_url = CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'authenticate'] . '?' . http_build_query(
			$post_data,
			'',
			(ini_get('arg_separator.output') ?? '&amp;'),
			PHP_QUERY_RFC3986
		);
		
		$response = CFGP_U::curl_get( $request_url );
	/*
		if(empty($response)){
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			CFGP_DB_Cache::set('cfgp-license-response-errors', array(
				'no_connection' => array(
					__('Unable to connect to server.', 'cf-geoplugin')
				)
			), DAY_IN_SECONDS);
			return false;
		}
	*/
		/**
		if( isset( $response['error'] ) && $response['error'] == true )
		{
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			CFGP_DB_Cache::set('cfgp-license-response-errors', (isset($response['errors']) ? $response['errors'] : array(
				'api_error' => array(
					$response['message']
				)
			)), DAY_IN_SECONDS);
			return false;
		}
		else
		{
			// Clear responses if exists
			CFGP_DB_Cache::delete('cfgp-license-response-errors');
			CFGP_DB_Cache::delete('cfgp-license-response-success');
			// Clear license
			self::set(CFGP_Defaults::LICENSE);
			CFGP_DB_Cache::set('cfgp-license-response-success', $response['message'], DAY_IN_SECONDS);
			// Clear special API cache
			CFGP_API::remove_cache();
			return true;
		}
		**/
		
		/*
		 * OK, we have some problems with some websites.
		 *
		 * In that case, we will force deactivation until we find 
		 * the perfect solution to the problem we are facing.
		 */
		
		// Clear responses if exists
		CFGP_DB_Cache::delete('cfgp-license-response-errors');
		CFGP_DB_Cache::delete('cfgp-license-response-success');
		// Clear license
		self::set(CFGP_Defaults::LICENSE);
		CFGP_DB_Cache::set('cfgp-license-response-success', __('License successfully deactivated!', 'cf-geoplugin'), DAY_IN_SECONDS);
		// Clear special API cache
		CFGP_API::remove_cache();
		CFGP_DB_Cache::flush();
		return true;
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
		$get_option = CFGP_Cache::get('license');
		
		// If cache is empty, get from the database
		if( !$get_option ){
			$get_option = CFGP_Cache::set(
				'license',
				wp_parse_args(
					(
						CFGP_NETWORK_ADMIN 
						? get_site_option( CFGP_NAME . '-license' ) 
						: get_option( CFGP_NAME . '-license' )
					),
					CFGP_Defaults::LICENSE
				)
			);
		}
		
		// Search and return
		if($get_option) {
			if( $name === false ){
				return apply_filters( 'cfgp/license/get', $get_option, $default);
			} else {
				if(isset($get_option[$name])) {
					// Return values
					return apply_filters(
						'cfgp/license/get',
						(
							(!empty($get_option[$name]) || $get_option[$name] === 0) 
							? $get_option[$name] 
							: $default
						),
						$default
					);
				}
			}
		}
		
		// Show default
		return apply_filters( 'cfgp/license/get', $default, $default);
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
		$filter = apply_filters('cfgp/license/set/filter', array_keys(CFGP_Defaults::LICENSE));
		
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
			update_site_option(CFGP_NAME . '-license', $options, true);
		}else{
			update_option(CFGP_NAME . '-license', $options, true);
		}
		
		// Save to cache
		CFGP_Cache::set('license', $options);
		
		return apply_filters( 'cfgp/license/set', $options, CFGP_Defaults::LICENSE, $name_or_array, $value);
	}
	
	/*
	 * Response code
	 */
	private static function response_error_code($key){
		// Error codes
		$error_code = array(
			1 => array(
				'message' => __('Invalid code', 'cf-geoplugin'),
				'info' => __('Store code provided do not match the one set for the API.', 'cf-geoplugin'),
			),
			2 => array(
				'message' => __('Invalid license key', 'cf-geoplugin'),
				'info' => __('License key provided do not match the license key string format established by the API. (regex validation).', 'cf-geoplugin'),
			),
			3 => array(
				'message' => __('Invalid license key', 'cf-geoplugin'),
				'info' => __('SKU provided do not match the product SKU associated with the license key.', 'cf-geoplugin'),
			),
			4 => array(
				'message' => __('Invalid license key', 'cf-geoplugin'),
				'info' => __('License key provided do not match the license key string format established by the API. (missing dash validation).', 'cf-geoplugin'),
			),
			5 => array(
				'message' => __('Invalid license key', 'cf-geoplugin'),
				'info' => __('License key provided was not found in the database.', 'cf-geoplugin'),
			),
			100 => array(
				'message' => __('Required', 'cf-geoplugin'),
				'info' => __('No SKU was provided in the request.', 'cf-geoplugin'),
			),
			101 => array(
				'message' => __('Required', 'cf-geoplugin'),
				'info' => __('No license key code was provided in the request.', 'cf-geoplugin'),
			),
			102 => array(
				'message' => __('Required', 'cf-geoplugin'),
				'info' => __('No store code was provided in the request.', 'cf-geoplugin'),
			),
			103 => array(
				'message' => __('Required', 'cf-geoplugin'),
				'info' => __('No activation ID was provided in the request.', 'cf-geoplugin'),
			),
			104 => array(
				'message' => __('Required', 'cf-geoplugin'),
				'info' => __('No domain was provided in the request.', 'cf-geoplugin'),
			),
			200 => array(
				'message' => __('License key has expired', 'cf-geoplugin'),
				'info' => __('License key provided has expired.', 'cf-geoplugin'),
			),
			201 => array(
				'message' => __('License key activation limit reached. Deactivate one of the registered activations to proceed', 'cf-geoplugin'),
				'info' => __('License key provided has reached the limit of activations allowed.', 'cf-geoplugin'),
			),
			202 => array(
				'message' => __('License key domain activation limit reached. Deactivate one or more of the registered activations to proceed', 'cf-geoplugin'),
				'info' => __('License key provided has reached the domain limit allowed.', 'cf-geoplugin'),
			),
			203 => array(
				'message' => __('Invalid activation', 'cf-geoplugin'),
				'info' => __('Activation ID provided was not found.', 'cf-geoplugin'),
			),
		);
		
		// Find message by array key (index)
		if(isset($error_code[$key])) {
			return $error_code[$key];
		}
		
		// Not found
		return array(
			'message' => __('Undefined error', 'cf-geoplugin'),
			'info' => __('This error is not defined and you need to contact the author of the plugin for more information.', 'cf-geoplugin'),
		);
	}
	
	/*
	 * Static product data if main website fail to send informations
	 */
	public static function get_static_product_data () {
		return array(
			732 => array(
				'id' => 732,
				'title' => 'Geolocation Basic Services',
				'slug' => 'cf-geo-plugin-basic-license',
				'sku' => 'CFGEO1M',
				'url' => 'https://wpgeocontroller.com/license/cf-geo-plugin-basic-license',
				'price' => array(
					'amount' => 15,
					'regular' => 20,
					'sale' => 15,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
			),
			586 => array(
				'id' => 586,
				'title' => 'Geolocation Personal Services',
				'slug' => 'cf-geo-plugin-unlimited-personal-license',
				'sku' => 'CFGEOSWL',
				'url' => 'https://wpgeocontroller.com/license/cf-geo-plugin-unlimited-personal-license',
				'price' => array(
					'amount' => 62,
					'regular' => 89,
					'sale' => 62,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
			),
			589 => array(
				'id' => 589,
				'title' => 'Geolocation Freelancer Services',
				'slug' => 'cf-geo-plugin-unlimited-freelancer-license',
				'sku' => 'CFGEO3WL',
				'url' => 'https://wpgeocontroller.com/license/cf-geo-plugin-unlimited-freelancer-license',
				'price' => array(
					'amount' => 82,
					'regular' => 132,
					'sale' => 82,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
			),
			590 => array(
				'id' => 590,
				'title' => 'Geolocation Business Services',
				'slug' => 'cf-geo-plugin-unlimited-business-license',
				'sku' => 'CFGEODWL',
				'url' => 'https://wpgeocontroller.com/license/cf-geo-plugin-unlimited-business-license',
				'price' => array(
					'amount' => 349,
					'regular' => 499,
					'sale' => 349,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
			),
			2933 => array(
				'id' => 2933,
				'title' => 'Geolocation Lifetime Services',
				'slug' => 'geolocation-lifetime-services',
				'sku' => 'LIFETIME',
				'url' => 'https://wpgeocontroller.com/license/geolocation-lifetime-services',
				'price' => array(
					'amount' => 1499,
					'regular' => 1499,
					'sale' => 0,
					'date' => array(
						'sale_from' => NULL,
						'sale_to' => NULL
					),
					'currency' => 'USD',
					'currency_symbol' => '&#36;'
				)
			)
		);
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