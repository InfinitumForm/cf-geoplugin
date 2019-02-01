<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page Debug
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

include_once CFGP_INCLUDES . '/class-cf-geoplugin-os.php';
// Buypass debugger. Just keep original data somewhere
$CFGEO_BUYPASS = $CFGEO;

if( $this->get( 'ip-lookup', 'bool', false )  )
{
    if( class_exists( 'CF_Geoplugin_API' ) && $ip_address = $this->get( 'ip_address' ) )
    {
        $CFGEO_API = new CF_Geoplugin_API;
        $GLOBALS['CFGEO'] = $CFGEO = $CFGEO_API->run(array(
            'ip'    => $ip_address,
            'debug' => true
        ));
    }
}

$data = 'active show';
$debug = '';
if( isset( $_GET['action'] ) && ( $_GET['action'] == 'debugger' || $_GET['action'] == 'download_debug_log' ) )
{
    $data = '';
    $debug = 'active show';
}

?>
<div class="clearfix"></div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h5 mt-3"><i class="fa fa-bug"></i> <?php _e( 'Debug Mode', CFGP_NAME ); ?></h1>
            <hr>
        </div>
    </div>
    <div class="row mt-3">
    	<div class="col-9">  
			
            <div class="row">
            	<div class="col-sm-8">
                    <div class="card border-secondary bg-secondary text-white">
                        <div class="card-body">
                        	<?php echo sprintf(__( 'Enter custom IP address for the lookup.', CFGP_NAME ), CFGP_VERSION ); ?>
                            <form method="get" target="_self" id="template-options-tab" autocomplete="off">
                            	<input type="hidden" name="page" value="cf-geoplugin-debug" />
                                <input type="hidden" name="ip-lookup" value="true" />
                                <div class="input-group">
                                    <input type="text" value="<?php echo $this->get( 'ip_address' ); ?>" placeholder="<?php echo CFGP_IP; ?>" class="form-control" id="ip_address" name="ip_address" autocomplete="off" />
                                    <div class="input-group-append">
	                                    <button type="submit" class="btn btn-warning text-black"><i class="fa fa-eye"></i> <?php _e( 'Lookup', CFGP_NAME ); ?></button>
                                        <?php if( $this->get( 'ip-lookup', 'bool', false )  ) : ?><a href="<?php echo self_admin_url('admin.php?page=cf-geoplugin-debug'); ?>" class="btn btn-light text-black"><i class="fa fa-ban"></i> <?php _e( 'Clean', CFGP_NAME ); ?></a><?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                
                <div class="col-sm-4">
                    <div class="card border-success">
                        <div class="card-body text-white bg-success text-center">
                            <div class="row align-items-center">
                                <div class="col-2 text-left">
                                    <?php CF_Geoplugin_Global::runtime_status_icon($CFGEO['runtime'], 'fa-3x'); ?>
                                </div>
                                <div class="col-10 text-right">
                                    <div class="h3"><?php echo number_format((float)$CFGEO['runtime'], 2, '.', ''); ?>s</div>
                                    <div class="card-text"><small><?php _e('Connection quality',CFGP_NAME); ?></small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <?php do_action('page-cf-geoplugin-debug-before-tab'); ?>
            <ul class="nav nav-tabs mt-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $data; ?>" href="#recived-data" role="tab" data-toggle="tab"><i class="fa fa-database"> <?php _e( 'Recived data', CFGP_NAME ); ?></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#sent-data" role="tab" data-toggle="tab"><i class="fa fa-share-square"> <?php _e( 'Sent data', CFGP_NAME ); ?></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#server-statistics" role="tab" data-toggle="tab"><i class="fa fa-server"> <?php _e( 'Server statistics', CFGP_NAME ); ?></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#google-map" role="tab" data-toggle="tab"><i class="fa fa-globe"> <?php _e( 'Google map', CFGP_NAME ); ?></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $debug; ?>" href="#debugger" role="tab" data-toggle="tab"><i class="fa fa-bug"> <?php _e( 'Debugger', CFGP_NAME ); ?></i></a>
                </li>
                <?php do_action('page-cf-geoplugin-debug-tab'); ?>
            </ul> 

            <!-- Tab panes -->
            <div class="tab-content">
            	<div role="tabpane" class="tab-pane fade in <?php echo $data; ?>" id="recived-data">
                    <div class="card text-body">
                        <div class="card-header bg-transparent">
                            <h1 class="h5"><?php echo sprintf( __( 'Information that the CF GeoPlugin API ver.%s receives', CFGP_NAME ), CFGP_VERSION ); ?></h1>
                        </div>  
                        <div class="card-body">
                        	<p><?php _e( 'This informations are from CF GEoPlugin API services.', CFGP_NAME ); ?></p>
                            <table class="table table-sm table-striped w-100"> 
                                <thead>
                                    <tr>
                                        <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Return Code',CFGP_NAME); ?></strong></th>
                                        <th class="manage-column column-returns column-primary"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    do_action( 'page-cf-geoplugin-debug-server-statistics-table' );
                                    if( !empty( $CFGEO['error'] ) )
                                    {
                                        $error = $CFGEO['error'];
                                        $message = $CFGEO['error_message'];
                                        $status = $CFGEO['status'];
                                        $version = $CFGEO['version'];
                                        echo "
                                            <tr>
                                                <td><strong>error</strong></td>
                                                <td>{$error}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>message</strong></td>
                                                <td>{$message}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>status</strong></td>
                                                <td>{$status}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>version</strong></td>
                                                <td>{$version}</td>
                                            </tr>
                                        ";
                                    }
                                    else
                                    {
										$exclude = array_map('trim', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter'));
										if($CF_GEOPLUGIN_OPTIONS['enable_flag'])
										{
											echo '
												<tr>
													<td></td>
													<td>' . do_shortcode('[cfgeo_flag size="18px"]') . '</td>
												</tr>
											';
										}
                                        foreach( $CFGEO as $key => $value )
                                        {
											if(in_array($key, $exclude, true) === false)
											{
												echo "
													<tr>
														<td><strong>{$key}</strong></td>
														<td>{$value}</td>
													</tr>
												";
											}
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade in" id="server-statistics">
                    <div class="card text-body">
                        <div class="card-header bg-transparent">
                            <h1 class="h5"><?php _e( 'Information of your WordPress installation, server and browser', CFGP_NAME ); ?></h1>
                        </div>
                        <div class="card-body">
                        	<p><?php _e( 'This information is only visible to you', CFGP_NAME ); ?></p>
                            <table class="table table-sm table-striped w-100">
                                <thead>
                                    <tr>
                                        <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Name',CFGP_NAME); ?></strong></th>
                                        <th class="manage-column column-returns column-primary"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php do_action('page-cf-geoplugin-debug-recived-data-table'); ?>
                                    <tr>
                                        <td><strong><?php _e( 'Site Title', CFGP_NAME ); ?></strong></td>
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
                                        <td><strong><?php _e( 'WordPress Host', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo self::get_host(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Admin Email', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'admin_email' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Encoding for pages and feeds', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo get_bloginfo( 'charset' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress Version', CFGP_NAME ); ?></strong></td>
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
                                        <td><strong><?php _e( 'Server Time', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo date( 'r' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress Folder Path', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo ABSPATH; ?></td>
                                    </tr>
                                    <?php if (defined('PHP_VERSION')): ?>
                                    <tr>
                                        <td><strong><?php _e( 'PHP Version', CFGP_NAME ); ?></strong></td>
                                        <td>PHP <?php echo PHP_VERSION; ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong><?php _e( 'Operting System', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CF_Geoplugin_OS::getOS(); ?> <?php echo CF_Geoplugin_OS::architecture(); ?>bit</td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'User Agent', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CF_Geoplugin_OS::user_agent(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'WordPress Debug', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo ( WP_DEBUG ? '<strong><span class="text-danger">' . __( 'On', CFGP_NAME ) . '</span></strong>' : __( 'Off', CFGP_NAME ) ); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade in" id="sent-data">
                    <div class="card text-body">
                        <div class="card-header bg-transparent">
                            <h1 class="h5"><?php echo sprintf( __( 'Information that the plugin CF GeoPlugin API ver.%s sends', CFGP_NAME ), CFGP_VERSION ); ?></h1>
                        </div>
                        <div class="card-body">
                        	<p><?php _e('This information are sent to CF GeoPlugin API. All of this informations (hostname, IP and timezone) are available for general public, world wide and we only use them for API purpose which helps plugin to determine the exact location of the visitors and prevent accidental collapse between the IP address. Your IP and email address is also a guarantee that you\'re not a robot or some spamming software.',CFGP_NAME); ?><br><?php _e('If you are concerned about your private informations, please read the <a href="http://cfgeoplugin.com/privacy-policy" target="_blank">Privacy Policy</a>',CFGP_NAME); ?></p>
                            <table class="table table-sm table-striped w-100"> 
                                <thead>
                                    <tr>
                                        <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Name',CFGP_NAME); ?></strong></th>
                                        <th class="manage-column column-returns column-primary" width="30%"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
                                        <th class="manage-column column-returns column-primary"><strong><?php _e('Info',CFGP_NAME); ?></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php do_action( 'page-cf-geoplugin-debug-sent-data-table' ); ?>
                                    <tr>
                                        <td><strong><?php _e( 'IP', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo ( $this->post('ip_address') ? $_POST['ip_address'] : CFGP_IP ); ?></td>
                                        <td><?php _e( 'Your or Visitor\'s IP Address', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Timestamp', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_TIME; ?></td>
                                        <td><?php _e( 'Server Current Unix Timestamp', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'SIP', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_SERVER_IP . (CFGP_PROXY ?' <strong><a class="text-danger" href="'.self_admin_url('admin.php?page=cf-geoplugin-settings').'">('.__('Proxy Enabled',CFGP_NAME).')</a></strong> ' : ''); ?></td>
                                        <td><?php _e( 'Server IP Address' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Host', CFGP_NAME ); ?></strong></td>
                                        <td>
                                        <?php echo CF_Geoplugin_Global::get_host(true); ?>
                                        </td>
                                        <td><?php _e( 'Server Host Name', CFGP_NAME ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e( 'Version', CFGP_NAME ); ?></strong></td>
                                        <td><?php echo CFGP_VERSION; ?></td>
                                        <td><?php _e( 'CF GeoPlugin Version', CFGP_NAME ); ?></td>
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
												echo ( !empty( $CF_GEOPLUGIN_OPTIONS['license_key'] ) ? $CF_GEOPLUGIN_OPTIONS['license_key'] : '-' )
										?></td>
                                        <td>
											<?php _e( 'CF GeoPlugin License Key', CFGP_NAME ); ?>
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
                    </div>
                </div>
                <div role="tabpane" class="tab-pane fade in" id="google-map">
                    <div class="card text-body">
                            <div class="card-header bg-transparent">
                                <h1 class="h5"><?php _e( 'Google Map', CFGP_NAME ); ?></h1>  
                            </div>
                            <div class="card-body">
                            <?php
                                if( $CF_GEOPLUGIN_OPTIONS['enable_gmap'] )
								{
									if( !empty( $CFGEO['error'] ) )
                                    {
                                        echo sprintf( __( "Google Map can't be displayed because of error: %s", CFGP_NAME ), $CFGEO['error_message'] );
                                    }
                                    else
                                    {
										if( empty($CF_GEOPLUGIN_OPTIONS['map_api_key']) ){
											echo '<p>';
											_e( 'Google Map API key is not set! Please go to Settings > Google Map to set it.', CFGP_NAME );
											echo '</p>';
										}
                                        echo do_shortcode( '[cfgeo_map width="100%" height="600px" longitude="'.$CFGEO['longitude'].'" latitude="'.$CFGEO['latitude'].'"]
											<address>
												<strong><big>'.$CFGEO['ip'].'</big></strong><br /><br />
												'.$CFGEO['city'].'<br />
												'.$CFGEO['region'].(!empty($CFGEO['region_code'])?' ('.$CFGEO['region_code'].')':'').'<br />
												'.$CFGEO['country'].'<br />
												'.$CFGEO['continent'].(!empty($CFGEO['country_code'])?' ('.$CFGEO['country_code'].')':'').'<br /><br />
												'.$CFGEO['longitude'].', '.$CFGEO['latitude'].'<br /><br />
												'.$CFGEO['timezone'].'
											</address>
										[/cfgeo_map]' );
                                    }
								}
								else
                                {
                                    _e( 'Google Map is not enabled! Please go to Settings to enable it.', CFGP_NAME );
                                }
                            ?>
                            </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade in <?php echo $debug; ?>" id="debugger">
                    <div class="card text-body">
                        <div class="card-header bg-transparent">
                            <h1 class="h5"><?php _e( 'Plugin Debugger', CFGP_NAME ); ?></h1>
                        </div>
                        <div class="card-footer bg-transparent">
                            <?php _e( 'This feature allows you to collect and download all possible plugin data. This is very helpful in some situations. Note: On every debug last log file is deleted and new one is created.' ) ?> 
                        </div>
                        <div class="card-body">
                            <a class="btn btn-warning" href="<?php echo self_admin_url( 'admin.php?page=' . $_GET['page'] . '&action=debugger' ); ?>"><?php _e( 'Debug Plugin', CFGP_NAME ); ?></a>&nbsp;
                        <?php
                            if( file_exists( CFGP_ROOT . '/cf-geoplugin-debug.log' ) )
                            {
                                ?>
                                <a class="btn btn-primary" href="<?php echo self_admin_url( 'admin.php?page=' . $_GET['page'] . '&action=download_debug_log' ); ?>"><?php _e( 'Download Last Debug File', CFGP_NAME ); ?></a><br />
                                <?php
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <?php do_action('page-cf-geoplugin-debug-tab-panel'); ?>
            </div>
            <?php do_action('page-cf-geoplugin-debug-after-tab'); ?>
        </div>
        <div class="col-sm-3">
            <?php do_action('page-cf-geoplugin-debug-sidebar'); ?>
        </div>
    </div>
</div>
<?php
// For any case
$GLOBALS['CFGEO'] = $CFGEO = $CFGEO_BUYPASS;