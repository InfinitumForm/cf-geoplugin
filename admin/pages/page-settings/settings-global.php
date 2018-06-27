<?php
$url=CF_GEO_D::URL();

$defender = new CF_Geoplugin_Defender;
$enable=$defender->enable;

$enableForm = ($enable==false ? ' disabled':'');
?>
<h3><span class="fa fa-cog"></span> <?php _e('Global Plugin Settings',CFGP_NAME); ?></h3>
<?php if($enable==false): ?>
	<?php require_once plugin_dir_path(__FILE__) . '/settings-get-premium.php'; ?>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" action="<?php echo  $url->url; ?>" target="_self" id="settings-form">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="cf_geo_enable_banner"><?php _e('Enable Country Flags',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_flag" id="cf_geo_enable_flag"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_flag")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_flag")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/Disable Geo Banner.",CFGP_NAME); ?></p>
                </td>
			</tr>
			<tr>
				<th scope="row">
                    <label for="cf_geo_enable_banner"><?php _e('Enable SEO Redirection',CFGP_NAME); ?>:</label>
                </th>
				<td>
                    <select name="cf_geo_enable_seo_redirection" id="cf_geo_enable_seo_redirection"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_seo_redirection")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_seo_redirection")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/Disable Geo Banner.",CFGP_NAME); ?></p>
                </td>
            </tr>
        	<tr>
                <th scope="row">
                    <label for="cf_geo_enable_banner"><?php _e('Enable Geo Banner',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_banner" id="cf_geo_enable_banner"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_banner")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_banner")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/Disable Geo Banner.",CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_enable_gmap"><?php _e('Enable Google Map',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_gmap" id="cf_geo_enable_gmap">
                        <option value="true"<?php echo (get_option("cf_geo_enable_gmap")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_gmap")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/Disable Google Map.",CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_enable_defender"><?php _e('Enable Geo Defender',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_defender" id="cf_geo_enable_defender">
                        <option value="true"<?php echo (get_option("cf_geo_enable_defender")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_defender")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/Disable Geo Defender.",CFGP_NAME); ?></p>
                </td>
            </tr>
        	<tr>
                <th scope="row">
                    <label for="cf_geo_enable_cloudflare"><?php _e('Enable Cloudflare',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_cloudflare" id="cf_geo_enable_cloudflare"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_cloudflare")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_cloudflare")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/disable Cloudflare connection.",CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_enable_dns_lookup"><?php _e('Enable DNS Lookup',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_dns_lookup" id="cf_geo_enable_dns_lookup"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_dns_lookup")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_dns_lookup")!="true" ?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("ATTENTION! Sometimes this can slowdown your WordPress blog.",CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_onyly_timezone"><?php _e('Enable SSL',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_ssl" id="cf_geo_enable_ssl"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_ssl")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_ssl")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select>
                </td>
            </tr>
            <tr style="display:none">
                <th scope="row">
                    <label for="cf_geo_connection_timeout"><?php _e('API Connection Timeout',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="number" id="cf_geo_connection_timeout" name="cf_geo_connection_timeout" value="<?php echo (get_option("cf_geo_connection_timeout")>0?get_option("cf_geo_connection_timeout"):9); ?>" min="1" max="50"<?php echo $enableForm; ?>>
                    <p><?php _e('Timeout for the API connection phase (Default is 9)',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr style="display:none">
                <th scope="row">
                    <label for="cf_geo_connection_timeout"><?php _e('API Timeout',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="number" id="cf_geo_connection_timeout" name="cf_geo_timeout" value="<?php echo (get_option("cf_geo_timeout")>0?get_option("cf_geo_timeout"):9); ?>" min="1" max="50"<?php echo $enableForm; ?>>
                    <p><?php _e('Set maximum time of the API request what is allowed to take (Default is 9)',CFGP_NAME); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_enable_proxy"><?php _e('Enable Proxy',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <select name="cf_geo_enable_proxy" id="cf_geo_enable_proxy"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo (get_option("cf_geo_enable_proxy")=="true"?' selected':''); ?>><?php _e('YES',CFGP_NAME); ?></option>
                        <option value="false"<?php echo (get_option("cf_geo_enable_proxy")!="true"?' selected':''); ?>><?php _e('NO',CFGP_NAME); ?></option>
                    </select> IP:
                    <input name="cf_geo_enable_proxy_ip" type="text" value="<?php echo get_option("cf_geo_enable_proxy_ip"); ?>"<?php echo $enableForm; ?> style="width:180px" max="45" maxlength="45" disabled>
                     PORT:<input name="cf_geo_enable_proxy_port" type="text" value="<?php echo get_option("cf_geo_enable_proxy_port"); ?>"<?php echo $enableForm; ?> style="width:60px" max="6" maxlength="6" disabled> - <a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=14042&url_id=902" target="_blank"><?php _e('Need Proxy? We have Recommended Service For You.',CFGP_NAME); ?></a><br><br>
                     Username <input name="cf_geo_enable_proxy_username" type="text" value="<?php echo get_option("cf_geo_enable_proxy_username"); ?>"<?php echo $enableForm; ?> style="width:180px" max="45" maxlength="45" disabled>
                     Password <input name="cf_geo_enable_proxy_password" type="password" value="<?php echo get_option("cf_geo_enable_proxy_password"); ?>"<?php echo $enableForm; ?> style="width:180px" max="45" maxlength="45" disabled>
                     <div class="manage-menus">
                        <strong><?php _e('NOTE & INFO',CFGP_NAME); ?></strong><br><br><?php _e('Some servers not share real IP because of security reasons or IP is blocked from geolocation. Using proxy you can bypass that protocols and enable geoplugin to work properly. Also, this option on individual servers can cause inaccurate geo informations, and because of that this option is disabled by default. You need to test this option on your side and use.',CFGP_NAME); ?>
                     </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cf_geo_auto_update"><?php _e('Enable Plugin Auto-Update', CFGP_NAME); ?></label>
                </th>
                <td>
                    <select name="cf_geo_auto_update" id="cf_geo_auto_update"<?php echo $enableForm; ?>>
                        <option value="true"<?php echo ( get_option("cf_geo_auto_update") == "true" ? 'selected' : '' ); ?>><?php _e('YES', CFGP_NAME); ?></option>
                        <option value="false"<?php echo ( get_option("cf_geo_auto_update") != "true" ? 'selected' : '' ); ?>><?php _e('NO', CFGP_NAME); ?></option>
                    </select>
                    <p><?php _e("Enable/disable auto-update of plugin.",CFGP_NAME); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
</form>