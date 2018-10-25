<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Activate License
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
$global = new CF_Geoplugin_Global;
$alert='';
$error_label = array(
	'license_key' 	=> __('License Key',CFGP_NAME),
	'activation_id' => __('Activation ID',CFGP_NAME),
	'domain' 		=> __('Domain',CFGP_NAME),
	'sku' 			=> __('SKU',CFGP_NAME),
);
if(isset($_POST['license_key']) && isset($_POST['license'])) :
	$post = array();
	foreach($_POST as $key=>$val) {
		if(in_array($key, array('license_key','sku','action','store_code','domain'), true) !== false) $post[$key]=trim($val);
	}
	
	if(count($post) === 5 )
	{
		$response = '';
		$url = 'https://cdn-cfgeoplugin.com/api6.0/authenticate.php';
		if( function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec') )
		{
//			$ch = curl_init(CFGP_STORE . '/wp-admin/admin-ajax.php');
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			$response = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			$context = CF_Geoplugin_Global::set_stream_context( array( 'Accept: application/json' ), 'POST', http_build_query( $post ) );
			$response = file_get_contents( $url, false, $context );
		}

		if($response)
		{
			$license = json_decode($response);
			if($license->error === false)
			{
				$CF_GEOPLUGIN_OPTIONS['license_key'] = $license->data->the_key;
				$CF_GEOPLUGIN_OPTIONS['license_id'] = $license->data->activation_id;
				$CF_GEOPLUGIN_OPTIONS['license_expire'] = $license->data->expire;
				$CF_GEOPLUGIN_OPTIONS['license_expire_date'] = $license->data->expire_date;
				$CF_GEOPLUGIN_OPTIONS['license_url'] = $license->data->url;
				$CF_GEOPLUGIN_OPTIONS['license_expired'] = $license->data->has_expired;
				$CF_GEOPLUGIN_OPTIONS['license_status'] = $license->data->status;
				$CF_GEOPLUGIN_OPTIONS['license_sku'] = $license->data->sku;
				$CF_GEOPLUGIN_OPTIONS['license'] = 1;
				
				if( !CFGP_MULTISITE )
					update_option('cf_geoplugin', $CF_GEOPLUGIN_OPTIONS, true);
				else
					update_site_option('cf_geoplugin', $CF_GEOPLUGIN_OPTIONS);
				
				exit('<h3 class="mt-5"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i><span class="sr-only">Loading...</span> '.__('Please wait...',CFGP_NAME).'</h3><meta http-equiv="Refresh" content="0.1; url='.self_admin_url('admin.php?page=cf-geoplugin-settings&action=activate_license').'">');
			}
			else
			{ ob_start();
			?>
				<div class="alert alert-danger" role="alert">
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
													if(!empty($post['license_key']))
														_e('One of the Reasons why this happen can be that you must choose valid "License Type". If you purchase "Personal License" and get license key, you must enter that license key and choose license type to validate your key. If key not match to your type you are not able to finish activation.',CFGP_NAME);
													else
														_e('You must enter license key in order to continue with licensing your plugin installation.',CFGP_NAME);
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
			<?php $alert = ob_get_clean();
			}
		}	
	}
endif;


?>
<div class="clearfix"></div>
<div class="container-fluid">
    <div class="row mt-4">
    	<?php if($this->get('page') != 'cf-geoplugin-settings') : ?>
    	<div class="col-sm-9">
        <?php else : ?>
        <div class="col-sm-12">
        <?php endif; ?>
        	<div class="jumbotron">
            	<?php echo $alert; ?>
                <?php if(CFGP_ACTIVATED) : ?>
                <p class="lead"><?php printf(
    __('Thank you for using unlimited license. You license is active until %1$s. It would be great to expand your license by that date. After expiration date you will experience plugin limitations.<br><br>To review or deactivate your license, please go to your %2$s.',CFGP_NAME),
    '<strong>' . ($CF_GEOPLUGIN_OPTIONS['license_expire'] == 0 ? __('never',CFGP_NAME) : date(get_option('date_format') . ' ' . get_option('time_format'), (int)$CF_GEOPLUGIN_OPTIONS['license_expire'])) . '</strong>',
	'<a href="' . $CF_GEOPLUGIN_OPTIONS['license_url'] . '" target="_blank">' . __('CF GeoPlugin account',CFGP_NAME) . '</a>'
); ?></p>
                <?php else : ?>
                <h1 class="display-4"><?php _e('Activate Unlimited!',CFGP_NAME); ?></h1>
                <p class="lead"><?php printf(
                    __('You currently using free version of plugin with a limited number of lookups.<br>Each free version of this plugin is limited to %1$s lookups per day and you have only %2$s lookups available for today. If you want to have unlimited lookup, please enter your license key.<br>If you are unsure and do not understand what this is about, read %3$s.',CFGP_NAME),
                    
                    '<strong>300</strong>',
                    '<strong>'.$CFGEO['lookup'].'</strong>',
                    '<strong><a href="https://cfgeoplugin.com/information/new-plugin-new-features-new-success/" target="_blank">' . __('this article',CFGP_NAME) . '</a></strong>'
                ); ?></p>
                <?php endif; ?>
                <hr class="my-4">
                <form method="post" action="<?php self_admin_url('admin.php?page=cf-geoplugin-activate'); ?>" enctype="multipart/form-data" target="_self" id="license-form" autocomplete="off">
                	<strong><label><?php _e('License Key',CFGP_NAME); ?> (<?php CFGP_ACTIVATED ? _e('activated',CFGP_NAME) : _e('required',CFGP_NAME); ?>)</label></strong><br>
                	<input type="text" name="license_key" value="<?php echo isset($_POST['license_key']) ? $_POST['license_key'] : $CF_GEOPLUGIN_OPTIONS['license_key']; ?>" class="form-control" placeholder="<?php _e('Enter Your License Key',CFGP_NAME); ?>" autocomplete="off" style="width: 500px; display:inline-block;" <?php echo CFGP_ACTIVATED ? ' disabled' : ''; ?>> 
                    <?php if(!CFGP_ACTIVATED) : ?><strong><a href="https://cfgeoplugin.com/#price" target="_blank" class="btn btn-info"><?php _e('GET MY LICENSE KEY',CFGP_NAME); ?></a></strong><?php endif; ?><br><br>
                    <strong><label><?php _e('License Type',CFGP_NAME); ?> <?php CFGP_ACTIVATED ? '' : _e('(required)',CFGP_NAME); ?></label></strong><br>
                    <select name="sku" class="form-control" style="width: 300px; display:inline-block;" <?php echo CFGP_ACTIVATED ? ' disabled' : ''; ?>>
                    <?php
                    	$options = CF_Geoplugin_Global::license_name(true);
						foreach($options as $key=>$val){
							$active = isset($_POST['sku']) && $_POST['sku'] == $key ? ' selected' : ($CF_GEOPLUGIN_OPTIONS['license_sku'] == $key ? ' selected' : '');
							printf('<option value="%1$s"%2$s>%3$s</option>', $key, $active, $val);
						}
					?>
                    </select><?php if(!CFGP_ACTIVATED) : ?><br><span>(<?php _e('License type must match to your license key that you ordered.',CFGP_NAME); ?>)</span><br><br>
                    <?php printf(
						__('Before any action don\'t forget to read and agree with %1$s and %2$s.'),
						'<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy',CFGP_NAME) . '</a></strong>',
						'<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions',CFGP_NAME) . '</a></strong>'
					); ?><br><br>                    
                    <input type="hidden" name="action" value="license_key_activate">
                    <input type="hidden" name="store_code" value="<?php echo $CF_GEOPLUGIN_OPTIONS['store_code']; ?>">
                    <input type="hidden" name="domain" value="<?php	echo CF_Geoplugin_Global::get_host(); ?>">
                    <input type="hidden" name="license" value="1">
                    <button type="submit" class="btn btn-primary btn-lg"><?php _e('Activate Unlimited',CFGP_NAME); ?></button><?php endif; ?>
                </form>                
            </div>
        </div>
        <?php if($this->get('page') != 'cf-geoplugin-settings') : ?>
        <div class="col-sm-3">
        	<?php do_action('page-cf-geoplugin-license-sidebar'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>