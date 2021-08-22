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

if(!class_exists('WP_List_Table')) {
	require_once ABSPATH. '/wp-admin/includes/class-wp-list-table.php';
}

if(!class_exists('CFGP_Media')) :
class CFGP_Media extends CFGP_Global {
	
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
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;