<?php
/**
 * Settings page
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       3.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Admin', false)) :
    class CFGP_Admin extends CFGP_Global
    {
        public function __construct()
        {
            $this->add_action('admin_bar_menu', 'admin_bar_menu', 90, 1);
            $this->add_action('wp_footer', 'admin_bar_menu_css', 90, 1);
            $this->add_action('admin_footer', 'admin_bar_menu_css', 90, 1);

            $this->add_action('admin_enqueue_scripts', 'register_scripts');
            $this->add_action('admin_enqueue_scripts', 'register_scripts_ctp');
            $this->add_action('admin_enqueue_scripts', 'register_style');
            $this->add_action('admin_init', 'admin_init');

            $this->add_action('manage_edit-cf-geoplugin-country_columns', 'rename__cf_geoplugin_country__column');
            $this->add_action('manage_edit-cf-geoplugin-region_columns', 'rename__cf_geoplugin_region__column');
            $this->add_action('manage_edit-cf-geoplugin-city_columns', 'rename__cf_geoplugin_city__column');
            $this->add_action('manage_edit-cf-geoplugin-postcode_columns', 'rename__cf_geoplugin_postcode__column');

            $this->add_action('wp_ajax_cfgp_rss_feed', 'ajax__rss_feed');
            $this->add_action('wp_ajax_cfgp_dashboard_rss_feed', 'ajax__dashboard_rss_feed');

            $this->add_action('wp_network_dashboard_setup', 'register_dashboard_widget');
            $this->add_action('wp_dashboard_setup', 'register_dashboard_widget');

            $this->add_filter('plugin_action_links_' . plugin_basename(CFGP_FILE), 'plugin_action_links');
            $this->add_filter('plugin_row_meta', 'cfgp_action_links', 10, 2);

            $this->add_action('wp_ajax_cfgp_select2_locations', ['CFGP_Library', 'ajax__select2_locations']);
            $this->add_action('wp_ajax_nopriv_cfgp_select2_locations', ['CFGP_Library', 'ajax__select2_locations']);

            // Update database
            if (is_admin()) {
                $this->add_action('plugins_loaded', 'update_database', 20, 0);
            }
        }

        // Update database
        public function update_database()
        {
            if (
                ($_GET['cf_geoplugin_db_update'] ?? null) == 'true'
                && wp_verify_nonce(($_GET['cf_geoplugin_nonce'] ?? null), 'cf_geoplugin_db_update')
            ) {
                ## Create database table for the Cache
                CFGP_DB_Cache::table_install();
                ## Create database table for the REST tokens
                CFGP_REST::table_install();
                ## Create database table for the SEO redirection if plugin is new
                CFGP_SEO_Table::table_install();
                // Update database version
                update_option(CFGP_NAME . '-db-version', CFGP_DATABASE_VERSION, false);

                $url = remove_query_arg('cf_geoplugin_db_update');
                $url = remove_query_arg('cf_geoplugin_nonce', $url);

                if (wp_safe_redirect($url)) {
                    exit;
                }
            }
        }

        // WP Hidden links by plugin setting page
        public function plugin_action_links($links)
        {
            $mylinks = [
                'settings'      => sprintf('<a href="' . esc_url(self_admin_url('admin.php?page=' . CFGP_NAME . '-settings')) . '" class="cfgeo-plugins-action-settings">%s</a>', esc_html__('Settings', 'cf-geoplugin')),
                'documentation' => sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-documentation">%s</a>', esc_url(CFGP_STORE . '/documentation/'), esc_html__('Documentation', 'cf-geoplugin')),
            ];

            return array_merge($links, $mylinks);
        }

        // Plugin action links after details
        public function cfgp_action_links($links, $file)
        {
            if (plugin_basename(CFGP_FILE) == $file) {
                $row_meta = [
                /*	'cfgp_donate' => sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-donation">%s</a>',
                        esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com' ),
                        esc_html__( 'Donate', 'cf-geoplugin')
                    ),	*/
                    'cfgp_vote' => sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-vote" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> %s</a>',
                        esc_url('https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5'),
                        esc_attr__('Give us five if you like!', 'cf-geoplugin'),
                        esc_html__('5 Stars?', 'cf-geoplugin')
                    ),
                ];

                $links = array_merge($links, $row_meta);
            }

            return $links;
        }

        public function register_dashboard_widget()
        {
            if (get_current_screen()->base !== 'dashboard') {
                return;
            }

            wp_add_dashboard_widget(
                CFGP_NAME . '-dashboard-statistic',
                __('Geo Controller', 'cf-geoplugin'),
                function () {
                    do_action('cfgp/dashboard/widget/statistic');
                },
                null,
                null,
                'normal',
                'high'
            );

            wp_add_dashboard_widget(
                CFGP_NAME . '-dashboard-feed',
                __('Geo Controller Live News & Info', 'cf-geoplugin'),
                function () {
                    add_action('admin_footer', function () { ?>
<script id="cfgp-rss-feed-js" type="text/javascript">
/* <![CDATA[ */
(function(jCFGP){$feed=jCFGP('.cfgp-load-dashboard-rss-feed');if($feed.length>0){jCFGP.ajax({url:"<?php echo esc_url(admin_url('/admin-ajax.php')); ?>",method:'post',accept:'text/html',data:{action:'cfgp_dashboard_rss_feed'},cache:true}).done(function(data){$feed.html(data).removeClass('cfgp-load-dashboard-rss-feed');});}}(jQuery||window.jQuery));
/* ]]> */
</script>
				<?php }, 99);
                    do_action('cfgp/dashboard/widget/feed');
                },
                null,
                null,
                'normal'
            );
        }

        public function ajax__rss_feed()
        {
            $RSS = CFGP_DB_Cache::get('cfgp-rss');

            if (!empty($RSS)) {
                echo wp_kses_post($RSS ?? '');
                exit;
            } else {
                $RSS  = $DASH_RSS = [];
                $data = CFGP_U::curl_get(CFGP_STORE . '/wp-json/cfgp/v1/news?posts_per_page=4', '', [], false);

                if ($data) {
                    $data = (object)$data;

                    if (isset($data->posts) && is_array($data->posts)) {
                        $x = 4;

                        foreach ($data->posts as $i => $post) {
                            $post = (object)$post;

                            $DASH_RSS[] = sprintf('<li><a href="%1$s" target="_blank">%2$s</a></li>', esc_url($post->post_url), esc_html($post->post_title));

                            if ($i <= $x) {
                                if ($i === 0) {
                                    $RSS[] = sprintf(
                                        '<div class="cfgp-rss-container">
										<a href="%1$s" target="_blank" class="cfgp-rss-img">
											<img src="%3$s" class="img-fluid">
										</a>
										<h3>%2$s</h3>
										<div class="cfgp-rss-excerpt">
											%4$s
										</div>
										<a href="%1$s" target="_blank" class="cfgp-rss-link">%6$s</a><br>
										<small class="cfgp-rss-date">~ %7$s</small>
									</div>',
                                        esc_url($post->post_url),
                                        esc_html($post->post_title),
                                        esc_url($post->post_image_medium),
                                        esc_html($post->post_excerpt),
                                        esc_url($post->post_url),
                                        __('Read more at Geo Controller site', 'cf-geoplugin'),
                                        date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
                                    );
                                } else {
                                    $RSS[] = sprintf(
                                        '<p class="cfgp-rss-container"><a href="%1$s" target="_blank" class="cfgp-rss-link">%2$s</a><br><small class="cfgp-rss-date">~ %7$s</small></p>',
                                        esc_url($post->post_url),
                                        esc_html($post->post_title),
                                        esc_url($post->post_image_medium),
                                        esc_html($post->post_excerpt),
                                        esc_url($post->post_url),
                                        __('Read more at Geo Controller site', 'cf-geoplugin'),
                                        date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
                                    );
                                }
                            }
                        }
                    }
                }

                if (!empty($DASH_RSS)) {
                    $DASH_RSS = '<ul class="rss-widget">' . join("\r\n", $DASH_RSS) . '</ul>';
                    CFGP_DB_Cache::set('cfgp-dashboard-rss', $DASH_RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
                }

                if (!empty($RSS)) {
                    $RSS = join("\r\n", $RSS);
                    CFGP_DB_Cache::set('cfgp-rss', $RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
                    echo wp_kses_post($RSS ?? '');
                    exit;
                }
            }

            esc_html_e('No news for today.', 'cf-geoplugin');
            exit;
        }

        public function ajax__dashboard_rss_feed()
        {
            $DASH_RSS = CFGP_DB_Cache::get('cfgp-dashboard-rss');

            if (!empty($DASH_RSS)) {
                echo wp_kses_post($DASH_RSS ?? '');
                exit;
            } else {
                $RSS  = $DASH_RSS = [];
                $data = CFGP_U::curl_get(CFGP_STORE . '/wp-json/cfgp/v1/news?posts_per_page=10', '', [], false);

                if ($data) {
                    $data = (object)$data;

                    if (isset($data->posts) && is_array($data->posts)) {
                        $x = 4;

                        foreach ($data->posts as $i => $post) {
                            $post = (object)$post;

                            $post->post_url          = sanitize_url($post->post_url ?? null);
                            $post->post_title        = sanitize_text_field($post->post_title ?? null);
                            $post->post_date_gmt     = sanitize_text_field($post->post_date_gmt ?? null);
                            $post->post_image_medium = sanitize_url($post->post_image_medium ?? null);
                            $post->post_excerpt      = wp_kses_post(sanitize_textarea_field($post->post_excerpt ?? ''));

                            $DASH_RSS[] = sprintf('<li><a href="%1$s" target="_blank">%2$s</a></li>', esc_url($post->post_url), esc_html($post->post_title));

                            if ($i <= $x) {
                                if ($i === 0) {
                                    $RSS[] = sprintf(
                                        '<div class="cfgp-rss-container">
										<a href="%1$s" target="_blank" class="cfgp-rss-img">
											<img src="%3$s" class="img-fluid">
										</a>
										<h3>%2$s</h3>
										<div class="cfgp-rss-excerpt">
											%4$s
										</div>
										<a href="%1$s" target="_blank" class="cfgp-rss-link">%6$s</a><br>
										<small class="cfgp-rss-date">~ %7$s</small>
									</div>',
                                        esc_url($post->post_url),
                                        esc_html($post->post_title),
                                        esc_url($post->post_image_medium),
                                        esc_html($post->post_excerpt),
                                        esc_url($post->post_url),
                                        __('Read more at Geo Controller site', 'cf-geoplugin'),
                                        date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
                                    );
                                } else {
                                    $RSS[] = sprintf(
                                        '<p class="cfgp-rss-container"><a href="%1$s" target="_blank" class="cfgp-rss-link">%2$s</a><br><small class="cfgp-rss-date">~ %7$s</small></p>',
                                        esc_url($post->post_url),
                                        esc_html($post->post_title),
                                        esc_url($post->post_image_medium),
                                        esc_html($post->post_excerpt),
                                        esc_url($post->post_url),
                                        __('Read more at Geo Controller site', 'cf-geoplugin'),
                                        date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
                                    );
                                }
                            }
                        }
                    }
                }

                if (!empty($RSS)) {
                    $RSS = join("\r\n", $RSS);
                    CFGP_DB_Cache::set('cfgp-rss', $RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));

                }

                if (!empty($DASH_RSS)) {
                    $DASH_RSS = '<ul class="rss-widget">' . join("\r\n", $DASH_RSS) . '</ul>';
                    CFGP_DB_Cache::set('cfgp-dashboard-rss', $DASH_RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
                    echo wp_kses_post($DASH_RSS ?? '');
                    exit;
                }
            }

            esc_html_e('No news for today.', 'cf-geoplugin');
            exit;
        }

        // Rename county table
        public function rename__cf_geoplugin_country__column($theme_columns)
        {
            $theme_columns['name']        = __('Country code', 'cf-geoplugin');
            $theme_columns['description'] = __('Country full name', 'cf-geoplugin');

            return $theme_columns;
        }

        // Rename region table
        public function rename__cf_geoplugin_region__column($theme_columns)
        {
            $theme_columns['name']        = __('Region code', 'cf-geoplugin');
            $theme_columns['description'] = __('Region full name', 'cf-geoplugin');

            return $theme_columns;
        }

        // Rename city table
        public function rename__cf_geoplugin_city__column($theme_columns)
        {
            $theme_columns['name'] = __('City name', 'cf-geoplugin');
            unset($theme_columns['description']);

            return $theme_columns;
        }

        // Rename postcode table
        public function rename__cf_geoplugin_postcode__column($theme_columns)
        {
            $theme_columns['name'] = __('Postcode', 'cf-geoplugin');
            unset($theme_columns['description']);

            return $theme_columns;
        }

        // Initialize plugin settings
        public function admin_init()
        {
            $this->plugin_custom_menu_class();
            $this->add_privacy_policy();
        }

        // Add privacy policy content
        public function add_privacy_policy()
        {
            if (!function_exists('wp_add_privacy_policy_content')) {
                return;
            }

            $privacy_policy = [
                __('This site uses the Geo Controller (known as Geo Controller) to display public visitor information based on IP addresses that can then be collected or used for various purposes depending on the settings of the plugin.', 'cf-geoplugin'),

                __('Geo Controller is a Geomarketing tool that allows you to have full geo control of your WordPress. Geo Controller gives you the ability to attach content, geographic information, geo tags, Google Maps to posts, pages, widgets and custom templates by using simple options, shortcodes, PHP code or JavaScript. It also lets you specify a default geographic location for your entire WordPress blog, do SEO redirection, spam protection, WooCommerce control and many more. Geo Controller help you to increase conversion, do better SEO, capture leads on your blog or landing pages.', 'cf-geoplugin'),

                sprintf(__('This website uses API services, technology and goods from the Geo Controller and that part belongs to the <a href="%1$s" target="_blank">Geo Controller Privacy Policy</a>.', 'cf-geoplugin'), CFGP_STORE . '/privacy-policy/'),
            ];

            wp_add_privacy_policy_content(
                __('Geo Controller', 'cf-geoplugin'),
                wp_kses_post(wpautop(join((PHP_EOL . PHP_EOL), $privacy_policy), false))
            );
        }

        // Fix collapsing admin menu
        public function plugin_custom_menu_class()
        {
            global $menu;

            $show = false;

            if (isset($_GET['post_type'])) {
                $show = $this->limit_scripts($_GET['post_type']);
            } // This will also check for taxonomies

            if (is_array($menu) && $show) {
                foreach ($menu as $key => $value) {
                    if ($value[0] == 'Geo Controller') {
                        $menu[$key][4] = 'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_cf-geoplugin menu-top-first wp-menu-open';
                    }
                }
            }
        }

        // Admin bar CSS
        public function admin_bar_menu_css()
        {
            if (is_admin_bar_showing()) :

                if (!(apply_filters('cfgp/topbar/menu/display', (CFGP_Options::get('enable_top_bar_menu', 1) == 1)) || (
                    apply_filters('cfgp/topbar/currency/display', (CFGP_Options::get('enable_top_bar_currency', 1) == 1))
                    && ($currency_converter = CFGP_U::api('currency_converter'))
                    && (CFGP_Options::get('base_currency') != CFGP_U::api('currency'))
                ))) {
                    return;
                }

                ?>	
<style media="all" id="cfgp-admin-bar-css">
/* <![CDATA[ */
<?php if (apply_filters('cfgp/topbar/menu/display', (CFGP_Options::get('enable_top_bar_menu', 1) == 1))) : ?>
#wpadminbar .ab-top-menu .menupop.<?php echo esc_attr(CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'); ?> .ab-item > .cfgp-ab-icon:before {
	font: normal 20px/1 dashicons;
    content: '\f231';
	position: relative;
    float: left;
    speak: never;
    padding: 4px 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    background-image: none !important;
    margin-right: 6px;
	color: #a7aaad;
    color: rgba(240, 246, 252, 0.6);
}
#wpadminbar .ab-top-menu .menupop .<?php echo esc_attr(CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-activate-link'); ?> .ab-item > .cfgp-ab-icon:before{
	content: '\f155';
}
#wpadminbar .ab-top-menu .menupop.<?php echo esc_attr(CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'); ?>:hover .ab-item > .cfgp-ab-icon:before,
#wpadminbar .ab-top-menu .menupop.<?php echo esc_attr(CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'); ?>.hover .ab-item > .cfgp-ab-icon:before {
	color: #72aee6;
}
<?php endif;

                if (
                    apply_filters('cfgp/topbar/currency/display', (CFGP_Options::get('enable_top_bar_currency', 1) == 1))
                    && ($currency_converter = CFGP_U::api('currency_converter'))
                    && (CFGP_Options::get('base_currency') != CFGP_U::api('currency'))
                ) : ?>
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course,
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course:focus,
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course:hover,
#wpadminbar:not(.mobile) .ab-top-menu > li.cf-geoplugin-toolbar-course > .ab-item:focus,
#wpadminbar.nojq .quicklinks .ab-top-menu > li.cf-geoplugin-toolbar-course > .ab-item:focus,
#wpadminbar:not(.mobile) .ab-top-menu > li.cf-geoplugin-toolbar-course:hover > .ab-item,
#wpadminbar .ab-top-menu > li.cf-geoplugin-toolbar-course.hover > .ab-item{
	background: #443333;
	color: rgba(240, 246, 252, 1);
}
<?php endif; ?>
/* ]]> */
</style><?php endif;
        }

        // Add admin top bar menu pages
        public function admin_bar_menu($wp_admin_bar)
        {

            if (!(current_user_can('administrator') || current_user_can('editor'))) {
                return $wp_admin_bar;
            }

            if (apply_filters('cfgp/topbar/menu/display', (CFGP_Options::get('enable_top_bar_menu', 1) == 1))) {
                $wp_admin_bar->add_node([
                    'id'    => CFGP_NAME . '-admin-bar-link',
                    'title' => '<span class="cfgp-ab-icon"></span>' . __('Geo Controller', 'cf-geoplugin'),
                    'href'  => esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin')),
                    'meta'  => [
                        'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-link',
                        'title' => __('Geo Controller', 'cf-geoplugin'),
                    ],
                ]);

                $wp_admin_bar->add_menu([
                    'parent' => CFGP_NAME . '-admin-bar-link',
                    'id'     => CFGP_NAME . '-admin-bar-shortcodes-link',
                    'title'  => __('Shortcodes', 'cf-geoplugin'),
                    'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME)),
                    'meta'   => [
                        'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-shortcodes-link',
                        'title' => __('Shortcodes', 'cf-geoplugin'),
                    ],
                ]);

                if (CFGP_Options::get('enable_gmap', false)) {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-google-map-link',
                        'title'  => __('Google Map', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-google-map')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-google-map-link',
                            'title' => __('Google Map', 'cf-geoplugin'),
                        ],
                    ]);
                }

                if (CFGP_Options::get('enable_defender', 1)) {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-defender-link',
                        'title'  => __('Site Protection', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-defender')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-defender-link',
                            'title' => __('Site Protection', 'cf-geoplugin'),
                        ],
                    ]);
                }

                if (CFGP_Options::get('enable_banner', false)) {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-banner-link',
                        'title'  => __('Geo Banner', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-banner')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-banner-link',
                            'title' => __('Geo Banner', 'cf-geoplugin'),
                        ],
                    ]);
                }

                if (CFGP_Options::get('enable_seo_redirection', 1)) {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-seo-redirection-link',
                        'title'  => __('SEO Redirection', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-seo-redirection')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-seo-redirection-link',
                            'title' => __('SEO Redirection', 'cf-geoplugin'),
                        ],
                    ]);
                }

                $wp_admin_bar->add_menu([
                    'parent' => CFGP_NAME . '-admin-bar-link',
                    'id'     => CFGP_NAME . '-admin-bar-settings-link',
                    'title'  => __('Settings', 'cf-geoplugin'),
                    'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-settings')),
                    'meta'   => [
                        'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-settings-link',
                        'title' => __('Settings', 'cf-geoplugin'),
                    ],
                ]);

                $wp_admin_bar->add_menu([
                    'parent' => CFGP_NAME . '-admin-bar-link',
                    'id'     => CFGP_NAME . '-admin-bar-debug-link',
                    'title'  => __('Debug Mode', 'cf-geoplugin'),
                    'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-debug')),
                    'meta'   => [
                        'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-debug-link',
                        'title' => __('Debug Mode', 'cf-geoplugin'),
                    ],
                ]);

                if (CFGP_License::activated()) {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-activate-link',
                        'title'  => __('License', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-activate')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-activate-link',
                            'title' => __('License', 'cf-geoplugin'),
                        ],
                    ]);
                } else {
                    $wp_admin_bar->add_menu([
                        'parent' => CFGP_NAME . '-admin-bar-link',
                        'id'     => CFGP_NAME . '-admin-bar-activate-link',
                        'title'  => '<span class="cfgp-ab-icon"></span>' . __('Activate Unlimited', 'cf-geoplugin'),
                        'href'   => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-activate')),
                        'meta'   => [
                            'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-activate-link',
                            'title' => __('Activate Unlimited', 'cf-geoplugin'),
                        ],
                    ]);
                }
            }

            // Display Currency Converter in the topbar
            if (
                apply_filters('cfgp/topbar/currency/display', (CFGP_Options::get('enable_top_bar_currency', 1) == 1))
                && ($currency_converter = CFGP_U::api('currency_converter'))
                && (CFGP_Options::get('base_currency') != CFGP_U::api('currency'))
            ) {
                $money = apply_filters(
                    'cfgp/topbar/currency/title',
                    sprintf(
                        '%s: %s &#8646; %s',
                        __('Today\'s course', 'cf-geoplugin'),
                        '<span class="cfgp-topbar-currency-from">' . (1 . '' . CFGP_Options::get('base_currency')) . '</span>',
                        '<span class="cfgp-topbar-currency-to">' . (number_format($currency_converter, 2) . '' . CFGP_U::api('currency')) . '</span>'
                    )
                );

                $wp_admin_bar->add_node([
                    'id'     => CFGP_NAME . '-course',
                    'title'  => $money,
                    'href'   => '',
                    'meta'   => [ 'class' => CFGP_NAME . '-toolbar-course' ],
                    'parent' => false,
                ]);
            }
        }

        public function register_style($page)
        {

            if (!$this->limit_scripts($page) && $page != 'index.php') {
                return;
            }

            wp_enqueue_style(CFGP_NAME . '-fonts', CFGP_ASSETS . '/css/fonts.min.css', [], CFGP_VERSION);
            wp_enqueue_style(CFGP_NAME . '-admin', CFGP_ASSETS . '/css/style-admin.css', [CFGP_NAME . '-fonts'], CFGP_VERSION);
        }

        // Register CPT and taxonomies scripts
        public function register_scripts_ctp($page)
        {
            $post = '';
            $url  = '';

            if (isset($_GET['taxonomy'])) {
                $post = sanitize_text_field($_GET['taxonomy']);
            } elseif (isset($_GET['post'])) {
                $post = get_post(absint(sanitize_text_field($_GET['post'])));
                $post = isset($post->post_type) ? $post->post_type : '';
            } elseif (isset($_GET['post_type'])) {
                $post = sanitize_text_field($_GET['post_type']);
            }

            if (!$this->limit_scripts($post)) {
                return false;
            }

            if ($post === '' . CFGP_NAME . '-banner') {
                $url = sprintf('edit.php?post_type=%s', $post);
            } else {
                $url = sprintf('edit-tags.php?taxonomy=%s&post_type=%s-banner', $post, 'cf-geoplugin');
            }

            wp_enqueue_style(CFGP_NAME . '-cpt', CFGP_ASSETS . '/css/style-cpt.css', 1, CFGP_VERSION, false);
            wp_enqueue_script(CFGP_NAME . '-cpt', CFGP_ASSETS . '/js/script-cpt.js', ['jquery'], CFGP_VERSION, true);
            wp_localize_script(CFGP_NAME . '-cpt', 'CFGP', [
                'ajaxurl' => CFGP_U::admin_url('admin-ajax.php'),
                'label'   => [
                    'unload'      => __('Data will lost , Do you wish to continue?', 'cf-geoplugin'),
                    'loading'     => __('Loading...', 'cf-geoplugin'),
                    'not_found'   => __('Not Found!', 'cf-geoplugin'),
                    'placeholder' => __('Search', 'cf-geoplugin'),
                    'taxonomy'    => [
                        'country' => [
                            'name'             => __('Country code', 'cf-geoplugin'),
                            'name_info'        => __('Country codes are short (2 letters) alphabetic or numeric geographical codes developed to represent countries and dependent areas, for use in data processing and communications.', 'cf-geoplugin'),
                            'description'      => __('Country full name', 'cf-geoplugin'),
                            'description_info' => __('The name of the country must be written in English without spelling errors.', 'cf-geoplugin'),
                        ],
                        'region' => [
                            'name'             => __('Region code', 'cf-geoplugin'),
                            'name_info'        => __('Region codes are short (2 letters) alphabetic or numeric geographical codes developed to represent countries and dependent areas, for use in data processing and communications.', 'cf-geoplugin'),
                            'description'      => __('Region full name', 'cf-geoplugin'),
                            'description_info' => __('The name of the region must be written in English without spelling errors.', 'cf-geoplugin'),
                        ],
                        'city' => [
                            'name'      => __('City name', 'cf-geoplugin'),
                            'name_info' => __('The city name must be written in the original city name.', 'cf-geoplugin'),
                        ],
                        'postcode' => [
                            'name'      => __('Postcode', 'cf-geoplugin'),
                            'name_info' => __('The postcode name must be written in the original international format.', 'cf-geoplugin'),
                        ],
                    ],
                ],
                'current_url' => $url,
            ]);
        }

        public function register_scripts($page)
        {
            if ($page != 'nav-menus.php') {
                if (!$this->limit_scripts($page)) {
                    return;
                }
            }

            wp_enqueue_style(CFGP_NAME . '-select2', CFGP_ASSETS . '/css/select2.min.css', 1, '4.1.0-rc.0');
            wp_enqueue_script(CFGP_NAME . '-select2', CFGP_ASSETS . '/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);

            if ($page == 'nav-menus.php') {
                wp_enqueue_style(CFGP_NAME . '-menus', CFGP_ASSETS . '/css/style-menus.css', [CFGP_NAME . '-select2'], CFGP_VERSION);
            }

            wp_enqueue_script(CFGP_NAME . '-admin', CFGP_ASSETS . '/js/script-admin.js', ['jquery', CFGP_NAME . '-select2'], CFGP_VERSION, true);
            wp_localize_script(CFGP_NAME . '-admin', 'CFGP', [
                'ajaxurl'  => CFGP_U::admin_url('admin-ajax.php'),
                'adminurl' => self_admin_url('/'),
                'label'    => [
                    'upload_csv' => __('Select or Upload CSV file', 'cf-geoplugin'),
                    'unload'     => __('Data will lost , Do you wish to continue?', 'cf-geoplugin'),
                    'loading'    => __('Loading...', 'cf-geoplugin'),
                    'not_found'  => __('Not Found!', 'cf-geoplugin'),
                    'alert'      => [
                        'close' => __('Close', 'cf-geoplugin'),
                    ],
                    'rss' => [
                        'no_news' => __('There are no news at the moment.', 'cf-geoplugin'),
                        'error'   => __("ERROR! Can't load news feed.", 'cf-geoplugin'),
                    ],
                    'settings' => [
                        'saved' => __('Option saved successfuly!', 'cf-geoplugin'),
                        'fail'  => __('There was some unexpected system error. Changes not saved!', 'cf-geoplugin'),
                        'false' => __('Changes not saved for unexpected reasons. Try again!', 'cf-geoplugin'),
                        'error' => __('Option you provide not match to global variables. Permission denied!', 'cf-geoplugin'),
                    ],
                    'csv' => [
                        'saved'       => __('Successfuly saved %d records.', 'cf-geoplugin'),
                        'fail'        => __('Failed to add %d rows.', 'cf-geoplugin'),
                        'upload'      => __('Upload CSV file.', 'cf-geoplugin'),
                        'filetype'    => __('The file must be comma separated CSV type', 'cf-geoplugin'),
                        'exit'        => __('Are you sure, you want to exit?\nChanges wont be saved!', 'cf-geoplugin'),
                        'delete'      => __('Are you sure, you want to delete this redirection?', 'cf-geoplugin'),
                        'missing_url' => __('URL Missing. Please insert URL from your CSV file or choose file from the library.', 'cf-geoplugin'),
                    ],
                    'rest' => [
                        'delete' => __('Are you sure, you want to delete this access token?', 'cf-geoplugin'),
                        'error'  => __("Can't delete access token because unexpected reasons.", 'cf-geoplugin'),
                    ],
                    'footer_menu' => [
                        'documentation' => __('Documentation', 'cf-geoplugin'),
                        'contact'       => __('Contact', 'cf-geoplugin'),
                        'blog'          => __('Blog', 'cf-geoplugin'),
                        'faq'           => __('FAQ', 'cf-geoplugin'),
                        'thank_you'     => __('Thank you for using', 'cf-geoplugin'),
                    ],
                    'seo_redirection' => [
                        'bulk_delete'  => __('Are you sure you want to delete all these SEO redirects? You will no longer be able to recover data. We suggest to you made a backup before deleting.', 'cf-geoplugin'),
                        'not_selected' => __('You didn\'t select anything.', 'cf-geoplugin'),
                    ],
                    'select2' => [
                        'not_found' => [
                            'country'  => __('Country not found.', 'cf-geoplugin'),
                            'region'   => __('Region not found.', 'cf-geoplugin'),
                            'city'     => __('City not found.', 'cf-geoplugin'),
                            'postcode' => esc_attr__('Postcode not found.', 'cf-geoplugin'),
                        ],
                        'type_to_search' => [
                            'country'  => esc_attr__('Start typing the name of the country.', 'cf-geoplugin'),
                            'region'   => esc_attr__('Start typing the name of the region.', 'cf-geoplugin'),
                            'city'     => esc_attr__('Start typing the name of a city.', 'cf-geoplugin'),
                            'postcode' => esc_attr__('Start typing the postcode.', 'cf-geoplugin'),
                        ],
                        'searching'      => __('Searching, please wait...', 'cf-geoplugin'),
                        'removeItem'     => __('Remove Item', 'cf-geoplugin'),
                        'removeAllItems' => __('Remove all items', 'cf-geoplugin'),
                        'loadingMore'    => __('Loading more results, please wait...', 'cf-geoplugin'),
                    ],
                ],
            ]);
        }

        /*
         * Limit scripts
         */
        public function limit_scripts($page)
        {
            if (strpos($page, 'cf-geoplugin') !== false) {
                return true;
            }

            return false;
        }

        /*
         * Instance
         * @verson    1.0.0
         */
        public static function instance()
        {
            $class    = self::class;
            $instance = CFGP_Cache::get($class);

            if (!$instance) {
                $instance = CFGP_Cache::set($class, new self());
            }

            return $instance;
        }
    }
endif;
