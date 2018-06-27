<?php
/**
 * wp-admin navigations and actions
 *
 * @link      http://cfgeoplugin.com/
 * @since     6.0.4
 * @author    Goran Zivkovic
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin/include
 */

/**
* wp-admin plugin navigation and actions
* @version   1.0.0
*/
class CF_GeoPlugin_Admin_Actions
{
    /**
     * Initialize variable for defender option.
     * 
     * @since       6.0.4
     * @access      private
     * @var         bool        $defender       Defender option.
     */
    private $defender = false;

    /**
     * Custom admin menu name.
     * 
     * @since       6.0.4
     * @access      private
     * @var         string      $this->cf_geoplugin_menu_name       Name of our admin menu.
     */
    private $cf_geoplugin_menu_name = 'cf-geoplugin';

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since       6.0.4
     */ 
    public function __construct()
    {
        /**
         * Actions
         */
        add_action( 'admin_menu', array( $this, 'cf_geoplugin_admin_menu' ), 10 );
        add_action( 'admin_init', array( $this, 'cf_geo_custom_menu_class' ) );
        add_action( 'admin_bar_menu', array( $this, 'cf_geoplugin_admin_bar_menu' ), 900 );

        /**
         * Filters
         */
        add_filter( 'plugin_action_links', array( $this, 'cf_geoplugin_add_action_plugin' ), 10, 5 );
    }

