<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Elementor integration
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__elementor', false ) ):
class CFGP__Plugin__elementor extends CFGP_Global
{
	// Current plugin version
	const VERSION = '1.0.2';
	// Minimum required Elementor version
	const MINIMUM_ELEMENTOR_VERSION = '3.3.0';
	// Minimum required PHP version
	const MINIMUM_PHP_VERSION = '7.0.0';
	
	/* 
	 * Construct
	 * @verson    1.0.0
	 */
	private function __construct()
    {
		$this->add_action( 'plugins_loaded', 'init', 10, 0 );
	}
	
	/* 
	 * Initialize this addon after plugins loaded
	 * @verson    1.0.0
	 */
	public function init () {
		// Check for required Elementor version
		if ( defined('ELEMENTOR_VERSION') && ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			$this->add_action( 'admin_notices', 'admin_notice_minimum_elementor_version' );
			return;
		}
		
		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' )) {
			$this->add_action( 'admin_notices', 'admin_notice_minimum_php_version' );
			return;
		}
		
		$this->add_action( 'elementor/elements/categories_registered', 'add_categories', 10, 1 );
		$this->add_action( 'elementor/widgets/widgets_registered', 'load_widgets', 10, 0 );
		
		/* UNDERCONSTRUCTION */
		//	$this->add_action( 'elementor/controls/controls_registered', 'add_controls', 10, 0 );
	}
	
	/* 
	 * Register elementor categories
	 * @verson    1.0.0
	 */
	public function add_categories( $elements_manager ) {

		$elements_manager->add_category(
			'cf-geoplugin',
			[
				'title' => __( 'Geo Controller', 'cf-geoplugin'),
				'icon' => 'cfa cfa-map-marker',
			]
		);
	}
	
	/* 
	 * Load elementor widgets
	 * @verson    1.0.0
	 */
	public function load_widgets(){
		$widgets = __DIR__ . '/widgets';

		if(!file_exists($widgets)) return;
		
		$fileSystemIterator = new FilesystemIterator($widgets);
		foreach ($fileSystemIterator as $widget_file)
		{
			// Find all widgets
			$filename = $widget_file->getFilename();
			if(preg_match('~cfgp-elementor-(.*?)-widget~i', $filename))
			{
				// Generate widget file path
				$file = "{$widgets}/{$filename}";
				if(file_exists($file))
				{
					// Load widget
					CFGP_U::include_once($file);
					// Translate class name
					$class_name = str_replace('.php', '', $filename);
					$class_name = explode('-', $class_name);
					$class_name = array_map('trim', $class_name);
					$class_name = array_map('ucfirst', $class_name);
					$class_name[0] = strtoupper($class_name[0]);
					$class_name = join('_', $class_name);
					// Include class
					
					if(class_exists($class_name, false))
					{
						// Let Elementor know about our widget
						\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new $class_name() );
					}
				}
			}
		}
	}
	
	/* 
	 * Load elementor controls
	 * @verson    1.0.0
	 */
	public function add_controls(){
		$controls = __DIR__ . '/controls';
		
		if(!file_exists($widgets)) return;
		
		$fileSystemIterator = new FilesystemIterator($controls);
		foreach ($fileSystemIterator as $control_file)
		{
			// Find all controls
			$filename = $control_file->getFilename();
			if(preg_match('~cfgp-elementor-(.*?)-control~i', $filename))
			{
				// Generate widget file path
				$file = "{$controls}/{$filename}";
				// Load widget
				if(file_exists($file))
				{
					CFGP_U::include_once($file);
					// Translate class name
					$class_name = str_replace('.php', '', $filename);
					$class_name = explode('-', $class_name);
					$class_name = array_map('trim', $class_name);
					$class_name = array_map('ucfirst', $class_name);
					$class_name[0] = strtoupper($class_name[0]);
					$class_name = join('_', $class_name);
					// Include class
					if(class_exists($class_name, false))
					{
						// Let Elementor know about our widget
						\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new $class_name() );
					}
				}
			}
		}
	}
	
	/* 
	 * Check is in the editor mode
	 * @verson    1.0.0
	 */
	public static function is_editor(){
		return (\Elementor\Plugin::$instance->editor->is_edit_mode());
	}
	
	/* 
	 * Check is in the preview mode
	 * @verson    1.0.0
	 */
	public static function is_preview(){
		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}
	
	/* 
	 * Check for required Elementor version
	 * @verson    1.0.0
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '%1$s requires %2$s version %3$s or greater.', 'cf-geoplugin'),
			'<strong>' . esc_html__( 'Geo Controller', 'cf-geoplugin') . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'cf-geoplugin') . '</strong>',
			'<strong>' . self::MINIMUM_ELEMENTOR_VERSION . '</strong>'
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
	}
	
	/* 
	 * Check for required PHP version
	 * @verson    1.0.0
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '%1$s requires %2$s version %3$s or greater.', 'cf-geoplugin'),
			'<strong>' . esc_html__( 'Geo Controller', 'cf-geoplugin') . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'cf-geoplugin') . '</strong>',
			'<strong>' . self::MINIMUM_PHP_VERSION . '</strong>'
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
	}
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;