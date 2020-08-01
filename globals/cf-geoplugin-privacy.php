<?php
/*
 * Privacy Policy
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since      7.7.2
*/

if(!function_exists('cf_geoplugin_privacy_policy')) :
function cf_geoplugin_privacy_policy() {
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
        return;
    }
 
    $content = sprintf(
        __( 'This site uses the WordPress Geo Plugin (formerly: CF Geo Plugin) to display public visitor information based on IP addresses that can then be collected or used for various purposes depending on the settings of the plugin.
		
		CF Geo Plugin is a GeoMarketing tool that allows you to have full geo control of your WordPress. CF Geo Plugin gives you ability to attach content, geographic information, geo tags, Google Maps to posts, pages, widgets and custom templates by using simple options, shortcodes, PHP code or JavaScript. It also lets you specify a default geographic location for your entire WordPress blog, do SEO redirection, spam protection, WooCommerce control and many more. CF Geo Plugin help you to increase conversion, do better SEO, capture leads on your blog or landing pages.
		
		This website uses API services, technology and goods from the WordPress Geo Plugin and that part belongs to the <a href="%1$s" target="_blank">WordPress Geo Plugin Privacy Policy</a>.',
        CFGP_NAME ),
        CFGP_STORE . '/privacy-policy/'
    );
 
    wp_add_privacy_policy_content(
        'WordPress Geo Plugin',
        wp_kses_post( wpautop( $content, false ) )
    );
}
endif;