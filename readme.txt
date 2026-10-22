=== Geo Controller - Geolocation & Geotargeting ===
Contributors: ivijanstefan, creativform
Donate link: https://ko-fi.com/ivijanstefanstipic
Tags: geolocation, geotargeting, geoip, woocommerce, redirect
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 9.0.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Commercial: https://wpgeocontroller.com/

WordPress geolocation and geotargeting with IP lookup, geo redirects, dynamic content, WooCommerce localization and visitor blocking.

== Description ==

Geo Controller is a WordPress geolocation and geotargeting plugin that uses IP location data to personalize website content, redirect visitors by country or region, localize WooCommerce, and control geographic access. It combines location-based shortcodes, conditional content, geo redirects, country flags, Google Maps, Geo Defender, and developer APIs in one plugin.

Previously known as CF Geo Plugin, Geo Controller continues the development of the original WordPress geolocation plugin under its new name. Existing users can continue using supported shortcodes, integrations, and location-based functionality without switching to a different plugin.

= WordPress IP Geolocation =

Geo Controller resolves a visitor's IP address through the Geo Controller API and makes the available location data accessible in WordPress. Depending on the lookup response and enabled options, this can include country, region, city, postcode, coordinates, timezone, language, currency, ISP, hostname, proxy, TOR, and device information.

* IPv4 and IPv6 lookup support
* Trusted proxy and Cloudflare-aware IP detection
* Configurable SSL, proxy, cache, and API transport settings
* Default values when requested data is unavailable
* Optional DNS, ISP, currency, and device-related data

