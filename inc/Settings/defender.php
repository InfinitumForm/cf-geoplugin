<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( CFGP_U::request_bool('preview'))
{
	die( wpautop( html_entity_decode( stripslashes( CFGP_Options::get('block_country_messages') ) ) ) );
	exit;
}

if(CFGP_U::request_bool('save_defender') && wp_verify_nonce(sanitize_text_field($_REQUEST['nonce']), CFGP_NAME.'-save-defender') !== false && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')
{
	if( !isset( $_POST['block_country'] ) ) {
		CFGP_Options::set( 'block_country', '' );
	}
	if( !isset( $_POST['block_region'] ) ) {
		CFGP_Options::set( 'block_region', '' );
	}
	if( !isset( $_POST['block_city'] ) ) {
		CFGP_Options::set( 'block_city', '' );
	}
	if( !isset( $_POST['block_proxy'] ) ) {
		CFGP_Options::set( 'block_proxy', 0 );
	}
	if( !isset( $_POST['ip_whitelist'] ) ) {
		CFGP_Options::set( 'ip_whitelist', '' );
	}

	$updates = [];
	/*
	 * Let's read POST, sanitize and save inside database
	 *
	 * Santization is added inside CFGP_Options::sanitize();
	 */
	foreach( $_POST as $key => $value )
	{
		if($key == 'submit') continue;
		
		$updates[] = CFGP_Options::set( $key, $value );
	}
	
	if( in_array( 'false', $updates ) !== false || count( $updates ) == 0 )
	{
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			__('There is an error. Settings not saved.', 'cf-geoplugin')
		);
	}
	else
	{
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			__('Settings saved.', 'cf-geoplugin')
		);
	}
}

$block_country = CFGP_Options::get('block_country');
if(!empty($block_country) && !is_array($block_country) && preg_match('/\]|\[/', $block_country)){
	$block_country = explode(']|[', $block_country);
}

$block_region = CFGP_Options::get('block_region');
if(!empty($block_region) && !is_array($block_region) && preg_match('/\]|\[/', $block_region)){
	$block_region = explode(']|[', $block_region);
}

