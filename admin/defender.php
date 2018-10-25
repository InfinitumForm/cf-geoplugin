<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page CF Defender
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
global $wp_version;
?>
<div class="clearfix"></div>
<div class="container-fluid">
    <!-- HEADER -->
    <div class="row mt-3">
        <div class="col-12">
            <h1 class="h5 mt-3">
                <i class="fa fa-lock text-left"></i>
                <?php _e( 'CF Geo Defender', CFGP_NAME ); ?>
            </h1>
            <hr>
        </div>
    </div>
    <?php
    if( isset( $_POST ) && !empty( $_POST ) )
    {
        ?>
        <div class="row mt-3">
            <div class="col-12">
                <?php
                    // Chosen select for empty select does not return anything so I thought this should work
                    if( isset( $_GET['setting'] ) && $_GET['setting'] == 'general' )
                    {
                        if( array_key_exists( 'block_country', $_POST ) === false )
                        {
                            $this->update_option( 'block_country', '' );
                        }
                        if( array_key_exists( 'block_region', $_POST ) === false )
                        {
                            $this->update_option( 'block_region', '' );
                        }
                        if( array_key_exists( 'block_city', $_POST ) === false )
                        {
                            $this->update_option( 'block_city', '' );
                        }
                    }

                    $updates = array();
                    foreach( $_POST as $key => $value )
                    {
                        if( $key == 'block_country' || $key=='block_region' || $key=='block_city' )
                        {
                            $value = implode( ']|[', $value );
                            $updated = $this->update_option( $key, esc_attr( $value ) );
                            $updates[] = (string) $updated[$key];
                        }
                        else
                        {
                            $updated = $this->update_option( $key, esc_attr( $value ) );
                            $updates[] = (string) $updated[$key];
                        }
                    }
                    if( in_array( "false", $updates ) !== false || count( $updates ) == 0 )
                    {
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php _e('There is some error!',CFGP_NAME); ?>
                            </div>
                        <?php
                    }
                    else
                    {
                        ?>
                            <div class="alert alert-success" role="alert">
                                <?php _e('Settings are saved!',CFGP_NAME); ?>
                            </div>
                        <?php
                    }
                ?>
            </div>
        </div>
        <?php
    } 
    $active_general = 'active show';
    $active_page = '';
    if( $this->get( 'setting' ) === 'page' )
    {
        $active_general = '';
        $active_page = 'active show';
    }
    ?>
    <div class="row mt-3">
    	<div class="col-sm-9">
            <?php 
                do_action('page-cf-geoplugin-defender-before-tab'); 
                if( $this->get( 'test', 'bool' ) === true )
                {
                    die( html_entity_decode( stripslashes( $this->get_option('block_country_messages') ) ) );
                    exit;
                }
            ?>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $active_general; ?>" href="#general-defender" role="tab" data-toggle="tab"><span class="fa fa-wrench"></span> General Defender Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $active_page; ?>" href="#defender-page" role="tab" data-toggle="tab"><span class="fa fa-file"></span> Defender page</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&test=true' ); ?>" target="_blank"><span class="fa fa-desktop"></span> Defender test</a>
                </li>
                <?php do_action('page-cf-geoplugin-defender-tab'); ?>
            </ul>
            <!-- Tap panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in <?php echo $active_general; ?>" id="general-defender">
                	<div class="row">
                        <div class="col-12">
                            <p><?php echo sprintf(__("With %s you can block the access from the specific IP, country, state and city to your site. Names of countries, states, regions or cities are not case sensitive, but the name must be entered correctly (in English) to get this feature work correctly. This feature is very safe and does not affect to SEO.",CFGP_NAME),'<strong>'.__("CF Geo Defender",CFGP_NAME).'</strong>'); ?></p>
                            <p><?php _e("Please, don't use this like antispam or antivirus, this option is only to prevent access to vebsite from specific locations. This option will remove all your content, template, design and display custom message to your visitors.", CFGP_NAME); ?></p>
                        </div>
                        <div class="col-12">
                            <form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&settings-updated=true&setting=general'); ?>" target="_self" id="template-options-tab">
                                <div class="form-group">
                                    <label for="block_ip"><?php _e('IP address separated by comma',CFGP_NAME); ?>:</label>
                                    <textarea class="form-control" id="block_ip" name="block_ip" rows="1" cols="3" style="min-height:62px"><?php echo $this->get_option('block_ip'); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="block_country"><?php _e('Choose Countries',CFGP_NAME); ?>:</label>
                                    <?php
                                        if ( version_compare( $wp_version, '4.6', '>=' ) )
                                        {
                                            $all_countries = get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-country',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                        else
                                        {
                                            $all_countries = $this->cf_geo_get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-country',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                    ?>
                                    <select class="chosen-select w-100" data-placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" aria-describedby="countryHelp" id="block_country" name="block_country[]" multiple >
                                    <?php
                                        if( is_array( $all_countries ) && !empty( $all_countries ) )
                                        {
                                            $find = array_map( "trim", explode( "]|[", $this->get_option('block_country') ) );
                                            foreach( $all_countries as $key => $country )
                                            {
                                                echo '<option id="'
                                                .$country->slug
                                                .'" value="'
                                                .$country->slug
                                                .'"'
                                                .(in_array($country->slug, $find)!==false?' selected':'')
                                                .'>'
                                                .$country->name
                                                .' - '.$country->description.'</option>';
                                            }
                                        }
                                    ?>
                                    </select>
                                    <small id="countryHelp" class="form-text text-muted"><?php _e('To setup list of countries, you need to go in Geo Banner -> Countries',CFGP_NAME); ?></small>
                                </div>
                                <div class="form-group">
                                    <label for="block_region"><?php _e('Choose Regions',CFGP_NAME); ?>:</label>
                                    <?php
                                        if ( version_compare( $wp_version, '4.6', '>=' ) )
                                        {
                                            $all_regions = get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-region',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                        else
                                        {
                                            $all_regions = $this->cf_geo_get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-region',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                    ?>
                                <select class="chosen-select w-100" name="block_region[]" id="block_region" data-placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" aria-describedby="regionHelp" multiple> 
                                <?php
                                    if( is_array( $all_regions ) && !empty( $all_regions ) )
                                    {
                                        $find = array_map( "trim", explode( "]|[", $this->get_option('block_region') ) );

                                        foreach( $all_regions as $key => $region )
                                        {
                                            echo '<option id="'
                                            .$region->slug
                                            .'" value="'
                                            .$region->slug
                                            .'"'
                                            .(in_array($region->slug, $find)!==false?' selected':'')
                                            .'>'
                                            .$region->name . '</option>';
                                        }
                                    }
                                ?>
                                </select>
                                <small id="regionHelp" class="form-text text-muted"><?php _e('To setup list of regions, you need to go in Geo Banner -> Regions',CFGP_NAME); ?></small>
                                </div>
                                <div class="form-group">
                                    <label for="block_city"><?php _e('Choose Cities',CFGP_NAME); ?>:</label>
                                    <?php
                                        if ( version_compare( $wp_version, '4.6', '>=' ) )
                                        {
                                            $all_cities = get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-city',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                        else
                                        {
                                            $all_cities = $this->cf_geo_get_terms(array(
                                                'taxonomy'		=> 'cf-geoplugin-city',
                                                'hide_empty'	=> false
                                            ));
                                        }
                                    ?>
                                    <select class="chosen-select w-100" name="block_city[]" id="block_city" data-placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" aria-describedby="cityHelp"  multiple > 
                                    <?php
                                    if( is_array( $all_cities ) && !empty( $all_cities ) )
                                    {
                                        $find = array_map( "trim", explode( "]|[", $this->get_option('block_city') ) );

                                        foreach( $all_cities as $key => $city )
                                        {
                                            echo '<option id="'
                                            .$city->slug
                                            .'" value="'
                                            .$city->slug
                                            .'"'
                                            .(in_array($city->slug, $find)!==false?' selected':'')
                                            .'>'
                                            .$city->name . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <small id="cityHelp" class="form-text text-muted" ><?php _e('To setup list of cities, you need to go in Geo Banner -> Cities',CFGP_NAME); ?></small>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><?php _e( 'Save / Update', CFGP_NAME ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade <?php echo $active_page; ?>" id="defender-page">
                    <div class="row">
                        <div class="col-12">
                            <form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&settings-updated=true&setting=page' ); ?>" target="_self" id="template-options-tab">
                                <div class="form-group">
                                    <label for="block_country_message" ><?php _e('Message that is displayed to a blocked visitor (HTML allowed)',CFGP_NAME); ?>:</label>
                                    <?php
                                        $settings = array( 'textarea_name'  => 'block_country_messages', 'editor_height' => 450 );
                                        $block_country_messages = html_entity_decode( trim( $this->get_option('block_country_messages') ) );
                                        if( empty( $block_country_messages ) )
                                        {
                                            $messages="
                                                <h1>Error</h1>
                                                <h3>404 - Page not found</h3>
                                                <p>We could not find the above page on our servers.</p>
                                                <p>NOTE: This option is not saved!</p>
                                            ";
                                            wp_editor( $messages, 'block_country_messages', $settings );
                                        }
                                        else
                                        {
                                            wp_editor( $block_country_messages, 'block_country_messages', $settings );
                                        }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><?php _e( 'Save / Update', CFGP_NAME ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action('page-cf-geoplugin-defender-after-tab'); ?>
        <div class="col-sm-3">
            <?php do_action('page-cf-geoplugin-defender-sidebar'); ?>
        </div>
    </div>
</div>