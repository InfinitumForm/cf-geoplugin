<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page SEO Redirection Add New
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/
global $wpdb, $wp_version;
?>

<div class="clearfix"></div>
<div class="container-fluid">
    <div class="row">
    	<div class="col-12">
            <h1 class="h5 mt-3"><i class="fa fa-location-arrow"></i> <?php 
            $title = __( 'Add new SEO redirection',CFGP_NAME);
            if( isset( $_GET['id'] ) ) $title = __( 'Update SEO redirection',CFGP_NAME);
           	echo $title; ?>
            </h1>
            <hr>
        </div>
        <div class="col-12" id="alert"></div>
    </div>
    <div class="row">
        <div class="col-9">
            <div class="card col-12 border-light">
                <div class="card-header bg-transparent">
                    <h1 class="h5"><?php _e( 'SEO Redirection Global Params', CFGP_NAME );?></h1>
                </div>
                <div class="card-footer bg-transparent">
                    <?php _e( 'Here you can set global params for SEO Redirection. If you want you can also set them individually in posts or pages.', CFGP_NAME ); ?>
                </div>
                <div class="card-body">
                <?php
                    $redirect_data = array(
                        'active'    => 1,
                        'country'   => '',
                        'region'    => '',
                        'city'      => '',
                        'url'       => '',
                        'http_code' => 302
                    );
                    if( isset( $_GET['id'] ) )
                    {
                        $table_name = self::TABLE['seo_redirection'];
                        $id = absint( $_GET['id'] );
                        $redirect = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$table_name} WHERE id=$id;", ARRAY_A );
                        if( $redirect !== NULL && $wpdb->num_rows > 0 ) $redirect_data = $redirect[0];
                    }
                ?>
                    <form action="<?php echo self_admin_url( 'admin-ajax.php' );?>?action=cf_geo_update_redirect" method="post" id="cf_geo_redirect_form"  class="col-8">
                        <div class="form-group" id="cf_geo_redirect_active">
                            <label for="cf_geo_redirect_active"><?php _e( 'Enable or disable global redirection.' ); ?></label><br>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="cf_geo_redirect_enable" id="cf_geo_enable" value="1" <?php checked( $redirect_data['active'], 1 ); ?>>
                                <label class="form-check-label" for="cf_geo_enable">Enable</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="cf_geo_redirect_enable" id="cf_geo_disable" value="0" <?php checked( $redirect_data['active'], 0 ); ?>>
                                <label class="form-check-label" for="cf_geo_disable">Disable</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cf_geo_country"><?php _e( 'Select Country' ); ?></label>
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
                            <select name="cf_geo_country" id="cf_geo_country" class="form-control" data-placeholder="<?php _e( 'Choose country...', CFGP_NAME ); ?>" aria-describedby="countryHelp">
                            <option value=""><?php _e( 'Choose Country...', CFGP_NAME ); ?></option>
                            <?php
                                if( is_array( $all_countries ) && count( $all_countries ) > 0 )
                                {
                                    foreach( $all_countries as $key => $country )
                                    {
                                        echo '<option id="'
                                        .$country->slug
                                        .'" value="'
                                        .$country->slug
                                        .'"'
                                        .( strtolower($country->slug) == strtolower($redirect_data['country']) ? ' selected':'')
                                        .'>'
                                        .$country->name
                                        .' - '.$country->description.'</option>';
                                    }
                                }
                            ?>
                            </select>
                            <small id="countryHelp" class="form-text text-muted"><?php _e('To setup list of countries, you need to go in Geo Banner -> Countries',CFGP_NAME); ?></small>
                            <div class="invalid-feedback" id="select-country" hidden>
                                Please select country
                            </div>
                        </div>
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
							if( is_array( $all_regions ) && count( $all_regions ) > 0 ) :
						?>
                        <div class="form-group">
                            <label for="cf_geo_region"><?php _e( 'Select Region', CFGP_NAME ); ?></label>
                            <select name="cf_geo_region" id="cf_geo_region" class="form-control" data-placeholder="<?php _e( 'Choose region...', CFGP_NAME ); ?>" aria-describedby="regionHelp">
                            <option value=""><?php _e( 'Choose Region...', CFGP_NAME ); ?></option>
                            <?php
								foreach( $all_regions as $key => $region )
								{
									echo '<option id="'
									.$region->slug
									.'" value="'
									.$region->slug
									.'"'
									.( strtolower($region->slug) == strtolower($redirect_data['region']) ? ' selected':'')
									.'>'
									.$region->name
									.' - '.$region->description.'</option>';
								}
                            ?>
                            </select>
                            <small id="regionHelp" class="form-text text-muted"><?php _e('To setup list of regions, you need to go in Geo Banner -> Regions',CFGP_NAME); ?></small>
                            <div class="invalid-feedback" id="select-region" hidden>
                                Please select region
                            </div>
                        </div>
                        <?php endif; ?>
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
							if( is_array( $all_cities ) && count( $all_cities ) > 0 ) :
						?>
                        <div class="form-group">
                            <label for="cf_geo_city"><?php _e( 'Select City', CFGP_NAME ); ?></label>
                            <select name="cf_geo_city" id="cf_geo_city" class="form-control" data-placeholder="<?php _e( 'Choose city...', CFGP_NAME ); ?>" aria-describedby="cityHelp">
                            <option value=""><?php _e( 'Choose City...', CFGP_NAME ); ?></option>
                            <?php
								foreach( $all_cities as $key => $city )
								{
									echo '<option id="'
									.$city->slug
									.'" value="'
									.$city->slug
									.'"'
									.( strtolower($city->slug) == strtolower($redirect_data['city']) ? ' selected':'')
									.'>'
									.$city->name
									.' - '.$city->description.'</option>';
								}
                            ?>
                            </select>
                            <small id="cityHelp" class="form-text text-muted" ><?php _e('To setup list of cities, you need to go in Geo Banner -> Cities',CFGP_NAME); ?></small>
                            <div class="invalid-feedback" id="select-city" hidden>
                                Please select city
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="cf_geo_redirect_url"><?php _e( 'Redirect URL', CFGP_NAME ); ?></label>
                            <input type="text" id="cf_geo_redirect_url" name="cf_geo_redirect_url" class="form-control" value="<?php echo $redirect_data['url']; ?>" placeholder="http://" aria-describedby="redirectHelp">
                            <small id="redirectHelp" class="form-text text-muted" ><?php _e('URL where you want to redirect',CFGP_NAME); ?></small>
                            <div class="invalid-feedback" id="input-url" hidden>
                                Please enter valid URL
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cf_geo_http_code"><?php _e( 'HTTP Code', CFGP_NAME ); ?></label>
                            <select name="cf_geo_http_code" id="cf_geo_http_code" class="form-control" aria-describedby="httpHelp">
                                <option value="301" <?php selected( $redirect_data['http_code'], 301 ); ?>><?php _e( '301 - Moved Permanently', CFGP_NAME ); ?></option>
                                <option value="302" <?php selected( $redirect_data['http_code'], 302 ); ?>><?php _e( '302 - Moved Temporary', CFGP_NAME ); ?></option>
                                <option value="303" <?php selected( $redirect_data['http_code'], 303 ); ?>><?php _e( '303 - See Other', CFGP_NAME ); ?></option>
                                <option value="404" <?php selected( $redirect_data['http_code'], 404 ); ?>><?php _e( '404 - Not Found (not recommended)', CFGP_NAME ); ?></option>
                            </select>
                            <small id="httpHelp" class="form-text text-muted" ><?php esc_attr_e( 'Select the desired HTTP redirection. (HTTP Code 302 is recommended)', CFGP_NAME ); ?></small>
                        </div>
                        <?php 
                            do_action( 'page-cf-geoplugin-seo-global-params' ); 
                            wp_nonce_field( 'cf_geo_update_redirect', 'cf_geo_update_redirect_nonce' );
                            if( isset( $_GET['id'] ) )
                            {
                                echo '<input type="hidden" name="cf_geo_redirect_action" value="' . absint( $_GET['id'] ) . '">';
                            }
                            else
                            {
                                echo '<input type="hidden" name="cf_geo_redirect_action" value="add-new">';
                            }
                        ?>
                        <button type="submit" class="btn btn-primary"><span class="fa fa-check"></span> <?php _e( 'Save', CFGP_NAME ); ?></button>
                        <a class="btn cf_geo_redirect_cancel" href="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&page_num='. $_GET['page_num'] ); ?>"><?php _e( 'Cancel', CFGP_NAME ); ?></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>