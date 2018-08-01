<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Metaboxes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 * @edited     Ivijan-Stefan Stipic
 */

if( !class_exists( 'CF_Geoplugin_Metabox' ) ) :
class CF_Geoplugin_Metabox extends CF_Geoplugin_Global
{
    public function __construct()
    {
        $this->add_action( 'add_meta_boxes', 'create_meta_box' );
        $this->add_action( 'save_post', 'meta_box_save' );
		$this->add_action( 'admin_print_styles-post-new.php', 'metabox_admin_scripts', 11 );
		$this->add_action( 'admin_print_styles-post.php', 'metabox_admin_scripts', 11 );
		
		$this->add_action( 'admin_footer-post-new.php', 'custom_javascript' );
		$this->add_action( 'admin_footer-post.php', 'custom_javascript' );
		
		$this->prefix = CFGP_METABOX;
    }
	
	// Set custom Javascript
	public function custom_javascript(){
		global $CF_GEOPLUGIN_OPTIONS;
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		?>
<script>
/* <![CDATA[ */
(function($){
	(function($$){
		if($($$))
		{
			$($$).each(function(index, element) {
				$(this).chosen({
					no_results_text: "<?php _e('Nothing found!',CFGP_NAME); ?>",
					width: "100%",
					search_contains:true
				});
			});
		}
	}('.chosen-select'));
}(jQuery || window.jQuery));
/* ]]> */
</script>
    <?php }
	
