<?php
/**
 * Plugin options
 *
 * This class made safe options and cache
 * options to prevent multiple MySQL calls
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

if(!class_exists('CFGP_API')) :
class CFGP_Options
{
	/*
	 * Get plugin option
	 *
	 * @pharam   (string)   $name                        If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default                     Default values
	 *
	 * @return   (array|string|int|bloat|bool)           plugin option/s
	 */
	public static function get($name = false, $default = NULL)
	{
		// Get cache
		$get_option = CFGP_Cache::get('options');
		
		// If cache is empty, get from the database
		if( !$get_option ){
			$get_option = CFGP_Cache::set(
				'options',
				wp_parse_args(
					( CFGP_NETWORK_ADMIN ? get_site_option( CFGP_NAME ) : get_option( CFGP_NAME ) ),
					apply_filters( 'cfgp/settings/default', CFGP_Defaults::OPTIONS)
				)
			);
		}
		
		// Search and return
		if($get_option) {
			if( $name === false ){
				return apply_filters( 'cfgp/options/get', $get_option, $default);
			} else {
				if(isset($get_option[$name])) {
					// Enable beta options
					if (in_array($name, CFGP_Defaults::BETA_OPTIONS)) {
						if(!isset($get_option['enable_beta'])) {
							return apply_filters( 'cfgp/options/get', $get_option, $default);
						}
						if($get_option['enable_beta'] == 0) {
							return apply_filters( 'cfgp/options/get', $get_option, $default);
						}
					}
					// Return values
					return apply_filters( 'cfgp/option/get', ((!empty($get_option[$name]) || $get_option[$name] === 0) ? $get_option[$name] : $default), $default);
				}
			}
		}
		
		// Show default
		return apply_filters( 'cfgp/options/get', $default, $default);
	}
	
	/*
	 * Get plugin BETA options
	 *
	 * @pharam   (string)   $name                        If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default                     Default values
	 *
	 * @return   (array|string|int|bloat|bool)           plugin option/s
	 */
	public static function get_beta($name = false, $default = NULL){
		$return = $default;
		if (in_array($name, CFGP_Defaults::BETA_OPTIONS)) {
			$return = self::get($name, $default);
		}		
		return apply_filters( 'cfgp/option/get_beta', $return, $name, $default, CFGP_Defaults::BETA_OPTIONS, CFGP_Defaults::OPTIONS );
	}	
	
	
	/*
	 * Set plugin option
	 *
	 * @pharam   (string|array)   $name_or_array       array of option name and values or just single option name
	 * @pharam   (string)         $value               if single option name is set, this is the value
	 *
	 * @return   (array)                               plugin options
	 */
	public static function set($name_or_array=[], $value=NULL)
	{
		// Clear cache;
		$clear_cache = false;
		// Get plugin options
		$options = self::get();
		// Get default options
		$default_options = apply_filters( 'cfgp/settings/default', CFGP_Defaults::OPTIONS);
		// Get default keys
		$filter = apply_filters('cfgp/options/set/filter', array_keys($default_options));
		// Collect and set new values
		if(!empty($name_or_array))
		{
			if(is_array($name_or_array)) {
				$clear_cache = true;
				$name_or_array = array_merge(
					(!empty($options) ? $options : $default_options),
					$name_or_array
				);
				$name_or_array = apply_filters('cfgp/options/set/fields', $name_or_array, $options, $default_options);
				foreach($name_or_array as $key => $val) {
					if(in_array($key, $filter) !== false) {
						$options[$key] = self::sanitize($val);
					} else {
						unset($name_or_array[$key]);
					}
				}
			} else if(!is_numeric($name_or_array) && is_string($name_or_array)) {
				$name = $name_or_array;
				$name = apply_filters("cfgp/options/set/field/{$name}", $name, $default_options);
				if(in_array($name, $filter) !== false) {
					$options[$name] = self::sanitize($value);
				}
			}
		}
		// Apply action
		do_action('cfgp/options/action/set', $options, $default_options, $name_or_array, $value, $clear_cache);
		// Return on the bad data
		if(empty($options)) return false;
		// Save new options
		if(CFGP_NETWORK_ADMIN){
			update_site_option(CFGP_NAME, $options, true);
		}else{
			update_option(CFGP_NAME, $options, true);
		}
		// Save to cache
		CFGP_Cache::set('options', $options);
		
		// Clear special API cache
		if( $clear_cache ) {
			CFGP_API::remove_cache();
		}
		
		// Return
		return apply_filters( 'cfgp/options/set', $options, $default_options, $name_or_array, $value);
	}
	
	/*
	 * Set plugin option
	 *
	 * @pharam   (string|array)   $name_or_array       array of option name and values or just single option name
	 *
	 * @return   (array)                               plugin options
	 */
	public static function delete($name_or_array)
	{
		// Get plugin options
		$options = self::get();
		// Get default options
		$default_options = apply_filters( 'cfgp/settings/default', CFGP_Defaults::OPTIONS);
		// Get default keys
		$filter = apply_filters('cfgp/options/delete/filter', array_keys($default_options));
		// Remove options
		if(is_array($name_or_array)) {
			$name_or_array = array_map('trim', $name_or_array);
			
			foreach($name_or_array as $key) {
				if(isset($options[$key]) && in_array($key, $filter) !== false) {
					unset($options[$key]);
				}
			}
		} else if(isset($options[$name_or_array]) && in_array($name_or_array, $filter) !== false) {
			unset($options[$name_or_array]);
		}
		// Set defaults
		$options = array_merge($default_options, $options);
		// Apply action
		do_action('cfgp/options/action/delete', $options, $default_options, $name_or_array);
		// Update options
		if(CFGP_NETWORK_ADMIN){
			update_site_option(CFGP_NAME, $options, true);
		}else{
			update_option(CFGP_NAME, $options, true);
		}
		// Save to cache
		CFGP_Cache::set('options', $options);
		// Return
		return apply_filters( 'cfgp/options/delete', $options, $default_options, $name_or_array);
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
			if(!empty($str)) {
				foreach($str as $key => $obj) {
					$data[$key]=self::sanitize( $obj ); 
				}
			}
			return $data;
		}
		else
		{			
			if(is_numeric($str))
			{
				$str = sanitize_text_field( $str );
				
				if(intval( $str ) == $str) {
					$str = intval( $str );
				} else if(floatval($str) == $str) {
					$str = floatval( $str );
				}
			}
			else if(filter_var($str, FILTER_VALIDATE_URL) !== false)
			{
				return esc_url($str);
			}
			else if(preg_match('/^([0-9a-z-_.]+@[0-9a-z-_.]+.[a-z]{2,8})$/i', $str))
			{
				$str = trim($str, "&$%#?!.;:,");
				$str = sanitize_email($str);

				return CFGP_U::strtolower($str);
			}
			else if(is_bool($str))
			{
				$str = ($str ? true : false);
			}
			else if(!is_bool($str) && in_array(strtolower($str), array('true','false'), true))
			{
				$str = ( strtolower($str) == 'true' );
			}
			else
			{
				$str = html_entity_decode($str);
				if(preg_match('/<\/?[a-z][\s\S]*>/i', $str)) {
					$str = wp_kses_post( $str ?? '' );
				} else if( preg_match('/[\n]/', $str) ) {
					$str = sanitize_textarea_field( $str );
				} else {
					$str = sanitize_text_field( $str );
				}
			}
		}
		
		return $str;
	}
	
	/**
	 * Sync with the old version of the plugin
	 * This functionality do automatization for the certain type of data expected in this plugin
	 */
	public static function sync_with_the_old_version_of_the_plugin(){
		global $wpdb;
		// Get old options before version 8.0.0
		if( CFGP_U::is_network_admin() ) {
			$old_options = get_site_option('cf_geoplugin');
		} else {
			$old_options = get_option( 'cf_geoplugin' );
		}
		// IF options exists, we must append it to new one
		if( $old_options )
		{
			// First collect license data and try to activate it			
			if(
				isset($old_options['license'])
				&& $old_options['license'] === 1
				&& isset($old_options['license_key'])
				&& !empty($old_options['license_key'])
				&& isset($old_options['license_sku']) 
				&& !empty($old_options['license_sku'])
			) {
				CFGP_License::activate($old_options['license_key'], $old_options['license_sku']);
			}
			// Set the other options properly
			$new_options = [];
			foreach($old_options as $option => $value){
				if( in_array($option, CFGP_Defaults::OPTIONS) ) {
					$new_options[$option] = $value;
				}
			}
			// Be sure all fields are in the place
			$new_options = array_merge(CFGP_Defaults::OPTIONS, $new_options);
			// Save
			self::set($new_options);
			// Remove old one
			delete_site_option('cf_geoplugin');
			delete_option( 'cf_geoplugin' );
		}
	}
}
endif;