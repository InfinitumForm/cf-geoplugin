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
			'woocommerce' => 'woocommerce'
		);
		
		function __construct( $options=array(), $only_object = false ) {
			$this->include_plugins($options, $only_object);
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

						if(class_exists($plugin_class) && method_exists($plugin_class, 'run')) {
							$plugin_class::run();
						} else {
							include_once $addon;
							if(class_exists($plugin_class) && method_exists($plugin_class, 'run')) {
								$plugin_class::run();
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