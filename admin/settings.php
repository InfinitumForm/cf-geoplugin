<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Settings Page CF Geo Plugin
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
include CFGP_INCLUDES . '/class-cf-geoplugin-forms.php';
$global = CF_Geoplugin_Global::get_instance();

//echo '<pre>', var_dump($CF_GEOPLUGIN_OPTIONS), '</pre>';

$alert = '';
if($this->get('action') == 'activate_license')
{
	if($CF_GEOPLUGIN_OPTIONS['license'])
	{
		$alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
		  <h4 class="alert-heading">' . __('Well done!',CFGP_NAME) . '</h4>
		  <p>' . __('License activated successfully!',CFGP_NAME) . '</p>
		  <hr>
		  <p>' . sprintf(__('You are now using unlimited lookup and the changes will appear instantly or will be visible in %s.',CFGP_NAME), $this->get_time_ago(isset($_SESSION[CFGP_PREFIX . 'session_expire']) ? $_SESSION[CFGP_PREFIX . 'session_expire'] : 0)) . '</p>
		  <button type="button" class="close" data-dismiss="alert" aria-label="'. __( 'Close', CFGP_NAME ) .'">
			<span aria-hidden="true">&times;</span>
		  </button>
		  </div>';
	}
	else
	{
		$alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<h4 class="alert-heading">' . __('Activation Fail!',CFGP_NAME) . '</h4>
			<p>' . __('Your activation key not valid and activation is not possible. Please check all parameters.',CFGP_NAME) . '</p>
			<hr>
			<p>' . __('If you think that this is an error, please contact technical support.',CFGP_NAME) . '</p>
			<button type="button" class="close" data-dismiss="alert" aria-label="'. __( 'Close', CFGP_NAME ) .'">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>';
	}
}

