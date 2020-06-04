<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Monarch integration
 *
 * @since      7.6.7
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */

use Elementor\Plugin; 

if( !class_exists( 'CF_Geoplugin_Elementor' ) ):
class CF_Geoplugin_Elementor extends CF_Geoplugin_Global{
	function __construct(){
		$this->add_action( 'elementor/widgets/widgets_registered', 'load_widgets');
		$this->add_action( 'elementor/elements/categories_registered', 'add_categories' );
	}
	
	function add_categories( $elements_manager ) {

		$elements_manager->add_category(
			'cf-geoplugin',
			[
				'title' => __( 'CF Geo Plugin', CFGP_NAME ),
				'icon' => 'fa fa-map-marker',
			]
		);

	}
	
	function load_widgets(){
		$widgets = __DIR__ . '/widgets';
		$fileSystemIterator = new FilesystemIterator($widgets);
		foreach ($fileSystemIterator as $widget_file)
		{
			// Find all widgets
			$filename = $widget_file->getFilename();
			if(preg_match('~cf-geoplugin-elementor-(.*?)-widget~i', $filename))
			{
				// Generate widget file path
				$file = "{$widgets}/{$filename}";
				// Load widget
				require_once $file;
				// Translate class name
				$class_name = str_replace('.php', '', $filename);
				$class_name = explode('-', $class_name);
				$class_name = array_map('trim', $class_name);
				$class_name = array_map('ucfirst', $class_name);
				$class_name[0] = strtoupper($class_name[0]);
				$class = join('_', $class_name);
				// Include class
				if(class_exists($class))
				{
					$register_widget = new $class;
					// Let Elementor know about our widget
					Plugin::instance()->widgets_manager->register_widget_type( $register_widget );
				}
			}
		}
	}
}
endif;