	// Add custom style to metabox
	public function metabox_admin_scripts() {
		global $post_type;
		global $CF_GEOPLUGIN_OPTIONS;
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		if( 'cf-geoplugin-banner' != $post_type)
		{
			wp_register_style( CFGP_NAME . '-choosen-style', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
			wp_enqueue_style( CFGP_NAME . '-choosen-style' );
			
			wp_register_style( CFGP_NAME . '-meta-box', CFGP_ASSETS . '/css/cf-geoplugin-meta-box.css', array(CFGP_NAME . '-choosen-style'), CFGP_VERSION );
			wp_enqueue_style( CFGP_NAME . '-meta-box' );
			
			wp_register_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
			wp_enqueue_script( CFGP_NAME . '-choosen' );
		}
	}

    // Create meta box
    public function create_meta_box()
    {
		global $CF_GEOPLUGIN_OPTIONS;
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		
        $args = array(
            'public'    => true
        );
        $screens = get_post_types( $args, 'names' );
        foreach( $screens as $page )
        {
            add_meta_box(
                CFGP_NAME . '-seo-redirection',
                'Country SEO Redirection',
                array( &$this, 'meta_box_seo_redirection' ),
                $page,
                'side',
				'core'
            );
			
			$this->add_filter( "postbox_classes_{$page}_" . CFGP_NAME . '-seo-redirection', 'meta_box_seo_redirection_start_closed' );
        }
    }
	
	// Keep closed if is not active
	function meta_box_seo_redirection_start_closed( $classes ) {	
		$cfgeo_seo_redirect = $this->get_post_meta( 'seo_redirect' );
		
		if ($cfgeo_seo_redirect) {
			
		}
		else
		{
			array_push( $classes, 'closed' );
		}
		return $classes;
	}

    // Meta box content
    public function meta_box_seo_redirection( $post )
    {
        global $wp_version;
        ?>
        <!-- COUNTRY -->
        <label for="<?php echo $this->prefix; ?>country"><?php _e( 'Choose Country', CFGP_NAME ); ?></label>
            <?php
                if( version_compare( $wp_version, '4.6', '>=' ) )
                {
                    $all_countries = get_terms(array(
                        'taxonomy'      => 'cf-geoplugin-country',
                        'hide_empty'    => false
                    ));
                }
                else
                {
                    $all_countries = $this->cf_geo_get_terms(array(
                        'taxonomy'      => 'cf-geoplugin-country',
                        'hide_empty'    => false
                    ));
                }
            ?>
        <select name="<?php echo $this->prefix; ?>country" id="<?php echo $this->prefix; ?>country" class="postbox chosen-select">
        <option value=""><?php _e( 'Choose Country...', CFGP_NAME ); ?></option>
        <?php
            if( is_array( $all_countries ) && count( $all_countries ) > 0 )
            {
                $selected_country = $this->get_post_meta( 'country' );
                foreach( $all_countries as $key => $country )
                {
                    echo '<option id="'
                    .$country->slug
                    .'" value="'
                    .$country->slug
                    .'"'
                    .( $country->slug === $selected_country ? ' selected':'')
                    .'>'
                    .$country->name
                    .' - '.$country->description.'</option>';
                }
            }
        ?>
        </select>
        <span class="description"><?php esc_attr_e( 'Select the country you want to redirect.', CFGP_NAME ); ?></span><br><br>

        <!-- REGION -->
        <?php
			if( version_compare( $wp_version, '4.6', '>=' ) )
			{
				$all_regions = get_terms(array(
					'taxonomy'      => 'cf-geoplugin-region',
					'hide_empty'    => false
				));
			}
			else
			{
				$all_regions = $this->cf_geo_get_terms(array(
					'taxonomy'      => 'cf-geoplugin-region',
					'hide_empty'    => false
				));
			}
        ?>
        <label for="<?php echo $this->prefix; ?>region"><?php _e( 'Choose Region', CFGP_NAME ); ?></label>
        <select name="<?php echo $this->prefix; ?>region" id="<?php echo $this->prefix; ?>region" class="postbox chosen-select">
        <option value=""><?php _e( 'Choose Region...', CFGP_NAME ); ?></option>
        <?php
		if( is_array( $all_regions ) && count( $all_regions ) > 0 ):
			$selected_region = $this->get_post_meta( 'region' );
			foreach( $all_regions as $key => $region )
			{
				echo '<option id="'
				.$region->slug
				.'" value="'
				.$country->slug
				.'"'
				.( $region->slug === $selected_region ? ' selected':'')
				.'>'
				.$region->name
				.' - '.$region->description.'</option>';
			}
		endif;
        ?>
        </select>
        <span class="description"><?php esc_attr_e( 'Select the region you want to redirect.', CFGP_NAME ); ?></span><br><br>

        <!-- CITY -->
        <?php
			if( version_compare( $wp_version, '4.6', '>=' ) )
			{
				$all_cities = get_terms(array(
					'taxonomy'      => 'cf-geoplugin-city',
					'hide_empty'    => false
				));
			}
			else
			{
				$all_cities = $this->cf_geo_get_terms(array(
					'taxonomy'      => 'cf-geoplugin-city',
					'hide_empty'    => false
				));
			}
        ?>
        <label for="<?php echo $this->prefix; ?>city"><?php _e( 'Choose City', CFGP_NAME ); ?></label>
        <select name="<?php echo $this->prefix; ?>city" id="<?php echo $this->prefix; ?>city" class="postbox chosen-select">
        <option value=""><?php _e( 'Choose City...', CFGP_NAME ); ?></option>
        <?php
			if( is_array( $all_cities ) && count( $all_cities ) > 0 ):
                $selected_city = $this->get_post_meta( 'city' );
                foreach( $all_cities as $key => $city )
                {
                    echo '<option id="'
                    .$city->slug
                    .'" value="'
                    .$city->slug
                    .'"'
                    .( $city->slug === $selected_city ? ' selected':'')
                    .'>'
                    .$city->name
                    .' - '.$city->description.'</option>';
                }
			endif;
        ?>
        </select>
        <span class="description"><?php esc_attr_e( 'Select the city you want to redirect.', CFGP_NAME ); ?></span><br><br>

        <label for="<?php echo $this->prefix; ?>redirect_url"><?php _e( 'Redirect URL', CFGP_NAME ); ?></label>
        <input type="text" id="<?php echo $this->prefix; ?>redirect_url" name="<?php echo $this->prefix; ?>redirect_url" class="large-text" value="<?php echo $this->get_post_meta( 'redirect_url' ); ?>"  placeholder="http://" />
        <span class="description"><?php esc_attr_e( 'URL where you want to redirect.', CFGP_NAME ); ?></span><br><br>

        <label for="<?php echo $this->prefix; ?>http_code"><?php _e( 'HTTP Code' ); ?></label>
        <?php
            $selected_code = $this->get_post_meta( 'http_code' );
			if(empty($selected_code)) $selected_code = 302;
        ?>
        <select name="<?php echo $this->prefix; ?>http_code" id="<?php echo $this->prefix; ?>http_code" class="postbox">
            <option value="301" <?php selected( $selected_code, '301' ); ?>><?php _e( '301 - Moved Permanently', CFGP_NAME ); ?></option>
            <option value="302" <?php selected( $selected_code, '302' ); ?>><?php _e( '302 - Moved Temporary', CFGP_NAME ); ?></option>
            <option value="303" <?php selected( $selected_code, '303' ); ?>><?php _e( '303 - See Other', CFGP_NAME ); ?></option>
            <option value="404" <?php selected( $selected_code, '404' ); ?>><?php _e( '404 - Not Found (not recommended)', CFGP_NAME ); ?></option>
        </select>
        <span class="description"><?php esc_attr_e( 'Select the desired HTTP redirection. (HTTP Code 302 is recommended)', CFGP_NAME ); ?></span><br>

        <br>
        <label for="<?php echo $this->prefix; ?>seo_redirect"><?php _e( 'Enable SEO Redirect', CFGP_NAME ); ?></label>
        <?php
            $checked = $this->get_post_meta( 'seo_redirect' );
            if( empty( $checked ) ) $checked = '0';
        ?>
        <input type="radio" name="<?php echo $this->prefix; ?>seo_redirect" value="1" <?php checked( $checked, '1' ); ?> /> <?php _e( 'Enable', CFGP_NAME ); ?>
        <input type="radio" name="<?php echo $this->prefix; ?>seo_redirect" value="0" <?php checked( $checked, '0' ); ?> /> <?php _e( 'Disable', CFGP_NAME ); ?>
        <?php
    }

    // Save meta box values
    public function meta_box_save( $id )
    {
		global $CF_GEOPLUGIN_OPTIONS;
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		
        update_post_meta( $id, $this->prefix . 'country', $this->post( $this->prefix . 'country' ) );
        update_post_meta( $id, $this->prefix . 'region', $this->post( $this->prefix . 'region' ) );
        update_post_meta( $id, $this->prefix . 'city', $this->post( $this->prefix . 'city' ) );
        update_post_meta( $id, $this->prefix . 'redirect_url', $this->post( $this->prefix . 'redirect_url', 'url' ) );
        update_post_meta( $id, $this->prefix . 'http_code', $this->post( $this->prefix . 'http_code', 'int' ) );
        update_post_meta( $id, $this->prefix . 'seo_redirect', $this->post( $this->prefix . 'seo_redirect' ) );
    }
}
endif;