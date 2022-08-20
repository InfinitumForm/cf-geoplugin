<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Cache Control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 * @todo          https://www.php.net/manual/en/function.spl-object-hash.php
 */
if(!class_exists('CFGP_Cache')) : class CFGP_Cache {
	/*
	 * Save all cached objcts to this variable
	 */
	private static $cache = NULL;

	/*
	 * Get cached object
	 *
	 * Returns the value of the cached object, or false if the cache key doesn’t exist
	 */
    public static function get($key) {
        return self::$cache[ self::key($key) ] ?? NULL;
    }
	
	/*
	 * Save object to cache
	 *
	 * This function adds data to the cache if the cache key doesn’t already exist.
	 * If it does exist, the data is not added and the function returns old value
	 */
    public static function add($key, $value) {
		self::garbage_cleaner();
		$key = self::key($key);
		if(!isset(self::$cache[ $key ])) {
			self::$cache[ $key ] = $value;
		}
		return self::$cache[ $key ];
    }

	/*
	 * Save object to cache
	 *
	 * Adds data to the cache. If the cache key already exists, then it will be overwritten;
	 * if not then it will be created.
	 */
    public static function set($key, $value, $expire=0) {
		self::garbage_cleaner();
		$key = self::key($key);
		self::$cache[ $key ] = $value;
		return self::$cache[ $key ];
    }
	
	/*
	 * Replace cached object
	 *
	 * Replaces the given cache if it exists, returns false otherwise.
	 */
    public static function replace($key, $value, $expire=0) {
		$key = self::key($key);
        if(isset(self::$cache[ $key ])) {
			self::$cache[ $key ] = $value;
		}
		return self::$cache[ $key ];
    }
	
	/*
	 * Delete cached object
	 *
	 * Clears data from the cache for the given key.
	 */
	public static function delete($key) {
		$key = self::key($key);
		if(isset(self::$cache[ $key ])) {
			unset(self::$cache[ $key ]);
		}
    }
	
	/*
	 * Clears all cached data
	 */
	public static function flush() {
		self::$cache=NULL;
		return true;
    }
	
	/*
	 * Debug cache
	 */
	public static function debug() {
		ob_start();
			var_dump(self::$cache);
		$debug = ob_get_clean();
		echo wp_kses_post('<pre class="cfgp-cache-debug">' . htmlspecialchars(preg_replace(
			array('/(\=\>\n\s{2,4})/'),
			array(' => '),
			$debug
		)) . '</pre>');
	}
	
	/*
	 * Cache size
	 */
	public static function get_size() {
		$get_size = strlen(json_encode(self::$cache));
		return ceil($get_size*8);
	}
	
	/*
	 * Cache key
	 */
	private static function key($key) {
		static $suffix;

		if ( empty($suffix) ) {
			$suffix = str_replace('.', '', (string)microtime(true));
		}

		$key = trim($key);

		return $key . '_' . $suffix;
	}
	
	/*
	 * PRIVATE: Clean up the accumulated garbage
	 */
	private static function garbage_cleaner() {
		if (!function_exists('mt_getrandmax') || !is_array(self::$cache)) {
			return;
		}

		if (function_exists('mt_rand')) {
			$getrandmax = mt_getrandmax();
			$rand = mt_rand();
		}
		else {
			$getrandmax = getrandmax();
			$rand = rand();
		}
		
		$exclude = apply_filters('cfgp/cache/exclude_from_cleaning', array(
			// Classes
			'CFGP_DB_Cache',
			'CFGP_API',
			'CFGP_IP',
			'CFGP_License',
			// Main objects
			'API',
			'ID',
			'IP',
			'REST_KEY',
			'license',
			'IP-blocked',
			'IP-server',
			// Helpers
			'parse_url',
			'current_url',
			'get_page',
			'get_post_type',
			'is_rest_enabled',
			'has_seo_redirection',
			'transfer_dns_records'
		));

		$capability = apply_filters('cfgp/cache/capability', 100);
		$gc_probability = apply_filters('cfgp/cache/gc_probability', 1);
		$gc_divisor = apply_filters('cfgp/cache/gc_divisor', 100);

		if (defined('CFGP_CACHE_CAPABILITY')) {
			$capability = CFGP_CACHE_CAPABILITY;
		}
		if (defined('CFGP_CACHE_GARBAGE_COLLECTION_PROBABILITY')) {
			$gc_probability = CFGP_CACHE_GARBAGE_COLLECTION_PROBABILITY;
		}
		if (defined('CFGP_CACHE_GARBAGE_COLLECTION_DIVISOR')) {
			$gc_divisor = CFGP_CACHE_GARBAGE_COLLECTION_DIVISOR;
		}

		if (($rand / $getrandmax) && ($gc_probability / $gc_divisor)) {
			while (count(self::$cache) > $capability) {
				reset(self::$cache);
				$key = key(self::$cache);
				
				if( !in_array($key, $exclude) ) {
					self::delete($key);
				}
			}
		}
	}
} endif;