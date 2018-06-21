<?php
	$defender = new CF_Geoplugin_Defender;
	$enable=$defender->enable;
?>
<div class="wrap" id="admin-page-geoplugin-new">
	<div class="welcome-panel">
		<h1><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span> <?php _e('NEW Version', CFGP_NAME); ?> <?php echo CFGP_VERSION; ?> <span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span></h1>

		<h3><?php _e('Welcome to version', CFGP_NAME); ?> <?php echo CFGP_VERSION; ?>!<h3>
		<p><?php _e('Over the past year, we develop this plugin to be useful to you and from time to time we add new options according to your requirements and our research according to what is now most necessary for geolocation marketing strategy. Therefore, we introduced a new necessary and useful features in one place and have made a significant step in the development of our plugin.', CFGP_NAME); ?></p>
		<ul>
			<li class="title"><h3><?php _e("PRO Features:",CFGP_NAME); ?></h3></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Cloudflare Geolocation Support",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Proxy Settings",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("DNS Lookup",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("SSL",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("IP Version Lookup",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("CF Geo Banner",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Country SEO Redirection",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Country Flag Support",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Google Map Global Settings",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("CF Geo Defender Full Functionality",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Include/Exclude by Geolocation Functionality",CFGP_NAME); ?></li>
			<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Lifetime Lycense, Support & Updaes",CFGP_NAME); ?></li>
		</ul>
		<?php if($enable===false): ?>
			<h3><?php printf(__('To register your PRO version, you need to visit %s', CFGP_NAME), '<a href="'.get_admin_url().'/admin.php?page=cf-geoplugin-settings">'.__('Settings page', CFGP_NAME).'</a>'); ?></h3>
		<?php else: ?>
			<h3><?php _e('Feel free to use this plugin with no limitation!', CFGP_NAME); ?></h3>
			<h3><?php printf(__('Continue with %s', CFGP_NAME), '<a href="'.get_admin_url().'/admin.php?page=cf-geoplugin">'.__('CF GeoPlugin', CFGP_NAME).'</a>'); ?></h3>
		<?php endif; ?>
		<br>
	</div>
</div>