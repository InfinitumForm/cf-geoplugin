<?php
$defender = new CF_Geoplugin_Defender;
$enable=$defender->enable;
?>
<?php if($enable==false): ?>
    <div class="manage-menus">
    	<h3><?php _e("Get CF GeoPlugin PRO!",CFGP_NAME); ?></h3><?php echo sprintf(__("Full functions of this plugin are only enabled in PRO version. Don't worry, we set up for you optimal settings.%sIf you want to enable all options like CF Geo Banner, Country Flags, Cloudflare, DNS Lookup, SSL, Proxy, Country SEO Redirection and use full functionality of CF Geo Plugin, you can do it for low as $%s with the %s.",CFGP_NAME),'<br>',CFGP_PREMIUM_PRICE, '<strong>lifetime license and support</strong>'); ?>
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
		<br><br><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="YAHB7JXXVLZUQ">
<input type="image" src="<?php echo CFGP_URL; ?>/admin/images/shop.jpg" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
    </div>
<?php endif; ?>