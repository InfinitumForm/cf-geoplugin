<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Main Plugin Class
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Init')) :
class CF_Geoplugin_Init extends CF_Geoplugin_Global
{
	/*
	 * Run all plugin functions here
	*/
	public function run(){
		$this->add_action('plugins_loaded', 'load_textdomain');
		$this->add_filter( 'auto_update_plugin', 'auto_update', 10, 2 );
		
		// Include internal library
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-library.php'))
		{
			require_once CFGP_INCLUDES . '/class-cf-geoplugin-library.php';
		}
		// Include API services for the CF GeoPlugin
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-api.php'))
		{
			require_once CFGP_INCLUDES . '/class-cf-geoplugin-api.php';
			if(class_exists('CF_Geoplugin_API')){
				// Do CRON job
				$this->add_action( 'cf_geo_validate', array('CF_Geoplugin_Global', 'validate'));
				// Run API
				$CFGEO_API = new CF_Geoplugin_API;
				$CFGEO = $CFGEO_API->run();
				$GLOBALS['CFGEO'] = $CFGEO;
			}
		}
		// Include Notifications
		if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-notifications.php' ) )
		{
			require_once CFGP_INCLUDES . '/class-cf-geoplugin-notifications.php';
			if( class_exists( 'CF_Geoplugin_Notifications' ) )
			{
				new CF_Geoplugin_Notifications;
			}
		}
		if(isset($_SESSION[CFGP_PREFIX . 'api_session']) && isset($CFGEO))
		{
			// Include WooCommerce integratin
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-woocommerce.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-woocommerce.php';
				if( class_exists( 'CF_Geoplugin_Woocommerce' ) )
				{
					new CF_Geoplugin_Woocommerce;
				}
			}
			// Include Public Functions
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-public.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-public.php';
				if( class_exists( 'CF_Geoplugin_Public' ) )
				{
					$public = new CF_Geoplugin_Public;
					$public->run();
				}
			}
			// Include Shortcodes
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-shortcodes.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-shortcodes.php';
				if( class_exists( 'CF_Geoplugin_Shortcodes' ) )
				{
					$shortcodes = new CF_Geoplugin_Shortcodes;
					$shortcodes->run();
				}
			}
			// Include Texteditor Buttons
			if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-texteditor-buttons.php'))
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-texteditor-buttons.php';
				if(class_exists('CF_Geoplugin_Texteditor_Buttons')){
					new CF_Geoplugin_Texteditor_Buttons;
				}
			}
			// Include admin pages
			if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-admin.php'))
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-admin.php';
				if(class_exists('CF_Geoplugin_Admin')){
					new CF_Geoplugin_Admin;
				}
			}
			// Include Geo Banner
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-banner.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-banner.php';
				if( class_exists( 'CF_Geoplugin_Banner' ) )
				{
					new CF_Geoplugin_Banner;
				}
			}
			// Include Meta Boxes
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-metabox.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-metabox.php';
				if( class_exists( 'CF_Geoplugin_Metabox' ) )
				{
					new CF_Geoplugin_Metabox;
				}
			}
			// Include SEO Redirection
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-seo-redirection.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-seo-redirection.php';
				if( class_exists( 'CF_Geoplugin_SEO_Redirection' ) )
				{
					new CF_Geoplugin_SEO_Redirection;
				}
			}
			
			// Include REST
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-rest.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-rest.php';
				if( class_exists( 'CF_Geoplugin_REST' ) )
				{
					$REST = new CF_Geoplugin_REST;
					$REST->run();
				}
			}

			// Include Defender
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-defender.php' ) )
			{
				require_once CFGP_INCLUDES . '/class-cf-geoplugin-defender.php';
				if( class_exists( 'CF_Geoplugin_Defender' ) )
				{
					new CF_Geoplugin_Defender;
				}
			}
		}
	}
	
	/*
	 * Save and prepare data on the activation
	*/
	public function activate(){
		global $wpdb;

		// Set default values
		$check = get_option('cf_geoplugin');
		
		if( !CFGP_MULTISITE )
			$check = get_option('cf_geoplugin');
		else 
			$check = get_site_option('cf_geoplugin');
		
		if(!is_array($check)) $check = NULL;
		
		if(empty($check))
		{
			$collect = array();
			// Collect all default values
			foreach($this->default_options as $name => $default)
			{
				$collect[$name] = $default;
			}
			// Unique plugin ID
			$collect['id']=md5($this->generate_token(32));
			// Save installed time
			$collect['plugin_installed']=time();
			// Save activation time
			$collect['plugin_activated']=time();
			// Save deactivation time
			$collect['plugin_deactivated']=0;
			
			
			// GET DEPRECATED AND REMOVE IT
			$something_old = get_option('cf_geo_enable_banner');
			if(!empty($something_old))
			{
				foreach( $this->deprecated_options as $key )
				{
					$check_deprecated = get_option($key);
					if(!empty($check_deprecated))
					{
						if(is_numeric($check_deprecated))
						{
							if(intval($check_deprecated) == $check_deprecated)
								$check_deprecated = intval($check_deprecated);
							else if(floatval($check_deprecated) == $check_deprecated)
								$check_deprecated = floatval($check_deprecated);
						}
						else
						{
							switch($check_deprecated)
							{
								case 'true': $check_deprecated = 1; break;
								case 'false': $check_deprecated = 0; break;
							}
						}
						
						$rename = str_replace('cf_geo_', '', $key);
						if(isset($this->default_options[$rename])){
							$collect[$rename] = $check_deprecated;
						}
						
					}
					delete_option( $key );
				}
			}
			
			// Save new data
			if( !CFGP_MULTISITE )
				update_option('cf_geoplugin', $collect, true);
			else
				update_site_option('cf_geoplugin', $collect);
		}
		else
		{
			// Fix missing ID
			if( isset($check['id']) )
			{
				if( empty($check['id']) ) $this->update_option('id', md5($this->generate_token(32)));
			}
			else
				$this->update_option('id', md5($this->generate_token(32)));
			
			// Update only activation time
			$this->update_option('plugin_activated', time());
			
			// ...and missing new pharams
			foreach($this->default_options as $name => $default)
			{
				if(isset($check[$name]))
					continue;
				else
					 $this->update_option($name, $default);
			}
		}
	
		// Setup CRON job
		if (! wp_next_scheduled ( 'cf_geo_validate' )) {
			wp_schedule_event(time(), 'twicedaily', 'cf_geo_validate');
		}

		// Create table for SEO redirections
		$table_name = $wpdb->prefix . self::TABLE['seo_redirection'];
		$charset_collate = $wpdb->get_charset_collate();

		$sql1 = "
			CREATE TABLE IF NOT EXISTS $table_name (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`country` varchar(100) NOT NULL,
			`region` varchar(100) NOT NULL,
			`city` varchar(100) NOT NULL,
			`url` varchar(100) NOT NULL,
			`http_code` int(3) NOT NULL DEFAULT 302,
			`active` int(1) NOT NULL DEFAULT 1,
			`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		  ) {$charset_collate};
		";

		// Require dbDelta to create/update table
		require_once ( ABSPATH . 'wp-admin/includes/upgrade.php'  );
		dbdelta( $sql1 );
	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    4.0.0
	 */
	public function load_textdomain() {
		
        $locale = apply_filters( 'plugin_locale', get_locale(), CFGP_NAME );

        if ( $loaded = load_textdomain( CFGP_NAME, CFGP_ROOT . '/languages' . '/' . CFGP_NAME . '-' . $locale . '.mo' ) ) {
            return $loaded;
        } else {
            load_plugin_textdomain( CFGP_NAME, FALSE, CFGP_ROOT . '/languages' );
        }
	}
	
	/*
	 * Deactivate plugin, save and destroy some things
	*/
	public function deactivate(){
		// destroy session
		session_destroy();
		// clear CRON
		wp_clear_scheduled_hook('cf_geo_validate');
		// Set deactivated time
		$this->update_option('plugin_deactivated', time());
	}
	
	/*
	 * Update plugin automaticaly
	*/
	public function auto_update($update, $item){
		global $CF_GEOPLUGIN_OPTIONS;
		if ( strpos($item->slug, 'cf-geoplugin') !== false && $CF_GEOPLUGIN_OPTIONS['enable_update'] == 1 ) {
			return true;
		}
		return $update;
	}
	
	
}
endif;