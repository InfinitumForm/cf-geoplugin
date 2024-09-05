<?php
/**
 * Contextual Help
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Help', false)) : class CFGP_Help extends CFGP_Global {
	
	function __construct(){
		
		$page = CFGP_U::request_string('page');
		$function = str_replace('-', '_', $page);
		if(method_exists($this, "help__{$function}")){
			$this->add_action('current_screen', "help__{$function}", 10);
		}
		
		if(CFGP_U::request_string('post_type') === 'cf-geoplugin-banner') {
			$this->add_action('current_screen', 'help__cf_geoplugin_banner');
		}
	}
	
	/*
	 * Geo Controller help
	 */
	public function help__cf_geoplugin() {
		ob_start(); ?>
<ul>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/how-to-use-cf-geoplugin" target="_blank"><?php esc_html_e('How to use Geo Controller', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/what-information-cf-geoplugin-returns" target="_blank"><?php esc_html_e('What Information Geo Controller returns?', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/cf-geoplugin-shortcodes" target="_blank"><?php esc_html_e('Geo Controller Shortcodes', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/cf-geo-plugin-tags" target="_blank"><?php esc_html_e('Geo Controller Tags', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/wordpress-geo-plugin-compatibility" target="_blank"><?php esc_html_e('Geo Controller Compatibility', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank"><?php esc_html_e('What do I get from Unlimited License', 'cf-geoplugin'); ?></a></li>
	<li><a href="https://wpgeocontroller.com/documentation/quick-start/frequently-asked-questions" target="_blank"><?php esc_html_e('Frequently Asked Questions', 'cf-geoplugin'); ?></a></li>
</ul>
		<?php
		
	//	$links = ob_get_clean();
		
		$links = '';
		if (ob_get_level()) {
			$links = ob_get_contents();
			ob_end_clean();
		}
		
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-plugin-usage',
			'title'    => __( 'Documentation', 'cf-geoplugin'),
			'content'  => '<h3>' . __( 'Documentation', 'cf-geoplugin') . '</h3>' . $links
		));
	}
	
	/*
	 * Geo Banner help
	 */
	public function help__cf_geoplugin_banner() {
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-banner-usage',
			'title'    => __( 'Geo Banner Usage', 'cf-geoplugin'),
			'content'  => '
				<h3>' . __( 'Geo Banner Usage', 'cf-geoplugin') . '</h3>
				<p>' . __( 'Geo Banner allows you to place dynamic content, images, videos and pages using shortcodes for specfic audience target by geo location.', 'cf-geoplugin') . '</p>
				<p>' . __( 'Geo Banner in its setting works as a standard page or post that allows easy and familiar use.', 'cf-geoplugin') . '</p>
				<p>' . __( 'When you create your banner, whether text, image, HTML or video, you need to save and publish content to get an adequate shortcode.', 'cf-geoplugin') . '</p>
				<p>' . __( 'You can place this shortcode into any page or post and when a visitor comes from the US, the content you defined will appear.', 'cf-geoplugin') . '</p>
				<p>' . __( 'You can also define inside shortcode “default content” that is visible to all non-defined visitors by geo location. On this way you can always change the content of the entire site, depending on your visitors location.', 'cf-geoplugin') . '</p>
			'
		));
	}
	
	/*
	 * SEO redirection help
	 */
	public function help__cf_geoplugin_seo_redirection() {
		
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-seo-redirect-intro',
			'title'    => __( 'Intro', 'cf-geoplugin'),
			'content'  => '
				<h3>' . __( 'How this SEO redirection work?', 'cf-geoplugin') . '</h3>
				<p>' . __( 'On this settings you can set SEO redirection for the whole WordPress installation', 'cf-geoplugin') . '</p>
				<p>' . __( 'This functionality allows you to apply redirects from your site to other sites for your visitors, based on their geo location.', 'cf-geoplugin') . '</p>
				<p>' . __( 'It is important to be careful with this functionality because you can fall into an endless loop or block yourself from accessing the site.', 'cf-geoplugin') . '</p>
			'
		));
		
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-seo-redirect-setup',
			'title'    => __( 'Setup', 'cf-geoplugin'),
			'content'  => '
				<h3>' . __( 'SEO Redirection Setup', 'cf-geoplugin') . '</h3>
				<p>' . __( 'In order to set the SEO redirection of your entire site you need to define the location from which you want to redirect your visitors to another location.', 'cf-geoplugin') . '</p>
				<p>' . __( 'In the settings you can set the HTTP code that is sent to the browser and which is also understood by Google.', 'cf-geoplugin') . '</p>
				<p>' . sprintf(__( 'This HTTP code is a key part of SEO settings because with it you can have complete control over your content. You can read more about HTTP codes in our article: %s', 'cf-geoplugin'), '<a href="https://wpgeocontroller.com/information/seo-redirection-in-wordpress" target="_blank">'.__('SEO Redirection in WordPress', 'cf-geoplugin').'</a>') . '</p>
				<p>' . __( 'You also have the option to redirect only once in a visitor session. This allows you to allow later access to the site to your visitor after redirecting to another location.', 'cf-geoplugin') . '</p>
			'
		));
		
		if(in_array(CFGP_U::request_string('action'), array('new', 'edit')) === false)
		{
			get_current_screen()->add_help_tab( array(
				'id'       => 'cfgp-seo-redirect-import-from-csv',
				'title'    => __( 'Import from CSV', 'cf-geoplugin'),
				'content'  => '
					<h3>' . __( 'To make our job easier, we made the possibility of CSV upload', 'cf-geoplugin') . '</h3>
					<p>' . __( 'If you want to make large amounts of redirects easier, we give you this option. Here you can easily enter a thousand redirects by the rules you define in your CSV file with just a few clicks. Before proceeding with this, you need to be informed about the structure of the CSV file that we expect.', 'cf-geoplugin') . '</p>
					<p>' . __( 'The file must be a standard comma separated CSV with exactly 8 columns. The order of the column is extremely important and its content is strict. If you do not follow the format and column order, CSV will be rejected.', 'cf-geoplugin') . '</p>
					<dl>
						<dt>country</dt>
						<dd>' . __('Country Code - standard 2 letter country code (example: RS)', 'cf-geoplugin') . '</dd>
						<dt>region</dt>
						<dd>' . __('Region Name (example: Belgrade)', 'cf-geoplugin') . '</dd>
						<dt>city</dt>
						<dd>' . __('City Name (example: Belgrade)', 'cf-geoplugin') . '</dd>
						<dt>postcode</dt>
						<dd>' . __('Postcode Name (example: 1210)', 'cf-geoplugin') . '</dd>
						<dt>url</dt>
						<dd>' . __('Redirect URL - valid URL format', 'cf-geoplugin') . '</dd>
						<dt>http_code</dt>
						<dd>' . __('HTTP Status Code - Accept 301, 302, 303 and 404', 'cf-geoplugin') . '</dd>
						<dt>active</dt>
						<dd>' . __('Active - Optional, accept integer (1-Enable, 0-Disable)', 'cf-geoplugin') . '</dd>
						<dt>only_once</dt>
						<dd>' . __('Redirect only once - Optional, accept integer (1-Enable, 0-Disable)', 'cf-geoplugin') . '</dd>
					</dl>
				'
			));
			get_current_screen()->add_help_tab( array(
				'id'       => 'cfgp-seo-redirect-export-in-csv-file',
				'title'    => __( 'Export in CSV file', 'cf-geoplugin'),
				'content'  => '
					<h3>' . __( 'You can always back up your data', 'cf-geoplugin') . '</h3>
					<p>' . __( 'You can back up your redirects at any time and then edit or supplement them in a single file and restore them to the server.', 'cf-geoplugin') . '</p>
				'
			));
		}
	}
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		
		if(!is_admin()) {
			return;
		}
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;