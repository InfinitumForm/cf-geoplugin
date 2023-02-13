<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Gravity Forms integrations
 *
 * @since      8.4.2
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__gravityforms' ) ):
class CFGP__Plugin__gravityforms extends CFGP_Global{
	
	private function __construct() {
		$this->add_action( 'gform_enqueue_scripts', 'enqueue_scripts', 100, 2 );
		$this->add_action( 'gform_register_init_scripts', 'register_init_scripts', 10, 3 );
		
		$this->add_filter( 'gform_ip_address', 'gform_ip_address', 1, 10 );
		$this->add_action( 'plugins_loaded', 'add_custom_fields', 0, 10 );
		
		$this->add_action( 'wp_ajax_cfgp_gfield_autocomplete_location', 'ajax_autocomplete_locations', 0, 10 );
		$this->add_action( 'wp_ajax_nopriv_cfgp_gfield_autocomplete_location', 'ajax_autocomplete_locations', 0, 10 );
	}
	
	/*
	 * Enqueue Scripts
	 * @verson    1.0.0
	 */
	private $enqueue_scripts;
	public function enqueue_scripts( $form, $is_ajax ) {
		if( !$this->enqueue_scripts ) {
			wp_register_script(
				CFGP_NAME . '-gform-cfgp',
				CFGP_URL . '/inc/plugins/gravityforms/js/gravityforms.js',
				array('jquery'),
				(string)CFGP_VERSION,
				true
			);
		
		
			wp_localize_script(CFGP_NAME . '-gform-cfgp', 'CFGP_GFORM', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => [
					'cfgp_gfield_autocomplete_location' => wp_create_nonce('cfgp-gfield-autocomplete-location')
				],
				'label' => [
					'please_wait' => esc_attr__('Please Wait...', 'cf-geoplugin')
				]
			));
			$this->enqueue_scripts = true;
		
		
			wp_register_style(
				CFGP_NAME . '-gform-cfgp',
				CFGP_URL . '/inc/plugins/gravityforms/css/gravityforms.css',
				array('gform_basic'),
				(string)CFGP_VERSION
			);
		}
	}
	
	/*
	 * Register Init Scripts
	 * @verson    1.0.0
	 */
	public function register_init_scripts( $form, $is_ajax ) {
		wp_enqueue_script(CFGP_NAME . '-gform-cfgp');
		wp_enqueue_style(CFGP_NAME . '-gform-cfgp');
	}
	
	/* 
	 * Replace gform_ip_address 
	 * @verson    1.0.0
	 */
	public function gform_ip_address ( $ip ) {
		return CFGP_IP::get();
	}
	
	/* 
	 * Add custom fields 
	 * @verson    1.0.0
	 */
	public function add_custom_fields ( ) {
		/* 
		 * Add country selection field 
		 * @verson    1.0.0
		 */
		include_once __DIR__ . '/custom-fields/gf-country-region-city.php';
		GF_Fields::register(new CFGP__Plugin__gravityforms__GF_Country_Region_City());
		/* 
		 * Add country selection field 
		 * @verson    1.0.0
		 */
		include_once __DIR__ . '/custom-fields/gf-country.php';
		GF_Fields::register(new CFGP__Plugin__gravityforms__GF_Country());
	}
	
	/* 
	 * AJAX: Autocomplete locations 
	 * @verson    1.0.0
	 */
	public function ajax_autocomplete_locations() {
		if( !wp_verify_nonce( $_POST['nonce'], 'cfgp-gfield-autocomplete-location' ) ) {
			wp_send_json_error([
				'message' => esc_attr__('The connection you requested has timed out. Please refresh the page and try again.', 'cf-geoplugin')
			]); exit;
		}
		
		$country_code = sanitize_text_field( $_POST['country_code'] ?? '' );
		
		if( empty($country_code) ) {
			wp_send_json_error([
				'message' => esc_attr__('Country code is not defined.', 'cf-geoplugin')
			]); exit;
		}
		
		wp_send_json_success([
			'regions' => CFGP_Library::get_regions($country_code),
			'cities' => CFGP_Library::get_cities($country_code)
		]); exit;
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