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
                    'desc'   => __('These settings only affect Geo Controller functionality and connection between plugin and WordPress setup. Use it smart and careful.', 'cf-geoplugin'),
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
                            'desc'    => __('Enable this option only when you use Cloudflare services on your website.', 'cf-geoplugin'),
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
                            'desc'    => __('This option force plugin to use SSL connection.', 'cf-geoplugin'),
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
                            'desc'    => __('If you use the cache plugin and have problems with caching, this option should be enabled on.', 'cf-geoplugin'),
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
                            'desc'    => __('Enable the WP Geo Controller menu in the admin bar.', 'cf-geoplugin'),
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
                            'desc'    => __('Enable the display of currency in the admin bar using WP Geo Controller.', 'cf-geoplugin'),
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
                    'desc'   => __('These settings enable advanced lookup and functionality of plugin.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'enable_dns_lookup',
                            'label'   => __('Enable DNS/ISP Lookup', 'cf-geoplugin'),
                            'desc'    => __('Activate DNS/ISP lookup to be able to provide this information.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 0,
                        ],
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
                        [
                            'name'     => 'base_currency',
                            'label'    => __('Base Currency', 'cf-geoplugin'),
                            'desc'     => __('Select your site base currency.', 'cf-geoplugin'),
                            'type'     => 'select',
                            'options'  => $currency,
                            'default'  => (get_option('woocommerce_currency') ?? 'USD'),
                            'disabled' => (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0)),
                            'info'     => (
                                (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0))
                                ? sprintf(
                                    // translators: %s is a link to WooCommerce settings page
                                    __('WooCommerce has taken over this functionality and if you want to change the base currency, you have to do it in %s.', 'cf-geoplugin'),
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
                    'desc'   => __('Here you can enable or disable features that you need. This is useful because you can disable functionality that you do not need.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'enable_menus_control',
                            'label'   => __('Enable Navigation Menus', 'cf-geoplugin'),
                            'desc'    => __('Control the display of menu items via geo location. Enable this feature and go to the navigation settings for further actions.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                            'info'    => sprintf(__('This option allows you to control Menus locations by geography. If you approve it, you will get new options within <strong>Appearance -> <a href="%s">Menus</a></strong>.', 'cf-geoplugin'), CFGP_U::admin_url('/nav-menus.php')),
                        ],
                        [
                            'name'    => 'enable_banner',
                            'label'   => __('Enable Geo Banner', 'cf-geoplugin'),
                            'desc'    => __('Display content to user by geo location.', 'cf-geoplugin'),
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
                            'desc'    => __('Place simple Google Map to your page.', 'cf-geoplugin'),
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
                            'label'   => __('Enable CSS property', 'cf-geoplugin'),
                            'desc'    => __('The Geo Controller has dynamic CSS settings that can hide or display some content if you use it properly.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'enable_js',
                            'label'   => __('Enable JavaScript property', 'cf-geoplugin'),
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
                            'desc'    => __('The Geo Controller REST API allows external apps to use geo informations from your website.', 'cf-geoplugin'),
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
                            'desc'    => __('You can redirect your visitors to other locations.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'redirect_mode',
                            'label'   => __('Redirection mode', 'cf-geoplugin'),
                            'desc'    => __('SEO redirection works differently for each server. We suggest you try one of the options as the best for your server.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Mode 1 (basic)', 'cf-geoplugin'),
                                2 => __('Mode 2 (standard)', 'cf-geoplugin'),
                                3 => __('Mode 3 (advanced)', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'enable_seo_csv',
                            'label'   => __('Enable CSV Import/Export in Site Redirection', 'cf-geoplugin'),
                            'desc'    => __('This allow you to upload CSV to your SEO redirection or download/backup SEO redirection list in the CSV.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'enable_seo_posts',
                            'label'   => __('Enable SEO Redirection in Post Types', 'cf-geoplugin'),
                            'desc'    => '',
                            'type'    => 'checkbox',
                            'options' => $seo_redirections,
                            'style'   => 'input-radio-block',
                        ],
                        [
                            'name'    => 'redirect_disable_bots',
                            'label'   => __('Disable Redirection for the Bots', 'cf-geoplugin'),
                            'desc'    => __('Disable SEO redirection for the bots, crawlers, spiders and social network bots. This can be a special case that is very important for SEO.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'hide_http_referrer_headers',
                            'label'   => __('Hide HTTP referrer headers data', 'cf-geoplugin'),
                            'desc'    => __('You can tell the browser to not send a referrer by enabling this option for all SEO redirections.', 'cf-geoplugin'),
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
                        __('With Anti Spam Protection you can enable anti spam filters and block access from the specific IP, country, state and city to your site.', 'cf-geoplugin'),
                        __('This feature is very safe and does not affect the SEO. By enabling this feature, you get full spam protection from over 60.000 blacklisted IP addresses.', 'cf-geoplugin'),
                    ],
                    'inputs' => [
                        [
                            'name'    => 'enable_defender',
                            'label'   => __('Enable Spam Protection', 'cf-geoplugin'),
                            'desc'    => __('Protect your website from the unwanted visitors by geo location or ip address.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'block_tor_network',
                            'label'   => __('TOR network control', 'cf-geoplugin'),
                            'desc'    => __('Control the access of TOR network visitors. The TOR IP list is updated every 6 hours, which means occasional IPs might bypass the protection. Note: Ensure you don’t block yourself.', 'cf-geoplugin'),
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
                            'label'   => __('Enable Automatic IP Address Blacklist Check', 'cf-geoplugin'),
                            'desc'    => __('Protect your website from bots, crawlers and other unwanted visitors that are found in our blacklist.', 'cf-geoplugin'),
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
                    'desc'   => __('The Geo Tag will help you to create your own geotags in a simple interactive way without having to deal with latitude or longitude degrees or the syntax of meta tags. Here you can enable GeoTag generators inside any post type on the your WordPress website.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'enable_geo_tag',
                            'label'   => __('Enable Geo Tag In', 'cf-geoplugin'),
                            'desc'    => '',
                            'type'    => 'checkbox',
                            'options' => $geo_tags,
                            'style'   => 'input-radio-block',
                        ],
                    ],
                ],

                [
                    'id'     => 'special-settings',
                    'title'  => __('Special Settings', 'cf-geoplugin'),
                    'desc'   => __('Special plugin settings that, in some cases, need to be changed to make some plugin systems to work properly. Many of these settings depends of your server.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'timeout',
                            'label'   => __('Set HTTP API timeout in seconds', 'cf-geoplugin'),
                            'type'    => 'number',
                            'desc'    => __('Set maximum time the request is allowed to take.', 'cf-geoplugin'),
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
                    'desc'   => __('Enter the IP addresses you want to whitelist from the SEO redirections and defender.', 'cf-geoplugin'),
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
                        __('Geo Controller sends notifications in 3 cases: 1) When you reach less than 50 lookups, 2) When the lookup expires, 3) When the license expires.', 'cf-geoplugin'),
                        sprintf(
                            __('This option is very important and cannot be turned off via these settings. But if you want to turn off this notifications, %s.', 'cf-geoplugin'),
                            '<a href="https://wpgeocontroller.com/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification" target="_blank">' . __('read this documentation', 'cf-geoplugin') . '</a>'
                        ),
                    ],
                    'inputs' => [
                        [
                            'name'    => 'notification_recipient_type',
                            'label'   => __('Who receives notifications?', 'cf-geoplugin'),
                            'desc'    => __('Select who receives notifications.', 'cf-geoplugin'),
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
                            'desc'    => __('You can always add multiple email addresses separated by comma.', 'cf-geoplugin'),
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
                    'desc'   => __('Here you can enable BETA functionality and test it. In many cases, normally you should not have any problems but some functionality is new and experimental that means if any conflict happens, you must be aware of this. If many users find this functionality useful we may keep this functionality and include it as standard functionality of Geo Controller.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'enable_beta',
                            'label'   => __('Enable BETA Features', 'cf-geoplugin'),
                            'desc'    => __('This enable/disable all BETA functionality by default.', 'cf-geoplugin'),
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
                            'desc'    => __('This allow you to use additional simple shortcode formats.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 1,
                        ],
                        [
                            'name'    => 'enable_redis_cache',
                            'label'   => __('Stick to Redis Cache only (experimental)', 'cf-geoplugin'),
                            'desc'    => __('Redis cache can sometimes cause unexpected and unwanted problems. Use this option wisely.', 'cf-geoplugin'),
                            'type'    => 'radio',
                            'display' => CFGP_U::redis_cache_exists(),
                            'options' => [
                                1 => __('Yes', 'cf-geoplugin'),
                                0 => __('No', 'cf-geoplugin'),
                            ],
                            'default' => 0,
                        ],
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
                    ],
                ],

                [
                    'id'    => 'proxy-settings',
                    'title' => __('Proxy Settings', 'cf-geoplugin'),
                    'desc'  => [
                        sprintf(
                            __('Some servers do not share real IP because of security reasons or IP is blocked from geolocation. Using proxy you can bypass that protocol and enable geoplugin to work properly. Also, this option on individual servers can cause inaccurate geo information, and because of that this option is disabled by default. You need to test this option on your side and use wise. Need proxy service? %s.', 'cf-geoplugin'),
                            '<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" class="affiliate-nordvpn" target="_blank">' . __('We have Recommended Service For You', 'cf-geoplugin') . '</a>'
                        ),
                        __('This is usually good if you use some Onion domain or you are a general user of the private web and all your websites are in the private networks.', 'cf-geoplugin'),
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
                            'desc'     => '',
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
                            'desc'     => '',
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
                            'desc'     => '',
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
                            'desc'     => '',
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
                    'desc'   => __('This settings is for Google Map API services.', 'cf-geoplugin'),
                    'inputs' => [
                        [
                            'name'    => 'map_api_key',
                            'label'   => __('Google Map API Key', 'cf-geoplugin'),
                            'type'    => 'text',
                            'desc'    => __('Google Maps JavaScript API applications require authentication.', 'cf-geoplugin'),
                            'default' => '',
                            'attr'    => [
                                'autocomplete' => 'off',
                            ],
                        ],
                        [
                            'name'    => 'map_latitude',
                            'label'   => __('Default Latitude', 'cf-geoplugin'),
                            'type'    => 'text',
                            'desc'    => __('Leave blank for Geo Controller default support or place custom value.', 'cf-geoplugin'),
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
                            'desc'    => __('Leave blank for Geo Controller default support or place custom value.', 'cf-geoplugin'),
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
                            'desc'    => __('Accept numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
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
                            'desc'    => __('Accept numeric value in percentage or pixels (% or px).', 'cf-geoplugin'),
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
                            'desc'    => __('If disabled, disables scrollwheel zooming on the map.', 'cf-geoplugin'),
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
                            'desc'    => __('If disabled, disables navigation on the map. The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'),
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
                            'desc'    => __('The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'),
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
                            'desc'    => __('The initial display options for the scale control.', 'cf-geoplugin'),
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
                            'desc'    => __('If disabled, the object can be dragged across the map and the underlying feature will have its geometry updated.', 'cf-geoplugin'),
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
                            'desc'    => __('Maximum width of info popup inside map (integer from 0 to 600).', 'cf-geoplugin'),
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
