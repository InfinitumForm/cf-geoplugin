<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Monarch integration
 *
 * @since      7.6.7
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */



if( !class_exists( 'CF_Geoplugin_Elementor' ) ):
class CF_Geoplugin_Elementor extends CF_Geoplugin_Global{
	
	const VERSION = '1.0.1';
	const MINIMUM_ELEMENTOR_VERSION = '3.0';
	const MINIMUM_PHP_VERSION = '5.6';
	
	private static $_instance = null;
	
	function __construct(){
		$this->add_action( 'plugins_loaded', 'init' );
		$this->add_filter( 'single_template', 'add_custom_single_template', 99 );
	}
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}
	
	public function init() {
		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			$this->add_action( 'admin_notices', 'admin_notice_minimum_elementor_version' );
			return;
		}
		
		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			$this->add_action( 'admin_notices', 'admin_notice_minimum_php_version' );
			return;
		}

		$this->add_action( 'elementor/elements/categories_registered', 'add_categories' );
		$this->add_action( 'elementor/widgets/widgets_registered', 'load_widgets');
		/* UNDERCONSTRUCTION */
	//	$this->add_action( 'elementor/controls/controls_registered', 'add_controls' );
	}
	
	function add_custom_single_template( $template ) {
		if( parent::get_post_type('cf-geoplugin-banner') && file_exists(__DIR__ . '/page/geo-banner.php') ){
			$template = __DIR__ . '/page/geo-banner.php';
		}
		return $template;
	}
	
	function is_editor(){
		return ((isset($_REQUEST['action']) && isset($_REQUEST['post']) && $_REQUEST['action'] == 'elementor' && absint($_REQUEST['post']) > 0) || isset($_GET['elementor-preview']));
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
	
	function add_controls(){
		$controls = __DIR__ . '/controls';
		$fileSystemIterator = new FilesystemIterator($controls);
		foreach ($fileSystemIterator as $control_file)
		{
			// Find all controls
			$filename = $control_file->getFilename();
			if(preg_match('~cf-geoplugin-elementor-(.*?)-control~i', $filename))
			{
				// Generate widget file path
				$file = "{$controls}/{$filename}";
				// Load widget
				if(file_exists($file))
				{
					include_once $file;
					// Translate class name
					$class_name = str_replace('.php', '', $filename);
					$class_name = explode('-', $class_name);
					$class_name = array_map('trim', $class_name);
					$class_name = array_map('ucfirst', $class_name);
					$class_name[0] = strtoupper($class_name[0]);
					$class_name = join('_', $class_name);
					// Include class
					if(class_exists($class_name))
					{
						// Let Elementor know about our widget
						\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new $class_name() );
					}
				}
			}
		}
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
				if(file_exists($file))
				{
					// Load widget
					include_once $file;
					// Translate class name
					$class_name = str_replace('.php', '', $filename);
					$class_name = explode('-', $class_name);
					$class_name = array_map('trim', $class_name);
					$class_name = array_map('ucfirst', $class_name);
					$class_name[0] = strtoupper($class_name[0]);
					$class_name = join('_', $class_name);
					// Include class
					if(class_exists($class_name))
					{
						// Let Elementor know about our widget
						\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new $class_name() );
					}
				}
			}
		}
	}
	

	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'CF Geo Plugin', 'elementor-test-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-test-extension' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}
	
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'CF Geo Plugin', 'elementor-test-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'elementor-test-extension' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}
}
endif;