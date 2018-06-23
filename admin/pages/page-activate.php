<?php
$url = CF_GEO_D::URL();
$error_label = array(
	'license_key' 	=> __('License Key',CFGP_NAME),
	'activation_id' => __('Activation ID',CFGP_NAME),
	'domain' 		=> __('Domain',CFGP_NAME),
	'sku' 			=> __('SKU',CFGP_NAME),
);
if(isset($_POST['license_key']) && isset($_POST['cf_geo_license'])) :
	$post = array();
	foreach($_POST as $key=>$val) {
		if(in_array($key, array('license_key','sku','action','store_code','domain'), true) !== false) $post[$key]=$val;
	}
	
	if(count($post) === 5)
	{
		$ch = curl_init(CFGP_STORE . '/wp-admin/admin-ajax.php');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		$response = curl_exec($ch);
		curl_close($ch);
		if($response)
		{
			$license = json_decode($response);
			if($license->error === false)
			{
				update_option('cf_geo_license_key', $license->data->the_key, true);
				update_option('cf_geo_license_id', $license->data->activation_id, true);
				update_option('cf_geo_license_expire', $license->data->expire, true);
				update_option('cf_geo_license_expire_date', $license->data->expire_date, true);
				update_option('cf_geo_license_url', $license->data->url, true);
				update_option('cf_geo_license_expired', $license->data->has_expired, true);
				update_option('cf_geo_license_status', $license->data->status, true);
				update_option('cf_geo_license_sku', $post['sku'], true);
				update_option('cf_geo_license', 1, true);
				exit('<h3>'.__('Please wait...',CFGP_NAME).'</h3><meta http-equiv="Refresh" content="0.1; url='.admin_url('/admin.php?page=cf-geoplugin&action=activate_license').'">');
			}
			else
			{
			?>
				<div class="error notice">
                	<h3><span class="fa fa-exclamation-triangle"></span> <?php _e('Activation failed!',CFGP_NAME); ?></h3>
                    <p><?php
						if(isset($license->errors))
						{ ?>
                        	<ul>
                            	<?php
                                	foreach($license->errors as $name => $obj)
									{
										echo '<li> <strong>' . $error_label[$name] . ':</strong><ol>';
										foreach($obj as $message) echo '<li>' . $message . '</li>';
										echo '</ol>';
										switch($name)
										{
											case 'license_key' :
												echo '<p><strong>';
													_e('Reason why this happen can be that you must choose valid "License Type". If you purchase "Personal License" and get license key, you must enter that license key and choose license type to validate your key. If key not match to your type you are not able to finish activation.',CFGP_NAME);
												echo '</strong></p>';
											break;
										}
										echo '</li>';
									};
								?>
                            </ul>
						<?php }
					?></p>
                </div>
			<?php }
		}
	}	
endif;
?><div class="wrap">
	
    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
            	<h2><span class="fa fa-star-o"></span> <?php _e('Activate Unlimited',CFGP_NAME); ?></h2>
                <p class="about-description"><?php printf(
                    __('You currently using free version of plugin with a limited number of lookups.<br>Each free version of this plugin is limited to %1$s lookups per day and you have only %2$s lookups available for today. If you want to have unlimited lookup, please enter your license key.<br>If you are unsure and do not understand what this is about, read %3$s.<br><br>Also, before any action don\'t forget to read and agree with %4$s and %5$s.',CFGP_NAME),
                    
                    '<strong>300</strong>',
                    '<strong>'.do_shortcode('[cf_geo return="lookup"]').'</strong>',
                    '<strong><a href="https://cfgeoplugin.com/information/new-plugin-new-features-new-success/" target="_blank">' . __('this article',CFGP_NAME) . '</a></strong>',
                    '<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy',CFGP_NAME) . '</a></strong>',
                    '<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions',CFGP_NAME) . '</a></strong>'
                ); ?></strong></p><br>
				<form method="post" enctype="multipart/form-data" action="<?php echo $url->url; ?>" target="_self" id="license-form" autocomplete="off">
                	<strong><label>License Key (required)</label></strong><br>
                	<input type="text" name="license_key" value="<?php echo isset($_POST['license_key']) ? $_POST['license_key'] : ''; ?>" placeholder="CF*********************Ge0" autocomplete="off" style="width: 300px;"> 
                    <strong><a href="https://cfgeoplugin.com/#price" target="_blank">   <?php _e('GET MY LICENSE KEY',CFGP_NAME); ?>   </a></strong><br><br>
                    <strong><label>License Type (required)</label></strong><br>
                    <select name="sku">
                    <?php
                    	$options = array(
							'CFGEO1M'	=> __('UNLIMITED Test License',CFGP_NAME),
							'CFGEOSWL'	=> __('UNLIMITED Personal License',CFGP_NAME),
							'CFGEO3WL'	=> __('UNLIMITED Freelancer License',CFGP_NAME),
							'CFGEODWL'	=> __('UNLIMITED Business License',CFGP_NAME)
						);
						foreach($options as $key=>$val){
							$active = isset($_POST['sku']) && $_POST['sku'] == $key ? ' selected' : '';
							printf('<option value="%1$s"%2$s>%3$s</option>', $key, $active, $val);
						}
					?>
                    </select> <span>(<?php _e('License type must match to your license key that you ordered.',CFGP_NAME); ?>)</span><br><br>
                    <?php printf(
						__('Before any action don\'t forget to read and agree with %1$s and %2$s.'),
						'<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy',CFGP_NAME) . '</a></strong>',
						'<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions',CFGP_NAME) . '</a></strong>'
					); ?><br><br>
                    <button type="submit" class="button button-primary"><?php _e('Activate Unlimited',CFGP_NAME); ?></button>
                    
                    <input type="hidden" name="action" value="license_key_activate">
                    <input type="hidden" name="store_code" value="<?php echo CFGP_STORE_CODE; ?>">
                    <input type="hidden" name="domain" value="<?php	echo $url->hostname; ?>">
                    <input type="hidden" name="cf_geo_license" value="1">
                </form>
            </div>
            <?php require_once plugin_dir_path(__FILE__) . 'include/sidebar.php'; ?> 
        </div>
    </div>
    
</div>