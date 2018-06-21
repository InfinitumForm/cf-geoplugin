<?php

if(isset($_GET['action']) && $_GET['action'] == 'activate_license'): ?>
<div class="notice notice-success is-dismissible"> 
	<p><strong><?php _e('LICENSE ACTIVATED!',CFGP_NAME) ?></strong></p>
    <p><?php
    	printf(
			__('Thank you for using unlimited license. You license is active until %1$s. It would be great to expand your license by that date. After expiration date you will experience plugin limitations.<br>To review or deactivate your license, please go to your %2$s.',CFGP_NAME),
			'<strong>' . date(get_option('date_format') . ' ' . get_option('time_format'),get_option('cf_geo_license_expire')) . '</strong>',
			'<a href="' . get_option('cf_geo_license_url') . '" target="_blank">' . __('CF GeoPlugin account',CFGP_NAME) . '</a>'
		)
	?></p>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text">Dismiss this notice.</span>
	</button>
</div>
<?php endif;

if(isset($_GET['part']) && $_GET['part'] == 'new-version'):
	require_once plugin_dir_path(__FILE__) . 'page-settings/settings-new.php';
else:
	$ip = CFGP_IP;
	$runtime=0;
	if($ip != '0.0.0.0')
		$runtime=do_shortcode('[cf_geo return="runtime"]');
	
	$defender = new CF_Geoplugin_Defender;
	$enable		=	$defender->enable;
