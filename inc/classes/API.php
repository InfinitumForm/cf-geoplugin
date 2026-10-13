<?php

/**
 * Main API class
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

if (!class_exists('CFGP_API', false)) :
    class CFGP_API extends CFGP_Global
    {
        private $host;
        public function __construct($dry_run = false)
        {
            try {
                // Set host
                $this->host = CFGP_U::get_host(true);

                if ($dry_run !== true) {
                    // Collect geo data
                    $return = $this->get();

                    // Fix response
                    if (!is_array($return)) {
                        $return = [];
                    }

                    // Add filter
                    $return = array_merge(
                        apply_filters('cfgp/api/default/fields', CFGP_Defaults::API_RETURN),
                        $return
                    );

                    // Merge all
                    $return = apply_filters('cfgp/api/results', $return, CFGP_Defaults::API_RETURN);

                    // Save API data to array
                    CFGP_Cache::set('API', $return);
                }
            } catch (Exception $e) {
                throw new ErrorException(esc_html('CFGP ERROR: ' . $e->getMessage()));
            }
        }

        /**
         * Fetch new geo informations
         *
         * @since    8.0.0
         */
        public static function lookup($ip, $property = [])
        {
            return self::instance(true)->get($ip, $property);
        }

        /**
         * Get cache key
         *
         * @since    8.0.0
         */
        public static function cache_key($ip, $property = [])
        {
            // Keep property
            $property = shortcode_atts([
                'dns' => CFGP_Options::get('enable_dns_lookup'),
            ], $property);

            // Start building key
            $ip_slug = str_replace(['.', ':'], '_', $ip);

            // DNS check
            if ($property['dns']) {
                $ip_slug = $ip_slug . '_dns';
            }

            // Spam check
            $spam_check = ((
                CFGP_Options::get('enable_spam_ip', 0)
                && CFGP_Options::get('enable_defender', 0)
                && CFGP_License::level(CFGP_Options::get('license_sku')) > 0
            ) ? 'true' : 'false');

            if ($spam_check) {
                $ip_slug = $ip_slug . '_spam_check';
            }

            // Hash
            $ip_slug = CFGP_U::hash($ip_slug);

            // Return
            return $ip_slug;
        }

        private function renewal_domain()
        {
            $domain = strtolower(trim((string)$this->host));
            $domain = preg_replace('~^https?://~i', '', $domain);
            $domain = preg_replace('~^www\.~i', '', $domain);
            $domain = preg_replace('~/.*$~', '', $domain);
            $domain = preg_replace('~:\d+$~', '', $domain);

            return rtrim($domain, '.');
        }

        private function renewal_request_params()
        {
            $mode = $this->renewal_mode();
            if ($mode === 'off') {
                $this->renewal_debug('request_skipped', ['mode' => $mode, 'reason' => 'mode_off']);
                return [];
            }

            $license       = CFGP_License::get();
            $activation_id = (string)($license['id'] ?? '');
            $license_key   = (string)($license['key'] ?? '');
            $domain        = $this->renewal_domain();
            if (
                !preg_match('/^\d+$/', $activation_id)
                || empty($license_key)
                || empty($domain)
                || !hash_equals($domain, (string)$this->host)
            ) {
                $this->renewal_debug('request_skipped', [
                    'mode' => $mode,
                    'reason' => 'missing_or_noncanonical_identity',
                    'endpoint_host' => $this->host,
                    'local_license_present' => !empty($license_key),
                    'activation_id_present' => $activation_id !== '',
                    'fingerprint_prepared' => false,
                    'geo_cache_bypassed' => false,
                    'renewal_headers_attached' => false,
                ]);
                return [];
            }

            $fingerprint = hash('sha256', $license_key . '|' . $this->host);
            $attempt_key = 'cfgp-license-renewal-attempt-' . $mode . '-' . hash('sha256', $activation_id . '|' . $this->host);
            if (CFGP_DB_Cache::get($attempt_key)) {
                $this->renewal_debug('attempt_throttled', [
                    'mode' => $mode,
                    'activation_id' => $activation_id,
                    'fingerprint' => $fingerprint,
                    'endpoint_host' => $this->host,
                    'local_license_present' => true,
                    'activation_id_present' => true,
                    'fingerprint_prepared' => true,
                    'local_throttle_active' => true,
                    'geo_cache_bypassed' => false,
                    'renewal_headers_attached' => false,
                ]);
                return [];
            }

            // A cache failure must not turn an expired local date into a hard block.
            CFGP_DB_Cache::set($attempt_key, 1, 6 * HOUR_IN_SECONDS);
            $this->renewal_debug('request_generated', [
                'mode' => $mode,
                'activation_id' => $activation_id,
                'fingerprint' => $fingerprint,
                'endpoint_host' => $this->host,
                'local_license_present' => true,
                'activation_id_present' => true,
                'fingerprint_prepared' => true,
                'local_throttle_active' => false,
                'geo_cache_bypassed' => true,
                'renewal_headers_attached' => true,
            ]);

            return [
                'activation_id' => $activation_id,
                'fingerprint'   => $fingerprint,
            ];
        }

        private function renewal_debug($event, $context = [])
        {
            if ($this->renewal_mode() !== 'shadow'
                && (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG)) {
                return;
            }

            $fields = [];
            foreach (['mode', 'activation_id', 'status', 'reason', 'endpoint_host', 'local_license_present', 'fingerprint_prepared', 'activation_id_present', 'local_throttle_active', 'geo_cache_bypassed', 'renewal_headers_attached'] as $field) {
                if (isset($context[$field])) {
                    $fields[] = $field . '=' . (is_bool($context[$field]) ? ($context[$field] ? 'true' : 'false') : (string)$context[$field]);
                }
            }

            if (!empty($context['fingerprint'])) {
                $fingerprint = (string)$context['fingerprint'];
                $fields[] = 'fingerprint=' . substr($fingerprint, 0, 8) . '…' . substr($fingerprint, -4);
            }

            error_log('cfgp renewal ' . $event . (empty($fields) ? '' : ' ' . implode(' ', $fields)));
        }

        private function renewal_mode()
        {
            $mode = apply_filters('cfgp/license/renewal/mode', 'active');

            return in_array($mode, ['off', 'shadow', 'active'], true) ? $mode : 'off';
        }

        private function renewal_request_headers($renewal_request)
        {
            if (empty($renewal_request)) {
                return [];
            }

            $headers = [
                'Accept'                         => 'application/json',
                'X-CFGP-Renewal-Activation-ID'   => $renewal_request['activation_id'],
                'X-CFGP-Renewal-Fingerprint'     => $renewal_request['fingerprint'],
            ];
            if ($this->renewal_mode() === 'shadow') {
                $headers['X-CFGP-Renewal-Debug'] = '1';
                $this->renewal_debug('headers_attached', ['mode' => 'shadow']);
            }
            return $headers;
        }

        private function sync_license_renewal($response)
        {
            if ($this->renewal_mode() !== 'active' || empty($response['license_renewal']) || !is_array($response['license_renewal'])) {
                return;
            }

            $renewal = $response['license_renewal'];
            $this->renewal_debug('payload_received', [
                'activation_id' => ($renewal['activation_id'] ?? ''),
                'status' => ($renewal['status'] ?? ''),
            ]);
            $license = CFGP_License::get();
            $activation_id = (string)($license['id'] ?? '');
            if (
                !preg_match('/^\d+$/', $activation_id)
                || !isset($renewal['activation_id'], $renewal['expire'], $renewal['has_expired'], $renewal['status'])
                || !is_numeric($renewal['expire'])
                || (string)$renewal['activation_id'] !== $activation_id
            ) {
                $this->renewal_debug('sync_rejected', ['reason' => 'invalid_payload_or_activation']);
                return;
            }

            $status = strtolower((string)$renewal['status']);
            $has_expired = (int)$renewal['has_expired'];
            if ($status === 'active' && $has_expired === 0) {
                if ((int)$renewal['expire'] > 0 && (int)($license['expire'] ?? 0) > (int)$renewal['expire']) {
                    $this->renewal_debug('sync_rejected', ['reason' => 'stale_active_expiry']);
                    return;
                }
            } elseif (!in_array($status, ['expired', 'cancelled', 'refunded', 'revoked'], true)) {
                $this->renewal_debug('sync_rejected', ['reason' => 'unsupported_status', 'status' => $status]);
                return;
            }

            CFGP_License::set([
                'expire'      => (int)$renewal['expire'],
                'expire_date' => (string)($renewal['expire_date'] ?? ''),
                'expired'     => ($status === 'active' && $has_expired === 0 ? 0 : 1),
                'status'      => ($status === 'active' && $has_expired === 0),
            ]);
            $this->renewal_debug('sync_applied', [
                'activation_id' => $activation_id,
                'status' => $status,
            ]);
        }

        /**
         * Get geo informations
         *
         * @since    8.0.0
         */
        private function get($ip = null, $property = [])
        {
            // Default fields
            $default_fields = apply_filters('cfgp/api/default/fields', CFGP_Defaults::API_RETURN);

            // Get IP
            if (!empty($ip)) {
                if (CFGP_IP::filter($ip) === false) {
                    return $default_fields;
                }
            } else {
                $ip = CFGP_IP::get();
            }

            // If there is no IP return defaults
            if (empty($ip)) {
                return $default_fields;
            }

            // DNS control
            $check_dns = ($property['dns'] ?? CFGP_Options::get('enable_dns_lookup'));

            // Spam check
            $spam_check = ((
                CFGP_Options::get('enable_spam_ip', 0)
                && CFGP_Options::get('enable_defender', 0)
                && CFGP_License::level(CFGP_Options::get('license_sku')) > 0
            ) ? 'true' : 'false');

            // Get base currency
            if (isset($property['base_currency']) && $property['base_currency']) {
                $base_currency = $property['base_currency'];
            } elseif (CFGP_U::is_plugin_active('woocommerce/woocommerce.php') && CFGP_Options::get('enable-woocommerce', 0)) {
                $base_currency = (get_option('woocommerce_currency') ?? CFGP_Options::get('base_currency', 'USD'));
            } else {
                $base_currency = CFGP_Options::get('base_currency', 'USD');
            }

            // Default returns
            $return = [];

            // Hash IP slug
            $ip_slug = self::cache_key($ip, $property);
            // Renewal identity is independently throttled, so a fresh
            // entitlement check is not indefinitely hidden by geodata cache.
            $renewal_request = $this->renewal_request_params();

            if ($transient = CFGP_DB_Cache::get("cfgp-api-{$ip_slug}")) {
                $return = $transient;

                $client_date = new DateTimeImmutable(
                    date('c', CFGP_TIME),
                    new DateTimeZone(date_default_timezone_get())
                );

                if (isset($return['timezone']) && $return['timezone'] !== date_default_timezone_get()) {
                    if (in_array($return['timezone'], DateTimeZone::listIdentifiers(), true)) {
                        $new_client_date = $client_date->setTimeZone(new DateTimeZone($return['timezone']));
                    } else {
                        $new_client_date    = $client_date->setTimeZone(new DateTimeZone('UTC'));
                        $return['timezone'] = 'UTC';
                    }

                    $return['timestamp_readable'] = $new_client_date->format('c');
                    $return['timestamp']          = strtotime($return['timestamp_readable'] ?? '');
                    $return['current_date']       = $new_client_date->format('F j, Y');
                    $return['current_time']       = $new_client_date->format('H:i:s');
                } else {
                    $return['timestamp_readable'] = $client_date->format('c');
                    $return['timestamp']          = strtotime($return['timestamp_readable'] ?? '');
                    $return['current_date']       = $client_date->format('F j, Y');
                    $return['current_time']       = $client_date->format('H:i:s');
                }

                if (($lookup = CFGP_DB_Cache::get('cfgp-api-available-lookup-' . $this->host))) {
                    $return['available_lookup'] = $lookup;
                }

                // Calculate runtime
                $runtime = (floatval(microtime()) - floatval(CFGP_START_RUNTIME));

                if ($runtime < 0) {
                    $runtime = -$runtime;
                }

                $return['runtime'] = $runtime;
            }

            // Get new data
            if (empty($return) || !empty($renewal_request)) {
                // Build query
                $request_pharams = apply_filters('cfgp/api/get/curl/pharams', [
                    'ip'           => $ip,
                    'server_ip'    => CFGP_IP::server(),
                    'timestamp'    => CFGP_TIME,
                    'referer'      => $this->host,
                    'email'        => get_bloginfo('admin_email'),
                    'license'      => get_option('cf_geo_defender_api_key'), // we need to keep in track some old activation keys
                    'base_convert' => $base_currency,
                    'dns'          => ($check_dns/* && CFGP_License::level() >= 1*/ ? 'true' : 'false'),
                    'version'      => CFGP_VERSION,
                    'wp_version'   => get_bloginfo('version'),
                    'spam_check'   => $spam_check,
                ]);
                // Build URL
                $request_url = CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'main'];
                // Fetch new informations
                if (!empty($renewal_request)) {
                    $this->renewal_debug('http_sent', [
                        'mode' => $this->renewal_mode(),
                        'activation_id' => $renewal_request['activation_id'],
                        'fingerprint' => $renewal_request['fingerprint'],
                    ]);
                }
                $response = CFGP_U::api_request($request_url, $request_pharams, $this->renewal_request_headers($renewal_request));
                if (!empty($renewal_request)) {
                    $this->renewal_debug('response_received', ['mode' => $this->renewal_mode()]);
                }

                // Fallback to return by IP
                if (!$response) {
                    $response = CFGP_U::api_request(
                        CFGP_Defaults::falback_api_endpoints($request_url),
                        $request_pharams,
                        $this->renewal_request_headers($renewal_request)
                    );
                }

                // Defender may use the explicitly enabled emergency HTTP endpoint
                // only after all HTTPS geolocation requests have failed.
                if (!$response) {
                    $response = $this->defender_emergency_http_request($request_url, $request_pharams);
                }

                // Fix data and save to cache
                    if (!empty($response)) {
                        // Convert and merge
                        $response = apply_filters(
                        'cfgp/api/get/geodata',
                        array_merge($default_fields, $response),
                        $response,
                            $default_fields
                        );

                        // Synchronize the internal renewal payload before the
                        // generic response formatter converts nested arrays.
                        $this->sync_license_renewal($response);
                        unset($response['license_renewal']);

                        // If there is a error, display it
                        if (($response['error'] ?? '') === true) {
                        return $response;
                    }

                    // Fix proxy
                    if (empty($response['proxy'])) {
                        $response['is_proxy'] = (CFGP_IP::is_proxy() ? 1 : 0);
                    }

                    // Is localhost
                    $response['is_local_server'] = ($response['is_local_server'] ? 1 : 0);

                    // Is is spam
                    $response['is_spam'] = ($response['is_spam'] ? 1 : 0);

                    // Is is tor
                    $response['is_tor'] = ($response['is_tor'] ? 1 : 0);

                    // Is is mobile
                    $response['is_mobile'] = ($response['is_mobile'] ? 1 : 0);

                    // Is is vat
                    $response['is_vat'] = ($response['is_vat'] ? 1 : 0);

                    // Is is EU
                    $response['is_eu'] = ($response['is_eu'] ? 1 : 0);

                    // Is is limited
                    $response['limited'] = ($response['limited'] ? 1 : 0);

                    // Escaping strings
                    foreach ($response as $key => $value) {
                        if (in_array($key, ['credit','error_message'], true)) {
                            $response[$key] = wp_kses_post($value ?? '');
                        } elseif (absint($value) == $value || floatval($value) == $value) {
                            $response[$key] = esc_attr($value);
                        } else {
                            $response[$key] = esc_html($value);
                        }
						
						if (is_numeric($value)) {
							$response[$key] = strpos((string)$value, '.') !== false ? floatval($value) : intval($value);
						}
                    }

                    // Reassign
                    $return = $response;

                    // Save lookup to session
                    if (is_numeric($return['available_lookup']) && $return['available_lookup'] <= CFGP_LIMIT) {
                        CFGP_DB_Cache::set('cfgp-api-available-lookup-' . $this->host, $return['available_lookup'], (DAY_IN_SECONDS * 2));
                    } elseif (
                        ($return['available_lookup'] == 'unlimited' || $return['available_lookup'] == 'lifetime')
                        && CFGP_DB_Cache::get('cfgp-api-available-lookup-' . $this->host)
                    ) {
                        CFGP_DB_Cache::delete('cfgp-api-available-lookup-' . $this->host);
                    }

                    // Development info
                    if (CFGP_U::dev_mode()) {
                        $return['request_url'] = esc_url($request_url);
                    }

                    // Save to session
                    CFGP_DB_Cache::set("cfgp-api-{$ip_slug}", $return, (MINUTE_IN_SECONDS * CFGP_SESSION));

                    // Calculate runtime
                    if (empty($response['runtime'])) {
                        $runtime = (floatval(microtime()) - floatval(CFGP_START_RUNTIME));

                        if ($runtime < 0) {
                            $runtime = -$runtime;
                        }

                        $response['runtime'] = round($runtime, 6);
                    }
                }
            }

            // Append browser data after cache
            $return = array_merge($return, [
                'browser'         => CFGP_Browser::instance()->getBrowser(),
                'browser_version' => CFGP_Browser::instance()->getVersion(),
                'platform'        => CFGP_Browser::instance()->getPlatform(),
                'is_mobile'       => (CFGP_Browser::instance()->isMobile() ? 1 : 0),
            ]);

            // Return
            return apply_filters('cfgp/api/render/response', $return);
        }

        /**
         * Use the opt-in, unauthenticated Defender geolocation fallback.
         *
         * HTTP responses can be intercepted or modified. Sensitive request fields,
         * renewal headers, and renewal response data must never cross this path.
         *
         * @param string $request_url    Primary HTTPS geolocation endpoint.
         * @param array  $request_params Existing geolocation request parameters.
         * @return array|false
         */
        private function defender_emergency_http_request($request_url, $request_params)
        {
            if (
                !defined('CFGP_EMERGENCY_HTTP_FALLBACK')
                || CFGP_EMERGENCY_HTTP_FALLBACK !== true
                || (
                    !CFGP_Options::get('enable_defender', 0)
                    && !CFGP_Options::get('block_tor_network', 0)
                )
            ) {
                return false;
            }

            $scheme = strtolower((string)wp_parse_url($request_url, PHP_URL_SCHEME));
            $path   = (string)wp_parse_url($request_url, PHP_URL_PATH);

            // This exception is limited to the main geolocation route.
            if ($scheme !== 'https' || rtrim($path, '/') !== '/v3') {
                return false;
            }

            $safe_params = [];
            foreach ((array)$request_params as $name => $value) {
                if (preg_match('/(?:license|secret|token|password|credential|authorization|api[_-]?key|(?:^|[_-])(?:auth|key|nonce|cookie|pass|username|user_login|user_pass)(?:$|[_-]))/i', (string)$name)) {
                    continue;
                }

                $safe_params[$name] = $value;
            }

            $endpoint = 'http://159.203.150.139/v3/';
            $transport_params = [
                'redirection'        => 0,
                'reject_unsafe_urls' => true,
            ];
            $method = defined('CFGP_API_REQUEST_METHOD')
                ? strtoupper(trim((string)CFGP_API_REQUEST_METHOD))
                : 'GET';

            if ($method === 'POST') {
                $response = CFGP_U::curl_post(
                    $endpoint,
                    $safe_params,
                    [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    ],
                    $transport_params
                );
            } else {
                if (!empty($safe_params)) {
                    $endpoint .= '?' . http_build_query($safe_params, '', '&', PHP_QUERY_RFC3986);
                }

                $response = CFGP_U::curl_get($endpoint, [], $transport_params);
            }

            if (is_array($response)) {
                unset($response['license_renewal']);
            }

            return $response;
        }

        /*
         * Remove plugin cache
         */
        public static function remove_cache()
        {
            global $wpdb;

            if (is_multisite() && is_main_site() && is_main_network()) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required to clear plugin transients from multisite metadata rows.
                $wpdb->query(
                    "DELETE FROM `{$wpdb->sitemeta}`
                    WHERE `meta_key` LIKE '_transient_cfgp-api-%'
                       OR `meta_key` LIKE '_transient_timeout_cfgp-api-%'
                       OR `meta_key` LIKE '_site_transient_cfgp-api-%'
                       OR `meta_key` LIKE '_site_transient_timeout_cfgp-api-%'"
                ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Clears plugin transients from the multisite meta table.
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required to clear plugin transients from options rows.
                $wpdb->query(
                    "DELETE FROM `{$wpdb->options}`
                    WHERE `option_name` LIKE '_transient_cfgp-api-%'
                       OR `option_name` LIKE '_transient_timeout_cfgp-api-%'
                       OR `option_name` LIKE '_site_transient_cfgp-api-%'
                       OR `option_name` LIKE '_site_transient_timeout_cfgp-api-%'"
                ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Clears plugin transients from the options table.
            }

            // Clear the related cache.
            CFGP_Cache::delete('API');
        }

        /*
         * Instance
         * @verson    1.0.0
         */
        public static function instance($dry_run = false)
        {
            $instance = CFGP_Cache::get(self::class . ($dry_run ? '_Dry' : null));

            if (!$instance) {
                $instance = CFGP_Cache::set(
                    self::class . ($dry_run ? '_Dry' : null),
                    new self($dry_run)
                );
            }

            return $instance;
        }
    }
endif;
