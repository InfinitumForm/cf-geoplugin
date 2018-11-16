<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Metaboxes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 * @edited     Ivijan-Stefan Stipic
 */

if( !class_exists( 'CF_Geoplugin_Metabox' ) ) :
class CF_Geoplugin_Metabox extends CF_Geoplugin_Global
{
    // CF Metaboxes prefix
    private $prefix = '';

    public function __construct()
    {
        $this->prefix = CFGP_METABOX;

        $this->add_action( 'add_meta_boxes', 'create_meta_box' );
        $this->add_action( 'save_post', 'meta_box_save' );
		$this->add_action( 'admin_enqueue_scripts', 'metabox_admin_scripts' );
		
		$this->add_action( 'admin_footer-post-new.php', 'custom_javascript' );
        $this->add_action( 'admin_footer-post.php', 'custom_javascript' );
    }
	
	// Set custom Javascript
	public function custom_javascript(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		?>
<script>
/* <![CDATA[ */
(function($){

}(jQuery || window.jQuery));
/* ]]> */
</script>
    <?php }
	
	// Add custom style to metabox
	public function metabox_admin_scripts( $hook_suffix ) {
        $screen = get_current_screen();
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		if( in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) && isset( $screen->post_type ) && $screen->post_type != 'cf-geoplugin-banner' )
		{            
            wp_register_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), '4.7.0' );
            wp_enqueue_style( CFGP_NAME . '-fontawesome' );
            
            wp_register_style( CFGP_NAME . '-choosen-style', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
			wp_enqueue_style( CFGP_NAME . '-choosen-style' );
			
			wp_register_style( CFGP_NAME . '-meta-box', CFGP_ASSETS . '/css/cf-geoplugin-meta-box.css', array(CFGP_NAME . '-choosen-style'), CFGP_VERSION );
			wp_enqueue_style( CFGP_NAME . '-meta-box' );
			
			wp_register_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
            wp_enqueue_script( CFGP_NAME . '-choosen' );
            
            wp_register_script( CFGP_NAME . '-meta-box', CFGP_ASSETS . '/js/cf-geoplugin-metabox.js', array( 'jquery' ), CFGP_VERSION, true );
            wp_localize_script( 
                CFGP_NAME . '-meta-box', 
                'CFGP_META',
                array(
                    'ajax_url'      		=> self_admin_url( 'admin-ajax.php' ),
                    'no_result'     		=> __( 'Nothing found!',CFGP_NAME ),
                    'remove_redirection'	=> __( 'Remove Redirection', CFGP_NAME ),
                    'add_redirection'       => __( 'Add New Redirection', CFGP_NAME ),
					'reset_redirection'		=> __( 'Reset Redirection', CFGP_NAME )
                )
            );
            wp_enqueue_script( CFGP_NAME . '-meta-box' );
		}
	}

    // Create meta box
    public function create_meta_box()
    {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		
        $args = array(
            'public'    => true
        );
        $screens = get_post_types( $args, 'names' );
        foreach( $screens as $page )
        {
            add_meta_box(
                CFGP_NAME . '-seo-redirection',
                __( 'SEO Redirections', CFGP_NAME ),
                array( &$this, 'meta_box_seo_redirection' ),
                $page,
                'normal',
				'low'
            );
        }
   
        add_meta_box(
            CFGP_NAME . '-banner-sc',
            __( 'CF Geoplugin Shortcode', CFGP_NAME ),
            array( &$this, 'banner_shortcode' ),
            CFGP_NAME . '-banner',
            'side',
            'high'
        );   
            
    }

    // Meta box content
    public function meta_box_seo_redirection( $post )
    {
        $this->add_redirections();
    }

    // Save meta box values
    public function meta_box_save( $id )
    {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
        if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;

        // Delete old data
        $this->get_old_seo_meta( $id );
        
        if( isset( $_POST[ $this->prefix ] ) ) 
        {
            foreach( $_POST[ $this->prefix ] as $i => $value )
            {
                if( isset( $value['redirect_url'] ) ) 
                {
                    $value['redirect_url'] = $this->addhttp( $value['redirect_url'] );
                    $value['redirect_url'] = esc_url_raw( $value['redirect_url'] );
                }
            }
            update_post_meta( $id, $this->prefix . 'redirection', array_values( $_POST[ $this->prefix ] ) ); // Reindex array beacuse of deleted repeaters. 0 = start
        }
        else update_post_meta( $id, $this->prefix . 'redirection', NULL );
    }

    /**
     * Add blank redirection form
     */
    public function add_redirections()
    {
        $redirection_data = $this->get_post_meta( 'redirection' );

        global $wp_version;
        if( version_compare( $wp_version, '4.6', '>=' ) )
        {
            $all_countries = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-country',
                'hide_empty'    => false
            ));

            $all_regions = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-region',
                'hide_empty'    => false
            ));

            $all_cities = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-city',
                'hide_empty'    => false
            ));
        }
        else
        {
            $all_countries = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-country',
                'hide_empty'    => false
            ));

            $all_regions = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-region',
                'hide_empty'    => false
            ));

            $all_cities = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-city',
                'hide_empty'    => false
            ));
        }
        
        $init = false;
        if( empty( $redirection_data ) || !is_array( $redirection_data ) ) 
        {
            $redirection_data = array( 0 => array( 'country' => array(), 'region' => array(), 'city' => array(), 'http_code' => '302', 'seo_redirect' => '0' ) ); // Make sure to execute system below at least once
            $init = true;
        }

        $add_redirection = $this->get_old_seo_meta( false );
        if( !empty( $add_redirection ) && $init ) $redirection_data[0] = $add_redirection;
        elseif( !empty( $add_redirection ) ) $redirection_data[] = $add_redirection; // If someone decide to switch to old version and then go back to new prevent data collision

        $end = count( $redirection_data );
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed posts striped cfgeo-post-redirect-table">
        <?php
            foreach( $redirection_data as $i => $value ) 
            {
        ?>
            <tbody>
            <tr class="repeating">
                <td>
                    <table class="wp-list-table widefat fixed posts cfgeo-post-redirect-table-form">
                        <tbody>
                        <tr>
                            <td>
                                <!-- COUNTRY -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country]"><?php _e( 'Choose Countries', CFGP_NAME ); ?></label><br>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country][]" placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country]" class="widefat fixed chosen-select" multiple>
                                <?php
                                    if( is_array( $all_countries ) && !empty( $all_countries ) )
                                    {
                                        foreach( $all_countries as $key => $country )
                                        {
                                            echo '<option id="'
												.$country->slug
												.'" value="'
												.$country->slug
												.'"'
												.( isset( $value['country'] ) && in_array( $country->slug, $value['country'] ) ? ' selected':'')
												.'>'
												.$country->name
												.' - '.$country->description.'</option>';
                                        }
                                    }
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the country you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <!-- REGION -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region]"><?php _e( 'Choose Regions', CFGP_NAME ); ?></label>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region][]" placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region]" class="chosen-select widefat fixed" multiple>
                                <?php
                                if( is_array( $all_regions ) &&  !empty( $all_regions ) ):
                                    foreach( $all_regions as $key => $region )
                                    {
                                        echo '<option id="'
											.$region->slug
											.'" value="'
											.$region->slug
											.'"'
											.( isset( $value['region'] ) && in_array( $region->slug, $value['region'] ) ? ' selected':'')
											.'>'
											.$region->name
											.' - '.$region->description.'</option>';
                                    }
                                endif;
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the region you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <!-- CITY -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city]"><?php _e( 'Choose City', CFGP_NAME ); ?></label>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city][]" placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city]" class="chosen-select widefat fixed" multiple>
                                <?php
                                    if( is_array( $all_cities ) && !empty( $all_cities ) ):
                                        foreach( $all_cities as $key => $city )
                                        {
                                            echo '<option id="'
												.$city->slug
												.'" value="'
												.$city->slug
												.'"'
												.( isset( $value['city'] ) && in_array( $city->slug, $value['city'] ) ? ' selected':'')
												.'>'
												.$city->name
												.' - '.$city->description.'</option>';
                                        }
                                    endif;
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the city you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]"><?php _e( 'Redirect URL', CFGP_NAME ); ?></label>
                                <input type="text" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]" class="large-text" value="<?php echo isset( $value['url'] ) ? $value['url'] : ''; ?>"  placeholder="http://" />
                                <span class="description"><?php esc_attr_e( 'URL where you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]"><?php _e( 'HTTP Code' ); ?></label>
                                <?php
                                    if( !isset( $value['http_code'] ) || empty( $value['http_code'] ) ) $value['http_code'] = '302';
                                ?>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]" class="cfgp-chosen widefat http_select">
                                <?php
                                
									$redirections = array(
										301 => __( '301 - Moved Permanently', CFGP_NAME ),
										302 => __( '302 - Moved Temporary', CFGP_NAME ),
										303 => __( '303 - See Other', CFGP_NAME ),
										404 => __( '404 - Not Found (not recommended)', CFGP_NAME )
									);
									foreach($redirections as $http_code => $http_name)
									{
										echo '<option value="' . $http_code . '" ' . selected( $value['http_code'], $http_code ) .'>' . $http_name . '</option>';
									}
								?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the desired HTTP redirection. (HTTP Code 302 is recommended)', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <label><?php _e( 'Enable SEO Redirect', CFGP_NAME ); ?></label><br />
                                <?php
                                    if( !isset( $value['seo_redirect'] ) || empty( $value['seo_redirect'] ) ) $value['seo_redirect'] = '0';
                                ?>
                                <div class="cfgp-enable-redirection">
                                    <label for="seo_redirect_checkbox_<?php echo $i; ?>_1"><input id="seo_redirect_checkbox_<?php echo $i; ?>_1" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][seo_redirect]" value="1" <?php checked( $value['seo_redirect'], '1' ); ?> /> <?php _e( 'Enable', CFGP_NAME ); ?> </label>&nbsp;&nbsp;
                                    </label for="seo_redirect_checkbox_><?php echo $i; ?>_0"><input id="seo_redirect_checkbox_<?php echo $i; ?>_0" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][seo_redirect]" value="0" <?php checked( $value['seo_redirect'], '0' ); ?> /> <?php _e( 'Disable', CFGP_NAME ); ?></label>
                                </div>
                                <div class="cfgp-add-remove-redirection" style="text-align:right">
                                    <?php
                                        if( $i+1 == $end ) printf( '<a class="cfgp-repeat cfgp-first-repeater" href="#" title="%s"><i class="fa fa-plus-circle fa-2x" style="color: green;"></i></a>&nbsp;&nbsp;&nbsp', __( 'Add New Redirection', CFGP_NAME ) );
                                        
                                        if( $i == 0 && $end == 1 ) printf('<a class="cfgp-reset-fields" href="#" title="%s"><i class="fa fa-repeat fa-2x" style="color: red;"></i></a>', __( 'Reset Redirection', CFGP_NAME ) );
                                        else printf( '<a class="cfgp-destroy-repeat" href="#" title="%s"><i class="fa fa-minus-circle fa-2x" style="color: red;"></i></a>', __( 'Remove Redirection', CFGP_NAME ) );
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                            if( !empty( $add_redirection ) )
                            {
                                ?>
                                <tr>
                                    <td colspan="3">
                                        <h4><?php _e( 'Old redirection data is found. If you want to merge update post, after save/update old data will be deleted. Redirection will work normally without save by old data.', CFGP_NAME ); ?></h4>
                                    </td>
                                </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        <?php } ?>
        </table>
        <?php
        echo ob_get_clean();
    }

    /**
     * Get old seo redirection postmeta
     */
    public function get_old_seo_meta( $delete )
    {
        $old_redirection = array(
            'country',
            'region',
            'city',
            'redirect_url',
            'http_code',
            'seo_redirect',
        );
        $add_redirection = array();
        
        foreach( $old_redirection as $i => $meta_key )
        {
            if( $delete !== false ) 
            {
                delete_post_meta( $delete, $this->prefix . $meta_key );
                continue;
            }

            $meta_value = $this->get_post_meta( $meta_key );
        
            if( $meta_key == 'redirect_url' ) $meta_key = 'url';

            if( !empty( $meta_value ) ) 
            {
                if( in_array( $meta_key, array( 'country', 'region', 'city' ) ) ) $meta_value = array( $meta_value );

                $add_redirection[ $meta_key ] = $meta_value;
            }
        }

        if( $delete === false ) return $add_redirection;
    }

    /**
     * Geo banner shortcode metabox
     */
    public function banner_shortcode( $post )
    {
        echo '<ul>';
        echo '<li><strong>' . __('Standard',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]</code></li>';
        echo '<li><strong>' . __('Advanced',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]' . __('Default content',CFGP_NAME) . '[/cfgeo_banner]</code></li>';
        echo '</ul>';
    }
}
endif;