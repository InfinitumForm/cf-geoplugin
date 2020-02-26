<?php
/**
 * Requirements Check
 *
 * Check plugin requirements
 *
 * @since         7.9.2
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CF_Geoplugin_Requirements_Check')) :
	class CF_Geoplugin_Requirements_Check {
		private $title = 'CF Geo Plugin';
		private $php = '5.6.0';
		private $wp = '3.0';
		private $file;

		public function __construct( $args ) {
			foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
				if ( isset( $args[$setting] ) ) {
					$this->$setting = $args[$setting];
				}
			}
			
			add_action( 'in_plugin_update_message-cf-geoplugin/cf-geoplugin.php', array(&$this, 'in_plugin_update_message'), 10, 2 );
		}
		
		function in_plugin_update_message($currentPluginMetadata, $newPluginMetadata){
		   if (isset($newPluginMetadata->upgrade_notice) && strlen(trim($newPluginMetadata->upgrade_notice)) > 0){
				echo '<div style="padding: 10px; color: #f9f9f9; margin-top: 10px"><h3>' . __('Important Upgrade Notice:', CFGP_NAME) . '</h3> ';
				echo $newPluginMetadata->upgrade_notice, '</div>';
		   }
		}

		public function passes() {
			$passes = $this->php_passes() && $this->wp_passes();
			if ( ! $passes ) {
				add_action( 'admin_notices', array( &$this, 'deactivate' ) );
			}
			return $passes;
		}

		public function deactivate() {
			if ( isset( $this->file ) ) {
				deactivate_plugins( plugin_basename( $this->file ) );
			}
		}

		private function php_passes() {
			if ( $this->__php_at_least( $this->php ) ) {
				return true;
			} else {
				add_action( 'admin_notices', array( &$this, 'php_version_notice' ) );
				return false;
			}
		}

		private static function __php_at_least( $min_version ) {
			return version_compare( phpversion(), $min_version, '>=' );
		}

		public function php_version_notice() {
			echo '<div class="notice notice-error">';
			echo '<p>'.sprintf(__('The %1$s cannot run on PHP versions older than PHP %2$s. Please contact your host and ask them to upgrade.', CFGP_NAME), esc_html( $this->title ), $this->php).'</p>';
			echo '</div>';
		}

		private function wp_passes() {
			if ( $this->__wp_at_least( $this->wp ) ) {
				return true;
			} else {
				add_action( 'admin_notices', array( &$this, 'wp_version_notice' ) );
				return false;
			}
		}

		private static function __wp_at_least( $min_version ) {
			return version_compare( get_bloginfo( 'version' ), $min_version, '>=' );
		}

		public function wp_version_notice() {
			echo '<div class="notice notice-error">';
			echo '<p>'.sprintf(__('The %1$s cannot run on WordPress versions older than %2$s. Please update your WordPress installation.', CFGP_NAME), esc_html( $this->title ), $this->wp).'</p>';
			echo '</div>';
		}
	}
endif;