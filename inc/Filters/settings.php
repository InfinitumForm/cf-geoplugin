<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


add_filter('cfgp/settings', function($options=[]){
	// Set currency
	$currency = [];
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
	
	$seo_redirections = $geo_tags = [];
	
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
	
	$gmap_zoom_options=[];
	for($i=1; $i <= 18; ++$i){
		$gmap_zoom_options[$i]=$i;
	}
	
	$options = array(
		// Tab
		array(
			'id' => 'general',
			'title' => __('General settings', 'cf-geoplugin'),
			// Section
			'sections' => array(
				array(
					'id' => 'wordpress-settings',
					'title' => __('WordPress Settings', 'cf-geoplugin'),
					'desc' => __('These settings only affect Geo Controller functionality and connection between plugin and WordPress setup. Use it smart and careful.', 'cf-geoplugin'),
					'inputs' => array(
						/*array(
							'name' => 'enable_update',
							'label' => __('Enable Plugin Auto Update', 'cf-geoplugin'),
							'desc' => __('Allow your plugin to be up to date.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),*/
						/*array(
							'name' => 'enable_dashboard_widget',
							'label' => __('Enable Dashboard Widget', 'cf-geoplugin'),
							'desc' => __('Enable Geo Controller widget in the dashboard area.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_advanced_dashboard_widget',
							'label' => __('Dashboard Widget Type', 'cf-geoplugin'),
							'desc' => __('Dashboard widget comming in 2 types. You can choose that best fit to you.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Advanced (recommended)', 'cf-geoplugin'),
								0 => __('Basic', 'cf-geoplugin')
							),
							'default' => 1
						),*/
						array(
							'name' => 'enable_cloudflare',
							'label' => __('Enable Cloudflare', 'cf-geoplugin'),
							'desc' => __('Enable this option only when you use Cloudflare services on your website.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
						array(
							'name' => 'enable_ssl',
							'label' => __('Enable SSL', 'cf-geoplugin'),
							'desc' => __('This option force plugin to use SSL connection.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
						array(
							'name' => 'enable_cache',
							'label' => __('Fix Cache', 'cf-geoplugin'),
							'desc' => __('If you use the cache plugin and have problems with caching, this option should be enabled on.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
						array(
							'name' => 'enable_redis_cache',
							'label' => __('Stick to Redis Cache only (experimental)', 'cf-geoplugin'),
							'desc' => __('Redis cache can sometimes cause unexpected and unwanted problems. Use this option wisely.', 'cf-geoplugin'),
							'type' => 'radio',
							'display' => CFGP_U::redis_cache_exists(),
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						)
					)
				),
				array(
					'id' => 'plugin-settings',
					'title' => __('Plugin Settings', 'cf-geoplugin'),
					'desc' => __('These settings enable advanced lookup and functionality of plugin.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'enable_dns_lookup',
							'label' => __('Enable DNS/ISP Lookup', 'cf-geoplugin'),
							'desc' => __('Activate DNS/ISP lookup to be able to provide this information.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
						/*
						array(
							'name' => 'measurement_unit',
							'label' => __('Measurement Unit', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								'km' => __('km', 'cf-geoplugin'),
								'mile' => __('mile', 'cf-geoplugin')
							),
							'default' => 'km'
						),
						*/
						array(
							'name' => 'base_currency',
							'label' => __('Base Currency', 'cf-geoplugin'),
							'desc' => __('Select your site base currency.', 'cf-geoplugin'),
							'type' => 'select',
							'options' => $currency,
							'default' => (get_option('woocommerce_currency') ?? 'USD'),
							'disabled' => (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0) ),
							'info' => (
								( CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0) )
								? sprintf(__('WooCommerce has taken over this functionality and if you want to change the base currency, you have to do it in <strong><a href="%s">WooCommerce Settings</a></strong>.', 'cf-geoplugin'), CFGP_U::admin_url('/admin.php?page=wc-settings#pricing_options-description'))
								: ''
							)
						)
					)
				),
				array(
					'id' => 'plugin-features',
					'title' => __('Plugin Features', 'cf-geoplugin'),
					'desc' => __('Here you can enable or disable features that you need. This is useful because you can disable functionality that you do not need.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'enable_menus_control',
							'label' => __('Enable Navigation Menus', 'cf-geoplugin'),
							'desc' => __('Control the display of menu items via geo location. Enable this feature and go to the navigation settings for further actions.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1,
							'info' => sprintf(__('This option allows you to control Menus locations by geography. If you approve it, you will get new options within <strong>Appearance -> <a href="%s">Menus</a></strong>.', 'cf-geoplugin'), CFGP_U::admin_url('/nav-menus.php'))
						),
						array(
							'name' => 'enable_banner',
							'label' => __('Enable Geo Banner', 'cf-geoplugin'),
							'desc' => __('Display content to user by geo location.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_gmap',
							'label' => __('Enable Google Map', 'cf-geoplugin'),
							'desc' => __('Place simple Google Map to your page.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0,
							'attr' => array(
								'class' => 'enable-disable-gmap'
							)
						),
						array(
							'name' => 'enable_css',
							'label' => __('Enable CSS property', 'cf-geoplugin'),
							'desc' => __('The Geo Controller has dynamic CSS settings that can hide or display some content if you use it properly.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_js',
							'label' => __('Enable JavaScript property', 'cf-geoplugin'),
							'desc' => __('Enable Geo Controller JavaScript support.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_rest',
							'label' => __('Enable REST API', 'cf-geoplugin'),
							'desc' => __('The Geo Controller REST API allows external apps to use geo informations from your website.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
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
					'title' => __('SEO Redirection', 'cf-geoplugin'),
					'desc' => '',
					'inputs' => array(
						array(
							'name' => 'enable_seo_redirection',
							'label' => __('Enable Site Redirection', 'cf-geoplugin'),
							'desc' => __('You can redirect your visitors to other locations.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'redirect_mode',
							'label' => __('Redirection mode', 'cf-geoplugin'),
							'desc' => __('SEO redirection works differently for each server. We suggest you try one of the options as the best for your server.', 'cf-geoplugin'),
							'type' => 'select',
							'options' => array(
								1 => __('Mode 1 (basic)', 'cf-geoplugin'),
								2 => __('Mode 2 (standard)', 'cf-geoplugin'),
								3 => __('Mode 3 (advanced)', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_seo_csv',
							'label' => __('Enable CSV Import/Export in Site Redirection', 'cf-geoplugin'),
							'desc' => __('This allow you to upload CSV to your SEO redirection or download/backup SEO redirection list in the CSV.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_seo_posts',
							'label' => __('Enable SEO Redirection in Post Types', 'cf-geoplugin'),
							'desc' => '',
							'type' => 'checkbox',
							'options' => $seo_redirections,
							'style' => 'input-radio-block'
						),
						array(
							'name' => 'redirect_disable_bots',
							'label' => __('Disable Redirection for the Bots', 'cf-geoplugin'),
							'desc' => __('Disable SEO redirection for the bots, crawlers, spiders and social network bots. This can be a special case that is very important for SEO.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'hide_http_referrer_headers',
							'label' => __('Hide HTTP referrer headers data', 'cf-geoplugin'),
							'desc' => __('You can tell the browser to not send a referrer by enabling this option for all SEO redirections.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
					)
				),
				array(
					'id' => 'spam-protection',
					'title' => __('Spam Protection', 'cf-geoplugin'),
					'desc' => array(
						__('With Anti Spam Protection you can enable anti spam filters and block access from the specific IP, country, state and city to your site.', 'cf-geoplugin'),
						__('This feature is very safe and does not affect the SEO. By enabling this feature, you get full spam protection from over 60.000 blacklisted IP addresses.', 'cf-geoplugin')
					),
					'inputs' => array(
						array(
							'name' => 'enable_defender',
							'label' => __('Enable Spam Protection', 'cf-geoplugin'),
							'desc' => __('Protect your website from the unwanted visitors by geo location or ip address.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'block_tor_network',
							'label' => __('Block TOR visitors (experimental)', 'cf-geoplugin'),
							'desc' => __('Block visits to the entire site for visitors from the TOR network. The TOR list is updated every 6 hours, so it may happen that certain IP addresses do not pass the protection.'."\r\n\r\n".'WARNING: Be careful not to block yourself if you use the TOR network.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
						array(
							'name' => 'enable_spam_ip',
							'label' => __('Enable Automatic IP Address Blacklist Check', 'cf-geoplugin'),
							'desc' => __('Protect your website from bots, crawlers and other unwanted visitors that are found in our blacklist.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						),
					)
				),
	
				array(
					'id' => 'geo-tag',
					'title' => __('Geo Tag', 'cf-geoplugin'),
					'desc' => __('The Geo Tag will help you to create your own geotags in a simple interactive way without having to deal with latitude or longitude degrees or the syntax of meta tags. Here you can enable GeoTag generators inside any post type on the your WordPress website.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'enable_geo_tag',
							'label' => __('Enable Geo Tag In', 'cf-geoplugin'),
							'desc' => '',
							'type' => 'checkbox',
							'options' => $geo_tags,
							'style' => 'input-radio-block'
						),
					)
				),
	
				array(
					'id' => 'special-settings',
					'title' => __('Special Settings', 'cf-geoplugin'),
					'desc' => __('Special plugin settings that, in some cases, need to be changed to make some plugin systems to work properly. Many of these settings depends of your server.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'timeout',
							'label' => __('Set HTTP API timeout in seconds', 'cf-geoplugin'),
							'type' => 'number',
							'desc' => __('Set maximum time the request is allowed to take.', 'cf-geoplugin'),
							'default' => 10,
							'attr' => array(
								'min' => 5,
								'max' => 300,
								'step' => 1
							)
						)
					)
				),
				
				
				array(
					'id' => 'whitelist-settings',
					'title' => __('Whitelist', 'cf-geoplugin'),
					'desc' => __('Enter the IP addresses you want to whitelist from the SEO redirections and defender.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'ip_whitelist',
							'label' => __('IP Whitelist', 'cf-geoplugin'),
							'type' => 'textarea',
							'desc' => __('Separate IP addresses with a comma or a new line.', 'cf-geoplugin'),
							'default' => '',
							'attr' => array(
								'style' => 'min-height:115px',
							)
						),
					)
				),
				
				
				array(
					'id' => 'email-notification',
					'enabled' => !( ( defined( 'CFGP_DISABLE_NOTIFICATION' ) && CFGP_DISABLE_NOTIFICATION ) === true),
					'title' => __('E-mail Notification Settings', 'cf-geoplugin'),
					'desc' => array(
						__('Geo Controller sends notifications in 3 cases: 1) When you reach less than 50 lookups, 2) When the lookup expires, 3) When the license expires.', 'cf-geoplugin'),
						sprintf(
							__('This option is very important and cannot be turned off via these settings. But if you want to turn off this notifications, %s.', 'cf-geoplugin'),
							'<a href="https://cfgeoplugin.com/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification" target="_blank">' . __('read this documentation', 'cf-geoplugin') . '</a>'
						)
					),
					'inputs' => array(
						array(
							'name' => 'notification_recipient_type',
							'label' => __('Who receives notifications?', 'cf-geoplugin'),
							'desc' => __('Select who receives notifications.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								'all' => __('All administrators on this site', 'cf-geoplugin'),
								'manual' => __('All email addresses from the list below', 'cf-geoplugin')
							),
							'default' => 0
						),
						array(
							'name' => 'notification_recipient_emails',
							'label' => __('Recipient Emails', 'cf-geoplugin'),
							'type' => 'textarea',
							'desc' => __('You can always add multiple email addresses separated by comma.', 'cf-geoplugin'),
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
					'title' => __('BETA Testing & Advanced Features', 'cf-geoplugin'),
					'desc' => __('Here you can enable BETA functionality and test it. In many cases, normally you should not have any problems but some functionality is new and experimental that means if any conflict happens, you must be aware of this. If many users find this functionality useful we may keep this functionality and include it as standard functionality of Geo Controller.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'enable_beta',
							'label' => __('Enable BETA Features', 'cf-geoplugin'),
							'desc' => __('This enable/disable all BETA functionality by default.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'enable_simple_shortcode',
							'label' => __('Enable Simple Shortcodes', 'cf-geoplugin'),
							'desc' => __('This allow you to use additional simple shortcode formats.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						/*
						array(
							'name' => 'enable_logging',
							'label' => __('Enable Advanced Logging', 'cf-geoplugin'),
							'desc' => __('This option will log any errors and warnings in your error_log file that you can later use during technical support.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						*/
					)
				),
				
				array(
					'id' => 'proxy-settings',
					'title' => __('Proxy Settings', 'cf-geoplugin'),
					'desc' => array(
						sprintf(
							__('Some servers do not share real IP because of security reasons or IP is blocked from geolocation. Using proxy you can bypass that protocol and enable geoplugin to work properly. Also, this option on individual servers can cause inaccurate geo information, and because of that this option is disabled by default. You need to test this option on your side and use wise. Need proxy service? %s.', 'cf-geoplugin'),
							'<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" class="affiliate-nordvpn" target="_blank">' . __('We have Recommended Service For You', 'cf-geoplugin') . '</a>'
						),
						__('This is usually good if you use some Onion domain or you are a general user of the private web and all your websites are in the private networks.', 'cf-geoplugin'),
					),
					'inputs' => array(
						array(
							'name' => 'proxy',
							'label' => __('Enable Proxy', 'cf-geoplugin'),
							'desc' => '',
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0,
							'attr' => array(
								'class' => 'enable-disable-proxy'
							)
						),
						array(
							'name' => 'proxy_ip',
							'label' => __('Proxy IP/Host', 'cf-geoplugin'),
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
							'label' => __('Proxy Port', 'cf-geoplugin'),
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
							'label' => __('Proxy Username', 'cf-geoplugin'),
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
							'label' => __('Proxy Password', 'cf-geoplugin'),
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
					'title' => __('TITLE', 'cf-geoplugin'),
					'desc' => __('DESCRIPTION', 'cf-geoplugin'),
					'inputs' => array(
						
					)
				),
	*/
				
			)
		),
		
		// GOOGLE MAP
		array(
			'id' => 'google-map',
			'title' => __('Google Map', 'cf-geoplugin'),
			// Section
			'sections' => array(
				array(
					'id' => 'google-map-settings',
					'title' => __('Google Map Settings', 'cf-geoplugin'),
					'desc' => __('This settings is for Google Map API services.', 'cf-geoplugin'),
					'inputs' => array(
						array(
							'name' => 'map_api_key',
							'label' => __('Google Map API Key', 'cf-geoplugin'),
							'type' => 'text',
							'desc' => __('Google Maps JavaScript API applications require authentication.', 'cf-geoplugin'),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
							)
						),
						array(
							'name' => 'map_latitude',
							'label' => __('Default Latitude', 'cf-geoplugin'),
							'type' => 'text',
							'desc' => __('Leave blank for Geo Controller default support or place custom value.', 'cf-geoplugin'),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:200px;'
							)
						),
						array(
							'name' => 'map_longitude',
							'label' => __('Default Longitude', 'cf-geoplugin'),
							'type' => 'text',
							'desc' => __('Leave blank for Geo Controller default support or place custom value.', 'cf-geoplugin'),
							'default' => '',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:200px;'
							)
						),
						array(
							'name' => 'map_width',
							'label' => __('Default Map Width', 'cf-geoplugin'),
							'type' => 'text',
							'desc' => __('Accept numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
							'default' => '100%',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:80px;'
							)
						),
						array(
							'name' => 'map_height',
							'label' => __('Default Map Height', 'cf-geoplugin'),
							'type' => 'text',
							'desc' => __('Accept numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
							'default' => '400px',
							'attr' => array(
								'autocomplete'=>'off',
								'style'=>'max-width:80px;'
							)
						),
						array(
							'name' => 'map_zoom',
							'label' => __('Default Max Zoom', 'cf-geoplugin'),
							'type' => 'select',
							'desc' => __('Most roadmap imagery is available from zoom levels 0 to 18.', 'cf-geoplugin'),
							'default' => 8,
							'options' => $gmap_zoom_options
						),
						array(
							'name' => 'map_scrollwheel',
							'label' => __('Zooming', 'cf-geoplugin'),
							'desc' => __('If disabled, disables scrollwheel zooming on the map.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'map_navigationControl',
							'label' => __('Navigation', 'cf-geoplugin'),
							'desc' => __('If disabled, disables navigation on the map. The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'map_mapTypeControl',
							'label' => __('Map Type Control', 'cf-geoplugin'),
							'desc' => __('The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'map_scaleControl',
							'label' => __('Scale Control', 'cf-geoplugin'),
							'desc' => __('The initial display options for the scale control.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'map_draggable',
							'label' => __('Draggable', 'cf-geoplugin'),
							'desc' => __('If disabled, the object can be dragged across the map and the underlying feature will have its geometry updated.', 'cf-geoplugin'),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 1
						),
						array(
							'name' => 'map_infoMaxWidth',
							'label' => __('Info Box Max Width', 'cf-geoplugin'),
							'type' => 'number',
							'desc' => __('Maximum width of info popup inside map (integer from 0 to 600).', 'cf-geoplugin'),
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
	
},1,1);