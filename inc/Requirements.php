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

if(!class_exists('CFGP_Requirements')) :
	class CFGP_Requirements {
		private $title = 'CF Geo Plugin';
		private $php = '7.0.0';
		private $wp = '5.4';
		private $slug = 'cf-geoplugin';
		private $file;

		public function __construct( $args ) {
			foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
				if ( isset( $args[$setting] ) ) {
					$this->{$setting} = $args[$setting];
				}
			}
			
			add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", array(&$this, 'in_plugin_update_message'), 10, 2 );
		}
		
		function in_plugin_update_message($args, $response) {
			
		//	echo '<pre>', var_dump($response), '</pre>';
			
		   if (isset($response->upgrade_notice) && strlen(trim($response->upgrade_notice)) > 0) : ?>
<style>
.cf-geoplugin-upgrade-notice{
	padding: 10px;
	color: #000;
	margin-top: 10px
}
.cf-geoplugin-upgrade-notice-list ol{
	list-style-type: decimal;
	padding-left:0;
	margin-left: 15px;
}
.cf-geoplugin-upgrade-notice + p{
	display:none;
}
.cf-geoplugin-upgrade-notice-info{
	margin-top:32px;
	font-weight:600;
}
</style>
<div class="cf-geoplugin-upgrade-notice">
	<h3><?php printf(__('Important upgrade notice for the version %s:', CFGP_NAME), $response->new_version); ?></h3>
	<div class="cf-geoplugin-upgrade-notice-list">
		<?php echo str_replace(
			array(
				'<ul>',
				'</ul>'
			),array(
				'<ol>',
				'</ol>'
			),
			$response->upgrade_notice
		); ?>
	</div>
	<div class="cf-geoplugin-upgrade-notice-info">
		<?php _e('NOTE: Before doing the update, it would be a good idea to backup your WordPress installations and settings.', CFGP_NAME); ?>
	</div>
</div> 
		    <?php endif;
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