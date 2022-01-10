<?php
/**
 * Requirements Check
 *
 * Check plugin requirements
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Requirements')) : class CFGP_Requirements {
	private $title = 'CF Geo Plugin';
	private $php = '7.0.0';
	private $wp = '5.4';
	private $slug = 'cf-geoplugin';
	private $file;

	public function __construct( $args ) {
		foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
			if ( isset($args[$setting]) && property_exists($this, $setting) ) {
				$this->{$setting} = $args[$setting];
			}
		}
		
		add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", array(&$this, 'in_plugin_update_message'), 10, 2 );
	}
	
	/*
	 * Detect if plugin passes all checks 
	 */
	public function passes() {
		$passes = ( $this->validate_php_version() && $this->validate_wp_version() );
		if ( ! $passes ) {
			add_action( 'admin_notices', function () {
				if ( isset( $this->file ) ) {
					deactivate_plugins( plugin_basename( $this->file ) );
				}
			} );
		}
		return $passes;
	}

	/*
	 * Check PHP version 
	 */
	private function validate_php_version() {
		if ( version_compare( phpversion(), $this->php, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on PHP versions older than PHP %2$s. Please contact your host and ask them to upgrade.', CFGP_NAME), esc_html( $this->title ), $this->php).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}

	/*
	 * Check WordPress version 
	 */
	private function validate_wp_version() {
		if ( version_compare( get_bloginfo( 'version' ), $this->wp, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on WordPress versions older than %2$s. Please update your WordPress installation.', CFGP_NAME), esc_html( $this->title ), $this->wp).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}
	
	/*
	 * Check WordPress version 
	 */
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
} endif;