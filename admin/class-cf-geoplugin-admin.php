<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
class CF_Geoplugin_Admin {

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
	 * The current folder path.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $path    The current folder path.
	 */
	private $path;
	
	
	private $defender=false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 * @param      string    $cf_geoplugin       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cf_geoplugin, $version, $prefix, $proxy ) {
		add_action( 'admin_head', array($this, 'cf_geoplugin_admin_script') );
		add_action( 'admin_head', array($this, 'cf_geoplugin_public_script') );
		add_action( 'wp_head', array($this, 'cf_geoplugin_public_script'),1,1);
		
		add_action('wp_ajax_cf_geo_rss_feed', array($this, 'cf_geo_rss_feed'));
		add_action('wp_ajax_nopriv_cf_geo_rss_feed', array($this, 'cf_geo_rss_feed'));
	
		$this->cf_geoplugin = $cf_geoplugin;
		$this->version 		= $version;
		$this->prefix	 	= $prefix;
		$this->proxy		= $proxy;
		$this->path			= plugin_dir_path( __FILE__ );
		$this->load_dependencies();
		new CF_Geoplugin_TinyMce_Shortcodes;
		new CF_Geoplugin_TinyMce_Banners;
		new CF_GeoPlugin_Admin_Actions;
		$encrypt = new CF_Geoplugin_Defender;
		$this->defender = $encrypt->enable;
	}
	
	
	public function cf_geo_rss_feed()
	{
		include CFGP_INCLUDES . '/class-xml-parse.php';
		$xml= new parseXML('https://cfgeoplugin.com/feed/', true);
		if(isset($xml->fetch) && isset($xml->fetch->channel) && isset($xml->fetch->channel->item) && count($xml->fetch->channel->item)>0)
		{
			$items = $xml->fetch->channel->item;
			$i = 0;
			foreach($items as $fetch)
			{
				if($i >= 5) continue;
				printf('<p><a href="%s" target="_blank"><h4>%s</h4></a>%s<br><small>~%s</small></p>',$fetch->link, $fetch->title, $fetch->description, date("F j, Y", strtotime($fetch->pubDate)));
				++$i;
			}
		}
		exit;
	}
	
	
	public function cf_geoplugin_admin_script(){ ?>
		<script type="text/javascript" >
		// <![CDATA[
			var CF_GEOPLUGIN = {
				url : window.location.href,
				host : window.location.hostname,
				protocol : window.location.protocol.replace(/\:/g,''),
				premium : <?php echo ($this->defender===true?'true':'false'); ?>,
				select_nothing_found : "<?php _e("Oops, nothing found!", $this->cf_geoplugin); ?>",
			};
		// ]]>
		</script><?php
	}
	
	public function cf_geoplugin_public_script(){ ?>
		<script type="text/javascript" >
		// <![CDATA[
			if(typeof cf == 'undefined')
				var cf = {};
			
			cf.geoplugin = {url : window.location.href, host : window.location.hostname, protocol : window.location.protocol.replace(/\:/g,''),<?php
				if(isset($_SESSION))
				{
					foreach($_SESSION as $name=>$value){
						$object = str_replace(CFGP_PREFIX, '', $name);
						if(strpos($name,CFGP_PREFIX)!==false && !in_array($object,array('continentCode','areaCode','dmaCode','timezoneName','currencySymbol','currencyConverter','status'))){
						
							if(is_array($value))
							{
								/*$arr = array();
								foreach($value as $k => $v){
									if(!is_numeric($k))
									{
										$arr[]=sprintf('%s:"%s"',$k,addslashes($v));
									}
								}
								printf('%s:"%s", ',$object,join(',', $arr));*/
							}
							else
							{
								printf('%s:"%s", ',$object,addslashes($value));
							}
						}
					}
				}
				else
				{
					do_shortcode('[cf_geo]');
					if(isset($_SESSION))
					{
						foreach($_SESSION as $name=>$value){
							$object = str_replace(CFGP_PREFIX, '', $name);
							if(strpos($name,CFGP_PREFIX)!==false && !in_array($object,array('continentCode','areaCode','dmaCode','timezoneName','currencySymbol','currencyConverter','status'))){
							
								if(is_array($value))
								{
									/*$arr = array();
									foreach($value as $k => $v){
										if(!is_numeric($k))
										{
											$arr[]=sprintf('%s:"%s"',$k,addslashes($v));
										}
									}
									printf('%s:"%s", ',$object,join(',', $arr));*/
								}
								else
								{
									printf('%s:"%s", ',$object,addslashes($value));
								}
							}
						}
					}
				}
			?>};
			window.cfgeo = cf.geoplugin;
		// ]]>
		</script><?php
	}
	
	/**
	 * Check if connection exists.
	 * @since    4.3.0
	 * @access   private
	 */
	private function is_connected($port=80)
	{
		$connected = @fsockopen("www.google.com", $port); 
		//website, port
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
		if($port===443)
				$is_conn = false; //action in connection failure
			else
				$is_conn = $this->is_connected(443); // check 443
		}
		return $is_conn;
	}
	
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function load_dependencies(){
		
		/**
		* Hack for earlier versions of WordPress to give plugin support
		* @version   1.0.0
		*/
		require_once $this->path . 'includes/cf-geoplugin-transitions.php';
		/**
		* wp-admin plugin navigation and actions
		* @version   1.0.0
		*/
		require_once $this->path . 'includes/cf-geoplugin-admin-actions.php';
		/**
		* TinyMce support and shortcde actions
		* @version   1.0.0
		*/
		require_once $this->path . 'includes/cf-geoplugin-tinymce.php';
	}
	/**
	 * Register the stylesheets for the admin area.
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
		 // https://use.fontawesome.com/305c735f60.css
		$is_connected = $this->is_connected();
		if($is_connected)
		{
			wp_enqueue_style(
				'font-awesome',
				'https://use.fontawesome.com/305c735f60.css',
				array(),
				$this->version,
				'all'
			);
		}
		else
		{
			if(!wp_style_is('font-awesome.min.css'))
				wp_enqueue_style(
					'font-awesome',
					plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css',
					array(),
					$this->version,
					'all'
				);
		}
		if(!wp_style_is('chosen.min.css'))
			wp_enqueue_style(
				'chosen',
				plugin_dir_url( __FILE__ ) . 'css/chosen.min.css',
				array(),
				'1.6.2',
				'all'
			);
		wp_enqueue_style(
			$this->cf_geoplugin.'-flag',
			CFGP_URL . '/public/css/flag-icon.min.css',
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style(
			$this->cf_geoplugin,
			plugin_dir_url( __FILE__ ) . 'css/cf-geoplugin-admin.css',
			array('font-awesome','chosen',$this->cf_geoplugin.'-flag'),
			$this->version,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
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
		
		$screen = get_current_screen();
		if(isset($_GET['post_type']) && $_GET['post_type']=='cf-geoplugin-banner' || $screen->post_type == 'cf-geoplugin-banner')
		wp_enqueue_script(
			$this->cf_geoplugin.'-category-filter',
			plugin_dir_url( __FILE__ ) . 'js/cg-geoplugin-category-filter.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		
		if(!wp_script_is('chosen.jquery.min.js'))
			wp_enqueue_script(
				'chosen',
				plugin_dir_url( __FILE__ ) . 'js/chosen.jquery.min.js',
				array( 'jquery' ),
				'1.6.2',
				true 
			);
		if(!wp_script_is('jquery.debounce.js'))	
			wp_enqueue_script(
				'debounce',
				plugin_dir_url( __FILE__ ) . 'js/jquery.debounce.js',
				array( 'jquery' ),
				'1.6.2',
				true 
			);
		wp_enqueue_script(
			$this->cf_geoplugin,
			plugin_dir_url( __FILE__ ) . 'js/cf-geoplugin-admin.js',
			array( 'chosen', 'jquery', 'debounce' ),
			$this->version,
			true 
		);
	}

}
