<?php
/**
 * Geo Controller - WordPress Site Health diagnostics.
 *
 * Checks only reproducible or locally verifiable issues in Geo Controller 9.0.6.
 * Does not contact external services, change settings, or expose license keys.
 *
 * @package Geo_Controller
 * @since   9.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'CFGP_Site_Health', false ) ) {
    final class CFGP_Site_Health {

        /**
         * Register applicable Site Health tests with both failed and passed results.
         *
         * @param array $tests Registered Site Health tests.
         * @return array
         */
        public static function register_tests( $tests ) {
            if (
                ! defined( 'CFGP_VERSION' ) ||
                ! class_exists( 'CFGP_Options', false ) ||
                ! class_exists( 'CFGP_License', false )
            ) {
                return $tests;
            }

            $issues   = self::get_issues();
            $results  = array_merge( $issues, self::get_passed_tests( $issues ) );

            foreach ( $results as $id => $issue ) {
                $tests['direct'][ 'cfgp_' . $id ] = array(
                    'label' => $issue['label'],
                    'test'  => static function () use ( $id, $issue ) {
                        return CFGP_Site_Health::format_result( $id, $issue );
                    },
                );
            }

            return $tests;
        }

        /**
         * Convert a diagnostic result to the WordPress Site Health format.
         *
         * @param string $id    Unique test identifier.
         * @param array  $issue Result metadata.
         * @return array
         */
        public static function format_result( $id, $issue ) {
            $actions = array();

            foreach ( $issue['links'] as $link ) {
                $external = ! empty( $link['external'] );
                $actions[] = sprintf(
                    '<a href="%1$s"%2$s>%3$s</a>',
                    esc_url( $link['url'] ),
                    $external ? ' target="_blank" rel="noopener noreferrer"' : '',
                    esc_html( $link['label'] )
                );
            }

            return array(
                'label'       => $issue['label'],
                'status'      => $issue['status'],
                'badge'       => array(
                    'label' => __( 'Geo Controller', 'cf-geoplugin' ),
                    'color' => 'blue',
                ),
                'description' => '<p>' . esc_html( $issue['description'] ) . '</p>',
                'actions'     => empty( $actions ) ? '' : '<p>' . implode( ' | ', $actions ) . '</p>',
                'test'        => 'cfgp_' . $id,
            );
        }

        /**
         * Collect diagnostics from existing plugin settings and local database.
         * No network requests are made here.
         *
         * @return array
         */
        private static function get_issues() {
            static $issues = null;

            if ( null !== $issues ) {
                return $issues;
            }

            $issues       = array();
            $settings_url = self::admin_page( 'cf-geoplugin-settings' );
            $license_url  = self::admin_page( 'cf-geoplugin-activate' );
            $debug_url    = self::admin_page( 'cf-geoplugin-debug' );
            $help_url     = 'https://infinitumform.com/';

            // 1. License: an unlicensed/free installation is NOT a problem.
            $license = CFGP_License::get();

            if ( is_array( $license ) ) {
                $has_key = ! empty( $license['key'] );
                $has_id  = ! empty( $license['id'] );
                $has_sku = ! empty( $license['sku'] );

                if ( ( $has_key || $has_id || $has_sku ) && ! CFGP_License::has_activation() ) {
                    $issues['license_incomplete'] = array(
                        'label'       => __( 'Geo Controller has incomplete license activation data', 'cf-geoplugin' ),
                        'status'      => 'recommended',
                        'description' => __( 'A license key, activation ID or product code is saved, but the activation record is incomplete. Review the existing license and reconnect it if necessary. Free installations without an activation are not affected.', 'cf-geoplugin' ),
                        'links'       => array( self::link( $license_url, __( 'Review license', 'cf-geoplugin' ) ) ),
                    );
                } elseif ( CFGP_License::has_activation() ) {
                    $is_lifetime = isset( $license['sku'] ) && 'LIFETIME' === $license['sku'];
                    $expires     = isset( $license['expire'] ) && is_numeric( $license['expire'] )
                        ? (int) $license['expire']
                        : 0;

                    if ( CFGP_License::requires_renewal() ) {
                        $issues['license_renewal'] = array(
                            'label'       => __( 'Geo Controller license renewal is required', 'cf-geoplugin' ),
                            'status'      => 'recommended',
                            'description' => __( 'The saved Geo Controller activation is expired, revoked or otherwise marked for renewal. The plugin can fall back to standard lookup limits. Review or renew the existing activation to restore licensed features.', 'cf-geoplugin' ),
                            'links'       => array( self::link( $license_url, __( 'Review license status', 'cf-geoplugin' ) ) ),
                        );
                    } elseif ( ! $is_lifetime && $expires > time() && $expires <= time() + 14 * DAY_IN_SECONDS ) {
                        $issues['license_expiring'] = array(
                            'label'       => __( 'Geo Controller license expires within 14 days', 'cf-geoplugin' ),
                            'status'      => 'recommended',
                            'description' => sprintf(
                                /* translators: %s: Localized license expiration date. */
                                __( 'The current Geo Controller license is due to expire on %s. Review its renewal status to avoid a change to standard lookup limits.', 'cf-geoplugin' ),
                                wp_date( get_option( 'date_format' ), $expires )
                            ),
                            'links'       => array( self::link( $license_url, __( 'Review license', 'cf-geoplugin' ) ) ),
                        );
                    }
                }
            }

            // The API exposes explicit failure statuses already shown by Geo Controller.
            // Do not make another request just to generate a Site Health warning.
            $api_status = class_exists( 'CFGP_U', false ) ? (int) CFGP_U::api( 'status', 0 ) : 0;

            if ( 505 === $api_status ) {
                $issues['api_version_unsupported'] = array(
                    'label'       => __( 'Geo Controller API does not support this plugin version', 'cf-geoplugin' ),
                    'status'      => 'critical',
                    'description' => __( 'The Geo Controller API returned status 505, which the plugin identifies as an unsupported plugin version. Update Geo Controller to a supported release and verify the API response again.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( admin_url( 'update-core.php' ), __( 'Check WordPress updates', 'cf-geoplugin' ) ),
                        self::link( $debug_url, __( 'Review Geo Controller diagnostics', 'cf-geoplugin' ) ),
                    ),
                );
            } elseif ( 403 === $api_status ) {
                $issues['api_domain_blocked'] = array(
                    'label'       => __( 'Geo Controller API has rejected this domain', 'cf-geoplugin' ),
                    'status'      => 'critical',
                    'description' => __( 'The Geo Controller API returned status 403, shown by the plugin as a blocked domain. Geolocation requests may not work until the service restriction is resolved. Review the API response and contact Geo Controller support.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( $debug_url, __( 'Review Geo Controller diagnostics', 'cf-geoplugin' ) ),
                        self::link( 'https://wpgeocontroller.com/', __( 'Geo Controller support website', 'cf-geoplugin' ), true ),
                    ),
                );
            }

            // 2. Database: compare the real schema version and required tables.
            $stored_version = get_option( 'cf-geoplugin-db-version', '' );
            $expected       = defined( 'CFGP_DATABASE_VERSION' ) ? CFGP_DATABASE_VERSION : '';

            if ( $expected && (string) $stored_version !== (string) $expected ) {
                $issues['database_version'] = array(
                    'label'       => __( 'Geo Controller database update is pending', 'cf-geoplugin' ),
                    'status'      => 'recommended',
                    'description' => __( 'The stored Geo Controller database version does not match the installed plugin version. Back up the database and run the database update using the existing plugin admin notice.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( self::admin_page( 'cf-geoplugin' ), __( 'Open Geo Controller', 'cf-geoplugin' ) ),
                        self::link( $help_url, __( 'Professional database assistance (optional)', 'cf-geoplugin' ), true ),
                    ),
                );
            } else {
                $missing = self::missing_tables();

                if ( ! empty( $missing ) ) {
                    $issues['database_tables'] = array(
                        'label'       => __( 'Geo Controller database tables are missing', 'cf-geoplugin' ),
                        'status'      => 'critical',
                        'description' => sprintf(
                            /* translators: %s: Comma-separated list of missing database tables. */
                            __( 'The database version is current, but the following Geo Controller tables could not be found: %s. The plugin cache, API tokens or redirection rules may not work correctly. Check database permissions and restore the missing tables from a backup or reinstall the plugin schema.', 'cf-geoplugin' ),
                            implode( ', ', $missing )
                        ),
                        'links'       => array(
                            self::link( $debug_url, __( 'Geo Controller diagnostics', 'cf-geoplugin' ) ),
                            self::link( $help_url, __( 'Professional WordPress assistance (optional)', 'cf-geoplugin' ), true ),
                        ),
                    );
                }
            }

            // 3. WooCommerce: check actual integration state, not generic caching.
            $woocommerce_enabled = (bool) CFGP_Options::get( 'enable-woocommerce', 0 );
            $woocommerce_active  = function_exists( 'WC' );
            $woo_geo_selected    = 'cf_geoplugin' === get_option( 'woocommerce_default_customer_address', '' );

            if ( $woocommerce_enabled && ! $woocommerce_active ) {
                $issues['woocommerce_missing'] = array(
                    'label'       => __( 'Geo Controller WooCommerce integration is enabled without WooCommerce', 'cf-geoplugin' ),
                    'status'      => 'recommended',
                    'description' => __( 'Geo Controller has WooCommerce integration enabled, but WooCommerce is not currently active. Restore WooCommerce or disable the unused integration.', 'cf-geoplugin' ),
                    'links'       => array( self::link( $settings_url . '#plugin_support', __( 'Review integration settings', 'cf-geoplugin' ) ) ),
                );
            } elseif ( $woocommerce_active && $woo_geo_selected && ! $woocommerce_enabled ) {
                $issues['woocommerce_geolocation'] = array(
                    'label'       => __( 'WooCommerce is configured to use an inactive Geo Controller integration', 'cf-geoplugin' ),
                    'status'      => 'critical',
                    'description' => __( 'WooCommerce Default customer location is set to "Geolocate (by Geo Controller)", but the Geo Controller WooCommerce integration is disabled. Enable the integration or select another customer location mode in WooCommerce settings.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( $settings_url . '#plugin_support', __( 'Enable Geo Controller integration', 'cf-geoplugin' ) ),
                        self::link( admin_url( 'admin.php?page=wc-settings&tab=general' ), __( 'WooCommerce general settings', 'cf-geoplugin' ) ),
                    ),
                );
            }

            if (
                $woocommerce_active &&
                ! $woocommerce_enabled &&
                in_array( get_option( 'woocommerce_cf_geoplugin_conversion', 'original' ), array( 'converted', 'both', 'inversion' ), true )
            ) {
                $issues['woocommerce_currency_inactive'] = array(
                    'label'       => __( 'WooCommerce currency conversion is configured but Geo Controller integration is off', 'cf-geoplugin' ),
                    'status'      => 'recommended',
                    'description' => __( 'Geo Controller currency display is configured in WooCommerce, but its integration is disabled in Geo Controller. The currency display settings may not take effect. Enable the integration or restore original currency display.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( $settings_url . '#plugin_support', __( 'Review Geo Controller integration', 'cf-geoplugin' ) ),
                        self::link( admin_url( 'admin.php?page=wc-settings&tab=general' ), __( 'WooCommerce currency settings', 'cf-geoplugin' ) ),
                    ),
                );
            }

            // Payment restrictions are a licensed feature in the actual WooCommerce hook.
            // A saved rule is not necessarily being applied: the hook requires license level 2.
            if (
                $woocommerce_active &&
                $woocommerce_enabled &&
                self::woocommerce_has_payment_restrictions() &&
                CFGP_License::level() < 2 &&
                ! CFGP_U::dev_mode()
            ) {
                $issues['woocommerce_payment_entitlement'] = array(
                    'label'       => __( 'Geo Controller WooCommerce payment rules need an eligible license', 'cf-geoplugin' ),
                    'status'      => 'critical',
                    'description' => __( 'Country-based payment gateway restrictions are configured, but Geo Controller enables their WooCommerce enforcement only with a license of the required level. The saved restrictions may therefore not be applied at checkout. Review the license and verify the available payment methods in a real checkout test.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( $license_url, __( 'Review license eligibility', 'cf-geoplugin' ) ),
                        self::link( admin_url( 'admin.php?page=wc-settings&tab=cf_geoplugin_payment_restriction' ), __( 'Review payment restrictions', 'cf-geoplugin' ) ),
                    ),
                );
            }

            // 4. WooCommerce lookup quota: report a real dependency, not a sales reminder.
            // An enabled integration alone is not evidence that any geo feature is used.
            if ( $woocommerce_active && $woocommerce_enabled ) {
                $geo_features = array();

                if ( $woo_geo_selected ) {
                    $geo_features[] = __( 'customer geolocation and tax location', 'cf-geoplugin' );
                }

                $currency_mode = get_option( 'woocommerce_cf_geoplugin_conversion', 'original' );

                if ( in_array( $currency_mode, array( 'converted', 'both', 'inversion' ), true ) ) {
                    $geo_features[] = __( 'location-based currency display', 'cf-geoplugin' );
                }

                if ( 'yes' === get_option( 'woocommerce_cf_geoplugin_save_checkout_location', 'no' ) ) {
                    $geo_features[] = __( 'checkout geolocation logging', 'cf-geoplugin' );
                }

                if ( self::woocommerce_has_payment_restrictions() ) {
                    $geo_features[] = __( 'country-dependent payment restrictions', 'cf-geoplugin' );
                }

                if ( ! empty( $geo_features ) ) {
                    // Reading the existing API result does not initiate a new lookup.
                    // A stored license alone cannot prove that the API grants unlimited access.
                    $quota          = class_exists( 'CFGP_U', false ) ? CFGP_U::api( 'available_lookup', null ) : null;
                    $api_status     = class_exists( 'CFGP_U', false ) ? CFGP_U::api( 'status', null ) : null;
                    $quota_numeric  = in_array( (int) $api_status, array( 200, 402 ), true ) && is_numeric( $quota );
                    $quota_limited  = $quota_numeric && (int) $quota >= 0 && (int) $quota <= (int) CFGP_LIMIT;
                    $unlimited_api  = in_array( $quota, array( 'unlimited', 'lifetime' ), true );
                    $unlimited_key  = CFGP_License::activated();
                    $development    = class_exists( 'CFGP_U', false ) && CFGP_U::dev_mode();
                    $features_text  = implode( ', ', $geo_features );
                    $quota_details  = '';

                    if ( $quota_limited ) {
                        $quota_details = sprintf(
                            /* translators: 1: remaining requests, 2: total daily requests. */
                            __( 'The API currently reports %1$d of %2$d daily lookups remaining.', 'cf-geoplugin' ),
                            max( 0, (int) $quota ),
                            absint( CFGP_LIMIT )
                        );
                    }

                    // Zero remaining requests is a confirmed interruption when the API
                    // responded with a recognized quota status, regardless of local license data.
                    if ( $quota_limited && 0 === (int) $quota ) {
                        $issues['woocommerce_lookup_exhausted'] = array(
                            'label'       => __( 'Geo Controller lookup limit reached for WooCommerce', 'cf-geoplugin' ),
                            'status'      => 'critical',
                            'description' => sprintf(
                                /* translators: 1: configured WooCommerce features, 2: daily limit. */
                                __( 'WooCommerce uses Geo Controller for %1$s, and the API reports no lookups remaining from the daily allowance of %2$d. Until the quota resets or unlimited access is restored, new location-dependent requests may fail. Check the API status and restore adequate lookup capacity.', 'cf-geoplugin' ),
                                $features_text,
                                absint( CFGP_LIMIT )
                            ),
                            'links'       => array(
                                self::link( $debug_url, __( 'Review API diagnostics', 'cf-geoplugin' ) ),
                                self::link( $license_url, __( 'Review license', 'cf-geoplugin' ) ),
                                self::link( 'https://wpgeocontroller.com/documentation/quick-start/frequently-asked-questions/how-does-lookup-work', __( 'Understand lookup limits', 'cf-geoplugin' ), true ),
                            ),
                        );
                    } elseif ( $quota_limited && (int) $quota <= max( 1, (int) floor( CFGP_LIMIT / 5 ) ) ) {
                        $issues['woocommerce_lookup_low'] = array(
                            'label'       => __( 'Geo Controller lookup capacity is running low for WooCommerce', 'cf-geoplugin' ),
                            'status'      => 'recommended',
                            'description' => sprintf(
                                /* translators: 1: configured WooCommerce features, 2: remaining lookup information. */
                                __( 'WooCommerce depends on Geo Controller for %1$s. %2$s Once the available lookups reach zero, new visitor geolocation requests can stop working until the quota resets. Check license entitlements or reduce dependence on the limited API.', 'cf-geoplugin' ),
                                $features_text,
                                $quota_details
                            ),
                            'links'       => array(
                                self::link( $license_url, __( 'Check license and quota', 'cf-geoplugin' ) ),
                                self::link( $debug_url, __( 'View API diagnostics', 'cf-geoplugin' ) ),
                            ),
                        );
                    } elseif ( $quota_limited && $unlimited_key ) {
                        $issues['woocommerce_entitlement_mismatch'] = array(
                            'label'       => __( 'Geo Controller license and API lookup allowance do not agree', 'cf-geoplugin' ),
                            'status'      => 'recommended',
                            'description' => sprintf(
                                /* translators: 1: configured WooCommerce features, 2: quota details. */
                                __( 'WooCommerce uses Geo Controller for %1$s. Although a license appears active locally, the API still reports a finite lookup allowance. %2$s Verify the license on the Geo Controller server before relying on uninterrupted geolocation.', 'cf-geoplugin' ),
                                $features_text,
                                $quota_details
                            ),
                            'links'       => array(
                                self::link( $license_url, __( 'Verify license activation', 'cf-geoplugin' ) ),
                                self::link( $debug_url, __( 'Review API diagnostics', 'cf-geoplugin' ) ),
                            ),
                        );
                    } elseif ( ! $unlimited_api && ! $unlimited_key && ! $development ) {
                        $issues['woocommerce_lookup_risk'] = array(
                            'label'       => __( 'WooCommerce geolocation depends on a limited Geo Controller license', 'cf-geoplugin' ),
                            'status'      => 'recommended',
                            'description' => sprintf(
                                /* translators: 1: configured WooCommerce features, 2: daily lookup allowance. */
                                __( 'WooCommerce uses Geo Controller for %1$s, but no active unlimited entitlement was detected. The free API provides up to %2$d IP lookups per domain per day. When the limit is reached, new geolocation results may be unavailable and WooCommerce location-dependent behavior may become unreliable. Review the entitlement or disable the dependent features.', 'cf-geoplugin' ),
                                $features_text,
                                absint( CFGP_LIMIT )
                            ),
                            'links'       => array(
                                self::link( $license_url, __( 'Activate or review license', 'cf-geoplugin' ) ),
                                self::link( 'https://wpgeocontroller.com/documentation/quick-start/frequently-asked-questions/how-does-lookup-work', __( 'How the 500 lookup limit works', 'cf-geoplugin' ), true ),
                            ),
                        );
                    }
                }
            }

            // 5. Google Maps: account for the plugin's documented legacy key.
            if ( CFGP_Options::get( 'enable_gmap', 0 ) ) {
                $map_key = trim( (string) CFGP_Options::get( 'map_api_key', '' ) );

                if ( '' === $map_key ) {
                    $legacy = defined( 'CFGP_NETWORK_ADMIN' ) && CFGP_NETWORK_ADMIN
                        ? get_site_option( 'cf_geoplugin', array() )
                        : get_option( 'cf_geoplugin', array() );

                    if ( is_array( $legacy ) && ! empty( $legacy['map_api_key'] ) ) {
                        $map_key = trim( (string) $legacy['map_api_key'] );
                    }
                }

                if ( '' === $map_key ) {
                    $issues['maps_key'] = array(
                        'label'       => __( 'Geo Controller Google Maps API key is missing', 'cf-geoplugin' ),
                        'status'      => 'recommended',
                        'description' => __( 'Google Maps support is enabled, but neither the current nor legacy plugin configuration contains a Google Maps JavaScript API key. Supply a valid key or disable Google Maps when it is not used.', 'cf-geoplugin' ),
                        'links'       => array( self::link( $settings_url, __( 'Configure Google Maps', 'cf-geoplugin' ) ) ),
                    );
                }
            }

            // 6. Experimental Redis mode requires an available persistent backend.
            $options = CFGP_Options::get();

            if (
                is_array( $options ) &&
                ! empty( $options['enable_beta'] ) &&
                ! empty( $options['enable_redis_cache'] )
            ) {
                if ( ! extension_loaded( 'redis' ) ) {
                    $issues['redis_extension'] = array(
                        'label'       => __( 'Geo Controller Redis mode is enabled without the Redis PHP extension', 'cf-geoplugin' ),
                        'status'      => 'recommended',
                        'description' => __( 'Experimental Redis mode is selected, but the Redis PHP extension is not loaded. Geo Controller will use its fallback cache backend. Install the extension or disable Redis mode.', 'cf-geoplugin' ),
                        'links'       => array(
                            self::link( $settings_url, __( 'Review cache settings', 'cf-geoplugin' ) ),
                            self::link( $help_url, __( 'Professional server assistance (optional)', 'cf-geoplugin' ), true ),
                        ),
                    );
                } elseif ( ! wp_using_ext_object_cache() ) {
                    $issues['redis_persistent_cache'] = array(
                        'label'       => __( 'Geo Controller Redis mode has no persistent WordPress object cache', 'cf-geoplugin' ),
                        'status'      => 'recommended',
                        'description' => __( 'Experimental Redis mode is enabled, but WordPress does not report an external object-cache drop-in. Cache values stored through wp_cache_set() may exist only for the current request. Configure a persistent object cache or disable Redis mode.', 'cf-geoplugin' ),
                        'links'       => array(
                            self::link( $settings_url, __( 'Review cache settings', 'cf-geoplugin' ) ),
                            self::link( $help_url, __( 'Professional cache configuration (optional)', 'cf-geoplugin' ), true ),
                        ),
                    );
                }
            }

            // 7. The optional emergency HTTP fallback really is unencrypted.
            if (
                defined( 'CFGP_EMERGENCY_HTTP_FALLBACK' ) &&
                true === CFGP_EMERGENCY_HTTP_FALLBACK &&
                ( CFGP_Options::get( 'enable_defender', 0 ) || CFGP_Options::get( 'block_tor_network', 0 ) )
            ) {
                $issues['insecure_fallback'] = array(
                    'label'       => __( 'Geo Controller emergency unencrypted HTTP fallback is enabled', 'cf-geoplugin' ),
                    'status'      => 'recommended',
                    'description' => __( 'The emergency Defender geolocation fallback may send a limited geolocation request over unencrypted HTTP after HTTPS endpoints fail. Disable CFGP_EMERGENCY_HTTP_FALLBACK unless this exceptional risk is explicitly required.', 'cf-geoplugin' ),
                    'links'       => array(
                        self::link( $debug_url, __( 'Review connection diagnostics', 'cf-geoplugin' ) ),
                        self::link( $help_url, __( 'Professional network assistance (optional)', 'cf-geoplugin' ), true ),
                    ),
                );
            }

            // 8. Nonpositive HTTP timeouts prevent reasonable API operation.
            $timeout = CFGP_Options::get( 'timeout', 5 );

            if ( is_numeric( $timeout ) && (float) $timeout <= 0 ) {
                $issues['http_timeout'] = array(
                    'label'       => __( 'Geo Controller HTTP API timeout is invalid', 'cf-geoplugin' ),
                    'status'      => 'recommended',
                    'description' => __( 'The configured Geo Controller HTTP API timeout is zero or negative. Use a positive timeout (the settings screen supports 5 to 300 seconds) so remote geolocation requests can complete.', 'cf-geoplugin' ),
                    'links'       => array( self::link( $settings_url, __( 'Correct HTTP timeout', 'cf-geoplugin' ) ) ),
                );
            }

            return $issues;
        }

        /**
         * Describe genuinely successful checks for the WordPress "Passed tests" list.
         *
         * Disabled optional integrations, unknown API responses, and missing data
         * are not treated as evidence of success. Each pass is limited to the
         * exact configuration property that can be verified locally.
         *
         * @param array $issues Problems detected by the existing diagnostics.
         * @return array
         */
        private static function get_passed_tests( $issues ) {
            $passed    = array();
            $issue_ids = array_keys( $issues );

            // A healthy local activation does not by itself prove unlimited API access.
            if (
                CFGP_License::has_activation() &&
                CFGP_License::activated() &&
                ! array_intersect( $issue_ids, array( 'license_incomplete', 'license_renewal', 'license_expiring' ) )
            ) {
                $passed['license_healthy'] = array(
                    'label'       => __( 'Geo Controller license activation has no detected problems', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'A locally active license was detected, with no incomplete activation, renewal requirement or expiration within the next 14 days. API lookup entitlement is assessed separately.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            // The API response is already cached by Geo Controller: no remote request.
            if ( class_exists( 'CFGP_U', false ) && 200 === (int) CFGP_U::api( 'status', 0 ) ) {
                $passed['api_response_healthy'] = array(
                    'label'       => __( 'Geo Controller API reports a successful response', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'The existing Geo Controller API result has status 200. This check does not make another geolocation request.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            // A current schema version and installed tables are independent checks.
            $expected = defined( 'CFGP_DATABASE_VERSION' ) ? (string) CFGP_DATABASE_VERSION : '';

            if (
                '' !== $expected &&
                $expected === (string) get_option( 'cf-geoplugin-db-version', '' ) &&
                ! isset( $issues['database_version'] )
            ) {
                $passed['database_version_healthy'] = array(
                    'label'       => __( 'Geo Controller database version is current', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'The stored Geo Controller database version matches the version required by the installed plugin.', 'cf-geoplugin' ),
                    'links'       => array(),
                );

                // The existing issue collector already checked all required tables.
                if ( ! isset( $issues['database_tables'] ) ) {
                    $passed['database_tables_healthy'] = array(
                        'label'       => __( 'Geo Controller required database tables exist', 'cf-geoplugin' ),
                        'status'      => 'good',
                        'description' => __( 'All three required Geo Controller database tables were found during the Site Health schema check.', 'cf-geoplugin' ),
                        'links'       => array(),
                    );
                }
            }

            $woocommerce_active  = function_exists( 'WC' );
            $woocommerce_enabled = (bool) CFGP_Options::get( 'enable-woocommerce', 0 );

            // Do not report WooCommerce integration as passed when it is disabled.
            if ( $woocommerce_active && $woocommerce_enabled ) {
                $passed['woocommerce_integration_healthy'] = array(
                    'label'       => __( 'Geo Controller WooCommerce integration is enabled', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'WooCommerce is active and the Geo Controller integration is enabled. Individual location-based features, payment rules and lookup allowance are evaluated separately.', 'cf-geoplugin' ),
                    'links'       => array(),
                );

                $payment_rules = self::woocommerce_has_payment_restrictions();

                if (
                    $payment_rules &&
                    ( CFGP_License::level() >= 2 || ( class_exists( 'CFGP_U', false ) && CFGP_U::dev_mode() ) ) &&
                    ! isset( $issues['woocommerce_payment_entitlement'] )
                ) {
                    $passed['woocommerce_payment_healthy'] = array(
                        'label'       => __( 'Geo Controller payment rules meet the local license requirement', 'cf-geoplugin' ),
                        'status'      => 'good',
                        'description' => __( 'Country-based payment rules are configured and the current local entitlement meets the WooCommerce enforcement requirement. Checkout behavior has not been tested remotely.', 'cf-geoplugin' ),
                        'links'       => array(),
                    );
                }

                $currency_mode = get_option( 'woocommerce_cf_geoplugin_conversion', 'original' );
                $geo_features_active =
                    'cf_geoplugin' === get_option( 'woocommerce_default_customer_address', '' ) ||
                    in_array( $currency_mode, array( 'converted', 'both', 'inversion' ), true ) ||
                    'yes' === get_option( 'woocommerce_cf_geoplugin_save_checkout_location', 'no' ) ||
                    $payment_rules;

                // Only a confirmed server allowance can demonstrate unlimited lookup capacity.
                if ( $geo_features_active && class_exists( 'CFGP_U', false ) ) {
                    $quota  = CFGP_U::api( 'available_lookup', null );
                    $status = (int) CFGP_U::api( 'status', 0 );

                    if (
                        200 === $status &&
                        in_array( $quota, array( 'unlimited', 'lifetime' ), true ) &&
                        ! array_intersect(
                            $issue_ids,
                            array(
                                'woocommerce_lookup_exhausted',
                                'woocommerce_lookup_low',
                                'woocommerce_entitlement_mismatch',
                                'woocommerce_lookup_risk',
                            )
                        )
                    ) {
                        $passed['woocommerce_lookup_healthy'] = array(
                            'label'       => __( 'WooCommerce geolocation has unlimited API lookup access', 'cf-geoplugin' ),
                            'status'      => 'good',
                            'description' => __( 'WooCommerce uses location-dependent Geo Controller features and the existing API response explicitly reports unlimited or lifetime lookup access.', 'cf-geoplugin' ),
                            'links'       => array(),
                        );
                    }
                }
            }

            if ( CFGP_Options::get( 'enable_gmap', 0 ) && ! isset( $issues['maps_key'] ) ) {
                $passed['maps_key_healthy'] = array(
                    'label'       => __( 'Geo Controller Google Maps API key is configured', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'Google Maps is enabled and a current or legacy API key is present. The key has not been validated against Google Maps services.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            $options = CFGP_Options::get();

            if (
                is_array( $options ) &&
                ! empty( $options['enable_beta'] ) &&
                ! empty( $options['enable_redis_cache'] ) &&
                extension_loaded( 'redis' ) &&
                wp_using_ext_object_cache() &&
                ! array_intersect( $issue_ids, array( 'redis_extension', 'redis_persistent_cache' ) )
            ) {
                $passed['redis_healthy'] = array(
                    'label'       => __( 'Geo Controller Redis prerequisites are present', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'Experimental Redis caching is selected, the Redis PHP extension is loaded and WordPress reports an external object cache. Redis connectivity is not verified by this check.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            if (
                ( CFGP_Options::get( 'enable_defender', 0 ) || CFGP_Options::get( 'block_tor_network', 0 ) ) &&
                ! ( defined( 'CFGP_EMERGENCY_HTTP_FALLBACK' ) && true === CFGP_EMERGENCY_HTTP_FALLBACK )
            ) {
                $passed['http_fallback_healthy'] = array(
                    'label'       => __( 'Geo Controller insecure emergency fallback is disabled', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'The optional unencrypted emergency HTTP fallback is not enabled for Geo Controller Defender or Tor-related checks.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            $timeout = CFGP_Options::get( 'timeout', 5 );

            if ( is_numeric( $timeout ) && (float) $timeout > 0 ) {
                $passed['http_timeout_healthy'] = array(
                    'label'       => __( 'Geo Controller HTTP API timeout is positive', 'cf-geoplugin' ),
                    'status'      => 'good',
                    'description' => __( 'The configured API timeout is a positive numeric value, allowing remote requests time to complete.', 'cf-geoplugin' ),
                    'links'       => array(),
                );
            }

            return $passed;
        }

        /**
         * Detect WooCommerce gateways that have explicit Geo Controller rules.
         * Never initialize gateways or alter checkout settings in a health check.
         *
         * @return bool
         */
        private static function woocommerce_has_payment_restrictions() {
            static $configured = null;
            global $wpdb;

            if ( null !== $configured ) {
                return $configured;
            }

            $configured = false;

            if ( ! isset( $wpdb->options ) ) {
                return false;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Inspect the saved rule modes without initializing WooCommerce payment gateways.
            $rule_options = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value IN (%s, %s)",
                    $wpdb->esc_like( 'woocommerce_cfgp_method_' ) . '%',
                    'cfgp_payment_enable',
                    'cfgp_payment_disable'
                )
            );

            foreach ( (array) $rule_options as $option_name ) {
                // The WooCommerce integration explicitly ignores rules without countries.
                $countries = get_option( $option_name . '_select', array() );

                if ( is_array( $countries ) && ! empty( array_filter( $countries, 'is_scalar' ) ) ) {
                    $configured = true;
                    break;
                }
            }

            return $configured;
        }

        /**
         * Check the three tables created by the plugin's schema installer.
         *
         * @return string[]
         */
        private static function missing_tables() {
            global $wpdb;

            $names = array(
                $wpdb->get_blog_prefix() . 'cfgp_cache',
                $wpdb->get_blog_prefix() . 'cfgp_rest_access_token',
                $wpdb->get_blog_prefix() . 'cfgp_seo_redirection',
            );
            $missing = array();

            foreach ( $names as $name ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema verification is a Site Health diagnostic only.
                $found = $wpdb->get_var(
                    $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $name ) )
                );

                if ( $found !== $name ) {
                    $missing[] = $name;
                }
            }

            return $missing;
        }

        /**
         * Build a site-level settings link without switching network contexts.
         *
         * @param string $page Plugin admin page slug.
         * @return string
         */
        private static function admin_page( $page ) {
            return admin_url( 'admin.php?page=' . rawurlencode( $page ) );
        }

        /**
         * Build safely rendered action link metadata.
         *
         * @param string $url      Destination.
         * @param string $label    Link text.
         * @param bool   $external Whether this link leaves WordPress admin.
         * @return array
         */
        private static function link( $url, $label, $external = false ) {
            return array(
                'url'      => $url,
                'label'    => $label,
                'external' => $external,
            );
        }
    }
}
