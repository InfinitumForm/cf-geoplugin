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

if(!class_exists('CFGP_API', false)) : class CFGP_Options
{
	/*
	 * Get plugin option
	 *
	 * @param   string   $name        If exists, return value for single option, if empty return all options
	 * @param   mixed    $default     Default values
	 *
	 * @return  mixed                 Plugin option/s
	 */
	public static function get($name = false, $default = NULL)
	{
		// Attempt to retrieve options from cache
		$options = CFGP_Cache::get('options');

		// If cache is empty, fetch from the database and update the cache
		if (!$options) {
			$rawOptions = CFGP_NETWORK_ADMIN ? get_site_option(CFGP_NAME) : get_option(CFGP_NAME);
			$options = wp_parse_args($rawOptions, apply_filters('cfgp/settings/default', CFGP_Defaults::OPTIONS));
			CFGP_Cache::set('options', $options);
		}

		// If name is not provided, return all options
		if ($name === false) {
			return apply_filters('cfgp/options/get', maybe_unserialize($options), $default, $name);
		}

		// If the requested option exists
		if (isset($options[$name])) {
			// Check for beta options and if they're enabled
			if (in_array($name, CFGP_Defaults::BETA_OPTIONS) && (!isset($options['enable_beta']) || $options['enable_beta'] == 0)) {
				return apply_filters('cfgp/options/get', maybe_unserialize($options), $default, $name);
			}

			return apply_filters('cfgp/option/get', maybe_unserialize($options[$name] ?? $default), $default, $name);
		}

		return apply_filters('cfgp/options/get', $default, $default);
	}
	
	/*
	 * Get plugin BETA options
	 *
	 * @param   string  $name      If exists, return value for single option, if empty return all options
	 * @param   mixed   $default   Default values
	 *
	 * @return  mixed              Plugin option/s
	 */
	public static function get_beta($name = false, $default = NULL) {
		if (in_array($name, CFGP_Defaults::BETA_OPTIONS)) {
			return apply_filters('cfgp/option/get_beta', self::get($name, $default), $name, $default, CFGP_Defaults::BETA_OPTIONS, CFGP_Defaults::OPTIONS);
		}
		return apply_filters('cfgp/option/get_beta', $default, $name, $default, CFGP_Defaults::BETA_OPTIONS, CFGP_Defaults::OPTIONS);
	}
	
	
	/*
	 * Set plugin option
	 *
	 * @param   mixed   $name_or_array   Either an array of option names and values or just a single option name
	 * @param   mixed   $value           If a single option name is provided, this is the value
	 *
	 * @return  array                    Plugin options
	 */
	public static function set($name_or_array = [], $value = NULL)
	{
		$options = self::get();
		$default_options = apply_filters('cfgp/settings/default', CFGP_Defaults::OPTIONS);
		$filter = apply_filters('cfgp/options/set/filter', array_keys($default_options));
		$clear_cache = false;

		if (!empty($name_or_array)) {
			if (is_array($name_or_array)) {
				$clear_cache = true;
				$name_or_array = array_merge($options ?: $default_options, $name_or_array);
				$name_or_array = apply_filters('cfgp/options/set/fields', $name_or_array, $options, $default_options);

				foreach ($name_or_array as $key => $val) {
					if (in_array($key, $filter)) {
						$options[$key] = self::sanitize($val);
					} else {
						unset($name_or_array[$key]);
					}
				}
			} elseif (is_string($name_or_array) && !is_numeric($name_or_array)) {
				$name = apply_filters("cfgp/options/set/field/{$name_or_array}", $name_or_array, $default_options);

				if (in_array($name, $filter)) {
					$options[$name] = self::sanitize($value);
				}
			}
		}

		do_action('cfgp/options/action/set', $options, $default_options, $name_or_array, $value, $clear_cache);

		if (!$options) return false;

		CFGP_NETWORK_ADMIN ? update_site_option(CFGP_NAME, $options, true) : update_option(CFGP_NAME, $options, true);

		CFGP_Cache::set('options', $options);

		if ($clear_cache) {
			CFGP_API::remove_cache();
		}

		return apply_filters('cfgp/options/set', $options, $default_options, $name_or_array, $value);
	}
	
	/**
	 * Delete plugin option
	 *
	 * @param   string|array  $name_or_array  Option name(s) to delete.
	 *
	 * @return  array                        Updated plugin options.
	 */
	public static function delete($name_or_array) {
		// Get current and default plugin options.
		$options = self::get();
		$default_options = apply_filters('cfgp/settings/default', CFGP_Defaults::OPTIONS);

		// Determine which options are allowed to be deleted.
		$allowed_keys = apply_filters('cfgp/options/delete/filter', array_keys($default_options));

		// Convert single key into an array for uniform handling.
		$keys_to_delete = (is_array($name_or_array)) ? $name_or_array : [$name_or_array];

		// Remove the specified options if they're allowed to be deleted.
		foreach ($keys_to_delete as $key) {
			if (isset($options[$key]) && in_array($key, $allowed_keys)) {
				unset($options[$key]);
			}
		}

		// Merge with defaults to ensure all required keys are present.
		$options = array_merge($default_options, $options);

		// Notify of option deletion.
		do_action('cfgp/options/action/delete', $options, $default_options, $name_or_array);

		// Update the options in the database.
		if (CFGP_NETWORK_ADMIN) {
			update_site_option(CFGP_NAME, $options, true);
		} else {
			update_option(CFGP_NAME, $options, true);
		}

		// Update the cache.
		CFGP_Cache::set('options', $options);

		// Return the updated options.
		return apply_filters('cfgp/options/delete', $options, $default_options, $name_or_array);
	}
	
	/**
	 * Sanitize string or array
	 * This functionality automates sanitization for the data types expected in this plugin.
	 *
	 * @param   mixed $input  The input string or array to sanitize.
	 *
	 * @return  mixed        Sanitized options.
	 */
	public static function sanitize($input) {
		// If the input is an array, sanitize each element recursively.
		if (is_array($input)) {
			return array_map([self::class, 'sanitize'], $input);
		}

		// Sanitize numeric values.
		if (is_numeric($input)) {
			if (intval($input) == $input) {
				return intval($input);
			} elseif (floatval($input) == $input) {
				return floatval($input);
			}
		}

		// Sanitize URLs.
		if (filter_var($input, FILTER_VALIDATE_URL) !== false) {
			return esc_url($input);
		}

		// Sanitize email addresses.
		if (preg_match('/^([0-9a-z-_.]+@[0-9a-z-_.]+\.[a-z]{2,8})$/i', $input)) {
			return CFGP_U::strtolower(sanitize_email(trim($input, "&$%#?!.;:,")));
		}

		// Convert boolean-like strings to actual booleans.
		$lowerInput = strtolower($input);
		if (in_array($lowerInput, ['true', 'false'], true)) {
			return $lowerInput === 'true';
		}

		// If the string contains HTML tags, sanitize as post content. Otherwise, sanitize as plain text.
		if (preg_match('/<\/?[a-z][\s\S]*>/i', $input)) {
			return wp_kses_post($input);
		} elseif (strpos($input, "\n") !== false) {
			return sanitize_textarea_field($input);
		} else {
			return sanitize_text_field($input);
		}
	}
}
endif;