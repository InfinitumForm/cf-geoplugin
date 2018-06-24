<?php
/**
 * wp-admin navigations and actions
 *
 * @link      http://cfgeoplugin.com/
 * @since      3.1.1
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin/include
 */

/**
* wp-admin plugin navigation and actions
* @version   1.0.0
*/
add_action('admin_menu', 'cf_geoplugin_admin_menu', 10);
function cf_geoplugin_admin_menu(){
	add_menu_page(
		__('CF GeoPlugin',CFGP_NAME),
		__('CF GeoPlugin',CFGP_NAME),
		'manage_options',
		'cf-geoplugin',
		'cf_geoplugin_page_geoplugin',
		//plugin_dir_url( dirname( __FILE__ ) ) . 'images/main-menu.png'
		'dashicons-location-alt'
	);
	$cf_geo_enable_gmap=get_option("cf_geo_enable_gmap");
	if($cf_geo_enable_gmap == 'true')
	{
		add_submenu_page(
			'cf-geoplugin',
			__('Google Map',CFGP_NAME),
			__('Google Map',CFGP_NAME),
			'manage_options',
			'cf-geoplugin-google-map',
			'cf_geoplugin_page_google_map'
		);
	}
	
	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) {
		$cf_geo_enable_defender=get_option("cf_geo_enable_defender");
		if($cf_geo_enable_defender == 'true')
		{
			add_submenu_page(
				'cf-geoplugin',
				__('Geo Defender',CFGP_NAME),
				__('Geo Defender',CFGP_NAME),
				'manage_options',
				'cf-geoplugin-defender',
				'cf_geoplugin_page_defender'
			);
		}
		/* ----- Fixed issue with pages. Fixed by: Goran Zivkovic -------- */
		$cf_geo_enable_banner=(get_option("cf_geo_enable_banner")=='true' ? true : false);
		if($cf_geo_enable_banner)
		{
				add_submenu_page(
				'cf-geoplugin',
				__('Geo Banner',CFGP_NAME),
				__('Geo Banner',CFGP_NAME),
				'manage_options',
				'edit.php?post_type=cf-geoplugin-banner'
				);
				
		}
		add_submenu_page(
		'cf-geoplugin',
		__('Countries',CFGP_NAME),
		__('Countries',CFGP_NAME),
		'manage_options',
		'edit-tags.php?taxonomy=cf-geoplugin-country&post_type=cf-geoplugin-banner'
		);
		add_submenu_page(
		'cf-geoplugin',
		__('Regions',CFGP_NAME),
		__('Regions',CFGP_NAME),
		'manage_options',
		'edit-tags.php?taxonomy=cf-geoplugin-region&post_type=cf-geoplugin-banner'
		);
		add_submenu_page(
		'cf-geoplugin',
		__('Cities',CFGP_NAME),
		__('Cities',CFGP_NAME),
		'manage_options',
		'edit-tags.php?taxonomy=cf-geoplugin-city&post_type=cf-geoplugin-banner'
		);
		/* ----------------------------------------------------------------------------- */
		add_submenu_page(
			'cf-geoplugin',
			__('Debug Mode',CFGP_NAME),
			__('Debug Mode',CFGP_NAME),
			'manage_options',
			'cf-geoplugin-debug',
			'cf_geoplugin_page_debug'
		);
		add_submenu_page(
			'cf-geoplugin',
			__('Settings',CFGP_NAME),
			__('Settings',CFGP_NAME),
			'manage_options',
			'cf-geoplugin-settings',
			'cf_geoplugin_page_settings'
		);
		/*add_submenu_page(
			'cf-geoplugin',
			__('F.A.Q.',CFGP_NAME),
			__('F.A.Q.',CFGP_NAME),
			'manage_options',
			'cf-geoplugin-faq',
			'cf_geoplugin_page_faq'
		);*/
		
		if(!CFGP_ACTIVATED)
		{
			if(!get_option("cf_geo_defender_api_key")) {
				add_submenu_page(
					'cf-geoplugin',
					__('Activate Unlimited',CFGP_NAME),
					'<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
					'manage_options',
					'cf-geoplugin-activate',
					'cf_geoplugin_page_activate'
				);
			}
		}
	}
}
function cf_geoplugin_page_geoplugin() {
	include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-geoplugin.php');
}
function cf_geoplugin_page_google_map() {
	include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-google-map.php');
}
function cf_geoplugin_page_defender() {
	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-defender.php');
}
function cf_geoplugin_page_debug() {
	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-debug.php');
}
function cf_geoplugin_page_settings() {

	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-settings.php');
};
function cf_geoplugin_page_activate() {
	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-activate.php');
};
function cf_geoplugin_page_faq() {

}

