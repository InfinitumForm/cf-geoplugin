<?php
/**
 * Settings page
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Admin')) :
class CFGP_Admin extends CFGP_Global {
	function __construct(){
		$this->add_action('admin_bar_menu', 'admin_bar_menu', 90, 1);
		$this->add_action('wp_footer', 'admin_bar_menu_css', 90, 1);
		$this->add_action('admin_footer', 'admin_bar_menu_css', 90, 1);
		
		$this->add_action('admin_enqueue_scripts', 'register_scripts');
		$this->add_action('admin_enqueue_scripts', 'register_scripts_ctp');
		$this->add_action('admin_enqueue_scripts', 'register_style');
		$this->add_action('admin_init', 'admin_init');
		
		$this->add_action('manage_edit-cf-geoplugin-country_columns', 'rename__cf_geoplugin_country__column');
		$this->add_action('manage_edit-cf-geoplugin-region_columns', 'rename__cf_geoplugin_region__column');
		$this->add_action('manage_edit-cf-geoplugin-city_columns', 'rename__cf_geoplugin_city__column');
		$this->add_action('manage_edit-cf-geoplugin-postcode_columns', 'rename__cf_geoplugin_postcode__column');
		
		$this->add_action('wp_ajax_cfgp_rss_feed', 'ajax__rss_feed');
		$this->add_action('wp_ajax_cfgp_dashboard_rss_feed', 'ajax__dashboard_rss_feed');
		
		$this->add_action('wp_network_dashboard_setup', 'register_dashboard_widget');
		$this->add_action('wp_dashboard_setup', 'register_dashboard_widget');
		
		$this->add_filter( 'plugin_action_links_' . plugin_basename(CFGP_FILE), 'plugin_action_links' );
		$this->add_filter( 'plugin_row_meta', 'cfgp_action_links', 10, 2 );
		
		$this->add_action('wp_ajax_cfgp_select2_locations', array('CFGP_Library', 'ajax__select2_locations'));
		$this->add_action('wp_ajax_nopriv_cfgp_select2_locations', array('CFGP_Library', 'ajax__select2_locations'));
	}
	
	// WP Hidden links by plugin setting page
	public function plugin_action_links( $links ) {
		$mylinks = array( 
			'settings'	=> sprintf( '<a href="' . self_admin_url( 'admin.php?page=' . CFGP_NAME . '-settings' ) . '" class="cfgeo-plugins-action-settings">%s</a>', esc_html__( 'Settings', CFGP_NAME ) ), 
			'documentation' => sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-documentation">%s</a>', esc_url( CFGP_STORE . '/documentation/' ), esc_html__( 'Documentation', CFGP_NAME ) ),
		);

		return array_merge( $links, $mylinks );
	}
	
	// Plugin action links after details
	public function cfgp_action_links( $links, $file )
	{
		if( plugin_basename( CFGP_FILE ) == $file )
		{
			$row_meta = array(
			/*	'cfgp_donate' => sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-donation">%s</a>',
					esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com' ),
					esc_html__( 'Donate', CFGP_NAME )
				),	*/
				'cfgp_vote'	=> sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="cfgeo-plugins-action-vote" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> %s</a>',
					esc_url( 'https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5' ),
					esc_attr__( 'Give us five if you like!', CFGP_NAME ),
					esc_html__( '5 Stars?', CFGP_NAME )
				)
			);

			$links = array_merge( $links, $row_meta );
		}
		return $links;
	}
	
	public function register_dashboard_widget(){
		if ( get_current_screen()->base !== 'dashboard' ) {
			return;
		}
		
		wp_add_dashboard_widget(
			CFGP_NAME . '-dashboard-statistic', 
			__( 'CF Geo Plugin', CFGP_NAME ),
			function (){
				do_action('cfgp/dashboard/widget/statistic');
			},
			NULL,
			NULL,
			'normal',
			'high'
		);
		
		wp_add_dashboard_widget(
			CFGP_NAME . '-dashboard-feed', 
			__( 'CF Geo Plugin Live News & Info', CFGP_NAME ),
			function (){
				add_action('admin_footer', function(){ ?>
<script id="cfgp-rss-feed-js" type="text/javascript">
/* <![CDATA[ */
(function(jCFGP){$feed=jCFGP('.cfgp-load-dashboard-rss-feed');if($feed.length>0){jCFGP.ajax({url:"<?php echo admin_url('/admin-ajax.php'); ?>",method:'post',accept:'text/html',data:{action:'cfgp_dashboard_rss_feed'},cache:true}).done(function(data){$feed.html(data).removeClass('cfgp-load-dashboard-rss-feed');});}}(jQuery||window.jQuery));
/* ]]> */
</script>
				<?php }, 99);
				do_action('cfgp/dashboard/widget/feed');
			},
			NULL,
			NULL,
			'normal'
		);
	}
	
	public function ajax__rss_feed () {
		$RSS = CFGP_DB_Cache::get('cfgp-rss');
		if( !empty($RSS) ) {
			echo $RSS;
			exit;
		} else {
			$RSS = $DASH_RSS = [];
			$data = CFGP_U::curl_get( CFGP_STORE . '/wp-ajax.php?action=cfgp_get_posts_data&numberposts=10&posts_per_page=10', '', array(), false);
			if($data)
			{
				$data = (object)$data;
				if(isset($data->posts) && is_array($data->posts))
				{
					$x = 4;
					foreach($data->posts as $i => $post)
					{
						$post = (object)$post;
						
						$DASH_RSS[]=sprintf('<li><a href="%1$s" target="_blank">%2$s</a></li>', esc_url($post->post_url), esc_html($post->post_title));
						
						if($i <= $x) {
							if($i === 0) {
								$RSS[]=sprintf('<div class="cfgp-rss-container">
										<a href="%1$s" target="_blank" class="cfgp-rss-img">
											<img src="%3$s" class="img-fluid">
										</a>
										<h3>%2$s</h3>
										<div class="cfgp-rss-excerpt">
											%4$s
										</div>
										<a href="%1$s" target="_blank" class="cfgp-rss-link">%6$s</a><br>
										<small class="cfgp-rss-date">~ %7$s</small>
									</div>',
									esc_url($post->post_url),
									esc_html($post->post_title),
									esc_url($post->post_image_medium),
									esc_html($post->post_excerpt),
									esc_url($post->post_url),
									__('Read more at CF Geo Plugin', CFGP_NAME),
									date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
								);
							} else {
								$RSS[]=sprintf('<p class="cfgp-rss-container"><a href="%1$s" target="_blank" class="cfgp-rss-link">%2$s</a><br><small class="cfgp-rss-date">~ %7$s</small></p>',
									esc_url($post->post_url),
									esc_html($post->post_title),
									esc_url($post->post_image_medium),
									esc_html($post->post_excerpt),
									esc_url($post->post_url),
									__('Read more at CF Geo Plugin', CFGP_NAME),
									date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
								);
							}
						}
					}
				}
			}
			
			if(!empty($DASH_RSS))
			{
				$DASH_RSS = '<ul class="rss-widget">' . join("\r\n", $DASH_RSS) . '</ul>';
				CFGP_DB_Cache::set('cfgp-dashboard-rss', $DASH_RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
			}
			
			if(!empty($RSS))
			{
				$RSS = join("\r\n", $RSS);
				CFGP_DB_Cache::set('cfgp-rss', $RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
				echo $RSS;
				exit;
			}
		}
		
		_e('No news for today.', CFGP_NAME);
		exit;
	}
	
	public function ajax__dashboard_rss_feed () {
		$DASH_RSS = CFGP_DB_Cache::get('cfgp-dashboard-rss');
		if( !empty($DASH_RSS) ) {
			echo $DASH_RSS;
			exit;
		} else {
			$RSS = $DASH_RSS = [];
			$data = CFGP_U::curl_get( CFGP_STORE . '/wp-ajax.php?action=cfgp_get_posts_data&numberposts=10&posts_per_page=10', '', array(), false);
			if($data)
			{
				$data = (object)$data;
				if(isset($data->posts) && is_array($data->posts))
				{
					$x = 4;
					foreach($data->posts as $i => $post)
					{
						$post = (object)$post;
						
						$DASH_RSS[]=sprintf('<li><a href="%1$s" target="_blank">%2$s</a></li>', esc_url($post->post_url), esc_html($post->post_title));
						
						if($i <= $x) {
							if($i === 0) {
								$RSS[]=sprintf('<div class="cfgp-rss-container">
										<a href="%1$s" target="_blank" class="cfgp-rss-img">
											<img src="%3$s" class="img-fluid">
										</a>
										<h3>%2$s</h3>
										<div class="cfgp-rss-excerpt">
											%4$s
										</div>
										<a href="%1$s" target="_blank" class="cfgp-rss-link">%6$s</a><br>
										<small class="cfgp-rss-date">~ %7$s</small>
									</div>',
									esc_url($post->post_url),
									esc_html($post->post_title),
									esc_url($post->post_image_medium),
									esc_html($post->post_excerpt),
									esc_url($post->post_url),
									__('Read more at CF Geo Plugin', CFGP_NAME),
									date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
								);
							} else {
								$RSS[]=sprintf('<p class="cfgp-rss-container"><a href="%1$s" target="_blank" class="cfgp-rss-link">%2$s</a><br><small class="cfgp-rss-date">~ %7$s</small></p>',
									esc_url($post->post_url),
									esc_html($post->post_title),
									esc_url($post->post_image_medium),
									esc_html($post->post_excerpt),
									esc_url($post->post_url),
									__('Read more at CF Geo Plugin', CFGP_NAME),
									date(CFGP_DATE_FORMAT, strtotime($post->post_date_gmt))
								);
							}
						}
					}
				}
			}
			
			if(!empty($RSS))
			{
				$RSS = join("\r\n", $RSS);
				CFGP_DB_Cache::set('cfgp-rss', $RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
				
			}
			
			if(!empty($DASH_RSS))
			{
				$DASH_RSS = '<ul class="rss-widget">' . join("\r\n", $DASH_RSS) . '</ul>';
				CFGP_DB_Cache::set('cfgp-dashboard-rss', $DASH_RSS, (MINUTE_IN_SECONDS * CFGP_SESSION));
				echo $DASH_RSS;
				exit;
			}
		}
		
		_e('No news for today.', CFGP_NAME);
		exit;
	}
	
	// Rename county table
	public function rename__cf_geoplugin_country__column ($theme_columns){
		$theme_columns['name'] = __('Country code', CFGP_NAME);
		$theme_columns['description'] = __('Country full name', CFGP_NAME);
		return $theme_columns;
	}
	
	// Rename region table
	public function rename__cf_geoplugin_region__column ($theme_columns){
		$theme_columns['name'] = __('Region code', CFGP_NAME);
		$theme_columns['description'] = __('Region full name', CFGP_NAME);
		return $theme_columns;
	}
	
	// Rename city table
	public function rename__cf_geoplugin_city__column ($theme_columns){
		$theme_columns['name'] = __('City name', CFGP_NAME);
		unset($theme_columns['description']);
		return $theme_columns;
	}
	
	// Rename postcode table
	public function rename__cf_geoplugin_postcode__column ($theme_columns){
		$theme_columns['name'] = __('Postcode', CFGP_NAME);
		unset($theme_columns['description']);
		return $theme_columns;
	}
	
	// Initialize plugin settings
	public function admin_init(){
		$this->plugin_custom_menu_class();
		$this->add_privacy_policy();
	}
	
	// Add privacy policy content
	function add_privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		
		$privacy_policy = array(
			__( 'This site uses the WordPress Geo Plugin (formerly: CF Geo Plugin) to display public visitor information based on IP addresses that can then be collected or used for various purposes depending on the settings of the plugin.', CFGP_NAME ),
			
			__( 'CF Geo Plugin is a GeoMarketing tool that allows you to have full geo control of your WordPress. CF Geo Plugin gives you the ability to attach content, geographic information, geo tags, Google Maps to posts, pages, widgets and custom templates by using simple options, shortcodes, PHP code or JavaScript. It also lets you specify a default geographic location for your entire WordPress blog, do SEO redirection, spam protection, WooCommerce control and many more. CF Geo Plugin help you to increase conversion, do better SEO, capture leads on your blog or landing pages.', CFGP_NAME ),
			
			sprintf(__( 'This website uses API services, technology and goods from the WordPress Geo Plugin and that part belongs to the <a href="%1$s" target="_blank">WordPress Geo Plugin Privacy Policy</a>.', CFGP_NAME ), CFGP_STORE . '/privacy-policy/')
		);
	 
		wp_add_privacy_policy_content(
			__( 'WordPress Geo Plugin', CFGP_NAME ),
			wp_kses_post( wpautop( join((PHP_EOL . PHP_EOL), $privacy_policy), false ) )
		);
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
	
	// Admin bar CSS
	public function admin_bar_menu_css() { if ( is_admin_bar_showing() ) : ?>
<style media="all" id="cfgp-admin-bar-css">
/* <![CDATA[ */
#wpadminbar .ab-top-menu .menupop.<?php echo CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'; ?> .ab-item > .cfgp-ab-icon:before {
	font: normal 20px/1 dashicons;
    content: '\f231';
	position: relative;
    float: left;
    speak: never;
    padding: 4px 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    background-image: none !important;
    margin-right: 6px;
	color: #a7aaad;
    color: rgba(240, 246, 252, 0.6);
}
#wpadminbar .ab-top-menu .menupop .<?php echo CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-activate-link'; ?> .ab-item > .cfgp-ab-icon:before{
	content: '\f155';
}
#wpadminbar .ab-top-menu .menupop.<?php echo CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'; ?>:hover .ab-item > .cfgp-ab-icon:before,
#wpadminbar .ab-top-menu .menupop.<?php echo CFGP_NAME . '.' . CFGP_NAME . '-admin-bar-link'; ?>.hover .ab-item > .cfgp-ab-icon:before {
	color: #72aee6;
}
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course,
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course:focus,
#wpadminbar .ab-top-menu .cf-geoplugin-toolbar-course:hover,
#wpadminbar:not(.mobile) .ab-top-menu > li.cf-geoplugin-toolbar-course > .ab-item:focus,
#wpadminbar.nojq .quicklinks .ab-top-menu > li.cf-geoplugin-toolbar-course > .ab-item:focus,
#wpadminbar:not(.mobile) .ab-top-menu > li.cf-geoplugin-toolbar-course:hover > .ab-item,
#wpadminbar .ab-top-menu > li.cf-geoplugin-toolbar-course.hover > .ab-item{
	background: #443333;
	color: rgba(240, 246, 252, 1);
}
/* ]]> */
</style><?php endif; }
	
	// Add admin top bar menu pages
	public function admin_bar_menu($wp_admin_bar) {
		if ( ! (current_user_can( 'administrator' ) || current_user_can( 'editor' )) ){
			return $wp_admin_bar;
		}
		
		$wp_admin_bar->add_node(array(
			'id' => CFGP_NAME . '-admin-bar-link',
			'title' => '<span class="cfgp-ab-icon"></span>' . __('Geo Plugin', CFGP_NAME), 
			'href' => esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-link',
				'title' => __('Geo Plugin', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-shortcodes-link',
			'title' => __('Shortcodes', CFGP_NAME), 
			'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME)), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-shortcodes-link',
				'title' => __('Shortcodes', CFGP_NAME),
			)
		));
		if(CFGP_Options::get('enable_gmap', false))
		{
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-google-map-link',
				'title' => __('Google Map', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-google-map')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-google-map-link',
					'title' => __('Google Map', CFGP_NAME),
				)
			));
		}
		if(CFGP_Options::get('enable_defender', 1))
		{
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-defender-link',
				'title' => __('Site Protection', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-defender')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-defender-link',
					'title' => __('Site Protection', CFGP_NAME),
				)
			));
		}
		if(CFGP_Options::get('enable_banner', false)) {
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-banner-link',
				'title' => __('Geo Banner', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-banner')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-banner-link',
					'title' => __('Geo Banner', CFGP_NAME),
				)
			));
		}
		if(CFGP_Options::get('enable_seo_redirection', 1))
		{
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-seo-redirection-link',
				'title' => __('SEO Redirection', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-seo-redirection')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-seo-redirection-link',
					'title' => __('SEO Redirection', CFGP_NAME),
				)
			));
		}
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-settings-link',
			'title' => __('Settings', CFGP_NAME), 
			'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-settings')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-settings-link',
				'title' => __('Settings', CFGP_NAME),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => CFGP_NAME . '-admin-bar-link',
			'id' => CFGP_NAME . '-admin-bar-debug-link',
			'title' => __('Debug Mode', CFGP_NAME), 
			'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-debug')), 
			'meta' => array(
				'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-debug-link',
				'title' => __('Debug Mode', CFGP_NAME),
			)
		));
		
		if(CFGP_License::activated()) {
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-activate-link',
				'title' => __('License', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-activate')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-activate-link',
					'title' => __('License', CFGP_NAME),
				)
			));
		} else {
			$wp_admin_bar->add_menu(array(
				'parent' => CFGP_NAME . '-admin-bar-link',
				'id' => CFGP_NAME . '-admin-bar-activate-link',
				'title' => '<span class="cfgp-ab-icon"></span>' . __('Activate Unlimited', CFGP_NAME), 
				'href' => esc_url(CFGP_U::admin_url('admin.php?page=' . CFGP_NAME . '-activate')), 
				'meta' => array(
					'class' => CFGP_NAME . ' ' . CFGP_NAME . '-admin-bar-activate-link',
					'title' => __('Activate Unlimited', CFGP_NAME),
				)
			));
		}
		
		
		// Display Currency Converter in the topbar
		if(
			apply_filters('cfgp/topbar/currency/display', true)
			&& ($currency_converter = CFGP_U::api('currency_converter'))
			&& (CFGP_Options::get('base_currency') != CFGP_U::api('currency'))
		)
		{			
			$money = apply_filters(
				'cfgp/topbar/currency/title',
				sprintf(
					'%s: %s &#8646; %s',
					__('Today\'s course', CFGP_NAME),
					'<span class="cfgp-topbar-currency-from">' . ( 1 . '' . CFGP_Options::get('base_currency') ) . '</span>',
					'<span class="cfgp-topbar-currency-to">' . ( number_format($currency_converter, 2) . '' . CFGP_U::api('currency') ) . '</span>'
				)
			);
			
			$wp_admin_bar->add_node( array(
				'id' => CFGP_NAME . '-course',
				'title' => $money,
				'href' => '',
				'meta'  => array( 'class' => CFGP_NAME . '-toolbar-course' ),
				'parent' => false,
			) );
		}
	}
	
	public function register_style($page){
		
		if(!$this->limit_scripts($page) && $page != 'index.php') return;
		
		wp_enqueue_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), (string)CFGP_VERSION );
		wp_enqueue_style( CFGP_NAME . '-admin', CFGP_ASSETS . '/css/style-admin.css', array(CFGP_NAME . '-fontawesome'), (string)CFGP_VERSION );
	}
	
	// Register CPT and taxonomies scripts
	public function register_scripts_ctp( $page )
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

		if( $post === '' . CFGP_NAME . '-banner' ) $url = sprintf( 'edit.php?post_type=%s', $post );
		else $url = sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s-banner', $post, CFGP_NAME );
		
		wp_enqueue_style( CFGP_NAME . '-cpt', CFGP_ASSETS . '/css/style-cpt.css', 1, (string)CFGP_VERSION, false );
		wp_enqueue_script( CFGP_NAME . '-cpt', CFGP_ASSETS . '/js/script-cpt.js', array('jquery'), (string)CFGP_VERSION, true );
		wp_localize_script(CFGP_NAME . '-cpt', 'CFGP', array(
			'ajaxurl' => CFGP_U::admin_url('admin-ajax.php'),
			'label' => array(
				'unload' => __('Data will lost , Do you wish to continue?',CFGP_NAME),
				'loading' => __('Loading...',CFGP_NAME),
				'not_found' => __('Not Found!',CFGP_NAME),
				'placeholder' => __('Search',CFGP_NAME),
				'taxonomy' => array(
					'country' => array(
						'name' => __('Country code',CFGP_NAME),
						'name_info' => __('Country codes are short (2 letters) alphabetic or numeric geographical codes developed to represent countries and dependent areas, for use in data processing and communications.',CFGP_NAME),
						'description' => __('Country full name',CFGP_NAME),
						'description_info' => __('The name of the country must be written in English without spelling errors.',CFGP_NAME),
					),
					'region' => array(
						'name' => __('Region code',CFGP_NAME),
						'name_info' => __('Region codes are short (2 letters) alphabetic or numeric geographical codes developed to represent countries and dependent areas, for use in data processing and communications.',CFGP_NAME),
						'description' => __('Region full name',CFGP_NAME),
						'description_info' => __('The name of the region must be written in English without spelling errors.',CFGP_NAME),
					),
					'city' => array(
						'name' => __('City name',CFGP_NAME),
						'name_info' => __('The city name must be written in the original city name.',CFGP_NAME),
					),
					'postcode' => array(
						'name' => __('Postcode',CFGP_NAME),
						'name_info' => __('The postcode name must be written in the original international format.',CFGP_NAME),
					)
				)
			),
			'current_url'	=> $url
		));
	}
	
	public function register_scripts($page){
		if( $page != 'nav-menus.php' ) {
			if(!$this->limit_scripts($page)) return;
		}
		
		wp_enqueue_style( CFGP_NAME . '-select2', CFGP_ASSETS . '/css/select2.min.css', 1,  '4.1.0-rc.0' );
		wp_enqueue_script( CFGP_NAME . '-select2', CFGP_ASSETS . '/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true );
		
		if( $page == 'nav-menus.php' ) {
			wp_enqueue_style( CFGP_NAME . '-menus', CFGP_ASSETS . '/css/style-menus.css', array(CFGP_NAME . '-select2'), (string)CFGP_VERSION );
		}
		
		wp_enqueue_script( CFGP_NAME . '-admin', CFGP_ASSETS . '/js/script-admin.js', array('jquery', CFGP_NAME . '-select2'), (string)CFGP_VERSION, true );
		wp_localize_script(CFGP_NAME . '-admin', 'CFGP', array(
			'ajaxurl' => CFGP_U::admin_url('admin-ajax.php'),
			'adminurl' => self_admin_url('/'),
			'label' => array(
				'upload_csv' => __('Select or Upload CSV file',CFGP_NAME),
				'unload' => __('Data will lost , Do you wish to continue?',CFGP_NAME),
				'loading' => __('Loading...',CFGP_NAME),
				'not_found' => __('Not Found!',CFGP_NAME),
				'alert' => array(
					'close' => __('Close',CFGP_NAME)
				),
				'rss' => array(
					'no_news' => __('There are no news at the moment.',CFGP_NAME),
					'error' => __("ERROR! Can't load news feed.",CFGP_NAME)
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
					'filetype' => __('The file must be comma separated CSV type',CFGP_NAME),
					'exit' => __('Are you sure, you want to exit?\nChanges wont be saved!',CFGP_NAME),
					'delete' =>	__('Are you sure, you want to delete this redirection?',CFGP_NAME),
					'missing_url' => __('URL Missing. Please insert URL from your CSV file or choose file from the library.',CFGP_NAME),
				),
				'rest' => array(
					'delete' => __("Are you sure, you want to delete this access token?",CFGP_NAME),
					'error' => __("Can't delete access token because unexpected reasons.",CFGP_NAME),
				),
				'footer_menu' => array(
					'documentation' =>	__('Documentation',CFGP_NAME),
					'contact' => __('Contact',CFGP_NAME),
					'blog' => __('Blog',CFGP_NAME),
					'faq' => __('FAQ',CFGP_NAME),
					'thank_you' => __('Thank you for using',CFGP_NAME)
				),
				'seo_redirection' => array(
					'bulk_delete' => __('Are you sure you want to delete all these SEO redirects? You will no longer be able to recover data. We suggest to you made a backup before deleting.',CFGP_NAME),
					'not_selected' => __('You didn\'t select anything.',CFGP_NAME)
				),
				'select2' => array(
					'not_found' => array(
						'country' => __('Country not found.',CFGP_NAME),
						'region' => __('Region not found.',CFGP_NAME),
						'city' => __('City not found.',CFGP_NAME),
						'postcode' => esc_attr__('Postcode not found.',CFGP_NAME)
					),
					'type_to_search' => array(
						'country' => esc_attr__('Start typing the name of the country.',CFGP_NAME),
						'region' => esc_attr__('Start typing the name of the region.',CFGP_NAME),
						'city' => esc_attr__('Start typing the name of a city.',CFGP_NAME),
						'postcode' => esc_attr__('Start typing the postcode.',CFGP_NAME)
					),
					'searching' => __('Searching, please wait...',CFGP_NAME),
					'removeItem' => __('Remove Item',CFGP_NAME),
					'removeAllItems' => __('Remove all items',CFGP_NAME),
					'loadingMore' => __('Loading more results, please wait...',CFGP_NAME)
				)
			)
		));
	}
	
	/*
	 * Limit scripts
	 */
	public function limit_scripts($page){
		if(strpos($page, CFGP_NAME) !== false) return true;
		return false;
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