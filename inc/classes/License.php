<?php
/**
 * License control
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

if(!class_exists('CFGP_License')) :
class CFGP_License extends CFGP_Global{
	
	/*
	 * License names
	 */
	public static function name($sku=false){
		$license_names = array(
			CFGP_Defaults::BASIC_LICENSE			=> __('UNLIMITED Basic License (1 month)',CFGP_NAME),
			CFGP_Defaults::PERSONAL_LICENSE			=> __('UNLIMITED Personal License (1 year)',CFGP_NAME),
			CFGP_Defaults::PERSONAL_LICENSE_4Y		=> __('UNLIMITED Personal License (4 years)',CFGP_NAME),
			CFGP_Defaults::FREELANCER_LICENSE		=> __('UNLIMITED Freelancer License (1 year)',CFGP_NAME),
			CFGP_Defaults::FREELANCER_LICENSE_4Y	=> __('UNLIMITED Freelancer License (4 years)',CFGP_NAME),
			CFGP_Defaults::BUSINESS_LICENSE			=> __('UNLIMITED Business License (1 year)',CFGP_NAME),
			CFGP_Defaults::BUSINESS_LICENSE_4Y		=> __('UNLIMITED Business License (4 years)',CFGP_NAME),
			CFGP_Defaults::LIFETIME_LICENSE			=> __('UNLIMITED Lifetime License',CFGP_NAME),
		);
		
		if( CFGP_DEV_MODE )
		{
			$license_names[CFGP_Defaults::DEVELOPER_LICENSE] = __('UNLIMITED Developer License', CFGP_NAME);
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
	
	public static function get_product_data(){
		$response = get_transient('cfgp-get-product-data');
		if(!$response) {
			if($return = CFGP_U::curl_get('https://cfgeoplugin.com/wp-admin/admin-ajax.php?action=cfgp_get_product_data', '', array(), true))
			{
				if($return['error'] === false && $return['lenght'] > 0){
					$response = $return['products'];
					set_transient('cfgp-get-product-data', $response, (MINUTE_IN_SECONDS * CFGP_SESSION));
				}
			}
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

		return empty($response) ? array() : $response;
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
	 * NOTICE FOR HACKERS AND DEVELOPERS:
	 * If you try to hack this function to activate the hidden functionality of the plugin,
	 * you can try but the lookup will definitely remain limited.
	 * Our server knows if you have activated the license or not, so there will be a lot of
	 * problems when trying to force activation.
	 *
	 * The license for this plugin is not expensive in general.
	 * Otherwise, if you need a job, let me know.
	 *
	 * I am planning to develop a special protection WordPress plugin that will bring a lot
	 * of work, and for that I need developers who also know how to hack.
	 * Because only a hacker can write good protection against hackers. ;)
	 *
	 * Cheers!
	 */
	public static function activated(){
		static $activated = NULL;
		
		if($activated !== NULL) return $activated;
		
		if(self::expired()) {
			$activated = false;
			return $activated;
		}
		
		foreach(array_keys(CFGP_Defaults::LICENSE) as $license_field){
			if($license_field != 'expired' && empty(self::get($license_field))){
				$activated = false;
				return $activated;
			}
		}
		
		$activated = true;
		return $activated;
	}
	
	/*
	 * Is license expired
	 * @return  true/false
	 */
	public static function expired(){
		static $expired = NULL;
		if(NULL === $expired) {
			$expired = ((int)self::expire_date('YmdH') < (int)date('YmdH'));
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
			$expire_date = array();
		}
		
		$expire_date[$format] = $generate_date($format);
		
		return $expire_date[$format];
	}
	
	/*
	 * Print API response errors notice
	 */
	public static function print_response_errors(){
		$response = get_transient('cfgp-license-response-errors');

		if($response) 
		{
			$error_label = array(
				'license_key' 	=> __('License Key',CFGP_NAME),
				'activation_id' => __('Activation ID',CFGP_NAME),
				'domain' 		=> __('Domain',CFGP_NAME),
				'sku' 			=> __('SKU',CFGP_NAME),
				'store_code'	=> __('Store Code',CFGP_NAME),
				'no_connection'	=> __('No Connection',CFGP_NAME),
				'api_error'		=> __('API Error',CFGP_NAME),
				'input_field'	=> __('Input Field',CFGP_NAME)
			);
			ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE); ?>
			
			<?php foreach($response as $key => $obj): ?>
                <?php if(is_numeric($key)) : $code = self::response_error_code($key); ?>
                    <h3><?php _e('Licensing Error',CFGP_NAME); ?>: <?php echo $code['message']; ?></h3>
                    <p><?php echo $code['info']; ?></p>
                <?php else: ?>
                	<h3><?php _e('Licensing Error',CFGP_NAME); ?>: <?php echo $error_label[$key]; ?></h3>
                    <ol>
                    <?php foreach($obj as $message): ?>
                        <li><?php echo $message; ?></li>
                    <?php endforeach; ?>
                    </ol>
                    <?php if($key == 'license_key'): ?>
                        <p><?php _e('You must enter a valid license key in order to continue with licensing your plugin installation.',CFGP_NAME);?></p>
                        <p><?php _e('Second reason why this may happen can be that you must choose a valid "License Type". If you purchase a "Personal License" and get a license key, you must enter that license key and choose license type to validate your key. If the key does not match your type you are not able to finish activation.',CFGP_NAME);?></p>
                    <?php elseif($key == 'sku'): ?>
                        <p><?php _e('One of the reasons why this may happen can be that you must choose a valid "License Type". If you purchase a "Personal License" and get a license key, you must enter that license key and choose license type to validate your key. If the key does not match your type you are not able to finish activation.',CFGP_NAME);?></p>
                    <?php endif; ?>
                <?php endif; ?>
			<?php endforeach; ?>
			<?php
			delete_transient('cfgp-license-response-errors');
			printf('<div class="notice notice-error is-dismissible">%s</div>', ob_get_clean());
		}
		
		return;
	}
	
	/*
	 * Print API response success notice
	 */
	public static function print_response_success(){
		$response = get_transient('cfgp-license-response-success');

		if($response) 
		{
			ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE); ?>
            <h3><?php _e('Activation succeeded', CFGP_NAME); ?></h3>
            <p><?php echo $response; ?></p>
			<?php
			delete_transient('cfgp-license-response-success');
			printf('<div class="notice notice-success is-dismissible">%s</div>', ob_get_clean());
		}
		
		return;
	}
	
	/*
	 * Activate plugin
	 */
	public static function activate($license_key, $sku){
		
		if(empty($license_key) || empty($sku)) {
			$input_field_error = array();
			
			if(empty($sku)) {
				$input_field_error = array_merge($input_field_error, array(
					__('You have not selected your license.',CFGP_NAME))
				);
			}
			if(empty($license_key)) {
				$input_field_error = array_merge($input_field_error, array(
					__('License key field is empty.',CFGP_NAME))
				);	
			}
			$input_field_error = array_merge($input_field_error, array(
				__('Make sure you select the license you purchased and enter the correct license key.',CFGP_NAME))
			);
			delete_transient('cfgp-license-response-success');
			set_transient('cfgp-license-response-errors', array(
				'input_field' => $input_field_error
			), YEAR_IN_SECONDS);
			return;
		}
		
		$post_data = array(
			'license_key' => $license_key,
			'sku' => $sku,
			'action' => 'license_key_activate',
			'store_code' => CFGP_STORE_CODE,
			'domain' => CFGP_U::get_host(true)
		);
		
		$response = CFGP_U::curl_post( CFGP_Defaults::API['authenticate'], $post_data, '', array(), true );
		
		if(empty($response)){
			delete_transient('cfgp-license-response-success');
			set_transient('cfgp-license-response-errors', array(
				'no_connection' => array(
					__('Unable to connect to server.',CFGP_NAME)
				)
			), YEAR_IN_SECONDS);
			return false;
		}
		
		if( isset( $response['error'] ) && $response['error'] == true )
		{
			delete_transient('cfgp-license-response-success');
			
			if(isset($response['errors_raw']) && !empty($response['errors_raw'])){
				$response['errors'] = $response['errors_raw'];
			}
			
			set_transient('cfgp-license-response-errors', (isset($response['errors']) ? $response['errors'] : array(
				'api_error' => array_merge(array(
					$response['message']
				), array(
					__('Make sure you select the license you purchased and enter the correct license key.',CFGP_NAME)
				))
			)), YEAR_IN_SECONDS);
			
			return false;
		}
		else
		{
			// Clear errors if exists
			delete_transient('cfgp-license-response-errors');
			
			// Update license
			$update = array(
				'key' => $response['data']['the_key'],
				'id' => $response['data']['activation_id'],
				'expire' => $response['data']['expire'],
				'expire_date' => $response['data']['expire_date'],
				'url' => $response['data']['url'],
				'sku' => $response['data']['sku'],
				'expired' => $response['data']['has_expired'],
				'status' => ($response['data']['status'] == 'active')
			);
			self::set($update);
			set_transient('cfgp-license-response-success', $response['message'], YEAR_IN_SECONDS);
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
		
		$response = CFGP_U::curl_post( CFGP_Defaults::API['authenticate'], $post_data, '', array(), true );
	
		if(empty($response)){
			delete_transient('cfgp-license-response-success');
			set_transient('cfgp-license-response-errors', array(
				'no_connection' => array(
					__('Unable to connect to server.',CFGP_NAME)
				)
			), YEAR_IN_SECONDS);
			return false;
		}
		
		if( isset( $response['error'] ) && $response['error'] == true )
		{
			delete_transient('cfgp-license-response-success');
			set_transient('cfgp-license-response-errors', (isset($response['errors']) ? $response['errors'] : array(
				'api_error' => array(
					$response['message']
				)
			)), YEAR_IN_SECONDS);
			return false;
		}
		else
		{
			// Clear errors if exists
			delete_transient('cfgp-license-response-errors');
			// Clear license
			self::set(CFGP_Defaults::LICENSE);
			set_transient('cfgp-license-response-success', $response['message'], YEAR_IN_SECONDS);
			return true;
		}
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
					( CFGP_NETWORK_ADMIN ? get_site_option( CFGP_NAME . '-license' ) : get_option( CFGP_NAME . '-license' ) ),
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
					return apply_filters( 'cfgp/license/get', ((!empty($get_option[$name]) || $get_option[$name] === 0) ? $get_option[$name] : $default), $default);
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
	public static function set($name_or_array=array(), $value=NULL)
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
				'message' => __('Invalid code', CFGP_NAME),
				'info' => __('Store code provided do not match the one set for the API.', CFGP_NAME),
			),
			2 => array(
				'message' => __('Invalid license key', CFGP_NAME),
				'info' => __('License key provided do not match the license key string format established by the API. (regex validation).', CFGP_NAME),
			),
			3 => array(
				'message' => __('Invalid license key', CFGP_NAME),
				'info' => __('SKU provided do not match the product SKU associated with the license key.', CFGP_NAME),
			),
			4 => array(
				'message' => __('Invalid license key', CFGP_NAME),
				'info' => __('License key provided do not match the license key string format established by the API. (missing dash validation).', CFGP_NAME),
			),
			5 => array(
				'message' => __('Invalid license key', CFGP_NAME),
				'info' => __('License key provided was not found in the database.', CFGP_NAME),
			),
			100 => array(
				'message' => __('Required', CFGP_NAME),
				'info' => __('No SKU was provided in the request.', CFGP_NAME),
			),
			101 => array(
				'message' => __('Required', CFGP_NAME),
				'info' => __('No license key code was provided in the request.', CFGP_NAME),
			),
			102 => array(
				'message' => __('Required', CFGP_NAME),
				'info' => __('No store code was provided in the request.', CFGP_NAME),
			),
			103 => array(
				'message' => __('Required', CFGP_NAME),
				'info' => __('No activation ID was provided in the request.', CFGP_NAME),
			),
			104 => array(
				'message' => __('Required', CFGP_NAME),
				'info' => __('No domain was provided in the request.', CFGP_NAME),
			),
			200 => array(
				'message' => __('License key has expired', CFGP_NAME),
				'info' => __('License key provided has expired.', CFGP_NAME),
			),
			201 => array(
				'message' => __('License key activation limit reached. Deactivate one of the registered activations to proceed', CFGP_NAME),
				'info' => __('License key provided has reached the limit of activations allowed.', CFGP_NAME),
			),
			202 => array(
				'message' => __('License key domain activation limit reached. Deactivate one or more of the registered activations to proceed', CFGP_NAME),
				'info' => __('License key provided has reached the domain limit allowed.', CFGP_NAME),
			),
			203 => array(
				'message' => __('Invalid activation', CFGP_NAME),
				'info' => __('Activation ID provided was not found.', CFGP_NAME),
			),
		);
		
		// Find message by array key (index)
		if(isset($error_code[$key])) {
			return $error_code[$key];
		}
		
		// Not found
		return array(
			'message' => __('Undefined error', CFGP_NAME),
			'info' => __('This error is not defined and you need to contact the author of the plugin for more information.', CFGP_NAME),
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