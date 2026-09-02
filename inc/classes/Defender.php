<?php

/**
 * Defender
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

if (!class_exists('CFGP_Defender', false)) : class CFGP_Defender extends CFGP_Global
{
    public function __construct()
    {
        $this->add_action('init', 'recovery_endpoint', 0);
        $this->add_action('init', 'clear_recovery_after_login', 0);
        $this->add_action('init', 'tor_protection', 1);
        $this->add_action('init', 'protect', 2);
    }

    /*
     * TOR Network Control
     *
     * Control the access of TOR network visitors.
     */
    public function tor_protection()
    {
        if (self::can_bypass_protection() || self::can_access_login_recovery()) {
            return;
        }

        switch ((int)CFGP_Options::get('block_tor_network', 0)) {

            // TOR Access: Unrestricted
            default:
            case 0:
                return;
                break;

                // TOR Access: Denied
            case 1:
                if ((int)CFGP_U::api('is_tor') === 1) {
                    if (function_exists('http_response_code')) {
                        http_response_code(403);
                    } else {
                        header('HTTP/1.0 403 Forbidden', true, 403);
                    }

                    die(wp_kses_post(wpautop(html_entity_decode(stripslashes((string) apply_filters(
                        'cfgp/defender/tor/denied/message',
                        sprintf(
                            '<h1>%s</h1><p>%s</p>',
                            __('403 Forbidden', 'cf-geoplugin'),
                            __('Sorry, users accessing via the TOR network are not allowed on this site.', 'cf-geoplugin')
                        )
                    ))))));
                }
                break;

                // TOR Access: Exclusive
            case 2:
                if ((int)CFGP_U::api('is_tor') === 0) {
                    if (function_exists('http_response_code')) {
                        http_response_code(403);
                    } else {
                        header('HTTP/1.0 403 Forbidden', true, 403);
                    }

                    die(wp_kses_post(wpautop(html_entity_decode(stripslashes((string) apply_filters(
                        'cfgp/defender/tor/exclusive/message',
                        sprintf(
                            '<h1>%s</h1><p>%s</p>',
                            __('403 Forbidden', 'cf-geoplugin'),
                            __('This site is accessible exclusively to TOR network users. Please access via the TOR network.', 'cf-geoplugin')
                        )
                    ))))));
                }
                break;
        }
    }

    // Protect site from visiting
    public function protect()
    {
        if (self::can_bypass_protection() || self::can_access_login_recovery()) {
            return;
        }

        // Defender is disabled ???
        if (CFGP_Options::get('enable_defender', 0) == 0) {
            return;
        }

        $ip = CFGP_U::api('ip');

        // Block on error
        if (empty($ip) || CFGP_U::api('error')) {
            return;
        }

        // Whitelist IP addresses
        $whitelist_ips = preg_split('/[,;\n|]+/', CFGP_Options::get('ip_whitelist'));
        $whitelist_ips = array_map('trim', $whitelist_ips);
        $whitelist_ips = array_filter($whitelist_ips);
        $whitelist_ips = apply_filters('cfgp/defender/whitelist/ip', $whitelist_ips, $ip);

        if (
            !empty($whitelist_ips)
            && is_array($whitelist_ips)
            && in_array($ip, $whitelist_ips, true) !== false
        ) {
            return;
        }

        // Check settings
        if ($this->check()) {
            if (function_exists('http_response_code')) {
                http_response_code(403);
            } else {
                header('HTTP/1.0 403 Forbidden', true, 403);
            }

            die(wp_kses_post(wpautop(html_entity_decode(stripslashes((string) (CFGP_Options::get('block_country_messages') ?? ''))))));
        }

        // Block Spam
        if (
            CFGP_Options::get('enable_spam_ip', 0)
            && CFGP_License::level(CFGP_Options::get('license_sku')) > 0
        ) {
            if (CFGP_U::api('is_spam')) {
                if (function_exists('http_response_code') && version_compare(PHP_VERSION, '5.4', '>=')) {
                    http_response_code(403);
                } else {
                    header($this->header, true, 403);
                }

                die(wp_kses_post(wpautop(html_entity_decode(stripslashes((string) (CFGP_Options::get('block_country_messages') ?? ''))))));
            }
        }
    }

    // Check what to do with user
    public function check()
    {
        if (self::can_bypass_protection()) {
            return false;
        }

        // Let's block proxy
        if (CFGP_Options::get('block_proxy', 0) && CFGP_U::api('is_proxy') == 1) {
            return true;
        }

        // Explode all IP's and block them... Yeah baby!
        $ips = preg_split('/[,;\n|]+/', CFGP_Options::get('block_ip', '') ?? '');
        $ips = array_map('trim', $ips);
        $ips = array_filter($ips);

        if (in_array(CFGP_U::api('ip'), $ips, true) !== false) {
            return true;
        }

        // Get countries
        $block_country = self::process_block_data('block_country');

        // Get regions
        $block_region = self::process_block_data('block_region');

        // Get cities
        $block_city = self::process_block_data('block_city');

        // Generate redirection mode
        $mode = [ null, 'country', 'region', 'city' ];
        $mode = $mode[ count(array_filter(array_map(
            function ($obj) {
                return !empty($obj);
            },
            [
                $block_country,
                $block_region,
                $block_city,
            ]
        ))) ];

        if (empty($block_region) && !empty($block_city)) {
            $mode = 'country_city';
        }

        // Switch mode
        switch ($mode) {
            case 'country':
                if (CFGP_U::check_user_by_country($block_country)) {
                    return true;
                }
                break;
            case 'region':
                if (
                    CFGP_U::check_user_by_region($block_region)
                    && CFGP_U::check_user_by_country($block_country)
                ) {
                    return true;
                }
                break;
            case 'city':
                if (
                    CFGP_U::check_user_by_city($block_city)
                    && CFGP_U::check_user_by_region($block_region)
                    && CFGP_U::check_user_by_country($block_country)
                ) {
                    return true;
                }
                break;
            case 'country_city':
                if (
                    CFGP_U::check_user_by_city($block_city)
                    && CFGP_U::check_user_by_country($block_country)
                ) {
                    return true;
                }
                break;
        }

        // Hey, we are all good. Right?
        return false;
    }

    /**
     * The only global Defender bypass is an authenticated site administrator.
     *
     * @return bool
     */
    private static function can_bypass_protection()
    {
        return (
            function_exists('is_user_logged_in')
            && is_user_logged_in()
            && function_exists('current_user_can')
            && current_user_can('manage_options')
        );
    }

    /**
     * Generate a one-time recovery URL for an authenticated administrator.
     *
     * @param int $user_id Administrator user ID.
     * @return string|false
     */
    public static function generate_recovery_link($user_id)
    {
        if (!function_exists('random_bytes')) {
            return false;
        }

        try {
            $token = self::base64url_encode(random_bytes(32));
        } catch (Exception $exception) {
            return false;
        }

        self::save_recovery_record([
            'hash'       => self::secret_hash($token),
            'created_at' => CFGP_TIME,
            'created_by' => absint($user_id),
            'used_at'    => null,
            'revoked'    => false,
        ]);

        return add_query_arg('cfgp_recovery', '1', home_url('/')) . '#token=' . $token;
    }

    /**
     * Revoke the currently stored recovery token.
     */
    public static function revoke_recovery_link()
    {
        $record = self::get_recovery_record();

        // Invalidate every outstanding restricted login session as well.
        self::revoke_recovery_sessions();

        if (is_array($record)) {
            $record['hash']    = null;
            $record['revoked'] = true;
            self::save_recovery_record($record);
        }
    }

    /**
     * Determine whether an unused recovery link currently exists.
     *
     * @return bool
     */
    public static function has_active_recovery_link()
    {
        $record = self::get_recovery_record();

        return (
            is_array($record)
            && !empty($record['hash'])
            && empty($record['used_at'])
            && empty($record['revoked'])
        );
    }

    /**
     * Render or process the isolated public recovery page.
     */
    public function recovery_endpoint()
    {
        if (!self::is_recovery_request()) {
            return;
        }

        self::send_recovery_headers();

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = isset($_POST['cfgp_recovery_token'])
                ? trim(sanitize_text_field(wp_unslash($_POST['cfgp_recovery_token'])))
                : '';

            if (self::validate_recovery_token($token)) {
                self::create_recovery_session();
                wp_safe_redirect(wp_login_url(admin_url()));
                exit;
            }
        }

        self::render_recovery_page();
        exit;
    }

    /**
     * Clear the restricted recovery session after any WordPress login.
     */
    public function clear_recovery_after_login()
    {
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            self::delete_recovery_session();
        }
    }

    /**
     * Recovery session may only bypass Defender for the WordPress login flow.
     *
     * @return bool
     */
    private static function can_access_login_recovery()
    {
        if (!self::is_login_request()) {
            return false;
        }

        $token = isset($_COOKIE['cfgp_recovery_session'])
            ? sanitize_text_field(wp_unslash($_COOKIE['cfgp_recovery_session']))
            : '';

        if (empty($token)) {
            return false;
        }

        $hash    = self::secret_hash($token);
        $session = get_transient('cfgp_defender_recovery_session_' . $hash);

        return (
            is_array($session)
            && isset($session['hash'])
            && isset($session['version'])
            && hash_equals($session['hash'], $hash)
            && absint($session['version']) === self::get_recovery_session_version()
        );
    }

    /**
     * Validate a submitted one-time token without exposing its state.
     *
     * @param string $token Recovery token.
     * @return bool
     */
    private static function validate_recovery_token($token)
    {
        if (empty($token) || self::recovery_rate_limited()) {
            return false;
        }

        $record = self::get_recovery_record();
        $hash   = self::secret_hash($token);

        if (
            !is_array($record)
            || !empty($record['revoked'])
            || !empty($record['used_at'])
            || empty($record['hash'])
            || !hash_equals($record['hash'], $hash)
        ) {
            self::record_recovery_failure();

            return false;
        }

        // Invalidate the token before creating a restricted login session.
        $record['hash']    = null;
        $record['used_at'] = CFGP_TIME;
        self::save_recovery_record($record);
        self::clear_recovery_rate_limit();

        return true;
    }

    /**
     * Create an independent, restricted fifteen-minute login session.
     */
    private static function create_recovery_session()
    {
        $token = self::base64url_encode(random_bytes(32));
        $hash  = self::secret_hash($token);
        $ttl   = 15 * MINUTE_IN_SECONDS;

        set_transient('cfgp_defender_recovery_session_' . $hash, [
            'hash'    => $hash,
            'version' => self::get_recovery_session_version(),
        ], $ttl);

        self::set_recovery_cookie($token, CFGP_TIME + $ttl);
    }

    /**
     * Remove the restricted login session and its cookie.
     */
    private static function delete_recovery_session()
    {
        $token = isset($_COOKIE['cfgp_recovery_session'])
            ? sanitize_text_field(wp_unslash($_COOKIE['cfgp_recovery_session']))
            : '';

        if (!empty($token)) {
            delete_transient('cfgp_defender_recovery_session_' . self::secret_hash($token));
        }

        self::set_recovery_cookie('', CFGP_TIME - HOUR_IN_SECONDS);
    }

    /**
     * Invalidate every active restricted login session without storing its token.
     */
    private static function revoke_recovery_sessions()
    {
        self::save_recovery_session_version(self::get_recovery_session_version() + 1);
    }

    /**
     * @return int
     */
    private static function get_recovery_session_version()
    {
        $option = CFGP_NAME . '-defender-recovery-session-version';
        $version = CFGP_NETWORK_ADMIN ? get_site_option($option, 0) : get_option($option, 0);

        return absint($version);
    }

    /**
     * @param int $version Recovery session version.
     */
    private static function save_recovery_session_version($version)
    {
        $option = CFGP_NAME . '-defender-recovery-session-version';

        if (CFGP_NETWORK_ADMIN) {
            update_site_option($option, absint($version), false);
        } else {
            update_option($option, absint($version), false);
        }
    }

    /**
     * Store the recovery cookie only for the login flow, never for site access.
     */
    private static function set_recovery_cookie($token, $expires)
    {
        if (headers_sent()) {
            return false;
        }

        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        $secure = function_exists('is_ssl') ? is_ssl() : false;

        if (PHP_VERSION_ID >= 70300) {
            return setcookie('cfgp_recovery_session', $token, [
                'expires'  => $expires,
                'path'     => '/',
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }

        // PHP 7.0-7.2 do not support the options-array cookie signature.
        return setcookie('cfgp_recovery_session', $token, $expires, '/', $domain, $secure, true);
    }

    /**
     * Limit invalid public recovery attempts by remote address.
     *
     * @return bool
     */
    private static function recovery_rate_limited()
    {
        $attempts = get_transient(self::recovery_rate_limit_key());

        return is_array($attempts) && isset($attempts['count']) && absint($attempts['count']) >= 5;
    }

    /**
     * Record a failed public recovery attempt for fifteen minutes.
     */
    private static function record_recovery_failure()
    {
        $key      = self::recovery_rate_limit_key();
        $attempts = get_transient($key);
        $count    = is_array($attempts) && isset($attempts['count']) ? absint($attempts['count']) : 0;

        set_transient($key, ['count' => $count + 1], 15 * MINUTE_IN_SECONDS);
    }

    /**
     * Remove the current IP's temporary recovery rate limit.
     */
    private static function clear_recovery_rate_limit()
    {
        delete_transient(self::recovery_rate_limit_key());
    }

    /**
     * @return string
     */
    private static function recovery_rate_limit_key()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

        return 'cfgp_defender_recovery_rate_' . hash_hmac('sha256', $ip, wp_salt('auth'));
    }

    /**
     * @return bool
     */
    private static function is_recovery_request()
    {
        return isset($_GET['cfgp_recovery']) && (string) $_GET['cfgp_recovery'] === '1';
    }

    /**
     * @return bool
     */
    private static function is_login_request()
    {
        global $pagenow;

        return (
            $pagenow === 'wp-login.php'
            || (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'wp-login.php')
        );
    }

    /**
     * @param string $secret Secret value.
     * @return string
     */
    private static function secret_hash($secret)
    {
        return hash_hmac('sha256', $secret, wp_salt('auth'));
    }

    /**
     * @param string $value Binary value.
     * @return string
     */
    private static function base64url_encode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @return array|false
     */
    private static function get_recovery_record()
    {
        $option = CFGP_NAME . '-defender-recovery';

        return CFGP_NETWORK_ADMIN ? get_site_option($option, false) : get_option($option, false);
    }

    /**
     * @param array $record Recovery token metadata.
     */
    private static function save_recovery_record($record)
    {
        $option = CFGP_NAME . '-defender-recovery';

        if (CFGP_NETWORK_ADMIN) {
            update_site_option($option, $record, false);
        } else {
            update_option($option, $record, false);
        }
    }

    /**
     * Send the headers required for a secret-bearing public recovery page.
     */
    private static function send_recovery_headers()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Referrer-Policy: no-referrer');
        header('X-Robots-Tag: noindex, nofollow, noarchive');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Render a local recovery form without loading WordPress theme resources.
     */
    private static function render_recovery_page()
    {
        $action = esc_url(add_query_arg('cfgp_recovery', '1', home_url('/')));
        ?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php esc_html_e('Recovery Login', 'cf-geoplugin'); ?></title></head>
<body>
<main>
<h1><?php esc_html_e('Recovery Login', 'cf-geoplugin'); ?></h1>
<p><?php esc_html_e('Enter your one-time recovery token to continue to the WordPress login page.', 'cf-geoplugin'); ?></p>
<form method="post" action="<?php echo $action; ?>" id="cfgp-recovery-form">
<label for="cfgp_recovery_token"><?php esc_html_e('Recovery token', 'cf-geoplugin'); ?></label>
<input type="password" id="cfgp_recovery_token" name="cfgp_recovery_token" autocomplete="off" required>
<button type="submit"><?php esc_html_e('Continue to login', 'cf-geoplugin'); ?></button>
</form>
</main>
<script>
(function () {
    var hash = window.location.hash.match(/(?:^#|&)token=([^&]+)/);
    if (!hash) { return; }
    var input = document.getElementById('cfgp_recovery_token');
    input.value = decodeURIComponent(hash[1]);
    window.history.replaceState(null, '', window.location.pathname + window.location.search);
    document.getElementById('cfgp-recovery-form').submit();
}());
</script>
</body>
</html>
<?php
    }

    private static function process_block_data($option_key)
    {
        $block_data = CFGP_Options::get($option_key, []);

        if (!empty($block_data) && !is_array($block_data) && preg_match('/\]|\[/', $block_data)) {
            $block_data = explode(']|[', $block_data);
            $block_data = array_map(function ($match) {
                return trim($match, ' [],');
            }, $block_data);
        }

        if (!empty($block_data) && is_array($block_data)) {
            $block_data = array_filter($block_data);
            $block_data = array_unique($block_data);
        }

        return $block_data;
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
