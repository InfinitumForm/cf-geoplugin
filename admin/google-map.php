<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page Google Map
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
?>

<div class="clearfix"></div>
<div class="container-fluid" id="cf-geoplugin-page">
    <div class="row">
    	<div class="col-12">
        	<h1 class="h5 mt-3"><i class="fa fa-globe"></i> <?php _e('Google Map',CFGP_NAME); ?></h1>
            <hr>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-sm-9"> 
            <?php do_action( 'page-google-map-before-tab' ) ?>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-dark active" href="#property-list" role="tab" data-toggle="tab"><span class="fa fa-archive"></span> <?php _e('Property List',CFGP_NAME); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#info" role="tab" data-toggle="tab"><span class="fa fa-info"></span> <?php _e('Info & Examples',CFGP_NAME); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#preview" role="tab" data-toggle="tab"><span class="fa fa-globe"></span> <?php _e('Preview',CFGP_NAME); ?></a>
                </li>
                <?php do_action('page-cf-geoplugin-google-map-tab'); ?>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active show" id="property-list">
                    <p class="pr-3 pl-3 pt-3"><?php _e('Google Maps is a desktop web mapping service developed by Google. It offers satellite imagery, street maps, 360° panoramic views of streets (Street View), real-time traffic conditions (Google Traffic), and route planning for traveling by foot, car, bicycle (in beta), or public transportation. CF GeoPlugin allow you to place google map easy in your WordPress blog using simple shortcode <code>[cfgeo_map]</code>.',CFGP_NAME); ?></p>
                    <p class="pr-3 pl-3"><?php _e('In the list below is all settings for the <code>[cfgeo_map]</code> shortcode.',CFGP_NAME); ?></p>
                    <table width="100%" class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Shortcode settings',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Info',CFGP_NAME); ?></strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php do_action( 'page-cf-geopplugin-google-map-shortcode-table' ); ?>
                            <tr>
                                <td><strong>title</strong></td>
                                <td><strong><?php _e('String',CFGP_NAME); ?></strong> - <?php _e('Mouse hover title',CFGP_NAME); ?></td>
                            </tr>
                            <tr>
                                <td><strong>latitude</strong></td>
                                <td><strong><?php _e('Number',CFGP_NAME); ?></strong> - <?php echo sprintf(__('Latitude is an angle (defined below) which ranges from 0° at the Equator to 90° (North or South) at the poles.%s',CFGP_NAME),'<br><br><strong>-'.__('By default is pointed to visitors city or address automaticly.',CFGP_NAME).'</strong>'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>longitude</strong></td>
                                <td><strong><?php _e('Number',CFGP_NAME); ?></strong> - <?php _e('Longitude (shown as a vertical line) is the angular distance, in degrees, minutes, and seconds, of a point east or west of the Prime (Greenwich) Meridian.',CFGP_NAME); ?><br><br><strong>-<?php _e('By default is pointed to visitors city or address automaticly',CFGP_NAME); ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>zoom</strong></td>
                                <td><strong><?php _e('Integer',CFGP_NAME); ?></strong> - <?php _e('Most roadmap imagery is available from zoom levels 0 to 18.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_zoom"]; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>width</strong></td>
                                <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?></strong> - <?php _e('Width of your map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_width"]; ?>.</strong></td>
                            </tr>
                            <tr>
                                <td><strong>height</strong></td>
                                <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?></strong> - <?php _e('Height of your map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_height"]; ?>.</strong></td>
                            </tr>
                            <tr>
                                <td><strong>scrollwheel</strong></td>
                                <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('disables scrollwheel zooming on the map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_scrollwheel"]; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>navigationControl</strong></td>
                                <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('disables navigation on the map. The initial enabled/disabled state of the Map type control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_navigationControl"]; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>mapTypeControl</strong></td>
                                <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('The initial enabled/disabled state of the Map type control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_scaleControl"]; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>scaleControl</strong></td>
                                <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('The initial display options for the Scale control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_mapTypeControl"]; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>draggable</strong></td>
                                <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('the object can be dragged across the map and the underlying feature will have its geometry updated.',CFGP_NAME); ?> <br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_draggable"]; ?>.</strong></td>
                            </tr>
                            <tr>
                                <td><strong>infoMaxWidth</strong></td>
                                <td><strong><?php _e('Integer from 0 to 600',CFGP_NAME); ?></strong> - <?php _e('Maximum width of info popup inside map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo $CF_GEOPLUGIN_OPTIONS["map_infoMaxWidth"]; ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpane" class="tab-pane fade pb-5" id="info">
                    <div class="row">
                        <div class="col-12">
                            <?php do_action('page-cf-geoplugin-debug-tab-info-start'); ?>   
                            <h3 class="h5"><?php _e('Usage, additional attributes and settings',CFGP_NAME); ?></h3>
                            <p class="manage-menus"><?php printf(__("If you whant to place simple google map in your post or page, you just need to place shortcode like this: %s - what will show place on Google map by visitors location.",CFGP_NAME),'<br><code>[cfgeo_map]</code>'); ?></p>
                            
                            <p class="manage-menus"><?php printf(__("If you want to display own company street address inside Google map, you can do that by adding optional atributes %s like on example: %s - what will show your company on Google map.", CFGP_NAME), '<code>longitude</code> and <code>latitude</code>', '<br><code>[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15"]</code>' ); ?></p>

                            <?php
                                $html_map = '<code>[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15" title="My Company Name"]<br>
                                &lt;address&gt;<br>
                                &lt;h3&gt;My Company Name&lt;/h3&gt;<br>
                                &lt;p&gt;No Name Street 35, New York, USA&lt;/p&gt;<br>
                                &lt;p&gt;We have what you need&lt;/p&gt;<br>
                                &lt;/address&gt;<br>
                                [/cfgeo_map]
                                </code>';
                            ?>
                            <p class="manage-menus"><?php printf(__("If you want to use HTML inside map and display info bar: <br>%s", CFGP_NAME), $html_map  ); ?></p>
                        </div>
                    </div>
                </div>
                <div role="tabpane" class="tab-pane mt-0 fade in" id="preview">
                    <div class="card p-0 mt-0 text-body border-0">
                            <div class="card-header bg-transparent border-0">
                                <h1 class="h5"><?php _e( 'Google Map', CFGP_NAME ); ?></h1>  
                            </div>
                            <div class="card-body border-0">
                            <?php
                                if( $CF_GEOPLUGIN_OPTIONS['enable_gmap'] )
								{
									if( !empty( $CFGEO['error'] ) )
                                    {
                                        echo sprintf( __( "Google Map can't be displayed because of error: %s", CFGP_NAME ), $CFGEO['error_message'] );
                                    }
                                    else
                                    {
										if( empty($CF_GEOPLUGIN_OPTIONS['map_api_key']) ){
											echo '<p>';
											_e( 'Google Map API key is not set! Please go to Settings > Google Map to set it.', CFGP_NAME );
											echo '</p>';
										}
                                        echo do_shortcode( '[cfgeo_map width="100%" height="600px" longitude="'.$CFGEO['longitude'].'" latitude="'.$CFGEO['latitude'].'"]
											<address>
												<strong><big>'.$CFGEO['ip'].'</big></strong><br /><br />
												'.$CFGEO['city'].'<br />
												'.$CFGEO['region'].(!empty($CFGEO['region_code'])?' ('.$CFGEO['region_code'].')':'').'<br />
												'.$CFGEO['country'].'<br />
												'.$CFGEO['continent'].(!empty($CFGEO['country_code'])?' ('.$CFGEO['country_code'].')':'').'<br /><br />
												'.$CFGEO['longitude'].', '.$CFGEO['latitude'].'<br /><br />
												'.$CFGEO['timezone'].'
											</address>
										[/cfgeo_map]' );
                                    }
								}
								else
                                {
                                    _e( 'Google Map is not enabled! Please go to Settings to enable it.', CFGP_NAME );
                                }
                            ?>
                            </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="col-sm-3">
            <?php do_action( 'page-cf-geoplugin-google-map-sidebar' ); ?>
        </div> 
    </div>
</div>