add_action( 'admin_init','cf_geo_custom_menu_class' );

function cf_geo_custom_menu_class() 
{
    global $menu;
	
	$show = false;
	
	if(isset($_GET['post_type']) && $_GET['post_type'] === 'cf-geoplugin-banner') $show = true;
	if(isset($_GET['taxonomy']) && in_array($_GET['taxonomy'], array('cf-geoplugin-country', 'cf-geoplugin-region','cf-geoplugin-city'), true) !== false) $show = true;

	if(is_array($menu)):
		foreach( $menu as $key => $value )
		{ 
			if( 'CF GeoPlugin' == $value[0] ){
				if($show) $menu[$key][4] = "wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_cf-geoplugin menu-top-first wp-menu-open";
			}
		}
	endif;
}

/**
* Add admin bar menu
*/
add_action( 'admin_bar_menu', 'cf_geoplugin_admin_bar_menu', 900 );
function cf_geoplugin_admin_bar_menu() {
	global $wp_admin_bar;
    $wp_admin_bar->add_node( array(
        'id' => 'cf-geoplugin',
        'title' => '<span  style="
    float:left; width:25px !important; height:25px !important;
    margin-left: 5px !important; margin-top: 2px !important; background:url(\''.plugin_dir_url( dirname( __FILE__ ) ).'images/cf-geo-25x25.png\') no-repeat center center / cover;"></span>',
        'href' => '',
		'meta'  => array( 'class' => 'cf-geoplugin-toolbar-page', 'title'=>sprintf(__("CF GeoPlugin ver.%s",CFGP_NAME),do_shortcode("[cf_geo return=version]"))),
		'parent' => false,
    ) );
	$wp_admin_bar->add_node( array(
        'id' => 'cf-geoplugin-helper',
        'title' => 'CF GeoPlugin',
        'href' => admin_url( 'admin.php?page=cf-geoplugin'),
		'meta'  => array( 'class' => 'cf-geoplugin-toolbar-help-page' ),
		'parent' => 'cf-geoplugin',
    ) );
	$cf_geo_enable_gmap=get_option("cf_geo_enable_gmap");
	if($cf_geo_enable_gmap == 'true')
	{
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-gmap',
			'title' => __('CF Google Map',CFGP_NAME),
			'href' => admin_url( 'admin.php?page=cf-geoplugin-google-map'),
			'meta'  => array( 'class' => 'cf-geoplugin-gmap-toolbar-page' ),
			'parent' => 'cf-geoplugin',
		) );
	}
	if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) {
		$cf_geo_enable_defender=get_option("cf_geo_enable_defender");
		if($cf_geo_enable_defender == 'true')
		{
			$wp_admin_bar->add_node( array(
				'id' => 'cf-geoplugin-defender',
				'title' => __('CF Geo Defender',CFGP_NAME),
				'href' => admin_url( 'admin.php?page=cf-geoplugin-defender'),
				'meta'  => array( 'class' => 'cf-geoplugin-defender-toolbar-page' ),
				'parent' => 'cf-geoplugin',
			) );
		}
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-debug',
			'title' => __('Debug Mode',CFGP_NAME),
			'href' => admin_url( 'admin.php?page=cf-geoplugin-debug'),
			'meta'  => array( 'class' => 'cf-geoplugin-debug-toolbar-page' ),
			'parent' => 'cf-geoplugin',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-setup',
			'title' => __('Settings',CFGP_NAME),
			'href' => admin_url( 'admin.php?page=cf-geoplugin-settings'),
			'meta'  => array( 'class' => 'cf-geoplugin-setup-toolbar-page' ),
			'parent' => 'cf-geoplugin',
		) );
	}
	$wp_admin_bar->add_node( array(
		'id' => 'cf-geoplugin-devider',
		'title' => '<span style="text-align:center; display:block;width:100%;">------------ '.__("Info",CFGP_NAME).' ------------</span>',
		'href' => '',
		'parent' => 'cf-geoplugin',
	) );
	/* Include CF Geop Init class */
	$init=new CF_GEO_D;
	/* Get IP */
	$ip=$init->IP;
	if(in_array($ip,$init->BLACKLIST_IP))
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info',
			'title' => __("Your IP: 0.0.0.0",CFGP_NAME),
			'href' => '',
			'parent' => 'cf-geoplugin',
		) );
	else
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info',
			'title' => '<em>'.__("Your IP",CFGP_NAME).': </em>'.do_shortcode("[cf_geo return=ip]").' ('.do_shortcode("[cf_geo return=ip_version]").')',
			'href' => '',
			'parent' => 'cf-geoplugin',
		) );
	$address = do_shortcode("[cf_geo return=address default='']");
	if(!empty($address))
	{
		
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-city',
			'title' => '<em>'.__("City",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=city default='-']"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-region',
			'title' => '<em>'.__("Region",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=region default='-']"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-country',
			'title' => '<em>'.__("Country",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=country default='-']"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-continent',
			'title' => '<em>'.__("Continent",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=continent default='-']"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-timezone',
			'title' => '<em>'.__("Timezone",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=timezone default='-']"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
		$wp_admin_bar->add_node( array(
			'id' => 'cf-geoplugin-info-status',
			'title' => '<em>'.__("Status",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=status]"),
			'href' => '',
			'parent' => 'cf-geoplugin-info',
		) );
	}
}

