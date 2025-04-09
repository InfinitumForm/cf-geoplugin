<?php

/**
 * SEO Redirection for Pages class
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       2.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_SEO_Redirection_Pages', false)) : class CFGP_SEO_Redirection_Pages extends CFGP_Global
{
    private $metabox;

    public function __construct()
    {
        // Prevent redirection for the crawlers and bots
        if (CFGP_Options::get('redirect_disable_bots', 0) && CFGP_U::is_bot()) {
            return;
        }

        // Prevent redirection using GET parametter
        if (isset($_GET['geo']) && ($_GET['geo'] === false || $_GET['geo'] === 'false')) {
            return;
        }

        // Prevent using REQUEST
        if (isset($_REQUEST['stop_redirection']) && ($_REQUEST['stop_redirection'] === true || $_REQUEST['stop_redirection'] === 'true')) {
            return;
        }

        // Stop on ajax
        if (wp_doing_ajax()) {
            return;
        }

        // Stop if is admin
        if (is_admin()) {
            return;
        }

        // Prevent by custom filter
        $API                     = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
        $stop_redirection_filter = apply_filters('cfgp/seo/stop_redirection', false, $API);

        if ($stop_redirection_filter) {
            if (CFGP_U::recursive_array_search($stop_redirection_filter, $API, true)) {
                return;
            }
        }
        $this->metabox = CFGP_Metabox::instance()->metabox;

        /**
         * Fire WordPress redirecion ASAP
         *
         * Here we have a couple of options to consider.
         * This is a list of actions that can serve:
         *
         * 01 $this->add_action( 'plugins_loaded',		'seo_redirection', 1);
         * 02 $this->add_action( 'wp',					'seo_redirection', 1);
         * 03 $this->add_action( 'send_headers',		'seo_redirection', 1);
         * 04 $this->add_action( 'posts_selection',		'seo_redirection', 1); // DANGER: Out of memory
         * 05 $this->add_action( 'template_redirect',	'seo_redirection', 1);
         */
        switch (CFGP_Options::get('redirect_mode', 2)) {
            default:
            case 1:
                $this->add_action('template_redirect', 'seo_redirection', 1);
                break;

            case 2:
                $this->add_action('send_headers', 'seo_redirection', 1);
                $this->add_action('template_redirect', 'seo_redirection', 1);
                break;

            case 3:
                $this->add_action('wp', 'seo_redirection', 1);
                $this->add_action('send_headers', 'seo_redirection', 1);
                $this->add_action('template_redirect', 'seo_redirection', 1);
                break;
        }
    }

    public function seo_redirection()
    {
        if (!is_admin()) {

            // Stop if API have error
            if (CFGP_U::api('error')) {
                return;
            }

            $current_page = CFGP_U::get_page();

            if (!$current_page) {
                return;
            }

            // Whitelist IP addresses
            $whitelist_ips = preg_split('/[,;\n|]+/', CFGP_Options::get('ip_whitelist'));
            $whitelist_ips = array_map('trim', $whitelist_ips);
            $whitelist_ips = array_filter($whitelist_ips);
            $whitelist_ips = apply_filters('cfgp/seo_redirection/whitelist/ip', $whitelist_ips, CFGP_U::api('ip'));
            $whitelist_ips = apply_filters('cfgp/seo_redirection/whitelist/ip/pages', $whitelist_ips, CFGP_U::api('ip'));

            if (
                !empty($whitelist_ips)
                && is_array($whitelist_ips)
                && in_array(CFGP_U::api('ip'), $whitelist_ips, true) !== false
            ) {
                return;
            }

            $enable_seo_posts = CFGP_Options::get('enable_seo_posts', []);

            if (
                empty($enable_seo_posts)
                || (
                    is_array($enable_seo_posts)
                    && in_array(get_post_type($current_page), $enable_seo_posts, true) === false
                )
            ) {
                return;
            }

            $seo_redirection = get_post_meta($current_page->ID, $this->metabox, true);

            $strtolower = (function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower');

            if ($seo_redirection && is_array($seo_redirection)) {
                foreach ($seo_redirection as $data) {

                    $cookie_name = apply_filters("cfgp/metabox/seo_redirection/only_once/cookie_name/{$current_page->ID}", '__cfgp_seo_' . md5(serialize($data)), $data, $current_page->ID);

                    if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
                        continue;
                    }

                    $url = (isset($data['url']) ? $data['url'] : '');

                    if ($url && self::current_url($url, true)) {
                        continue;
                    }

                    if ((isset($data['active']) ? $data['active'] : 1) !== 1) {
                        continue;
                    }

                    // Set redirection
                    $do_redirection = false;

                    // Get countries
                    $country = array_map($strtolower, (isset($data['country']) ? $data['country'] : []));
                    $country = array_map(['CFGP_U', 'transliterate'], $country);

                    // Get regions
                    $region = array_map($strtolower, (isset($data['region']) ? $data['region'] : []));
                    $region = array_map(['CFGP_U', 'transliterate'], $region);

                    // Get cities
                    $city = array_map($strtolower, (isset($data['city']) ? $data['city'] : []));
                    $city = array_map(['CFGP_U', 'transliterate'], $city);

                    // Get postcodes
                    $postcode = array_map($strtolower, (isset($data['postcode']) ? $data['postcode'] : []));

                    // Get HTTP codes
                    $http_code = (isset($data['http_code']) ? $data['http_code'] : 302);

                    // Get search type
                    $search_type = (isset($data['search_type']) ? $data['search_type'] : 'exact');

                    // Generate redirection mode
                    $mode = [ null, 'country', 'region', 'city', 'postcode' ];
                    $mode = $mode[ count(array_filter(array_map(
                        function ($obj) {
                            return !empty($obj);
                        },
                        [
                            $country,
                            $region,
                            $city,
                            $postcode,
                        ]
                    ))) ];

                    if (
                        !empty($country)
                        && empty($region)
                        && empty($postcode)
                        && !empty($city)
                    ) {
                        $mode = 'country_city';
                    }

                    if (
                        !empty($country)
                        && empty($region)
                        && !empty($postcode)
                        && !empty($city)
                    ) {
                        $mode = 'country_city_postcode';
                    }

                    if (
                        !empty($country)
                        && empty($region)
                        && !empty($postcode)
                        && empty($city)
                    ) {
                        $mode = 'country_postcode';
                    }

                    // Exclude countries
                    if ($data['exclude_country'] ?? null) {
                        $country = [];
                        $mode    = 'async';
                    }

                    // Exclude regions
                    if ($data['exclude_region'] ?? null) {
                        $region = [];
                        $mode   = 'async';
                    }

                    // Exclude cities
                    if ($data['exclude_city'] ?? null) {
                        $city = [];
                        $mode = 'async';
                    }

                    // Exclude postcodes
                    if ($data['exclude_postcode'] ?? null) {
                        $postcode = [];
                        $mode     = 'async';
                    }

                    // Switch mode
                    switch ($mode) {
                        case 'async':
                            if (count(array_filter(array_map(
                                function ($obj) {
                                    return !empty($obj);
                                },
                                [
                                    CFGP_U::check_user_by_city($city),
                                    CFGP_U::check_user_by_region($region),
                                    CFGP_U::check_user_by_country($country),
                                    CFGP_U::check_user_by_postcode($postcode),
                                ]
                            )))) {
                                $do_redirection = true;
                            }
                            break;

                        case 'country':
                            if (CFGP_U::check_user_by_country($country)) {
                                $do_redirection = true;
                            }
                            break;

                        case 'region':
                            if (
                                CFGP_U::check_user_by_region($region)
                                && CFGP_U::check_user_by_country($country)
                            ) {
                                $do_redirection = true;
                            }
                            break;

                        case 'city':
                            if (
                                CFGP_U::check_user_by_city($city)
                                && CFGP_U::check_user_by_region($region)
                                && CFGP_U::check_user_by_country($country)
                            ) {
                                $do_redirection = true;
                            }
                            break;

                        case 'postcode':
                            if (
                                CFGP_U::check_user_by_city($city)
                                && CFGP_U::check_user_by_region($region)
                                && CFGP_U::check_user_by_country($country)
                                && CFGP_U::check_user_by_postcode($postcode)
                            ) {
                                $do_redirection = true;
                            }
                            break;

                        case 'country_city':
                            if (
                                CFGP_U::check_user_by_city($city)
                                && CFGP_U::check_user_by_country($country)
                            ) {
                                $do_redirection = true;
                            }
                            break;

                        case 'country_city_postcode':
                            if (
                                CFGP_U::check_user_by_city($city)
                                && CFGP_U::check_user_by_country($country)
                                && CFGP_U::check_user_by_postcode($postcode)
                            ) {
                                $do_redirection = true;
                            }
                            break;

                        case 'country_postcode':
                            if (
                                CFGP_U::check_user_by_country($country)
                                && CFGP_U::check_user_by_postcode($postcode)
                            ) {
                                $do_redirection = true;
                            }
                            break;
                    }

                    if ($do_redirection) {
                        // Redirect only once
                        if (isset($data['only_once']) ? $data['only_once'] : 0) {
                            $expire = apply_filters('cfgp/seo/control_redirection_pages/cookie/expire', (YEAR_IN_SECONDS * 2), CFGP_TIME);
                            CFGP_U::setcookie($cookie_name, (CFGP_TIME.'_'.$expire), $expire);
                        }

                        // Redirections
                        if (CFGP_U::redirect($url, $http_code)) {
                            exit;
                        }
                    }
                }
            }
        }
    }

    /*
     * Get current URL or match current URL
     */
    private static function current_url($url = null, $avoid_protocol = false)
    {
        $get_url = CFGP_U::get_url();

        if ($avoid_protocol) {
            if (!empty($url)) {
                $url = preg_replace('/(https?\:\/\/)/i', '', $url);
            }
            $get_url = preg_replace('/(https?\:\/\/)/i', '', $get_url);
        }

        if (empty($url)) {
            return $get_url;
        } else {
            $url     = rtrim($url, '/');
            $get_url = rtrim($get_url, '/');

            if (strtolower($url) == strtolower($get_url)) {
                return $url;
            }
        }

        return false;
    }

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
