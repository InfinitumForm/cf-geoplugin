<?php
$url=CF_GEO_D::URL();

$defender = new CF_Geoplugin_Defender;
$enable=$defender->enable;

$enableForm = ($enable==false ? ' disabled':'');
?>
<h3><span class="fa fa-globe"></span> <?php _e('Google Map Settings',CFGP_NAME); ?></h3>
<?php if($enable==false): ?>
	<?php require_once plugin_dir_path(__FILE__) . '/settings-get-premium.php'; ?>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" action="<?php echo  $url->url; ?>" target="_self" id="settings-form">
<?php if($enable==false): ?>
	<table class="form-table manage-menus">
    	<tbody>
        	<tr>
            	<th scope="row" style="text-align:right">
                	<label for="cf_geo_defender_api_key"><?php _e('Activation KEY',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<input type="text" autocomplete="off" value="" name="cf_geo_defender_api_key" id="cf_geo_defender_api_key"><input type="submit" value="<?php _e('ACTIVATE',CFGP_NAME); ?>" class="button action">
                </td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
    <table class="form-table">
        <tbody>
           <tr>
                <th scope="row" style="width:250px;">
                    <label for="cf_geo_map_api_key">* <?php _e('Google Map JavaScript API Key',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" id="cf_geo_map_api_key" name="cf_geo_map_api_key" value="<?php echo (get_option("cf_geo_map_api_key")!=''?get_option("cf_geo_map_api_key"):''); ?>"> <a onclick="cf_geoplugin_popup('https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true','Google Map API Key','1024','450'); " href="javascript:void(0);"><strong><?php _e('GET API KEY',CFGP_NAME); ?></strong></a><p><?php _e('In some countries Google Maps JavaScript API applications require authentication.',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_latitude"><?php _e('Default Latitude',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" id="cf_geo_map_latitude" name="cf_geo_map_latitude" value="<?php echo (get_option("cf_geo_map_latitude")>0?get_option("cf_geo_map_latitude"):''); ?>"<?php echo $enableForm; ?>><p><?php _e('Leave blank for CF GeoPlugin default support or place custom value',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_longitude"><?php _e('Default Longitude',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" id="cf_geo_map_longitude" name="cf_geo_map_longitude" value="<?php echo (get_option("cf_geo_map_longitude")>0?get_option("cf_geo_map_longitude"):''); ?>"<?php echo $enableForm; ?>><p><?php _e('Leave blank for CF GeoPlugin default support or place custom value',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_width"><?php _e('Default Map Width',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" id="cf_geo_map_width" name="cf_geo_map_width" value="<?php echo (get_option("cf_geo_map_width")>0?get_option("cf_geo_map_width"):'100%'); ?>"<?php echo $enableForm; ?>>
                    <p><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?>)</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_height"><?php _e('Default Map Height',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" id="cf_geo_map_height" name="cf_geo_map_height" value="<?php echo (get_option("cf_geo_map_height")>0?get_option("cf_geo_map_height"):'400px'); ?>"<?php echo $enableForm; ?>>
                    <p><?php _e('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_zoom"><?php _e('Default Max Zoom',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_zoom" id="cf_geo_map_zoom"<?php echo $enableForm; ?>>
                        <?php
                            $zoom=get_option("cf_geo_map_zoom");
                            for($i=1; $i<=18; $i++){
                                echo '<option value="'.$i.'"'.($zoom==$i?' selected':'').'>'.$i.'</option>';
                            }
                        ?>
                    </select> <?php _e('Most roadmap imagery is available from zoom levels 0 to 18.',CFGP_NAME); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_scrollwheel"><?php _e('Zooming',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_scrollwheel" id="cf_geo_map_scrollwheel"<?php echo $enableForm; ?>>
                        <?php
                            $scrollwheel=get_option("cf_geo_map_scrollwheel");
                            for($i=0; $i<=1; $i++){
                                echo '<option value="'.$i.'"'.($scrollwheel==$i?' selected':'').'>'.$optionName[$i].'</option>';
                            }
                        ?>
                    </select> <?php echo sprintf(__("If %s, disables scrollwheel zooming on the map.",CFGP_NAME),'<em>'.__("disabled",CFGP_NAME).'</em>'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_navigationControl"><?php _e('Navigation',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_navigationControl" id="cf_geo_map_navigationControl"<?php echo $enableForm; ?>>
                        <?php
                            $navigationControl=get_option("cf_geo_map_navigationControl");
                            for($i=0; $i<=1; $i++){
                                echo '<option value="'.$i.'"'.($navigationControl==$i?' selected':'').'>'.$optionName[$i].'</option>';
                            }
                        ?>
                    </select> <?php echo sprintf(__("If %s, disables navigation on the map. The initial enabled/disabled state of the Map type control.",CFGP_NAME),'<em>'.__("disabled",CFGP_NAME).'</em>'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_mapTypeControl"><?php _e('Map Type Control',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_mapTypeControl" id="cf_geo_map_mapTypeControl"<?php echo $enableForm; ?>>
                        <?php
                            $mapTypeControl=get_option("cf_geo_map_mapTypeControl");
                            for($i=0; $i<=1; $i++){
                                echo '<option value="'.$i.'"'.($mapTypeControl==$i?' selected':'').'>'.$optionName[$i].'</option>';
                            }
                        ?>
                    </select> <?php _e('The initial enabled/disabled state of the Map type control.',CFGP_NAME); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_scaleControl"><?php _e('Scale Control',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_scaleControl" id="cf_geo_map_scaleControl"<?php echo $enableForm; ?>>
                        <?php
                            $scaleControl=get_option("cf_geo_map_scaleControl");
                            for($i=0; $i<=1; $i++){
                                echo '<option value="'.$i.'"'.($scaleControl==$i?' selected':'').'>'.$optionName[$i].'</option>';
                            }
                        ?>
                    </select> <?php _e('The initial display options for the scale control.',CFGP_NAME); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_draggable"><?php _e('Draggable',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_map_draggable" id="cf_geo_map_draggable"<?php echo $enableForm; ?>>
                        <?php
                            $draggable=get_option("cf_geo_map_draggable");
                            for($i=0; $i<=1; $i++){
                                echo '<option value="'.$i.'"'.($draggable==$i?' selected':'').'>'.$optionName[$i].'</option>';
                            }
                        ?>
                    </select> <?php echo sprintf(__("If %s, the object can be dragged across the map and the underlying feature will have its geometry updated.",CFGP_NAME),'<em>'.__("disabled",CFGP_NAME).'</em>'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_map_infoMaxWidth"><?php _e('Info Box Max Width',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="number" min="1" max="600" id="cf_geo_map_infoMaxWidth" name="cf_geo_map_infoMaxWidth" value="<?php echo (get_option("cf_geo_map_infoMaxWidth")>0?get_option("cf_geo_map_infoMaxWidth"):200); ?>"<?php echo $enableForm; ?>>
                    <p><?php _e('Maximum width of info popup inside map (integer from 0 to 600).',CFGP_NAME); ?></p>
                </td>
            </tr>
         </tbody>
    </table>
</form>