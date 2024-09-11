<?php
/**
 * Add controls of the navigation menu
 *
 * @link          http://infinitumform.com/
 * @since         8.0.1
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Debug', false)) : class CFGP_Debug extends CFGP_Global {
	private static $data = [];
	
	/*
	 * Save informations to array
	 */
    public static function save( $value, $title = NULL, $uniue = false ) {
		
		static $collection;
		
		$name = '';
		if( $uniue === true ) {
			$name = md5(serialize(array($value, $title)));
			if( isset($collection[$name]) ) {
				return $collection[$name];
			}
		}
		
		if( ! (is_string($value) || is_numeric($value)) ) {
			$value = var_export($value, 1);
		}
		
		if( !empty($title) ) {
			self::title($title);
		}
		
		$key = self::key();
		self::$data[ $key ] = $value;
		
		if( $uniue === true ) {
			$collection[$name];
		}
		
		return self::$data[ $key ];
    }
	
	/*
	 * Print informations
	 */
	public static function print( $echo = false ) {
		if( $echo === true ) {
			if( !empty(self::$data) ) {
				echo wp_kses_post( join(PHP_EOL, self::$data) );
			}
		} else {
			if( !empty(self::$data) ) {
				return wp_kses_post( join(PHP_EOL, self::$data) );
			}
			return NULL;
		}
	}
	
	/*
	 * Write log file
	 */
	public static function write( $path, $filename = 'cf_geoplugin_debug.log' ) {
		// Check is there is a logs
		if( empty(self::$data) ) {
			throw new Exception( esc_html__('There is no logs', 'cf-geoplugin') );
			return 0;
		}
		
		// Fix directory separator (Win OS)
		if( CFGP_OS::is_win() ) {
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		}
		
		// Check is there a directory and try to create one if not exists
		if( !file_exists($path) ) {
			
			mkdir($path, 0644, true);
			
			if( !file_exists($path) ) {
				throw new Exception( sprintf( esc_html__('There is no directory on the path: "%s"', 'cf-geoplugin'), esc_html($path)) );
				return -1;
			}
		}
		
		// Check is directory writable
		if( !is_writable($path) ) {
			throw new Exception( sprintf(esc_html__('Directory "%s" is not writable', 'cf-geoplugin'), esc_html($path)) );
			return -2;
		}
		
		// Build full path
		$full_path = $path . DIRECTORY_SEPARATOR . $filename;
		
		// Unlink existing file
		if( file_exists($full_path) && is_writable($full_path) ) {
			unlink($full_path);
		}
		
		// Save logs to file and return full path
		if( $fo = fopen($full_path , 'w') ) {
			
			fwrite($fo, '+=====================================================================' . PHP_EOL, 79);
			$title = sprintf(
				'| %s - %s',
				__('Geo Controller Debug', 'cf-geoplugin'),
				date('Y-m-d H:i:s p e')
			);
			fwrite($fo, $title . PHP_EOL, (strlen($title . PHP_EOL)+8));
			fwrite($fo, '+=====================================================================' . PHP_EOL, 79);
			
			foreach(self::$data as $i => $txt) {
				fwrite($fo, $txt . PHP_EOL, (strlen($txt . PHP_EOL)+8));
			}
			fclose($fo);
			
			return $full_path;
		}
		
		// Everything fail
		throw new Exception( sprintf(esc_html__('Unable to write file: "%s"', 'cf-geoplugin'), esc_html($full_path)) );
		return false;
	}
	
	/*
	 * Write log file
	 */
	private static function title( $title ) {
		self::$data[self::key()] = '+=====================================================================';
		self::$data[self::key()] = '| ' . $title;
		self::$data[self::key()] = '+=====================================================================';
	}
	
	/*
	 * PRIVATE: Generate key
	 */
	private static function key() {
		static $i = -1;
		++$i;
		return $i;
	}
}
endif;