    /**
     * Register custom admin menu and submenus.
     * 
     * @since       6.0.4 
     */
    public function cf_geoplugin_admin_menu()
    {
        add_menu_page(
            __('CF GeoPlugin',CFGP_NAME),
            __('CF GeoPlugin',CFGP_NAME),
            'manage_options',
            $this->cf_geoplugin_menu_name,
            array( $this, 'cf_geoplugin_page_geoplugin' ),
            //plugin_dir_url( dirname( __FILE__ ) ) . 'images/main-menu.png'
            'dashicons-location-alt'
        );
        $cf_geo_enable_gmap=get_option("cf_geo_enable_gmap");
        if($cf_geo_enable_gmap == 'true')
        {
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Google Map',CFGP_NAME),
                __('Google Map',CFGP_NAME),
                'manage_options',
                $this->cf_geoplugin_menu_name . '-google-map',
                array( $this, 'cf_geoplugin_page_google_map' )
            );
        }
        
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) 
        {
            $cf_geo_enable_defender=get_option("cf_geo_enable_defender");
            if($cf_geo_enable_defender == 'true')
            {
                add_submenu_page(
                    $this->cf_geoplugin_menu_name,
                    __('Geo Defender',CFGP_NAME),
                    __('Geo Defender',CFGP_NAME),
                    'manage_options',
                    $this->cf_geoplugin_menu_name . '-defender',
                    array( $this, 'cf_geoplugin_page_defender' )
                );
            }
            $cf_geo_enable_banner=get_option("cf_geo_enable_banner");
            if($cf_geo_enable_banner == 'true')
            {
                add_submenu_page(
                    $this->cf_geoplugin_menu_name,
                    __('Geo Banner',CFGP_NAME),
                    __('Geo Banner',CFGP_NAME),
                    'manage_options',
                    'edit.php?post_type=cf-geoplugin-banner'
                );
                    
            }
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Countries',CFGP_NAME),
                __('Countries',CFGP_NAME),
                'manage_options',
                'edit-tags.php?taxonomy=cf-geoplugin-country&post_type=cf-geoplugin-banner'
            );
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Regions',CFGP_NAME),
                __('Regions',CFGP_NAME),
                'manage_options',
                'edit-tags.php?taxonomy=cf-geoplugin-region&post_type=cf-geoplugin-banner'
            );
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Cities',CFGP_NAME),
                __('Cities',CFGP_NAME),
                'manage_options',
                'edit-tags.php?taxonomy=cf-geoplugin-city&post_type=cf-geoplugin-banner'
            );
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Debug Mode',CFGP_NAME),
                __('Debug Mode',CFGP_NAME),
                'manage_options',
                $this->cf_geoplugin_menu_name . '-debug',
                array( $this, 'cf_geoplugin_page_debug' )
            );
            add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('Settings',CFGP_NAME),
                __('Settings',CFGP_NAME),
                'manage_options',
                $this->cf_geoplugin_menu_name . '-settings',
                array( $this, 'cf_geoplugin_page_settings' )
            );
            /*add_submenu_page(
                $this->cf_geoplugin_menu_name,
                __('F.A.Q.',CFGP_NAME),
                __('F.A.Q.',CFGP_NAME),
                'manage_options',
                $this->cf_geoplugin_menu_name . '-faq',
                array( $this, 'cf_geoplugin_page_faq' )
            );*/
            
            if(!CFGP_ACTIVATED)
            {
                if(!get_option("cf_geo_defender_api_key")) {
                    add_submenu_page(
                        $this->cf_geoplugin_menu_name,
                        __('Activate Unlimited',CFGP_NAME),
                        '<span class="dashicons dashicons-star-filled"></span> '.__('Activate Unlimited',CFGP_NAME),
                        'manage_options',
                        $this->cf_geoplugin_menu_name . '-activate',
                        array( $this, 'cf_geoplugin_page_activate' )
                    );
                }
            }
        }  
    }
    
    /**
     * Submenus callbacks.
     * 
     * @since       6.0.4
     */

    /**
     * Submenu: Cf GeoPlugin
     */
    public function cf_geoplugin_page_geoplugin() 
    {
        include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-geoplugin.php');
    }

    /**
     * Submenu: Google Map
     */
    public function cf_geoplugin_page_google_map() 
    {
        include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-google-map.php');
    }

    /**
     * Submenu: Geo Defender
     */
    public function cf_geoplugin_page_defender() 
    {
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-defender.php');
    }

    /**
     * Submenu: Debug Mode
     */
    public function cf_geoplugin_page_debug() 
    {
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-debug.php');
    }

    /**
     * Submenu: Settings
     */
    public function cf_geoplugin_page_settings() 
    {
    
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-settings.php');
    }

    /**
     * Submenu: Activate Unlimited
     */
    public function cf_geoplugin_page_activate() 
    {
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'pages/page-activate.php');
    }

    /**
     * Submenu: F.A.Q.
     */
    public function cf_geoplugin_page_faq() 
    {
    
    }

    /**
     * Fix issue with collapsing admin menu.
     * 
     * @since       6.0.4
     */
    public function cf_geo_custom_menu_class()
    {
        global $menu, $submenu;
	
        $show = false;
        
        if(isset($_GET['post_type']) && $_GET['post_type'] === 'cf-geoplugin-banner') $show = true;
        if(isset($_GET['taxonomy']) && in_array($_GET['taxonomy'], array('cf-geoplugin-country', 'cf-geoplugin-region','cf-geoplugin-city'), true) !== false) $show = true;
    
        if(is_array($menu))
        {
            foreach( $menu as $key => $value )
            { 
                if( 'CF GeoPlugin' == $value[0] )
                {
                    if($show) $menu[$key][4] = "wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_cf-geoplugin menu-top-first wp-menu-open";
                }
            }
        }
    }

    /**
     * Add our menu in admin bar.
     * 
     * @since       6.0.4
     */
    public function cf_geoplugin_admin_bar_menu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->add_node( array(
            'id' => $this->cf_geoplugin_menu_name,
            'title' => '<span  style="
        float:left; width:25px !important; height:25px !important;
        margin-left: 5px !important; margin-top: 2px !important; background:url(\''.plugin_dir_url( dirname( __FILE__ ) ).'images/cf-geo-25x25.png\') no-repeat center center / cover;"></span>',
            'href' => '',
            'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-toolbar-page', 'title'=>sprintf(__("CF GeoPlugin ver.%s",CFGP_NAME),do_shortcode("[cf_geo return=version]"))),
            'parent' => false,
        ) );
        $wp_admin_bar->add_node( array(
            'id' => $this->cf_geoplugin_menu_name . '-helper',
            'title' => 'CF GeoPlugin',
            'href' => admin_url( 'admin.php?page=cf-geoplugin'),
            'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-toolbar-help-page' ),
            'parent' => $this->cf_geoplugin_menu_name,
        ) );
        $cf_geo_enable_gmap=get_option("cf_geo_enable_gmap");
        if($cf_geo_enable_gmap == 'true')
        {
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-gmap',
                'title' => __('CF Google Map',CFGP_NAME),
                'href' => admin_url( 'admin.php?page=cf-geoplugin-google-map'),
                'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-gmap-toolbar-page' ),
                'parent' => $this->cf_geoplugin_menu_name,
            ) );
        }
        if ( current_user_can( 'edit_pages' ) && current_user_can( 'edit_posts' ) ) {
            $cf_geo_enable_defender=get_option("cf_geo_enable_defender");
            if($cf_geo_enable_defender == 'true')
            {
                $wp_admin_bar->add_node( array(
                    'id' => $this->cf_geoplugin_menu_name . '-defender',
                    'title' => __('CF Geo Defender',CFGP_NAME),
                    'href' => admin_url( 'admin.php?page=cf-geoplugin-defender'),
                    'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-defender-toolbar-page' ),
                    'parent' => $this->cf_geoplugin_menu_name,
                ) );
            }
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-debug',
                'title' => __('Debug Mode',CFGP_NAME),
                'href' => admin_url( 'admin.php?page=cf-geoplugin-debug'),
                'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-debug-toolbar-page' ),
                'parent' => $this->cf_geoplugin_menu_name,
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-setup',
                'title' => __('Settings',CFGP_NAME),
                'href' => admin_url( 'admin.php?page=cf-geoplugin-settings'),
                'meta'  => array( 'class' => $this->cf_geoplugin_menu_name . '-setup-toolbar-page' ),
                'parent' => $this->cf_geoplugin_menu_name,
            ) );
        }
        $wp_admin_bar->add_node( array(
            'id' => $this->cf_geoplugin_menu_name . '-devider',
            'title' => '<span style="text-align:center; display:block;width:100%;">------------ '.__("Info",CFGP_NAME).' ------------</span>',
            'href' => '',
            'parent' => $this->cf_geoplugin_menu_name,
        ) );
        /* Include CF Geop Init class */
        $init=new CF_GEO_D;
        /* Get IP */
        $ip=$init->IP;
        if(in_array($ip,$init->BLACKLIST_IP))
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info',
                'title' => __("Your IP: 0.0.0.0",CFGP_NAME),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name,
            ) );
        else
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info',
                'title' => '<em>'.__("Your IP",CFGP_NAME).': </em>'.do_shortcode("[cf_geo return=ip]").' ('.do_shortcode("[cf_geo return=ip_version]").')',
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name,
            ) );
        $address = do_shortcode("[cf_geo return=address default='']");
        if(!empty($address))
        {
            
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-city',
                'title' => '<em>'.__("City",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=city default='-']"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-region',
                'title' => '<em>'.__("Region",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=region default='-']"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-country',
                'title' => '<em>'.__("Country",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=country default='-']"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-continent',
                'title' => '<em>'.__("Continent",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=continent default='-']"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-timezone',
                'title' => '<em>'.__("Timezone",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=timezone default='-']"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
            $wp_admin_bar->add_node( array(
                'id' => $this->cf_geoplugin_menu_name . '-info-status',
                'title' => '<em>'.__("Status",CFGP_NAME).':</em> '.do_shortcode("[cf_geo return=status]"),
                'href' => '',
                'parent' => $this->cf_geoplugin_menu_name . '-info',
            ) );
        }
    }

    /**
     * Add plugin page buttons
     * 
     * @since       6.0.4
     */
    public function cf_geoplugin_add_action_plugin( $actions, $plugin_file ) 
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
}