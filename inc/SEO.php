<?php
/**
 * Main API class
 *
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_SEO')) :
class CFGP_SEO extends CFGP_Global {
	
	public function __construct(){
		$this->add_filter( 'upload_mimes', 'upload_mimes', 99 );
		$this->add_filter( 'mime_types', 'upload_mimes', 99 );
		$this->add_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );
	}
	
	/**
	 * Allow .csv uploads
	 */
	public function upload_mimes($mimes = array()) {
		$mimes['csv'] = "text/csv";
		return $mimes;
	}
	
	/**
	 * Allow multi .csv uploads
	 */
	public function upload_multi_mimes( $check, $file, $filename, $mimes ) {
		if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
			// Adjust to your needs!
			$multi_mimes = array( array( 'csv' => 'text/csv' ), array( 'csv' => 'application/vnd.ms-excel' ) );

			// Run new checks for our custom mime types and not on core mime types.
			foreach( $multi_mimes as $mime ) {
				$this->remove_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );
				$check = wp_check_filetype_and_ext( $file, $filename, $mime );
				$this->add_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );
				if ( ! empty( $check['ext'] ) ||  ! empty( $check['type'] ) ) {
					return $check;
				}
			}
		}
		return $check;
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