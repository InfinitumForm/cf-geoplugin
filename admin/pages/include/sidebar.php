<div class="postbox-container" id="postbox-container-1">
	<?php
		//$data = CF_GEO_D::curl_get("http://cfgeoplugin.com/team.json");
		//$data = json_decode($data);
		$defender = new CF_Geoplugin_Defender;
		$enable=$defender->enable;
		
	//	var_dump($data);
	?>
	<?php if($enable==false): ?>
	<div class="postbox">
		<h2 class="hndle ui-sortable-handle"><span><span class="fa fa-star-o"></span> <?php _e("Get CF GeoPlugin PRO!",CFGP_NAME); ?></span></h2>
		<div class="inside">
			<p><?php echo sprintf(__("Full functions are only enabled in PRO version. Don't worry, we setup for you optimal settings.%sIf you want to enable all options like CF Geo Banner, Country Flags, Cloudflare, DNS Lookup, SSL, Proxy and use full functionality of CF Geo Plugin, you can do it for low as $%s with the %s.",CFGP_NAME),'<br><br>',CFGP_PREMIUM_PRICE, '<strong>lifetime license and support</strong>'); ?></p>
			<ul>
				<li><h3><?php _e("PRO Features:",CFGP_NAME); ?></h3></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Cloudflare Support",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Proxy Settings",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("DNS Lookup",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("SSL",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("CF Geo Banner",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Country Flag Support",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("Google Map Global Settings",CFGP_NAME); ?></li>
				<li><span class="fa fa-check" aria-hidden="true"></span> <?php _e("CF Geo Defender Full Functionality",CFGP_NAME); ?></li>
			</ul>
			<br><br>
			<a href="<?php echo get_admin_url(); ?>/admin.php?page=cf-geoplugin-settings" style="display:block; width:100%;">
				<img alt="PayPal - The safer, easier way to pay online!" src="<?php echo CFGP_URL; ?>/admin/images/shop.jpg" style="margin:0 auto;display:block;">
			</a>
		</div>
	</div>
	<?php endif; ?>
	<?php /* if(isset($data->sponsors)): ?>
	<div class="postbox">
		<h2 class="hndle ui-sortable-handle"><span><span class="fa fa-star-o"></span> <?php _e('Valuable Sponsors',CFGP_NAME); ?></span></h2>
		<div class="inside">
			<?php echo sprintf(__("Meet our valuable sponsors who support us and help our development team. If you like to be our sponsor and your logo appears here please contact us %s", CFGP_NAME),'<a href="mailto:creativform@gmail.com">creativform@gmail.com</a>'); ?>
			<?php
				foreach($data->sponsors as $c)
				{
					echo sprintf('<a href="%s" target="_blank" id="sponsor" title="%s" style="%s"><img src="%s" alt="%s" style="%s"></a>', $c->url,$c->title,'display:block; width:100%;',$c->img,$c->title,'margin:10px auto; display:block; width:100%; max-width:150px;');
				}
			?>
		</div>
	</div>
	<?php  endif;*/ ?>
<!--	<div class="postbox">
		<h2 class="hndle ui-sortable-handle"><span><span class="fa fa-heartbeat"></span> <?php _e('Donate',CFGP_NAME); ?></span></h2>
		<div class="inside">
		<?php echo sprintf(__("Our plugin is for now free, but the work on it cost time and money.%sIf you really like this plugin, we will continue to develop and update it. You can donate some money to our development team because in the future we plan to improve this plugin and add new functions and better user experience.%s Thank you for your concern.%s Sincerely, your %s",CFGP_NAME),'<br><br>','<br><br>','<br><br>', '<a href="http://cfgeoplugin.com" target="_blank">'.__('CF GeoPlugin team',CFGP_NAME).'</a>'); ?>
		<br><br>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">

				<input type="hidden" name="business"
					value="creativform@gmail.com">
				

				<input type="hidden" name="cmd" value="_donations">
				

				<input type="hidden" name="item_name" value="<?php _e('CF GeoPlugin Donation',CFGP_NAME); ?>">
				<input type="hidden" name="item_number" value="<?php _e('Donation to CF team for the improvement and maintenance of CF GeoPlugin',CFGP_NAME); ?>">
				<input type="hidden" name="currency_code" value="USD">

				

				<input type="image" name="submit" border="0"
				src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"
				alt="PayPal - The safer, easier way to pay online" style="margin:10px auto; display:block;">
				<img alt="" border="0" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif">
			
			</form>
		</div>
	</div> -->

	<div class="postbox">
		<h2 class="hndle ui-sortable-handle"><span><span class="fa fa-info"></span> <?php _e('Live News & Info',CFGP_NAME); ?></span></h2>
		<script>
        	(function($){
				$.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'cf_geo_rss_feed'}).done(function(d){$("#rss").html(d);});
			}(jQuery || window.jQuery));
        </script>
        <div class="inside" id="rss">
        	 <div style="text-align:center; padding:32px 0">
                 <i class="fa fa-circle-o-notch fa-spin fa-5x fa-fw"></i>
                 <span class="sr-only">Loading...</span>
             </div>
		</div>
	</div>

</div>