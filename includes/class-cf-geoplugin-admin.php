<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Admin Pages
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Admin')) :
class CF_Geoplugin_Admin extends CF_Geoplugin_Global
{
	// Main CF GeoPlugin Page
	function page_cf_geoplugin(){
		if(file_exists(CFGP_ADMIN . '/cf-geoplugin.php'))
			require_once CFGP_ADMIN . '/cf-geoplugin.php';
	}
	
	// Defender page
	function page_cf_geoplugin_defender(){
		if(file_exists(CFGP_ADMIN . '/defender.php'))
			require_once CFGP_ADMIN . '/defender.php';
	}
	
	// Google Map page
	function page_cf_geoplugin_google_map(){
		if(file_exists(CFGP_ADMIN . '/google-map.php'))
			require_once CFGP_ADMIN . '/google-map.php';
	}
	
	// Debug page
	function page_cf_geoplugin_debug(){
		if(file_exists(CFGP_ADMIN . '/debug.php'))
			require_once CFGP_ADMIN . '/debug.php';
	}
	
	// Settings page
	function page_cf_geoplugin_settings(){
		if(file_exists(CFGP_ADMIN . '/settings.php'))
			require_once CFGP_ADMIN . '/settings.php';
	}
	
	// License page
	function page_cf_geoplugin_license(){
		if(file_exists(CFGP_ADMIN . '/license.php'))
			require_once CFGP_ADMIN . '/license.php';
	}
	// SEO Redirection page
	function page_cf_geoplugin_seo_redirection()
	{
		if( file_exists( CFGP_ADMIN . '/seo-redirection.php' ) )
			require_once CFGP_ADMIN . '/seo-redirection.php';
	}
	
	// Set custom style
	public function custom_style(){?>
<style type="text/css">

</style>
    <?php }
	
	// Register Style
	public function register_style($page){
		if(!$this->limit_scripts($page)) return false;
		wp_register_style( CFGP_NAME . '-bootstrap-reboot', CFGP_ASSETS . '/css/bootstrap-reboot.min.css', array(), '4.1.1' );
		wp_enqueue_style( CFGP_NAME . '-bootstrap-reboot' );
		
		wp_register_style( CFGP_NAME . '-bootstrap', CFGP_ASSETS . '/css/bootstrap.min.css', array(CFGP_NAME . '-bootstrap-reboot'), '4.1.1' );
		wp_enqueue_style( CFGP_NAME . '-bootstrap' );
		
		wp_register_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap'), '4.7.0' );
		wp_enqueue_style( CFGP_NAME . '-fontawesome' );
		
		wp_register_style( CFGP_NAME . '-choosen-style', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-fontawesome'),  '1.8.7' );
		wp_enqueue_style( CFGP_NAME . '-choosen-style' );
		
		wp_register_style( CFGP_NAME . '-style', CFGP_ASSETS . '/css/cf-geoplugin.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-fontawesome', CFGP_NAME . '-choosen-style'), CFGP_VERSION );
		wp_enqueue_style( CFGP_NAME . '-style' );
		
		// $this->add_action('admin_head', 'custom_style', 10);
	}
	
	// This function is only called when our plugin's page loads!
    public function load_style(){
		$this->add_action( 'admin_enqueue_scripts', 'register_style' );
    }
	
	// Set custom Javascript
	public function custom_javascript(){ ?>

<script>
/* <![CDATA[ */
(function($){
		
}(jQuery || window.jQuery));
/* ]]> */
</script>

<?php }
	
