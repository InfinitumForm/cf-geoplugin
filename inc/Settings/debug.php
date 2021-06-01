<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $cfgp_cache;

$remove_tags = array();

$API = $cfgp_cache->get('API');
if($NEW_API = CFGP_API::lookup(CFGP_U::request_string('cfgp_lookup'))){
	$API = $NEW_API;
}

?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-globe"></i> <?php _e('Debug', CFGP_NAME); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

				<div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-debug-sidebar">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<?php do_action('cfgp/page/debug/sidebar'); ?>
					</div>
				</div>

        	<div id="post-body">
            	<div id="post-body-content">
					
                    <div class="tablenav top">
                    	<form method="get" autocomplete="off">
                        	<input type="text" value="<?php echo CFGP_U::request_string('cfgp_lookup'); ?>" name="cfgp_lookup" placeholder="<?php _e('IP Lookup', CFGP_NAME); ?>: <?php echo $API['ip']; ?>" autocomplete="off"> 
                            <button type="submit" class="button button-primary"><?php _e('Lookup', CFGP_NAME); ?></button>
                            <a href="<?php echo admin_url('admin.php?page=cf-geoplugin-debug'); ?>" target="_self" class="button" title=""><i class="fa fa-refresh"></i></a>
                            <input type="hidden" name="page" value="cf-geoplugin-debug">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(CFGP_NAME . '-lookup'); ?>">
                        </form>
                    </div>
                    
                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
                        	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#recived-data"><i class="fa fa-database"></i><span class="label"> <?php _e('Recived data', CFGP_NAME); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#sent-data"><i class="fa fa-share-square"></i><span class="label"> <?php _e('Sent data', CFGP_NAME); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#server-statistics"><i class="fa fa-server"></i><span class="label"> <?php _e('Server statistics', CFGP_NAME); ?></span></a>
                            <?php if( CFGP_Options::get_beta('enable_gmap', 0) ): ?>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#google-map"><i class="fa fa-globe"></i><span class="label"> <?php _e('Google map', CFGP_NAME); ?></span></a>
                            <?php endif; ?>
                       <!--     <a href="javascript:void(0);" class="nav-tab" data-id="#debugger"><i class="fa fa-bug"></i><span class="label"> <?php _e('Debugger', CFGP_NAME); ?></span></a>  -->
                        </nav>
                        
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="recived-data">
                        <p><?php echo sprintf( __( 'Information that the CF Geo Plugin API ver.%s receives', CFGP_NAME ), CFGP_VERSION ); ?></p>
                        <?php if($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-recived-data">
                                <thead>
                                    <tr>
                                        <th><?php _e('Return Field',CFGP_NAME); ?></th>
                                        <th><?php _e('Return Value',CFGP_NAME); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(array_merge(
										array('cfgeo_flag' => CFGP_U::admin_country_flag($API['country_code'])), 
										$API
									) as $key => $value) : if(in_array($key, $remove_tags)) continue; ?>
                                    <tr>
                                    <?php if(in_array($key, array('cfgeo_flag'))) : ?>
                                    	<td>&nbsp;</td>
                                    <?php else : ?>
                                    	<td><b><?php echo $key; ?></b></td>
                                    <?php endif; ?>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Return Field',CFGP_NAME); ?></th>
                                        <th><?php _e('Return Value',CFGP_NAME); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif; ?>
                        </div>
                        
                        <div class="cfgp-tab-panel" id="sent-data">
                        	<p><?php _e( 'This information are sent to CF Geo Plugin API. All of this informations (hostname, IP and timezone) are available for general public, world wide and we only use them for API purpose which helps plugin to determine the exact location of the visitors and prevent accidental collapse between the IP address. Your IP and email address is also a guarantee that you\'re not a robot or some spamming software.', CFGP_NAME ); ?></p>
                            <p><?php printf( __( 'If you are concerned about your private informations, please read the %s', CFGP_NAME ), '<a href="http://cfgeoplugin.com/privacy-policy" target="_blank">'.__('Privacy Policy', CFGP_NAME).'</a>' ); ?></p>
                            
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics"> 
                                <thead>
                                    <tr>
                                        <th><?php _e('Name',CFGP_NAME); ?></th>
                                        <th><?php _e('Value',CFGP_NAME); ?></th>
                                        <th><?php _e('Info',CFGP_NAME); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?php _e( 'IP', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_U::request_string('cfgp_lookup', CFGP_IP::get()); ?></td>
                                        <td><?php _e( 'Your or Visitor\'s IP Address', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Timestamp', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_TIME; ?></td>
                                        <td><?php _e( 'Server Current Unix Timestamp', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'SIP', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_IP::server() . (CFGP_U::proxy() ?' <strong><a class="text-danger" href="'.admin_url('admin.php?page=cf-geoplugin-settings').'">('.__('Proxy Enabled',CFGP_NAME).')</a></strong> ' : ''); ?></td>
                                        <td><?php _e( 'Server IP Address' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Host', CFGP_NAME ); ?></strong></td>
                                        <td>
                                        <?php echo CFGP_U::get_host(true); ?>
                                        </td>
                                        <td><?php _e( 'Server Host Name', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Version', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_VERSION; ?></td>
                                        <td><?php _e( 'CF Geo Plugin Version', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Email' ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'admin_email' ); ?></td>
                                        <td><?php _e('Admin e-mail address.',CFGP_NAME); ?> <?php _e('Only reason why we collect your email address is because plugin support and robot prevention. Your email address will NOT be spammed or shared with 3rd party in any case and you can any time request from us on email <a href="mailto:support@cfgeoplugin.com">support@cfgeoplugin.com</a> to remove your all personal data from our system by GDPR rules.',CFGP_NAME); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'License', CFGP_NAME ); ?></strong></td>
                                        <td><?php
											if(CFGP_DEFENDER_ACTIVATED)
												echo get_option("cf_geo_defender_api_key");
											else
												echo CFGP_Options::get('license_key');
										?></td>
                                        <td>
											<?php _e( 'CF Geo Plugin License Key', CFGP_NAME ); ?>
											<?php
											if(CFGP_DEFENDER_ACTIVATED)
												_e( 'Lifetime', CFGP_NAME );
											else
												echo ( !empty( $CF_GEOPLUGIN_OPTIONS['license_expire'] ) ? '<br><small>('.__( 'License Expire', CFGP_NAME ) . ': <b>' . date("r",$CF_GEOPLUGIN_OPTIONS['license_expire']).'</b>)</small>' : '' )
										?>
										</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <div class="cfgp-tab-panel" id="server-statistics">
                        	<p><?php printf( __( 'Information of your WordPress installation, server and browser', CFGP_NAME ), CFGP_VERSION ); ?></p>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics">
                                <thead>
                                    <tr>
                                        <th><?php _e('Field',CFGP_NAME); ?></th>
                                        <th><?php _e('Value',CFGP_NAME); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<tr>
                                        <td><strong><?php _e( 'Plugin ID', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_option(CFGP_NAME . '-ID'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin installed', CFGP_NAME ); ?></strong></td>
                                        <td><?php
											$plugin_installed = get_option(CFGP_NAME . '-activation');
											if($plugin_installed && is_array($plugin_installed)){
												$plugin_installed = array_shift($plugin_installed);
												echo date(get_option('date_format').' '.get_option('time_format'),strtotime($plugin_installed));
											} else {
												$plugin_installed = NULL;
												 echo '-';
											}
										?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin updated', CFGP_NAME ); ?></strong></td>
                                        <td><?php
											$plugin_activation = get_option(CFGP_NAME . '-activation'); 
											if($plugin_activation && is_array($plugin_activation)){
												$plugin_activation = end($plugin_activation);
												if($plugin_activation != $plugin_installed) {
													echo date(get_option('date_format').' '.get_option('time_format'),strtotime($plugin_activation));
												} else echo '-';
											} else echo '-';
										?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Site title', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'name' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Tagline', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'description' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress address (URL)', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'wpurl' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress host', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_U::get_host(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress multisite', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo (CFGP_MULTISITE ? __('Enabled', CFGP_NAME) : __('Disabled', CFGP_NAME)); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Admin email', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'admin_email' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Encoding for pages and feeds', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'charset' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress version', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'version' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Content-Type', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'html_type' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Language', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'language' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Server time', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo date( 'r' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress directory path', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo ABSPATH; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP version', CFGP_NAME ); ?></strong></td>
                                        <td>PHP <?php echo PHP_VERSION; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP version ID', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo PHP_VERSION_ID; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'PHP architecture', CFGP_NAME ); ?></strong></td>
                                        <td><?php printf(__('%dbit', CFGP_NAME), (CFGP_OS::is_php64() ? 64 : 32)); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Operting system', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_OS::get(); ?> <?php printf(__('%dbit', CFGP_NAME), CFGP_OS::architecture()); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'User agent', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_OS::user_agent(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress debug', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo ( WP_DEBUG ? '<strong><span class="text-danger">' . __( 'On', CFGP_NAME ) . '</span></strong>' : __( 'Off', CFGP_NAME ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Plugin directory path', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_ROOT; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Session API expire', CFGP_NAME ); ?></strong></td>
                                        <td><?php printf(__('%d minutes', CFGP_NAME), CFGP_SESSION); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Local or live server', CFGP_NAME ); ?></strong></td>
                                        <td><?php (CFGP_LOCAL ? _e('Local server', CFGP_NAME) : _e('Live server', CFGP_NAME)); ?></td>
                                    </tr>
                                </tbody>
                        	</table>
                        </div>
                        <?php if( CFGP_Options::get_beta('enable_gmap', 0) ): ?>
                        <div class="cfgp-tab-panel" id="google-map">
                        <?php
                        	echo do_shortcode( '[cfgeo_map width="100%" height="600px" longitude="'.$API['longitude'].'" latitude="'.$API['latitude'].'"]
								<address>
									<strong><big>'.CFGP_U::admin_country_flag($API['country_code']).' '.$API['ip'].'</big></strong><br /><br />
									'.$API['city'].'<br />
									'.$API['region'].(!empty($API['region_code'])?' ('.$API['region_code'].')':'').'<br />
									'.$API['country'].'<br />
									'.$API['continent'].(!empty($API['country_code'])?' ('.$API['country_code'].')':'').'<br /><br />
									'.$API['longitude'].', '.$API['latitude'].'<br /><br />
									'.$API['timezone'].'
								</address>
							[/cfgeo_map]' );
						?>
                        </div>
                        <?php endif; ?>
                    <!--    <div class="cfgp-tab-panel" id="debugger"></div>    -->
					</div>
                    
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>
