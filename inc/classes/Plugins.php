<?php
/**
 * Include plugins support if they are available
 *
 * @link              http://infinitumform.com/
 * @since             8.0.0
 * @package           cf-geoplugin
 * @author            Ivijan-Stefan Stipic
 */
 
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Plugins')) :
	class CFGP_Plugins extends CFGP_Global
	{
		private $plugins = array(
			'woocommerce' => 'woocommerce',
			'wooplatnica' => 'wooplatnica',
			'contact-form-7' => 'wp-contact-form-7'
	//		'wordpress-seo' => 'wp-seo',
			
		);
		
		function __construct( $options=array(), $only_object = false ) {
			$this->include_plugins($options, $only_object);
			$this->add_filter( 'cfgp/settings', 'cfgp_settings' );
			$this->add_filter( 'cfgp/settings/default', 'cfgp_settings_default' );
		}
		
		/* 
		 * Add plugins settings
		 * @verson    1.0.0
		 */
		public function cfgp_settings ($options =array()){
			$plugin_options = array();
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( is_plugin_active("{$dir_name}/{$file_name}.php") && file_exists($addon) )
				{
					if($plugin_info = CFGP_U::plugin_info(array(), $dir_name))
					{
						$plugin_options[]= array(
							'name' => 'enable-' . $dir_name,
							'label' => $plugin_info->name,
							'desc' => sprintf(__('Enable %s integration.', CFGP_NAME), $plugin_info->name),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', CFGP_NAME),
								0 => __('No', CFGP_NAME)
							),
							'default' => 0
						);
						$plugin_info = NULL;
					}
				}
			}
			
			return array_merge($options, array(
				// Tab
				array(
					'id' => 'plugin_support',
					'title' => __('Plugin Support', CFGP_NAME),
					// Section
					'sections' => array(
						array(
							'id' => 'enable-plugins',
							'title' => __('Enable Plugins', CFGP_NAME),
							'desc' => __('Allow CF Geo Plugin to integrate with existing installed plugins.', CFGP_NAME),
							'inputs' => $plugin_options
						)
					)
				)
			));
		}
		
		/* 
		 * Add default plugin security settings
		 * @verson    1.0.0
		 */
		public function cfgp_settings_default ($options =array()){
			$plugin_options = array();
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( is_plugin_active("{$dir_name}/{$file_name}.php") && file_exists($addon) )
				{
					$plugin_options['enable-' . $dir_name]=0;
				}
			}
			
			return array_merge($options, $plugin_options);
		}
		
		/* 
		 * Include WordPress plugins support
		 * @verson    1.0.0
		 */
		private function include_plugins( $options=array(), $only_object = false ){
			if($only_object === false)
			{				
				// Include important function
				if(!function_exists('is_plugin_active')) {
					include( ABSPATH . 'wp-admin/includes/plugin.php' );
				}
				
				$this->plugins = apply_filters('cfgp/plugins', $this->plugins);
				
				foreach($this->plugins as $dir_name=>$file_name)
				{
					$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
					if( is_plugin_active("{$dir_name}/{$file_name}.php") && file_exists($addon) )
					{
						$class_name = str_replace(['-','.'], '_', $dir_name);
						$plugin_class = "CFGP__Plugin__{$class_name}";
						
						if(CFGP_Options::get('enable-' . $dir_name, 0))
						{
							if(class_exists($plugin_class) && method_exists($plugin_class, 'instance')) {
								$plugin_class::instance();
							} else {
								include_once $addon;
								if(class_exists($plugin_class) && method_exists($plugin_class, 'instance')) {
									$plugin_class::instance();
								}
							}
						}
					}
				}
			}
		}
		
		/* 
		 * Instance
		 * @verson    1.0.0
		 */
		public static function instance($options = array(), $only_object = false ) {
			$class = self::class;
			$instance = CFGP_Cache::get($class);
			if ( !$instance ) {
				$instance = CFGP_Cache::set($class, new self($options, $only_object));
			}
			return $instance;
		}
	}
endif;