/* Plugin page buttons */
add_filter( 'plugin_action_links', 'cf_geoplugin_add_action_plugin', 10, 5 );
function cf_geoplugin_add_action_plugin( $actions, $plugin_file ) 
{
	static $plugin;
	if(!isset($plugin))
		$plugin = plugin_basename(CFGP_FILE);

	if ($plugin == $plugin_file)
	{
		$settings = array('settings' => '<i class="fa fa-cog fa-spin"></i> <a href="'.admin_url( 'admin.php?page=cf-geoplugin-settings').'" target="_self" rel="noopener noreferrer">Settings</a>');
		$faq = array('faq' => '<i class="fa fa-question-circle-o"></i> <a href="http://cfgeoplugin.com/faq" target="_blank" rel="noopener noreferrer">FAQ</a>');
		$vote = array('vote' => '<i class="fa fa-star fa-spin"></i> <a href="https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5#new-topic-0" target="_blank" rel="noopener noreferrer">Vote</a>');
		
		if(CFGP_ACTIVATED)
			$donate = array('donate' => '<i class="fa fa-heartbeat"></i> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com" target="_blank" rel="noopener noreferrer">Donate</a>');
		else
			$donate = array('donate' => '<i class="fa fa-exclamation-triangle"></i> <a href="'.admin_url('admin.php?page=cf-geoplugin-activate').'" rel="noopener noreferrer" title="ACTIVATE UNLIMITED LOOKUP">ACTIVATE</a>');
	
		
		$actions = array_merge($faq, $actions);	
		$actions = array_merge($vote, $actions);
		$actions = array_merge($donate, $actions);
		$actions = array_merge($settings, $actions);
	}		
	return $actions;
}