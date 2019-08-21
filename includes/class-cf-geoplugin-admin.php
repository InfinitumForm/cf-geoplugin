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
	private $is_connected = false;
	
	// Main WordPress Geo Plugin Page
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
		
		if(!$this->is_connected)
			$this->is_connected = parent::is_connected();
		
		if($this->is_connected)
		{
			wp_register_style( CFGP_NAME . '-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap'), '4.7.0' );
			wp_register_style( CFGP_NAME . '-choosen-style', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-fontawesome'),  '1.8.7' );
		}
		else
		{
			wp_register_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap'), '4.7.0' );
			wp_register_style( CFGP_NAME . '-choosen-style', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', array(CFGP_NAME . '-bootstrap-reboot', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-fontawesome'),  '1.8.7' );
		}
		
		wp_enqueue_style( CFGP_NAME . '-fontawesome' );
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
	public function custom_javascript(){ 
		?>
		<script>
/* <![CDATA[ */
(function($){
		
}(jQuery || window.jQuery));
/* ]]> */
</script>

<?php }
	
	// Register Scripts
	public function register_javascripts($page){		
		//$this->add_action('admin_head', 'custom_javascript', 10);

		if(!$this->limit_scripts($page)) return false;
		
		if(!$this->is_connected)
			$this->is_connected = parent::is_connected();
		
		if($this->is_connected)
		{
			wp_register_script(  CFGP_NAME . '-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', array('jquery'), '1.14.7' );
			wp_register_script( CFGP_NAME . '-choosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js', array('jquery', CFGP_NAME . '-popper', CFGP_NAME . '-bootstrap'), '1.8.7', true );
			wp_register_script(  CFGP_NAME . '-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array('jquery', CFGP_NAME . '-popper'), '4.1.1' );
		}
		else
		{
			wp_register_script(  CFGP_NAME . '-popper', CFGP_ASSETS . '/js/popper.min.js', array('jquery'), '4.1.1' );
			wp_register_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery', CFGP_NAME . '-popper', CFGP_NAME . '-bootstrap'), '1.8.7', true );
			wp_register_script(  CFGP_NAME . '-bootstrap', CFGP_ASSETS . '/js/bootstrap.min.js', array('jquery', CFGP_NAME . '-popper'), '4.1.1' );
		}

		wp_enqueue_script(  CFGP_NAME . '-popper' );
		wp_enqueue_script(  CFGP_NAME . '-bootstrap' );
		wp_enqueue_script( CFGP_NAME . '-choosen' );
		
		wp_register_script(  CFGP_NAME . '-admin', CFGP_ASSETS . '/js/cfgeoplugin.js', array('jquery', CFGP_NAME . '-popper', CFGP_NAME . '-bootstrap',  CFGP_NAME . '-choosen'), CFGP_VERSION, true );
		wp_enqueue_script(  CFGP_NAME . '-admin' );
		
		wp_localize_script(CFGP_NAME . '-admin', 'CFGP', array(
				'ajaxurl' => self_admin_url('admin-ajax.php'),
				'adminurl' => self_admin_url('/'),
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
	}

	// Register CPT and taxonomies scripts
	public function register_javascripts_ctp( $page )
	{
		$post = '';
		$url = '';
		
		if( isset( $_GET['taxonomy'] ) ) $post = $_GET['taxonomy'];
		elseif( isset( $_GET['post'] ) )
		{
			$post = get_post( absint( $_GET['post'] ) );
			$post = isset( $post->post_type ) ? $post->post_type : '';
		}
		elseif( isset( $_GET['post_type'] ) ) $post = $_GET['post_type'];

		if( !$this->limit_scripts( $post ) ) return false;

		if( $post === 'cf-geoplugin-banner' ) $url = sprintf( 'edit.php?post_type=%s', $post );
		else $url = sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s-banner', $post, CFGP_NAME );

		wp_register_script( CFGP_NAME . '-cpt', CFGP_ASSETS . '/js/cf-geoplugin-cpt.js', array( 'jquery' ), CFGP_VERSION, true );
		wp_enqueue_script( CFGP_NAME . '-cpt' );
		wp_localize_script(CFGP_NAME . '-cpt', 'CFGP', array(
			'ajaxurl' => self_admin_url('admin-ajax.php') ,
			'label' => array(
				'loading' => __('Loading...',CFGP_NAME),
				'not_found' => __('Not Found!',CFGP_NAME),
				'placeholder' => __('Search',CFGP_NAME)
			),
			'current_url'	=> $url
		));
	}
	
	// This function is only called when our plugin's page loads!
    public function load_javascripts(){
		
		$this->add_action( 'admin_enqueue_scripts', 'register_javascripts' );
		$this->add_action( 'admin_enqueue_scripts', 'register_javascripts_ctp' );
    }
	
	// Create "WordPress Geo Plugin" Page
	public function add_cf_geoplugin() {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		add_menu_page(
			__( 'Geo Plugin', CFGP_NAME ),
			__( 'Geo Plugin', CFGP_NAME ),
			'manage_options',
			CFGP_NAME,
			array( &$this, 'page_cf_geoplugin' ),
			'dashicons-location-alt',
			59
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
					__('Spam Protection',CFGP_NAME),
					__('Spam Protection',CFGP_NAME),
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
					self_admin_url('edit.php?post_type=' . CFGP_NAME . '-banner')
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
					array( &$this, 'page_cf_geoplugin_seo_redirection' )
				);
			}
			add_submenu_page(
				CFGP_NAME,
				__('Countries',CFGP_NAME),
				__('Countries',CFGP_NAME),
				'manage_options',
				self_admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-country&post_type=' . CFGP_NAME . '-banner')
			);
			add_submenu_page(
				CFGP_NAME,
				__('Regions',CFGP_NAME),
				__('Regions',CFGP_NAME),
				'manage_options',
				self_admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-region&post_type=' . CFGP_NAME . '-banner')
			);
			add_submenu_page(
				CFGP_NAME,
				__('Cities',CFGP_NAME),
				__('Cities',CFGP_NAME),
				'manage_options',
				self_admin_url('edit-tags.php?taxonomy=' . CFGP_NAME . '-city&post_type=' . CFGP_NAME . '-banner')
			);
			add_submenu_page(
				CFGP_NAME,
				__('Debug Mode',CFGP_NAME),
				__('Debug Mode',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-debug',
				array( &$this, 'page_cf_geoplugin_debug' )
			);
			add_submenu_page(
				CFGP_NAME,
				__('Settings',CFGP_NAME),
				__('Settings',CFGP_NAME),
				'manage_options',
				CFGP_NAME . '-settings',
				array( &$this, 'page_cf_geoplugin_settings' )
			);
			
			if(!CFGP_ACTIVATED)
			{
				add_submenu_page(
					CFGP_NAME,
					__('Activate Unlimited',CFGP_NAME),
					'<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
					'manage_options',
					CFGP_NAME . '-activate',
					array( &$this, 'page_cf_geoplugin_license' )
				);
			}
		}
	}
	
	// WP Hidden links by plugin setting page
	public function plugin_setting_page( $links ) {
		$mylinks = array( 
			'settings'	=> sprintf( '<a href="' . self_admin_url( 'admin.php?page=' . CFGP_NAME . '-settings' ) . '"><b>%s</b></a>', esc_html__( 'Settings', CFGP_NAME ) ), 
			'documentation' => sprintf( '<i class="fa fa-book"></i> <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( CFGP_STORE . '/documentation/' ), esc_html__( 'Documentation', CFGP_NAME ) ),
		);

		return array_merge( $links, $mylinks );
	}

	// Plugin action links after details
	public function cfgp_action_links( $links, $file )
	{
		if( plugin_basename( CFGP_FILE ) == $file )
		{
			$row_meta = array(
				'cfgp_faq' => sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( CFGP_STORE . '/faq/' ), esc_html__( 'FAQ', CFGP_NAME ) ),
				'cfgp_donate' => sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com' ), esc_html__( 'Donate', CFGP_NAME ) ),
				'cfgp_vote'	=> sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( 'https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5' ), esc_html__( 'Vote', CFGP_NAME ) )
			);

			$links = array_merge( $links, $row_meta );
		}
		return $links;
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
				if( $value[0] == 'Geo Plugin' )
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
            <span class="fa fa-info"></span> <?php _e('Live News & info',CFGP_NAME) ?>
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
		$xml= new parseXML(CFGP_STORE . '/feed/', true);
		if(isset($xml->fetch) && isset($xml->fetch->channel) && isset($xml->fetch->channel->item) && count($xml->fetch->channel->item)>0)
		{
			$items = $xml->fetch->channel->item;
			$i = 0;
			$print = array();
			foreach($items as $fetch)
			{
				if( $i >= 3 ) continue;
				$print[]=sprintf(
					'<p><a href="%1$s" target="_blank" class="text-info"><h4 class="h5">%2$s</h4></a>%3$s<small>~%4$s</small></p>',
					$fetch->link,
					$fetch->title,
					strip_tags($fetch->description, '<a><img><h1><h2><h3><h4><p><br><strong><i><u><b>'),
					date("F j, Y", strtotime($fetch->pubDate))
				);
				++$i;
			}
			
			$print = strip_tags( join("\r\n", $print), '<a><img><h1><h2><h3><h4><p><br>' );
			$print = preg_replace('/<a\s(.*?)>/i', '<a $1 target="_blank">', $print);
			$print = preg_replace('/href="(.*?)"/i', 'href="$1?ref=live-news"', $print);
			$_SESSION[CFGP_PREFIX . 'rss'] = $print;
			echo $print;
		}
		exit;
	}
	
	/* 
	 * Update plugin options
	 * @since 7.0.0
	*/
	public function cf_geo_update_option()
	{
		$name = $this->post('name');

		$value = isset( $_POST['value'] ) ? $_POST['value'] : '';

		if( is_array( $value ) )
		{
			/**
			 * Checkbox arrays
			 */
			$enable_seo_posts = array();
			$enable_geo_tag = array();

			$updated = 'true';
			foreach( $value as $i => $array )
			{
				$option_name = $array['name'];
				$option_value = $array['value'];

				if( $option_name === 'connection_timeout' || $option_name === 'timeout' ) 
				{
					if( empty( $option_value ) || (int)$option_value < 3 || (int)$option_value > 9999 ) $option_value = isset( $this->default_options[ $option_name ] ) ? $this->default_options[ $option_name ] : 15;
					$option_value = (int)$option_value;
				}
				elseif( $option_name === 'enable_seo_posts[]' )
				{
					$enable_seo_posts[] = $option_value;
				}
				elseif( $option_name === 'enable_geo_tag[]' )
				{
					$enable_geo_tag[] = $option_value;
				}
				elseif( isset( $this->default_options[ $option_name ] ) ) 
				{
					$this->update_option( $option_name, $option_value );
				}
			}

			/**
			 * Update checkboxes
			 */
			$this->update_option( 'enable_seo_posts', $enable_seo_posts );
			$this->update_option( 'enable_geo_tag', $enable_geo_tag );
		}
		else $updated = 'error';
		
		echo $updated;
		wp_die();
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
			'only_once'		=> $this->post( 'cf_geo_only_once', 'int', 0 ),
			'action'		=> $this->post( 'cf_geo_redirect_action' )
		);

		$table_name = parent::TABLE['seo_redirection'];
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
					'only_once'		=> $data['only_once'],
					'active'		=> $data['active']
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d',
					'%d'
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
					'only_once'		=> $data['only_once'],
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
		$CFGEO = $GLOBALS['CFGEO']; 
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; 
		global $wp_admin_bar;
		// GEOPLUGIN
		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME,
			'title' => '<span  style="
		float:left; width:25px !important; height:25px !important;
		margin-left: 5px !important; margin-top: 2px !important; background:url(\''.CFGP_ASSETS . '/images/cf-geo-25x25.png\') no-repeat center center / cover;"></span>',
			'href' => '',
			'meta'  => array( 'class' => CFGP_NAME . '-toolbar-page', 'title'=>sprintf(__("WordPress Geo Plugin ver.%s",CFGP_NAME),$CFGEO['version'])),
			'parent' => false,
		) );
		$wp_admin_bar->add_node( array(
			'id' => CFGP_NAME . '-helper',
			'title' => 'Geo Plugin',
			'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME),
			'meta'  => array( 'class' => CFGP_NAME . '-toolbar-help-page' ),
			'parent' => CFGP_NAME,
		) );
		
		if($CF_GEOPLUGIN_OPTIONS['enable_gmap'])
		{
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-gmap',
				'title' => __('Google Map',CFGP_NAME),
				'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-google-map'),
				'meta'  => array( 'class' => CFGP_NAME . '-gmap-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );
		}
		
		if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) {

			if($CF_GEOPLUGIN_OPTIONS['enable_defender'])
			{
				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-defender',
					'title' => __('Anti Spam Protection',CFGP_NAME),
					'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-defender'),
					'meta'  => array( 'class' => CFGP_NAME . '-defender-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-banner',
					'title' => __('Geo Banner',CFGP_NAME),
					'href' => self_admin_url( 'edit.php?post_type=' . CFGP_NAME . '-banner' ),
					'meta'  => array( 'class' => CFGP_NAME . '-banner-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-seo-redirection',
					'title' => __('SEO Redirection',CFGP_NAME),
					'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-seo-redirection'),
					'meta'  => array( 'class' => CFGP_NAME . '-seo-redirection-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-countries',
					'title' => __('Countries',CFGP_NAME),
					'href' => self_admin_url( 'edit-tags.php?taxonomy=' . CFGP_NAME . '-country&post_type=cf-geoplugin-banner' ),
					'meta'  => array( 'class' => CFGP_NAME . '-countries-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-regions',
					'title' => __('Regions',CFGP_NAME),
					'href' =>  self_admin_url( 'edit-tags.php?taxonomy=' . CFGP_NAME . '-region&post_type=cf-geoplugin-banner' ),
					'meta'  => array( 'class' => CFGP_NAME . '-regions-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );

				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-cities',
					'title' => __('Cities',CFGP_NAME),
					'href' => self_admin_url( 'edit-tags.php?taxonomy=' . CFGP_NAME . '-city&post_type=cf-geoplugin-banner' ),
					'meta'  => array( 'class' => CFGP_NAME . '-city-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );
			}
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-debug',
				'title' => __('Debug Mode',CFGP_NAME),
				'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-debug'),
				'meta'  => array( 'class' => CFGP_NAME . '-debug-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-setup',
				'title' => __('Settings',CFGP_NAME),
				'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-settings'),
				'meta'  => array( 'class' => CFGP_NAME . '-setup-toolbar-page' ),
				'parent' => CFGP_NAME,
			) );

			if(!CFGP_ACTIVATED)
			{
				$wp_admin_bar->add_node( array(
					'id' => CFGP_NAME . '-activate',
					'title' => __('Activate Unlimited',CFGP_NAME),
					'href' => self_admin_url( 'admin.php?page=' . CFGP_NAME . '-activate'),
					'meta'  => array( 'class' => CFGP_NAME . '-activate-toolbar-page' ),
					'parent' => CFGP_NAME,
				) );
			}
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

		$attachment_url = ( isset( $_POST['import_file_url'] ) ) ? $_POST['import_file_url'] : false;
		
		if( empty($attachment_url) )
		{
			$result['message'] = __('Failed to open file path', CFGP_NAME) . ' (' . $attachment_url . ')';
			wp_send_json( $result );
		}

		$query_data = $this->csv_to_array( $attachment_url );

		if( $query_data === false )
		{
			$result['message'] = __('Failed to open or read file', CFGP_NAME);
			wp_send_json( $result );
		}
		if( empty( $query_data ) )
		{
			$result['message'] = __('Failed to extract data from file', CFGP_NAME);
			wp_send_json( $result );
		}

		$table_name = $wpdb->prefix . parent::TABLE['seo_redirection'];
		$wpdb->query( "TRUNCATE TABLE {$table_name};");

		foreach( $query_data as $queries )
		{
			$sql = "INSERT INTO {$table_name} ( country, region, city, url, http_code, active, only_once ) VALUES ";
			$value = array();
			foreach( $queries as $query )
			{
				$value[] = sprintf("( '%s', '%s', '%s', '%s', %d, %d, %d )", $query['country'], $query['region'], $query['city'], $query['url'], $query['http_code'], (int)$query['active'], (int)$query['only_once']);
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
		
		if(isset($header['Content-Type']) && ($header['Content-Type'] == 'text/csv' || $header['Content-Type'] == 'application/vnd.ms-excel'))
		{
			
			// IF we can open and read the file
			if (($handle = fopen($filename, "r")) !== FALSE) {
				global $wpdb;
				$table_name = $wpdb->prefix . parent::TABLE['seo_redirection'];
				$i = 0;
				$chunk = $offset;
				
				// while data exists loop over data
				while ( ( $ceil = fgetcsv($handle, (isset($header['Content-Length']) ? $header['Content-Length'] : 2000), $analyse['delimiter']['value']) ) !== FALSE ) {
					if( count( $ceil ) <= 7 )
					{
						$data = array(
							'country'	=> isset( $ceil[0]) ? $ceil[0] : '',
							'region'	=> isset( $ceil[1] ) ? $ceil[1] : '',
							'city'		=> isset( $ceil[2] ) ? $ceil[2] : '',
							'url'		=> isset( $ceil[3] ) ? $ceil[3] : '',
							'http_code'	=> ( isset( $ceil[4] ) && in_array( $ceil[4], array_keys($this->http_codes) ) !== false ) ? (int)$ceil[4] : 302,
							'active'	=> ( isset( $ceil[5] ) && ( (int)$ceil[5] == 0 || (int)$ceil[5] == 1 ) ) ? (int)$ceil[5] : 1,
							'only_once'	=> ( isset( $ceil[6] ) && ( (int)$ceil[6] == 0 || (int)$ceil[6] == 1 ) ) ? (int)$ceil[6] : 0
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
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if( isset( $_GET['action'] ) && $_GET['action'] == 'export_csv' && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 0 )
		{
			if(isset($CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) ? ($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) : 1)
			{
				global $wpdb;

				$table_name = $wpdb->prefix . parent::TABLE['seo_redirection'];
				$results = $wpdb->get_results(
					"
						SELECT country, region, city, url, http_code, active, only_once
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
					
					if(!$CF_GEOPLUGIN_OPTIONS['enable_cache'])
					{
						// disable caching
						$now = gmdate("D, d M Y H:i:s");
						header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
						header("Cache-Control: max-age=0, post-check=0, pre-check=0, no-cache, must-revalidate, proxy-revalidate");
						header("Last-Modified: {$now} GMT");
						header('Pragma: public');
					}
				
					// force download 
					header('Content-Description: File Transfer');
					header('Content-Encoding: UTF-8');
					header('Content-Type: text/csv; charset=UTF-8');
					
					header('Content-Disposition: attachment; filename='.$file);
					header('Content-Transfer-Encoding: binary');

					$content = mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
					$content = stripcslashes($content);
					echo $content;
					exit;
				}
			}	
		}
	}

	// Dashboard widgets
	public function cf_geoplugin_dashboard_widgets()
	{
		if(!$this->is_connected)
			$this->is_connected = parent::is_connected();
		
		if(!$this->is_connected) return false; // When user doesn't have connection prevent API call errors

		wp_add_dashboard_widget(
			CFGP_NAME . '-admin-status', 
			trim(do_shortcode('[cfgeo_flag size="14" image]') . ' ' . __( 'WordPress Geo Plugin', CFGP_NAME )),
			array( &$this, 'cf_geoplugin_dashboard_callback' ) 
		);
	}

	// Geoplugin admin stats dashboard callback
	public function cf_geoplugin_dashboard_callback()
	{
		if(!$this->is_connected)
			$this->is_connected = parent::is_connected();
		
		if(!$this->is_connected) return false; // When user doesn't have connection prevent API call errors

		wp_register_style( CFGP_NAME . '-dashboard-style', CFGP_ASSETS . '/css/cf-geoplugin-dashboard.css', array('dashboard'), CFGP_VERSION );
		wp_enqueue_style( CFGP_NAME . '-dashboard-style' );
		
		if ( ! function_exists( 'plugins_api' ) ) {
			  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}
		/** Prepare our query */
		//donate_link
		//versions
		$plugin = plugins_api( 'plugin_information', array(
			'slug' => 'cf-geoplugin',
			'fields' => array(
				'version' => true,
			)
		));

		if( $plugin === false || is_wp_error( $plugin ) ) return false; // User doesnt't have connection or something is wrong with API so prevent errors

		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
		
		$plugin_updated = version_compare(CFGP_VERSION, $plugin->version, '<');
		?>
		<div id="cf-geoplugin-dashboard-wrapper">
			<div id="cf-geoplugin-dashboard-info" class="activity-block">
            	<h3><?php _e( 'Informations', CFGP_NAME ); ?>:</h3>
				<ul>
					<li><strong>IP:</strong> <span>(IPv<?php echo $CFGEO['ip_version']; ?>) <?php echo $CFGEO['ip']; ?></span></li>
					<li><strong><?php _e( 'Address', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['address']; ?></span></li>
					<li><strong><?php _e( 'Currency', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['currency']; ?></span></li>
					<li><strong><?php _e( 'Lookup', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['lookup']; ?></span></li>
					<li><strong><?php _e( 'Runtime', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['runtime']; ?></span></li>
				</ul>	
			</div>
			
			<div id="cf-geoplugin-dashboard-version-info-title" class="activity-block<?php if($plugin_updated) : ?> hilight<?php endif; ?>">
            <?php if(!$plugin_updated) : ?>
				<h3><strong><?php printf(__( 'What is new on WordPress Geo Plugin version %s', CFGP_NAME ), $plugin->version); ?></strong></h3>
            <?php else: ?>
            	<h3><strong style="color:#cc0000;"><?php printf(__( 'NEW version is available - WordPress Geo Plugin ver.%s', CFGP_NAME ), $plugin->version); ?></strong></h3>
            <?php endif; ?>
			</div>
            <div id="cf-geoplugin-dashboard-version-info" class="activity-block<?php if($plugin_updated) : ?> hilight<?php endif; ?>">
            <?php if($plugin_updated) : ?>
            <h3><strong><?php _e( 'Update WordPress Geo Plugin and get:', CFGP_NAME ); ?></strong></h3>
            <?php endif; ?>
            <?php
				preg_match('@<h4>' . str_replace('.','\.',$plugin->version) . '</h4>.*?(<ul>(.*?)</ul>)@si', $plugin->sections['changelog'], $version_details, PREG_OFFSET_CAPTURE);
				if(isset($version_details[1]) && isset($version_details[1][0]))
					echo $version_details[1][0];
				else
					_e( 'There was error in fetching plugin data.', CFGP_NAME )
			?>
			<?php if($plugin_updated && !is_multisite()) : ?>
				<a href="<?php echo self_admin_url('plugin-install.php?tab=plugin-information&plugin=cf-geoplugin&TB_iframe=true&width=600&height=550'); ?>"  class="button button-primary button-hero">Download new version NOW</a>
			<?php endif; ?>
            </div>
            <div id="cf-geoplugin-dashboard-details-info-title" class="activity-block">
				<h3><strong><?php _e( 'WordPress Geo Plugin details', CFGP_NAME ); ?></strong></h3>
			</div>
            <div id="cf-geoplugin-dashboard-details-info" class="activity-block">
            	<ul class="cf-geoplugin-dashboard-details">
                	<li><strong><?php _e( 'Last Update', CFGP_NAME ); ?>:</strong> <span><?php echo date((get_option('date_format').' '.get_option('time_format')),strtotime($plugin->last_updated)); ?></span></li>
					<li><strong><?php _e( 'Homepage', CFGP_NAME ); ?>:</strong> <span><a href="<?php echo $plugin->homepage ?>" target="_blank"><?php echo $plugin->homepage ?></a></span></li>
					<li><strong><?php _e( 'Donation', CFGP_NAME ); ?>:</strong> <span><a href="<?php echo $plugin->donate_link ?>" target="_blank"><?php _e( 'Make Donation via PayPal', CFGP_NAME ) ?></a></span></li>
                    <li><strong><?php _e( 'WP Support', CFGP_NAME ); ?>:</strong> <span><?php
                    	if(version_compare(get_bloginfo('version'), $plugin->requires, '>='))
						{
							printf('<b style="color:#2dbc0d;">' . __( 'Supported on WP version %s', CFGP_NAME ) . '</b>', get_bloginfo('version'));
						}
						else
						{
							_e( '', CFGP_NAME );
							printf('<b style="color:#cc0000;">' . __( 'Plugin require WordPress version %s or above!', CFGP_NAME ) . '</b>', $plugin->requires);
						}
					?></span></li>
                    <li><strong><?php _e( 'PHP Support', CFGP_NAME ); ?>:</strong> <span><?php
						preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
                    	if(version_compare(PHP_VERSION, $plugin->requires_php, '>='))
						{
							printf('<b style="color:#2dbc0d;">' . __( 'Supported on PHP version %s', CFGP_NAME ) . '</b>', $match[0]);
						}
						else
						{
							_e( '', CFGP_NAME );
							printf('<b style="color:#cc0000;">' . __( 'Plugin not support PHP version %1$s. Please use PHP vesion %2$s or above.', CFGP_NAME ) . '</b>', PHP_VERSION, $plugin->requires_php);
						}
					?></span></li>
                </ul>
                <?php if(CFGP_DEV_MODE === true && isset( $plugin->downloaded )) : ?><strong style="cf-geoplugin-dashboard-downloaded"><?php printf(__( 'Total downloaded %d times', CFGP_NAME ),$plugin->downloaded); ?>.</strong><?php endif; ?>
        	</div>
			<div class="cf-geoplugin-dashboard-details-copyright" id="cf-geoplugin-dashboard-footer">
				<div class="cf-geoplugin-dashboard-details-copyright-column" style="text-align:center; margin-top:10px;">
					<a href="<?php echo CFGP_STORE; ?>/pricing/" target="_blank"><?php _e( 'Pricing', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/documentation/" target="_blank"><?php _e( 'Documentation', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/faq/" target="_blank"><?php _e( 'FAQ', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/privacy-policy/" target="_blank"><?php _e( 'Privacy Policy', CFGP_NAME ); ?></a> | <a href="<?php echo self_admin_url( 'admin.php?page=' . CFGP_NAME . '-settings'); ?>"><?php _e( 'Settings', CFGP_NAME ); ?></a>
				</div>
				<div class="cf-geoplugin-dashboard-details-copyright-column" style="text-align:center; margin-top:5px;">
					<small>Copyright © 2015-<?php echo date('Y'); ?> WordPress Geo Plugin. All rights reserved.</small>
				</div>
				<div class="cf-geoplugin-dashboard-details-copyright-column" style="text-align:center; margin-top:5px;">
					<small><a href="https://infinitumform.com?ref=cf-geoplugin" target="_blank" title="INFINITUM FORM - Specialized agency for web development, graphic design, marketing and PR" rel="author">Created by INFINITUM FORM®</a></small>
				</div>
			</div>
		</div>
		<?php
	}
	
	public function rv_custom_dashboard_widget() {
		if(!$this->is_connected)
			$this->is_connected = parent::is_connected();
		
		if(!$this->is_connected) return false; // When user doesn't have connection prevent API call errors

		// Bail if not viewing the main dashboard page
		if ( get_current_screen()->base !== 'dashboard' ) {
			return;
		}
		
		wp_register_style( CFGP_NAME . '-dashboard-style', CFGP_ASSETS . '/css/cf-geoplugin-dashboard.css', array('dashboard'), CFGP_VERSION );
		wp_enqueue_style( CFGP_NAME . '-dashboard-style' );
		
		if ( ! function_exists( 'plugins_api' ) ) {
			  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}
		/** Prepare our query */
		//donate_link
		//versions
		$plugin = plugins_api( 'plugin_information', array(
			'slug' => 'cf-geoplugin',
			'fields' => array(
				'version' => true,
			)
		));

		if( $plugin === false || is_wp_error( $plugin ) ) return false; // User doesnt't have connection or something is wrong with API so prevent errors

		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
		
		$plugin_updated = version_compare(CFGP_VERSION, $plugin->version, '<');
		
		?>

		<div id="custom-id" class="welcome-panel" style="display: none;">
			<div class="welcome-panel-content"  id="cf-geoplugin-dashboard-wrapper">
				<h2><?php echo trim(($CF_GEOPLUGIN_OPTIONS['enable_flag']?do_shortcode('[cfgeo_flag size="21" image]'):'') . ' ' . __( 'WordPress Geo Plugin', CFGP_NAME )); ?></h2>
				<p class="about-description"><?php _e( 'WordPress Geo Plugin is fully active and ready to use', CFGP_NAME ); ?></p>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column" id="cf-geoplugin-dashboard-info">
						<div class="activity-block">
							<h3><?php _e( 'Informations', CFGP_NAME ); ?></h3>
						</div>
						<div id="cf-geoplugin-dashboard-info" class="activity-block">
							<ul>
								<li><strong>IP:</strong> <span>(IPv<?php echo $CFGEO['ip_version']; ?>) <?php echo $CFGEO['ip']; ?></span></li>
								<li><strong><?php _e( 'Address', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['address']; ?></span></li>
								<li><strong><?php _e( 'Currency', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['currency']; ?></span></li>
								<li><strong><?php _e( 'Lookup', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['lookup']; ?></span></li>
								<li><strong><?php _e( 'Runtime', CFGP_NAME ); ?>:</strong> <span><?php echo $CFGEO['runtime']; ?></span></li>
							</ul>	
						</div>
					</div>
					<div class="welcome-panel-column">
						<div id="cf-geoplugin-dashboard-version-info-title" class="activity-block<?php if($plugin_updated) : ?> hilight<?php endif; ?>">
						<?php if(!$plugin_updated) : ?>
							<h3><strong><?php printf(__( 'What is new on WordPress Geo Plugin version %s', CFGP_NAME ), $plugin->version); ?></strong></h3>
						<?php else: ?>
							<h3><strong style="color:#cc0000;"><?php printf(__( 'NEW version is available - WordPress Geo Plugin ver.%s', CFGP_NAME ), $plugin->version); ?></strong></h3>
						<?php endif; ?>
						</div>
						<div id="cf-geoplugin-dashboard-version-info" class="activity-block<?php if($plugin_updated) : ?> hilight<?php endif; ?>">
						<?php if($plugin_updated) : ?>
						<h3><strong><?php _e( 'Update WordPress Geo Plugin and get:', CFGP_NAME ); ?></strong></h3>
						<?php endif; ?>
						<?php
							preg_match('@<h4>' . str_replace('.','\.',$plugin->version) . '</h4>.*?(<ul>(.*?)</ul>)@si', $plugin->sections['changelog'], $version_details, PREG_OFFSET_CAPTURE);
							if(isset($version_details[1]) && isset($version_details[1][0]))
								echo $version_details[1][0];
							else
								_e( 'There was error in fetching plugin data.', CFGP_NAME )
						?>
						<?php if($plugin_updated && !is_multisite() && !$this->get( 'cfgp_auto_update', 'bool' ) ) : ?>
							<a href="<?php echo self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=cf-geoplugin&TB_iframe=true&widht=600&height=550' ); ?>"  class="button button-primary button-hero"><?php _e( 'Download new version NOW', CFGP_NAME ); ?></a>
						<?php
							elseif( $plugin_updated && $this->get( 'cfgp_auto_update', 'bool' ) ) :
								?>
								<h3><strong><?php _e( 'Plugin Update Process:' ); ?></strong></h3>
								<?php
								$this->plugin_auto_update();
							endif;
						?>
						</div>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<div id="cf-geoplugin-dashboard-details-info-title" class="activity-block">
							<h3><strong><?php _e( 'WordPress Geo Plugin details', CFGP_NAME ); ?></strong></h3>
						</div>
						<div id="cf-geoplugin-dashboard-details-info" class="activity-block">
							<ul class="cf-geoplugin-dashboard-details">
								<li><strong><?php _e( 'Last Update', CFGP_NAME ); ?>:</strong> <span><?php echo date((get_option('date_format').' '.get_option('time_format')),strtotime($plugin->last_updated)); ?></span></li>
								<li><strong><?php _e( 'Homepage', CFGP_NAME ); ?>:</strong> <span><a href="<?php echo $plugin->homepage ?>" target="_blank"><?php echo $plugin->homepage ?></a></span></li>
								<li><strong><?php _e( 'Donation', CFGP_NAME ); ?>:</strong> <span><a href="<?php echo $plugin->donate_link ?>" target="_blank"><?php _e( 'Make Donation via PayPal', CFGP_NAME ) ?></a></span></li>
								<li><strong><?php _e( 'WP Support', CFGP_NAME ); ?>:</strong> <span><?php
									if(version_compare(get_bloginfo('version'), $plugin->requires, '>='))
									{
										printf('<b style="color:#2dbc0d;">' . __( 'Supported on WP version %s', CFGP_NAME ) . '</b>', get_bloginfo('version'));
									}
									else
									{
										_e( '', CFGP_NAME );
										printf('<b style="color:#cc0000;">' . __( 'Plugin require WordPress version %s or above!', CFGP_NAME ) . '</b>', $plugin->requires);
									}
								?></span></li>
								<li><strong><?php _e( 'PHP Support', CFGP_NAME ); ?>:</strong> <span><?php
									preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
									if(version_compare(PHP_VERSION, $plugin->requires_php, '>='))
									{
										printf('<b style="color:#2dbc0d;">' . __( 'Supported on PHP version %s', CFGP_NAME ) . '</b>', $match[0]);
									}
									else
									{
										_e( '', CFGP_NAME );
										printf('<b style="color:#cc0000;">' . __( 'Plugin not support PHP version %1$s. Please use PHP vesion %2$s or above.', CFGP_NAME ) . '</b>', PHP_VERSION, $plugin->requires_php);
									}
								?></span></li>
							</ul>
							<?php if(CFGP_DEV_MODE === true && isset( $plugin->downloaded ) ) : ?><strong style="cf-geoplugin-dashboard-downloaded"><?php printf(__( 'Total downloaded %d times', CFGP_NAME ),$plugin->downloaded); ?>.</strong><?php endif; ?>
						</div>
					</div>
				</div>
				<div class="welcome-panel-column-container" id="cf-geoplugin-dashboard-footer">
					<div class="welcome-panel-column">
						Copyright © 2015-<?php echo date('Y'); ?> WordPress Geo Plugin. All rights reserved.
					</div>
					<div class="welcome-panel-column" style="text-align:center">
						<a href="https://infinitumform.com?ref=cf-geoplugin" target="_blank" title="INFINITUM FORM - Specialized agency for web development, graphic design, marketing and PR" rel="author">Created by INFINITUM FORM®</a>
					</div>
					<div class="welcome-panel-column" style="text-align:right">
						<a href="<?php echo CFGP_STORE; ?>/pricing/" target="_blank"><?php _e( 'Pricing', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/documentation/" target="_blank"><?php _e( 'Documentation', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/faq/" target="_blank"><?php _e( 'FAQ', CFGP_NAME ); ?></a> | <a href="<?php echo CFGP_STORE; ?>/privacy-policy/" target="_blank"><?php _e( 'Privacy Policy', CFGP_NAME ); ?></a> | <a href="<?php echo self_admin_url( 'admin.php?page=' . CFGP_NAME . '-settings'); ?>"><?php _e( 'Settings', CFGP_NAME ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('#welcome-panel').after($('#custom-id').show());
			});
		</script>

<?php }

	/**
	 * Add async/defer attributes to scripts
	 */
	public function add_script_attribute( $tag, $handle )
	{
		if( $handle !== CFGP_NAME . '-google-map' ) return $tag;

		return str_replace( ' src', ' async src', $tag );
	}

	/**
	 * Disable gutenberg in geo banner
	 */
	public function cf_disable_gutenberg( $use_block_editor, $post_type )
	{
		if( $post_type === 'cf-geoplugin-banner' ) $use_block_editor = false;
		
		return $use_block_editor;
	}
	
	/**
	 * Hook for the post delete
	 */
	public function delete_post($id){
		// Remove cookie if they exists
		if(isset($_COOKIE) && !empty($_COOKIE))
		{
			$cookie_name = '__cfgp_seo_' . $id . '_once_';
			foreach($_COOKIE as $key => $value)
			{
				if(strpos($key, $cookie_name) !== false)
				{
					setcookie($key, time() . '', (time()-((365 * DAY_IN_SECONDS) * 2)), COOKIEPATH, COOKIE_DOMAIN );
					unset($_COOKIE[$key]);
				}
			}
		}
	}
	
	/**
	 * Allow .csv uploads
	 */
	public function upload_mimes($mimes = array()) {
		$mimes['csv'] = "text/csv";
		return $mimes;
	}
	
	function upload_multi_mimes( $check, $file, $filename, $mimes ) {
		if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
			// Adjust to your needs!
			$multi_mimes = array( array( 'csv' => 'text/csv' ), array( 'csv' => 'application/vnd.ms-excel' ) );

			// Run new checks for our custom mime types and not on core mime types.
			foreach( $multi_mimes as $mime ) {
				$this->remove_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );
				$check = wp_check_filetype_and_ext( $file, $filename, $mime );
				$this->add_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );
				if ( ! empty( $check['ext'] ) ||  ! empty( $check['type'] ) ) {
					return $check;
				}
			}
		}
		return $check;
	}

	/**
	 * All initialized functions
	 */
	public function admin_init(){
		$this->load_javascripts();
		$this->load_style();
		$this->export_seo_csv();
		$this->plugin_custom_menu_class();
		$this->add_action( 'delete_post', 'delete_post', 10 );
	}

	// Construct all
	function __construct(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		$this->add_action( 'admin_init', 'admin_init' );
		
		if(CFGP_MULTISITE) $this->add_action( "network_admin_menu", 'add_cf_geoplugin' );
		else $this->add_action( 'admin_menu', 'add_cf_geoplugin' );

		$this->add_action( 'page-cf-geoplugin-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-defender-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-license-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-debug-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-google-map-sidebar', 'add_rss_feed' );
		$this->add_action( 'page-cf-geoplugin-spam-ip-sidebar', 'add_rss_feed' );
		
		$this->add_action( 'wp_ajax_cf_geo_rss_feed', 'cf_geo_rss_feed' );
		$this->add_action( 'wp_ajax_nopriv_cf_geo_rss_feed', 'cf_geo_rss_feed' );
		
		$this->add_action( 'wp_ajax_cf_geo_update_option', 'cf_geo_update_option' );
		$this->add_action( 'wp_ajax_cf_geo_update_redirect', 'cf_geo_update_redirect' );
		$this->add_action( 'wp_ajax_cf_geo_import_csv', 'cf_geo_import_csv' );
		
		$this->add_filter( 'plugin_action_links_' . plugin_basename(CFGP_FILE), 'plugin_setting_page' );
		$this->add_filter( 'plugin_row_meta', 'cfgp_action_links', 10, 2 );
		$this->add_filter( 'upload_mimes', 'upload_mimes', 99 );
		$this->add_filter( 'mime_types', 'upload_mimes', 99 );
		$this->add_filter( 'wp_check_filetype_and_ext', 'upload_multi_mimes', 99, 4 );

		$this->add_action( 'admin_bar_menu', 'cf_geoplugin_admin_bar_menu', 900 );

		if($CF_GEOPLUGIN_OPTIONS['enable_dashboard_widget']){
			if($CF_GEOPLUGIN_OPTIONS['enable_advanced_dashboard_widget']){
				$this->add_action( 'admin_footer', 'rv_custom_dashboard_widget' );
			} else {
				if( CFGP_MULTISITE ) $this->add_action( 'wp_network_dashboard_setup', 'cf_geoplugin_dashboard_widgets' );
				else $this->add_action( 'wp_dashboard_setup', 'cf_geoplugin_dashboard_widgets' );
			}
		}

		$this->add_filter( 'use_block_editor_for_post_type', 'cf_disable_gutenberg', 10, 2 );
		//$this->add_filter( 'script_loader_tag', 'add_script_attribute', 10, 2 );
	}
}
endif;