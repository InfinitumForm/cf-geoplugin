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
	private $required_php_extensions = array();

	public function __construct( $args ) {
		foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
			if ( isset($args[$setting]) && property_exists($this, $setting) ) {
				$this->{$setting} = $args[$setting];
			}
		}
		
		if( is_admin() ) {
			$this->update_database_alert();
		}
		
		$this->required_php_extensions = array(
			'curl_version' => (object)array(
				'name' => esc_html( 'cURL', CFGP_NAME),
				'desc' => esc_html( 'cURL PHP extension', CFGP_NAME),
				'link' => esc_url('https://www.php.net/manual/en/curl.installation.php')
			),
			'mb_substr' => (object)array(
				'name' => esc_html( 'Multibyte String', CFGP_NAME),
				'desc' => esc_html( 'Multibyte String PHP extension (mbstring)', CFGP_NAME),
				'link' => esc_url('https://www.php.net/manual/en/mbstring.installation.php')
			)
		);
		
		add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", array(&$this, 'in_plugin_update_message'), 10, 2 );
	}
	
	/*
	 * Update database alert 
	 */
	private function update_database_alert() {
		$current_db_version = (get_option(CFGP_NAME . '-db-version') ?? '0.0.0');
		if( version_compare($current_db_version, CFGP_DATABASE_VERSION, '!=') ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-info" id="cf-geoplugin-database-update">';
					echo '<p><strong>'.sprintf(__('%1$s database update required!', CFGP_NAME), esc_html( $this->title ), esc_html( CFGP_DATABASE_VERSION )).'</strong></p>';
					echo '<p>'.sprintf(__('%1$s has been updated! To keep things running smoothly, we have to update your database to the newest version.', CFGP_NAME), esc_html( $this->title ), esc_html( CFGP_DATABASE_VERSION )).'</p>';
					echo '<p class="submit"><a href="'.add_query_arg([
						'cf_geoplugin_db_update' => 'true',
						'cf_geoplugin_nonce' => wp_create_nonce('cf_geoplugin_db_update')
					]).'" class="button button-primary">'.__('Update Database', CFGP_NAME).'</a></p>';
				echo '</div>';
			} );
			return false;
		}
	}
	
	/*
	 * Detect if plugin passes all checks 
	 */
	public function passes() {
		$passes = ( $this->validate_php_version() && $this->validate_wp_version() && $this->validate_php_modules() );
		if ( ! $passes ) {
			add_action( 'admin_notices', function () {
				if ( isset( $this->file ) ) {
					deactivate_plugins( plugin_basename( $this->file ) );
					wp_mail(
						get_option('admin_email'),
						sprintf(__('NOTICE: The %s is disabled for some reason!', CFGP_NAME), $this->title),
						sprintf(__("There has been some incompatibility with your server and %s is disabled.\r\n\r\nPlease visit your admin panel, go to plugins page and check what is causing this problem.", CFGP_NAME), $this->title)
					);
				}
			} );
		}
		return $passes;
	}
	
	/*
	 * Check PHP modules 
	 */
	private function validate_php_modules() {
		if(empty($this->required_php_extensions)) {
			return true;
		}
		
		$modules = array_map('function_exists', array_keys($this->required_php_extensions));
		$modules = array_filter($modules, function($m){return !empty($m);} );
		
		if ( count($modules) === count($this->required_php_extensions) ) {
			return true;
		}
		
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error">';
			printf('<p><strong>%s</strong></p><ol>', sprintf(__('%s requires the following PHP modules (extensions) to be activated:', CFGP_NAME), $this->title));
			foreach($this->required_php_extensions as $fn => $obj) {
				if( !function_exists($fn) ) {
					printf('<li>%1$s - <a href="%2s" target="_blank">%3$s</a></li>', $obj->desc, $obj->link, __('install', CFGP_NAME));
				}
			}
			echo '</ol>';
			printf('<p>%s</p>', __('Without these PHP modules you will not be able to use this plugin.', CFGP_NAME));
			printf('<p>%s</p>', __('Your hosting providers can help you to solve this problem. Contact them and request activation of the missing PHP modules.', CFGP_NAME));
			echo '</div>';
		} );
		
		return false;
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
<style media="all" id="cfgp-plugin-update-message-css">
/* <![CDATA[ */
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
/* ]]> */
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