<?php
/**
 * Post Category Filter
 */

class CF_Geoplugin_Post_Category_Filter {
	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = CFGP_NAME;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      Post_Category_Filter
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     4.0.0
	 */
	private function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     4.0.0
	 *
	 * @return    Post_Category_Filter    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     4.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		if ( 'post' === $screen->base ) {
			wp_localize_script( $this->plugin_slug, 'cf_geoplugin_category_filter', $this->get_language_strings() );
		}
	}

	/**
	 * Get translation strings
	 *
	 * @since     4.0.0
	 *
	 * @return    array    Translatable strings
	 */
	public function get_language_strings() {
		return array(
			'placeholder' => __( 'Filter', $this->plugin_slug )
		);
	}

}
