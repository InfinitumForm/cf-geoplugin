<?php

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

add_filter('cfgp/settings', function ($options = []) {
    // Set currency
    $currency = [];

    foreach (CFGP_Defaults::CURRENCY_NAME as $currency_code => $currency_name) {
        $currency[$currency_code] = sprintf('%s - %s (%s)', $currency_code, $currency_name, (CFGP_Defaults::CURRENCY_SYMBOL[$currency_code] ?? $currency_code));
    }

    // Get post types
    $get_post_types = apply_filters('cf_geoplugin_post_types', get_post_types(
        [
            'public' => true,
        ],
        'objects'
    ));

    $seo_redirections = $geo_tags = [];

    $default_value_seo      = CFGP_Options::get('enable_seo_posts');
    $default_value_geo_tags = CFGP_Options::get('enable_geo_tag');

    foreach ($get_post_types as $i => $obj) {
        if (in_array($obj->name, [ 'attachment', 'nav_menu_item', 'custom_css', 'customize_changeset', 'user_request', 'cf-geoplugin-banner' ], true)) {
            continue;
        }

        $seo_redirections[] = [
            'label'   => $obj->label,
            'value'   => $obj->name,
            'default' => $default_value_seo,
            'id'      => sprintf('%s-seo-%s', $obj->name, $i),
        ];

        $geo_tags[] = [
            'label'   => $obj->label,
            'value'   => $obj->name,
            'default' => $default_value_geo_tags,
            'id'      => sprintf('%s-geo-%s', $obj->name, $i),
        ];

    }

    $gmap_zoom_options = [];

    for ($i = 1; $i <= 18; ++$i) {
        $gmap_zoom_options[$i] = $i;
    }

    $options = [
        // Tab
        [
            'id'    => 'general',
            'title' => __('General settings', 'cf-geoplugin'),
            // Section
            'sections' => [
                [
                    'id'     => 'wordpress-settings',
                    'title'  => __('WordPress Settings', 'cf-geoplugin'),
                    'desc'   => __('These settings only affect Geo Controller functionality and the connection between the plugin and the WordPress setup. Use them wisely and carefully.', 'cf-geoplugin'),
                    'inputs' => [
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
						[
							'name'    => 'enable_cloudflare',
							'label'   => __('Enable Cloudflare', 'cf-geoplugin'),
							'desc'    => __('Enable this option only if you are using Cloudflare services on your website.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'enable_ssl',
							'label'   => __('Enable SSL', 'cf-geoplugin'),
							'desc'    => __('This option forces the plugin to use an SSL connection.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'enable_cache',
							'label'   => __('Fix Cache', 'cf-geoplugin'),
							'desc'    => __('If you are using a cache plugin and experience caching issues, enable this option.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'enable_top_bar_menu',
							'label'   => __('Menu in Admin Bar', 'cf-geoplugin'),
							'desc'    => __('Enable the WP Geo Controller menu in the WordPress admin bar.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'enable_top_bar_currency',
							'label'   => __('Currency in Admin Bar', 'cf-geoplugin'),
							'desc'    => __('Enable the display of currency in the WordPress admin bar using WP Geo Controller.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
                    ],
                ],
                [
					'id'     => 'plugin-settings',
					'title'  => __('Plugin Settings', 'cf-geoplugin'),
					'desc'   => __('These settings enable advanced lookups and plugin functionality.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'enable_dns_lookup',
							'label'   => __('Enable DNS/ISP Lookup', 'cf-geoplugin'),
							'desc'    => __('Activate DNS/ISP lookup to provide additional information.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						/*
						[
							'name'    => 'measurement_unit',
							'label'   => __('Measurement Unit', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								'km'   => __('Kilometers (km)', 'cf-geoplugin'),
								'mile' => __('Miles (mi)', 'cf-geoplugin'),
							],
							'default' => 'km',
						],
						*/
						[
							'name'     => 'base_currency',
							'label'    => __('Base Currency', 'cf-geoplugin'),
							'desc'     => __('Select the base currency for your site.', 'cf-geoplugin'),
							'type'     => 'select',
							'options'  => $currency,
							'default'  => (get_option('woocommerce_currency') ?? 'USD'),
							'disabled' => (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0)),
							'info'     => (
								(CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0))
								? sprintf(
									// translators: %s is a link to the WooCommerce settings page
									__('WooCommerce controls this functionality. To change the base currency, please update it in %s.', 'cf-geoplugin'),
									sprintf(
										'<strong><a href="%s">%s</a></strong>',
										esc_url(CFGP_U::admin_url('/admin.php?page=wc-settings#pricing_options-description')),
										__('WooCommerce Settings', 'cf-geoplugin')
									)
								)
								: ''
							),
						],
					],
				],

                [
					'id'     => 'plugin-features',
					'title'  => __('Plugin Features', 'cf-geoplugin'),
					'desc'   => __('Here you can enable or disable the features you need. This is useful because you can turn off functionality that you do not require.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'enable_menus_control',
							'label'   => __('Enable Navigation Menus', 'cf-geoplugin'),
							'desc'    => __('Control the display of menu items by geolocation. Enable this feature and then go to the navigation settings for further configuration.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
							'info'    => sprintf(
								__('This option allows you to control menu locations by geography. Once enabled, new options will appear in <strong>Appearance → <a href="%s">Menus</a></strong>.', 'cf-geoplugin'),
								CFGP_U::admin_url('/nav-menus.php')
							),
						],
						[
							'name'    => 'enable_banner',
							'label'   => __('Enable Geo Banner', 'cf-geoplugin'),
							'desc'    => __('Display content to users based on their geolocation.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_gmap',
							'label'   => __('Enable Google Map', 'cf-geoplugin'),
							'desc'    => __('Add a simple Google Map to your page.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
							'attr'    => [
								'class' => 'enable-disable-gmap',
							],
						],
						[
							'name'    => 'enable_css',
							'label'   => __('Enable CSS Rules', 'cf-geoplugin'),
							'desc'    => __('Geo Controller provides dynamic CSS settings that can hide or display content when used properly.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_js',
							'label'   => __('Enable JavaScript Rules', 'cf-geoplugin'),
							'desc'    => __('Enable Geo Controller JavaScript support.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_rest',
							'label'   => __('Enable REST API', 'cf-geoplugin'),
							'desc'    => __('The Geo Controller REST API allows external apps to retrieve geolocation data from your website.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
							'attr'    => [
								'class' => 'enable-disable-rest',
							],
						],
					],
				],

                [
					'id'     => 'seo-redirection',
					'title'  => __('SEO Redirection', 'cf-geoplugin'),
					'desc'   => '',
					'inputs' => [
						[
							'name'    => 'enable_seo_redirection',
							'label'   => __('Enable Site Redirection', 'cf-geoplugin'),
							'desc'    => __('Redirect your visitors to other locations based on rules you configure.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'redirect_mode',
							'label'   => __('Redirection Mode', 'cf-geoplugin'),
							'desc'    => __('SEO redirection behaves differently depending on the server configuration. We recommend testing the available modes to find the one best suited for your server.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Mode 1 (Basic)', 'cf-geoplugin'),
								2 => __('Mode 2 (Standard)', 'cf-geoplugin'),
								3 => __('Mode 3 (Advanced)', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_seo_csv',
							'label'   => __('Enable CSV Import/Export for Site Redirection', 'cf-geoplugin'),
							'desc'    => __('Allow CSV uploads for SEO redirection rules, or export/backup your redirection list as a CSV file.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_seo_posts',
							'label'   => __('Enable SEO Redirection for Post Types', 'cf-geoplugin'),
							'desc'    => __('Select which post types will support SEO redirection.', 'cf-geoplugin'),
							'type'    => 'checkbox',
							'options' => $seo_redirections,
							'style'   => 'input-radio-block',
						],
						[
							'name'    => 'redirect_disable_bots',
							'label'   => __('Disable Redirection for Bots', 'cf-geoplugin'),
							'desc'    => __('Disable SEO redirection for bots, crawlers, spiders, and social network bots. This is a special case that can be very important for SEO.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'hide_http_referrer_headers',
							'label'   => __('Hide HTTP Referrer Header Data', 'cf-geoplugin'),
							'desc'    => __('Prevent the browser from sending referrer headers by enabling this option for all SEO redirections.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
					],
				],

                [
					'id'    => 'spam-protection',
					'title' => __('Spam Protection', 'cf-geoplugin'),
					'desc'  => [
						__('With Anti-Spam Protection, you can enable filters to block access from specific IPs, countries, states, and cities.', 'cf-geoplugin'),
						__('This feature is safe and does not affect SEO. By enabling it, you get full spam protection with over 60,000 blacklisted IP addresses.', 'cf-geoplugin'),
					],
					'inputs' => [
						[
							'name'    => 'enable_defender',
							'label'   => __('Enable Spam Protection', 'cf-geoplugin'),
							'desc'    => __('Protect your website from unwanted visitors by geolocation or IP address.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'block_tor_network',
							'label'   => __('TOR Network Control', 'cf-geoplugin'),
							'desc'    => __('Manage access for visitors using the TOR network. The TOR IP list is updated every 6 hours, so some IPs may occasionally bypass protection. Note: Make sure not to block yourself.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								0 => __('TOR Access: Unrestricted', 'cf-geoplugin'),
								1 => __('TOR Access: Denied', 'cf-geoplugin'),
								2 => __('TOR Access: Exclusive', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'enable_spam_ip',
							'label'   => __('Enable Automatic IP Blacklist Check', 'cf-geoplugin'),
							'desc'    => __('Protect your website from bots, crawlers, and other unwanted visitors listed in the blacklist.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
					],
				],
				
                [
					'id'     => 'geo-tag',
					'title'  => __('Geo Tag', 'cf-geoplugin'),
					'desc'   => __('The Geo Tag feature helps you create custom geotags in a simple, interactive way without dealing with latitude/longitude coordinates or meta tag syntax. Here you can enable GeoTag generators inside any post type on your WordPress website.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'enable_geo_tag',
							'label'   => __('Enable Geo Tag In', 'cf-geoplugin'),
							'desc'    => __('Select the post types where Geo Tagging should be enabled.', 'cf-geoplugin'),
							'type'    => 'checkbox',
							'options' => $geo_tags,
							'style'   => 'input-radio-block',
						],
					],
				],

				[
					'id'     => 'special-settings',
					'title'  => __('Special Settings', 'cf-geoplugin'),
					'desc'   => __('Special plugin settings that may need to be adjusted to ensure certain plugin systems work properly. Many of these settings depend on your server environment.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'timeout',
							'label'   => __('Set HTTP API Timeout (in seconds)', 'cf-geoplugin'),
							'type'    => 'number',
							'desc'    => __('Define the maximum time a request is allowed to take before it times out.', 'cf-geoplugin'),
							'default' => 10,
							'attr'    => [
								'min'  => 5,
								'max'  => 300,
								'step' => 1,
							],
						],
					],
				],


                [
					'id'     => 'whitelist-settings',
					'title'  => __('Whitelist', 'cf-geoplugin'),
					'desc'   => __('Enter the IP addresses you want to whitelist from SEO redirections and the defender.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'ip_whitelist',
							'label'   => __('IP Whitelist', 'cf-geoplugin'),
							'type'    => 'textarea',
							'desc'    => __('Separate IP addresses with a comma or a new line.', 'cf-geoplugin'),
							'default' => '',
							'attr'    => [
								'style' => 'min-height:115px',
							],
						],
					],
				],

				[
					'id'      => 'email-notification',
					'enabled' => !((defined('CFGP_DISABLE_NOTIFICATION') && CFGP_DISABLE_NOTIFICATION) === true),
					'title'   => __('E-mail Notification Settings', 'cf-geoplugin'),
					'desc'    => [
						__('Geo Controller sends notifications in three cases: 1) When fewer than 50 lookups remain, 2) When the lookups expire, 3) When the license expires.', 'cf-geoplugin'),
						sprintf(
							__('This option is important and cannot be disabled here. If you want to turn off these notifications, %s.', 'cf-geoplugin'),
							'<a href="https://wpgeocontroller.com/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification" target="_blank">' . __('read this documentation', 'cf-geoplugin') . '</a>'
						),
					],
					'inputs' => [
						[
							'name'    => 'notification_recipient_type',
							'label'   => __('Who receives notifications?', 'cf-geoplugin'),
							'desc'    => __('Select the recipients of notifications.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								'all'    => __('All administrators on this site', 'cf-geoplugin'),
								'manual' => __('All email addresses from the list below', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						[
							'name'    => 'notification_recipient_emails',
							'label'   => __('Recipient Emails', 'cf-geoplugin'),
							'type'    => 'textarea',
							'desc'    => __('Add multiple email addresses separated by commas.', 'cf-geoplugin'),
							'default' => get_bloginfo('admin_email'),
							'attr'    => [
								'autocomplete' => 'off',
								'rows'         => 2,
							],
						],
					],
				],


                [
					'id'     => 'beta',
					'title'  => __('BETA Testing & Advanced Features', 'cf-geoplugin'),
					'desc'   => __('Here you can enable and test BETA functionality. In most cases, you should not experience any issues, but since some functionality is new and experimental, conflicts may occur. If many users find these features useful, they may later be included as standard functionality in Geo Controller.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'enable_beta',
							'label'   => __('Enable BETA Features', 'cf-geoplugin'),
							'desc'    => __('Enable or disable all BETA functionality by default.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_simple_shortcode',
							'label'   => __('Enable Simple Shortcodes', 'cf-geoplugin'),
							'desc'    => __('Allow the use of additional simple shortcode formats.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'enable_redis_cache',
							'label'   => __('Use Redis Cache Only (Experimental)', 'cf-geoplugin'),
							'desc'    => __('Redis cache may sometimes cause unexpected or unwanted issues. Use this option with caution.', 'cf-geoplugin'),
							'type'    => 'radio',
							'display' => CFGP_U::redis_cache_exists(),
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
						],
						/*
						[
							'name'    => 'enable_logging',
							'label'   => __('Enable Advanced Logging', 'cf-geoplugin'),
							'desc'    => __('Log errors and warnings into your error_log file for later troubleshooting and technical support.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						*/
					],
				],


                [
					'id'    => 'proxy-settings',
					'title' => __('Proxy Settings', 'cf-geoplugin'),
					'desc'  => [
						sprintf(
							__('Some servers do not share the real IP address for security reasons, or the IP may be blocked from geolocation. By using a proxy, you can bypass these restrictions and allow Geo Controller to work properly. However, on some servers, this may cause inaccurate geo information, so the option is disabled by default. You should test it on your setup and use it carefully. Need a proxy service? %s.', 'cf-geoplugin'),
							'<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" class="affiliate-nordvpn" target="_blank">' . __('We Recommend This Service', 'cf-geoplugin') . '</a>'
						),
						__('This is especially useful if you use an Onion domain, are a general user of the private web, or run your websites in private networks.', 'cf-geoplugin'),
					],
					'inputs' => [
						[
							'name'    => 'proxy',
							'label'   => __('Enable Proxy', 'cf-geoplugin'),
							'desc'    => '',
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 0,
							'attr'    => [
								'class' => 'enable-disable-proxy',
							],
						],
						[
							'name'     => 'proxy_ip',
							'label'    => __('Proxy IP/Host', 'cf-geoplugin'),
							'type'     => 'text',
							'desc'     => __('Enter the proxy server IP address or hostname.', 'cf-geoplugin'),
							'default'  => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr'     => [
								'autocomplete' => 'off',
								'class'        => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled'),
							],
						],
						[
							'name'     => 'proxy_port',
							'label'    => __('Proxy Port', 'cf-geoplugin'),
							'type'     => 'number',
							'desc'     => __('Enter the proxy server port.', 'cf-geoplugin'),
							'default'  => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr'     => [
								'autocomplete' => 'off',
								'class'        => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled'),
								'min'          => 0,
								'max'          => 9999,
							],
						],
						[
							'name'     => 'proxy_username',
							'label'    => __('Proxy Username', 'cf-geoplugin'),
							'type'     => 'text',
							'desc'     => __('Enter the proxy username if authentication is required.', 'cf-geoplugin'),
							'default'  => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr'     => [
								'autocomplete' => 'off',
								'class'        => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled'),
							],
						],
						[
							'name'     => 'proxy_password',
							'label'    => __('Proxy Password', 'cf-geoplugin'),
							'type'     => 'password',
							'desc'     => __('Enter the proxy password if authentication is required.', 'cf-geoplugin'),
							'default'  => '',
							'disabled' => (CFGP_Options::get('proxy', 0) ? false : true),
							'attr'     => [
								'autocomplete' => 'off',
								'class'        => (CFGP_Options::get('proxy', 0) ? 'proxy-disable' : 'proxy-disable disabled'),
							],
						],
					],
				],


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

            ],
        ],

        // GOOGLE MAP
        [
			'id'    => 'google-map',
			'title' => __('Google Map', 'cf-geoplugin'),
			// Section
			'sections' => [
				[
					'id'     => 'google-map-settings',
					'title'  => __('Google Map Settings', 'cf-geoplugin'),
					'desc'   => __('These settings configure Google Map API services.', 'cf-geoplugin'),
					'inputs' => [
						[
							'name'    => 'map_api_key',
							'label'   => __('Google Map API Key', 'cf-geoplugin'),
							'type'    => 'text',
							'desc'    => __('Google Maps JavaScript API applications require an API key for authentication.', 'cf-geoplugin'),
							'default' => '',
							'attr'    => [
								'autocomplete' => 'off',
							],
						],
						[
							'name'    => 'map_latitude',
							'label'   => __('Default Latitude', 'cf-geoplugin'),
							'type'    => 'text',
							'desc'    => __('Leave blank to use Geo Controller defaults, or enter a custom value.', 'cf-geoplugin'),
							'default' => '',
							'attr'    => [
								'autocomplete' => 'off',
								'style'        => 'max-width:200px;',
							],
						],
						[
							'name'    => 'map_longitude',
							'label'   => __('Default Longitude', 'cf-geoplugin'),
							'type'    => 'text',
							'desc'    => __('Leave blank to use Geo Controller defaults, or enter a custom value.', 'cf-geoplugin'),
							'default' => '',
							'attr'    => [
								'autocomplete' => 'off',
								'style'        => 'max-width:200px;',
							],
						],
						[
							'name'    => 'map_width',
							'label'   => __('Default Map Width', 'cf-geoplugin'),
							'type'    => 'text',
							'desc'    => __('Enter a numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
							'default' => '100%',
							'attr'    => [
								'autocomplete' => 'off',
								'style'        => 'max-width:80px;',
							],
						],
						[
							'name'    => 'map_height',
							'label'   => __('Default Map Height', 'cf-geoplugin'),
							'type'    => 'text',
							'desc'    => __('Enter a numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
							'default' => '400px',
							'attr'    => [
								'autocomplete' => 'off',
								'style'        => 'max-width:80px;',
							],
						],
						[
							'name'    => 'map_zoom',
							'label'   => __('Default Max Zoom', 'cf-geoplugin'),
							'type'    => 'select',
							'desc'    => __('Most roadmap imagery is available from zoom levels 0 to 18.', 'cf-geoplugin'),
							'default' => 8,
							'options' => $gmap_zoom_options,
						],
						[
							'name'    => 'map_scrollwheel',
							'label'   => __('Zooming', 'cf-geoplugin'),
							'desc'    => __('If disabled, scrollwheel zooming on the map is turned off.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'map_navigationControl',
							'label'   => __('Navigation', 'cf-geoplugin'),
							'desc'    => __('If disabled, navigation on the map is turned off. This controls the initial enabled/disabled state of the navigation control.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'map_mapTypeControl',
							'label'   => __('Map Type Control', 'cf-geoplugin'),
							'desc'    => __('Set the initial enabled/disabled state of the map type control.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'map_scaleControl',
							'label'   => __('Scale Control', 'cf-geoplugin'),
							'desc'    => __('Set the initial display options for the scale control.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'map_draggable',
							'label'   => __('Draggable', 'cf-geoplugin'),
							'desc'    => __('If enabled, objects on the map can be dragged, and the underlying geometry will be updated.', 'cf-geoplugin'),
							'type'    => 'radio',
							'options' => [
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin'),
							],
							'default' => 1,
						],
						[
							'name'    => 'map_infoMaxWidth',
							'label'   => __('Info Box Max Width', 'cf-geoplugin'),
							'type'    => 'number',
							'desc'    => __('Set the maximum width of the info popup inside the map (integer from 0 to 600).', 'cf-geoplugin'),
							'default' => 200,
							'attr'    => [
								'autocomplete' => 'off',
								'min'          => 0,
								'max'          => 600,
							],
						],
					],
				],
			],
		],

    ];

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

}, 1, 1);