?>
<div class="wrap" id="admin-page-geoplugin">
    <h1><span class="fa fa-map-marker"></span> CF GeoPlugin</h1>
                    <p><?php echo sprintf(__('Geo plugin allows you to attach geographic coordinate information to posts by shortcodes. It also lets you specify a default geographic location for your entire WordPress blog. This data is provided by our new API what can locate any user information. The plugin also supports %s, and you can use these shortcodes inside Contact Form 7 builder.',CFGP_NAME),'<a href="http://contactform7.com/" target="_blank">'.__('Contact Form 7',CFGP_NAME).'</a>'); ?></p>
    <h2 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active" href="#shortcodes"><span class="fa fa-code"></span> <?php _e('Shortcodes',CFGP_NAME); ?></a>
        <a class="nav-tab" href="#info"><span class="fa fa-info"></span> <?php _e('Info & Examples',CFGP_NAME); ?></a>
        <?php if($enable===false):?>
    <!--    <a class="nav-tab" href="#get-premium"><span class="fa fa-star-o"></span> <?php _e('Register Premium',CFGP_NAME); ?></a> -->
        <?php endif;?>
    </h2>
                    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                
                
                
                <div class="nav-tab-body">
                    <div class="nav-tab-item nav-tab-item-active" id="shortcodes">          
                        <h3><?php _e('Shortcode & Example',CFGP_NAME); ?></h3>
                        <?php if($ip == '0.0.0.0') : ?>
                        <p style="color:#F00"><strong><?php _e("NOTE: You using CF GeoPlugin on local server and plugin can't return real informations. This data is only for demo like example and are not real. You can place this shortcodes inside content but you will not see results until you place website on the live server.",CFGP_NAME); ?></strong></p>
                        <table width="100%" class="wp-list-table widefat fixed striped pages">
                            <thead>
                                <tr>
                                    <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                    <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="code">[cf_geo]</td>
                                    <td><?php _e('206.226.73.56',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo_flag]</td>
                                    <td><?php echo do_shortcode('[cf_geo_flag country="us"]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip"]</td>
                                    <td><?php _e('206.226.73.56',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_version"]</td>
                                    <td><?php _e('IPv4',CFGP_NAME); ?><?php echo ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>')?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns"]</td>
                                    <td>ip-demo-206.226.73.56.dns.dnsprovider.com<?php echo ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>')?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns_host"]</td>
                                    <td>dnsprovider.com<?php echo ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>')?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns_provider"]</td>
                                    <td>http://dnsprovider.com</td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="address"]</td>
                                    <td><?php _e('Aurora, Illinois, United States',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="city"]</td>
                                    <td><?php _e('Aurora',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="region"]</td>
                                    <td><?php _e('Illinois',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="region_code"]</td>
                                    <td><?php _e('IL',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="country"]</td>
                                    <td><?php _e('United States',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="country_code"]</td>
                                    <td><?php _e('US',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="continent"]</td>
                                    <td><?php _e('America',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="continent_code"]</td>
                                    <td><?php _e('NA',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="area_code"]</td>
                                    <td><?php _e('630',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="dma_code"]</td>
                                    <td><?php _e('602',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="latitude"]</td>
                                    <td><?php _e('41.7606',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="longitude"]</td>
                                    <td><?php _e('-88.3201',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="timezone"]</td>
                                    <td><?php _e('America/Chicago',CFGP_NAME); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency"]</td>
                                    <td><?php _e('USD',CFGP_NAME) . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency_symbol"]</td>
                                    <td><?php _e('&#36;',CFGP_NAME) . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency_converter"]</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="host"]</td>
                                    <td><?php echo rtrim(str_replace(array("http","https","//",":"),"",get_bloginfo('url')),"/"); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_host"]</td>
                                    <td><?php echo CFGP_SERVER_IP; ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="current_date"]</td>
                                    <td><?php echo date("j F, Y"); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="current_time"]</td>
                                    <td><?php echo date("H:i:s"); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="runtime"]</td>
                                    <td>0.0<?php echo sprintf('%05d', mt_rand(1111,99999)); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="status"]</td>
                                   <td>200</td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="credit"]</td>
                                    <td><?php _e('These geographic information Is provided by ',CFGP_NAME); ?><a href="http://cfgeoplugin.com/" target="_blank">CF GeoPlugin</a></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="version"]</td>
                                    <td><?php echo CFGP_VERSION;?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="lookup"]</td>
                                    <td>300</td>
                                </tr>
                            </tbody>
                    
                            <thead>
                                <tr>
                                    <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                    <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                                </tr>
                            </thead>
                        </table>
                        <p style="color:#F00"><strong><?php _e("NOTE: You using CF GeoPlugin on local server and plugin can't return real informations. This data is only for demo like example and are not real. You can place this shortcodes inside content but you will not see results until you place website on the live server.",CFGP_NAME); ?></strong></p>
                        <?php else: ?>
                        <table width="100%" class="wp-list-table widefat fixed striped pages">
                            <thead>
                                <tr>
                                    <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                    <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="code">[cf_geo]</td>
                                    <td><?php echo do_shortcode('[cf_geo]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo_flag]</td>
                                    <td><?php echo do_shortcode('[cf_geo_flag]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_version"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip_version"]'); ?><?php echo ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>')?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip_dns"]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns_host"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip_dns_host"]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_dns_provider"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip_dns_provider"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="address"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="address"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="city"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="city"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="region"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="region"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="region_code"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="region_code"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="country"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="country"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="country_code"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="country_code"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="continent"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="continent"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="continent_code"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="continent_code"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="latitude"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="latitude"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="longitude"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="longitude"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="timezone"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="timezone"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="currency"]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency_symbol"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="currency_symbol"]') . ($enable?'':' - '.'<span style="color:red;">'.__('PRO Version Only', CFGP_NAME).'</span>'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="currency_converter"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="currency_converter"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="host"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="host"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="ip_host"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="ip_host"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="current_date"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="current_date"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="current_time"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="current_time"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="runtime"]</td>
                                    <td><?php echo $runtime; ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="status"]</td>
                                   <td><?php echo do_shortcode('[cf_geo return="status"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="credit"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="credit"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="version"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="version"]'); ?></td>
                                </tr>
                                <tr>
                                    <td class="code">[cf_geo return="lookup"]</td>
                                    <td><?php echo do_shortcode('[cf_geo return="lookup"]'); ?></td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                    <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                                </tr>
                            </thead>
                        </table>
                        <?php endif; ?>
                    </div>
                    <div class="nav-tab-item" id="info">
                        <?php if($ip != '0.0.0.0' && isset($runtime) && !empty($runtime)): ?>
                        <div class="welcome-panel text-big">  
                            <?php _e('Connection quality',CFGP_NAME); ?>: <?php
                                    if(round($runtime)<=0){
                                    echo '<span class="green"><span class="fa fa-battery-full"> '.__('exellent',CFGP_NAME).'</span></span>';
                                }
                                else if(round($runtime) == 1){
                                    echo '<span class="green"><span class="fa fa-battery-three-quarters"> '.__('perfect',CFGP_NAME).'</span></span>';
                                }
                                else if(round($runtime) == 2){
                                    echo '<span class="green"><span class="fa fa-battery-half"> '.__('good',CFGP_NAME).'</span></span>';
                                }
                                else if(round($runtime) == 3){
                                    echo '<span class="orange"><span class="fa fa-battery-quarter"> '.__('week',CFGP_NAME).'</span></span>';
                                }
                                else if(round($runtime) >= 4){
                                    echo '<span class="red"><span class="fa fa-battery-empty"> '.__('bad',CFGP_NAME).'</span></span>';
                                }
                                echo ' ('.$runtime.'ms)';
                            ?>
                        </div>
                        <?php endif; ?>
                        <h3><?php _e('Usage, additional attributes and settings',CFGP_NAME); ?></h3>
                        <p class="manage-menus"><?php echo sprintf(__("If you like to display region (for example California for users who are from California), you just need to use return attribute in your shortcode like this: %s - what will return region name by visitors location.",CFGP_NAME),'<br><code>[cf_geo return="region"]</code>'); ?></p>
                        
                        <p class="manage-menus"><?php echo sprintf(__('If you whant to track some custom IP and return some information from that IP, you can do that by adding one optional attribute %s like on example: %s - what will return area code from that IP address.',CFGP_NAME),'<code>ip</code>','<br><code>[cf_geo ip="127.0.0.1" return="area_code"]</code>'); ?></p>
                        
                        <p class="manage-menus"><?php echo sprintf(__("If you like to ad default values to your shortcode if data is empty you need to add extra attribute in your shortcode like this example: %s - what will return US if geoplugin can't locate country code.",CFGP_NAME),'<br><code>[cf_geo return="country_code" default="US"]</code>'); ?></p>
                        <p class="manage-menus"><?php echo sprintf(__("Sometimes you need to include other HTML, CSS and JavaScript, and jQuery codes inside %s. Sometimes you need to insert a geolocation in input fields. This is not easy but here is one example with jQuery: %s This code will auto fill value of CF7 city field when a visitor visits the contact page.",CFGP_NAME),'<strong>ContactForm7</strong>','<br><br><code>&nbsp;[text* city placeholder "* City"]<br>
&nbsp;&nbsp;[text* country placeholder "* Country"]<br><br>
&lt;script&gt;<br>
jQuery(document).ready(function(){<br>
&nbsp;&nbsp;// Get CF GeoPlugin Data<br>
&nbsp;&nbsp;var city = \'[cf_geo return="city"]\';<br>
&nbsp;&nbsp;var country = \'[cf_geo return="country"]\';<br><br>
&nbsp;&nbsp;// Insert values inside input fields<br>
&nbsp;&nbsp;jQuery("input[name^=\'city\']").val(city);<br>
&nbsp;&nbsp;jQuery("input[name^=\'country\']").val(country);<br>
});<br>
&lt;/script&gt;<br><br>
</code>'); ?></p>

					<h3><?php _e('Displaying Country Flags in text or like image',CFGP_NAME); ?></h3>
					
					<p class="manage-menus"><?php echo sprintf(__("If you like to display country flag in your text like icon, you can do that simple like: %s - and you will see flag in your text.",CFGP_NAME),'<br><code>[cf_geo_flag]</code>'); ?></p>
					
					<p class="manage-menus"><?php echo sprintf(__("If you like to display country flag in your content like image, you can do that also simple using %s or %s attributes like: %s - and you will see image flag in your content",CFGP_NAME),'<code>img</code>','<code>image</code>','<br><code>[cf_geo_flag img]</code>'); ?></p>
					
					<p class="manage-menus"><?php echo sprintf(__("You also can give custom sizes of flags in %s, %s, %s, %s or %s using %s attribute like this: %s - and you will see your flag in that size. %s",CFGP_NAME),'<code>%</code>','<code>px</code>','<code>in</code>','<code>pt</code>','<code>em</code>', '<code>size</code>','<br><code>[cf_geo_flag size="32px"]</code>','<br><strong>-'.__("You can use this size in image and normal text mode also.",CFGP_NAME).'</strong>'); ?></p>
					
					<p class="manage-menus"><?php echo sprintf(__("You also can display custom flag using %s attribute by placing country code simple like: %s - and you will see flag in your text or like image.",CFGP_NAME),'<code>country</code>', '<br><code>[cf_geo_flag country="ca"]</code>'); ?></p>
					
					<p class="manage-menus"><?php echo sprintf(__("We allow you also full controll of this flags and you can place %s, %s or %s attributes to be able use this in any kind of work like this: %s",CFGP_NAME),'<code>css</code>', '<code>class</code>', '<code>id</code>', '<br><code>[cf_geo_flag css="padding:10px;" class="your-custom-class custom-class custom" id="top-flag"]</code>'); ?></p>
                        
                    </div>
                </div>
            </div>
            <?php require_once plugin_dir_path(__FILE__) . 'include/sidebar.php'; ?>
            
        </div>
    </div>
</div>
<?php endif;