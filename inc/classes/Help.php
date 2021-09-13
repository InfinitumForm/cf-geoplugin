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

if(!class_exists('CFGP_Help')) :
class CFGP_Help extends CFGP_Global {
	
	function __construct(){
		$page = CFGP_U::request_string('page');
		$function = str_replace('-', '_', $page);
		if(method_exists($this, "help__{$function}")){
			$this->add_action('current_screen', "help__{$function}");
		}
		if(method_exists($this, "screen_option__{$function}")){
			$this->add_action('set-screen-option', "screen_option__{$function}", 10, 3);
		}
	}
	
	/*
	 * SEO redirection screen option
	 */
	public function screen_option__cf_geoplugin_seo_redirection($status, $option, $value) {
		if ( 'cfgp_seo_redirection_num_rows' == $option ) return $value;
	}
	
	/*
	 * SEO redirection help
	 */
	public function help__cf_geoplugin_seo_redirection() {
		
		add_screen_option( 'per_page', [
			'option'  => 'cfgp_seo_redirection_num_rows',
			'default'   => 20,
			'label'	  => __('Number of items per page', CFGP_NAME)
		] );
		
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-seo-redirect-intro',
			'title'    => __( 'Intro', CFGP_NAME ),
			'content'  => '
				<h3>' . __( 'How this SEO redirection work?', CFGP_NAME) . '</h3>
				<p>' . __( 'On this settings you can set SEO redirection for the whole WordPress installation', CFGP_NAME) . '</p>
				<p>' . __( 'This functionality allows you to apply redirects from your site to other sites for your visitors, based on their geo location.', CFGP_NAME) . '</p>
				<p>' . __( 'It is important to be careful with this functionality because you can fall into an endless loop or block yourself from accessing the site.', CFGP_NAME) . '</p>
			'
		));
		
		get_current_screen()->add_help_tab( array(
			'id'       => 'cfgp-seo-redirect-setup',
			'title'    => __( 'Setup', CFGP_NAME ),
			'content'  => '
				<h3>' . __( 'SEO Redirection Setup', CFGP_NAME) . '</h3>
				<p>' . __( 'In order to set the SEO redirection of your entire site you need to define the location from which you want to redirect your visitors to another location.', CFGP_NAME) . '</p>
				<p>' . __( 'In the settings you can set the HTTP code that is sent to the browser and which is also understood by Google.', CFGP_NAME) . '</p>
				<p>' . sprintf(__( 'This HTTP code is a key part of SEO settings because with it you can have complete control over your content. You can read more about HTTP codes in our article: %s', CFGP_NAME), '<a href="https://cfgeoplugin.com/information/seo-redirection-in-wordpress" target="_blank">'.__('SEO Redirection in WordPress', CFGP_NAME).'</a>') . '</p>
				<p>' . __( 'You also have the option to redirect only once in a visitor session. This allows you to allow later access to the site to your visitor after redirecting to another location.', CFGP_NAME) . '</p>
			'
		));
		
		if(in_array(CFGP_U::request_string('action'), array('new', 'edit')) === false)
		{
			get_current_screen()->add_help_tab( array(
				'id'       => 'cfgp-seo-redirect-import-from-csv',
				'title'    => __( 'Import from CSV', CFGP_NAME ),
				'content'  => '
					<h3>' . __( 'To make our job easier, we made the possibility of CSV upload', CFGP_NAME) . '</h3>
					<p>' . __( 'If you want to make large amounts of redirects easier, we give you this option. Here you can easily enter a thousand redirects by the rules you define in your CSV file with just a few clicks. Before proceeding with this, you need to be informed about the structure of the CSV file that we expect.', CFGP_NAME) . '</p>
					<p>' . __( 'The file must be a standard comma separated CSV with exactly 8 columns. The order of the column is extremely important and its content is strict. If you do not follow the format and column order, CSV will be rejected.', CFGP_NAME) . '</p>
					<dl>
						<dt>country</dt>
						<dd>' . __('Country Code - standard 2 letter country code (example: RS)', CFGP_NAME) . '</dd>
						<dt>region</dt>
						<dd>' . __('Region Name (example: Belgrade)', CFGP_NAME) . '</dd>
						<dt>city</dt>
						<dd>' . __('City Name (example: Belgrade)', CFGP_NAME) . '</dd>
						<dt>postcode</dt>
						<dd>' . __('Postcode Name (example: 1210)', CFGP_NAME) . '</dd>
						<dt>url</dt>
						<dd>' . __('Redirect URL - valid URL format', CFGP_NAME) . '</dd>
						<dt>http_code</dt>
						<dd>' . __('HTTP Status Code - Accept 301, 302, 303 and 404', CFGP_NAME) . '</dd>
						<dt>active</dt>
						<dd>' . __('Active - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME) . '</dd>
						<dt>only_once</dt>
						<dd>' . __('Redirect only once - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME) . '</dd>
					</dl>
				'
			));
			get_current_screen()->add_help_tab( array(
				'id'       => 'cfgp-seo-redirect-export-in-csv-file',
				'title'    => __( 'Export in CSV file', CFGP_NAME ),
				'content'  => '
					<h3>' . __( 'You can always back up your data', CFGP_NAME) . '</h3>
					<p>' . __( 'You can back up your redirects at any time and then edit or supplement them in a single file and restore them to the server.', CFGP_NAME) . '</p>
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