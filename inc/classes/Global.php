<?php
/**
 * Global Class
 *
 * Main global constructor
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

if(!class_exists('CFGP_Global', false)) : class CFGP_Global {
	
	/*
	 * Hook for register_uninstall_hook()
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function register_uninstall_hook($function){
		return register_uninstall_hook( CFGP_FILE, $function );
	}
	
	/*
	 * Hook for register_deactivation_hook()
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function register_deactivation_hook($function){
		return register_deactivation_hook( CFGP_FILE, $function );
	}
	
	/*
	 * Hook for register_activation_hook()
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function register_activation_hook($function){
		return register_activation_hook( CFGP_FILE, $function );
	}
	
	/* 
	 * Hook for add_action()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_action(string $tag, $function_to_add, int $priority = 10, int $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = [&$this, $function_to_add];
			
		return add_action( (string)$tag, $function_to_add, $priority, $accepted_args );
	}
	
	/* 
	 * Hook for remove_action()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function remove_action(string $tag, $function_to_remove, int $priority = 10){
		if(!is_array($function_to_remove))
			$function_to_remove = [&$this, $function_to_remove];
			
		return remove_action( $tag, $function_to_remove, $priority );
	}
	
	/* 
	 * Hook for add_filter()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_filter(string $tag, $function_to_add, int $priority = 10, int $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = [&$this, $function_to_add];
			
		return add_filter( (string)$tag, $function_to_add, $priority, $accepted_args );
	}
	
	/* 
	 * Hook for remove_filter()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function remove_filter(string $tag, $function_to_remove, int $priority = 10){
		if(!is_array($function_to_remove))
			$function_to_remove = [&$this, $function_to_remove];
			
		return remove_filter( (string)$tag, $function_to_remove, $priority );
	}
	
	/* 
	 * Hook for add_shortcode()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_shortcode(string $tag, $function_to_add){
		if(!is_array($function_to_add))
			$function_to_add = [&$this, $function_to_add];
		
		if(!shortcode_exists($tag)) {
			return add_shortcode( $tag, $function_to_add );
		}
		
		return false;
	}
	
	/* 
	 * Hook for add_options_page()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_options_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null){
		if(!is_array($function))
			$function = [&$this, $function];
		
		return add_options_page($page_title, $menu_title, $capability, $menu_slug, $function, $position);
	}
	
	/* 
	 * Hook for add_settings_section()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_settings_section($id, $title, $callback, $page){
		if(!is_array($callback))
			$callback = [&$this, $callback];
		
		return add_settings_section($id, $title, $callback, $page);
	}
	
	/* 
	 * Hook for register_setting()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function register_setting($option_group, $option_name, array $args = []){
		if(!is_array($args) && is_callable($args))
			$args = [&$this, $args];
		
		return register_setting($option_group, $option_name, $args);
	}
	
	/* 
	 * Hook for add_settings_field()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_settings_field($id, $title, $callback, $page, $section = 'default', array $args = []){
		if(!is_array($callback))
			$callback = [&$this, $callback];
		
		return add_settings_field($id, $title, $callback, $page, $section, $args);
	}
	
	/* 
	 * Hook for add_menu_page()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = NULL ){
		if(!filter_var($menu_slug, FILTER_VALIDATE_URL) && !is_array($function))
			$function = [&$this, $function];
		
		return add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
	}
	
	/* 
	 * Hook for add_submenu_page()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = NULL ){
		
		if(!filter_var($menu_slug, FILTER_VALIDATE_URL) && !is_array($function))
			$function = [&$this, $function];
		
		return add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function, $position);
	}
	
	/* 
	 * Hook for add_meta_box()
	 * @author        Ivijan-Stefan Stipic
	*/
	public function add_meta_box(string $id, string $title, $callback, $screen = NULL, string $context = 'advanced', string $priority = 'default', array $callback_args = NULL){
		if(!is_array($callback))
			$callback = [&$this, $callback];
		
		return add_meta_box($id, $title, $callback, $screen, $context, $priority, $callback_args);
	}
	
	
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function __instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;