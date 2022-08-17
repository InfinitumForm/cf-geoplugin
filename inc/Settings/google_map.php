<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

$API = CFGP_Cache::get('API');

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? NULL)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-globe"></i> <?php _e('Google Map', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

        	<div id="post-body">
            	<div id="post-body-content">
                
                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
                        	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#property"><i class="cfa cfa-archive"></i><span class="label"> <?php _e('Property List', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#info"><i class="cfa cfa-info"></i><span class="label"> <?php _e('Info & Examples', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#preview"><i class="cfa cfa-globe"></i><span class="label"> <?php _e('Preview', 'cf-geoplugin'); ?></span></a>
                        </nav>
                        
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="property">
                        	<p><?php 
							printf(
								'%s%s',
								__('Google Maps is a desktop web mapping service developed by Google. It offers satellite imagery, street maps, 360° panoramic views of streets (Street View), real-time traffic conditions (Google Traffic), and route planning for traveling by foot, car, bicycle (in beta), or public transportation.', 'cf-geoplugin'),
								sprintf(
									__('CF GeoPlugin allows you to place google maps easily in your WordPress blog using simple shortcode %s.', 'cf-geoplugin'),
									'<code>[cfgeo_map]</code>'
								)
							)
							?></p>
                            <p><?php
                            	printf(
									__('In the list below are all settings for the %s shortcode.', 'cf-geoplugin'),
									'<code>[cfgeo_map]</code>'
								)
							?></p>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-google-map-property">
                                <thead>
                                    <tr>
                                        <th><?php _e('Shortcode settings', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Info', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<tr>
                                        <td><strong>title</strong></td>
                                        <td><strong><?php _e('String', 'cf-geoplugin'); ?></strong> - <?php _e('Mouse hover title', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>latitude</strong></td>
                                        <td><strong><?php _e('Number', 'cf-geoplugin'); ?></strong> - <?php echo sprintf(__('Latitude is an angle (defined below) which ranges from 0° at the Equator to 90° (North or South) at the poles.%s', 'cf-geoplugin'),'<br><br><strong>-'.__('By default is pointed to visitors city or address automatically.', 'cf-geoplugin').'</strong>'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>longitude</strong></td>
                                        <td><strong><?php _e('Number', 'cf-geoplugin'); ?></strong> - <?php _e('Longitude (shown as a vertical line) is the angular distance, in degrees, minutes, and seconds, of a point east or west of the Prime (Greenwich) Meridian.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('By default is pointed to visitors city or address automatically', 'cf-geoplugin'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>zoom</strong></td>
                                        <td><strong><?php _e('Integer', 'cf-geoplugin'); ?></strong> - <?php _e('Most roadmap imagery is available from zoom levels 0 to 18.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_zoom"); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>width</strong></td>
                                        <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)', 'cf-geoplugin'); ?></strong> - <?php _e('Width of your map.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_width"); ?>.</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>height</strong></td>
                                        <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)', 'cf-geoplugin'); ?></strong> - <?php _e('Height of your map.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_height"); ?>.</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>scrollwheel</strong></td>
                                        <td><strong><?php _e('Integer 1 or 0', 'cf-geoplugin'); ?></strong> - <?php _e('If', 'cf-geoplugin'); ?> <em>0</em>, <?php _e('disables scrollwheel zooming on the map.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_scrollwheel"); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>navigationControl</strong></td>
                                        <td><strong><?php _e('Integer 1 or 0', 'cf-geoplugin'); ?></strong> - <?php _e('If', 'cf-geoplugin'); ?> <em>0</em>, <?php _e('disables navigation on the map. The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_navigationControl"); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>mapTypeControl</strong></td>
                                        <td><strong><?php _e('Integer 1 or 0', 'cf-geoplugin'); ?></strong> - <?php _e('The initial enabled/disabled state of the Map type control.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_scaleControl"); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>scaleControl</strong></td>
                                        <td><strong><?php _e('Integer 1 or 0', 'cf-geoplugin'); ?></strong> - <?php _e('The initial display options for the Scale control.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_mapTypeControl"); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>draggable</strong></td>
                                        <td><strong><?php _e('Integer 1 or 0', 'cf-geoplugin'); ?></strong> - <?php _e('If', 'cf-geoplugin'); ?> <em>0</em>, <?php _e('the object can be dragged across the map and the underlying feature will have its geometry updated.', 'cf-geoplugin'); ?> <br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_draggable"); ?>.</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>infoMaxWidth</strong></td>
                                        <td><strong><?php _e('Integer from 0 to 600', 'cf-geoplugin'); ?></strong> - <?php _e('Maximum width of info popup inside map.', 'cf-geoplugin'); ?><br><br><strong>-<?php _e('Default is', 'cf-geoplugin'); ?> <?php echo CFGP_Options::get("map_infoMaxWidth"); ?></strong></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Shortcode settings', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Info', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                        	</table>
                        </div>
                        
                        <div class="cfgp-tab-panel" id="info">
                        	<table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-google-map-info">
                                <thead>
                                    <tr>
                                        <th><?php _e('Usage, additional attributes and settings', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<tr>
                                        <td><?php printf(__('If you want to place a simple google map in your post or page, you just need to place a shortcode like this: %s - what will show place on Google map by visitors location.', 'cf-geoplugin'),'<br><br><code>[cfgeo_map]</code>'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php printf(__('If you want to display your own company street address inside Google map, you can do that by adding optional attributes %s like on example: %s - what will show your company on Google map.', 'cf-geoplugin'), __('<code>longitude</code> and <code>latitude</code>', 'cf-geoplugin'), '<br><br><code>[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15"]</code>' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php 
											$html_map = '<code>[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15" title="'.__('My Company Name', 'cf-geoplugin').'"]<br>
											&lt;address&gt;<br>
											&lt;h3&gt;'.__('My Company Name', 'cf-geoplugin').'&lt;/h3&gt;<br>
											&lt;p&gt;No Name Street 35, New York, USA&lt;/p&gt;<br>
											&lt;p&gt;'.__('We have what you need', 'cf-geoplugin').'&lt;/p&gt;<br>
											&lt;/address&gt;<br>
											[/cfgeo_map]
											</code>';
											printf(__("If you want to use HTML inside map and display info bar: <br><br>%s", 'cf-geoplugin'), $html_map  );
										?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="cfgp-tab-panel" id="preview"><?php
                        	echo do_shortcode( '[cfgeo_map width="100%" height="600px" longitude="'.$API['longitude'].'" latitude="'.$API['latitude'].'"]
								<address>
									<strong><big>'.CFGP_U::admin_country_flag($API['country_code']).' '.$API['ip'].'</big></strong><br /><br />
									'.$API['city'].'<br />
									'.$API['region'].(!empty($API['region_code'])?' ('.$API['region_code'].')':'').'<br />
									'.$API['country'].'<br />
									'.$API['continent'].(!empty($API['country_code'])?' ('.$API['country_code'].')':'').'<br /><br />
									'.$API['longitude'].', '.$API['latitude'].'<br /><br />
									'.$API['timezone'].'
								</address>
							[/cfgeo_map]' );
						?></div>
                        
                    </div>
                    
                </div>
            </div>
			
			<div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-google_map-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action('cfgp/page/google_map/sidebar'); ?>
				</div>
			</div>
			
            <br class="clear">
        </div>
    </div>
</div>
