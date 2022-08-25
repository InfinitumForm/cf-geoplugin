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
		// plugin-directory => plugin-file.php
		private $plugins = array(
			'woocommerce' 		=> 'woocommerce',
			'wooplatnica' 		=> 'wooplatnica',
			'contact-form-7'	=> 'wp-contact-form-7',
			'wordpress-seo' 	=> 'wp-seo',
			'elementor' 		=> 'elementor'
		);
		
		function __construct( $options=[], $only_object = false ) {
			$this->include_plugins($options, $only_object);
			$this->add_filter( 'cfgp/settings', 'cfgp_settings' );
			$this->add_filter( 'cfgp/settings/default', 'cfgp_settings_default' );
		}
		
		/* 
		 * Add plugins settings
		 * @verson    1.0.0
		 */
		public function cfgp_settings ($options =[]){
			$plugin_options = [];
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
				{
					if($plugin_info = CFGP_U::plugin_info([], $dir_name))
					{
						$plugin_options[]= array(
							'name' => 'enable-' . $dir_name,
							'label' => $plugin_info->name,
							'desc' => sprintf(__('Enable %s integration.', 'cf-geoplugin'), $plugin_info->name),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						);
						$plugin_info = NULL;
					}
				}
			}
			
			if(empty($plugin_options)){
				return $options;
			}
			
			return array_merge($options, array(
				// Tab
				array(
					'id' => 'plugin_support',
					'title' => __('Plugin Support', 'cf-geoplugin'),
					// Section
					'sections' => array(
						array(
							'id' => 'enable-plugins',
							'title' => __('Enable Plugins', 'cf-geoplugin'),
							'desc' => __('Allow Geo Controller to integrate with existing installed plugins.', 'cf-geoplugin'),
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
		public function cfgp_settings_default ($options =[]){
			$plugin_options = [];
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
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
		private function include_plugins( $options=[], $only_object = false ){
			if($only_object === false)
			{							
				$this->plugins = apply_filters('cfgp/plugins', $this->plugins);
				
				foreach($this->plugins as $dir_name=>$file_name)
				{
					$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
					if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
					{
						$class_name = str_replace(['-','.'], '_', $dir_name);
						$plugin_class = "CFGP__Plugin__{$class_name}";
						
						if(CFGP_Options::get('enable-' . $dir_name, 0))
						{
							if(class_exists($plugin_class) && method_exists($plugin_class, 'instance')) {
								$plugin_class::instance();
							} else {
								CFGP_U::include_once($addon);
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
		public static function instance($options = [], $only_object = false ) {
			$class = self::class;
			$instance = CFGP_Cache::get($class);
			if ( !$instance ) {
				$instance = CFGP_Cache::set($class, new self($options, $only_object));
			}
			return $instance;
		}
	}
endif;