if( $global->get( 'action' ) == 'deactivate_license' ) 
{
	if( $CF_GEOPLUGIN_OPTIONS['license'] )
	{
		$alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<h4 class="alert-heading">' . __('Deactivation failed!',CFGP_NAME) . '</h4>
			<p>' . __('Currently we are not able to perform deactivation request please try again later.',CFGP_NAME) . '</p>
			<hr>
			<p>' . __('Your license is still valid with all benefits.',CFGP_NAME) . '</p>
			<button type="button" class="close" data-dismiss="alert" aria-label="'. __( 'Close', CFGP_NAME ) .'">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>';
	}
	else
	{
		$alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
			<h4 class="alert-heading">' . __('Attention!',CFGP_NAME) . '</h4>
			<p>' . __('License deactivated successfully!',CFGP_NAME) . '</p>
			<hr>
			<p>' . __('Due to license deactivation your APi limitation will be 300 requests per day.',CFGP_NAME) . '</p>' .
			'<button type="button" class="close" data-dismiss="alert" aria-label="'. __( 'Close', CFGP_NAME ) .'">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>';
	}
}
?>
<div class="clearfix"></div>
<div class="container-fluid">
	<div class="row">
        <div class="col-12">
        	<h1 class="h5 mt-3"><i class="fa fa-cogs text-left"></i> <?php _e('CF Geo Plugin Settings',CFGP_NAME); ?></h1>
            <hr>
        </div>
        <div class="col-12" id="alert"><?php echo $alert; ?></div>
	</div>
    <div class="row mt-4">
    	<div class="col-sm-12">
        
            <ul class="nav nav-tabs" role="tablist" id="settings-tab">
                <li class="nav-item">
                    <a class="nav-link text-dark active" href="#settings-general" role="tab" data-toggle="tab"><span class="fa fa-cogs"></span> <?php _e('General Settings',CFGP_NAME); ?></a>
                </li>
                <li class="nav-item"<?php echo (!$CF_GEOPLUGIN_OPTIONS['enable_gmap'] ? ' style="display: none;"' : ''); ?>>
                    <a class="nav-link text-dark" href="#settings-google-map" role="tab" data-toggle="tab"><span class="fa fa-globe"></span> <?php _e('Google Map',CFGP_NAME); ?></a>
                </li>
                <li class="nav-item"<?php echo (!$CF_GEOPLUGIN_OPTIONS['enable_rest'] ? ' style="display: none;"' : ''); ?>>
                    <a class="nav-link text-dark" href="#settings-rest-api" role="tab" data-toggle="tab"><span class="fa fa-code"></span> <?php _e('REST API',CFGP_NAME); ?></a>
                </li>
                <?php if(!CFGP_DEFENDER_ACTIVATED) : ?>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#settings-license" role="tab" data-toggle="tab"><span class="fa fa-star"></span> <?php _e('License',CFGP_NAME); ?></a>
                </li>
                <?php endif; ?>
                <?php do_action('page-cf-geoplugin-settings-tab'); ?>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#settings-info" role="tab" data-toggle="tab"><span class="fa fa-info"></span> <?php _e('Credits & Info',CFGP_NAME); ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active show" id="settings-general">
                	<div class="row">
    					<div class="col-12">
                        	<?php
                            	$general = new CF_Geoplugin_Form;
								$general->html('<h5 class="mt-3" id="WordPress_Settings">'.__('WordPress Settings',CFGP_NAME).'</h5>');
								$general->html('<p>'.__('This settings only affect on CF Geo Plugin functionality and connection between plugin and WordPress setup. Use it smart and careful.',CFGP_NAME).'</p><hr>');
								
								$general->radio(array(
									'label'		=> __('Enable Plugin Auto Update',CFGP_NAME),
									'name'		=> 'enable_update',
									'default'	=> ((isset($CF_GEOPLUGIN_OPTIONS['enable_update']) && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) >= 1) ? $CF_GEOPLUGIN_OPTIONS['enable_update'] : 0),
									array(
										'text'			=> __('Enable',CFGP_NAME),
										'value'			=> 1,
										'id'			=> 'enable_update_true',
										'disabled'		=> CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) < 1
									),
									array(
										'text'			=> __('Disable',CFGP_NAME),
										'value'			=> 0,
										'id'			=> 'enable_update_false',
										'disabled'		=> CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) < 1
									),
									'info'		=> __('Allow your plugin to be up to date.',CFGP_NAME) . (CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) < 1 ? '(' . __('Auto update is only enabled with plugin license.',CFGP_NAME) . ')' : ''),
								));
								
								$general->radio(array(
									'label'		=> __('Enable Dashboard Widget',CFGP_NAME),
									'name'		=> 'enable_dashboard_widget',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_dashboard_widget', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_dashboard_widget_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_dashboard_widget_false',
									),
									'info'		=> __('Enable CF Geo Plugin widget in the dashboard area.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Dashboard Widget Type',CFGP_NAME),
									'name'		=> 'enable_advanced_dashboard_widget',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_advanced_dashboard_widget', 0),
									array(
										'text'	=> __('Advanced (recommended)',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_advanced_dashboard_widget_true',
									),
									array(
										'text'	=> __('Basic',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_advanced_dashboard_widget_false',
									),
									'info'		=> __('Dashboard widget comming in 2 types. You can choose what best fit to you.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable Cloudflare',CFGP_NAME),
									'name'		=> 'enable_cloudflare',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_cloudflare', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_cloudflare_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_cloudflare_false',
									),
									'info'		=> __('Enable this option only when you use Cloudflare services on your website.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable SSL',CFGP_NAME),
									'name'		=> 'enable_ssl',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_ssl', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_ssl_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_ssl_false',
									),
									'info'		=> __('This option force plugin to use SSL connection',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable Caching',CFGP_NAME),
									'name'		=> 'enable_cache',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_cache', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_cache_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_cache_false',
									),
									'info'		=> __('This option allows caching. Usually used in combination with a cache plugin. If you do not want your redirects to be cached, leave this field disabled',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Measurement Unit',CFGP_NAME),
									'name'		=> 'measurement_unit',
									'default'	=> CF_Geoplugin_Global::get_the_option('measurement_unit', 'km'),
									array(
										'value' => 'km',
										'text' => __('km',CFGP_NAME),
										'id'	=> 'measurement_unit_km',
									),
									array(
										'value' => 'mile',
										'text' => __('mile',CFGP_NAME),
										'id'	=> 'measurement_unit_mile',
									)
								));
								
								
								if(!( CF_Geoplugin_Global::get_the_option('woocommerce_active', false) ))
								{
									$base_currency_options = array();
									$i = 0;
									foreach( CF_Geplugin_Library::CURRENCY_SYMBOL as $currency => $value )
									{
										$base_currency_options[$i]['value'] = $currency;
										$base_currency_options[$i]['text'] = $currency . (!empty($value) ? " &nbsp;&nbsp;-&nbsp;&nbsp; {$value}" : '');
										$i++;
									}
									$general->select(array_merge(array(
										'label'		=> __('Base currency',CFGP_NAME),
										'name'		=> 'base_currency',
										'id'		=> 'base_currency',
										'default'	=> CF_Geoplugin_Global::get_the_option('base_currency', 'USD'),
										'attr'		=> array('autocomplete'=>'off', 'style'=>'max-width:120px;display: inline-block;'),
										'info'		=> '<p>' . __('The base currency (transaction currency) - The currency by which conversion is checked by geo location.',CFGP_NAME) . '<span class="text-info" id="base_currency_info"' . ((!$CF_GEOPLUGIN_OPTIONS['woocommerce_active'] || !$CF_GEOPLUGIN_OPTIONS['enable_woocommerce']) ? ' style="display: none;"' : '') . '><br>' . __('Woocommerce take control of this and you can change it inside Woocommerce Settings.',CFGP_NAME) . '</span>' . '</p>',
										'disabled'	=> CF_Geoplugin_Global::get_the_option('woocommerce_active', false)
									), $base_currency_options ));
								}
								
								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" id="Plugin_Settings">'.__('Plugin Settings',CFGP_NAME).'</h5>');
								$general->html('<p>'.__('This settings enable advanced lookup and functionality of plugin.',CFGP_NAME).'</p><hr>');
								
								$general->radio(array(
									'label'		=> __('Enable DNS Lookup',CFGP_NAME),
									'name'		=> 'enable_dns_lookup',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_dns_lookup', 0),
									'license'	=> 1,
									'license_message'	=> __('This option is only available with unlimited lookup license',CFGP_NAME),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_dns_lookup_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_dns_lookup_false',
									),
									'info'		=> __('DNS lookup allow you to get DNS informations from your visitors.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable Country Flags',CFGP_NAME),
									'name'		=> 'enable_flag',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_flag', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_flag_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_flag_false',
									),
									'info'		=> __('Display country flag SVG or PNG image on your website.',CFGP_NAME)
								));
								
								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" id="Plugin_Features">'.__('Plugin Features',CFGP_NAME).'</h5>');
								$general->html('<p>'.__('Here you can enable or disable features that you need. This is useful because you can disable functionality what you not need.',CFGP_NAME).'</p><hr>');
								
								$general->radio(array(
									'label'		=> __('Enable Geo Banner',CFGP_NAME),
									'name'		=> 'enable_banner',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_banner', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_banner_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_banner_false',
									),
									'info'		=> __('Display content to user by geo location.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable Google Map',CFGP_NAME),
									'name'		=> 'enable_gmap',
									'class'		=> 'enable_gmap',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_gmap', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_gmap_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_gmap_false',
									),
									'info'		=> __('Place simple Google Map to your page.',CFGP_NAME)
								));

								if( is_plugin_active('woocommerce/woocommerce.php') )
								{	
									$general->radio(array(
										'label'		=> __('WooCommerce integration'),
										'name'		=> 'enable_woocommerce',
										'class'		=> 'enable_woocommerce',
										'default'	=> CF_Geoplugin_Global::get_the_option('enable_woocommerce', 0),
										'info'		=> !$CF_GEOPLUGIN_OPTIONS['woocommerce_active'] ? __('This function is only enabled when Woocommerce is active.', CFGP_NAME) : ($CF_GEOPLUGIN_OPTIONS['enable_woocommerce'] ? __( 'For more options visit WooCommerce Settings', CFGP_NAME ) : __( 'If you want to CF Geo Plugin add new options to your WooCommerce, activate this option.', CFGP_NAME )),
										array(
											'text'	=> __('Enable', CFGP_NAME),
											'value'	=> 1,
											'id'	=> 'enable_woocommerce_true',
											'disabled'	=> ( CF_Geoplugin_Global::get_the_option('woocommerce_active', 0) ? false : true ),
											'input_class'	=> 'enable-woocommerce'
										),
										array(
											'text'	=> __('Disable', CFGP_NAME),
											'value'	=> 0,
											'id'	=> 'enable_woocommerce_false',
											'disabled'	=> ( CF_Geoplugin_Global::get_the_option('woocommerce_active', 0) ? false : true ),
											'input_class'	=> 'enable-woocommerce'
										),
									));
									
									
									if( is_plugin_active('wooplatnica/wooplatnica.php') )
									{
										$general->radio(array(
											'label'		=> __('Wooplatnica integration'),
											'name'		=> 'enable_wooplatnica',
											'class'		=> 'enable_wooplatnica',
											'default'	=> CF_Geoplugin_Global::get_the_option('enable_wooplatnica', 0),
											'info'		=> __( 'If you want to CF Geo Plugin take control over Woocommerce addon "Wooplatnica", activate this option.', CFGP_NAME ),
											array(
												'text'	=> __('Enable', CFGP_NAME),
												'value'	=> 1,
												'id'	=> 'enable_wooplatnica_true',
												'input_class'	=> 'enable-wooplatnica'
											),
											array(
												'text'	=> __('Disable', CFGP_NAME),
												'value'	=> 0,
												'id'	=> 'enable_wooplatnica_false',
												'input_class'	=> 'enable-wooplatnica'
											),
										));
									}
								}
								
								if( is_plugin_active('contact-form-7/wp-contact-form-7.php') )
								{
									$general->radio(array(
										'label'		=> __('Contact Form 7 integration'),
										'name'		=> 'enable_cf7',
										'class'		=> 'enable_cf7',
										'default'	=> CF_Geoplugin_Global::get_the_option('enable_cf7', 0),
										'info'		=> __( 'If you want to CF Geo Plugin add new options to your Contact Form 7, activate this option.', CFGP_NAME ),
										array(
											'text'	=> __('Enable', CFGP_NAME),
											'value'	=> 1,
											'id'	=> 'enable_cf7_true',
											'input_class'	=> 'enable-cf7'
										),
										array(
											'text'	=> __('Disable', CFGP_NAME),
											'value'	=> 0,
											'id'	=> 'enable_cf7_false',
											'input_class'	=> 'enable-cf7'
										),
									));
								}

								$general->radio(array(
									'label'		=> __('Enable REST API',CFGP_NAME),
									'name'		=> 'enable_rest',
									'class'		=> 'enable_rest',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_rest', 0),
									'info'		=> __('The CF Geo Plugin REST API allows external apps to use geo informations.',CFGP_NAME) . (CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) < 4 ? '<br><span class="text-info">' . __('REST API is only functional for the Business License.',CFGP_NAME) . '</span>' : ''),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_rest_true'
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_rest_false'
									)
								));
								
								$general->html(do_action('page-cf-geoplugin-settings-features'));
								
								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" id="SEO_Redirect">'.__('SEO Redirection',CFGP_NAME).'</h5>');
								$general->html('<hr>');

								$general->radio(array(
									'label'		=> __('Enable Site SEO Redirection',CFGP_NAME),
									'name'		=> 'enable_seo_redirection',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_seo_redirection', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_seo_redirection_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_seo_redirection_false',
									),
									'info'		=> __('You can redirect your visitors to other locations.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Enable CSV in Site SEO Redirection',CFGP_NAME),
									'name'		=> 'enable_beta_seo_csv',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_beta_seo_csv', 1),
									'info'		=> __('This allow you to upload CSV to your SEO redirection or download/backup SEO redirection list in the CSV.',CFGP_NAME),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_beta_seo_csv_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_beta_seo_csv_false',
									)
								));

								$post_types = apply_filters( 'cf_geoplugin_post_types', get_post_types(
									array(
										'public'	=> true,
									),
									'objects'
								));
								$post_types = apply_filters( 'cf_geoplugin_seo_redirection_post_types', $post_types);

								$first_seo_options = array();
									
								$default_value_seo = isset( $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] ) && !empty( $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] ) ? $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] : '';
								foreach( $post_types as $i => $obj )
								{
									if( in_array( $obj->name, array( 'attachment', 'nav_menu_item', 'custom_css', 'customize_changeset', 'user_request', 'cf-geoplugin-banner' ) ) ) continue;

									if( isset( $CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] ) && (int)$CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] == 1 )
									{
										$first_seo_options[] = $obj->name;
									}
									
						//			if( isset( $CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] ) && (int)$CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] == 1 ) $default_value_seo = $obj->name;
									$seo_redirections[] = array(
										'text'		=> $obj->label,
										'name'		=> 'enable_seo_posts[]',
										'value'		=> $obj->name,
										'default'	=> $default_value_seo,
										'id'		=> sprintf( '%s-seo-%s', $obj->name, $i ),
										'input_class'	=> 'enable_seo_posts'
									);
								}

								$seo_redirections['label'] = __( 'Enable Post SEO Redirection In', CFGP_NAME );
								$seo_redirections['container_class'] = 'container-enable-seo-posts';
								$general->checkbox(
									$seo_redirections
								); 

								if( !empty( $first_seo_options ) ) $this->update_option( 'enable_seo_posts', $first_seo_options );
								
								$general->radio(array(
									'label'		=> __('Disable redirection for the bots',CFGP_NAME),
									'name'		=> 'redirect_disable_bots',
									'default'	=> CF_Geoplugin_Global::get_the_option('redirect_disable_bots', 1),
									array(
										'text'	=> __('Yes',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'redirect_disable_bots_true',
									),
									array(
										'text'	=> __('No',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'redirect_disable_bots_false',
									),
									'info'		=> __('Disable SEO redirection for the bots, crawlers, spiders and social network bots. This can be a special case that is very important for the SEO.',CFGP_NAME)
								));
								
								$general->radio(array(
									'label'		=> __('Hide HTTP referer headers data',CFGP_NAME),
									'name'		=> 'hide_http_referer_headers',
									'default'	=> CF_Geoplugin_Global::get_the_option('hide_http_referer_headers', 0),
									array(
										'text'	=> __('Yes',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'hide_http_referer_headers_true',
									),
									array(
										'text'	=> __('No',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'hide_http_referer_headers_false',
									),
									'info'		=> __('You can tell the browser to not send a referrer by enabling this option for all SEO redirections.',CFGP_NAME)
								));

								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" id="Spam_Protection">'.__('Spam Protection',CFGP_NAME).'</h5>');
								$general->html('<p>'.__( 'With Anti Spam Protection you can enable anti spam filter and block access from the specific IP, country, state and city to your site. This feature is very safe and does not affect to the SEO.' ).'</p><p>'.__( 'By enabling this feature, you get full spam protection from over 30.000 blacklisted IP addresses.' ).'</p><hr>');
							
								$general->radio(array(
									'label'		=> __('Enable Spam Protection',CFGP_NAME),
									'name'		=> 'enable_defender',
									'class'		=> 'enable_defender',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_defender', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_defender_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_defender_false',
									),
									'info'		=> __('Protect your website from the unwanted visitors by geo location or ip address.',CFGP_NAME)
								));

								$general->radio(array(
									'label'		=> __('Enable Automatic IP Address Blacklist Check',CFGP_NAME),
									'name'		=> 'enable_spam_ip',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_spam_ip', 0),
									'license'	=> 1,
									'license_message'	=> __('This option is only available with unlimited lookup license',CFGP_NAME),
									array(
										'text'			=> __('Enable',CFGP_NAME),
										'value'			=> 1,
										'id'			=> 'enable_spam_ip_true',
										'input_class'	=> 'enable_spam_ip',
										'disabled'		=> ( CF_Geoplugin_Global::get_the_option('enable_defender', 1) ? false : true )
									),
									array(
										'text'			=> __('Disable',CFGP_NAME),
										'value'			=> 0,
										'id'			=> 'enable_spam_ip_false',
										'input_class'	=> 'enable_spam_ip',
										'disabled'		=> ( CF_Geoplugin_Global::get_the_option('enable_defender', 1) ? false : true )
									),
									'info'		=>  __('Protect your website from bots, crawlers and other unwanted visitors that are found in our blacklist.',CFGP_NAME)
								));

								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" id="Geo_Tag">'.__('Geo Tag',CFGP_NAME).'</h5>');
								$general->html('<p>'.__( 'The Geo Tag will help you to create your own geo tags in a simple interactive way without having to deal with latitude or longitude degrees or the syntax of meta tags.' ).'</p>');
								$general->html('<p>'.__( 'Here you can enable Geo Tag generators inside any post type on the your WordPress website.' ).'</p><hr>');

								$post_types_geo = apply_filters( 'cf_geoplugin_post_types', get_post_types(
									array(
										'public'	=> true,
									),
									'objects'
								));
								$post_types_geo = apply_filters( 'cf_geoplugin_geo_tag_post_types', $post_types_geo);

								$default_value_geo = isset( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) && !empty( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) ? $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] : '';
								foreach( $post_types_geo as $i => $obj )
								{
									if( in_array( $obj->name, array( 'attachment', 'nav_menu_item', 'custom_css', 'customize_changeset', 'user_request', 'cf-geoplugin-banner' ) ) ) continue;
									
									//if( isset( $CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] ) && (int)$CF_GEOPLUGIN_OPTIONS['first_plugin_activation'] == 1 ) $default_value_geo = $obj->name;
									$geo_tag[] = array(
										'text'		=> $obj->label,
										'name'		=> 'enable_geo_tag[]',
										'value'		=> $obj->name,
										'default'	=> $default_value_geo,
										'id'		=> sprintf( '%s-geo-%s', $obj->name, $i ),
										'input_class'	=> 'enable_geo_tag'
									);
								}

								$geo_tag['label'] = __( 'Enable Geo Tag In', CFGP_NAME );
								$geo_tag['container_class'] = 'container-enable-geo-tag';
								$general->checkbox(
									$geo_tag
								);
								
								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								
								$general->html('<h5 class="mt-5" id="Special_Settings">'.__('Special Settings',CFGP_NAME).'</h5>');
								$general->html('<p>'.__('Special plugin settings that, in some cases, need to be changed to make some plugin systems to work properly. Many of theese settings depends of your server.', CFGP_NAME).'</p><hr>');


								$general->input(array(
									'type'		=> 'number',
									'label'		=> __( 'Set HTTP API timeout in seconds', CFGP_NAME ),
									'name'		=> 'timeout',
									'attr'		=> array(
										'min'	=> 3,
										'max'	=> 9999,
										'style'	=> 'max-width:100px; display:inline-block'
									),
									'id'		=> 'timeout',
									'value'		=> isset( $CF_GEOPLUGIN_OPTIONS['timeout'] ) ? $CF_GEOPLUGIN_OPTIONS['timeout'] : (isset( $global->default_options[ 'timeout' ] ) ? $global->default_options[ 'timeout' ] : 5),
									'info'		=> __( 'Set maximum time the request is allowed to take', CFGP_NAME )
								));

								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$general->html('<h5 class="mt-5" class="BETA_Testing">'.__('BETA Testing & Advanced Features',CFGP_NAME).'</h5>');
								$general->html('<p>'.__('Here you can enable BETA functionality and test it. In many cases, normaly you should not have any problems but some functionality are new and experimental that mean if any conflict happen, you must be aware of this. If many users find this functionality useful we may keep this functionality and include it as standard functionality of CF Geo Plugin.',CFGP_NAME).'</p><hr>');
								
								$general->radio(array(
									'label'		=> __('Enable BETA Features',CFGP_NAME),
									'name'		=> 'enable_beta',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_beta', 1),
									'info'		=> __('This enable/disable all BETA functionality by default.',CFGP_NAME),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_beta_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_beta_false',
									)
								));
								
								$general->radio(array(
									'label'		=> __('Enable Simple Shortcodes',CFGP_NAME),
									'name'		=> 'enable_beta_shortcode',
									'default'	=> CF_Geoplugin_Global::get_the_option('enable_beta_shortcode', 1),
									'info'		=> __('This allow you to use additional simple shortcode formats.',CFGP_NAME),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'enable_beta_shortcode_true',
										'disabled' 	=> (CF_Geoplugin_Global::get_the_option('enable_beta', 1) ? false : true),
										'input_class'	=> 'beta-disable'
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'enable_beta_shortcode_false',
										'disabled' 	=> (CF_Geoplugin_Global::get_the_option('enable_beta', 1) ? false : true),
										'input_class'	=> 'beta-disable'
									),
								));
								
								$general->radio(array(
									'label'		=> __('Enable Advanced Logging',CFGP_NAME),
									'name'		=> 'log_errors',
									'default'	=> CF_Geoplugin_Global::get_the_option('log_errors', 0),
									'info'		=> __('This option will log any errors and warnings in your error_log file that you can later use during technical support.',CFGP_NAME),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'log_errors_true'
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'log_errors_false'
									),
								));
								
								// Print form
								$general->html( sprintf( '<br><br><button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								
								$general->html('<h5 class="mt-5" id="Proxy_Settings">'.__('Proxy Settings',CFGP_NAME).'</h5>');
								$general->html('<p>'.sprintf(__('Some servers not share real IP because of security reasons or IP is blocked from geolocation. Using proxy you can bypass that protocols and enable geoplugin to work properly. Also, this option on individual servers can cause inaccurate geo informations, and because of that this option is disabled by default. You need to test this option on your side and use wise. Need proxy service? %1$s.',CFGP_NAME),'<a href="https://go.nordvpn.net/aff_c?offer_id=15&aff_id=14042&url_id=902" target="_blank">'.__('We have Recommended Service For You',CFGP_NAME).'</a>').'</p><p>'.__('This is usually good if you use some Onion domain or you are a general user of the private web and all your websites are in the private networks.',CFGP_NAME).'</p><hr>');
								
								
							if(CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 1):
								$general->radio(array(
									'label'		=> __('Enable Proxy',CFGP_NAME),
									'name'		=> 'proxy',
									'default'	=> CF_Geoplugin_Global::get_the_option('proxy', 0),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'proxy_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'proxy_false',
									)
								));
								
								$general->input(array(
									'label'		=> __('Proxy IP/Host',CFGP_NAME),
									'name'		=> 'proxy_ip',
									'id'		=> 'proxy_ip',
									'value'		=> CF_Geoplugin_Global::get_the_option('proxy_ip', ''),									
									'disabled' => (CF_Geoplugin_Global::get_the_option('proxy', 0) ? false : true),
									'attr'		=> array('autocomplete'=>'off'),
									'input_class'	=> 'proxy-disable'
								));
								
								$general->input(array(
									'label'		=> __('Proxy Port',CFGP_NAME),
									'name'		=> 'proxy_port',
									'id'		=> 'proxy_port',
									'value'		=> CF_Geoplugin_Global::get_the_option('proxy_port', ''),									
									'disabled' 	=> (CF_Geoplugin_Global::get_the_option('proxy', 0) ? false : true),
									'attr'		=> array('autocomplete'=>'off'),
									'input_class'	=> 'proxy-disable'
								));
								
								$general->input(array(
									'label'		=> __('Proxy Username',CFGP_NAME),
									'name'		=> 'proxy_username',
									'id'		=> 'proxy_username',
									'value'		=> CF_Geoplugin_Global::get_the_option('proxy_username', ''),									
									'disabled' 	=> (CF_Geoplugin_Global::get_the_option('proxy', 0) ? false : true),
									'attr'		=> array('autocomplete'=>'off'),
									'input_class'	=> 'proxy-disable'
								));
								
								$general->input(array(
									'label'		=> __('Proxy Password',CFGP_NAME),
									'name'		=> 'proxy_password',
									'id'		=> 'proxy_password',
									'type'		=> 'password',
									'value'		=> CF_Geoplugin_Global::get_the_option('proxy_password', ''),									
									'disabled'	=> (CF_Geoplugin_Global::get_the_option('proxy', 0) ? false : true),
									'attr'		=> array('autocomplete'=>'off'),
									'input_class'	=> 'proxy-disable'
								));
							else :
								$general->html('<p class="text-danger"><strong>'.__('This option is only available for users with Personal, Freelancer and Business license.',CFGP_NAME).'</strong></p><hr>');
							endif;
								
								$general->html( sprintf( '<button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								
								
								$general->form(array('name'=>'general-settings', 'autocomplete'=>'off', 'class' => 'cfgp_options_form'));
							?>
                        </div>
                    </div>
                </div>
                
                <div role="tabpanel" class="tab-pane fade" id="settings-google-map">
                	<div class="row">
    					<div class="col-12">
                        	<?php
                            	$gmap = new CF_Geoplugin_Form;						
								$gmap->html('<h5 class="mt-3">'.__('Google Map Settings',CFGP_NAME).'</h5>');
								$gmap->html('<p>'.__('This settings is for Google Map API services.',CFGP_NAME).'</p><hr>');
								
								$gmap->input(array(
									'label'		=> __('Google Map API Key',CFGP_NAME),
									'name'		=> 'map_api_key',
									'id'		=> 'map_api_key',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_api_key', ''),
									'attr'		=> array('autocomplete'=>'off'),
									'html'		=> '<a onclick="cf_geoplugin_popup(\'https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&amp;keyType=CLIENT_SIDE&amp;reusekey=true\',\''.__('Google Map API Key',CFGP_NAME).'\',\'1024\',\'450\'); " href="javascript:void(0);"><strong>'.__('GET API KEY',CFGP_NAME).'</strong></a>',
									'info'		=> __('In some countries Google Maps JavaScript API applications require authentication.',CFGP_NAME)
								));
								
								$gmap->input(array(
									'label'		=> __('Default Latitude',CFGP_NAME),
									'name'		=> 'map_latitude',
									'id'		=> 'map_latitude',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_latitude', ''),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Leave blank for CF Geo Plugin default support or place custom value.',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:200px;')
								));
								
								$gmap->input(array(
									'label'		=> __('Default Longitude',CFGP_NAME),
									'name'		=> 'map_longitude',
									'id'		=> 'map_longitude',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_longitude', ''),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Leave blank for CF Geo Plugin default support or place custom value.',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:200px;')
								));
								
								$gmap->input(array(
									'label'		=> __('Default Map Width',CFGP_NAME),
									'name'		=> 'map_width',
									'id'		=> 'map_width',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_width', '100%'),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:80px;')
								));
								
								$gmap->input(array(
									'label'		=> __('Default Map Height',CFGP_NAME),
									'name'		=> 'map_height',
									'id'		=> 'map_height',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_height', '400px'),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Accept numeric value in percentage or pixels (% or px)',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:80px;')
								));
								
								
								$map_zoom_options=array();
								for($i=1; $i <= 18; ++$i){
									$map_zoom_options[$i]['value']=$i;
									$map_zoom_options[$i]['text']=$i;
								}
								$gmap->select(array_merge(array(
									'label'		=> __('Default Max Zoom',CFGP_NAME),
									'name'		=> 'map_zoom',
									'id'		=> 'map_zoom',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_zoom', 8),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Most roadmap imagery is available from zoom levels 0 to 18.',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:50px;'),
								),$map_zoom_options));
								
								$gmap->radio(array(
									'label'		=> __('Zooming',CFGP_NAME),
									'name'		=> 'map_scrollwheel',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_scrollwheel', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'map_scrollwheel_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'map_scrollwheel_false',
									),
									'info'		=> __('If disabled, disables scrollwheel zooming on the map.',CFGP_NAME),
								));
								
								$gmap->radio(array(
									'label'		=> __('Navigation',CFGP_NAME),
									'name'		=> 'map_navigationControl',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_navigationControl', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'map_navigationControl_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'map_navigationControl_false',
									),
									'info'		=> __('If disabled, disables navigation on the map. The initial enabled/disabled state of the Map type control.',CFGP_NAME),
								));
								
								$gmap->radio(array(
									'label'		=> __('Map Type Control',CFGP_NAME),
									'name'		=> 'map_mapTypeControl',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_mapTypeControl', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'map_mapTypeControl_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'map_mapTypeControl_false',
									),
									'info'		=> __('The initial enabled/disabled state of the Map type control.',CFGP_NAME),
								));
								
								$gmap->radio(array(
									'label'		=> __('Scale Control',CFGP_NAME),
									'name'		=> 'map_scaleControl',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_scaleControl', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'map_scaleControl_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'map_scaleControl_false',
									),
									'info'		=> __('The initial display options for the scale control.',CFGP_NAME),
								));
								
								$gmap->radio(array(
									'label'		=> __('Draggable',CFGP_NAME),
									'name'		=> 'map_draggable',
									'default'	=> CF_Geoplugin_Global::get_the_option('map_draggable', 1),
									array(
										'text'	=> __('Enable',CFGP_NAME),
										'value'	=> 1,
										'id'	=> 'map_draggable_true',
									),
									array(
										'text'	=> __('Disable',CFGP_NAME),
										'value'	=> 0,
										'id'	=> 'map_draggable_false',
									),
									'info'		=> __('If disabled, the object can be dragged across the map and the underlying feature will have its geometry updated.',CFGP_NAME),
								));
								
								$gmap->input(array(
									'label'		=> __('Info Box Max Width',CFGP_NAME),
									'name'		=> 'map_infoMaxWidth',
									'type'		=> 'number',
									'id'		=> 'map_infoMaxWidth',
									'value'		=> CF_Geoplugin_Global::get_the_option('map_infoMaxWidth', 200),
									'attr'		=> array('autocomplete'=>'off'),
									'info'		=> __('Maximum width of info popup inside map (integer from 0 to 600).',CFGP_NAME),
									'attr'		=> array('style'=>'max-width:80px;','min'=>0, 'max'=>600)
								));
								
								// Print form
								$gmap->html( sprintf( '<br><br><button type="submit" class="btn btn-success pull-right cfgp_save_options">%s</button>', __( 'Update All Options', CFGP_NAME ) ) );
								$gmap->form(array('name'=>'google-map-settings', 'autocomplete'=>'off', 'class' => 'cfgp_options_form'));
							?>
                		</div>
                	</div>
                </div>
                <?php if(!CFGP_DEFENDER_ACTIVATED) : ?>
                <div role="tabpanel" class="tab-pane fade" id="settings-license">
                	<div class="row">
    					<div class="col-12">
                        	<?php include_once dirname(__FILE__).'/license.php'; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div role="tabpanel" class="tab-pane fade" id="settings-rest-api">
                	<div class="row">
    					<div class="col-12 pb-5">
                        	<h5 class="mt-3"><?php _e('REST API Setup',CFGP_NAME) ?></h5>
                            <?php if(CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) < 4): ?>
                            <h5 class="mt-3 text-danger"><?php _e('NOTE: The REST API is only functional for the Business License',CFGP_NAME) ?></h5>
                            <?php endif; ?>
                            <p><?php _e('The CF Geo Plugin REST API allows external apps to use geo informations and made your WordPress like geo informations provider.',CFGP_NAME) ?></p>
                            <h5><?php _e('API KEY',CFGP_NAME) ?>:</h5>
                            <div><code style="font-size: large;width: 100%;text-align: center;font-weight: 800;padding: 10px"><?php echo $CF_GEOPLUGIN_OPTIONS['id']; ?></code></div>
                            <h5 class="mt-3"><?php _e('Secret API KEY',CFGP_NAME) ?>:</h5>
                            <div><code id="cf-geoplugin-secret-key" style="font-size: large;width: 100%;text-align: center;font-weight: 800;padding: 10px"><?php echo isset($CF_GEOPLUGIN_OPTIONS['rest_secret']) && !is_array($CF_GEOPLUGIN_OPTIONS['rest_secret']) && !empty($CF_GEOPLUGIN_OPTIONS['rest_secret']) ? $CF_GEOPLUGIN_OPTIONS['rest_secret'] : ' - ' . __('Generate Secret Key',CFGP_NAME) . ' - '; ?></code> <button type="button" class="btn btn-sm btn-secondary ml-3" id="cf-geoplugin-generate-secret-key"><?php _e('Generate Secret Key',CFGP_NAME) ?></button></div>
                            
                            <h5 class="mt-3"><?php _e('Documentation',CFGP_NAME) ?>:</h5>
                            <p><?php _e('This API is designed to provide easy and secure access to geo information on your site sending simple POST or GET requests and receiving JSON formatted data. Through this API, you can easily connect via any programming language that allows crossdomain communication.',CFGP_NAME) ?></p>
                            
                            <div class="row ml-1 mr-1">
                                <div class="nav col-sm-2 flex-column nav-pills" id="cf-geo-rest-tab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" id="cf-geo-rest-info-tab" data-toggle="pill" href="#cf-geo-rest-info" role="tab" aria-controls="cf-geo-rest-info" aria-selected="true"><?php _e('Authentication',CFGP_NAME) ?><div class="cfgp-arrow"></div></a>
                                    <a class="nav-link" id="cf-geo-rest-info-tab-lookup-tab" data-toggle="pill" href="#cf-geo-rest-info-tab-lookup" role="tab" aria-controls="cf-geo-rest-info-tab-lookup" aria-selected="false"><?php _e('Lookup',CFGP_NAME) ?><div class="cfgp-arrow"></div></a>
                                    <a class="nav-link" id="cf-geo-rest-info-tab-token-tab" data-toggle="pill" href="#cf-geo-rest-info-tab-token" role="tab" aria-controls="cf-geo-rest-info-tab-token" aria-selected="false"><?php _e('Available Tokens',CFGP_NAME) ?><div class="cfgp-arrow"></div></a>
                                </div>
                                <div class="col-sm-10 tab-content" id="cf-geo-rest-tabContent">
                                    <div class="tab-pane border border-secondary rounded pt-1 pb-1 pl-3 pr-3 fade show active" id="cf-geo-rest-info" role="tabpanel" aria-labelledby="cf-geo-rest-info-tab">
                                    	<h5 class="mt-3"><?php _e('Authentication endpoint',CFGP_NAME) ?>:</h5>
                                        <p><?php _e('Endpoint used to authenticate connection between CF Geo Plugin on your site and your external app.',CFGP_NAME) ?></p>
                                        <p><code><?php echo self_admin_url('admin-ajax.php?action=cf_geoplugin_authenticate'); ?></code></p>
                                        <p><?php _e('Expected GET or POST parameters.',CFGP_NAME) ?></p>
                                        <table class="table">
                                        	<tr>
                                            	<th style="width:25%"><?php _e('Parameter',CFGP_NAME) ?></th>
                                                <th style="width:13%"><?php _e('Type',CFGP_NAME) ?></th>
                                                <th style="width:13%"><?php _e('Obligation',CFGP_NAME) ?></th>
                                                <th><?php _e('Description',CFGP_NAME) ?></th>
                                            </tr>
                                            <tr>
                                            	<td><kbd>action</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Endpoint action. Should always be: <strong>cf_geoplugin_authenticate</strong>',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>api_key</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('API KEY',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>secret_key</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Secret API KEY',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>app_name</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Your external application name.',CFGP_NAME) ?></td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <h5 class="mt-3"><?php _e('Return standard JSON API response format',CFGP_NAME) ?>:</h5>
                                        <pre class="bg-light">{
    "error" : false,
    "error_message" : NULL,
    "code" : 200,
    "access_token" : " - generated access token - ",
    "message" : "Successful Authentication"
}</pre>
										<table class="table">
                                        	<tr>
                                            	<th style="width:27%"><?php _e('Parameter',CFGP_NAME) ?></th>
                                                <th style="width:25%"><?php _e('Type',CFGP_NAME) ?></th>
                                                <th><?php _e('Description',CFGP_NAME) ?></th>
                                            </tr>
                                            <tr>
                                            	<td><kbd>error</kbd></td>
                                                <td>bool</td>
                                                <td>true / false</td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>error_message</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('Return only when error exists',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>code</kbd></td>
                                                <td>integer</td>
                                                <td><?php _e('HTTP status code',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>access_token</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('Return access token only when authentication is successful',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>message</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('Return only when authentication is successful',CFGP_NAME) ?></td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <p><?php _e('When you receive your access token, you need to save it in a database or integrate it within the code in your external app and it serves for further linking to your site.',CFGP_NAME) ?></p>
                                    </div>
                                    <div class="tab-pane border border-secondary rounded pt-1 pb-1 pl-3 pr-3 fade" id="cf-geo-rest-info-tab-lookup" role="tabpanel" aria-labelledby="cf-geo-rest-info-tab-lookup-tab">
                                    	<h5 class="mt-3"><?php _e('Lookup endpoint',CFGP_NAME) ?>:</h5>
                                        <p><?php _e('Endpoint used to lookup IP address informations. To make this work properly, you must have a valid KEY and Access Token API.',CFGP_NAME) ?></p>
                                        <p><code><?php echo self_admin_url('admin-ajax.php?action=cf_geoplugin_lookup'); ?></code></p>
                                        <p><?php _e('Expected GET or POST parameters.',CFGP_NAME) ?></p>
                                        <table class="table">
                                        	<tr>
                                            	<th style="width:25%"><?php _e('Parameter',CFGP_NAME) ?></th>
                                                <th style="width:13%"><?php _e('Type',CFGP_NAME) ?></th>
                                                <th style="width:13%"><?php _e('Obligation',CFGP_NAME) ?></th>
                                                <th><?php _e('Description',CFGP_NAME) ?></th>
                                            </tr>
                                            <tr>
                                            	<td><kbd>action</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Endpoint action. Should always be: <strong>cf_geoplugin_lookup</strong>',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>api_key</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('API KEY',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>access_token</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Generated access token',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>ip</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('required',CFGP_NAME) ?></td>
                                                <td><?php _e('Client IP address',CFGP_NAME) ?></td>
                                            </tr>
                                            <tr>
                                            	<td><kbd>base_currency</kbd></td>
                                                <td>string</td>
                                                <td><?php _e('optional',CFGP_NAME) ?></td>
                                                <td><?php _e('The base currency (transaction currency) - The currency by which conversion is checked by geo location. Default: <strong>USD</strong>',CFGP_NAME) ?></td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <h5 class="mt-3"><?php _e('Return standard JSON API response format',CFGP_NAME) ?>:</h5>
                                        <pre class="bg-light">{
<?php
if( CF_Geoplugin_Global::get_the_option('first_plugin_activation', 1) ) $this->update_option( 'first_plugin_activation', 0 );

$remove = array(
	'status',
	'lookup',
	'version',
	'credit',
	'dmaCode',
	'areaCode',
	'continentCode',
	'currencySymbol',
	'currencyConverter'
);

foreach($CFGEO as $key => $value) :
if(!(in_array($key, $remove, true) !== false))
{
if($key == 'error') $value = 'false';
echo "	\"{$key}\" : " . ($value === 0 ? 0 : ($value === '' ? '""' : (is_int($value) || in_array($value, array('true','false')) || is_float($value) ? $value : '"' . str_replace('/','\\/',esc_attr($value)) . '"'))) . ",\n";	
}
endforeach;
?>
	"code" : <?php echo $CFGEO['status'] . "\n"; ?>
}</pre>
										<p><?php _e('You can use these JSON information in your external app anywhere. TIP: In order for your external app to be fast, it would be good to make this call once and record in a temporary session that will expire after few minutes.',CFGP_NAME) ?></p>
                                    </div>
                                    <div class="tab-pane border border-secondary rounded pt-1 pb-1 pl-3 pr-3 fade" id="cf-geo-rest-info-tab-token" role="tabpanel" aria-labelledby="cf-geo-rest-info-tab-token-tab">
                                    	<h5 class="mt-3"><?php _e('Available Tokens',CFGP_NAME) ?>:</h5>
                                        <p><?php _e('Here is a list of registered access tokens that are active on your site. You can also disable any active access token.',CFGP_NAME) ?></p>
                                        <table class="table table-sm table-striped">
                                        <thead>
                                        	<tr>
                                            	<th style="width:50%"><?php _e('Access Token',CFGP_NAME) ?></th>
                                                <th style="width:20%"><?php _e('App name',CFGP_NAME) ?></th>
                                                <th style="width:18%"><?php _e('Date',CFGP_NAME) ?></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
										<?php if(isset($CF_GEOPLUGIN_OPTIONS['rest_token']) && isset($CF_GEOPLUGIN_OPTIONS['rest_token_info']) && is_array($CF_GEOPLUGIN_OPTIONS['rest_token_info']) && count($CF_GEOPLUGIN_OPTIONS['rest_token_info']) > 0) : ?>
                                        	<?php foreach($CF_GEOPLUGIN_OPTIONS['rest_token'] as $i => $token) : ?>
												<?php if(isset($CF_GEOPLUGIN_OPTIONS['rest_token_info'][$token])) : $token_info = $CF_GEOPLUGIN_OPTIONS['rest_token_info'][$token]; ?>
                                                <tr id="<?php echo $token; ?>">
                                                    <td style="text-wrap:suppress; word-break:break-all"><?php echo $token; ?></td>
                                                    <td><?php echo $token_info['app_name']; ?></td>
                                                    <td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), (int)$token_info['time']); ?></td>
                                                    <td class="text-right">
                                                    	<button type="button" class="btn btn-sm btn-danger cf-geoplugin-delete-token" data-token="<?php echo $token; ?>"><?php _e('Delete Token',CFGP_NAME) ?></button>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                        	<tr>
                                                <td colspan="4"><?php _e('There are no registered applications yet.',CFGP_NAME) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php do_action('page-cf-geoplugin-settings-tab-content'); ?>
                
                <div role="tabpanel" class="tab-pane fade" id="settings-info">
                	<div class="row">
    					<div class="col-12">
                        	<?php include_once dirname(__FILE__).'/settings/credits.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>