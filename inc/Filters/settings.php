<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


add_filter('cfgp/settings', function($options=array()){
	// Set currency
	$currency = array();
	foreach( CFGP_Defaults::CURRENCY_NAME as $currency_code => $currency_name ) {
		$currency[$currency_code] = sprintf('%s - %s (%s)', $currency_code, $currency_name, (CFGP_Defaults::CURRENCY_SYMBOL[$currency_code] ?? $currency_code));
	}
	
	// Get post types
	$get_post_types = apply_filters( 'cf_geoplugin_post_types', get_post_types(
		array(
			'public'	=> true,
		),
		'objects'
	));
	
	$seo_redirections = $geo_tags = array();
	
	$default_value_seo = CFGP_Options::get('enable_seo_posts');
	$default_value_geo_tags = CFGP_Options::get('enable_geo_tag');
	
	foreach( $get_post_types as $i => $obj )
	{
		if( in_array( $obj->name, array( 'attachment', 'nav_menu_item', 'custom_css', 'customize_changeset', 'user_request', 'cf-geoplugin-banner' ) ) ) continue;
	
		$seo_redirections[] = array(
			'label'		=> $obj->label,
			'value'		=> $obj->name,
			'default'	=> $default_value_seo,
			'id'		=> sprintf( '%s-seo-%s', $obj->name, $i ),
		);
	
		$geo_tags[] = array(
			'label'		=> $obj->label,
			'value'		=> $obj->name,
			'default'	=> $default_value_geo_tags,
			'id'		=> sprintf( '%s-geo-%s', $obj->name, $i ),
		);
	
	}
	
	$gmap_zoom_options=array();
	for($i=1; $i <= 18; ++$i){
		$gmap_zoom_options[$i]=$i;
	}
	
	$options = array(
		// Tab
		array(
			'id' => 'general',
			'title' => __('General settings', CFGP_NAME),
			// Section
			'sections' => array(
				array(
					'id' => 'wordpress-settings',
					'title' => __('WordPress Settings', CFGP_NAME),
					'desc' => __('These settings only affect CF Geo Plugin functionality and connection between plugin and WordPress setup. Use it smart and careful.', CFGP_NAME),
					'inputs' => array(
						/*array(
							'name' => 'enable_update',
							'label' => __('Enable Plugin Auto Update', CFGP_NAME),
							'desc' => __('Allow your plugin to be up to date.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),*/
						/*array(
							'name' => 'enable_dashboard_widget',
							'label' => __('Enable Dashboard Widget', CFGP_NAME),
							'desc' => __('Enable CF Geo Plugin widget in the dashboard area.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_advanced_dashboard_widget',
							'label' => __('Dashboard Widget Type', CFGP_NAME),
							'desc' => __('Dashboard widget comming in 2 types. You can choose that best fit to you.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Advanced (recommended)', CFGP_NAME),
								0 => __('Basic', CFGP_NAME)
							),
							'default' => 1
						),*/
						array(
							'name' => 'enable_cloudflare',
							'label' => __('Enable Cloudflare', CFGP_NAME),
							'desc' => __('Enable this option only when you use Cloudflare services on your website.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						),
						array(
							'name' => 'enable_ssl',
							'label' => __('Enable SSL', CFGP_NAME),
							'desc' => __('This option force plugin to use SSL connection.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						),
						array(
							'name' => 'enable_cache',
							'label' => __('Fix Cache', CFGP_NAME),
							'desc' => __('If you use the cache plugin and have problems with caching, this option should be enabled on.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						)					
					)
				),
				array(
					'id' => 'plugin-settings',
					'title' => __('Plugin Settings', CFGP_NAME),
					'desc' => __('These settings enable advanced lookup and functionality of plugin.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'enable_dns_lookup',
							'label' => __('Enable DNS/ISP Lookup', CFGP_NAME),
							'desc' => __('Activate DNS/ISP lookup to be able to provide this information.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						),
						/*
						array(
							'name' => 'measurement_unit',
							'label' => __('Measurement Unit', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								'km' => __('km', CFGP_NAME),
								'mile' => __('mile', CFGP_NAME)
							),
							'default' => 'km'
						),
						*/
						array(
							'name' => 'base_currency',
							'label' => __('Base Currency', CFGP_NAME),
							'desc' => __('Select your site base currency.', CFGP_NAME),
							'type' => 'select',
							'options' => $currency,
							'default' => (get_option('woocommerce_currency') ?? 'USD'),
							'disabled' => (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0) ),
							'info' => (
								( CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0) )
								? sprintf(__('WooCommerce has taken over this functionality and if you want to change the base currency, you have to do it in <strong><a href="%s">WooCommerce Settings</a></strong>.', CFGP_NAME), CFGP_U::admin_url('/admin.php?page=wc-settings#pricing_options-description'))
								: ''
							)
						)
					)
				),
				array(
					'id' => 'plugin-features',
					'title' => __('Plugin Features', CFGP_NAME),
					'desc' => __('Here you can enable or disable features that you need. This is useful because you can disable functionality that you do not need.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'enable_menus_control',
							'label' => __('Enable Navigation Menus', CFGP_NAME),
							'desc' => __('Control the display of menu items via geo location. Enable this feature and go to the navigation settings for further actions.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1,
							'info' => sprintf(__('This option allows you to control Menus locations by geography. If you approve it, you will get new options within <strong>Appearance -> <a href="%s">Menus</a></strong>.', CFGP_NAME), CFGP_U::admin_url('/nav-menus.php'))
						),
						array(
							'name' => 'enable_banner',
							'label' => __('Enable Geo Banner', CFGP_NAME),
							'desc' => __('Display content to user by geo location.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_gmap',
							'label' => __('Enable Google Map', CFGP_NAME),
							'desc' => __('Place simple Google Map to your page.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0,
							'attr' => array(
								'class' => 'enable-disable-gmap'
							)
						),
						array(
							'name' => 'enable_css',
							'label' => __('Enable CSS property', CFGP_NAME),
							'desc' => __('The CF Geo Plugin has dynamic CSS settings that can hide or display some content if you use it properly.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_js',
							'label' => __('Enable JavaScript property', CFGP_NAME),
							'desc' => __('Enable CF Geo Plugin JavaScript support.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_rest',
							'label' => __('Enable REST API', CFGP_NAME),
							'desc' => __('The CF Geo Plugin REST API allows external apps to use geo informations from your website.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0,
							'attr' => array(
								'class' => 'enable-disable-rest'
							)
						),
					)
				),
				array(
					'id' => 'seo-redirection',
					'title' => __('SEO Redirection', CFGP_NAME),
					'desc' => '',
					'inputs' => array(
						array(
							'name' => 'enable_seo_redirection',
							'label' => __('Enable Site Redirection', CFGP_NAME),
							'desc' => __('You can redirect your visitors to other locations.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_seo_csv',
							'label' => __('Enable CSV Import/Export in Site Redirection', CFGP_NAME),
							'desc' => __('This allow you to upload CSV to your SEO redirection or download/backup SEO redirection list in the CSV.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_seo_posts',
							'label' => __('Enable SEO Redirection in Post Types', CFGP_NAME),
							'desc' => '',
							'type' => 'checkbox',
							'options' => $seo_redirections,
							'style' => 'input-radio-block'
						),
						array(
							'name' => 'redirect_disable_bots',
							'label' => __('Disable Redirection for the Bots', CFGP_NAME),
							'desc' => __('Disable SEO redirection for the bots, crawlers, spiders and social network bots. This can be a special case that is very important for SEO.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'hide_http_referrer_headers',
							'label' => __('Hide HTTP referrer headers data', CFGP_NAME),
							'desc' => __('You can tell the browser to not send a referrer by enabling this option for all SEO redirections.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						),
					)
				),
				array(
					'id' => 'spam-protection',
					'title' => __('Spam Protection', CFGP_NAME),
					'desc' => array(
						__('With Anti Spam Protection you can enable anti spam filters and block access from the specific IP, country, state and city to your site.', CFGP_NAME),
						__('This feature is very safe and does not affect the SEO. By enabling this feature, you get full spam protection from over 60.000 blacklisted IP addresses.', CFGP_NAME)
					),
					'inputs' => array(
						array(
							'name' => 'enable_defender',
							'label' => __('Enable Spam Protection', CFGP_NAME),
							'desc' => __('Protect your website from the unwanted visitors by geo location or ip address.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_spam_ip',
							'label' => __('Enable Automatic IP Address Blacklist Check', CFGP_NAME),
							'desc' => __('Protect your website from bots, crawlers and other unwanted visitors that are found in our blacklist.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						),
					)
				),
	
				array(
					'id' => 'geo-tag',
					'title' => __('Geo Tag', CFGP_NAME),
					'desc' => __('The Geo Tag will help you to create your own geotags in a simple interactive way without having to deal with latitude or longitude degrees or the syntax of meta tags. Here you can enable GeoTag generators inside any post type on the your WordPress website.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'enable_geo_tag',
							'label' => __('Enable Geo Tag In', CFGP_NAME),
							'desc' => '',
							'type' => 'checkbox',
							'options' => $geo_tags,
							'style' => 'input-radio-block'
						),
					)
				),
	
				array(
					'id' => 'special-settings',
					'title' => __('Special Settings', CFGP_NAME),
					'desc' => __('Special plugin settings that, in some cases, need to be changed to make some plugin systems to work properly. Many of these settings depends of your server.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'timeout',
							'label' => __('Set HTTP API timeout in seconds', CFGP_NAME),
							'type' => 'number',
							'desc' => __('Set maximum time the request is allowed to take.', CFGP_NAME),
							'default' => 10,
							'attr' => array(
								'min' => 5,
								'max' => 300,
								'step' => 1
							)
						),
					)
				),
				
				array(
					'id' => 'email-notification',
					'enabled' => !( ( defined( 'CFGP_DISABLE_NOTIFICATION' ) && CFGP_DISABLE_NOTIFICATION ) === true),
					'title' => __('E-mail Notification Settings', CFGP_NAME),
					'desc' => array(
						__('CF Geo Plugin sends notifications in 3 cases: 1) When you reach less than 50 lookups, 2) When the lookup expires, 3) When the license expires.', CFGP_NAME),
						sprintf(
							__('This option is very important and cannot be turned off via these settings. But if you want to turn off this notifications, %s.', CFGP_NAME),
							'<a href="https://cfgeoplugin.com/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification" target="_blank">' . __('read this documentation', CFGP_NAME) . '</a>'
						)
					),
					'inputs' => array(
						array(
							'name' => 'notification_recipient_type',
							'label' => __('Who receives notifications?', CFGP_NAME),
							'desc' => __('Select who receives notifications.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								'all' => __('All administrators on this site', CFGP_NAME),
								'manual' => __('All email addresses from the list below', CFGP_NAME)
							),
							'default' => 0
						),
						array(
							'name' => 'notification_recipient_emails',
							'label' => __('Recipient Emails', CFGP_NAME),
							'type' => 'textarea',
							'desc' => __('You can always add multiple email addresses separated by comma.', CFGP_NAME),
							'default' => get_bloginfo('admin_email'),
							'attr' => array(
								'autocomplete'=>'off',
								'rows' => 2
							)
						),
					)
				),
				
				
				array(
					'id' => 'beta',
					'title' => __('BETA Testing & Advanced Features', CFGP_NAME),
					'desc' => __('Here you can enable BETA functionality and test it. In many cases, normally you should not have any problems but some functionality is new and experimental that means if any conflict happens, you must be aware of this. If many users find this functionality useful we may keep this functionality and include it as standard functionality of CF Geo Plugin.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'enable_beta',
							'label' => __('Enable BETA Features', CFGP_NAME),
							'desc' => __('This enable/disable all BETA functionality by default.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'enable_simple_shortcode',
							'label' => __('Enable Simple Shortcodes', CFGP_NAME),
							'desc' => __('This allow you to use additional simple shortcode formats.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						/*
						array(
							'name' => 'enable_logging',
							'label' => __('Enable Advanced Logging', CFGP_NAME),
							'desc' => __('This option will log any errors and warnings in your error_log file that you can later use during technical support.', CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						*/
					)
				),
				
				array(
					'id' => 'proxy-settings',
					'title' => __('Proxy Settings', CFGP_NAME),
					'desc' => array(
						sprintf(
							__('Some servers do not share real IP because of security reasons or IP is blocked from geolocation. Using proxy you can bypass that protocol and enable geoplugin to work properly. Also, this option on individual servers can cause inaccurate geo information, and because of that this option is disabled by default. You need to test this option on your side and use wise. Need proxy service? %s.', CFGP_NAME),
							'<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" class="affiliate-nordvpn" target="_blank">' . __('We have Recommended Service For You', CFGP_NAME) . '</a>'
						),
						__('This is usually good if you use some Onion domain or you are a general user of the private web and all your websites are in the private networks.', CFGP_NAME),
					),
					'inputs' => array(
						array(
							'name' => 'proxy',
							'label' => __('Enable Proxy', CFGP_NAME),
							'desc' => '',
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0,
							'attr' => array(
								'class' => 'enable-disable-proxy'
							)
						),
						array(
							'name' => 'proxy_ip',
							'label' => __('Proxy IP/Host', CFGP_NAME),
							'type' => 'text',
							'desc' => '',
							'default' => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr' => array(
								'autocomplete'=>'off',
								'class' => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled')
							)
						),
						array(
							'name' => 'proxy_port',
							'label' => __('Proxy Port', CFGP_NAME),
							'type' => 'number',
							'desc' => '',
							'default' => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr' => array(
								'autocomplete'=>'off',
								'class' => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled'),
								'min'=>0,
								'max'=>9999
							)
						),
						array(
							'name' => 'proxy_username',
							'label' => __('Proxy Username', CFGP_NAME),
							'type' => 'text',
							'desc' => '',
							'default' => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr' => array(
								'autocomplete'=>'off',
								'class' => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled')
							)
						),
						array(
							'name' => 'proxy_password',
							'label' => __('Proxy Password', CFGP_NAME),
							'type' => 'password',
							'desc' => '',
							'default' => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr' => array(
								'autocomplete'=>'off',
								'class' => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled')
							)
						),
					)
				),
				
				
				
				/* continue */
				
				
	/*
				array(
					'id' => 'SOME-ID',
					'title' => __('TITLE', CFGP_NAME),
					'desc' => __('DESCRIPTION', CFGP_NAME),
					'inputs' => array(
						
					)
				),
	*/
				
			)
		),
		
		// GOOGLE MAP
		array(
			'id' => 'google-map',
			'title' => __('Google Map', CFGP_NAME),
			// Section
			'sections' => array(
				array(
					'id' => 'google-map-settings',
					'title' => __('Google Map Settings', CFGP_NAME),
					'desc' => __('This settings is for Google Map API services.', CFGP_NAME),
					'inputs' => array(
						array(
							'name' => 'map_api_key',
							'label' => __('Google Map API Key', CFGP_NAME),
							'type' => 'text',
							'desc' => __('In some countries Google Maps JavaScript API applications require authentication.',CFGP_NAME),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
							)
						),
						array(
							'name' => 'map_latitude',
							'label' => __('Default Latitude', CFGP_NAME),
							'type' => 'text',
							'desc' => __('Leave blank for CF Geo Plugin default support or place custom value.',CFGP_NAME),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:200px;'
							)
						),
						array(
							'name' => 'map_longitude',
							'label' => __('Default Longitude', CFGP_NAME),
							'type' => 'text',
							'desc' => __('Leave blank for CF Geo Plugin default support or place custom value.',CFGP_NAME),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:200px;'
							)
						),
						array(
							'name' => 'map_width',
							'label' => __('Default Map Width', CFGP_NAME),
							'type' => 'text',
							'desc' => __('Accept numeric value in percentage or pixels (% or px).',CFGP_NAME),
							'default' => '100%',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:80px;'
							)
						),
						array(
							'name' => 'map_height',
							'label' => __('Default Map Height', CFGP_NAME),
							'type' => 'text',
							'desc' => __('Accept numeric value in percentage or pixels (% or px).',CFGP_NAME),
							'default' => '400px',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:80px;'
							)
						),
						array(
							'name' => 'map_zoom',
							'label' => __('Default Max Zoom', CFGP_NAME),
							'type' => 'select',
							'desc' => __('Most roadmap imagery is available from zoom levels 0 to 18.',CFGP_NAME),
							'default' => 8,
							'options' => $gmap_zoom_options
						),
						array(
							'name' => 'map_scrollwheel',
							'label' => __('Zooming', CFGP_NAME),
							'desc' => __('If disabled, disables scrollwheel zooming on the map.',CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'map_navigationControl',
							'label' => __('Navigation', CFGP_NAME),
							'desc' => __('If disabled, disables navigation on the map. The initial enabled/disabled state of the Map type control.',CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'map_mapTypeControl',
							'label' => __('Map Type Control', CFGP_NAME),
							'desc' => __('The initial enabled/disabled state of the Map type control.',CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'map_scaleControl',
							'label' => __('Scale Control', CFGP_NAME),
							'desc' => __('The initial display options for the scale control.',CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'map_draggable',
							'label' => __('Draggable', CFGP_NAME),
							'desc' => __('If disabled, the object can be dragged across the map and the underlying feature will have its geometry updated.',CFGP_NAME),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 1
						),
						array(
							'name' => 'map_infoMaxWidth',
							'label' => __('Info Box Max Width', CFGP_NAME),
							'type' => 'number',
							'desc' => __('Maximum width of info popup inside map (integer from 0 to 600).',CFGP_NAME),
							'default' => 200,
							'attr' => array(
								'autocomplete'=>'off',
								'min'=>0,
								'max'=>600
							)
						),
					)
				),
			)
		)
	);

/**
	// Filters by options
	foreach($options as $i => $array){
		foreach($array as $key => $field){
			// Remove Google Map
			if($key === 'id' && $field == 'google-map')
			{
				if(CFGP_Options::get('enable_gmap', 0) != 1)
				{
					unset($options[$i]);
				}
				break;
			}
		}
	}
**/

	return $options;
	
},1,0);