	// Register Scripts
	public function register_javascripts($page){
		if(!$this->limit_scripts($page)) return false;
		wp_register_script(  CFGP_NAME . '-popper', CFGP_ASSETS . '/js/popper.min.js', array('jquery'), '4.1.1' );
		wp_enqueue_script(  CFGP_NAME . '-popper' );
		
		wp_register_script(  CFGP_NAME . '-bootstrap', CFGP_ASSETS . '/js/bootstrap.min.js', array('jquery', CFGP_NAME . '-popper'), '4.1.1' );
		wp_enqueue_script(  CFGP_NAME . '-bootstrap' );
		
		wp_register_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery', CFGP_NAME . '-popper', CFGP_NAME . '-bootstrap'), '1.8.7', true );
		wp_enqueue_script( CFGP_NAME . '-choosen' );
		
		wp_register_script(  CFGP_NAME . '-admin', CFGP_ASSETS . '/js/cfgeoplugin.js', array('jquery', CFGP_NAME . '-popper', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-choosen'), CFGP_VERSION, true );
		wp_enqueue_script(  CFGP_NAME . '-admin' );
		
		wp_localize_script(CFGP_NAME . '-admin', 'CFGP', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'adminurl' => admin_url('/'),
				'label' => array(
					'loading' => __('Loading...',CFGP_NAME),
					'not_found' => __('Not Found!',CFGP_NAME),
					'alert' => array(
						'close' => __('Close',CFGP_NAME)
					),
					'rss' => array(
						'no_news' => __('There are no news at the moment.',CFGP_NAME),
						'error' => __('ERROR! Can\'t load news feed.',CFGP_NAME)
					),
					'chosen' => array(
						'not_found' => __('Nothing found!',CFGP_NAME)
					),
					'settings' => array(
						'saved' => __('Option saved successfuly!',CFGP_NAME),
						'fail' => __('There was some unexpected system error. Changes not saved!',CFGP_NAME),
						'false' => __('Changes not saved for unexpected reasons. Try again!',CFGP_NAME),
						'error' => __('Option you provide not match to global variables. Permission denied!',CFGP_NAME)
					),
					'csv' => array(
						'saved' => __('Successfuly saved %d records.',CFGP_NAME),
						'fail' => __('Failed to add %d rows.',CFGP_NAME),
						'upload' =>	__('Upload CSV file.',CFGP_NAME),
						'filetype' =>	__('The file must be comma separated CSV type',CFGP_NAME),
						'exit' =>	__("Are you sure, you want to exit?\nChanges wont be saved!",CFGP_NAME),
						'delete' =>	__("Are you sure, you want to delete this redirection?",CFGP_NAME),
						'missing_url' => __("URL Missing. Please insert URL from your CSV file or choose file from the library.",CFGP_NAME),
					),
					'rest' => array(
						'delete' =>	__("Are you sure, you want to delete this access token?",CFGP_NAME),
						'error' =>	__("Can't delete access token because unexpected reasons.",CFGP_NAME),
					)
				)
			));
		
		// $this->add_action('admin_head', 'custom_javascript', 30);
	}

	// Register CPT and taxonomies scripts
	public function register_javascripts_ctp( $page )
	{
		if( !isset( $_GET['post_type'] ) ) return false;
		$page = $_GET['post_type'];

		if( !$this->limit_scripts( $page ) ) return false;

		wp_register_script( CFGP_NAME . '-cpt', CFGP_ASSETS . '/js/cf-geoplugin-cpt.js', array( 'jquery' ), CFGP_VERSION, true );
		wp_enqueue_script( CFGP_NAME . '-cpt' );
		wp_localize_script(CFGP_NAME . '-cpt', 'CFGP', array(
			'ajaxurl' => admin_url('admin-ajax.php') ,
			'label' => array(
				'loading' => __('Loading...',CFGP_NAME),
				'not_found' => __('Not Found!',CFGP_NAME),
				'placeholder' => __('Search',CFGP_NAME)
			)
		));
	}
	
	// This function is only called when our plugin's page loads!
    public function load_javascripts(){
		
		$this->add_action( 'admin_enqueue_scripts', 'register_javascripts' );
		$this->add_action( 'admin_enqueue_scripts', 'register_javascripts_ctp' );
    }
	
	// Create "CF Geo Plugin" Page
	public function add_cf_geoplugin() {
		global $CF_GEOPLUGIN_OPTIONS;
		add_menu_page(
			__('CF Geo Plugin',CFGP_NAME),
			__('CF Geo Plugin',CFGP_NAME),
			'manage_options',
			CFGP_NAME,
			array( &$this, 'page_cf_geoplugin' ),
			'dashicons-location-alt'
		);
		
		if($CF_GEOPLUGIN_OPTIONS['enable_gmap'])
        {
            add_submenu_page(
                CFGP_NAME,
                __('Google Map',CFGP_NAME),
                __('Google Map',CFGP_NAME),
                'manage_options',
                CFGP_NAME . '-google-map',
                array( &$this, 'page_cf_geoplugin_google_map' )
            );
        }
		
		if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) 
        {
			if($CF_GEOPLUGIN_OPTIONS['enable_defender'])
			{
				add_submenu_page(
					CFGP_NAME,
					__('Geo Defender',CFGP_NAME),
					__('Geo Defender',CFGP_NAME),
					'manage_options',
					CFGP_NAME . '-defender',
					array( &$this, 'page_cf_geoplugin_defender' )
				);
			}
			if($CF_GEOPLUGIN_OPTIONS['enable_banner'])
			{
				add_submenu_page(
					CFGP_NAME,
					__('Geo Banner',CFGP_NAME),
					__('Geo Banner',CFGP_NAME),
					'manage_options',
					'edit.php?post_type=' . CFGP_NAME . '-banner'
				);
					
			}
			if($CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
			{
				add_submenu_page(
					CFGP_NAME,
					__('SEO Redirection',CFGP_NAME),
					__('SEO Redirection',CFGP_NAME),
					'manage_options',
					CFGP_NAME . '-seo-redirection',
					array( $this, 'page_cf_geoplugin_seo_redirection' )
				);
			}
			add_submenu_page(
				CFGP_NAME,
				__('Countries',CFGP_NAME),
				__('Countries',CFGP_NAME),
				'manage_options',
				'edit-tags.php?taxonomy=' . CFGP_NAME . '-country&post_type=' . CFGP_NAME . '-banner'
			);
			add_submenu_page(
				CFGP_NAME,
				__('Regions',CFGP_NAME),
				__('Regions',CFGP_NAME),
				'manage_options',
				'edit-tags.php?taxonomy=' . CFGP_NAME . '-region&post_type=' . CFGP_NAME . '-banner'
			);
			add_submenu_page(
				CFGP_NAME,
				__('Cities',CFGP_NAME),
				__('Cities',CFGP_NAME),
				'manage_options',
				'edit-tags.php?taxonomy=' . CFGP_NAME . '-city&post_type=' . CFGP_NAME . '-banner'
			);
			add_submenu_page(
				CFGP_NAME,
				__('Debug Mode',CFGP_NAME),
				__('Debug Mode',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-debug',
				array( $this, 'page_cf_geoplugin_debug' )
			);
			add_submenu_page(
				CFGP_NAME,
				__('Settings',CFGP_NAME),
				__('Settings',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-settings',
				array( $this, 'page_cf_geoplugin_settings' )
			);
			
			if(!CFGP_ACTIVATED)
			{
				if(!get_option("cf_geo_defender_api_key")) {
					add_submenu_page(
						CFGP_NAME,
						__('Activate Unlimited',CFGP_NAME),
						'<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
						'manage_options',
						CFGP_NAME . '-activate',
						array( $this, 'page_cf_geoplugin_license' )
					);
				}
			}
		}
	}
	
	// WP Hidden links by plugin setting page
	public function plugin_setting_page( $links ) {
		$mylinks = array( '<a href="' . admin_url( 'admin.php?page=' . CFGP_NAME . '-settings' ) . '">Settings</a>', );
		return array_merge( $links, $mylinks );
	}
	
	public function limit_scripts($page){
		if(strpos($page, CFGP_NAME) !== false) return true;
		return false;
	}

	// Fix collapsing admin menu
	public function plugin_custom_menu_class()
	{
		global $menu;

		$show = false;
		if( isset( $_GET['post_type'] ) ) $show = $this->limit_scripts( $_GET['post_type'] ); // This will also check for taxonomies

		if( is_array( $menu ) && $show )
		{
			foreach( $menu as $key => $value )
			{
				if( $value[0] == 'CF Geo Plugin' )
				{
					$menu[$key][4] = 'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_cf-geoplugin menu-top-first wp-menu-open';
				}
			}
		}
	}

	// Add RSS fed from plugin site
	public function add_rss_feed()
	{ ?>
    <div class="card">
        <div class="card-header">
            <span class="fa fa-info"></span> Live News & info
        </div>
        <div class="card-body">
            <ul class="list-unstyled list-feed">
                <div class="inside<?php echo (isset($_SESSION[CFGP_PREFIX . 'rss']) ? ' rss-loaded' : ''); ?>" id="rss">
                	<?php
                    	if( isset($_SESSION[CFGP_PREFIX . 'rss']) ) :
							echo $_SESSION[CFGP_PREFIX . 'rss'];
						else :
					?>
                     <div style="text-align:center; padding:32px 0">
                         <i class="fa fa-circle-o-notch fa-spin fa-5x fa-fw"></i>
                         <span class="sr-only"><?php _e('Loading...',CFGP_NAME) ?></span>
                     </div>
                     <?php endif; ?>
                </div>
            </ul>
        </div>
    </div>
	<?php }
	
	/* 
	 * RSS FEED
	 * @since 7.0.0
	*/
	public function cf_geo_rss_feed()
	{		
		include CFGP_INCLUDES . '/class-cf-geoplugin-xml.php';
		$xml= new parseXML('https://cfgeoplugin.com/feed/', true);
		if(isset($xml->fetch) && isset($xml->fetch->channel) && isset($xml->fetch->channel->item) && count($xml->fetch->channel->item)>0)
		{
			$items = $xml->fetch->channel->item;
			$i = 0;
			$print = array();
			foreach($items as $fetch)
			{
				if( $i >= 5 ) continue;
				$print[]=sprintf(
					'<p><a href="%1$s" target="_blank" class="text-info"><h4 class="h5">%2$s</h4></a>%3$s<small>~%4$s</small></p>',
					$fetch->link,
					$fetch->title,
					$fetch->description,
					date("F j, Y", strtotime($fetch->pubDate))
				);
				++$i;
			}
			
			$print = join("\r\n", $print);
			$_SESSION[CFGP_PREFIX . 'rss'] = $print;
			echo $print;
		}
		exit;
	}
	
	/* 
	 * RSS FEED
	 * @since 7.0.0
	*/
	public function cf_geo_update_option()
	{
		$name = $this->post('name');
		if( $name === 'base_currency' )
		{
			foreach( $_SESSION as $key => $value )
			{
				if( strpos( $key, CFGP_NAME ) !== false )
				{
					unset( $_SESSION[$key] );
				}
			}
		}
		$value = $this->post('value');
		if(isset($this->default_options[$name]))
		{
			if($this->update_option($name, $value) !== false)
				echo 'true';
			else
				echo 'false';
		}
		else
		{
			echo 'error';
		}
		exit;
	}

	// Update SEO redirect params
	public function cf_geo_update_redirect()
	{
		if( !check_admin_referer( 'cf_geo_update_redirect', 'cf_geo_update_redirect_nonce' ) )
		{
			echo 'error';
			exit;
		}

		global $wpdb;
		$message = 'true';

		$data = array(
			'active'		=> $this->post( 'cf_geo_redirect_enable', 'int' ),
			'country'		=> $this->post( 'cf_geo_country'),
			'region'		=> $this->post( 'cf_geo_region' ),
			'city'			=> $this->post( 'cf_geo_city' ),
			'url'			=> $this->post( 'cf_geo_redirect_url', 'url' ),
			'http_code'		=> $this->post( 'cf_geo_http_code', 'int' ),
			'action'		=> $this->post( 'cf_geo_redirect_action' )
		);

		$table_name = self::TABLE['seo_redirection'];
		if( $data['action'] === 'add-new' )
		{
			$result = $wpdb->insert(
				$wpdb->prefix . $table_name,
				array(
					'country'		=> $data['country'],
					'region'		=> $data['region'],
					'city'			=> $data['city'],
					'url'			=> $data['url'],
					'http_code'		=> $data['http_code'],
					'active'		=> $data['active']
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d',
				)	
			);
			if( $result === false ) $message = 'false';
		}
		else
		{
			$result = $wpdb->update(
				$wpdb->prefix . $table_name,
				array(
					'country'		=> $data['country'],
					'region'		=> $data['region'],
					'city'			=> $data['city'],
					'url'			=> $data['url'],
					'http_code'		=> $data['http_code'],
					'active'		=> $data['active']
				),
				array(
					'id'	=> $data['action']
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d',
				),
				array(
					'%d'
				)
			);
			if( $result === false ) $message = 'false';
		}
		echo $message;
		exit;
	}
	
	// Admin bar
	function cf_geoplugin_admin_bar_menu() {
		global $CFGEO, $CF_GEOPLUGIN_OPTIONS, $wp_admin_bar;
		// GEOPLUGIN
		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME,
			'title' => '<span  style="
		float:left; width:25px !important; height:25px !important;
		margin-left: 5px !important; margin-top: 2px !important; background:url(\''.CFGP_ASSETS . '/images/cf-geo-25x25.png\') no-repeat center center / cover;"></span>',
			'href' => '',
			'meta'  => array( 'class' => CFGP_NAME . '-toolbar-page', 'title'=>sprintf(__("CF GeoPlugin ver.%s",CFGP_NAME),$CFGEO['version'])),
			'parent' => false,
		) );
		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME . '-helper',
			'title' => 'CF GeoPlugin',
			'href' => admin_url( 'admin.php?page=' . CFGP_NAME),
			'meta'  => array( 'class' => CFGP_NAME . '-toolbar-help-page' ),
			'parent' => CFGP_NAME,
		) );
		
		if($CF_GEOPLUGIN_OPTIONS['enable_gmap'])
		{
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-gmap',
				'title' => __('CF Google Map',CFGP_NAME),
				'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-google-map'),
				'meta'  => array( 'class' => CFGP_NAME . '-gmap-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );
		}
		
		if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) {

			if($CF_GEOPLUGIN_OPTIONS['enable_defender'])
			{
				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-defender',
					'title' => __('CF Geo Defender',CFGP_NAME),
					'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-defender'),
					'meta'  => array( 'class' => CFGP_NAME . '-defender-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-banner',
					'title' => __('CF Geo Banner',CFGP_NAME),
					'href' => get_admin_url() . 'edit.php?post_type=cf-geoplugin-banner',
					'meta'  => array( 'class' => CFGP_NAME . '-banner-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-seo-redirection',
					'title' => __('SEO Redirection',CFGP_NAME),
					'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-seo-redirection'),
					'meta'  => array( 'class' => CFGP_NAME . '-seo-redirection-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-countries',
					'title' => __('CF Geo Countries',CFGP_NAME),
					'href' => get_admin_url() . 'edit-tags.php?taxonomy=cf-geoplugin-country&post_type=cf-geoplugin-banner',
					'meta'  => array( 'class' => CFGP_NAME . '-countries-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-regions',
					'title' => __('CF Geo Regions',CFGP_NAME),
					'href' =>  get_admin_url() . 'edit-tags.php?taxonomy=cf-geoplugin-region&post_type=cf-geoplugin-banner',
					'meta'  => array( 'class' => CFGP_NAME . '-regions-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-cities',
					'title' => __('CF Geo Cities',CFGP_NAME),
					'href' => get_admin_url() . 'edit-tags.php?taxonomy=cf-geoplugin-city&post_type=cf-geoplugin-banner',
					'meta'  => array( 'class' => CFGP_NAME . '-city-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );
			}
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-debug',
				'title' => __('Debug Mode',CFGP_NAME),
				'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-debug'),
				'meta'  => array( 'class' => CFGP_NAME . '-debug-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-setup',
				'title' => __('Settings',CFGP_NAME),
				'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-settings'),
				'meta'  => array( 'class' => CFGP_NAME . '-setup-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );

			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-activate',
				'title' => __('Activate Unlimited',CFGP_NAME),
				'href' => admin_url( 'admin.php?page=' . CFGP_NAME . '-activate'),
				'meta'  => array( 'class' => CFGP_NAME . '-activate-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );
		}
		
		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME . '-devider',
			'title' => '<span style="text-align:center; display:block;width:100%;">------------ '.__("Info",CFGP_NAME).' ------------</span>',
			'href' => '',
			'parent' => CFGP_NAME,
		) );

		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME . '-info',
			'title' => '<em>'.__("Your IP",CFGP_NAME).': </em>'.( isset($CFGEO['ip']) ? $CFGEO['ip'] : '' ) . ' (' . ( isset($CFGEO['ip_version']) ? $CFGEO['ip_version'] : '' ) . ')',
			'href' => '',
			'parent' => CFGP_NAME,
		) );
		
		$address = $CFGEO['address'];
		if(!empty($address))
		{
			
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-city',
				'title' => '<em>'.__("City",CFGP_NAME).':</em> '.$CFGEO['city'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-region',
				'title' => '<em>'.__("Region",CFGP_NAME).':</em> '.$CFGEO['region'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-country',
				'title' => '<em>'.__("Country",CFGP_NAME).':</em> '.$CFGEO['country'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-continent',
				'title' => '<em>'.__("Continent",CFGP_NAME).':</em> '.$CFGEO['continent'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-timezone',
				'title' => '<em>'.__("Timezone",CFGP_NAME).':</em> '.$CFGEO['timezone'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-info-status',
				'title' => '<em>'.__("Status",CFGP_NAME).':</em> '.$CFGEO['status'],
				'href' => '',
				'parent' => CFGP_NAME . '-info',
			) );
		}
		
		// Currency
		if(!empty($CFGEO['currency_converter']))
		{
			$money =  __('Today\'s course',CFGP_NAME) . ': ' . (1 . '' . $CF_GEOPLUGIN_OPTIONS['base_currency']) . ' = ' . number_format($CFGEO['currency_converter'], 2) . '' . $CFGEO['currency'];
			
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-course',
				'title' => $money,
				'href' => '',
				'meta'  => array( 'class' => CFGP_NAME . '-toolbar-course' ),
				'parent' => false,
			) );
		}
	}

	// Import redirections from CSV
	public function cf_geo_import_csv()
	{
		global $wpdb;

		$result = array(
			'success'		=> 0,
			'fail'			=> 0,
			'fail_data'		=> '', 
			'message'		=> '',
		);

		$attachment_url = ( isset( $_POST['import_file_url'] ) ) ? $this->post('import_file_url') : false;

		$query_data = $this->csv_to_array( $attachment_url );

		if( $query_data === false )
		{
			$result['message'] = 'Failed to open or read file';
			wp_send_json( $result );
		}
		if( empty( $query_data ) )
		{
			$result['message'] = 'Failed to extract data from file';
			wp_send_json( $result );
		}

		$table_name = $wpdb->prefix . self::TABLE['seo_redirection'];
		$wpdb->query( "TRUNCATE TABLE {$table_name};");

		foreach( $query_data as $queries )
		{
			$sql = "INSERT INTO {$table_name} ( country, region, city, url, http_code, active ) VALUES ";
			$value = array();
			foreach( $queries as $query )
			{
				$value[] = sprintf("( '%s', '%s', '%s', '%s', %d, %d )", $query['country'], $query['region'], $query['city'], $query['url'], $query['http_code'], $query['active']);
			}	
			$sql .= join( ',', $value );
			$sql .= ';';
			
			$o = $wpdb->query($sql);
			if( $o === false ) ++$result['fail'];
			else $result['success'] = $o;
		}
		wp_send_json( $result );
	}

	// Convert CSV file to PHP array
	public function csv_to_array( $filename = '' )
	{
		// Set chunking offset
		$offset = 500;
			
		// List headers
		$header=get_headers($filename, 1);
		
		// Analyse file
		$analyse = $this->analyse_file($filename);
		
		// setup our return data  
		$return_data = array();
		
		if(isset($header['Content-Type']) && $header['Content-Type'] == 'text/csv')
		{
			
			// IF we can open and read the file
			if (($handle = fopen($filename, "r")) !== FALSE) {
				global $wpdb;
				$table_name = $wpdb->prefix . self::TABLE['seo_redirection'];
				$i = 0;
				$chunk = $offset;
				
				// while data exists loop over data
				while ( ( $ceil = fgetcsv($handle, (isset($header['Content-Length']) ? $header['Content-Length'] : 2000), $analyse['delimiter']['value']) ) !== FALSE ) {
					if( count( $ceil ) <= 6 )
					{
						$data = array(
							'country'	=> isset( $ceil[0]) ? $ceil[0] : '',
							'region'	=> isset( $ceil[1] ) ? $ceil[1] : '',
							'city'		=> isset( $ceil[2] ) ? $ceil[2] : '',
							'url'		=> isset( $ceil[3] ) ? $ceil[3] : '',
							'http_code'	=> ( isset( $ceil[4] ) && in_array( $ceil[4], array('301', '302', '303', '404'), true ) !== false ) ? (int)$ceil[4] : 302,
							'active'	=> ( isset( $ceil[5] ) && ( $ceil[5] == 0 || $ceil[5] == 1 ) ) ? (int)$ceil[5] : 1
						);	
						
						$data['url'] = filter_var( $data['url'], FILTER_SANITIZE_URL );
						if(filter_var($data['url'], FILTER_VALIDATE_URL) && (!empty($data['country']) || !empty($data['region']) || !empty($data['city'])))
						{
							$return_data[$i][] = $data;
							
							--$chunk;
							if( $chunk < 0 )
							{
								$chunk = $offset;
								++$i;
							}
						}
					}
				} 			
				// close our file
				fclose($handle);
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
		// return the new data as a php array
		return $return_data;
	}


	// Export SEO redirection data as CSV
	public function export_seo_csv()
	{
		global $CF_GEOPLUGIN_OPTIONS;
		if( isset( $_GET['action'] ) && $_GET['action'] == 'export_csv' && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 0 )
		{
			if(isset($CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) ? ($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) : 1)
			{
				global $wpdb;

				$table_name = $wpdb->prefix . self::TABLE['seo_redirection'];
				$results = $wpdb->get_results(
					"
						SELECT country, region, city, url, http_code, active
						FROM {$table_name};
					", ARRAY_A
				);
				if( $results !== false )
				{
					ob_start();
					foreach( $results as $result )
					{
						echo join( ';', $result ) . "\n";
					}
					$content = ob_get_clean();
					$file = 'cfgeo_seo_export_' . date('d-m-Y') . '-' . date('h:i:sa') . '.csv';
					header('Content-Description: File Transfer');
					header('Content-Encoding: UTF-8');
					header('Content-type: text/csv; charset=UTF-8');
					header('Content-Disposition: attachment; filename='.$file);
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					$content = mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
					$content = stripcslashes($content);
					echo $content;
					exit;
				}
			}	
		}
	}
		
	// Construct all
	function __construct(){
		$this->add_action( 'init', 'load_javascripts' );
		$this->add_action( 'init', 'load_style' );
		$this->add_action( 'init', 'export_seo_csv' );
		$this->add_action( 'admin_init', 'plugin_custom_menu_class' );
		$this->add_action( 'admin_menu', 'add_cf_geoplugin' );
		
		$this->add_action( 'page-cf-geoplugin-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-defender-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-license-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-debug-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-google-map-sidebar', 'add_rss_feed' );
		
		$this->add_action( 'wp_ajax_cf_geo_rss_feed', 'cf_geo_rss_feed' );
		$this->add_action( 'wp_ajax_nopriv_cf_geo_rss_feed', 'cf_geo_rss_feed' );
		
		$this->add_action( 'wp_ajax_cf_geo_update_option', 'cf_geo_update_option' );
		$this->add_action( 'wp_ajax_cf_geo_update_redirect', 'cf_geo_update_redirect' );
		$this->add_action( 'wp_ajax_cf_geo_import_csv', 'cf_geo_import_csv' );
		
		$this->add_filter( 'plugin_action_links_' . plugin_basename(CFGP_FILE), 'plugin_setting_page' );
		
		$this->add_action( 'admin_bar_menu', 'cf_geoplugin_admin_bar_menu', 900 );
	}
}
endif;