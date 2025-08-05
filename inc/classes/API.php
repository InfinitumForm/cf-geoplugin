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
            if (empty($return)) {
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
                $request_url = CFGP_Defaults::API[(CFGP_Options::get('enable_ssl', 0) ? 'ssl_' : '') . 'main'] . '?' . http_build_query(
                    $request_pharams,
                    '',
                    (ini_get('arg_separator.output') ?? '&amp;'),
                    PHP_QUERY_RFC3986
                );
                // Fetch new informations
                $response = CFGP_U::curl_get($request_url);

                // Fallback to return by IP
                if (!$response) {
                    $response = CFGP_U::curl_get(CFGP_Defaults::falback_api_endpoints($request_url));
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

        /*
         * Remove plugin cache
         */
        public static function remove_cache()
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
