<?php
/**
 * Contextual Help
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       1.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Help', false)) : class CFGP_Help extends CFGP_Global
{
    public function __construct()
    {

        $page     = CFGP_U::request_string('page');
        $function = str_replace('-', '_', $page);

        if (method_exists($this, "help__{$function}")) {
            $this->add_action('current_screen', "help__{$function}", 10);
        }

        if (CFGP_U::request_string('post_type') === 'cf-geoplugin-banner') {
            $this->add_action('current_screen', 'help__cf_geoplugin_banner');
        }
    }

    private function add_help_tab($id, $title, $content)
    {
        $screen = get_current_screen();

        if ($screen) {
            $screen->add_help_tab([
                'id'      => $id,
                'title'   => esc_html($title),
                'content' => wp_kses_post($content),
            ]);
        }
    }

    private function documentation_link($url, $label)
    {
        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url($url),
            esc_html($label)
        );
    }

    /*
     * Geo Controller help
     */
    public function help__cf_geoplugin()
    {
        $this->add_help_tab(
            'cfgp-plugin-overview',
            __('Overview', 'cf-geoplugin'),
            '<h3>' . __('Using geographic data', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('This page lists the shortcodes, geographic properties, Geo Tags and CSS classes available for location-aware content. Location is inferred from the visitor IP address and may be approximate.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Use the displayed Copy buttons to copy the exact syntax. Test location-dependent output with caching, proxy and CDN configuration representative of the live website.', 'cf-geoplugin') . '</p>'
        );

        $this->add_help_tab(
            'cfgp-plugin-shortcodes',
            __('Shortcodes', 'cf-geoplugin'),
            '<h3>' . __('Regular and Simple Shortcodes', 'cf-geoplugin') . '</h3>' .
            '<p>' . sprintf(
                /* translators: 1: cfgeo shortcode, 2: simple property shortcode pattern. */
                __('Use %1$s for conditional content and formatted geographic output. Use %2$s for direct property output when Simple Shortcodes are enabled in Geo Controller settings.', 'cf-geoplugin'),
                '<code>[cfgeo]</code>',
                '<code>[cfgeo_{property}]</code>'
            ) . '</p>' .
            '<p>' . __('The property names shown in the tables correspond to values returned for the current visitor. Conditional shortcodes may contain text, HTML and nested shortcodes; preserve the opening and closing shortcode syntax shown in each example.', 'cf-geoplugin') . '</p>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes', __('Shortcode overview', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes/cfgeo', __('Conditional shortcode documentation', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes/cfgeo_property', __('Property shortcode documentation', 'cf-geoplugin')) . '</li></ul>'
        );

        $this->add_help_tab(
            'cfgp-plugin-tags-css',
            __('Geo Tags and CSS', 'cf-geoplugin'),
            '<h3>' . __('Geo Tags and CSS classes', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Geo Tags are text placeholders for geographic values. CSS classes show or hide existing elements according to the detected location. Both features must be enabled in Geo Controller settings before use.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Full-page caches can store location-specific output and serve it to another visitor. Configure cache variation or exclusions before relying on geographic shortcodes, tags or CSS visibility rules.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/cf-geo-plugin-tags', __('Geo Tag documentation', 'cf-geoplugin')) . '</p>'
        );

        $this->add_help_tab(
            'cfgp-plugin-documentation',
            __('Documentation', 'cf-geoplugin'),
            '<h3>' . __('Documentation', 'cf-geoplugin') . '</h3>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/how-to-use-cf-geoplugin', __('How to use Geo Controller', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/what-information-cf-geoplugin-returns', __('Geographic information returned by Geo Controller', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes', __('Geo Controller Shortcodes', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/cf-geo-plugin-tags', __('Geo Controller Tags', 'cf-geoplugin')) . '</li></ul>'
        );
    }

    /*
     * Geo Banner help
     */
    public function help__cf_geoplugin_banner()
    {
        get_current_screen()->add_help_tab([
            'id'      => 'cfgp-banner-usage',
            'title'   => __('Geo Banner Usage', 'cf-geoplugin'),
            'content' => '
				<h3>' . __('Geo Banner Usage', 'cf-geoplugin') . '</h3>
				<p>' . __('Geo Banner allows you to place dynamic content, images, videos and pages using shortcodes for specfic audience target by geo location.', 'cf-geoplugin') . '</p>
				<p>' . __('Geo Banner in its setting works as a standard page or post that allows easy and familiar use.', 'cf-geoplugin') . '</p>
				<p>' . __('When you create your banner, whether text, image, HTML or video, you need to save and publish content to get an adequate shortcode.', 'cf-geoplugin') . '</p>
				<p>' . __('You can place this shortcode into any page or post and when a visitor comes from the US, the content you defined will appear.', 'cf-geoplugin') . '</p>
				<p>' . __('You can also define inside shortcode “default content” that is visible to all non-defined visitors by geo location. On this way you can always change the content of the entire site, depending on your visitors location.', 'cf-geoplugin') . '</p>
			',
        ]);
    }

    /*
     * SEO redirection help
     */
    public function help__cf_geoplugin_seo_redirection()
    {
        $this->add_help_tab(
            'cfgp-seo-redirect-overview',
            __('Overview', 'cf-geoplugin'),
            '<h3>' . __('Location-based site rules', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Create or edit a rule by selecting its geographic conditions, destination URL and HTTP status. The rules list provides enable, disable, only-once, always and delete actions, including bulk actions.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('More specific geographic fields narrow a match. Review overlapping rules and test from the intended locations before enabling them on a production website.', 'cf-geoplugin') . '</p>'
        );

        $this->add_help_tab(
            'cfgp-seo-redirect-setup',
            __('Rule setup', 'cf-geoplugin'),
            '<h3>' . __('Create and manage a rule', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Enter a complete destination URL, choose the geographic fields that must match, select the response status and enable the rule. Use the edit action to change an existing rule without recreating it.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Only once stores a visitor cookie after the rule is applied so subsequent matching requests can continue to the requested page. Browser privacy settings or cleared cookies can cause the rule to apply again.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Avoid destinations that return visitors to the same rule. Geo Controller rejects a destination equal to the current URL, but multi-step loops between different URLs must still be avoided.', 'cf-geoplugin') . '</p>'
        );

        $this->add_help_tab(
            'cfgp-seo-redirect-http',
            __('HTTP and SEO', 'cf-geoplugin'),
            '<h3>' . __('Choose the intended response', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Use 301 or 308 for permanent moves and 302, 303 or 307 for temporary behavior. Status 404 returns a Not Found response and is not a normal redirect; use it only when the matching location must not receive the requested resource.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Redirects can affect indexing, analytics and referral data. The settings page also provides bot exclusion and referrer controls; choose them according to the website SEO and privacy requirements.', 'cf-geoplugin') . '</p>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/plugin-usage/seo-redirection-usage/available-http-codes', __('Available HTTP statuses', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/plugin-usage/seo-redirection-usage/how-to-bypass-seo-redirection', __('Bypass a redirection while testing', 'cf-geoplugin')) . '</li></ul>'
        );

        $this->add_help_tab(
            'cfgp-seo-redirect-csv',
            __('CSV and backups', 'cf-geoplugin'),
            '<h3>' . __('Import, export and restore rules', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('The CSV file must contain exactly eight columns in this order: country, region, city, postcode, url, http_code, active, only_once. Accepted statuses are 301, 302, 303, 307, 308 and 404; active and only_once accept 1 or 0.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Import replaces the current redirection data. Export the existing rules before importing so they can be restored if the uploaded file is incomplete or incorrect.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/plugin-usage/seo-redirection-usage', __('SEO redirection documentation', 'cf-geoplugin')) . '</p>'
        );
    }

    public function help__cf_geoplugin_google_map()
    {
        $this->add_help_tab(
            'cfgp-google-map-setup',
            __('Map setup', 'cf-geoplugin'),
            '<h3>' . __('Google Maps configuration', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Configure the Google Maps API key, default coordinates and default map values in Geo Controller settings. The Google Cloud project must have the required Maps API enabled and must permit requests from this website.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Latitude, longitude, width, height, zoom and control options supplied in a shortcode override their configured defaults for that map.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/google-map-settings', __('Google Map settings documentation', 'cf-geoplugin')) . '</p>'
        );

        $this->add_help_tab(
            'cfgp-google-map-shortcode',
            __('Map shortcode', 'cf-geoplugin'),
            '<h3>' . __('Build a map shortcode', 'cf-geoplugin') . '</h3>' .
            '<p>' . sprintf(
                /* translators: %s: map shortcode. */
                __('Use %s with the location and display attributes shown on this page. Copy a complete example when it is important to preserve quotes and multiline content.', 'cf-geoplugin'),
                '<code>[cfgeo_map]</code>'
            ) . '</p>' .
            '<p>' . __('Map rendering depends on a valid API key, the selected Google APIs, account restrictions and the visitor browser. A map failure does not change the detected geographic data.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/quick-start/geo-controller-shortcodes/cfgeo_map', __('Map shortcode documentation', 'cf-geoplugin')) . '</p>'
        );
    }

    public function help__cf_geoplugin_defender()
    {
        $this->add_help_tab(
            'cfgp-defender-rules',
            __('Access rules', 'cf-geoplugin'),
            '<h3>' . __('Configure access restrictions carefully', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Defender can restrict visitors by IP address, detected location or proxy status and can exempt configured whitelist entries. IP-based location and proxy detection can be incomplete or inaccurate, especially behind VPNs, proxies or CDNs.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Restrictions can affect public pages and WordPress administration. Verify the whitelist and keep the recovery link available before enabling rules that could match an administrator.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/plugin-usage/anti-spam-protection', __('Defender documentation', 'cf-geoplugin')) . '</p>'
        );

        $this->add_help_tab(
            'cfgp-defender-recovery',
            __('Recovery and testing', 'cf-geoplugin'),
            '<h3>' . __('Prevent an administrator lockout', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Copy and store the generated recovery link before testing restrictions. Open it only when access must be restored, and treat it as sensitive because anyone with the complete link may be able to bypass the configured block.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Test the blocked response, custom message and recovery process in a separate browser session before relying on the rules in production.', 'cf-geoplugin') . '</p>'
        );
    }

    public function help__cf_geoplugin_debug()
    {
        $this->add_help_tab(
            'cfgp-debug-data',
            __('Diagnostic data', 'cf-geoplugin'),
            '<h3>' . __('Read the diagnostic tabs', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Received data shows the geographic response currently available to Geo Controller. Sent data shows request context used for the lookup, while Server statistics describes the PHP and server environment. The map tab visualizes available coordinates.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Use IP Lookup to inspect the response for a specific address. Geolocation is an estimate based on network information, not a precise device position; VPNs, proxies, mobile networks, CDNs and cached pages can change the apparent address or produce incomplete results.', 'cf-geoplugin') . '</p>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/concepts/troubleshooting-basics', __('Troubleshooting basics', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/concepts/data-sources-and-accuracy', __('Data sources and accuracy', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/concepts/ip-detection-proxies-vpn-cdns', __('IP, proxy, VPN and CDN considerations', 'cf-geoplugin')) . '</li></ul>'
        );

        $this->add_help_tab(
            'cfgp-debug-support',
            __('Troubleshooting', 'cf-geoplugin'),
            '<h3>' . __('Before reporting a problem', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Compare the detected IP and geographic response with the active proxy or CDN configuration, then clear relevant caches and repeat the request. Do not publish unredacted diagnostics because request and server data may contain sensitive information.', 'cf-geoplugin') . '</p>'
        );
    }

    public function help__cf_geoplugin_settings()
    {
        $this->add_help_tab(
            'cfgp-settings-overview',
            __('Settings overview', 'cf-geoplugin'),
            '<h3>' . __('Configure Geo Controller', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('General settings control updates, dashboard output, SSL, Cloudflare and cache compatibility. Lookup settings control DNS, measurement units and currency. Feature settings enable shortcodes, menus, banners, maps, CSS, JavaScript and API access.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('SEO redirection, protection, Geo Tags, whitelist, notifications, proxy settings and Google Maps each have dedicated sections. Save changes in each section after reviewing their effect.', 'cf-geoplugin') . '</p>' .
            '<p>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings', __('Settings documentation', 'cf-geoplugin')) . '</p>'
        );

        $this->add_help_tab(
            'cfgp-settings-network-cache',
            __('Network and cache', 'cf-geoplugin'),
            '<h3>' . __('Proxy, CDN and cache settings', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('Enable Cloudflare or configure proxy headers only when those services are actually in front of WordPress. Incorrect trusted-proxy configuration can cause the wrong visitor IP to be used.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Location-aware output can conflict with full-page caching. Use the cache compatibility option together with the cache or CDN configuration and verify responses from more than one location. Increase the HTTP API timeout only when lookups regularly need more time, because longer waits can delay page requests.', 'cf-geoplugin') . '</p>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/enable-cloudflare', __('Cloudflare settings', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/fix-cache', __('Cache compatibility', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/proxy-settings', __('Proxy settings', 'cf-geoplugin')) . '</li></ul>'
        );

        $this->add_help_tab(
            'cfgp-settings-services',
            __('Services and protection', 'cf-geoplugin'),
            '<h3>' . __('APIs, redirection and protection', 'cf-geoplugin') . '</h3>' .
            '<p>' . __('The REST API exposes geographic functionality to other requests, while Public API Transport selects GET or POST for supported public API communication. Enable only the interfaces the website uses. Redirection and Defender settings can change visitor access, so test them with administrator recovery and bot behavior in mind.', 'cf-geoplugin') . '</p>' .
            '<p>' . __('Beta and advanced logging options are intended for controlled testing and diagnostics. Logging can increase stored data and should be disabled when it is no longer required.', 'cf-geoplugin') . '</p>' .
            '<ul><li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/enable-rest-api', __('REST API setting', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/public-api-transport', __('Public API Transport setting', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/enable-site-redirection', __('SEO redirection setting', 'cf-geoplugin')) . '</li>' .
            '<li>' . $this->documentation_link('https://wpgeocontroller.com/documentation/user-guide/cf-geoplugin-settings/enable-spam-protection', __('Defender setting', 'cf-geoplugin')) . '</li></ul>'
        );
    }

    /*
     * Instance
     * @verson    1.0.0
     */
    public static function instance()
    {

        if (!is_admin()) {
            return;
        }
        $class    = self::class;
        $instance = CFGP_Cache::get($class);

        if (!$instance) {
            $instance = CFGP_Cache::set($class, new self());
        }

        return $instance;
    }
}
endif;
