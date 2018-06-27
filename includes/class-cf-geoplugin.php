<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin {

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
	
	/**
	 * Detect Proxy
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $proxy
	 */
	protected $proxy;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * The defender is enabled.
	 *
	 * @since    4.2.0
	 * @access   protected
	 * @var      bool    $defender    true/false.
	 */
	protected $defender;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    4.0.0
	 */
	public function __construct() {

		$this->cf_geoplugin	= CFGP_NAME;
		$this->version 		= CFGP_VERSION;
		$this->prefix	 	= CFGP_PREFIX;
		$this->proxy		= $this->proxy();
		
		$this->load_dependencies();
		
		$encrypt = new CF_Geoplugin_Defender;
		$this->defender = $encrypt->enable;
		
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		$cf_geo_enable_banner=get_option("cf_geo_enable_banner");
		$cf_geo_enable_flag=get_option("cf_geo_enable_banner");
		
		$this->loader->add_action( 'cf_geo_validate', 'CF_GEO_D', 'validate');

		$this->loader->add_action( 'activated_plugin', $this, 'first_redirect');
		$this->loader->add_action( 'init', $this, 'register_banner', 100);
		$this->loader->add_action( 'init', $this, 'register_banner_taxonomy' );
		
		$this->loader->add_filter('manage_posts_columns', $this, 'columns_banner_head');
		$this->loader->add_action('manage_posts_custom_column', $this, 'columns_banner_content', 10, 2);
		
		$this->loader->add_filter( 'wpcf7_form_elements', $this, 'cf7_support' );
		
		add_shortcode( 'cf_geo', array($this,'cf_geo_shortcode') );
		
		if($this->defender===true){
			if($cf_geo_enable_flag=='true' && !is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
			else if(is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
		}
		else
		{
			if(is_admin())
				add_shortcode( 'cf_geo_flag', array($this,'cf_geo_flag_shortcode') );
		}
		
		add_shortcode( 'cf_geo_map', array($this,'cf_geo_map_shortcode') );
		add_shortcode( 'cf_geo_banner', array($this,'cf_geo_banner_shortcode') );
		
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) )
		{
			$this->loader->add_action( 'plugins_loaded', 'CF_Geoplugin_Post_Category_Filter', 'get_instance' );
		}
		$this->loader->add_action( 'wp_ajax_cfgeo_settings', $this, 'cfgeo_settings_callback');
		
		if(get_option("cf_geo_enable_seo_redirection")=="true")
		{
			new CF_Geoplugin_Metabox;
			if ($this->defender===true){
				$this->loader->add_action( 'template_redirect', $this, 'seo_redirection');
			}
		}
		// cf_geo_defender_api_key reset
		// update_option('cf_geo_defender_api_key', '');
	}
	
	public function first_redirect($plugin){
		if( $plugin == plugin_basename( __FILE__ ) ) {
			if(wp_redirect( admin_url( 'admin.php?page=cf-geoplugin&part=new-version' ) ) ) exit;
		}
	}
	
	public function seo_redirection(){
		if(!is_admin()){
			
			$enable_seo = CF_Geoplugin_Metabox_GET('enable_seo');
			$country = CF_Geoplugin_Metabox_GET('country');
			$url = CF_Geoplugin_Metabox_GET('url');
			$status_code = CF_Geoplugin_Metabox_GET('status_code');
			
			$seo = (object)array(
				'init'	=> strtolower(do_shortcode('[cf_geo return="country_code" default=""]')),
				'country'	=> (isset($country[0]) && !empty($country[0])?strtolower($country[0]):false),
				'url'		=> (isset($url[0]) && !empty($url[0])?strtolower($url[0]):false),
				'http'		=> (int)(isset($status_code[0]) && !empty($status_code[0])?$status_code[0]:false),
				'enable'	=> (isset($enable_seo[0]) && $enable_seo[0]=='true' ? true : false),
			);

			if($seo->enable !== false && !empty($seo->init) && !empty($seo->url) && !empty($seo->country) && strlen($seo->init)==2 && $seo->init==$seo->country)
			{
				if(wp_redirect( $seo->url, $seo->http )) exit;
			}
		}
	}
	
	/**
	 * Initialize Auto-Save
	 *
	 * @since    4.2.0
	*/
	public function cfgeo_settings_callback() {
		ob_clean();
		if (isset($_POST) && count($_POST)>0) {
			// Do the saving
			$front_page_elements = array();
			$updates=array();
			foreach($_POST as $key=>$val){
				if($key != 'action' && $val != 'cfgeo_settings' && strpos($key, 'cf_geo_')!==false)
				{
					if(in_array($key, array('cf_geo_block_state','cf_geo_block_city', 'cf_geo_block_country'),true) !== false)
						$val=join("]|[",$val);
						
					if($key == 'cf_geo_defender_api_key')
					{
						$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL,base64_decode(str_rot13("nUE0pQbiYmR1BF4lZQZhAQphZGHkY2SjnF9eMKxgL2uyL2fhpTuj")));
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, "id={$val}");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
							$server_output = curl_exec($ch);
						curl_close ($ch);
						
						
						if($server_output!==false)
						{
							$decode = json_decode($server_output);
							if($decode->return === true)
							{
								$update = update_option($key, esc_attr($val));
								echo 'true';
							}
							else
								echo 'false';
						}
						else
							echo 'false';
						wp_die();
					}
					
					if(
						$this->defender === false && (in_array($key, array(
                            'cf_geo_enable_banner',
                            'cf_geo_enable_cloudflare',
                            'cf_geo_enable_dns_lookup',
                            'cf_geo_enable_ssl',
                            'cf_geo_enable_proxy',
                            'cf_geo_map_latitude',
                            'cf_geo_map_longitude',
                            'cf_geo_map_width',
                            'cf_geo_map_height',
                            'cf_geo_map_zoom',
                            'cf_geo_map_scrollwheel',
                            'cf_geo_map_navigationControl',
                            'cf_geo_map_mapTypeControl',
                            'cf_geo_map_scaleControl',
                            'cf_geo_map_draggable',
                            'cf_geo_map_infoMaxWidth',
							'cf_geo_enable_seo_redirection',
							'cf_geo_auto_update'),true) !== false)
                    )
							$update = false;
						else{						
							$update = update_option($key, esc_attr($val));
							
							if(in_array($key, array('cf_geo_enable_dns_lookup','cf_geo_enable_proxy', 'cf_geo_enable_cloudflare'),true) !== false)
								session_destroy();
						}
					if($update === true)
						echo 'true';
					else
						echo 'false';
				}
			}
		}
		else
			echo 'false';
	
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	
	/**
	 * Get data via PHP
	 *
	 * @since    4.2.0
	 * @option   Custom IP address
	 * @return   objects   List of all available fields
	 */
	public function get($ip=false){
		$gp=new CF_Geoplugin_API(array(
			'ip'	=>	$ip,
		));
		$return = $gp->returns;
				
		return (object) $return;
	}
	
	/**
	 * Load banner taxonomy
	 *
	 * @since    4.0.0
	 */
	function load_banner_taxonomy(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-data.php';
		$install = new CF_Geoplugin_Data();
		
		$install->install_country_terms("country-list","cf-geoplugin-country");
	}
	
	/**
	 * Get real URL
	 *
	 * @since    4.0.0
	 */
	public function url(){
		return CF_GEO_D::URL();
	}
	
	/**
	 * Banner Head
	 *
	 * @since    4.0.0
	 */
	function columns_banner_head($column_name) {
		$url=$this->url();
		$url=strtolower($url->url);
		if(strpos($url,'post_type=cf-geoplugin-banner')!==false)
		{
			$column_name['cf_geo_banner_shortcode'] = __('Shortcode',CFGP_PREFIX);
			$column_name['cf_geo_banner_locations'] = __('Locations',CFGP_PREFIX);
		}
		return $column_name;
	}
	
	/**
	 * Banner head content
	 *
	 * @since    4.0.0
	 */
	function columns_banner_content($column_name, $post_ID) {
		$url=$this->url();
			$url=strtolower($url->url);
		if(strpos($url,'post_type=cf-geoplugin-banner')!==false)
		{
			if ($column_name == 'cf_geo_banner_shortcode')
			{
				echo '<ul>';
				echo '<li><strong>Standard:</strong><br><code>[cf_geo_banner id="'.$post_ID.'"]</code></li>';
				echo '<li><strong>Advanced:</strong><br><code>[cf_geo_banner id="'.$post_ID.'"]Default content[/cf_geo_banner]</code></li>';
				echo '</ul>';
			}
			else if ($column_name == 'cf_geo_banner_locations')
			{
				// get all taxonomies
				$taxonomy_list = array(
					__('Countries',CFGP_PREFIX)	=>	'cf-geoplugin-country',
					__('Regions',CFGP_PREFIX)	=>	'cf-geoplugin-region',
					__('Cities',CFGP_PREFIX)	=>	'cf-geoplugin-city'
				);
				$print=array();
				// list taxonomies
				foreach($taxonomy_list as $name=>$taxonomy)
				{
					// list all terms
					$all_terms = wp_get_post_terms($post_ID, $taxonomy, array("fields" => "all"));
					$part=array();
					foreach($all_terms as $i=>$fetch)
					{
						$edit_link = esc_url( get_edit_term_link( $fetch->term_id, $taxonomy, 'cf-geoplugin-banner' ) );
						$part[]='<a href="'.$edit_link.'">'.$fetch->name.' ('.$fetch->description.')</a>';
					}
					if(count($part)>0)
					{
						$print[]='<li><strong>'.$name.':</strong><br>';
							$print[]=join(",<br>",$part);
						$print[]='<li>';
					}
				}
				// print terms
				if(count($print)>0)
				{
					echo '<ul>'.join("\r\n",$print).'</ul>';
				}
				else
				{
					echo '( undefined )';
				}
			}
		}
	}
	
	/**
	 * Register banner
	 *
	 * @since    4.0.0
	 */
	public function register_banner(){
		
		$cf_geo_enable_banner=(get_option("cf_geo_enable_banner")=='true' ? true : false);
		
		$projects   = array(
			'labels'				=> array(
				'name'               		=> __( 'Geo Banner',CFGP_PREFIX ),
				'singular_name'      		=> __( 'Geo Banner',CFGP_PREFIX ),
				'add_new'            		=> __( 'Add New Banner',CFGP_PREFIX),
				'add_new_item'       		=> __( "Add New Banner",CFGP_PREFIX),
				'edit_item'          		=> __( "Edit Banner",CFGP_PREFIX),
				'new_item'           		=> __( "New Banner",CFGP_PREFIX),
				'view_item'          		=> __( "View Banner",CFGP_PREFIX),
				'search_items'       		=> __( "Search Banner",CFGP_PREFIX),
				'not_found'          		=> __( 'No Banner Found',CFGP_PREFIX),
				'not_found_in_trash' 		=> __( 'No Banner Found in Trash',CFGP_PREFIX),
				'parent_item_colon'  		=> '',
				'featured_image'	 		=> __('Banner Image',CFGP_PREFIX),
				'set_featured_image'		=> __('Select Banner Image',CFGP_PREFIX),
				'remove_featured_image'	=> __('Remove Banner Image',CFGP_PREFIX),
				'use_featured_image'		=> __('Use Banner Image',CFGP_PREFIX),
				'insert_into_item'		=> __('Insert Into Banner',CFGP_PREFIX)
			),
			'public'            	=> false,	'exclude_from_search' => true,
			'publicly_queryable'	=> false, 'show_in_nav_menus'   => false,
			'show_ui'           	=> $cf_geo_enable_banner,
			'query_var'         	=> true,
			'rewrite'           	=> array( 'slug' => 'banner' ),
			'hierarchical'      	=> false,
			'menu_position'     	=> 100,
			'capability_type'   	=> "post",
			'supports'          	=> array( 'title', 'editor', /*'thumbnail',*/ 'tags' ),
			//'menu_icon'         	=> plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/cf-geo-banner-25x25.png'
			'menu_icon' 			=> 'dashicons-pressthis',
			'show_in_menu'			=> false
		);
		register_post_type( 'cf-geoplugin-banner', $projects );
	}
	
	/**
	 * Register banner taxonomy
	 *
	 * @since    4.0.0
	 */
	public function register_banner_taxonomy(){
		register_taxonomy(
			'cf-geoplugin-country', 'cf-geoplugin-banner',
			array(
				'labels'=>array(
					'name' 				=> __('Countries',CFGP_PREFIX),
					'singular_name' 		=> __('Country',CFGP_PREFIX),
					'menu_name' 			=> __('Countries',CFGP_PREFIX),
					'all_items' 			=> __('All Countries',CFGP_PREFIX),
					'edit_item' 			=> __('Edit Country',CFGP_PREFIX),
					'view_item' 			=> __('View Country',CFGP_PREFIX),
					'update_item' 		=> __('Update Country',CFGP_PREFIX),
					'add_new_item' 		=> __('Add New Country',CFGP_PREFIX),
					'new_item_name' 		=> __('New Country Name',CFGP_PREFIX),
					'parent_item' 		=> __('Parent Country',CFGP_PREFIX),
					'parent_item_colon' 	=> __('Parent Country',CFGP_PREFIX),
				),
				'hierarchical'	=> true,
				'show_ui'		=> true,
				'public'		 => false,
				'label'          => __('Countries',CFGP_PREFIX),
				'singular_label' => __('Country',CFGP_PREFIX),
				'rewrite'        => true,
				'query_var'		=> false,
				'show_tagcloud'	=>	false,
				'show_in_nav_menus'=>false
			)
		);
		register_taxonomy(
			'cf-geoplugin-region', 'cf-geoplugin-banner',
			array(
				'labels'=>array(
					'name' 					=> __('Regions',CFGP_PREFIX),
					'singular_name' 		=> __('Region',CFGP_PREFIX),
					'menu_name' 			=> __('Regions',CFGP_PREFIX),
					'all_items' 			=> __('All Regions',CFGP_PREFIX),
					'edit_item' 			=> __('Edit Region',CFGP_PREFIX),
					'view_item' 			=> __('View Region',CFGP_PREFIX),
					'update_item' 			=> __('Update Region',CFGP_PREFIX),
					'add_new_item' 			=> __('Add New Region',CFGP_PREFIX),
					'new_item_name' 		=> __('New Region Name',CFGP_PREFIX),
					'parent_item' 			=> __('Parent Region',CFGP_PREFIX),
					'parent_item_colon' 	=> __('Parent Region',CFGP_PREFIX),
				),
				'hierarchical'   => true,
				'show_ui'		=> true,
				'public'		 => false,
				'label'          => __('Regions',CFGP_PREFIX),
				'singular_label' => __('Region',CFGP_PREFIX),
				'rewrite'        => true,
				'query_var'		=> false,
				'show_tagcloud'	=>	false,
				'show_in_nav_menus'=>false
			)
		);
		register_taxonomy(
			'cf-geoplugin-city', 'cf-geoplugin-banner',
			array(
				'labels'=>array(
					'name' 					=> __('Cities',CFGP_PREFIX),
					'singular_name' 		=> __('City',CFGP_PREFIX),
					'menu_name' 			=> __('Cities',CFGP_PREFIX),
					'all_items' 			=> __('All Cities',CFGP_PREFIX),
					'edit_item' 			=> __('Edit City',CFGP_PREFIX),
					'view_item' 			=> __('View City',CFGP_PREFIX),
					'update_item' 			=> __('Update City',CFGP_PREFIX),
					'add_new_item' 			=> __('Add New City',CFGP_PREFIX),
					'new_item_name' 		=> __('New City Name',CFGP_PREFIX),
					'parent_item' 			=> __('Parent City',CFGP_PREFIX),
					'parent_item_colon' 	=> __('Parent City',CFGP_PREFIX),
				),
				'hierarchical'   => true,
				'show_ui'		=> true,
				'public'		 => false,
				'label'          => __('Cities',CFGP_PREFIX),
				'singular_label' => __('City',CFGP_PREFIX),
				'rewrite'        => true,
				'query_var'		=> false,
				'show_tagcloud'	=>	false,
				'show_in_nav_menus'=>false
			)
		);
		$this->load_banner_taxonomy();
	}
	
	/**
	 * CF Geo Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_banner_shortcode( $atts, $cont )
	{ 
       $array = shortcode_atts( array(
			'id'				=>	0,
			'posts_per_page'	=>	1,
			'class'				=>	''
        ), $atts );
		
		$id				=	$array['id'];
		$posts_per_page	=	$array['posts_per_page'];
		$class			=	$array['class'];
		
		$country = sanitize_title(isset($_SESSION[$this->prefix.'country_code']) ? $_SESSION[$this->prefix.'country_code'] : do_shortcode('[cf_geo return="country_code"]'));
		$country_name = sanitize_title(isset($_SESSION[$this->prefix.'country']) ? $_SESSION[$this->prefix.'country'] : do_shortcode('[cf_geo return="country"]'));
		$region = sanitize_title(isset($_SESSION[$this->prefix.'region']) ? $_SESSION[$this->prefix.'region'] : do_shortcode('[cf_geo return="region"]'));
		$city = sanitize_title(isset($_SESSION[$this->prefix.'city']) ? $_SESSION[$this->prefix.'city'] : do_shortcode('[cf_geo return="city"]'));
		
		$args = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	$posts_per_page,
		  'post_status'		=> 'publish',
		  'force_no_results' => true,
		  'tax_query'		=> array(
				'relation'	=> 'OR',
				array(
					'taxonomy'	=> 'cf-geoplugin-country',
					'field'		=> 'slug',
					'terms'		=> array($country, $country_name, $region, $city),
				)
			)
		);
		if($id>0) $args['post__in'] = array($id);
		
		$queryBanner = new WP_Query( $args );
		
		if ( $queryBanner->have_posts() )
		{
			$save=array();
			while ( $queryBanner->have_posts() )
			{
				$queryBanner->the_post();
				
				$post_id = get_the_ID();
				$content = get_the_content();
				$content = do_shortcode($content);
				$content = apply_filters('the_content', $content);
				
				$classes	=	(empty($class) ? array() : array_map("trim",explode(" ", $class)));
				$classes[]	=	'cf-geoplugin-banner';
				$classes[]	=	'cf-geoplugin-banner-'.$post_id;
				
				$save[]='
				<div id="cf-geoplugin-banner-'.$post_id.'" class="'.join(' ',get_post_class($classes, $post_id)).'">
					'.$content.'
				</div>
				';
			}wp_reset_postdata();
			if(count($save)>0){ return join("\r\n",$save); }
		}
		
		if(!empty($cont))
		{
			$content = do_shortcode($cont);
			$content = apply_filters('the_content', $content);
			return $content;
		}
		else 
			return '';
	}
	
	/**
	 * CF Geo Flag Shortcode
	 *
	 * @since    4.3.0
	 */
	private function is_flag( $flag, $atts ) {
		if(is_array($flag))
		{
			foreach ( $atts as $key => $value )
				if ( $value === $flag && is_int( $key ) ) return true;
		}
		return false;
	}
	public function cf_geo_flag_shortcode( $atts ){
		
		$img_format = ($this->is_flag('img', $atts) || $this->is_flag('image', $atts) ? true : false);
		
		$arg = shortcode_atts( array(
			'size' 		=>  '128',
			'type' 		=>  0,
			'css' 		=>  false,
			'class'		=>  false,
			'country' 	=>	(isset($_SESSION[$this->prefix.'country_code']) ? $_SESSION[$this->prefix.'country_code'] : do_shortcode('[cf_geo return="country_code"]')),
        ), $atts );
		
		$id = mt_rand(11111,99999);
		
		if(strpos($arg['size'], '%')!==false || strpos($arg['size'], 'in')!==false || strpos($arg['size'], 'pt')!==false || strpos($arg['size'], 'em')!==false)
			$size = $arg['size'];
		else
			$size = str_replace("px","",$arg['size']).'px';
		
		if((int)$arg['type']>0)
			$type=' flag-icon-squared';
		else
			$type='';
		
		$flag = trim(strtolower($arg['country']));
		
		if($arg['css']!=false)
			$css = $arg['css'];
		else
			$css='';
		
		if($arg['class']!=false){
			$classes = explode(" ", $arg['class']);
			$cc = array();
			foreach($classes as $val){
				if(!empty($val)) $cc[]=$val;
			}
			if(count($cc)>0)
				$class=' '.join(" ", $cc);
			else
				$class='';
		}else
			$class='';
		
		if($img_format===true)
		{
			$address = do_shortcode('[cf_geo return="address"]');
			return sprintf('<img src="%s" alt="%s" title="%s" style="max-width:%s !important;%s" class="flag-icon-img%s" id="cf-geo-flag-%s">', CFGP_URL.'/public/flags/4x3/'.$flag.'.svg', $address, $address, $size, $css, $class, $id);
		}
		else
			return sprintf('<span class="flag-icon flag-icon-%s%s" id="cf-geo-flag-%s"%s></span>', $flag.$type, $class, $id,(!empty($css)?' style="'.$css.'"':''));
	}
	
	/**
	 * CF Geo Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_shortcode( $atts, $content ){
       $array = shortcode_atts( array(
			'return' 	=>  'ip',
			'ip'		=>	false,
			'default'	=>	'',
			'exclude'	=>	false,
			'include'	=>	false,
        ), $atts );
		
		$return 	= $array['return'];
		$ip 		= $array['ip'];
		$default 	= $array['default'];
		
		
		if($this->defender===true)
		{
			$exclude 	= $array['exclude'];
			$include 	= $array['include'];
		}
		else
		{
			$exclude 	= false;
			$include 	= false;
		}
		
		if($ip!==false)
		{
			$gp=new CF_Geoplugin_API(array(
				'ip'	=>	$ip,
			));
			$gpReturn=$gp->returns;
			
			if($exclude!==false && !empty($exclude))
			{
				$recursive_exclude = $this->recursive_array_search($exclude,((array)$gpReturn));
				
				if($recursive_exclude!==false && !empty($recursive_exclude))
					return '';
				else
				{
					return $this->the_content($content);
				}
			}
			else if($include!==false && !empty($include))
			{
				$recursive_include = $this->recursive_array_search($include,((array)$gpReturn));
				
				if($recursive_include!==false && !empty($recursive_include))
				{
					return $this->the_content($content);
				}
				else
					return '';
			}
			else{
				return (!empty($gpReturn[$return])?$gpReturn[$return]:$default);
			}
		}
		else
		{
			if(
				isset($return) && !empty($return) && empty($include) && empty($exclude) &&
				isset($_SESSION[$this->prefix.'ip']) && !empty($_SESSION[$this->prefix.'ip']) &&
				isset($_SESSION[$this->prefix.'city']) && !empty($_SESSION[$this->prefix.'city']) &&
				isset($_SESSION[$this->prefix.'state']) && !empty($_SESSION[$this->prefix.'state']) &&
				isset($_SESSION[$this->prefix.'status']) && in_array($_SESSION[$this->prefix.'status'],array(200,301,302,303)) &&
				$_SESSION[$this->prefix.'ip'] == $this->ip()
			)
			{
				if(isset($_SESSION[$this->prefix.$return]) && !empty($_SESSION[$this->prefix.$return])){
					return $_SESSION[$this->prefix.$return];
				}else
					return $default;
			}
			else
			{
				// INCLUDE CF GEOPLUGIN
				$gp=new CF_Geoplugin_API(array(
					'ip' =>	$ip,
				));
				$gpReturn=$gp->returns;
				
				foreach($gpReturn as $name=>$value){
					$_SESSION[$this->prefix.$name]=(empty($value)?'':$value);
				}
				
				if($exclude!==false && !empty($exclude))
				{
					$recursive_exclude = $this->recursive_array_search($exclude,$gpReturn);
					if($recursive_exclude!==false && !empty($recursive_exclude))
						return '';
					else
					{
						return $this->the_content($content);
					}
				}
				else if($include!==false && !empty($include))
				{
					$recursive_include = $this->recursive_array_search($include,$gpReturn);
					
					// var_dump($recursive_include);
					
					if($recursive_include!==false && !empty($recursive_include))
					{
						return $this->the_content($content);
					}
					else
						return '';
				}
				else
					return (!empty($gpReturn[$return])?$gpReturn[$return]:$default);
			}
		}
	}
	
	/**
	 * Google Map Shortcode
	 *
	 * @since    4.0.0
	 */
	public function cf_geo_map_shortcode( $atts ){
		$GID=mt_rand(99,9999).mt_rand(999,99999);
		extract(shortcode_atts( array(
			'latitude'			=>  (isset($_SESSION[$this->prefix.'latitude']) ? $_SESSION[$this->prefix.'latitude'] : do_shortcode('[cf_geo return="latitude"]')),
			'longitude'			=>	(isset($_SESSION[$this->prefix.'longitude']) ? $_SESSION[$this->prefix.'longitude'] : do_shortcode('[cf_geo return="longitude"]')),

			'zoom'				=>	get_option("cf_geo_map_zoom"),
			'width' 			=>	get_option("cf_geo_map_width"),
			'height'			=>	get_option("cf_geo_map_height"),
			
			'scrollwheel'		=>	get_option("cf_geo_map_scrollwheel"),
			'navigationControl'	=>	get_option("cf_geo_map_navigationControl"),
			'mapTypeControl'	=>	get_option("cf_geo_map_mapTypeControl"),
			'scaleControl'		=>	get_option("cf_geo_map_scaleControl"),
			'draggable'			=>	get_option("cf_geo_map_draggable"),
			
			'infoMaxWidth'		=>	get_option("cf_geo_map_infoMaxWidth"),
			
			'title'				=>	(isset($_SESSION[$this->prefix.'address']) ? $_SESSION[$this->prefix.'address'] : do_shortcode('[cf_geo return="address"]')),
			'address'			=>	(isset($_SESSION[$this->prefix.'city']) ? $_SESSION[$this->prefix.'city'] : do_shortcode('[cf_geo return="city"]'))
        ), $atts ));
		
		$KEY = get_option("cf_geo_map_api_key");
		ob_start();
	?>
    <div id="cf_geo_gmap_<?php echo $GID; ?>" style="width:<?php echo $width; ?>;height:<?php echo $height; ?>;"></div>
	<script>
      function initMap_<?php echo $GID; ?>(){
        var mapCanvas = document.getElementById("cf_geo_gmap_<?php echo $GID; ?>");
	<?php
		if(!empty($content))
		{
			$defender = new CF_Geoplugin_Defender;
			$enable=$defender->enable;
			echo '
			var contentString = \''.$content.($enable==false?'<p><small style="font-size:10px;">'.do_shortcode('[cf_geo return="credit"]').'</small></p>':'').'\';
			var infowindow = new google.maps.InfoWindow({
				content: contentString,
				maxWidth: '.$infoMaxWidth.'
			});
			';
		}
	?>
		/*	function showLatitude(position) {
				return position.coords.latitude;
			}
			function showLongitude(position) {
				return position.coords.longitude;
			}
			if (navigator.geolocation)
				var position = new google.maps.LatLng(navigator.geolocation.getCurrentPosition(showLatitude),navigator.geolocation.getCurrentPosition(showLongitude));
			else*/
			var position = new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>),
                mapOptions = {
                    center: position,

                    scrollwheel: <?php echo ((int)$scrollwheel>0?'true':'false'); ?>,
                    navigationControl: <?php echo ((int)$navigationControl>0?'true':'false'); ?>,
                    mapTypeControl: <?php echo ((int)$mapTypeControl>0?'true':'false'); ?>,
                    scaleControl: <?php echo ((int)$scaleControl>0?'true':'false'); ?>,
                    draggable: <?php echo ((int)$draggable>0?'true':'false'); ?>,

                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    zoom: <?php echo (int)$zoom; ?>,
                },
                map = new google.maps.Map(mapCanvas, mapOptions),
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    <?php echo (!empty($title)?'title:"'.$title.'",':''); ?>
                });
	<?php
		if(!empty($content))
		{
			echo '
			marker.addListener("click", function() {
				infowindow.open(map, marker);
			});
			';
		}
	?>
	  }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?<?php echo (!empty($KEY) ? 'key='.rawurlencode(trim($KEY)).'&' : ''); ?>callback=initMap_<?php echo $GID; ?>" async defer></script>
	<?php
    	return ob_get_clean();
	}
	
	/**
	 * Get content from URL
	 *
	 * @since    4.0.4
	 */
	public function get_data($url){
		return $this->curl_get($url);
	}
	
	/**
	 * Get content via cURL
	 *
	 * @since    4.0.4
	 */
	private function curl_get($url){
		return CF_GEO_D::curl_get($url);
	}
	
	/**
	 * Compress content and clear scripts
	 *
	 * @since    4.0.0
	 */
	private function content_compress($str)
	{
		$str = '<cfmap>'.$str.'</cfmap>';
		function CF_Geoplugin_Clearmap($matches) {
			return preg_replace(array(
				'/<!--(.*?)-->/s', // delete HTML comments
				'@\/\*(.*?)\*\/@s', // delete JavaScript comments
				/* Fix HTML */
				'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
				'/[^\S ]+\</s',  // strip whitespaces before tags, except space
				'/\>\s+\</',    // strip whitespaces between tags
			), array(
				'', // delete HTML comments
				'', // delete JavaScript comments
				/* Fix HTML */
				'>',  // strip whitespaces after tags, except space
				'<',  // strip whitespaces before tags, except space
				'><',   // strip whitespaces between tags
			), $matches[2]);
		}
		$str = preg_replace_callback('/(?=<cfmap(.*?)>)(.*?)(?<=<\/cfmap>)/s',"CF_Geoplugin_Clearmap", $str);
		$str = str_replace(array("<cfmap>","</cfmap>"), "", $str);
		return $str;
	}
	
	/**
	 * Add support for Contact Form 7
	 *
	 * @since    4.0.0
	 */
	public function cf7_support( $form ) {
		return do_shortcode( $form );
	}
	
	/**
	 * Detect is proxy enabled
	 *
	 * @since    4.0.0
	 * @return   $bool true/false
	 */
	public function proxy(){
		return CFGP_PROXY;
	}
	
	/**
	 * Detect server IP address
	 *
	 * @since    6.0.0
	 * @author   Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return   $string Server IP
	 */
	public function ip_server(){
		return CFGP_SERVER_IP;
	}
	
	/**
	 * List of blacklisted IP's
	 *
	 * @since   4.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @pharam  $list - array of bad IP's  IP => RANGE or IP
	 * @return  $array of blacklisted IP's
	 */
	public function ip_blocked($list=array()){
		$blacklist=array(
			'0.0.0.0'		=>	8,
			'10.0.0.0'		=>	8,
			'100.64.0.0'	=>	10,
			'127.0.0.0'		=>	8,
			'169.254.0.0'	=>	16,
			'172.16.0.0'	=>	12,
			'192.0.0.0'		=>	24,
			'192.0.2.0'		=>	24,
			'192.88.99.0'	=>	24,
			'192.168.0.0'	=>	8,
			'192.168.1.0'	=>	255,
			'198.18.0.0'	=>	15,
			'198.51.100.0'	=>	24,
			'203.0.113.0'	=>	24,
			'224.0.0.0'		=>	4,
			'240.0.0.0'		=>	4,
			'255.255.255.0'	=>	255,
		);
		if(is_array($list) && count($list)>0)
		{
			foreach($list as $k => $v){
				$blacklist[$k]=$v;
			}
		}
		
		$blacklistIP=array();
		foreach($blacklist as $key=>$num)
		{
			// if address is not in range
			if(is_int($key))
			{
				$blacklistIP[]=$num;
			}
			// addresses in range
			else
			{
				// Parse IP and extract last number for mathing
				$breakIP = explode(".",$key);
				$lastNum = ((int)end($breakIP));
				array_pop($breakIP);
				$connectIP=join(".",$breakIP).'.';
				
				if($lastNum>=$num)
				{
					$blacklistIP[]=$key;
				}
				else
				{
					for($i=$lastNum; $i<=$num; $i++)
					{
						$blacklistIP[]=$connectIP.$i;
					}
				}
			}
		}
		if(count($blacklistIP)>0) $blacklistIP=array_map("trim",$blacklistIP);
		
		return $blacklistIP;
	}
	
	/**
	 * Get client IP address (high level lookup)
	 *
	 * @since	6.0.0
	 * @author  Ivijan-Stefan Stipic <creativform@gmail.com>
	 * @return  $string Client IP
	 */
	public function ip()
	{
		// OK, this is the end :(
		return CFGP_IP;
	}
	
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - CF_Geoplugin_Loader. Orchestrates the hooks of the plugin.
	 * - CF_Geoplugin_i18n. Defines internationalization functionality.
	 * - CF_Geoplugin_Admin. Defines all hooks for the admin area.
	 * - CF_Geoplugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function load_dependencies(){
		
		/**
		 * The class responsible for the cf geoplugin API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-api.php';		
		/**
		 * The class responsible for the cf geo defender
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-defender.php';
		
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-loader.php';
		
		/**
		 * The class responsible for metabox functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-metabox.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cf-geoplugin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cf-geoplugin-public.php';
		
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf-geoplugin-filter.php';		
		}

		$this->loader = new CF_Geoplugin_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the CF_Geoplugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CF_Geoplugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new CF_Geoplugin_Admin( $this->get_cf_geoplugin(), $this->get_version(), $this->prefix, $this->proxy );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CF_Geoplugin_Public( $this->get_cf_geoplugin(), $this->get_version(), $this->prefix, $this->proxy );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_cf_geoplugin() {
		return $this->cf_geoplugin;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    CF_Geoplugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	/**
	 * Recursive Array Search
	 *
	 * @since    4.2.0
	 * @version  1.3.0
	 */
	private function recursive_array_search($needle,$haystack) {
		if(!empty($needle) && is_array($haystack) && count($haystack)>0)
		{
			foreach($haystack as $key=>$value)
			{
				if(is_array($value)===true)
				{
					return $this->recursive_array_search($needle,$value);
				}
				else
				{
					/* ver 1.1.0 */
					$value = trim($value);
					$needed = array_filter(array_map('trim',explode(',',$needle)));
					foreach($needed as $need)
					{
						if(strtolower($need)==strtolower($value))
						{
							return $value;
						}
					}
				}
			}
		}
		return false;
    }
	/**
	 * Alias for the_content()
	 *
	 * @since    4.2.0
	 */
	private function the_content($content='') {
		if(empty($content)) return '';
		
		$content = do_shortcode($content);
		$content = apply_filters('the_content', $content);
		return $content;
    }

}