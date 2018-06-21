<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.2.1
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * Register all metabox inside posts.
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
 class CF_Geoplugin_Metabox
{
 
    /**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      CF_Geoplugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $cf_geoplugin    The string used to uniquely identify this plugin.
	 */
	protected $cf_geoplugin;
	
	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $prefix
	 */
	protected $prefix;
	protected $metabox;
	protected $defender=false;
	
	
	function __construct(){
		if ( is_admin() )
		{
			$this->cf_geoplugin	= CFGP_NAME;
			$this->version 		= CFGP_VERSION;
			$this->prefix	 	= CFGP_PREFIX;
			$this->metabox		= CFGP_METABOX;
			
			$encrypt = new CF_Geoplugin_Defender;
			$this->defender = $encrypt->enable;
			
            add_action( 'init', array($this, 'init_metabox'), 9999 );
			add_filter( 'cfgeo_meta_boxes', array($this, 'metaboxes') );
        }
		else
		{
			add_action('template_redirect', array($this, 'redirect'));
		}
 
    }
	public function metaboxes(array $meta_boxes){
		$prefix = $this->metabox;
		$meta_boxes=array();
		
		
		$all_countries = cf_geo_get_terms(array(
			'taxonomy'		=> 'cf-geoplugin-country',
			'hide_empty'	=> false
		));
		$countries=array();
		$countries[]=array(
			'value'	=>	'',
			'name'	=>	__('Choose country...',CFGP_NAME),
		);
		if(is_array( $all_countries ) && count($all_countries)>0)
		{
			//$cf_geo_block_country = CF_Geoplugin_Metabox_GET('country', $post->ID, true);
			//$find_current = array_map("trim",explode(",",$cf_geo_block_country));
			foreach($all_countries as $i=>$country)
			{
				$countries[]=array(
					'value'	=>	$country->slug,
					'name'	=>	$country->name. ' - ' .$country->description,
				);
			}
		}
		
		$http = array();
		
		
		
		foreach(array(
		301 => __('Moved Permanently', CFGP_NAME),
		302 => __('Moved Temporary', CFGP_NAME),
		303 => __('See Other', CFGP_NAME),
		404 => __('Not Found (not recommended)', CFGP_NAME),
		) as $status_code=>$status_message)
		{
			$http[]=array(
				'value'	=>	$status_code,
				'name'	=>	$status_code. ' ' .$status_message,
			);
		}
		
		$meta_boxes[] = array(
			'id'         => 'cf_geoplugin_seo_redirection',
			'title'      => __('Country SEO Redirection',CFGP_NAME),
			'pages'      => get_post_types(),
			'context'    => 'side',
			'priority'   => 'high',
			'show_names' => false,
			'desc' => __( 'Here you can safe redirect this page to another page using geolocation.', CFGP_NAME ),
			'fields' => array(
				array(
					'name' => __('Select Country',CFGP_NAME),
					'desc' => __('Select the country you want to redirect.',CFGP_NAME),
					'default' => '',
					'id' => $prefix . 'country',
					'type' => 'select',
					'options'=>$countries,
				),
				array(
					'name' => __('Redirect URL',CFGP_NAME),
					'desc' => __('URL where you want to redirect',CFGP_NAME),
					'default' => '',
					'id' => $prefix . 'url',
					'type' => 'text'
				),
				array(
					'name' => __('HTTP Code',CFGP_NAME),
					'desc' => __('Select the desired HTTP redirection (HTTP CODE 302 is recommended)',CFGP_NAME),
					'default' => '302',
					'id' => $prefix . 'status_code',
					'type' => 'select',
					'options'=>$http,
				),
				array(
					'name'    => __('Enable SEO Redirection',CFGP_NAME),
					'id'      => $prefix . 'enable_seo',
					'type'    => 'radio_inline',
					'default' => 'false',
					'desc'	  => '',
					'options' => array(
						'true' => __( 'Enabled', CFGP_NAME ),
						'false'   => __( 'Disabled', CFGP_NAME )
					),
				),
			)
		);
		
		return $meta_boxes;
	}
	
	/**
     * Meta box initialization.
     */
    public function init_metabox() {
        if ( ! class_exists( 'cfGeo_Meta_Box' ) )
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/metabox/init.php';
    }
	
	public function redirect(){
		$response = array(
			'geo' => CF_Geoplugin_Metabox_GET('country', false, true),
			'url' => CF_Geoplugin_Metabox_GET('url', false, true),
			'status_code' => CF_Geoplugin_Metabox_GET('status_code', false, true),
		);
		
		$find_current = array_map("strtolower",array_map("trim",explode(",",$response['geo'])));
		
		if(
			in_array($response['status_code'], array(301,302,303,404)) 
			&& (filter_var($response['url'], FILTER_VALIDATE_URL) !== false)
			&& in_array(strtolower(do_shortcode('[cf_geo return="country_code"]')), $find_current)
		)
		{
			wp_redirect($response['url'], $response['status_code']);
			exit;
		}
	}
	
	private function load_banner_taxonomy(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-data.php';
		$install = new CF_Geoplugin_Data();
		
		$install->install_country_terms("country-list","cf-geoplugin-country");
	}
}