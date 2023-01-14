<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( CFGP_U::dev_mode() ) {
	$remove_tags = [];
} else {
	$remove_tags = array(
		'is_eu',
		'is_vat',
		'is_mobile',
		'is_proxy',
		'is_spam',
		'license_hash'
	);
}

$remove_tags = apply_filters('cfgp/debug/remove_tags', $remove_tags);

$API = CFGP_Cache::get('API');
if($NEW_API = CFGP_API::lookup(CFGP_U::request_string('cfgp_lookup'))){
	$API = $NEW_API;
}

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? NULL)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-globe"></i> <?php _e('Debug', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

        	<div id="post-body">
            	<div id="post-body-content">
					
                    <div class="tablenav top">
                    	<form method="get" autocomplete="off">
                        	<input type="text" value="<?php echo esc_attr(CFGP_U::request_string('cfgp_lookup')); ?>" name="cfgp_lookup" placeholder="<?php esc_attr_e('IP Lookup', 'cf-geoplugin'); ?>: <?php echo esc_attr($API['ip'] ?? NULL); ?>" autocomplete="off"> 
                            <button type="submit" class="button button-primary"><?php _e('Lookup', 'cf-geoplugin'); ?></button>
                            <a href="<?php echo esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin-debug')); ?>" target="_self" class="button" title=""><i class="cfa cfa-refresh"></i></a>
                            <input type="hidden" name="page" value="cf-geoplugin-debug">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(CFGP_NAME . '-lookup'); ?>">
                        </form>
                    </div>
                    
                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
						<?php do_action('cfgp/debug/nav-tab/before'); ?>
                        	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#recived-data"><i class="cfa cfa-database"></i><span class="label"> <?php _e('Recived data', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#sent-data"><i class="cfa cfa-share-square"></i><span class="label"> <?php _e('Sent data', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#server-statistics"><i class="cfa cfa-server"></i><span class="label"> <?php _e('Server statistics', 'cf-geoplugin'); ?></span></a>
                            <?php if( CFGP_Options::get('enable_gmap', 0) ): ?>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#google-map"><i class="cfa cfa-globe"></i><span class="label"> <?php _e('Google map', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                       <!--     <a href="javascript:void(0);" class="nav-tab" data-id="#debugger"><i class="cfa cfa-bug"></i><span class="label"> <?php _e('Debugger', 'cf-geoplugin'); ?></span></a>  -->
					   <?php do_action('cfgp/debug/nav-tab/after'); ?>
                        </nav>
                        
						<?php do_action('cfgp/debug/tab-panel/before'); ?>
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="recived-data">
                        <p><?php echo sprintf( __( 'Information that the Geo Controller API ver.%s receives', 'cf-geoplugin'), CFGP_VERSION ); ?></p>
                        <?php if($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-recived-data">
                                <thead>
                                    <tr>
                                        <th><?php _e('Return Field', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(apply_filters('cfgp/table/debug', array_merge(
										array('cfgeo_flag' => CFGP_U::admin_country_flag($API['country_code'] ?? NULL)), 
										$API
									), $API) as $key => $value) : if(in_array($key, $remove_tags)) continue; ?>
                                    <tr>
                                    <?php if(in_array($key, array('cfgeo_flag'))) : ?>
                                    	<td>&nbsp;</td>
                                    <?php else : ?>
                                    	<td><b><?php echo esc_attr($key); ?></b></td>
                                    <?php endif; ?>
                                        <td><?php
											if( in_array($key, ['cfgeo_flag', 'credit', 'error_message']) ) {
												echo wp_kses_post( $value || is_numeric($value) ? $value : '-' );
											} else {
												echo esc_html( $value || is_numeric($value) ? $value : '-' );
											}
										?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Return Field', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif; ?>
                        </div>
                        
                        <div class="cfgp-tab-panel" id="sent-data">
                        	<p><?php _e( 'This information are sent to Geo Controller API. All of this informations (hostname, IP and timezone) are available for general public, world wide and we only use them for API purpose which helps plugin to determine the exact location of the visitors and prevent accidental collapse between the IP address. Your IP and email address is also a guarantee that you\'re not a robot or some spamming software.', 'cf-geoplugin'); ?></p>
                            <p><?php printf( __( 'If you are concerned about your private informations, please read the %s', 'cf-geoplugin'), '<a href="http://cfgeoplugin.com/privacy-policy" target="_blank">'.__('Privacy Policy', 'cf-geoplugin').'</a>' ); ?></p>
                            
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics"> 
                                <thead>
                                    <tr>
                                        <th><?php _e('Name', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Value', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Info', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?php _e( 'IP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_U::request_string('cfgp_lookup', CFGP_IP::get()) ); ?></td>
                                        <td><?php _e( 'Your or Visitor\'s IP Address', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Timestamp', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_TIME ); ?></td>
                                        <td><?php _e( 'Server Current Unix Timestamp', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'SIP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_IP::server() ) . (CFGP_U::proxy() ?' <strong><a class="text-danger" href="'.admin_url('admin.php?page=cf-geoplugin-settings').'">('.esc_html__('Proxy Enabled', 'cf-geoplugin').')</a></strong> ' : ''); ?></td>
                                        <td><?php _e( 'Server IP Address' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Host', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_U::get_host(true) ); ?></td>
                                        <td><?php _e( 'Server Host Name', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'Email' ); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'admin_email' ) ); ?></td>
                                        <td><?php _e('Admin e-mail address.', 'cf-geoplugin'); ?> <?php _e('Only reason why we collect your email address is because plugin support and robot prevention. Your email address will NOT be spammed or shared with 3rd party in any case and you can any time request from us on email <a href="mailto:support@cfgeoplugin.com">support@cfgeoplugin.com</a> to remove your all personal data from our system by GDPR rules.', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin Version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_VERSION ); ?></td>
                                        <td><?php _e( 'Geo Controller Version', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'WordPress Version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                                        <td><?php _e( 'We use the WordPress version for statistics and debugging.', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'Spam Check', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( ( (
											CFGP_Options::get('enable_spam_ip', 0) 
											&& CFGP_Options::get('enable_defender', 0) 
											&& CFGP_License::level( CFGP_Options::get('license_sku') ) > 0 
										) ? 'true' : 'false' ) ); ?></td>
                                        <td><?php _e( 'Sends a parameter that triggers a spam check on the site.', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'License', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
											if(CFGP_DEFENDER_ACTIVATED)
												echo esc_html( get_option("cf_geo_defender_api_key") );
											else
												echo esc_html( CFGP_Options::get('license_key') );
										?></td>
                                        <td>
											<?php _e( 'Geo Controller License Key', 'cf-geoplugin'); ?>
											<?php
											if(CFGP_DEFENDER_ACTIVATED)
												_e( 'Lifetime', 'cf-geoplugin');
											else
												echo ( !empty( CFGP_License::expire_date() ) ? '<br><small>('.__( 'License Expire', 'cf-geoplugin') . ': <b>' . esc_html(date("r", CFGP_License::expire_date())).'</b>)</small>' : '' )
										?>
										</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <div class="cfgp-tab-panel" id="server-statistics">
                        	<p><?php printf( __( 'Information of your WordPress installation, server and browser', 'cf-geoplugin'), CFGP_VERSION ); ?></p>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics">
                                <thead>
                                    <tr>
                                        <th width="30%"><?php _e('Field', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<tr>
                                        <td><strong><?php _e( 'Plugin ID', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_U::ID() ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin installed', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
											$plugin_installed = get_option(CFGP_NAME . '-activation');
											if($plugin_installed && is_array($plugin_installed)){
												$plugin_installed = array_shift($plugin_installed);
												echo esc_html( date(CFGP_DATE_TIME_FORMAT, strtotime($plugin_installed)) );
											} else {
												$plugin_installed = NULL;
												 echo '-';
											}
										?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin updated', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
											$plugin_activation = get_option(CFGP_NAME . '-activation');
											if($plugin_activation && is_array($plugin_activation)){
												$plugin_activation = end($plugin_activation);
												if($plugin_activation != $plugin_installed) {
													echo esc_html( date(CFGP_DATE_TIME_FORMAT, strtotime($plugin_activation)) );
												} else echo '-';
											} else echo '-';
										?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Deprecated support', 'cf-geoplugin'); ?></strong></td>
                                        <td>
										<?php if( defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS ) : ?>
											<strong class="text-success"><?php _e( 'Deprecated code support is activated.', 'cf-geoplugin'); ?></strong>
											<br><?php _e( 'For now, we\'ve approved the use of deprecated code by default to make transition easier. We recommend that you switch your project to new code as we will be removing support for deprecated code in the future.', 'cf-geoplugin'); ?>
										<?php else : ?>
											<strong class="text-default"><?php _e( 'Deprecated code support is not active.', 'cf-geoplugin'); ?></strong>
										<?php endif; ?>
										</td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'Server type', 'cf-geoplugin'); ?></strong></td>
                                        <td>
										<?php if(CFGP_IP::is_localhost()) : ?>
											<strong class="text-danger"><?php _e( 'Local Server', 'cf-geoplugin'); ?></strong>
										<?php else : ?>
											<strong class="text-success"><?php _e( 'Production Server', 'cf-geoplugin'); ?></strong>
										<?php endif; ?>
										</td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'Site title', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'name' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Tagline', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'description' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress address (URL)', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'wpurl' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress host', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_U::get_host() ); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'Server IP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_IP::server() ) . (CFGP_U::proxy() ?' <strong><a class="text-danger" href="'.admin_url('admin.php?page=cf-geoplugin-settings').'">('.__('Proxy Enabled', 'cf-geoplugin').')</a></strong> ' : ''); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress multisite', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo (CFGP_MULTISITE ? __('Enabled', 'cf-geoplugin') : __('Disabled', 'cf-geoplugin')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Admin email', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'admin_email' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Encoding for pages and feeds', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'charset' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Content-Type', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'html_type' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Language', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( get_bloginfo( 'language' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Server time', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( date( 'r' ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress directory path', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( ABSPATH ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP: Version', 'cf-geoplugin'); ?></strong></td>
                                        <td>PHP <?php echo esc_html( PHP_VERSION ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP: Version ID', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( PHP_VERSION_ID ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP: Architecture', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php printf(__('%dbit', 'cf-geoplugin'), (CFGP_OS::is_php64() ? 64 : 32)); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'PHP: Memory usage of the plugin', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_U::filesize(CFGP_Cache::get_size(), 2) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP: Operting system', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_OS::get() ); ?> <?php printf(__('%dbit', 'cf-geoplugin'), CFGP_OS::architecture()); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Browser', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php printf(_x('%1$s (%2$s)', 'Debug: User agent (Browser)', 'cf-geoplugin'), esc_html( CFGP_Browser::instance()->getBrowser() ), esc_html( CFGP_Browser::instance()->getVersion() )); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php _e( 'User platform', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( trim( sprintf(
											'%1$s %2$s',
											esc_html( CFGP_Browser::instance()->getPlatform() ),
											(CFGP_Browser::instance()->isMobile() ? __( '(mobile device)', 'cf-geoplugin') : '')
										) ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress debug', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo ( WP_DEBUG ? '<strong><span class="text-danger">' . __( 'On', 'cf-geoplugin') . '</span></strong>' : __( 'Off', 'cf-geoplugin') ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin directory path', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html( CFGP_ROOT ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Session API expire', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php printf(__('%d minutes', 'cf-geoplugin'), CFGP_SESSION); ?></td>
                                    </tr>
                                </tbody>
                        	</table>
                        </div>
                        <?php if( CFGP_Options::get('enable_gmap', 0) ): ?>
                        <div class="cfgp-tab-panel" id="google-map">
                        <?php
                        	echo do_shortcode( '[cfgeo_map width="100%" height="600px" longitude="'.esc_attr( $API['longitude']??NULL ).'" latitude="'.esc_attr( $API['latitude']??NULL ).'"]
								<address>
									<strong><big>'.CFGP_U::admin_country_flag($API['country_code']??NULL).' '.esc_html($API['ip']??NULL).'</big></strong><br /><br />
									'.esc_html($API['city']??NULL).'<br />
									'.esc_html($API['region']??NULL).(!empty($API['region_code']??NULL)?' ('.$API['region_code']??NULL.')':'').'<br />
									'.esc_html($API['country']??NULL).'<br />
									'.esc_html($API['continent']??NULL).(!empty($API['country_code']??NULL)?' ('.esc_html($API['country_code']??NULL).')':'').'<br /><br />
									'.esc_html($API['longitude']??NULL.', '.$API['latitude']??NULL).'<br /><br />
									'.esc_html($API['timezone']??NULL).'
								</address>
							[/cfgeo_map]' );
						?>
                        </div>
                        <?php endif; ?>
                    <!--    <div class="cfgp-tab-panel" id="debugger"></div>    -->
					</div>
					
					<?php do_action('cfgp/debug/tab-panel/after'); ?>
                    
                </div>
            </div>
			
			<div class="inner-sidebar" id="<?php echo esc_attr(CFGP_NAME); ?>-debug-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action('cfgp/page/debug/sidebar'); ?>
				</div>
			</div>
			
            <br class="clear">
        </div>
    </div>
</div>
