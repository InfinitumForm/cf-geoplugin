<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Plugin Notifications
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Notifications')) :
class CF_Geoplugin_Notifications extends CF_Geoplugin_Global
{
	function __construct(){
		if(!CFGP_ACTIVATED)
		{
			if(
				isset($_GET['page']) && (
					$_GET['page'] == 'cf-geoplugin-activate' ||
					$_GET['page'] == 'cf-geoplugin-settings'
				)
			){} else {
				$this->add_action( 'plugins_loaded', 'activation_notice' );
			}
		}

		/**
		 * PHP an WP version check
		 */
		$this->add_action( 'plugins_loaded', 'version_notice' );

		$this->add_action( 'plugins_loaded', 'like_plugin' );
	}
	
	// Like Plugin
	public function like_plugin() {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if( time() >= ($CF_GEOPLUGIN_OPTIONS['plugin_activated'] + (60 * 60 * 24 * 5)))
		{
			$title = __( 'Do you like CF GeoPlugin?', CFGP_NAME );
			$message = sprintf(
				__('If you do, please give us review with the %s ',CFGP_NAME),
				'<a href="https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5#new-topic-0" target="_blank"><strong>' . __('5 stars') . '</strong></a>'
			);
			
			self::notice()->register_notice(
				'like',
				'info',
				sprintf( '<strong>%1$s</strong> <span>%2$s</span>', esc_html( $title ),  $message),
				array( 'dismissible' => true, 'scope' => 'user' )
			);
		}
	}

	// Activation notice
	public function activation_notice() {
		$CFGEO = $GLOBALS['CFGEO'];
		
		if( !current_user_can( 'activate_plugins'  ) ) return;
		
		$title = __( 'CF GEO PLUGIN', CFGP_NAME );
		$lookup = (int)$CFGEO['lookup'];
		if($lookup && $lookup > 50)
			$type = 'warning';
		else
			$type = 'error';
			
		$message1 = sprintf(
			__('You currently using free version of plugin with a limited number of lookups.<br>Each free version of this plugin is limited to %1$s lookups per day and you have only %2$s lookups available for today. If you want to have unlimited lookup, please enter your license key.<br>If you are unsure and do not understand what this is about, please read %3$s.<br><br>Also, before any action don\'t forget to read and agree with %4$s and %5$s.',CFGP_NAME),
			
			'<strong>'.CFGP_LIMIT.'</strong>',
			'<strong>'.$lookup.'</strong>',
			'<strong><a href="https://cfgeoplugin.com/new-plugin-new-features-new-success/" target="_blank">' . __('this article',CFGP_NAME) . '</a></strong>',
			'<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy',CFGP_NAME) . '</a></strong>',
			'<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions',CFGP_NAME) . '</a></strong>'
		);
		$message2 = '<a href="' . self_admin_url('/admin.php?page=cf-geoplugin-activate') . '" class="button button-primary">' . __('Activate Unlimited',CFGP_NAME) . '</a>';

		
		self::notice()->register_notice(
			'activation',
			$type,
			sprintf( '<h3>%1$s</h3><p>%2$s</p><p><strong>%3$s</strong></p>', esc_html( $title ),  $message1, $message2),
			array( 'dismissible' => false )
		);
	}

	/**
	 * Versions notice
	 */
	public function version_notice()
	{ 
		global $wp_version;
		if( !current_user_can( 'activate_plugins'  ) || ( version_compare( $wp_version, '3.0', '>=' ) && version_compare( PHP_VERSION, '5.6.0', '>=' ) ) ) return;
		global $wp_version;
		$title = __( 'CF GEO PLUGIN', CFGP_NAME );
		$type = 'error';

		$messages = array(
			'php'		=> '',
			'wp'		=> '',
			'curl'		=> '',
			'plugin'	=> '<strong><p>' . __( 'It\'s possible that plugin will not work properly.', CFGP_NAME ) . '</p></strong>',
		);

		if( !function_exists( 'curl_init' ) )
		{
			$messages['curl'] = '<p>' . __( 'cURL is NOT installed or active in your PHP installation.' ) . '</p>';
		}
		if( version_compare(  $wp_version, '3.0', '<' ) )
		{
			$messages['wp'] = '<p>' . __( 'Plugin requires at leats WordPress 3.0 version, please consider updating your WordPress site.', CFGP_NAME ) . '</p>';
		}
		if( version_compare( PHP_VERSION, '5.6.0', '<' ) )
		{
			$messages['php'] = '<p>' . __( 'Plugin requires at leats PHP 5.6.0, please consider to update your PHP version on PHP 5.6 or above.', CFGP_NAME ) . '</p>';
		}
		
		self::notice()->register_notice(
			'versions',
			$type,
			sprintf( '<h3>%s</h3>%s%s%s%s', $title, $messages['php'], $messages['wp'], $messages['curl'], $messages['plugin'] ),
			array( 'dismissible' => false )
		);
	} 
}
endif;