IP geolocation is approximate. VPNs, proxies, mobile networks, shared connections, CDN configuration, and database accuracy can affect results. Review [data sources and accuracy](https://wpgeocontroller.com/documentation/concepts/data-sources-and-accuracy) and [IP detection guidance](https://wpgeocontroller.com/documentation/concepts/ip-detection-proxies-vpn-cdns) before using location data for important decisions.

= Geotargeting and Dynamic Content =

Use WordPress shortcodes to display visitor data or conditionally show content by country, region, city, continent, postcode, EU status, VAT status, proxy, TOR, or device type. Geo Banner rules provide another way to target text, media, and other WordPress content.

Common examples:

    [cfgeo return="country"]
    [cfgeo return="city" default="Your Default Text"]
    [cfgeo include="US,GB,DE"]Content for selected countries.[/cfgeo]
    [in_eu]Content for EU visitors.[/in_eu]

Nested shortcodes remain available in conditional content where the surrounding WordPress content area processes shortcodes. Optional Simple Shortcodes, such as `[cfgeo_city]`, must be enabled in Geo Controller settings. See the [shortcode reference](https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes) for supported attributes and return values.

Location-specific HTML can be affected by full-page caching when one cached response is shared between visitors. Follow the [dynamic content and caching guidance](https://wpgeocontroller.com/documentation/concepts/dynamic-content-and-caching) and test every rule with a safe fallback.

= Common Geolocation Use Cases =

Geo Controller can be used to create location-aware WordPress experiences without building a separate geolocation system.

* Display different content, messages, banners, or affiliate links based on visitor location.
* Redirect visitors to country-specific pages, regional websites, or localized content.
* Show local contact information, phone numbers, addresses, and Google Maps.
* Customize WooCommerce payment options and currency information by visitor location.
* Restrict access from selected countries, IP addresses, proxies, or TOR networks.
* Provide location-based information to custom themes, plugins, forms, and integrations.

These features can be combined using shortcodes, conditional content, WordPress integrations, and developer APIs. Availability depends on configuration and, for selected features, the active license.

= Geo Redirects and Geographic SEO Redirection =

Create ordered geographic rules for pages, posts, custom post types, or site-wide destinations. Rules can evaluate supported country, region, city, and postcode values, with exclusions, bot handling, referrer controls, and CSV import/export tools.

Available response codes are 301, 302, 303, 307, 308, and 404. Choose the response that matches the intended behavior: permanent redirects should not be used while destinations are still being tested, and 404 returns a not-found response rather than redirecting the visitor.

Redirects do not guarantee search-engine results or legal compliance. Avoid redirect loops, keep destinations crawlable where appropriate, and test caching, canonical URLs, language settings, and bot behavior before production use.

= WooCommerce Geolocation, Currency and Payments =

The included WooCommerce integration can use Geo Controller location data to support:

* Customer geolocation and location data saved with orders
* Country-based payment gateway visibility
* Local currency information and currency conversion
* EU and VAT-aware content
* Classic Checkout, Checkout Blocks, Store API, and HPOS workflows

Some WooCommerce controls and service-backed data require an eligible active license. Availability is shown in the plugin settings. Currency results are informational and may not match the final rate used by a payment provider, bank, tax service, or accounting system.

= Geo Defender and Visitor Access Control =

Geo Defender can restrict access using configured geographic and IP rules, with support for TOR, proxy, and spam-related signals where available. Administrators can configure a response, allow trusted addresses, receive notifications, and use the existing recovery mechanism if a rule blocks administrative access.

Geolocation and reputation signals can be incomplete or incorrect. Geo Defender is an access-control aid, not a replacement for authentication, a firewall, malware protection, or professional security and compliance review. Test rules carefully before enabling them on a production site.

= Google Maps, Country Flags and Geographic Metadata =

Enable `[cfgeo_map]` to display a single-point Google Map for a detected or specified location. A valid Google Maps API key is required.

    [cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15" title="My Company Name"]
        <h3>My Company Name</h3>
        <p>No Name Street 35, New York, USA</p>
        <p>We have what you need</p>
    [/cfgeo_map]

Geo tags can add configured geographic metadata to selected public content. Country flags are available through `[cfgeo_flag]`, including custom country, size, class, ID, inline CSS, and include/exclude options.

Examples:

    [cfgeo_flag]
    [cfgeo_flag country="ca" size="32px"]

Read the [Google Maps shortcode documentation](https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes/cfgeo_map) before enabling map output. Google Maps is a separate external service with its own account, billing, terms, and privacy behavior.

= Integrations and Developer Features =

The plugin contains dedicated integration code for WooCommerce, Elementor, Contact Form 7, Gravity Forms, BuddyBoss Platform, WooPlatnica, and Yoast SEO. It also includes Cloudflare-aware IP handling and settings intended for common caching configurations. Compatibility still depends on the active theme, plugin versions, cache/CDN behavior, and site configuration.

Developers can use:

* `CFGP_U::api()` for the current structured geodata array
* `CFGP_U::api('country_code')` for a single available field
* `CFGP_API::lookup()` for an explicit advanced lookup
* `window.cfgeo` when the plugin's JavaScript output is enabled
* Native WordPress `do_shortcode()` in templates that intentionally render shortcode content
* The optional public REST lookup feature when the required license level is active

Always validate returned keys and provide fallbacks because not every lookup returns every field.

= Free and Licensed Features =

The plugin can be installed and used without a paid license. The standard Geo Controller API allowance is up to 500 lookups per day. An active paid license provides unlimited lookups during its license period, and some service-backed features are enabled according to license level.

License-dependent controls can include automatic spam-IP checks, selected WooCommerce payment controls, and the public REST lookup service. The admin interface indicates whether a setting requires a license. Current license plans, available features, and site limits are listed on the [pricing page](https://wpgeocontroller.com/pricing).

Geo Controller depends on an external API service for IP lookup data. Review the [Privacy Policy](https://wpgeocontroller.com/privacy-policy) and [privacy, consent, and compliance documentation](https://wpgeocontroller.com/documentation/concepts/privacy-consent-and-compliance) to decide how the service fits your site's disclosures and legal obligations.

== Installation ==

1. Install **Geo Controller** from the WordPress Plugins screen, or upload the plugin files to `/wp-content/plugins/cf-geoplugin/`.
2. Activate the plugin.
3. Open **Geo Controller** in WordPress administration and review the general, feature, cache, and proxy settings.
4. Test a basic lookup with `[cfgeo return="country" default="Unknown"]`.
5. Configure only the modules you need, then test location rules with caching enabled exactly as it will be in production.

When the plugin is uninstalled through WordPress, its plugin options, license data, transients, and plugin-owned database data are removed. Back up any configuration you may need before uninstalling.

== Frequently Asked Questions ==

= Is Geo Controller free? =

Yes. The plugin works without a paid license with a standard allowance of up to 500 API lookups per day. Active paid licenses provide unlimited lookups during the license period and enable features associated with the applicable license level. See the [current plans](https://wpgeocontroller.com/pricing).

= Is Geo Controller the same plugin as CF Geo Plugin? =

Yes. Geo Controller was previously known as CF Geo Plugin. It is the continuation of the same WordPress plugin, with ongoing development, improved functionality, and maintained compatibility with supported existing shortcodes and integrations. Existing users do not need to install a separate plugin or migrate to a different product.

= What information is sent to an external service? =

IP geolocation requires communication with the Geo Controller API. An IP address and technical request data are processed to return approximate location information. Optional Google Maps output communicates with Google when enabled. Review the [Privacy Policy](https://wpgeocontroller.com/privacy-policy) and document these services appropriately for your site.

= How accurate is IP geolocation? =

IP geolocation estimates network location; it does not verify a person's physical position. Country results are generally more reliable than city or postcode results. VPNs, proxies, mobile carriers, corporate gateways, satellite connections, and incorrect trusted-proxy settings can change the detected IP or location. Provide fallback content and avoid using an IP result as the sole basis for high-impact decisions.

= How can I test content for another country, region, or city? =

Use a controlled connection or reputable testing service that provides an exit point in the target location, then confirm the detected IP and fields on Geo Controller's Debug page. Clear relevant page and plugin caches between tests. Do not assume that one VPN endpoint represents every visitor in that location.

= Why does every visitor see the same location-based content? =

A full-page cache or CDN may be serving one visitor's rendered HTML to everyone. Exclude pages that require per-visitor output, use the plugin's supported dynamic/AJAX behavior where appropriate, and purge caches after changing rules. See [dynamic content and caching](https://wpgeocontroller.com/documentation/concepts/dynamic-content-and-caching).

= Why is a field empty or showing its default value? =

The provider may not have that field for the detected IP, the daily allowance may have been reached, the request may have failed, or proxy/CDN settings may identify the wrong address. Check the Debug page, the displayed API response and error, SSL/proxy settings, trusted forwarding headers, and server logs. Use a `default` shortcode value for expected gaps.

= Can I use shortcodes in a custom theme or template? =

Yes, when the template processes WordPress shortcodes. For example:

`echo do_shortcode('[cfgeo return="country" default="Unknown"]');`

Use [WordPress do_shortcode()](https://developer.wordpress.org/reference/functions/do_shortcode/) only where rendering administrator-controlled shortcode content is appropriate.

= How do I access geolocation data in PHP? =

Use the current helper and validate keys defensively:

    $data = CFGP_U::api();
    $country = is_array($data) ? ($data['country'] ?? '') : '';
    $country_code = CFGP_U::api('country_code');

    $response = CFGP_API::lookup('8.8.8.8', ['dns' => true]);
    $city = is_array($response) ? ($response['city'] ?? '') : '';

See the [PHP integration documentation](https://wpgeocontroller.com/documentation/advanced-usage/php-integration). Historic global variables from older CF Geo Plugin releases should not be used for new integrations.

= How do I access geolocation data in JavaScript? =

Enable the plugin's JavaScript output, then read the generated `window.cfgeo` object after it is available:

`if (window.cfgeo) {
    console.log(window.cfgeo.country);
}`

See the [JavaScript integration documentation](https://wpgeocontroller.com/documentation/advanced-usage/javascript-integration).

= Can I populate Contact Form 7 fields? =

Yes, if JavaScript output is enabled. A form can contain fields such as:

    [text* city placeholder "* City Name"]
    [text* country placeholder "* Country Name"]

Your site script can then assign `window.cfgeo.city` and `window.cfgeo.country` after checking that the object and fields exist. Escape or validate values for the target context.

= Does Geo Controller slow down a site? =

Any API call and location-dependent rendering has a cost. Geo Controller caches lookup data and supports dynamic loading options, but actual impact depends on hosting, network latency, cache configuration, enabled modules, and traffic. Measure the relevant pages and avoid unnecessary repeated lookups.

= Does Geo Controller make a site legally compliant? =

No plugin can guarantee compliance automatically. Geo Controller provides location-aware conditions that may support a site's implementation, but the site owner remains responsible for notices, consent, access rules, data processing, taxes, and applicable law. Obtain qualified advice where needed.

= Where can I get help? =

Start with the [documentation](https://wpgeocontroller.com/documentation). If the issue remains, use [Contact & Support](https://wpgeocontroller.com/contact-and-support) and include the plugin version, WordPress version, relevant settings, Debug output with secrets removed, and steps to reproduce.

== Screenshots ==

1. Geo Controller Available shortcodes and values
2. CF Google Map shortcodes and setup
3. Anti Spam Protection settings
4. Geo Controller global settings
5. Debug Mode for Geo Controller and IP Lookup
6. Adding shortcode in text editor
7. Admin bar quick links
8. Example 1
9. Example 2
10. Example 3
11. SEO Redirection settings
12. Contact Form 7 settings
13. Available tags
14. WooCommerce Settings

== Changelog ==

= 9.0.8 =
* Geo Controller Site Health updates.

= 9.0.7 =
* Fix crawler detection, improve Googlebot and Bingbot recognition, CIDR validation, and correct bot detection caching.

= 9.0.6 =
* Fixed a fatal error during WordPress initialization caused by an early user capability check. Deferred plugin integration notifications until WordPress user functions are available.

= 9.0.5 =
* Reworked the WordPress.org readme with clearer geolocation, geotargeting, WooCommerce, access-control, privacy, caching, and licensing guidance.
* Added accessible Copy controls for shortcode, geo tag, CSS property, and Google Maps syntax examples, with fallback and translated feedback.
* Refined the five primary admin pages with cleaner, compact tabs, tables, sections, descriptions, focus states, and responsive controls.
* Aligned Simple Shortcodes tab visibility with its standard feature setting.

= 9.0.4 =
* Fixed [in_eu] and [not_in_eu] shortcode registration after the Simple Shortcodes option moved from beta to standard settings.

= 9.0.3 =
* Security: moved dedicated Geo Controller API endpoints to HTTPS and removed the former automatic direct-IP HTTP downgrade.
* API: added centralized, backward-compatible GET/POST transport selection; GET remains the default, while POST sends API parameters in the request body.
* Added an optional Defender emergency direct-IP HTTP fallback that is disabled by default and excludes license keys, API secrets, authentication tokens, passwords, and renewal data.
* Security: validated forwarding headers against trusted proxy and Cloudflare ranges, bounded and rate-limited public location requests, and restricted integration actions to administrators.
* Security: safely JSON-encoded dynamic geolocation JavaScript property names and values so remote or cached data cannot escape the inline object.
* Compatibility: preserved geolocation, Defender, licensing, API response handling, product information, Live News, and WordPress update integrations.

= 9.0.2 =
* Fixed escaped shortcode examples and interactive map configuration output.
* Moved the Simple Shortcodes option to standard feature settings and kept Redis caching experimental.
* Compatibility: improved WooCommerce Classic Checkout, Checkout Blocks, Store API, and HPOS order handling.
* Fixed WooCommerce price conversion, geolocation, payment-country restrictions, and gateway settings output.

= 9.0.1 =
* Fixed license renewal synchronization for renewed subscription activations.
* Added automatic renewal revalidation for valid subscription activations.
* Improved expired-license renewal diagnostics and administrator guidance.

= 9.0.0 =
* Compatibility: maintained PHP 7.0 to PHP 8.4 support.
* Stabilized shortcode and Geo Banner caching across transient, database, and Redis modes.
* Fixed cache cleanup and prevented Geo Banner caching when disabled.
* Stabilized shortcode, GPS, Geo Banner, and Google Map attributes while preserving legacy support.
* Added optional REST API transport for public authentication and lookups; AJAX remains the default.
* Added API transport settings and endpoint documentation.
* Preserved existing API responses and access-token behavior.
* Security: protected Defender saves, previews, and bypass cookies with capability, nonce, and method checks.
* Security: limited permanent Defender bypass to administrators and removed legacy recovery and bot bypasses.
* Security: added single-use recovery links for temporary WordPress login access.
* Security: protected global and page-level SEO settings with capability, nonce, and method checks.
* Fixed postcode matching, country-city redirects, exclusion rules, empty CSV exports, and import rollbacks.
* CSS display controls now preserve flex, grid, inline, inline-block, table, and other display modes.

== Upgrade Notice ==

= 9.0.8 =
* Geo Controller Site Health updates.

= 9.0.7 =
* Fix crawler detection, improve Googlebot and Bingbot recognition, CIDR validation, and correct bot detection caching.

== Other Notes ==

= Plugin Links =

* [Geo Controller Website](https://wpgeocontroller.com/)
* [Documentation](https://wpgeocontroller.com/documentation)
* [Contact & Support](https://wpgeocontroller.com/contact-and-support)
* [Terms and Conditions](https://wpgeocontroller.com/terms-and-conditions)
* [Privacy Policy](https://wpgeocontroller.com/privacy-policy)

= External Services and Privacy =

Geo Controller sends IP lookup requests to the Geo Controller API. Optional Google Maps output uses Google Maps separately when configured. Site owners are responsible for evaluating these services, providing appropriate notices or consent, and meeting applicable legal requirements.

Review the [Privacy Policy](https://wpgeocontroller.com/privacy-policy), [Terms and Conditions](https://wpgeocontroller.com/terms-and-conditions), and [privacy documentation](https://wpgeocontroller.com/documentation/concepts/privacy-consent-and-compliance) before deployment.
