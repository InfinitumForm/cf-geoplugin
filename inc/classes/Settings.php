<?php
/**
 * Settings page
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Settings')) :
class CFGP_Settings extends CFGP_Global {
	
	function __construct(){
		if(!class_exists('CFGP_Sidebar')) {
			include_once CFGP_INC . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'sidebar.php';
			CFGP_Sidebar::instance();
		}
		$this->add_action( (CFGP_NETWORK_ADMIN ? 'network_admin_menu' : 'admin_menu'), 'add_pages',  11 );
		$this->add_action( 'admin_init', 'admin_init' );
	}
	
	// Initialize plugin settings
	public function admin_init(){
		
		if(isset($_GET['rstr_response']) && $_GET['rstr_response'] == 'saved') {
			$this->add_action( 'admin_notices', 'notices__saved' );
		}
		
		if(isset($_GET['save_settings'])){
			if($_GET['save_settings'] == 'true') {
				$this->save_settings();
			} else if($_GET['save_settings'] == 'false') {
				$this->add_action( 'admin_notices', 'notices__error' );
			}
		}
	}
	
	// Save settings
	public function save_settings(){
		$parse_url = CFGP_U::parse_url();
		$url = $parse_url['url'];
		if($nonce = CFGP_U::request_string('nonce', false))
		{
			if(wp_verify_nonce($nonce, CFGP_NAME.'-save-settings') !== false)
			{
				if(isset($_POST['cf-geoplugin']))
				{				
					if(CFGP_Options::set($_POST['cf-geoplugin']))
					{
						$url = remove_query_arg('save_settings');
						$url = remove_query_arg('nonce');
						$url = add_query_arg('rstr_response', 'saved', $url);
						wp_safe_redirect($url);
					}
					else
					{
						$url = add_query_arg('save_settings', 'false', $url);
						$url = add_query_arg('rstr_response', 'error_options', $url);
						wp_safe_redirect($url);
					}
				}
				else
				{
					$url = add_query_arg('save_settings', 'false', $url);
					$url = add_query_arg('rstr_response', 'error_form', $url);
					wp_safe_redirect($url);
				}
			}
			else
			{
				$url = add_query_arg('save_settings', 'false', $url);
				$url = add_query_arg('rstr_response', 'error_nonce', $url);
				wp_safe_redirect($url);
			}
		}
	}
	
	public function notices__saved(){
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			__('Settings saved.', CFGP_NAME)
		);
	}
	
	public function notices__error(){
		if(isset($_GET['rstr_response'])) :
			if($_GET['rstr_response'] == 'error_nonce')
			{
				printf(
					'<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
					__('NONCE ERROR!', CFGP_NAME),
					__('Nonce is incorrect or has expired. Please refresh the page and try again. Unable to save settings.', CFGP_NAME)
				);
			}
			else if($_GET['rstr_response'] == 'error_form')
			{
				printf(
					'<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
					__('FORM ERROR!', CFGP_NAME),
					__('The form was not submitted regularly. Unable to save settings.', CFGP_NAME)
				);
			}
			else if($_GET['rstr_response'] == 'error_options')
			{
				printf(
					'<div class="notice notice-error"><h3 style="margin: 1em 0 0 0;">%s</h3><p>%s</p></div>',
					__('FORM ERROR!', CFGP_NAME),
					__('The form was not submitted regularly. Unable to save settings.', CFGP_NAME)
				);
			}
		else return; endif;
	}
	
	/* Add admin pages */
	public function add_pages(){
		// Only admins
		if ( !(current_user_can( 'update_plugins' ) && current_user_can( 'delete_plugins' ) && current_user_can( 'install_plugins' )) ){
			return;
		}
		
		$this->add_menu_page(
			__( 'Geo Plugin', CFGP_NAME ),
			__( 'Geo Plugin', CFGP_NAME ),
			'manage_options',
			CFGP_NAME,
			'main_page__callback',
			'dashicons-location-alt',
			59
		);
		if(CFGP_Options::get('enable_gmap', false))
		{
			$this->add_submenu_page(
				CFGP_NAME,
				__('Google Map',CFGP_NAME),
				__('Google Map',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-google-map',
				'google_map__callback'
			);
		}
		if(CFGP_Options::get('enable_defender', 1))
		{
			$this->add_submenu_page(
				CFGP_NAME,
				__('Site Protection',CFGP_NAME),
				__('Site Protection',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-defender',
				'defender__callback'
			);
		}
		if(CFGP_Options::get('enable_banner', false)) {
			$this->add_submenu_page(
				CFGP_NAME,
				__('Geo Banner',CFGP_NAME),
				__('Geo Banner',CFGP_NAME),
				'manage_options',
				CFGP_U::admin_url('edit.php?post_type=' . CFGP_NAME . '-banner')
			);
		}
		if(CFGP_Options::get('enable_seo_redirection', 1))
		{
			$this->add_submenu_page(
				CFGP_NAME,
				__('SEO Redirection',CFGP_NAME),
				__('SEO Redirection',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-seo-redirection',
				'seo_redirection__callback'
			);
		}/*
		$this->add_submenu_page(
			CFGP_NAME,
			__('Countries',CFGP_NAME),
			__('Countries',CFGP_NAME),
			'manage_options',
			CFGP_U::admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-country&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Regions',CFGP_NAME),
			__('Regions',CFGP_NAME),
			'manage_options',
			CFGP_U::admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-region&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Cities',CFGP_NAME),
			__('Cities',CFGP_NAME),
			'manage_options',
			CFGP_U::admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-city&post_type=' . CFGP_NAME . '-banner')
		);*/
		$this->add_submenu_page(
			CFGP_NAME,
			__('Postcode',CFGP_NAME),
			__('Postcodes',CFGP_NAME),
			'manage_options',
			CFGP_U::admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-postcode&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Debug Mode',CFGP_NAME),
			__('Debug Mode',CFGP_NAME),
			'manage_options',
			CFGP_NAME . '-debug',
			'debug__callback'
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Settings',CFGP_NAME),
			__('Settings',CFGP_NAME),
			'manage_options',
			CFGP_NAME . '-settings',
			'settings__callback'
		);
		
		
		if(CFGP_License::activated()) {
			$this->add_submenu_page(
				CFGP_NAME,
				__('License',CFGP_NAME),
				__('License',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-activate',
				'license__callback'
			);
		} else {		
			$this->add_submenu_page(
				CFGP_NAME,
				__('Activate Unlimited',CFGP_NAME),
				'<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-activate',
				'license__callback'
			);
		}
	}
	
	public function main_page__callback(){
		include_once CFGP_INC . '/filters/main_page.php';
		include_once CFGP_INC . '/settings/main_page.php';
	}
	
	public function google_map__callback(){
		include_once CFGP_INC . '/settings/google_map.php';
	}
	
	public function defender__callback(){
		include_once CFGP_INC . '/settings/defender.php';
	}
	
	public function seo_redirection__callback(){
		wp_enqueue_media();
		include_once CFGP_INC . '/filters/seo_redirection_form.php';
		include_once CFGP_INC . '/filters/seo_redirection_import.php';
		include_once CFGP_INC . '/filters/seo_redirection_table.php';
		include_once CFGP_INC . '/settings/seo_redirection.php';
	}
	
	public function debug__callback(){
		include_once CFGP_INC . '/settings/debug.php';
	}
	
	public function settings__callback(){
		include_once CFGP_INC . '/filters/settings.php';
		include_once CFGP_INC . '/filters/settings-rest.php';
		include_once CFGP_INC . '/settings/settings.php';
	}
	
	public function license__callback(){
		include_once CFGP_INC . '/filters/license.php';
		include_once CFGP_INC . '/settings/license.php';
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