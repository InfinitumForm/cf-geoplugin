<?php
if (isset($_POST) && count($_POST)>0) {
	// Do the saving
	$front_page_elements = array();

	foreach($_POST as $key=>$val){
		update_option($key, esc_attr($val));
	}
}
?>
<div class="wrap">
    <h1><span class="fa fa-globe"></span> <?php _e('CF Google Map',CFGP_NAME); ?></h1>
    <p><?php _e('Google Maps is a desktop web mapping service developed by Google. It offers satellite imagery, street maps, 360° panoramic views of streets (Street View), real-time traffic conditions (Google Traffic), and route planning for traveling by foot, car, bicycle (in beta), or public transportation. CF GeoPlugin allow you to place google map easy in your WordPress blog using simple shortcode.',CFGP_NAME); ?></p>
    
    <h2 class="nav-tab-wrapper">
    	<a class="nav-tab nav-tab-active" href="#property"><span class="fa fa-cog"></span> <?php _e('Property List',CFGP_NAME); ?></a>
        <a class="nav-tab" href="#info"><span class="fa fa-info"></span> <?php _e('Info & Examples',CFGP_NAME); ?></a>
    </h2>
    
    <div class="nav-tab-body">
    	<div class="nav-tab-item nav-tab-item-active" id="property">
        	<h3>Property List</h3>
            <table width="100%" class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Name',CFGP_NAME); ?></strong></th>
                        <th class="manage-column column-returns column-primary"><strong><?php _e('Info',CFGP_NAME); ?></strong></th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="code">title</td>
                        <td><strong><?php _e('String',CFGP_NAME); ?></strong> - <?php _e('Mouse hover title',CFGP_NAME); ?></td>
                    </tr>
                    <tr>
                        <td class="code">latitude</td>
                        <td><strong><?php _e('Number',CFGP_NAME); ?></strong> - <?php echo sprintf(__('Latitude is an angle (defined below) which ranges from 0° at the Equator to 90° (North or South) at the poles.%s',CFGP_NAME),'<br><br><strong>-'.__('By default is pointed to visitors city or address automaticly.',CFGP_NAME).'</strong>'); ?>
				  </td>
        </tr>
                    <tr>
                        <td class="code">longitude</td>
                        <td><strong><?php _e('Number',CFGP_NAME); ?></strong> - <?php _e('Longitude (shown as a vertical line) is the angular distance, in degrees, minutes, and seconds, of a point east or west of the Prime (Greenwich) Meridian.',CFGP_NAME); ?><br><br><strong>-<?php _e('By default is pointed to visitors city or address automaticly',CFGP_NAME); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">zoom</td>
                        <td><strong><?php _e('Integer',CFGP_NAME); ?></strong> - <?php _e('Most roadmap imagery is available from zoom levels 0 to 18.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_zoom"); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">width</td>
                        <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?></strong> - <?php _e('Width of your map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo (get_option("cf_geo_map_width")); ?>.</strong></td>
        </tr>
                    <tr>
                        <td class="code">height</td>
                        <td><strong><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?></strong> - <?php _e('Height of your map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo (get_option("cf_geo_map_height")); ?>.</strong></td>
        </tr>
                    <tr>
                        <td class="code">scrollwheel</td>
                        <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('disables scrollwheel zooming on the map.',CFGP_NAME); ?>',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_scrollwheel"); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">navigationControl</td>
                        <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('disables navigation on the map. The initial enabled/disabled state of the Map type control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_navigationControl"); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">mapTypeControl</td>
                        <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('The initial enabled/disabled state of the Map type control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_scaleControl"); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">scaleControl</td>
                        <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('The initial display options for the Scale control.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_mapTypeControl"); ?></strong></td>
        </tr>
                    <tr>
                        <td class="code">draggable</td>
                        <td><strong><?php _e('Integer 1 or 0',CFGP_NAME); ?></strong> - <?php _e('If',CFGP_NAME); ?> <em>0</em>, <?php _e('the object can be dragged across the map and the underlying feature will have its geometry updated.',CFGP_NAME); ?> <br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_draggable"); ?>.</strong></td>
        </tr>
                    <tr>
                        <td class="code">infoMaxWidth</td>
                        <td><strong><?php _e('Integer from 0 to 600',CFGP_NAME); ?></strong> - <?php _e('Maximum width of info popup inside map.',CFGP_NAME); ?><br><br><strong>-<?php _e('Default is',CFGP_NAME); ?> <?php echo get_option("cf_geo_map_infoMaxWidth"); ?></strong></td>
        </tr>
                </tbody>
           </table>
        </div>
        <div class="nav-tab-item nav-tab-item-active" id="info">
        	<h3><?php _e('Adding Google Map in wordpress',CFGP_NAME); ?></h3>
            <p class="manage-menus">
			<?php echo sprintf(__("If you whant to place simple google map in your post or page, you just need to place shortcode %s and your visitor will see own city on google map by default. This shortcode have also own properties what you can use to customize your Google map (look property list).",CFGP_NAME),'<code>[cf_geo_map]</code>'); ?>
            <br><br>		
			<?php echo sprintf(__("Like example, you can display own company street address inside Google map like this: %s and pointer will show your street and place where you work.",CFGP_NAME),'<code>[cf_geo_map longitude="-74.0059" latitude="40.7128" zoom="15"]</code>'); ?>
            <br><br>
            <?php _e('Google map also allow you to use HTML inside map and display info bar:',CFGP_NAME); ?>
            <br><br>
            <code>[cf_geo_map longitude="-74.0059" latitude="40.7128" zoom="15" title="<?php _e('My Company Name',CFGP_NAME); ?>"]&nbsp;<br>
                &nbsp;&nbsp;&nbsp;&lt;h3&gt;<?php _e('My Company Name',CFGP_NAME); ?>&lt;h3&gt;&nbsp;<br>
                &nbsp;&nbsp;&nbsp;&lt;p&gt;<?php _e('No Name Street 35, New York, USA',CFGP_NAME); ?>&lt;/p&gt;&nbsp;<br>
                &nbsp;&nbsp;&nbsp;&lt;p&gt;<?php _e('We have what you need',CFGP_NAME); ?>&lt;/p&gt;&nbsp;<br>
            [/cf_geo_map]&nbsp;</code><br><br>
            <?php _e('With this plugin you can easy setup your google map.',CFGP_NAME); ?>
            </p>
        </div>
    </div>
	<?php include plugin_dir_path( __FILE__ ) . 'page-settings/settings-donation.php'; ?>
   <?php 
   	$defender = new CF_Geoplugin_Defender;
	$enable=$defender->enable;
   if($enable==true) : ?>
    <?php echo do_shortcode("[cf_geo_map]<h4>Demo Map</h4><p>".do_shortcode('[cf_geo return="address"]')."</p>[/cf_geo_map]"); ?>
    <?php endif; ?>
   
</div>