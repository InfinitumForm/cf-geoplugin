<?php

/**
 * Utilities
 *
 * Main global classes with active hooks
 *
 * @link            http://infinitumform.com/
 * @since           8.0.0
 *
 * @package         cf-geoplugin
 *
 * @author          Ivijan-Stefan Stipic
 *
 * @version       	3.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_U', false)) : class CFGP_U
{
    private static $user;

    /*
     * Get plugin ID
     * @return        string
     * @author        Ivijan-Stefan Stipic
    */
    public static function ID()
    {
        if ($ID = CFGP_Cache::get('ID')) {
            return $ID;
        }

        $ID = get_option(CFGP_NAME . '-ID');

        if (!$ID) {
            $ID = ('cfgp_' . self::generate_token(55) . '_' . self::generate_token(4));
            add_option(CFGP_NAME . '-ID', $ID, false);
        }

        return CFGP_Cache::set('ID', $ID);
    }

    /*
     * Get plugin KEY for the REST API
     * @return        string
     * @author        Ivijan-Stefan Stipic
     */
    public static function KEY()
    {
        if ($KEY = CFGP_Cache::get('REST_KEY')) {
            return $KEY;
        }

        return CFGP_Cache::set('REST_KEY', self::hash(str_rot13(substr(CFGP_U::ID(), 6, 21)), 'sha256'));
    }

    /*
     * Get plugin CACHE KEY for the internal REST API
     * @return        string
     * @author        Ivijan-Stefan Stipic
     */
    public static function CACHE_KEY()
    {
        if ($KEY = CFGP_Cache::get('CACHE_SERVER_KEY')) {
            return $KEY;
        }

        return CFGP_Cache::set('CACHE_SERVER_KEY', self::hash(substr(CFGP_U::ID(), 2, 20), 'sha512'));
    }

    /*
     * Get HTTP codes
     * @return        object
     * @author        Ivijan-Stefan Stipic
     */
    public static function get_http_codes()
    {
        return apply_filters('cfgp_http_codes', [
            301 => __('301 - Moved Permanently', 'cf-geoplugin'),
            302 => __('302 - Found (Moved temporarily)', 'cf-geoplugin'),
            303 => __('303 - See Other', 'cf-geoplugin'),
            307 => __('307 - Temporary Redirect (since HTTP/1.1)', 'cf-geoplugin'),
            308 => __('308 - Permanent Redirect', 'cf-geoplugin'),
            404 => __('404 - Not Found (not recommended)', 'cf-geoplugin'),
        ]);
    }

    /*
     * Get HTTP code name
     * @return        object/null
     * @author        Ivijan-Stefan Stipic
    */
    public static function get_http_code_name($code)
    {
        $code       = (int)$code;
        $http_codes = self::get_http_codes();

        return ($http_codes[$code] ?? null);
    }

    /*
     * Get user
     * @return        object/null
     * @author        Ivijan-Stefan Stipic
    */
    public static function get_user($user_id_or_email = null)
    {

        // Check if user is passed via request and no parameter is passed
        if (empty($user_id_or_email) && isset($_REQUEST['cfgp_user'])) {
            $user_id_or_email = absint($_REQUEST['cfgp_user']);
        }

        // If a parameter (user ID or email) is passed
        if ($user_id_or_email) {
            if (is_numeric($user_id_or_email)) {
                self::$user = get_user_by('ID', absint($user_id_or_email));
            } elseif (filter_var($user_id_or_email, FILTER_VALIDATE_EMAIL)) {
                self::$user = get_user_by('email', sanitize_email($user_id_or_email));
            }
        }

        // Automatic detection if user is not yet set
        if (empty(self::$user)) {
            if (is_author()) {
                $author_id  = get_query_var('author');
                self::$user = $author_id ? get_user_by('ID', $author_id) : get_user_by('slug', get_query_var('author_name'));
            } elseif (is_user_logged_in()) {
                self::$user = wp_get_current_user();
            }
        }

        return self::$user;
    }

    /**
     * Get content via cURL
     *
     * @since    4.0.4
     */
    public static function curl_get($url, $headers = [], $new_params = [], $json = false)
    {
        $cache_name = 'cfgp-curl_get-' . self::hash(serialize([$url, $headers, $new_params, $json]));

        if (null !== ($cache = CFGP_Cache::get($cache_name))) {
            return $cache;
        }

        $headers = empty($headers) ? ['Accept: application/json'] : $headers;

        if (CFGP_Options::get('proxy', 0) == 1) {
            $proxy_settings = [
                'WP_PROXY_HOST'     => 'proxy_ip',
                'WP_PROXY_PORT'     => 'proxy_port',
                'WP_PROXY_USERNAME' => 'proxy_username',
                'WP_PROXY_PASSWORD' => 'proxy_password',
            ];

            foreach ($proxy_settings as $constant => $option_name) {
                if (!defined($constant) && $value = CFGP_Options::get($option_name, false)) {
                    define($constant, $value);
                }
            }
        }

        $default_params = [
            'timeout' => CFGP_Options::get('timeout', 5),
            'headers' => $headers,
        ];
        $params = wp_parse_args($new_params, $default_params);

        $request = wp_remote_get(esc_url_raw($url), $params);

        if (is_wp_error($request)) {
            return CFGP_Cache::set($cache_name, false);
        }

        $output = wp_remote_retrieve_body($request);

        if (is_wp_error($output) || empty($output)) {
            return CFGP_Cache::set($cache_name, false);
        }

        if (!$json) {
            $output = json_decode($output, true);
        }

        CFGP_Cache::set($cache_name, $output);

        return $output;
    }

    /**
     * POST content via cURL
     *
     * @since    4.0.4
     */
    public static function curl_post($url, $post_data = [], $headers = ['Accept: application/json'], $new_params = [], $json = false)
    {
        $cache_name = 'cfgp-curl_post-' . self::hash(serialize([$url, $headers, $new_params, $json]));

        if (null !== ($cache = CFGP_Cache::get($cache_name))) {
            return $cache;
        }

        $proxy_settings = [
            'WP_PROXY_HOST'     => 'proxy_ip',
            'WP_PROXY_PORT'     => 'proxy_port',
            'WP_PROXY_USERNAME' => 'proxy_username',
            'WP_PROXY_PASSWORD' => 'proxy_password',
        ];

        foreach ($proxy_settings as $constant => $option_name) {
            if (!defined($constant) && $value = CFGP_Options::get($option_name, false)) {
                define($constant, $value);
            }
        }

        $default_params = [
            'method'  => 'POST',
            'timeout' => CFGP_Options::get('timeout', 5),
            'headers' => $headers,
            'body'    => $post_data,
        ];
        $params = wp_parse_args($new_params, $default_params);

        $request = wp_remote_post(esc_url_raw($url), $params);

        if (is_wp_error($request)) {
            return CFGP_Cache::set($cache_name, false);
        }

        $output = wp_remote_retrieve_body($request);

        if (is_wp_error($output) || empty($output)) {
            return CFGP_Cache::set($cache_name, false);
        }

        if (!$json) {
            $output = json_decode($output, true);
        }

        CFGP_Cache::set($cache_name, $output);

        return $output;
    }

    /*
     * Decode content
     * @return        string
     * @author        Ivijan-Stefan Stipic
    */
    public static function decode(string $content)
    {
        $content = rawurldecode($content);
        $content = htmlspecialchars_decode($content);
        $content = html_entity_decode($content);
        $content = strtr(
            $content,
            array_flip(
                get_html_translation_table(
                    HTML_ENTITIES,
                    ENT_QUOTES
                )
            )
        );

        return $content;
    }

    /*
     * Get image source URL by post
     * @author        Ivijan-Stefan Stipic
    */
    public static function get_attachment_image_src_by_post($post, $size = 'thumbnail', $icon = false)
    {
        $attachment_id = get_post_thumbnail_id($post);

        if ($attachment_id) {
            $src = wp_get_attachment_image_src($attachment_id, $size, $icon);

            if ($src && isset($src[0])) {
                return $src[0];
            }
        }

        return null;
    }

    /*
     * Generate unique token
     * @author        Ivijan-Stefan Stipic
     */
    public static function generate_token(int $length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes') || function_exists('random_bytes')) {
            if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                return substr(
                    str_rot13(
                        bin2hex(
                            random_bytes(
                                ceil($length * 2)
                            )
                        )
                    ),
                    0,
                    $length
                );
            } else {
                return substr(
                    str_rot13(
                        bin2hex(
                            openssl_random_pseudo_bytes(
                                ceil($length * 2)
                            )
                        )
                    ),
                    0,
                    $length
                );
            }
        } else {
            return substr(
                str_replace(
                    ['.', ' ', '_'],
                    mt_rand(1000, 9999),
                    uniqid('t' . microtime())
                ),
                0,
                $length
            );
        }
    }

    /*
     * Return plugin informations
     * @return        array/object
     * @author        Ivijan-Stefan Stipic
     */
    public static function plugin_info(array $fields = [], $slug = false, $force_cache = true)
    {

        $slug       = $slug ?: CFGP_NAME;
        $cache_name = CFGP_NAME . '-plugin_info-' . self::hash(serialize($fields) . $slug);

        $cache = CFGP_Cache::get($cache_name);

        if ($cache) {
            return $cache;
        }

        if ($force_cache) {
            $db_cache = CFGP_DB_Cache::get("cfgp-{$cache_name}");

            if ($db_cache) {
                return $db_cache;
            }
        }

        if (!is_admin()) {
            return null;
        }

        if (!function_exists('plugins_api')) {
            self::include_once(WP_ADMIN_DIR . '/includes/plugin-install.php');
        }

        // Here are the default fields based on WordPress defaults, but only keep those that are necessary:
        $fields = array_merge([
            'name'              => false,
            'slug'              => false,
            'version'           => false,
            'author'            => false,
            'short_description' => false,
            'homepage'          => false,
            'icons'             => false,
            'last_updated'      => false,
        ], $fields);

        /** Prepare our query */
        $plugin_data = plugins_api('plugin_information', [
            'slug'   => $slug,
            'fields' => $fields,
        ]);

        if (is_wp_error($plugin_data)) {
            return $plugin_data;
        }

        CFGP_Cache::set($cache_name, $plugin_data);

        if ($force_cache) {
            CFGP_DB_Cache::set("cfgp-{$cache_name}", $plugin_data, DAY_IN_SECONDS);
        }

        return $plugin_data;
    }

    /*
     * Set cookie
     * @verson    1.0.0
    */
    public static function setcookie($name, $val, $time = 0)
    {
        if (!headers_sent()) {
            $cookie_time = $time > 0 ? (CFGP_TIME + absint($time)) : 0;

            setcookie($name, $val, $cookie_time, COOKIEPATH, COOKIE_DOMAIN);

            if (CFGP_Options::get('cache-support', 'yes') == 'yes') {
                self::cache_flush();
            }

            return true;
        }

        return false;
    }

    /*
     * Set defender cookie
     * @verson    1.0.0
     */
    public static function set_defender_cookie()
    {
        $token       = self::KEY();
        $cookie_name = 'cfgp__' . str_rot13(substr($token, 6, 8));
        $time        = (int)apply_filters('cfgp_set_defender_cookie_timeout', HOUR_IN_SECONDS);

        return self::setcookie($cookie_name, $token, $time);
    }

    /*
     * Delete defender cookie
     * @verson    1.0.0
     */
    public static function delete_defender_cookie()
    {
        $token       = self::KEY();
        $cookie_name = 'cfgp__' . str_rot13(substr($token, 6, 8));
        $time        = ceil(CFGP_TIME - ((int)apply_filters('cfgp_set_defender_cookie_timeout', HOUR_IN_SECONDS) * 2));

        return self::setcookie($cookie_name, $token, $time);
    }

    /*
     * Check defender cookie
     * @verson    1.0.0
     */
    public static function check_defender_cookie()
    {
        if ($check_defender_cookie = CFGP_Cache::get('check_defender_cookie')) {
            return $check_defender_cookie;
        }

        $token       = self::KEY();
        $cookie_name = 'cfgp__' . str_rot13(substr($token, 6, 8));

        return CFGP_Cache::set(
            'check_defender_cookie',
            (isset($_COOKIE[$cookie_name]) && sanitize_text_field($_COOKIE[$cookie_name]) === $token)
        );
    }

    /*
     * Flush Plugin cache
     * @verson    1.0.0
     */
    public static function flush_plugin_cache()
    {
        global $wpdb;

        // Determine the table and column name based on the site type.
        $table_name  = (is_multisite() && is_main_site() && is_main_network()) ? $wpdb->sitemeta : $wpdb->options;
        $column_name = (is_multisite() && is_main_site() && is_main_network()) ? 'meta_key' : 'option_name';

        // Create the SQL statement.
        $sql = "DELETE FROM `{$table_name}`
				WHERE `{$column_name}` LIKE '_transient_cfgp-api-%'
				   OR `{$column_name}` LIKE '_transient_timeout_cfgp-api-%'
				   OR `{$column_name}` LIKE '_site_transient_cfgp-api-%'
				   OR `{$column_name}` LIKE '_site_transient_timeout_cfgp-api-%'";

        // Execute the SQL statement.
        $wpdb->query($sql);

        // Remove current cache
        CFGP_Cache::flush();
    }

    /*
     * Flush Cache
     * @verson    2.0.0
    */
    public static function cache_flush($force = false)
    {
        // Prevent headers bug
        if (headers_sent()) {
            return;
        }

        global $post, $user, $w3_plugin_totalcache;

        // Standard cache
        header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Set nocache headers
        if (function_exists('nocache_headers')) {
            nocache_headers();
        }

        // Flush WP cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        } elseif ($w3_plugin_totalcache) {
            $w3_plugin_totalcache->flush_all();
        }

        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache(true);
        }
        /*
                // WP Rocket
                if ( function_exists( 'rocket_clean_domain' ) ) {
                    rocket_clean_domain();
                }
        */

        // Flush LS Cache
        if (class_exists('\LiteSpeed\Purge', false)) {
            \LiteSpeed\Purge::purge_all();
        } elseif (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
        } elseif (function_exists('liteSpeed_purge_all')) {
            litespeed_purge_all();
        }

        // WP Super Cache
        if (function_exists('prune_super_cache') && function_exists('get_supercache_dir')) {
            prune_super_cache(get_supercache_dir(), true);
        }

        // Cache Enabler.
        if (function_exists('clear_site_cache')) {
            clear_site_cache();
        }

        // Clean Pagely cache
        if (class_exists('PagelyCachePurge', false)) {
            (new PagelyCachePurge())->purgeAll();

            return true;
        }

        // Clean Hyper Cache
        if (function_exists('hyper_cache_clear')) {
            hyper_cache_clear();

            return true;
        }

        // Comet Cache
        if (class_exists('comet_cache', false) && method_exists('comet_cache', 'clear')) {
            comet_cache::clear();
        }

        // Clean Simple Cache
        if (function_exists('simple_cache_flush')) {
            simple_cache_flush();
        }

        // Clean Autoptimize
        if (class_exists('autoptimizeCache') && method_exists('autoptimizeCache', 'clearall')) {
            autoptimizeCache::clearall();
        }

        // Clean WP-Optimize
        if (class_exists('WP_Optimize_Cache_Commands', false)) {
            (new WP_Optimize_Cache_Commands())->purge_page_cache();
        }

        if ($force) {
            self::flush_plugin_cache();
        }
    }

    /*
     * Safe and SEO redirections to new location
     * @verson    1.0.0
    */
    public static function redirect($location, int $status = 302, bool $safe = null)
    {
        $status = absint($status);

        $location_parts = apply_filters('cfgp/redirect/location', $location, $status, $safe);

        // Prevent AJAX
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        // Validate URL
        if (!filter_var($location, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Automatic switch to safe redirection
        if (null === $safe) {
            $safe = (strpos($location, self::parse_url()['domain']) !== false);
        }

        // Check good status code
        if ($safe && ($status < 300 || 399 < $status)) {
            new Exception(__('HTTP redirect status code must be a redirection code, 3xx.', 'cf-geoplugin'));

            return false;
        }

        /*
         * We have a case where someone needs to pass some custom URL parameters
         * to another URL and we need to support that. In that case we check the
         * current location, take those parameters and pass them to the new URL.
         *
         * Like example: https://wpgeocontroller.com/?name=*&surname=*
         *
         * In this case, the "name" and "surname" parameters will be searched
         * for and replaced if they appear in the original URL. If they do not
         * appear, these parameters will be removed from the new URL.
         */
        if (strpos($location, '=*') !== false) {
            parse_str(parse_url($location, PHP_URL_QUERY), $location_parts);

            $location_parts = apply_filters('cfgp/redirect/location_parts', $location_parts, $location, $status);

            foreach ($location_parts as $key => $match) {
                if (urldecode($match) == '*') {
                    if (($_GET[$key] ?? null)) {
                        $location_parts[$key] = $_GET[$key];
                    } else {
                        $location_parts[$key] = null;
                    }
                }
            }

            $location = add_query_arg($location_parts, $location);

            unset($location_parts);
        }

        // Cache control
        if (CFGP_Options::get('cache-support', 'yes') == 'yes') {
            self::cache_flush();
        }

        // Disable referrer
        if (CFGP_Options::get('hide_http_referrer_headers', 0)) {
            header('Referrer-Policy: no-referrer');
        }

        if (!headers_sent()) {
            if (function_exists('wp_redirect')) {
                // Emulate wp_safe_redirect()
                if ($safe) {
                    $location = wp_validate_redirect(
                        $location,
                        apply_filters('cfgp/safe_redirect/fallback', site_url(), $status)
                    );
                }

                // Do redirection
                return wp_redirect($location, $status, CFGP_NAME);
            } else {
                // Windows server need some nice touch
                global $is_IIS;

                if (!defined('PHP_SAPI')) {
                    define('PHP_SAPI', php_sapi_name());
                }

                if (!$is_IIS && 'cgi-fcgi' !== PHP_SAPI) {
                    if (function_exists('status_header')) {
                        status_header($status); // This causes problems on IIS and some FastCGI setups.
                    }
                }
                // Inform application who redirects
                header('X-Redirect-By: ' . CFGP_NAME);
                // Standard redirect
                header("Location: {$location}", true, $status);
                // Optional workaround for an IE bug (thanks Olav)
                header('Connection: close');

                return true;
            }
        } else {
            die('<meta http-equiv="refresh" content="time; URL=' . esc_url($location) . '" />');
        }
    }

    /*
     * Get current URL
     * @verson    1.0.0
    */
    public static function get_current_url()
    {
        global $wp;

        return add_query_arg([], home_url($wp->request));
    }

    /**
     * Get real Hostname
     *
     * @since    6.0.1
     **/
    public static function get_host($clean = false)
    {
        if (CFGP_IP::is_localhost()) {
            return 'localhost';
        }

        $hostInfo = self::parse_url();

        if ($clean) {
            return preg_replace('/https?:\/\/|w{3}\./i', '', strtolower($hostInfo['domain']));
        } else {
            return strtolower($hostInfo['domain']);
        }
    }

    /**
     * Parse URL
     *
     * @verson    1.0.0
     */
    public static function parse_url()
    {

        $parse_url = CFGP_Cache::get('parse_url');

        if (!$parse_url) {
            $http   = 'http' . (self::is_ssl() ? 's' : '');
            $domain = preg_replace(
                '%:/{3,}%i',
                '://',
                rtrim($http, '/') . '://' . sanitize_text_field($_SERVER['HTTP_HOST'] ?? '')
            );
            $domain = rtrim($domain, '/');
            $url    = preg_replace(
                '%:/{3,}%i',
                '://',
                $domain . '/' . (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) ? sanitize_text_field(ltrim($_SERVER['REQUEST_URI'], '/')) : '')
            );

            $parse_url = CFGP_Cache::set('parse_url', [
                'method'    => $http,
                'home_fold' => str_replace($domain, '', home_url()),
                'url'       => esc_url($url),
                'domain'    => $domain,
            ]);
        }

        return $parse_url;
    }

    /**
     * Get URL
     *
     * @verson    1.0.0
     */
    public static function get_url()
    {

        $current_url = CFGP_Cache::get('current_url');

        if (!$current_url) {
            $url = self::parse_url();
            $url = $url['url'];

            $current_url = CFGP_Cache::set('current_url', $url);
        }

        return $current_url;
    }

    /*
     * CHECK IS SSL
     * @return	true/false
     */
    public static function is_ssl($url = false)
    {

        $ssl = CFGP_Cache::get('is_ssl');

        if ($url !== false && is_string($url)) {
            return (preg_match('/(https|ftps)/Ui', $url) !== false);
        } elseif (empty($ssl)) {
            if (
                (is_admin() && defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true)
                || ($_SERVER['HTTPS'] ?? null == 'on')
                || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null == 'https')
                || ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? null == 'on')
                || ($_SERVER['SERVER_PORT'] ?? null == 443)
                || ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? null == 443)
                || ($_SERVER['REQUEST_SCHEME'] ?? null == 'https')
            ) {
                $ssl = CFGP_Cache::set('is_ssl', true);
            }
        }

        return $ssl;
    }

    /*
    * Check is block editor screen
    * @since     8.0.0
    */
    public static function is_editor()
    {

        $is_editor = CFGP_Cache::get('is_editor');

        if (empty($is_editor)) {
            if (version_compare(get_bloginfo('version'), '5.0', '>=')) {
                if (!function_exists('get_current_screen')) {
                    self::include_once(ABSPATH  . '/wp-admin/includes/screen.php');
                }
                $get_current_screen = get_current_screen();

                if (is_callable([$get_current_screen, 'is_block_editor']) && method_exists($get_current_screen, 'is_block_editor')) {
                    $is_editor = CFGP_Cache::set('is_editor', $get_current_screen->is_block_editor());
                }
            } else {
                $is_editor = CFGP_Cache::set('is_editor', (($_GET['action'] ?? null) == 'edit' && is_numeric($_GET['post'] ?? null)));
            }
        }

        return $is_editor;
    }

    /*
     * CHECK INTERNET CONNECTION
     * @since	7.0.0
     * @return	true/false
     */
    public static function is_connected()
    {

        if (CFGP_Cache::get('is_connected')) {
            return true;
        }

        // List connections
        $urls = [
            'www.google.com',
            'www.facebook.com',
        ];

        foreach ($urls as $url) {
            // list ports
            foreach ([443,80] as $port) {
                $connected = fsockopen($url, $port);

                if ($connected !== false) {
                    fclose($connected);

                    return CFGP_Cache::set('is_connected', true);
                }
            }
        }

        // OK you not have connection - boohooo
        return false;
    }

    /**
     * Detect is proxy enabled
     *
     * @since    4.0.0
     *
     * @return $bool true/false
     */
    public static function proxy()
    {
        return (CFGP_Options::get('proxy', false) ? true : false);
    }

    /**
     * Check is bot, search engine or crawler
     *
     * @since    7.7.6
     **/
    public static function is_bot($ip = false)
    {
        // Search by IP
        if (empty($ip)) {
            $ip = CFGP_IP::get();
        }

        $range = apply_filters('cfgp/crawler/ip/range', [
            // Google
            '34.65.0.0'      => '34.155.255.255',
            '64.18.0.0'      => '64.18.15.255',
            '64.68.90.1'     => '64.68.90.255',
            '64.233.173.193' => '64.233.173.255',
            '64.233.160.0'   => '64.233.191.255',
            '66.249.64.0'    => '66.249.95.255 ',
            '66.102.0.0'     => '66.102.15.255',
            '72.14.192.0'    => '72.14.255.255',
            '74.125.0.0'     => '74.125.255.255',
            '108.177.8.0'    => '108.177.15.255',
            '172.217.0.0'    => '172.217.31.255',
            '173.194.0.0'    => '173.194.255.255',
            '207.126.144.0'  => '207.126.159.255',
            '209.85.128.0'   => '209.85.255.255',
            '216.58.192.0'   => '216.58.223.255',
            '216.239.32.0'   => '216.239.63.255',
            // MSN
            '64.4.0.0'     => '64.4.63.255',
            '65.52.0.0'    => '65.55.255.255',
            '131.253.21.0' => '131.253.47.255',
            '157.54.0.0'   => '157.60.255.255',
            '207.46.0.0'   => '207.46.255.255',
            '207.68.128.0' => '207.68.207.255',
            // Yahoo
            '8.12.144.0'    => '8.12.144.255',
            '66.196.64.0'   => '66.196.127.255',
            '66.228.160.0'  => '66.228.191.255',
            '67.195.0.0'    => '67.195.255.255',
            '68.142.192.0'  => '68.142.255.255',
            '72.30.0.0'     => '72.30.255.255',
            '74.6.0.0'      => '74.6.255.255',
            '98.136.0.0'    => '98.139.255.255',
            '202.160.176.0' => '202.160.191.255',
            '209.191.64.0'  => '209.191.127.255',
            // Bing
            '104.146.0.0'   => '104.146.63.255',
            '104.146.100.0' => '104.146.113.255',
            // Yandex
            '100.43.64.0' => '100.43.79.255',
            '100.43.80.0' => '100.43.83.255',
            // Baidu
            '103.6.76.0'     => '103.6.79.255',
            '104.193.88.0'   => '104.193.91.255',
            '106.12.0.0'     => '106.13.255.255',
            '115.231.36.136' => '115.231.36.159',
            '39.156.69.79',
            '220.181.38.148',
            // DuckDuckGo
            '50.16.241.113'  => '50.16.241.117',
            '54.208.100.253' => '54.208.102.37',
            '72.94.249.34'   => '72.94.249.38',
            '23.21.227.69',
            '40.88.21.235',
            '50.16.247.234',
            '52.204.97.54',
            '52.5.190.19',
            '54.197.234.188',
            '107.21.1.8',
            // Sogou
            '118.191.216.42' => '118.191.216.57',
            '119.28.109.132',
            // Ask
            '65.214.45.143' => '65.214.45.148',
            '66.235.124.7',
            '66.235.124.101',
            '66.235.124.193',
            '66.235.124.73',
            '66.235.124.196',
            '66.235.124.74',
            '63.123.238.8',
            '202.143.148.61',
            // Pinterest
            '54.236.1.1' => '54.236.1.255',
            '54.82.14.182',
            '54.81.171.36',
            '23.20.24.147',
            '54.237.150.66',
            '54.237.197.55',
            '54.211.68.214',
            '54.234.164.192',
            '50.16.155.205',
            '23.20.84.153',
            '54.224.131.213',
            // Facebook
            '69.63.176.0'  => '69.63.176.21',
            '69.63.184.0'  => '69.63.184.21',
            '66.220.144.0' => '66.220.144.21',
            '69.63.176.0'  => '69.63.176.20',
            '31.13.24.0'   => '31.13.24.21',
            '31.13.64.0'   => '31.13.64.18',
            '69.171.224.0' => '69.171.224.19',
            '74.119.76.0'  => '74.119.76.22',
            '103.4.96.0'   => '103.4.96.22',
            '173.252.64.0' => '173.252.64.18',
            '204.15.20.0'  => '204.15.20.22',
            // Twitter
            '199.59.156.0' => '199.59.156.255',
            // Linkedin
            '144.2.22.0'   => '144.2.22.24',
            '144.2.224.0'  => '144.2.224.24',
            '144.2.225.0'  => '144.2.225.24',
            '144.2.228.0'  => '144.2.228.24',
            '144.2.229.0'  => '144.2.229.24',
            '144.2.233.0'  => '144.2.233.24',
            '144.2.237.0'  => '144.2.237.24',
            '216.52.16.0'  => '216.52.16.24',
            '216.52.17.0'  => '216.52.17.24',
            '216.52.18.0'  => '216.52.18.24',
            '216.52.20.0'  => '216.52.20.24',
            '216.52.21.0'  => '216.52.21.24',
            '216.52.22.0'  => '216.52.22.24',
            '65.156.227.0' => '65.156.227.24',
            '8.39.53.0'    => '8.39.53.24',
            // YOU.com
            '172.66.40.57',
            '172.66.43.199',
        ]);

        $ip2long = sprintf('%u', ip2long($ip));

        if ($ip2long !== false) {
            foreach ($range as $start => $end) {
                $end   = sprintf('%u', ip2long($end));
                $start = sprintf('%u', ip2long($start));

                $is_key = ($start === false || $start == 0);

                if ($end === false || $end == 0) {
                    continue;
                }

                if (is_numeric($start) && $is_key && $end == $ip2long) {
                    return true;
                } else {
                    if (!$is_key && $ip2long >= $start && $ip2long <= $end) {
                        return true;
                    }
                }
            }
        }

        // Get by user agent (wide range)
        if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
            return (preg_match('/rambler|abacho|ac(oi|cona)|aspseek|al(tavista|exa)|estyle|scrubby|lycos|geona|ia_archiver|sogou|facebook|duckduck(bot|go)?|twitter|pinterest|linkedin|skype|naver|bing(bot)?|google|ya(hoo|ndex)|baidu(spider)?|teoma|xing|java\/1\.7\.0_45|crawl|you|slurp|spider|mediapartners|\sbot\s|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']) ? true : false);
        }

        return false;
    }

    /**
     * PRIVATE: Set stream context
     *
     * @since	1.3.5
     */
    public static function set_stream_context($header = [], $method = 'POST', $content = '')
    {
        $header = array_merge([ 'Content-Type: application/x-www-form-urlencoded' ], $header);

        return stream_context_create(
            [
                'http' => [
                    'method'  => $method,
                    'header'  => $header,
                    'content' => $content,
                ],
            ]
        );
    }

    /**
     * Find value in deep assoc array
     *
     * @since    7.0.0
     **/
    public static function array_find_deep($array, $search, $keys = [])
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sub = self::array_find_deep($value, $search, array_merge($keys, [$key]));

                if (count($sub)) {
                    return $sub;
                }
            } elseif (self::strtolower($value) === self::strtolower($search)) {
                return array_merge($keys, [$key]);
            }
        }

        return [];
    }

    /**
     * Find parent from assoc array
     *
     * @since    7.0.0
     **/
    public static function array_find_parent($array, $needle, $parent = null)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $pass = $parent;

                if (is_string($key)) {
                    $pass = $key;
                }
                $found = self::array_find_parent($value, $needle, $pass);

                if ($found !== false) {
                    return $found;
                }
            } elseif (self::strtolower($key) === self::strtolower($needle)) {
                return $parent;
            }
        }

        return false;
    }

    /**
     * Recursive Array Search
     *
     * @since    4.2.0
     *
     * @version  1.3.1
     */
    public static function recursive_array_search($needle, $haystack, $relative = false)
    {
        if (!empty($needle) && !empty($haystack) && is_array($haystack)) {
            foreach ($haystack as $key => $value) {
                if (is_array($value) === true) {
                    return self::recursive_array_search($needle, $value, $relative);
                } else {
                    /* ver 1.1.0 */
                    $value  = trim($value);
                    $needed = array_filter(array_map('trim', explode(',', $needle)));

                    foreach ($needed as $need) {
                        if ($relative === true) {
                            if (stripos($value, $need, 0) !== false) {
                                return $value;
                            }
                        } else {
                            if (self::strtolower($need) == self::strtolower($value)) {
                                return $value;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /*
     * Check is attribute exists in the shortcodes
    */
    public static function is_attribute_exists($find, $atts)
    {

        if (is_array($atts)) {
            foreach ($atts as $key => $val) {
                if (is_numeric($key)) {
                    if ($val === $find) {
                        return true;
                    }
                } else {
                    if ($key === $find) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /*
     * Print country flag
     */
    public static function admin_country_flag($country_code = '', $size = '21px')
    {

        if (empty($country_code)) {
            $API          = CFGP_Cache::get('API');
            $country_code = $API['country_code'];
        }

        $flag_slug = trim(strtolower($country_code));

        $md5 = md5($flag_slug.$size);

        if ($cache = CFGP_Cache::get("admin_country_flag_{$md5}")) {
            return $cache;
        }

        $flag = '';

        if (file_exists(CFGP_ROOT.'/assets/flags/4x3/'.$flag_slug.'.svg')) {
            $flag = sprintf('<img src="%s" alt="%s" style="max-width:%s;">', CFGP_ASSETS.'/flags/4x3/'.$flag_slug.'.svg', $flag_slug, $size);
        }

        CFGP_Cache::set("admin_country_flag_{$md5}", $flag);

        return $flag;
    }

    /*
     * Request Integer
     */
    public static function request_int($name, $default = 0, $session = false, $session_name = null)
    {

        if (!$session_name) {
            $session_name = $name;
        }

        if ($session === true) {
            if ($return = CFGP_Cache::get($session_name)) {
                return $return;
            }
        }

        $return = absint(filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT, [
            'options' => [
                'default' => filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT, [
                    'options' => [
                        'default' => $default,
                    ],
                ]),
            ],
        ]));

        if ($session === true) {
            CFGP_Cache::set($session_name, $return);
        }

        return $return;
    }

    /*
     * Request Float
     */
    public static function request_float($name, $default = 0, $session = false, $session_name = null)
    {
        if (!$session_name) {
            $session_name = $name;
        }

        if ($session === true) {
            if ($return = CFGP_Cache::get($session_name)) {
                return $return;
            }
        }

        $return = floatval(filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, [
            'options' => [
                'default' => filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_FLOAT, [
                    'options' => [
                        'default' => $default,
                    ],
                ]),
            ],
        ]));

        if ($session === true) {
            CFGP_Cache::set($session_name, $return);
        }

        return $return;
    }

    /*
     * Request string
     */
    public static function request_string($name, $default = null, $session = false, $session_name = null)
    {
        if (!$session_name) {
            $session_name = $name;
        }

        if ($session === true) {
            if ($return = CFGP_Cache::get($session_name)) {
                return $return;
            }
        }

        $return = sanitize_text_field($_REQUEST[$name] ?? $default);

        if ($session === true) {
            CFGP_Cache::set($session_name, $return);
        }

        return $return;
    }

    /*
     * Request Emain
     */
    public static function request_email($name, $default = null)
    {
        return sanitize_email(filter_input(INPUT_POST, $name, FILTER_SANITIZE_EMAIL, [
            'options' => [
                'default' => filter_input(INPUT_GET, $name, FILTER_SANITIZE_EMAIL, [
                    'options' => [
                        'default' => $default,
                    ],
                ]),
            ],
        ]));
    }

    /*
     * Request Bool
     */
    public static function request_bool($name)
    {
        return (isset($_REQUEST[$name]) && sanitize_text_field($_REQUEST[$name]) == 'true');
    }

    /*
     * Request
     */
    public static function request($name, $default = '')
    {
        return (isset($_REQUEST[$name]) ? CFGP_Options::sanitize($_REQUEST[$name]) : $default);
    }

    /*
     * Returns API fields
     */
    public static function api($name = false, $default = '')
    {
        $API = null;

        if (CFGP_Cache::get('API')) {
            $API = CFGP_Cache::get('API');
        }

        if (empty($name)) {
            return apply_filters('cfgp/api/return', ($API ? $API : $default), $API, $default);
        } else {
            return apply_filters('cfgp/api/return/' . $name, ($API[$name] ?? $default), $API, $default);
        }
    }

    /*
     * Next level of var_dump()
     */
    public static function dump()
    {
        if (func_num_args() === 1) {
            $a = func_get_args();
            echo wp_kses_post('<pre class="cfgp-dump">', var_export($a[0], true), '</pre>');
        } elseif (func_num_args() > 1) {
            echo wp_kses_post('<pre class="cfgp-dump">' . var_export(func_get_args(), true) . '</pre>');
        } else {
            throw new Exception('You must provide at least one argument to this function.');
        }
    }

    /*
     * Fragment cache
     */
    public static function fragment_caching($str, $cache = false, $wrap_before = '', $wrap_after = '')
    {
        if (W3TC_DYNAMIC_SECURITY && function_exists('w3tc_flush_all') && $cache) {
            return sprintf('<!-- mfunc %2$s -->%1$s<!-- /mfunc %2$s -->', $wrap_before.$str.$wrap_after, W3TC_DYNAMIC_SECURITY);
        }

        return $str;
    }

    /**
    * Get current page ID
    *
    * @autor    Ivijan-Stefan Stipic
    *
    * @since    1.0.7
    *
    * @version  2.0.0
    ******************************************************************/
    public static function get_page_ID()
    {
        global $post, $wp;

        if ($current_page_id = CFGP_Cache::get('current_page_id')) {
            return $current_page_id;
        }

        if ($id = self::get_page_ID__private__wp_query()) {
            return CFGP_Cache::set('current_page_id', $id);
        } elseif ($id = self::get_page_ID__private__get_the_id()) {
            return CFGP_Cache::set('current_page_id', $id);
        } elseif (!is_null($post) && isset($post->ID) && !empty($post->ID)) {
            return CFGP_Cache::set('current_page_id', $post->ID);
        } elseif ($post = self::get_page_ID__private__GET_post()) {
            return CFGP_Cache::set('current_page_id', $post);
        } elseif ($p = self::get_page_ID__private__GET_p()) {
            return CFGP_Cache::set('current_page_id', $p);
        } elseif ($page_id = self::get_page_ID__private__GET_page_id()) {
            return CFGP_Cache::set('current_page_id', $page_id);
        } elseif (!is_admin() && $id = self::get_page_ID__private__query()) {
            return $id;
        } elseif ($id = self::get_page_ID__private__page_for_posts()) {
            return CFGP_Cache::set('current_page_id', get_option('page_for_posts'));
        } elseif ($wp && isset($wp->request) && function_exists('get_page_by_path') && ($current_page = get_page_by_path($wp->request))) {
            $page_id = CFGP_Cache::set('current_page_id', $current_page->ID);
        }

        return false;
    }

    // Get page ID by using get_the_id() function
    protected static function get_page_ID__private__get_the_id()
    {
        if (function_exists('get_the_id')) {
            if ($id = get_the_ID()) {
                return $id;
            }
        }

        return false;
    }

    // Get page ID by wp_query
    protected static function get_page_ID__private__wp_query()
    {
        global $wp_query;

        return ((!is_null($wp_query) && isset($wp_query->post) && isset($wp_query->post->ID) && !empty($wp_query->post->ID)) ? $wp_query->post->ID : false);
    }

    // Get page ID by GET[post] in edit mode
    protected static function get_page_ID__private__GET_post()
    {
        return ((isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'edit') && (isset($_GET['post']) && is_numeric($_GET['post'])) ? absint($_GET['post']) : false);
    }

    // Get page ID by GET[page_id]
    protected static function get_page_ID__private__GET_page_id()
    {
        return ((isset($_GET['page_id']) && is_numeric($_GET['page_id'])) ? absint($_GET['page_id']) : false);
    }

    // Get page ID by GET[p]
    protected static function get_page_ID__private__GET_p()
    {
        return ((isset($_GET['p']) && is_numeric($_GET['p'])) ? absint($_GET['p']) : false);
    }

    // Get page ID by OPTION[page_for_posts]
    protected static function get_page_ID__private__page_for_posts()
    {
        $page_for_posts = get_option('page_for_posts');

        return (!is_admin() && 'page' == get_option('show_on_front') && $page_for_posts ? absint($page_for_posts) : false);
    }

    // Get page ID by mySQL query
    protected static function get_page_ID__private__query()
    {
        global $wpdb;
        $actual_link = rtrim(sanitize_text_field($_SERVER['REQUEST_URI']), '/');
        $parts       = explode('/', $actual_link);

        if (!empty($parts)) {
            $slug = end($parts);

            if (!empty($slug)) {
                if ($post_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT `{$wpdb->posts}`.`ID` FROM `{$wpdb->posts}`
						WHERE
							`{$wpdb->posts}`.`post_status` = %s
						AND
							`{$wpdb->posts}`.`post_name` = %s
						AND
							TRIM(`{$wpdb->posts}`.`post_name`) <> ''
						LIMIT 1",
                        'publish',
                        esc_sql(sanitize_title($slug))
                    )
                )) {
                    return CFGP_Cache::set('current_page_id', absint($post_id));
                }
            }
        }

        return false;
    }
    /**
    * END Get current page ID
    *****************************************************************/

    /**
    * Get current page object
    *
    * @autor    Ivijan-Stefan Stipic
    *
    * @since    8.0.0
    *
    * @version  1.0.0
    ******************************************************************/
    public static function get_page()
    {
        global $wp;

        // Get current page from cache
        $current_page = CFGP_Cache::get('get_page');

        // Get post by ID
        if (!$current_page) {
            $current_page = (isset($wp->query_vars['p']) ? get_post(absint($wp->query_vars['p'])) : null);
        }

        // Get page by ID
        if (!$current_page) {
            $current_page = (isset($wp->query_vars['page_id']) ? get_post(absint($wp->query_vars['page_id'])) : null);
        }

        // Get post by date/time
        if (
            !$current_page
            && (
                isset($wp->query_vars['name'])
                || isset($wp->query_vars['year'])
                || isset($wp->query_vars['monthnum'])
                || isset($wp->query_vars['day'])
                || isset($wp->query_vars['hour'])
                || isset($wp->query_vars['minute'])
                || isset($wp->query_vars['second'])
            )
        ) {

            $attr = [];

            if (isset($wp->query_vars['name'])) {
                $attr['name'] = $wp->query_vars['name'];
            }

            if (
                isset($wp->query_vars['year'])
                || isset($wp->query_vars['monthnum'])
                || isset($wp->query_vars['day'])
                || isset($wp->query_vars['hour'])
                || isset($wp->query_vars['minute'])
                || isset($wp->query_vars['second'])
            ) {
                $attr['date_query'] = [];

                if (isset($wp->query_vars['year'])) {
                    $attr['date_query']['year'] = $wp->query_vars['year'];
                }

                if (isset($wp->query_vars['monthnum'])) {
                    $attr['date_query']['month'] = $wp->query_vars['monthnum'];
                }

                if (isset($wp->query_vars['day'])) {
                    $attr['date_query']['day'] = $wp->query_vars['day'];
                }

                if (isset($wp->query_vars['hour'])) {
                    $attr['date_query']['hour'] = $wp->query_vars['hour'];
                }

                if (isset($wp->query_vars['minute'])) {
                    $attr['date_query']['minute'] = $wp->query_vars['minute'];
                }

                if (isset($wp->query_vars['second'])) {
                    $attr['date_query']['second'] = $wp->query_vars['second'];
                }
            }

            $page = get_posts($attr);

            if ($page) {
                $current_page = $page[0];
            }
        }

        // Get page by GET pharam
        if (!$current_page) {
            $current_page = (isset($_GET['page_id']) ? get_post(absint($_GET['page_id'])) : null);
        }

        // Get post by GET pharam
        if (!$current_page) {
            $current_page = (isset($_GET['p']) ? get_post(absint($_GET['p'])) : null);
        }

        // Get page by path
        if (!$current_page && isset($wp->request) && !empty($wp->request)) {
            $current_page = get_page_by_path($wp->request);
        }

        return CFGP_Cache::set('get_page', $current_page);
    }

    /**
     * Check user's city for defender or seo redirection
     */
    public static function check_user_by_city($city)
    {
        if (empty($city)) {
            return false;
        }

        // Ensure city is always processed as an array
        $city = is_array($city) ? $city : [$city];

        // Transliterate and sanitize each city in the list
        $city = array_map([__CLASS__, 'transliterate'], $city);
        $city = array_map('sanitize_title', $city);

        // Get the current user's city from API and sanitize it
        $user_city = sanitize_title(self::api('city'));

        // Check if the user's city matches any of the cities in the list
        return in_array($user_city, $city, true);
    }

    /**
     * Check user's region for defender or seo redirection
     */
    public static function check_user_by_region($region)
    {
        if (empty($region)) {
            return false;
        }

        // Ensure region is always processed as an array
        $region = is_array($region) ? $region : [$region];

        // Transliterate and sanitize each region in the list
        $region = array_map([__CLASS__, 'transliterate'], $region);
        $region = array_map('sanitize_title', $region);

        // Get current user's region code and name from API
        $user_region_code = sanitize_title(self::api('region_code'));
        $user_region_name = sanitize_title(self::api('region'));

        // Check if either the region code or region name matches
        return (in_array($user_region_code, $region, true) || in_array($user_region_name, $region, true));
    }

    /**
     * Check user's country for defender or seo redirection
     */
    public static function check_user_by_country($country)
    {
        if (empty($country)) {
            return false;
        }

        // Ensure country is always processed as an array
        $country = is_array($country) ? $country : [$country];

        // Transliterate and sanitize each country in the list
        $country = array_map([__CLASS__, 'transliterate'], $country);
        $country = array_map('sanitize_title', $country);

        // Get current user's country code and name from API
        $user_country_code = sanitize_title(self::api('country_code'));
        $user_country_name = sanitize_title(self::api('country'));

        // Check if either the country code or country name matches
        return (in_array($user_country_code, $country, true) || in_array($user_country_name, $country, true));
    }

    /**
     * Check user's postcode for defender or seo redirection
     */
    public static function check_user_by_postcode($postcode)
    {
        if (empty($postcode)) {
            return false;
        }

        $postcode = is_array($postcode) ? $postcode : [$postcode];
        $postcode = array_map('sanitize_title', $postcode);

        $user_postcode = sanitize_title(self::api('postcode'));

        return in_array($user_postcode, $postcode, true);
    }

    /**
     * Replacemant for the mb_convert_encoding - Setup for the UCS-4
     */
    public static function mb_convert_encoding($string, $from = 'UTF-8', $to = 'UCS-4')
    {
        return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($m) {
            $char = current($m);
            $utf  = iconv($from, $to, $char);

            return sprintf('&#x%s;', ltrim(strtoupper(bin2hex($utf)), '0'));
        }, $string);
    }

    /**
     * Generate convert outoput
     */
    public static function generate_converter_output($amount, $symbol, $position = 'L', $separator = '')
    {
        if (in_array(strtoupper($position), ['L', 'LEFT', 'LEVO'], true) !== false) {
            return wp_kses_post(sprintf('%s%s%s', $symbol, $separator, $amount));
        } else {
            return wp_kses_post(sprintf('%s%s%s', $amount, $separator, $symbol));
        }
    }

    /*
     * Check is plugin active
     */
    public static function is_plugin_active($plugin)
    {
        static $active_plugins = [];

        if (!isset($active_plugins[$plugin])) {
            if (!function_exists('is_plugin_active')) {
                self::include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $active_plugins[$plugin] = is_plugin_active($plugin);
        }

        return $active_plugins[$plugin];
    }

    /*
     * Hook for the admin URL
     * @author        Ivijan-Stefan Stipic
     * @version       2.0.0
     * @since         7.11.3
    */
    public static function admin_url($str = '')
    {
        if (defined('CFGP_MULTISITE') && CFGP_MULTISITE && self::is_network_admin()) {
            return self_admin_url($str);
        }

        return admin_url($str);
    }

    /*
     * Hook is network admin
     * @author        Ivijan-Stefan Stipic
     * @return        boolean true/false
    */
    public static function is_network_admin()
    {
        return (function_exists('is_network_admin') && is_network_admin());
    }

    /**
     * Get post type
     */
    public static function get_post_type($find = false)
    {
        global $post, $parent_file, $typenow, $current_screen, $pagenow;

        if ($post_type = CFGP_Cache::get('get_post_type')) {
            if (is_array($find)) {
                return in_array($post_type, $find, true);
            } elseif (is_string($find)) {
                return ($post_type === $find);
            } else {
                return $post_type;
            }
        }

        $post_type = null;

        if ($post && (property_exists($post, 'post_type') || method_exists($post, 'post_type'))) {
            $post_type = $post->post_type;
        }

        if (empty($post_type) && !empty($current_screen) && (property_exists($current_screen, 'post_type') || method_exists($current_screen, 'post_type')) && !empty($current_screen->post_type)) {
            $post_type = $current_screen->post_type;
        }

        if (empty($post_type) && !empty($typenow)) {
            $post_type = $typenow;
        }

        if (empty($post_type) && function_exists('get_current_screen')) {
            $post_type = get_current_screen();
        }

        if (empty($post_type) && function_exists('get_post_type') && isset($_REQUEST['post']) && !empty($_REQUEST['post']) && ($get_post_type = get_post_type((int)$_REQUEST['post']))) {
            $post_type = $get_post_type;
        }

        if (empty($post_type) && isset($_REQUEST['post_type']) && !empty($_REQUEST['post_type'])) {
            $post_type = sanitize_key($_REQUEST['post_type']);
        }

        if (empty($post_type) && in_array($pagenow, ['edit.php', 'post-new.php'], true)) {
            $post_type = 'post';
        }

        $post_type = apply_filters('cfgp/get_post_type', $post_type);
        CFGP_Cache::set('get_post_type', $post_type);

        if (is_array($find)) {
            return in_array($post_type, $find, true);
        } elseif (is_string($find)) {
            return ($post_type === $find);
        } else {
            return $post_type;
        }
    }

    /**
     * Check if plugin has SEO redirection
     */
    public static function has_seo_redirection()
    {

        if (null !== ($exists = CFGP_Cache::get('has_seo_redirection'))) {
            return $exists;
        }

        global $wpdb;

        return CFGP_Cache::set('has_seo_redirection', ($wpdb->get_var("SELECT 1 FROM `{$wpdb->cfgp_seo_redirection}` WHERE 1=1 LIMIT 1") == 1));
    }

    /**
     * Convert bytes to human file size
     */
    public static function filesize($bytes, $decimals = 0, $short_name = false)
    {
        if ($bytes instanceof FileSystem\File) {
            $bytes = $bytes->size();
        }

        if ($bytes instanceof Data\File) {
            $bytes = $bytes->size();
        }

        if ($bytes instanceof Data\Folder) {
            $bytes = $bytes->size();
        }

        if ($short_name) {
            $size = [
                esc_html__('B', 'cf-geoplugin'),
                esc_html__('KB', 'cf-geoplugin'),
                esc_html__('MB', 'cf-geoplugin'),
                esc_html__('GB', 'cf-geoplugin'),
                esc_html__('TB', 'cf-geoplugin'),
                esc_html__('PB', 'cf-geoplugin'),
                esc_html__('EB', 'cf-geoplugin'),
                esc_html__('ZB', 'cf-geoplugin'),
                esc_html__('YB', 'cf-geoplugin'),
            ];
        } else {
            $size = [
                esc_html__('byte', 'cf-geoplugin'),
                esc_html__('kilobyte', 'cf-geoplugin'),
                esc_html__('megabyte', 'cf-geoplugin'),
                esc_html__('gigabyte', 'cf-geoplugin'),
                esc_html__('terabyte', 'cf-geoplugin'),
                esc_html__('petabyte', 'cf-geoplugin'),
                esc_html__('exabyte', 'cf-geoplugin'),
                esc_html__('zettabyte', 'cf-geoplugin'),
                esc_html__('yottabyte', 'cf-geoplugin'),
            ];
        }
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . ($size[$factor] ?? end($size));
    }

    /**
     * Check is WP REST API enabled
     */
    public static function is_rest_enabled()
    {
        if (null !== ($cache = CFGP_Cache::get('is_rest_enabled'))) {
            return $cache;
        }

        return CFGP_Cache::set('is_rest_enabled', (
            (defined('CFGP_REST_ENABLED') && CFGP_REST_ENABLED)
            && apply_filters('cfgp/rest/v1/enable', true)
            && apply_filters('json_enabled', true)
            && apply_filters('rest_enabled', true)
            && apply_filters('rest_jsonp_enabled', true)
        ));
    }

    /*
     * Get proper date format in users timezone
     *
     * @param  $format             Default: 'D, F d Y g:i A'
     * @param  $timestamp          Current time
     *
     * @return string
     */
    public function date($format = 'D, F d Y g:i A', $timestamp = null)
    {
        if (empty($timestamp)) {
            $timestamp = date('r');
        }

        $timezone = self::api('timezone');

        if (is_numeric($timestamp) && strlen((string)$timestamp) >= 10) {
            $timestamp = date('r', (int)$timestamp);
        }

        $date = new DateTimeImmutable(
            $timestamp,
            new DateTimeZone(date_default_timezone_get())
        );

        if ($timezone) {
            $date->setTimeZone(new DateTimeZone($timezone));
        }

        return $date->format($format);
    }

    /*
     * The include_once statement includes and evaluates the specified file during the execution of the script.
     *
     * @param  $path
     *
     * @return bool
     */
    public static function include_once($path)
    {
        if (!is_array($path)) {
            $path = [$path];
        }

        $path = self::convert_path($path);

        $i = 0;

        foreach ($path as $include) {
            if (file_exists($include)) {
                include_once $include;
                ++$i;
            }
        }

        return ($i > 0);
    }

    /*
     * The include expression includes and evaluates the specified file.
     *
     * @param  $path
     *
     * @return bool
     */
    public static function include($path)
    {
        if (!is_array($path)) {
            $path = [$path];
        }

        $path = self::convert_path($path);

        $i = 0;

        foreach ($path as $include) {
            if (file_exists($include)) {
                include $include;
                ++$i;
            }
        }

        return ($i > 0);
    }

    /*
     * Converts the file path to the format of its environment
     *
     * @param  $path
     *
     * @return string
     */
    public static function convert_path($path)
    {

        if ('\\' === DIRECTORY_SEPARATOR) {
            if (is_array($path)) {
                $path = array_map(function ($p) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $p);
                }, $path);
            } else {
                $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            }
        }

        return $path;
    }

    /*
     * Transliterator.
     * This transliteration is based on logic from WordPress plugin https://wordpress.org/plugins/serbian-transliteration/
     *
     * @source https://en.wikipedia.org/wiki/List_of_Unicode_characters
     * @author Ivijan-Stefan Stipic
     *
     * @param  $string
     *
     * @return string
     */
    public static function transliterate($string)
    {

        if (empty($string) || !is_string($string) || is_numeric($string)) {
            return $string;
        }

        $transliterate = apply_filters('cfgp/transliterate', [
            // A
            'A' => [
                'À',
                'Á',
                'Â',
                'Ã',
                'Ä',
                'Å',
                'Ā',
                'Ă',
                'Ą',
            ],
            'a' => [
                'à',
                'á',
                'â',
                'ã',
                'ä',
                'å',
                'ā',
                'ă',
                'ą',
            ],
            // AE
            'AE' => [
                'Æ',
            ],
            'ae' => [
                'æ',
            ],
            // B
            'B' => [
                'Ɓ',
                'Ƃ',
                'Ƅ',
                'Þ',
            ],
            'b' => [
                'ƀ',
                'ƃ',
                'þ',
            ],
            // C
            'C' => [
                'Ç',
                'Ć',
                'Ĉ',
                'Ċ',
                'Č',
                'Ƈ',
            ],
            'c' => [
                'ç',
                'ć',
                'ĉ',
                'ċ',
                'č',
                'ƈ',
            ],
            // D
            'D' => [
                'Ď',
                'Ɗ',
                'Ƌ',
            ],
            'd' => [
                'ď',
                'ƌ',
                'ƍ',
            ],
            // DJ
            'DJ' => [
                'Đ',
            ],
            'dj' => [
                'đ',
            ],
            // E
            'E' => [
                'Ǝ',
                'Ɛ',
                'È',
                'É',
                'Ê',
                'Ë',
                'Ē',
                'Ĕ',
                'Ė',
                'Ę',
                'Ě',
            ],
            'e' => [
                'ē',
                'ĕ',
                'ė',
                'ę',
                'ě',
                'è',
                'é',
                'ê',
                'ë',
            ],
            // ETH
            'eth' => [
                'ð',
            ],
            // F
            'F' => [
                'Ƒ',
            ],
            'f' => [
                'ƒ',
            ],
            // G
            'G' => [
                'Ɠ',
                'Ĝ',
                'Ğ',
                'Ġ',
                'Ģ',
            ],
            'g' => [
                'ĝ',
                'ğ',
                'ġ',
                'ģ',
            ],
            // H
            'H' => [
                'Ĥ',
                'Ħ',
            ],
            'h' => [
                'ĥ',
                'ħ',
            ],
            // I
            'I' => [
                'Ì',
                'Í',
                'Î',
                'Ï',
                'Ĩ',
                'Ī',
                'Ĭ',
                'Į',
                'İ',
                'Ɩ',
                'Ɨ',
            ],
            'i' => [
                'ĩ',
                'ī',
                'ĭ',
                'į',
                'ı',
                'ì',
                'í',
                'î',
                'ï',
            ],
            // IJ
            'IJ' => [
                'Ĳ',
            ],
            'ij' => [
                'ĳ',
            ],
            // J
            'J' => [
                'Ĵ',
            ],
            'j' => [
                'ĵ',
            ],
            // K
            'K' => [
                'Ƙ',
                'Ķ',
            ],
            'k' => [
                'ķ',
                'ĸ',
                'ƙ',
            ],
            // L
            'L' => [
                'Ĺ',
                'Ļ',
                'Ľ',
                'Ŀ',
                'Ł',
            ],
            'l' => [
                'ĺ',
                'ļ',
                'ľ',
                'ŀ',
                'ł',
                'ƚ',
                'ƛ',
            ],
            // LJ
            'LJ' => [
                'ǈ',
            ],
            'lj' => [
                'ǉ',
            ],
            // N
            'N' => [
                'Ń',
                'Ñ',
                'Ņ',
                'Ň',
                'Ɲ',
            ],
            'n' => [
                'ń',
                'ņ',
                'ň',
                'ŉ',
                'ñ',
                'ƞ',
            ],
            // NJ
            'NJ' => [
                'Ǌ',
            ],
            'nj' => [
                'ǌ',
            ],
            // O
            'O' => [
                'Ō',
                'Ŏ',
                'Ő',
                'Ò',
                'Ó',
                'Ô',
                'Õ',
                'Ö',
                'Ø',
                'Ơ',
                'Ɵ',
                'Ɔ',
            ],
            'o' => [
                'ò',
                'ó',
                'ô',
                'õ',
                'ö',
                'ø',
                'ŏ',
                'ő',
                'ơ',
            ],
            // OE
            'OE' => [
                'Œ',
            ],
            'oe' => [
                'œ',
            ],
            // P
            'P' => [
                'Ƥ',
            ],
            'p' => [
                'ƥ',
            ],
            // R
            'R' => [
                'Ŕ',
                'Ŗ',
                'Ř',
            ],
            'r' => [
                'ŕ',
                'ŗ',
                'ř',
            ],
            // S
            'S' => [
                'Ś',
                'Ŝ',
                'Ş',
                'Š',
                'ß',
            ],
            's' => [
                'ś',
                'ŝ',
                'ş',
                'š',
                'ſ',
            ],
            // T
            'T' => [
                'Ţ',
                'Ť',
                'Ŧ',
                'Ʈ',
                'Ƭ',
            ],
            't' => [
                'ţ',
                'ť',
                'ŧ',
                'ƫ',
                'ƭ',
            ],
            // U
            'U' => [
                'Ù',
                'Ú',
                'Û',
                'Ü',
                'Ũ',
                'Ū',
                'Ŭ',
                'Ů',
                'Ű',
                'Ų',
                'Ʋ',
                'Ư',
            ],
            'u' => [
                'ũ',
                'ū',
                'ŭ',
                'ů',
                'ű',
                'ų',
                'ù',
                'ú',
                'û',
                'ü',
                'ư',
            ],
            // W
            'W' => [
                'Ŵ',
                'Ẁ',
                'Ẃ',
                'Ẅ',
            ],
            'w' => [
                'ŵ',
                'ẃ',
                'ẁ',
                'ẅ',
            ],
            // Y
            'Y' => [
                'Ŷ',
                'Ÿ',
                'Ý',
                'Ƴ',
                'ƴ',
            ],
            'y' => [
                'ŷ',
                'ý',
                'ÿ',
                'ƴ',
            ],
            // Z
            'Z' => [
                'Ź',
                'Ż',
                'Ž',
                'Ƶ',
            ],
            'z' => [
                'ź',
                'ż',
                'ž',
                'ƶ',
            ],
        ], $string);

        foreach ($transliterate as $replace => $find) {
            $string = str_replace($find, $replace, $string);
        }

        return $string;
    }

    /*
     * Geo Controller using a bit different way to format content
     *
     * @param  $string
     *
     * @return string
     */
    public static function the_content($string)
    {
        $string = htmlspecialchars_decode($string);
        $string = str_replace(']]>', ']]&gt;', $string);

        return $string;
    }

    /*
     * Geo Controller using a bit different way to format string to lowercase
     *
     * @param  $string
     *
     * @return string
     */
    public static function strtolower($string)
    {
        return (function_exists('mb_strtolower') ? mb_strtolower($string) : strtolower($string));
    }

    /*
     * Adding safe hash functionality
     *
     * @param  $data
     * @param  $algo
     * @param  $binary
     * @param  $options
     *
     * @return string
     */
    public static function hash($data, $algo = 'sha512', $binary = false)
    {
        // Check if the hash and hash_algos functions exist
        if (function_exists('hash') && function_exists('hash_algos')) {
            $algos = hash_algos();

            // Check if the requested algorithm exists
            if (in_array($algo, $algos, true)) {
                return hash($algo, $data, $binary);
            }

            // Fallback for specific algorithms
            switch ($algo) {
                case 'sha512':
                    if (in_array('whirlpool', $algos, true)) {
                        return hash('whirlpool', $data, $binary);
                    }
                    break;

                case 'sha256':
                    if (in_array('ripemd256', $algos, true)) {
                        return hash('ripemd256', $data, $binary);
                    } elseif (in_array('snefru', $algos, true)) {
                        return hash('snefru', $data, $binary);
                    }
                    break;

                case 'md5':
                    if (in_array('ripemd128', $algos, true)) {
                        return hash('ripemd128', $data, $binary);
                    }
                    break;

                case 'ripemd128':
                    if (in_array('md5', $algos, true)) {
                        return hash('md5', $data, $binary);
                    }
                    break;

                default:
                    break;
            }
        }

        // Return md5 as the last option
        return md5($data);
    }

    /*
     * Is dev mode
     *
     * @return bool
     */
    public static function dev_mode()
    {
        return defined('CFGP_DEV_MODE') && CFGP_DEV_MODE;
    }

    /*
     * Is dev mode
     *
     * @return bool
     */
    public static function allowed_html_tags_for_page()
    {
        $wp_kses_allowed_html = wp_kses_allowed_html('post');

        // Common attributes for most tags
        $common_attributes = [
            'name'          => [],
            'style'         => [],
            'class'         => [],
            'id'            => [],
            'disabled'      => [],
            'readonly'      => [],
            'tabindex'      => [],
            'aria-hidden'   => [],
            'aria-help'     => [],
            'aria-label'    => [],
            'aria-expanded' => [],
            'role'          => [],
            'autocomplete'  => [],
            'placeholder'   => [],
        ];

        // Extend the allowed tags with form-related tags and their attributes
        $wp_kses_allowed_html = array_merge($wp_kses_allowed_html, [
            'input' => array_merge($common_attributes, [
                'type'           => [],
                'value'          => [],
                'data-url'       => [],
                'checked'        => [],
                'min'            => [],
                'max'            => [],
                'formnovalidate' => [],
                'formtarget'     => [],
                'formmethod'     => [],
                'formenctype'    => [],
                'formaction'     => [],
                'form'           => [],
            ]),
            'textarea' => $common_attributes,
            'select'   => array_merge($common_attributes, [
                'multiple'           => [],
                'data-type'          => [],
                'data-country_codes' => [],
                'data-placeholder'   => [],
                'data-select2-id'    => [],
            ]),
            'option' => array_merge($common_attributes, [
                'value'           => [],
                'selected'        => [],
                'data-select2-id' => [],
            ]),
            'optgroup' => $common_attributes,
            'form'     => array_merge($common_attributes, [
                'method'     => [],
                'enctype'    => [],
                'novalidate' => [],
                'rel'        => [],
                'target'     => [],
            ]),
        ]);

        return $wp_kses_allowed_html;
    }

    /*
     * Checks if Redis Cache exists
     * @verson    1.0.1
     */
    public static function redis_cache_exists()
    {
        static $has_redis;

        if (null === $has_redis) {
            if (apply_filters('cfgp_enable_redis', true)) {
                $has_redis = class_exists('Redis', false);
            } else {
                $has_redis = false;
            }
        }

        return $has_redis;
    }

    /*
     * Insert object after defined object in array
     *
     * @return array
     */
    public static function array_insert_after_key($current_array, $insert_after_index, $new_array)
    {
        // Only if is array
        if (is_array($current_array)) {
            // New collections
            $return_array = [];
            // Let's search
            $i = 0;

            foreach ($current_array as $key => $obj) {
                if (is_numeric($key)) {
                    $key = ($key + $i);
                }
                // Save current
                $return_array[$key] = $obj;

                // Append
                if ($key == $insert_after_index) {
                    foreach ($new_array as $new_key => $new_obj) {
                        if (is_numeric($key)) {
                            ++$i;
                            $return_array[$key + $i] = $new_obj;
                        } else {
                            $return_array[$new_key] = $new_obj;
                        }
                    }
                }
            }

            // Return new aray
            return $return_array;
        }

        // Let's keep same data
        return $current_array;
    }
}
endif;
