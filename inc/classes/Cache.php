<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Cache Control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 */
if(!class_exists('CFGP_Cache')) :
class CFGP_Cache
{
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
	 * If it does exist, the data is not added and the function returns
	 */
    public static function add($key, $value) {
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
		echo '<pre class="cfgp-cache-debug">' . htmlspecialchars(preg_replace(
			array('/(\=\>\n\s{2,4})/'),
			array(' => '),
			$debug
		)) . '</pre>';
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

		return $key . '__' . $suffix;
	}
}
endif;