<?php
/**
 * Settings page
 *
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Settings')) :
class CFGP_Settings extends CFGP_Global {
	
	function __construct(){
		$this->add_action( (CFGP_NETWORK_ADMIN ? 'network_admin_menu' : 'admin_menu'), 'add_pages',  11 );
		$this->add_action( 'admin_enqueue_scripts', 'register_scripts' );
		$this->add_action( 'admin_enqueue_scripts', 'register_scripts_ctp' );
		$this->add_action( 'admin_enqueue_scripts', 'register_style' );
		$this->add_action( 'admin_init', 'admin_init' );
		$this->add_action( 'admin_bar_menu', 'admin_bar_menu', 90, 1 );
		
		if(!class_exists('CFGP_Sidebar')) {
			include_once CFGP_INC . '/Settings/sidebar.php';
			CFGP_Sidebar::instance();
		}
	}
	
	// Initialize plugin settings
	public function admin_init(){
		$this->plugin_custom_menu_class();
		
		if(isset($_GET['rstr_response']) && $_GET['rstr_response'] == 'saved') {
			$this->notices__saved();
		}
		
		if(isset($_GET['save_settings'])){
			if($_GET['save_settings'] == 'true') {
				$this->save_settings();
			} else if($_GET['save_settings'] == 'false') {
				$this->notices__error();
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
	
	public function register_style($page){
		if(!$this->limit_scripts($page)) return;
		
		wp_enqueue_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), (string)CFGP_VERSION );
		wp_enqueue_style( CFGP_NAME . '-admin', CFGP_ASSETS . '/css/style-admin.css', array(CFGP_NAME . '-fontawesome'), (string)CFGP_VERSION );
	}
	
	// Register CPT and taxonomies scripts
	public function register_scripts_ctp( $page )
	{
		$post = '';
		$url = '';
		
		if( isset( $_GET['taxonomy'] ) ) $post = $_GET['taxonomy'];
		elseif( isset( $_GET['post'] ) )
		{
			$post = get_post( absint( $_GET['post'] ) );
			$post = isset( $post->post_type ) ? $post->post_type : '';
		}
		elseif( isset( $_GET['post_type'] ) ) $post = $_GET['post_type'];

		if( !$this->limit_scripts( $post ) ) return false;

		if( $post === 'cf-geoplugin-banner' ) $url = sprintf( 'edit.php?post_type=%s', $post );
		else $url = sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s-banner', $post, CFGP_NAME );
		
		wp_enqueue_script( CFGP_NAME . '-cpt', CFGP_ASSETS . '/js/script-cpt.js', array('jquery'), (string)CFGP_VERSION, true );
		wp_localize_script(CFGP_NAME . '-cpt', 'CFGP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'label' => array(
				'loading' => esc_attr__('Loading...',CFGP_NAME),
				'not_found' => esc_attr__('Not Found!',CFGP_NAME),
				'placeholder' => esc_attr__('Search',CFGP_NAME)
			),
			'current_url'	=> $url
		));
	}
	
	public function register_scripts($page){
		if(!$this->limit_scripts($page)) return;
		
		wp_enqueue_style( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
		wp_enqueue_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
		
		wp_enqueue_script( CFGP_NAME . '-admin', CFGP_ASSETS . '/js/script-admin.js', array('jquery', CFGP_NAME . '-choosen'), (string)CFGP_VERSION, true );
		wp_localize_script(CFGP_NAME . '-admin', 'CFGP', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'adminurl' => self_admin_url('/'),
			'label' => array(
				'unload' => esc_attr__('Data will lost , Do you wish to continue?',CFGP_NAME),
				'loading' => esc_attr__('Loading...',CFGP_NAME),
				'not_found' => esc_attr__('Not Found!',CFGP_NAME),
				'alert' => array(
					'close' => esc_attr__('Close',CFGP_NAME)
				),
				'rss' => array(
					'no_news' => esc_attr__('There are no news at the moment.',CFGP_NAME),
					'error' => esc_attr__("ERROR! Can't load news feed.",CFGP_NAME)
				),
				'chosen' => array(
					'not_found' => esc_attr__('Nothing found!',CFGP_NAME)
				),
				'settings' => array(
					'saved' => esc_attr__('Option saved successfuly!',CFGP_NAME),
					'fail' => esc_attr__('There was some unexpected system error. Changes not saved!',CFGP_NAME),
					'false' => esc_attr__('Changes not saved for unexpected reasons. Try again!',CFGP_NAME),
					'error' => esc_attr__('Option you provide not match to global variables. Permission denied!',CFGP_NAME)
				),
				'csv' => array(
					'saved' => esc_attr__('Successfuly saved %d records.',CFGP_NAME),
					'fail' => esc_attr__('Failed to add %d rows.',CFGP_NAME),
					'upload' =>	esc_attr__('Upload CSV file.',CFGP_NAME),
					'filetype' => esc_attr__('The file must be comma separated CSV type',CFGP_NAME),
					'exit' => esc_attr__('Are you sure, you want to exit?\nChanges wont be saved!',CFGP_NAME),
					'delete' =>	esc_attr__('Are you sure, you want to delete this redirection?',CFGP_NAME),
					'missing_url' => esc_attr__('URL Missing. Please insert URL from your CSV file or choose file from the library.',CFGP_NAME),
				),
				'rest' => array(
					'delete' => esc_attr__("Are you sure, you want to delete this access token?",CFGP_NAME),
					'error' => esc_attr__("Can't delete access token because unexpected reasons.",CFGP_NAME),
				),
				'footer_menu' => array(
					'documentation' =>	esc_attr__('Documentation',CFGP_NAME),
					'contact' => esc_attr__('Contact',CFGP_NAME),
					'blog' => esc_attr__('Blog',CFGP_NAME),
					'faq' => esc_attr__('FAQ',CFGP_NAME),
					'thank_you' => esc_attr__('Thank you for using',CFGP_NAME)
				)
			)
		));
		
	}
	
	// Add admin top bar menu pages
	public function admin_bar_menu($wp_admin_bar) {
		$wp_admin_bar->add_node(array(
			'id' => CFGP_NAME . '-admin-bar-link',
			'title' => __('Geo Plugin', CFGP_NAME), 
			'href' => esc_url(admin_url('admin.php?page=cf-geoplugin')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-link',
				'title' => __('Geo Plugin', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-shortcodes-link',
			'title' => __('Shortcodes', CFGP_NAME), 
			'href' => esc_url(admin_url('admin.php?page=cf-geoplugin')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-shortcodes-link',
				'title' => __('Shortcodes', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-settings-link',
			'title' => __('Settings', CFGP_NAME), 
			'href' => esc_url(admin_url('admin.php?page=cf-geoplugin-settings')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-settings-link',
				'title' => __('Settings', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-debug-link',
			'title' => __('Debug Mode', CFGP_NAME), 
			'href' => esc_url(admin_url('admin.php?page=cf-geoplugin-debug')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-debug-link',
				'title' => __('Debug Mode', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-activate-link',
			'title' => __('License', CFGP_NAME), 
			'href' => esc_url(admin_url('admin.php?page=cf-geoplugin-activate')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-activate-link',
				'title' => __('License', CFGP_NAME),
			)
		));
	}
	
	/* Add admin pages */
	public function add_pages(){
		// Only admins and editors
		if ( !(current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' )) ) return;
		
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
				admin_url('edit.php?post_type=' . CFGP_NAME . '-banner')
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
		}
		$this->add_submenu_page(
			CFGP_NAME,
			__('Countries',CFGP_NAME),
			__('Countries',CFGP_NAME),
			'manage_options',
			admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-country&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Regions',CFGP_NAME),
			__('Regions',CFGP_NAME),
			'manage_options',
			admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-region&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Cities',CFGP_NAME),
			__('Cities',CFGP_NAME),
			'manage_options',
			admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-city&post_type=' . CFGP_NAME . '-banner')
		);
		$this->add_submenu_page(
			CFGP_NAME,
			__('Postcode',CFGP_NAME),
			__('Postcode',CFGP_NAME),
			'manage_options',
			admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-postcode&post_type=' . CFGP_NAME . '-banner')
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
		
		$this->add_submenu_page(
			CFGP_NAME,
			__('Activate Unlimited',CFGP_NAME),
			'<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
			'manage_options',
			CFGP_NAME . '-activate',
			'license__callback'
		);
	}
	
	public function main_page__callback(){
		include_once CFGP_INC . '/Settings/main_page.php';
	}
	
	public function google_map__callback(){
		include_once CFGP_INC . '/Settings/google_map.php';
	}
	
	public function defender__callback(){
		include_once CFGP_INC . '/Settings/defender.php';
	}
	
	public function seo_redirection__callback(){
		include_once CFGP_INC . '/Filters/seo_redirection_table.php';
		include_once CFGP_INC . '/Filters/seo_redirection_new.php';
		include_once CFGP_INC . '/Filters/seo_redirection_edit.php';
		include_once CFGP_INC . '/Settings/seo_redirection.php';
	}
	
	public function debug__callback(){
		include_once CFGP_INC . '/Settings/debug.php';
	}
	
	public function settings__callback(){
		include_once CFGP_INC . '/Filters/settings.php';
		include_once CFGP_INC . '/Filters/settings-rest.php';
		include_once CFGP_INC . '/Settings/settings.php';
	}
	
	public function license__callback(){
		include_once CFGP_INC . '/Filters/license.php';
		include_once CFGP_INC . '/Settings/license.php';
	}
	
	/*
	 * Limit scripts
	 */
	public function limit_scripts($page){
		if(strpos($page, CFGP_NAME) !== false) return true;
		return false;
	}
	
	// Fix collapsing admin menu
	public function plugin_custom_menu_class()
	{
		global $menu;

		$show = false;
		if( isset( $_GET['post_type'] ) ) $show = $this->limit_scripts( $_GET['post_type'] ); // This will also check for taxonomies

		if( is_array( $menu ) && $show )
		{
			foreach( $menu as $key => $value )
			{
				if( $value[0] == 'Geo Plugin' )
				{
					$menu[$key][4] = 'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_cf-geoplugin menu-top-first wp-menu-open';
				}
			}
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
		
		global $cfgp_cache;
		$class = self::class;
		$instance = $cfgp_cache->get($class);
		if ( !$instance ) {
			$instance = $cfgp_cache->set($class, new self());
		}
		return $instance;
	}
}
endif;