$block_city = CFGP_Options::get('block_city');
if(!empty($block_city) && !is_array($block_city) && preg_match('/\]|\[/', $block_city)){
	$block_city = explode(']|[', $block_city);
}

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? NULL)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-lock"></i> <?php _e('Anti Spam Protection & Site Restriction', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

        	<div id="post-body">
            	<div id="post-body-content">
					<form method="post" action="<?php echo esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin-defender&save_defender=true&nonce='.wp_create_nonce(CFGP_NAME.'-save-defender'))); ?>">
                    	<div class="nav-tab-wrapper-chosen">
                        	<nav class="nav-tab-wrapper">
                            	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#defender-settings"><i class="cfa cfa-wrench"></i><span class="label"> <?php _e('General Defender Settings', 'cf-geoplugin'); ?></span></a>
                                <a href="javascript:void(0);" class="nav-tab" data-id="#defender-settings-page"><i class="cfa cfa-file"></i><span class="label"> <?php _e('Defender page', 'cf-geoplugin'); ?></span></a>
                                <a href="<?php echo esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin-defender&preview=true')); ?>" class="nav-tab" target="_blank"><i class="cfa cfa-desktop"></i><span class="label"> <?php _e('Preview', 'cf-geoplugin'); ?></span></a>
                            </nav>
                            
                            <div class="cfgp-tab-panel cfgp-tab-panel-active" id="defender-settings">
                            	<p><?php _e('With Anti Spam Protection you can block the access from the specific IP, country, state and city to your site. Names of countries, states, regions or cities are not case sensitive, but the name must be entered correctly (in English) to get this feature work correctly. This feature is very safe and does not affect SEO.', 'cf-geoplugin'); ?></p>
                                
                                <div class="nav-tab-wrapper-chosen cfgp-country-region-city-multiple-form">
                                    <nav class="nav-tab-wrapper">
                                        <a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#ip-restriction"><i class="cfa cfa-shield"></i><span class="label"> <?php _e('IP Restriction', 'cf-geoplugin'); ?></span></a>
                                        <a href="javascript:void(0);" class="nav-tab" data-id="#location-restriction"><i class="cfa cfa-globe"></i><span class="label"> <?php _e('Location Restriction', 'cf-geoplugin'); ?></span></a>
										<a href="javascript:void(0);" class="nav-tab" data-id="#proxy-restriction"><i class="cfa cfa-sitemap"></i><span class="label"> <?php _e('Proxy Restriction', 'cf-geoplugin'); ?></span></a>
										<a href="javascript:void(0);" class="nav-tab" data-id="#whitelist"><i class="cfa cfa-list"></i><span class="label"> <?php _e('Whitelist', 'cf-geoplugin'); ?></span></a>
                                    </nav>
                                    <div class="cfgp-tab-panel cfgp-tab-panel-active" id="ip-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_ip"><?php _e('IP address separated by comma or by new line', 'cf-geoplugin'); ?>:</label>
                                            <textarea class="form-control" id="block_ip" name="block_ip" rows="5" style="min-height:115px"><?php echo wp_kses_post(CFGP_Options::get('block_ip', '')); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="cfgp-tab-panel" id="location-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_country"><?php _e('Choose Countries', 'cf-geoplugin'); ?>:</label>
											<?php
												CFGP_Form::select_countries(
													array(
														'name'=>'block_country',
														'id' => 'block_country'
													),
													$block_country,
													true
												);
											?>
                                            <br>
                                            <button type="button" class="button cfgp-select-all" data-target="block_country"><object data="<?php echo esc_url(CFGP_ASSETS . '/images/select.svg'); ?>" width="10" height="10"></object> <?php esc_attr_e( 'Select/Deselect all', 'cf-geoplugin'); ?></button>
                                        </div>
										<div class="cfgp-form-group">
                                            <label for="block_region"><?php _e('Choose Region', 'cf-geoplugin'); ?>:</label>
                                            <?php
												CFGP_Form::select_regions(
													array(
														'name'=>'block_region',
														'id' => 'block_region',
														'country_code' => $block_country
													),
													$block_region,
													true
												);
											?>
											<!-- br>
                                            <button type="button" class="button cfgp-select-all" data-target="block_region"><object data="<?php echo esc_url(CFGP_ASSETS . '/images/select.svg'); ?>" width="10" height="10"></object> <?php esc_attr_e( 'Select/Deselect all', 'cf-geoplugin'); ?></button -->
                                        </div>
										<div class="cfgp-form-group">
                                            <label for="block_city"><?php _e('Choose Cities', 'cf-geoplugin'); ?>:</label>
                                            <?php
												CFGP_Form::select_cities(
													array(
														'name'=>'block_city',
														'id' => 'block_city',
														'country_code' => $block_country
													),
													$block_city,
													true
												);
											?>
											<!-- br>
                                            <button type="button" class="button cfgp-select-all" data-target="block_city"><object data="<?php echo esc_url(CFGP_ASSETS . '/images/select.svg'); ?>" width="10" height="10"></object> <?php esc_attr_e( 'Select/Deselect all', 'cf-geoplugin'); ?></button -->
                                        </div>
                                    </div>
									<div class="cfgp-tab-panel" id="proxy-restriction">
										<div class="cfgp-form-group">
											<p><?php _e( 'Protect your site from unwanted visitors using proxies and VPNs with just one click.', 'cf-geoplugin'); ?></p>
											<div class="cfgp-form-group-checkboxes">
												<?php
													CFGP_Form::radio(
														array(
															1 => esc_html__('Block Proxy', 'cf-geoplugin'),
															0 => esc_html__('Do not block Proxy', 'cf-geoplugin')
														),
														array(
															'name'=>'block_proxy',
															'id'=>'block_proxy_enable'
														),
														CFGP_Options::get('block_proxy', 0)
													);
												?>
											</div>
										</div>
									</div>
									<div class="cfgp-tab-panel" id="whitelist">
                                    	<div class="cfgp-form-group">
                                            <label for="ip_whitelist"><?php _e('Enter the IP addresses you want to whitelist and separate them with a comma or a new line', 'cf-geoplugin'); ?>:</label>
                                            <textarea class="form-control" id="ip_whitelist" name="ip_whitelist" rows="5" style="min-height:115px"><?php echo wp_kses_post(CFGP_Options::get('ip_whitelist', '')); ?></textarea>
                                        </div>
                                    </div>
                                 </div>
								 
								 <p><strong><?php _e( 'Warning: This option may also block your access to the site if your ISP uses a proxy to serve internet information. Therefore, you must place one cookie in your browser to avoid this problem for you.', 'cf-geoplugin'); ?></strong></p>
								<p><?php _e( 'Copy this link and keep it in a secret and safe place:', 'cf-geoplugin'); ?></p>
								<p><strong><code><?php echo home_url( '?cfgp_admin_access=' . str_rot13(substr(CFGP_U::KEY(), 3, 32) )); ?></code></strong></p>
								<p><?php _e( 'When you check this option, we will set a cookie for you automatically but you can use this link whenever the plugin blocks you from accessing your site.', 'cf-geoplugin'); ?></p>
                                 
                                 <?php if(CFGP_Options::get('enable_spam_ip')): ?>
                                     <p><strong><?php printf(__( 'Automatic IP Address Blacklist Check is enabled. All of these IPs are from a safe source and most of them are bots and crawlers. Blacklisted IPs will be automatically recognized and blocked. If you don\'t want this kind of protection %s.', 'cf-geoplugin'),
                                        '<a href="'.admin_url('admin.php?page=cf-geoplugin-settings').'#spam-protection">'
                                            .__('disable it in plugin settings', 'cf-geoplugin')
                                        .'</a>'); ?></strong></p>
                                 <?php else: ?>
                                     <p><strong><?php printf(__( 'Automatic IP address blacklist check is NOT ENABLED. If you want additional protection %s.', 'cf-geoplugin'),
                                        '<a href="'.admin_url('admin.php?page=cf-geoplugin-settings').'#spam-protection">'
                                            .__('enable it in settings', 'cf-geoplugin')
                                        .'</a>'); ?></strong></p>
                                 <?php endif; ?>
                                 
                                 <p style="color:#cc0000;"><?php _e( 'These options will remove all your content, template, design and display custom messages to your visitors.', 'cf-geoplugin'); ?></p>
                                 <?php submit_button(); ?>
                            </div>
                            
                            <div class="cfgp-tab-panel" id="defender-settings-page">
                            	<p><?php _e('Message that is displayed to a blocked visitor (HTML allowed)', 'cf-geoplugin'); ?>:</p>
                                <div class="cfgp-form-group">
                                    <?php
                                        $settings = array( 'textarea_name'  => 'block_country_messages', 'editor_height' => 450, 'textarea_rows' => 30 );
                                        $block_country_messages = html_entity_decode( trim( CFGP_Options::get('block_country_messages') ) );
                                        if( empty( $block_country_messages ) )
                                        {
                                            $messages="<h1>Error</h1>
<h3>404 - Page not found</h3>
<p>We could not find the above page on our servers.</p>
<p>NOTE: This option is not saved!</p>";
                                            wp_editor( $messages, 'block_country_messages', $settings );
                                        }
                                        else
                                        {
                                            wp_editor( $block_country_messages, 'block_country_messages', $settings );
                                        }
                                    ?>
                                </div>
                                <?php submit_button(); ?>                           
                            </div>
                            
                    	</div>
                    </form>
                </div>
            </div>
			
			<div class="inner-sidebar" id="<?php echo esc_attr(CFGP_NAME); ?>-defender-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action('cfgp/page/defender/sidebar'); ?>
				</div>
			</div>
			
            <br class="clear">
        </div>
    </div>
</div>