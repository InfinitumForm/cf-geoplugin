<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Database Cache Control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 */
if(!class_exists('CFGP_DB_Cache')) : class CFGP_DB_Cache {
	
	public function __construct(){
		global $wpdb;
		$wpdb->query( $wpdb->prepare("DELETE FROM `{$wpdb->cfgp_cache}` WHERE `expire` != 0 AND `expire` <= %d", time() ));
	}
	
	/*
	 * Get cached object
	 *
	 * Returns the value of the cached object, or false if the cache key doesn’t exist
	 */
	public static function get( string $key, $default=NULL ) {
		
		$key = self::key($key);

		if( !self::table_exists() ) {
			if( $transient = get_transient( $key ) ) {
				return $transient;
			} else {
				return $default;
			}
		}
		
		global $wpdb;
		
		if( !empty($key) && ($result = $wpdb->get_var( $wpdb->prepare("
			SELECT
				`{$wpdb->cfgp_cache}`.`value`
			FROM
				`{$wpdb->cfgp_cache}`
			WHERE
				`{$wpdb->cfgp_cache}`.`key` = %s
		", $key ))) ) {
			if(is_serialized($result)){
				$result = unserialize($result);
			}
			return $result;
		}
		
		return $default;
	}
	
	/*
	 * Save object to cache
	 *
	 * This function adds data to the cache if the cache key doesn’t already exist.
	 * If it does exist returns false, if not save it return NULL
	 */
    public static function add(string $key, $value, int $expire = 0) {
		
		$key = self::key($key);
		
		if(self::get($key, NULL) === NULL) {
			
			if( !self::table_exists() ) {
				$save = set_transient( $key, $value, $expire );
			} else {
				if($expire > 0) {
					$expire = (CFGP_TIME+$expire);
				}
				
				if(is_array($value) || is_object($value) || is_bool($value)){
					$value = serialize($value);
				}
				
				global $wpdb;
				
				$save = $wpdb->query( $wpdb->prepare("
					INSERT INTO `{$wpdb->cfgp_cache}` (`key`, `value`, `expire`)
					VALUES (%s, %s, %d)
				", $key, $value, $expire ));
			}
			
			if($save && !is_wp_error($save)){
				return $value;
			}
			
			return NULL;
		}
		
		return false;
    }
	
	/*
	 * Save object to cache
	 *
	 * Adds data to the cache. If the cache key already exists, then it will be overwritten;
	 * if not then it will be created.
	 */
    public static function set(string $key, $value, int $expire=0) {
		
		$key = self::key($key);
		
		if( !self::table_exists() ) {
			if( set_transient( $key, $value, $expire ) ) {
				return $value;
			} else {
				return NULL;
			}
		}
		
		if( !self::add($key, $value, $expire) ) {
			if( self::replace($key, $value, $expire) ) {
				return $value;
			}
		}
		
		return NULL;
    }
	
	/*
	 * Replace cached object
	 *
	 * Replaces the given cache if it exists, returns NULL otherwise.
	 */
    public static function replace(string $key, $value, int $expire=0) {
		
		$key = self::key($key);
		
		if( !self::table_exists() ) {
			$save = set_transient( $key, $value, $expire );
		} else {
			if($expire > 0) {
				$expire = (CFGP_TIME+$expire);
			}
			
			if(is_array($value) || is_object($value) || is_bool($value)){
				$value = serialize($value);
			}
			
			global $wpdb;
			
			$save = $wpdb->query( $wpdb->prepare("
				UPDATE `{$wpdb->cfgp_cache}`
				SET `value` = %s, `expire` = %d
				WHERE `key` = %s
			", $value, $expire, $key ));
		}
		
		if($save && !is_wp_error($save)){
			return $value;
		}
		
		return NULL;
    }
	
	/*
	 * Delete cached object
	 *
	 * Clears data from the cache for the given key.
	 */
	public static function delete(string $key) {
		
		$key = self::key($key);
		
		if( !self::table_exists() ) {
			return delete_transient( $key );
		}
		
		global $wpdb;
		
		return $wpdb->query( $wpdb->prepare("DELETE FROM `{$wpdb->cfgp_cache}` WHERE `key` = %s", $key ));
    }
	
	/*
	 * Clears all cached data
	 */
	public static function flush() {
		
		if( !self::table_exists() ) {
			CFGP_U::flush_plugin_cache();
			return true;
		}
		
		global $wpdb;
		return $wpdb->query("TRUNCATE TABLE `{$wpdb->cfgp_cache}`");
    }
	
	/*
	 * Cache key
	 */
	private static function key(string $key) {
		$key = trim($key);
		$key = stripslashes($key);
		
		return str_replace(
			array(
				'.',
				"\s",
				"\t",
				"\n",
				"\r",
				'\\',
				'/'
			),
			array(
				'_',
				'-',
				'-',
				'-',
				'-',
				'_',
				'_'
			),
			$key
		);
	}
	
	/*
	 * Check is database table exists
	 * @verson    1.0.0
	 */
	public static function table_exists($dry = false) {
		static $table_exists = NULL;
		global $wpdb;
		
		if(NULL === $table_exists || $dry) {
			if($wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->cfgp_cache}'" ) != $wpdb->cfgp_cache) {
				if( $dry ) {
					return false;
				}
				
				$table_exists = false;
			} else {
				if( $dry ) {
					return true;
				}
				
				$table_exists = true;
			}
		}
		
		return $table_exists;
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
			CREATE TABLE IF NOT EXISTS {$wpdb->cfgp_cache} (
				`key` varchar(255) NOT NULL,
				`value` text NOT NULL,
				`expire` int(11) NOT NULL DEFAULT 0,
				UNIQUE KEY `cache_key` (`key`),
				KEY `cache_expire` (`expire`)
			) {$charset_collate}
			");
		}
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
	
} endif;