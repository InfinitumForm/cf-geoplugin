<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/public
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $cf_geoplugin    The ID of this plugin.
	 */
	private $cf_geoplugin;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $prefix
	 */
	protected $prefix;
	
	/**
	 * Detect Proxy
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $proxy
	 */
	protected $proxy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 * @param      string    $cf_geoplugin       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cf_geoplugin, $version, $prefix, $proxy ) {

		$this->cf_geoplugin = $cf_geoplugin;
		$this->version 		= $version;
		$this->prefix	 	= $prefix;
		$this->proxy		= $proxy;
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CF_Geoplugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CF_Geoplugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class. 
		 */
		wp_enqueue_style(
			$this->cf_geoplugin.'-flag',
			plugin_dir_url( __FILE__ ) . 'css/flag-icon.min.css',
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style( $this->cf_geoplugin, plugin_dir_url( __FILE__ ) . 'css/cf-geoplugin.css', array($this->cf_geoplugin.'-flag'), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CF_Geoplugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CF_Geoplugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->cf_geoplugin, plugin_dir_url( __FILE__ ) . 'js/cf-geoplugin.js', array( 'jquery' ), $this->version, false );

	}

}
