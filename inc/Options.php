<?php
/**
 * Plugin options
 *
 * This class made safe options and cache
 * options to prevent multiple MySQL calls
 *
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
		global $cfgp_cache;
		// Get cache
		$get_option = $cfgp_cache->get('options');
		
		// If cache is empty, get from the database
		if( !$get_option ){
			$get_option = $cfgp_cache->set(
				'options',
				wp_parse_args(
					( CFGP_NETWORK_ADMIN ? get_site_option( CFGP_NAME ) : get_option( CFGP_NAME ) ),
					CFGP_Defaults::OPTIONS
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
							return apply_filters( 'cfgp/options/get', $default, $default);
						}
						if($get_option['enable_beta'] == 0) {
							return apply_filters( 'cfgp/options/get', $default, $default);
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
	 * Get plugin BETA options - TO DO
	 *
	 * @pharam   (string)   $name                        If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default                     Default values
	 *
	 * @return   (array|string|int|bloat|bool)           plugin option/s
	 */
	public static function get_beta($name = false, $default = NULL){
		return self::get($name, $default);
	}
	
	/*
	 * Set plugin option
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
		$filter = apply_filters('cfgp/options/set/filter', array_keys(CFGP_Defaults::OPTIONS));
		
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
			update_site_option(CFGP_NAME, $options, true);
		}else{
			update_option(CFGP_NAME, $options, true);
		}
		
		// Save to cache
		$cfgp_cache->set('options', $options);
		
		return apply_filters( 'cfgp/options/set', $options, CFGP_Defaults::OPTIONS, $name_or_array, $value);
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
		global $cfgp_cache;
		
		// Get plugin options
		$options = self::get();
		
		// Get default keys
		$filter = apply_filters('cfgp/options/delete/filter', array_keys(CFGP_Defaults::OPTIONS));
		
		// Remove options
		if(is_array($name_or_array))
		{
			$name_or_array = array_map('trim', $name_or_array);
			
			foreach($name_or_array as $key) {
				if(isset($options[$key]) && in_array($key, $filter) !== false) {
					unset($options[$key]);
				}
			}
		}
		else if(isset($options[$name_or_array]) && in_array($name_or_array, $filter) !== false)
		{
			unset($options[$name_or_array]);
		}
		
		// Set defaults
		$options = array_merge(CFGP_Defaults::OPTIONS, $options);
		
		// Update options
		if(CFGP_NETWORK_ADMIN){
			update_site_option(CFGP_NAME, $options, true);
		}else{
			update_option(CFGP_NAME, $options, true);
		}
		
		// Save to cache
		$cfgp_cache->set('options', $options);
		
		return apply_filters( 'cfgp/options/delete', $options, CFGP_Defaults::OPTIONS, $name_or_array);
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
				$data[$key]=self::sanitize( $obj ); 
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
}
endif;