<?php
/**
 * License control
 *
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
		return empty($response) ? array() : $response;
	}
	
	/*
	 * Access level
	*/
	public static function level($level = 0){		
		$return = 0;
		
		if($level==0){
			$level=CFGP_Options::get();
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
			if(isset($level['license']) && isset($level['license_sku']))
			{
				if($level['license'])
				{
					if(isset($levels[$level['license_sku']])) {
						$return = $levels[$level['license_sku']];
					}
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
	 * NOTICE FOR HACKERS:
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
		foreach(array_keys(CFGP_Defaults::LICENSE) as $license_field){
			if(empty(CFGP_Options::get($license_field))) return false;
		}
		
		return true;
	}
	
	public static function print_response_errors(){
		$response = get_transient('cfgp-license-response-errors');
		
		if($response) 
		{
			$error_label = array(
				'license_key' 	=> __('License Key',CFGP_NAME),
				'activation_id' => __('Activation ID',CFGP_NAME),
				'domain' 		=> __('Domain',CFGP_NAME),
				'sku' 			=> __('SKU',CFGP_NAME),
				'store_code'	=> __('Store Code',CFGP_NAME)
			);
			ob_start('', 0, PHP_OUTPUT_HANDLER_REMOVABLE); ?>
			
			<?php foreach($response as $key => $obj): ?>
            	<h3><?php _e('ERROR',CFGP_NAME); ?>: <?php echo $error_label[$key]; ?></h3>
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
			<?php endforeach; ?>
			
			<?php
			delete_transient('cfgp-license-response-errors');
			return ob_get_clean();
		}
		
		return false;
	}
	
	/*
	 * Activate plugin
	 */
	public static function activate($license_key, $sku){
		global $cfgp_cache;
		$post_data = array(
			'license_key' => $license_key,
			'sku' => $sku,
			'action' => 'activate_license',
			'store_code' => CFGP_STORE_CODE,
			'domain' => CFGP_U::get_host(true)
		);
		
		$response = CFGP_U::curl_post( CFGP_Defaults::API['authenticate'], $post_data, '', array(), true );
		
		if( isset( $response['error'] ) && $response['error'] )
		{
			set_transient('cfgp-license-response-errors', $response['errors'], YEAR_IN_SECONDS);
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
		global $cfgp_cache;
		// Get cache
		$get_option = $cfgp_cache->get('license');
		
		// If cache is empty, get from the database
		if( !$get_option ){
			$get_option = $cfgp_cache->set(
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
					return apply_filters( 'cfgp/option/get', ((!empty($get_option[$name]) || $get_option[$name] === 0) ? $get_option[$name] : $default), $default);
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
		global $cfgp_cache;
		
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
						$options[$key] = self::sanitize($val);
					}
				}
			}
			else if(!is_numeric($name_or_array) && is_string($name_or_array))
			{
				if(in_array($name_or_array, $filter) !== false) {
					$options[$name_or_array] = self::sanitize($value);
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
		$cfgp_cache->set('license', $options);
		
		return apply_filters( 'cfgp/license/set', $options, CFGP_Defaults::LICENSE, $name_or_array, $value);
	}
	
	/*
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		global $cfgp_cache;
		$class = self::class;
		$instance = $cfgp_cache->get($class);
		if ( !$instance ) {
			$instance = $cfgp_cache->set($class, new self());
		}
		return $instance;
	}
}
endif;