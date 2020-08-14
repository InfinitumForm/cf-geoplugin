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
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$this->session_type = $CF_GEOPLUGIN_OPTIONS['session_type'];
		
		if($this->session_type == 2) clear_cf_geoplugin_session(true);
		
		$this->add_action('plugins_loaded', 'load_textdomain');

		if( isset( $CF_GEOPLUGIN_OPTIONS['enable_update'] ) && $CF_GEOPLUGIN_OPTIONS['enable_update'] )
		{
			if( self::access_level( $CF_GEOPLUGIN_OPTIONS['license_sku'] ) > 0 )
			{
				$this->add_filter( 'cron_schedules', 'custom_cron_intervals' );
				$this->add_action( 'cfgp_auto_update', 'plugin_auto_update' ); 
				$this->add_action( 'plugins_loaded', 'cron_jobs' );
			}
		}
		else if( wp_next_scheduled( 'cfgp_auto_update' ) !== false )
		{
			$time = wp_next_scheduled( 'cfgp_auto_update' );
			wp_unschedule_event( $time, 'cfgp_auto_update' );
		}
		
		CF_Geoplugin_Debug::log( '------------ LOADING ALL CLASSES ------------' );
		
		// Include admin pages
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-admin.php'))
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-admin.php';
			if(class_exists('CF_Geoplugin_Admin')){
				new CF_Geoplugin_Admin;
				CF_Geoplugin_Debug::log( 'Admin class loaded' );
			}
			else CF_Geoplugin_Debug::log( 'Admin class not loaded - Class does not exists' );
		}
		else CF_Geoplugin_Debug::log( 'Admin class not loaded - File does not exists' );
		
		// Include REST
		if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-rest.php' ) )
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-rest.php';
			if( class_exists( 'CF_Geoplugin_REST' ) )
			{
				$REST = new CF_Geoplugin_REST;
				$REST->run();
				CF_Geoplugin_Debug::log( 'REST API class loaded' );
			}
			else CF_Geoplugin_Debug::log( 'REST API class not loaded - Class does not exists' );
		}
		else CF_Geoplugin_Debug::log( 'REST API class not loaded - File does not exists' );
		
		// Include COVID 19
		if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-covid-19.php' ) )
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-covid-19.php';
			if( class_exists( 'CF_Geoplugin_Covid_19' ) )
			{
				$COVID19 = new CF_Geoplugin_Covid_19;
				$COVID19->run();
				CF_Geoplugin_Debug::log( 'CF_Geoplugin_Covid_19 class loaded' );
			}
			else CF_Geoplugin_Debug::log( 'CF_Geoplugin_Covid_19 class not loaded - Class does not exists' );
		}
		else CF_Geoplugin_Debug::log( 'CF_Geoplugin_Covid_19 class not loaded - File does not exists' );

		// Include internal library
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-library.php'))
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-library.php';
			CF_Geoplugin_Debug::log( 'Library included' );
		}
		else CF_Geoplugin_Debug::log( 'Library not included - Files does not exists' );
		
		// Include API services for the CF GeoPlugin
		if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-api.php'))
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-api.php';
			if(class_exists('CF_Geoplugin_API')){
				// Do CRON job
				$this->add_action( 'cf_geo_validate', array('CF_Geoplugin_Global', 'validate'));
				// Run API
				$CFGEO_API = new CF_Geoplugin_API;
				CF_Geoplugin_Debug::log( 'API class loaded' );
				$GLOBALS['CFGEO'] = $CFGEO_API->run();
				CF_Geoplugin_Debug::log( 'API returned data:' );
				CF_Geoplugin_Debug::log( $GLOBALS['CFGEO'] );
			}
			else CF_Geoplugin_Debug::log( 'API class not loaded - Class does not exists' );
		}
		else CF_Geoplugin_Debug::log( 'API class not loaded - File does not exists' );
		
		// Allow developers to use plugin data inside PHP
		if( file_exists( CFGP_ROOT . '/globals/cf-geoplugin-api.php' ) )
		{
			include CFGP_ROOT . '/globals/cf-geoplugin-api.php';
		}
		else CF_Geoplugin_Debug::log( 'CF Geoplugin class not loaded - File does not exists' );
		
		// Include Notifications
		if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-notifications.php' ) )
		{
			include_once CFGP_INCLUDES . '/class-cf-geoplugin-notifications.php';
			if( class_exists( 'CF_Geoplugin_Notifications' ) )
			{
				new CF_Geoplugin_Notifications;
				CF_Geoplugin_Debug::log( 'Notifications class loaded' );
			}
			else CF_Geoplugin_Debug::log( 'Notification class not loaded - Class does not exists' );
		}
		else CF_Geoplugin_Debug::log( 'Notifications class not loaded - File does not exists' );

		if(isset($GLOBALS['CFGEO']) && !empty($GLOBALS['CFGEO']))
		{
			// Include Public Functions
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-public.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-public.php';
				if( class_exists( 'CF_Geoplugin_Public' ) )
				{
					$public = new CF_Geoplugin_Public;
					$public->run();
					CF_Geoplugin_Debug::log( 'Public class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Public class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Public class not loaded - File does not exists' );

			// Include Utilities
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-utilities.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-utilities.php';
				if( class_exists( 'CF_Geoplugin_Utilities' ) )
				{
					$utilities = new CF_Geoplugin_Utilities;
					$utilities->run();
					CF_Geoplugin_Debug::log( 'Utilities class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Utilities class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Shortcodes class not loaded - File does not exists' );

			// Include Texteditor Buttons
			if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-texteditor-buttons.php'))
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-texteditor-buttons.php';
				if(class_exists('CF_Geoplugin_Texteditor_Buttons')){
					new CF_Geoplugin_Texteditor_Buttons;
					CF_Geoplugin_Debug::log( 'Texteditor buttons class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Texteditor buttons class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Texteditor buttons class not loaded - File does not exists' );
			
			// Include Geo Banner
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-banner.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-banner.php';
				if( class_exists( 'CF_Geoplugin_Banner' ) )
				{
					new CF_Geoplugin_Banner;
					CF_Geoplugin_Debug::log( 'Banner class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Banner class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Banner class not loaded - File does not exists' );
			
			// Include Meta Boxes
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-metabox.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-metabox.php';
				if( class_exists( 'CF_Geoplugin_Metabox' ) )
				{
					new CF_Geoplugin_Metabox;
					CF_Geoplugin_Debug::log( 'Metabox class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Metabox class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Metabox class not loaded - File does not exists' );
			
			// Include SEO Redirection
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-seo-redirection.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-seo-redirection.php';
				if( class_exists( 'CF_Geoplugin_SEO_Redirection' ) )
				{
					new CF_Geoplugin_SEO_Redirection;
					CF_Geoplugin_Debug::log( 'SEO redirection class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'SEO redirection class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'SEO redirection class not loaded - File does not exists' );			

			// Include Defender
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-defender.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-defender.php';
				if( class_exists( 'CF_Geoplugin_Defender' ) )
				{
					new CF_Geoplugin_Defender;
					CF_Geoplugin_Debug::log( 'Defender class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Defender class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Defender class not loaded - File does not exists' );
			
			// Include Notification
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-notification.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-notification.php';
				if( class_exists( 'CF_Geoplugin_Notification' ) )
				{
					new CF_Geoplugin_Notification;
					CF_Geoplugin_Debug::log( 'Notification class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Notification class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Notification class not loaded - File does not exists' );
			
			
			
			
			// Include Shortcodes KEEP IT LAST
			if( file_exists( CFGP_INCLUDES . '/class-cf-geoplugin-shortcodes.php' ) )
			{
				include_once CFGP_INCLUDES . '/class-cf-geoplugin-shortcodes.php';
				if( class_exists( 'CF_Geoplugin_Shortcodes' ) )
				{
					$shortcodes = new CF_Geoplugin_Shortcodes;
					$shortcodes->run();
					CF_Geoplugin_Debug::log( 'Shortcodes class loaded' );
				}
				else CF_Geoplugin_Debug::log( 'Shortcodes class not loaded - Class does not exists' );
			}
			else CF_Geoplugin_Debug::log( 'Shortcodes class not loaded - File does not exists' );
			
			


			CF_Geoplugin_Debug::log( '------------ SERVER INFORMATIONS ------------' );
			CF_Geoplugin_Debug::log( var_export( $_SERVER, 1 ) );

			CF_Geoplugin_Debug::log( '------------ SESSION INFORMATIONS ------------' );
			$session_info = array();
			foreach( $_SESSION as $key => $val )
			{
				if( strpos( $key, CFGP_PREFIX ) !== false )
				{
					$session_info[ $key ] = $val;
				}
			}
			CF_Geoplugin_Debug::log( $session_info );

			CF_Geoplugin_Debug::log( '------------ DEFINED VALUES ------------' );
			$defines = array(
				'CFGP_FILE'			=> CFGP_FILE,
				'CFGP_VERSION'		=> CFGP_VERSION,
				'CFGP_ROOT'			=> CFGP_ROOT,
				'CFGP_INCLUDES'		=> CFGP_INCLUDES,
				'CFGP_ADMIN'		=> CFGP_ADMIN,
				'CFGP_URL'			=> CFGP_URL,
				'CFGP_ASSETS'		=> CFGP_ASSETS,
				'CFGP_NAME'			=> CFGP_NAME,
				'CFGP_METABOX'		=> CFGP_METABOX,
				'CFGP_PREFIX'		=> CFGP_PREFIX,
				'CFGP_STORE'		=> CFGP_STORE,
				'CFGP_LIMIT'		=> CFGP_LIMIT,
				'CFGP_DEV_MODE'		=> CFGP_DEV_MODE,
				'CFGP_MULTISITE'	=> CFGP_MULTISITE,
				'CFGP_IP'			=> CFGP_IP,
				'CFGP_SERVER_IP'	=> CFGP_SERVER_IP,
				'CFGP_PROXY'		=> CFGP_PROXY,
				'CFGP_ACTIVATED'	=> CFGP_ACTIVATED,
				'CFGP_DEFENDER_ACTIVATED'	=> CFGP_DEFENDER_ACTIVATED
			);
			CF_Geoplugin_Debug::log( $defines );

			CF_Geoplugin_Debug::log( '------------ CF GEOPLUGIN OPTIONS ------------' );
			CF_Geoplugin_Debug::log( var_export( $CF_GEOPLUGIN_OPTIONS, 1) );
		}
		
		return $GLOBALS['CFGEO'];
	}
	
	/*
	 * Save and prepare data on the activation
	*/
	public function activate(){
		global $wpdb;

		$debug = $GLOBALS['debug'];
		// Set default values
		$check = get_option('cf_geoplugin');
		
		if( parent::is_network_admin() )
			$check = get_site_option('cf_geoplugin');
		else 
			$check = get_option('cf_geoplugin');
		
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
				CF_Geoplugin_Debug::log( 'Old options found. Merged:' );
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
					CF_Geoplugin_Debug::log( $key );
					
					if( parent::is_network_admin() )
						delete_site_option( $key );
					else
						delete_option( $key );
				}
			}
			
			// Save new data
			if( parent::is_network_admin() )
				update_site_option('cf_geoplugin', $collect, true);
			else
				update_option('cf_geoplugin', $collect);
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
			wp_schedule_event(CFGP_TIME, 'twicedaily', 'cf_geo_validate');
		}

		// Create table for SEO redirections
		$table_name = $wpdb->prefix . parent::TABLE['seo_redirection'];
		$charset_collate = $wpdb->get_charset_collate();
		
		// Add new column to database IF NOT EXISTS
		if($wpdb->query("
			   SELECT 1 FROM information_schema.tables 
			   WHERE table_schema = '{$wpdb->dbname}' 
			   AND table_name = '{$table_name}'
		;"))
		{
			$list_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}");
			if(!in_array('only_once', $list_columns))
			{
				$wpdb->query( "ALTER TABLE {$table_name} ADD only_once TINYINT(1) NOT NULL DEFAULT 0" );
			}
		}

		$sql1 = "
			CREATE TABLE IF NOT EXISTS {$table_name} (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`only_once` TINYINT(1) NOT NULL DEFAULT 0,
			`country` varchar(100) NOT NULL,
			`region` varchar(100) NOT NULL,
			`city` varchar(100) NOT NULL,
			`url` varchar(100) NOT NULL,
			`http_code` SMALLINT(3) NOT NULL DEFAULT 302,
			`active` TINYINT(1) NOT NULL DEFAULT 1,
			`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`)
		  ) {$charset_collate};
		";

		// Require dbDelta to create/update table
		if( !function_exists( 'dbDelta' ) ) include ABSPATH . 'wp-admin/includes/upgrade.php';
		
		dbDelta( $sql1 );
		
		if( defined( 'CFGP_MULTISITE' ) && CFGP_MULTISITE && function_exists('flush_rewrite_rules') )
			flush_rewrite_rules();
		
		CF_Geoplugin_Debug::log( 'Plugin activated and tables created' );
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
		/*if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			if (function_exists('session_status') && session_status() != PHP_SESSION_NONE) {
				session_destroy();
			}
		}
		else
		{
			if(session_id() != '') {
				session_destroy();
			}
		}*/
		// clear CRON
		wp_clear_scheduled_hook('cf_geo_validate');
		// Set deactivated time
		$this->update_option('plugin_deactivated', time());
		// Clear auto update hook
		wp_clear_scheduled_hook( 'cfgp_auto_update' );
	}
	
	/*
	 * Custom cron intervals
	*/
	public function custom_cron_intervals( $intervals )
	{
		$intervals['five_minutes'] = array(
			'interval'		=> 5 * MINUTE_IN_SECONDS,
			'display'		=> __( 'Five Minutes', CFGP_NAME )
		);

		return $intervals;
	}

	/**
	 * Cron jobs
	 */
	public function cron_jobs()
	{
		if( !wp_next_scheduled( 'cfgp_auto_update' ) )
		{
			wp_schedule_event( time(), 'five_minutes', 'cfgp_auto_update' );
		}
	}	
}
endif;