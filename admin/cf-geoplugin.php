<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page CF GeoPlugin
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 *
**/

global $CFGEO, $CF_GEOPLUGIN_OPTIONS;
?>
<div class="clearfix"></div>
<div class="container-fluid">
	<div class="row">
    	<div class="col-12">
        	<h1 class="h5 mt-3"><i class="fa fa-map-marker"></i> <?php _e('CF GeoPlugin',CFGP_NAME); ?></h1>
            <hr>
        </div>
        <div class="col">
        	<div class="card border-secondary">
                <div class="card-body text-white bg-secondary text-center">
                	<div class="row align-items-center">
                    	<div class="col-sm-2 text-left">
                        	<?php echo $CF_GEOPLUGIN_OPTIONS['enable_flag'] ? do_shortcode('[cfgeo_flag size=3em]') : '<i class="fa fa-globe fa-3x"></i>'; ?>
                        </div>
                        <div class="col-sm-10 text-right">
							<div class="h4"><?php echo $CFGEO['ip']; ?></div>
                            <div class="card-text"><small><?php echo $CFGEO['address']; ?></small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
        	<div class="card border-info">
                <div class="card-body text-white bg-info text-center">
                	<div class="row align-items-center">
                    	<div class="col-sm-2 text-left">
                        	<?php CF_Geoplugin_Global::lookup_status_icon($CFGEO['lookup'], 'fa-3x'); ?>
                        </div>
                        <div class="col-sm-10 text-right">
							<div class="h4">
                            <?php if($CFGEO['lookup'] == 'unlimited') : ?>
                            	<?php _e('UNLIMITED',CFGP_NAME); ?>
                            <?php else : ?>
								<?php echo (CFGP_LIMIT-$CFGEO['lookup']); ?> <small><?php _e('of',CFGP_NAME); ?></small> <?php echo CFGP_LIMIT; ?>
                            <?php endif; ?>
                            </div>
                            <div class="card-text">
                            <?php if(CFGP_ACTIVATED) : ?>
								<?php if(CFGP_DEFENDER_ACTIVATED) : ?>
                                    <?php _e('LIFETIME!',CFGP_NAME); ?>
                                <?php elseif($CF_GEOPLUGIN_OPTIONS['license_expire'] == 0): ?>
                                	<?php _e('LIFETIME!',CFGP_NAME); ?>
                                <?php else : ?>
                                	<small><?php printf(__('Expire %s',CFGP_NAME), date(get_option('date_format') . ' ' . get_option('time_format'), (int)$CF_GEOPLUGIN_OPTIONS['license_expire'])); ?></small>
                                <?php endif; ?>
                            <?php else : ?>
                            	<?php if($CFGEO['lookup'] == 'unlimited') : ?>
                                	<?php _e('LIFETIME!',CFGP_NAME); ?>
                                <?php else : ?>
									<small><?php _e('Lookup',CFGP_NAME); ?> | <a href="<?php echo admin_url('admin.php?page=cf-geoplugin-activate'); ?>" class="text-white text-strong"><strong><?php _e('GET UNLIMITED',CFGP_NAME); ?></strong></a></small>
                                <?php endif; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
        	<div class="card border-success">
                <div class="card-body text-white bg-success text-center">
                	<div class="row align-items-center">
                    	<div class="col-sm-2 text-left">
                            <?php CF_Geoplugin_Global::runtime_status_icon($CFGEO['runtime'], 'fa-3x'); ?>
                        </div>
                        <div class="col-sm-10 text-right">
							<div class="h4"><?php echo number_format((float)$CFGEO['runtime'], 2, '.', ''); ?>s</div>
                            <div class="card-text"><small><?php _e('Connection quality',CFGP_NAME); ?></small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
    	<div class="col-sm-9">
        	<?php do_action('page-cf-geoplugin-before-tab'); ?>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-dark active" href="#shortcodes" role="tab" data-toggle="tab"><span class="fa fa-code"></span> <?php _e('Shortcodes',CFGP_NAME); ?></a>
                </li>
                <?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#beta" role="tab" data-toggle="tab"><span class="fa fa-code"></span> <?php _e('Simple Shortcodes',CFGP_NAME); ?> <sup class="text-danger" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?php _e('This BETA options you can turn off inside your Settings under General tab',CFGP_NAME); ?>">BETA</sup></a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#info" role="tab" data-toggle="tab"><span class="fa fa-info"></span> <?php _e('Info & Examples',CFGP_NAME); ?></a>
                </li>
                <?php do_action('page-cf-geoplugin-tab'); ?>
            </ul>
            
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active show" id="shortcodes">
                	<table width="100%" class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php do_action('page-cf-geoplugin-shortcode-table'); ?>
                            <tr>
                                <td><kbd>[cfgeo]</kbd></td>
                                <td><?php echo $CFGEO['ip']; ?></td>
                            </tr>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_flag']) : ?>
                            <tr>
                                <td><kbd>[cfgeo_flag]</kbd></td>
                                <td><?php echo do_shortcode('[cfgeo_flag css="font-size:18px"]'); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><kbd>[cfgeo return="ip"]</kbd></td>
                                <td><?php echo $CFGEO['ip']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="ip_version"]</kbd></td>
                                <td><?php echo $CFGEO['ip_version']; ?></td>
                            </tr>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_dns_lookup'] && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) >= 1) : ?>
                            <tr>
                                <td><kbd>[cfgeo return="ip_dns"]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="ip_dns_host"]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="ip_dns_provider"]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_provider']; ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><kbd>[cfgeo return="address"]</kbd></td>
                                <td><?php echo $CFGEO['address']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="city"]</kbd></td>
                                <td><?php echo $CFGEO['city']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="region"]</kbd></td>
                                <td><?php echo $CFGEO['region']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="region_code"]</kbd></td>
                                <td><?php echo $CFGEO['region_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="country"]</kbd></td>
                                <td><?php echo $CFGEO['country']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="country_code"]</kbd></td>
                                <td><?php echo $CFGEO['country_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="continent"]</kbd></td>
                                <td><?php echo $CFGEO['continent']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="continent_code"]</kbd></td>
                                <td><?php echo $CFGEO['continent_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="latitude"]</kbd></td>
                                <td><?php echo $CFGEO['latitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="longitude"]</kbd></td>
                                <td><?php echo $CFGEO['longitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="timezone"]</kbd></td>
                                <td><?php echo $CFGEO['timezone']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="locale"]</kbd></td>
                                <td><?php echo $CFGEO['locale']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="currency"]</kbd></td>
                                <td><?php echo $CFGEO['currency']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="currency_symbol"]</kbd></td>
                                <td><?php echo $CFGEO['currency_symbol']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="currency_converter"]</kbd></td>
                                <td><?php echo $CFGEO['currency_converter']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="host"]</kbd></td>
                                <td><?php echo $CFGEO['host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="ip_host"]</kbd></td>
                                <td><?php echo $CFGEO['ip_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="current_date"]</kbd></td>
                                <td><?php echo $CFGEO['current_date']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="current_time"]</kbd></td>
                                <td><?php echo $CFGEO['current_time']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="accuracy_radius"]</kbd></td>
                                <td><?php echo $CFGEO['accuracy_radius']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="runtime"]</kbd></td>
                                <td><?php echo $CFGEO['runtime']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="status"]</kbd></td>
                               <td><?php echo $CFGEO['status']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="version"]</kbd></td>
                                <td><?php echo $CFGEO['version']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="lookup"]</kbd></td>
                                <td><?php echo $CFGEO['lookup']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>[cfgeo return="credit"]</kbd></td>
                                <td><?php echo $CFGEO['credit']; ?></td>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                            </tr>
                        </thead>
                    </table>
                </div>
			<?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                <div role="tabpanel" class="tab-pane fade pt-3" id="beta">
                	<h3 class="ml-3 mr-3"><?php _e('List of experimental shortcodes',CFGP_NAME); ?></h3>
                    <p class="ml-3 mr-3"><?php _e('This shortcodes only have purpose to return available geo-information. You can\'t do include, exclude or add default value. Just display geodata following with appropriate shortcodes. ',CFGP_NAME); ?></p>
                    <table width="100%" class="table table-striped table-sm">
                        <tbody>
                        	<?php if($CF_GEOPLUGIN_OPTIONS['enable_flag']) : ?>
                            <tr>
                                <td><kbd>[country_flag]</kbd></td>
                                <td><?php echo do_shortcode('[country_flag css="font-size:18px"]'); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><kbd>[cfgeo_ip]</kbd></td>
                                <td><?php echo $CFGEO['ip']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_ip_version]</kbd></td>
                                <td><?php echo $CFGEO['ip_version']; ?></td>
                            </tr>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_dns_lookup']) : ?>
                            <tr>
                                <td><kbd>[cfgeo_ip_dns]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_ip_dns_host]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_ip_dns_provider]</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_provider']; ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><kbd>[cfgeo_address]</kbd></td>
                                <td><?php echo $CFGEO['address']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_city]</kbd></td>
                                <td><?php echo $CFGEO['city']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_region]</kbd></td>
                                <td><?php echo $CFGEO['region']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_region_code]</kbd></td>
                                <td><?php echo $CFGEO['region_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_country]</kbd></td>
                                <td><?php echo $CFGEO['country']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_country_code]</kbd></td>
                                <td><?php echo $CFGEO['country_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_continent]</kbd></td>
                                <td><?php echo $CFGEO['continent']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_continent_code]</kbd></td>
                                <td><?php echo $CFGEO['continent_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_latitude]</kbd></td>
                                <td><?php echo $CFGEO['latitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_longitude]</kbd></td>

                                <td><?php echo $CFGEO['longitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_timezone]</kbd></td>
                                <td><?php echo $CFGEO['timezone']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_locale]</kbd></td>
                                <td><?php echo $CFGEO['locale']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_currency]</kbd></td>
                                <td><?php echo $CFGEO['currency']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_currency_symbol]</kbd></td>
                                <td><?php echo $CFGEO['currency_symbol']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_currency_converter]</kbd></td>
                                <td><?php echo $CFGEO['currency_converter']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_host]</kbd></td>
                                <td><?php echo $CFGEO['host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_ip_host]</kbd></td>
                                <td><?php echo $CFGEO['ip_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_current_date]</kbd></td>
                                <td><?php echo $CFGEO['current_date']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_current_time]</kbd></td>
                                <td><?php echo $CFGEO['current_time']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>[cfgeo_version]</kbd></td>
                                <td><?php echo $CFGEO['version']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_accuracy_radius]</kbd></td>
                                <td><?php echo $CFGEO['accuracy_radius']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_lookup]</kbd></td>
                                <td><?php echo $CFGEO['lookup']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_runtime]</kbd></td>
                                <td><?php echo $CFGEO['runtime']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_status]</kbd></td>
                               <td><?php echo $CFGEO['status']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_credit]</kbd></td>
                                <td><?php echo $CFGEO['credit']; ?></td>
                            </tr>
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
			<?php endif; ?>
                <div role="tabpanel" class="tab-pane fade pb-5" id="info">
                	<div class="row">
                    	<div class="col-12">
                        	<?php do_action('page-cf-geoplugin-tab-info-start'); ?>
                            <h3><?php _e('Usage, additional attributes and settings',CFGP_NAME); ?></h3>
                                <p class="manage-menus"><?php printf(__("If you like to display region (for example California for users who are from California), you just need to use return attribute in your shortcode like this: %s - what will return region name by visitors location.",CFGP_NAME),'<br><code>[cfgeo return="region"]</code>'); ?></p>
                                
                                <p class="manage-menus"><?php printf(__('If you whant to track some custom IP and return some information from that IP, you can do that by adding one optional attribute %s like on example: %s - what will return area code from that IP address.',CFGP_NAME),'<code>ip</code>','<br><code>[cfgeo ip="127.0.0.1" return="area_code"]</code>'); ?></p>
                                
                                <p class="manage-menus"><?php printf(__("If you like to ad default values to your shortcode if data is empty you need to add extra attribute in your shortcode like this example: %s - what will return US if geoplugin can't locate country code.",CFGP_NAME),'<br><code>[cfgeo return="country_code" default="US"]</code>'); ?></p>
                                <p class="manage-menus"><?php printf(__("Sometimes you need to include other HTML, CSS and JavaScript, and jQuery codes inside <strong>ContactForm7</strong>. Sometimes you need to insert a geolocation in input fields. This is not easy but here is one example with jQuery: %s This code will auto fill value of CF7 city field when a visitor visits the contact page.",CFGP_NAME),'<br><br><code>&nbsp;[text* city placeholder "* City"]<br>
        &nbsp;&nbsp;[text* country placeholder "* Country"]<br><br>
        &lt;script&gt;<br>
        jQuery(document).ready(function(){<br>
        &nbsp;&nbsp;// Get CF GeoPlugin Data<br>
        &nbsp;&nbsp;var city = \'[cfgeo return="city"]\';<br>
        &nbsp;&nbsp;var country = \'[cfgeo return="country"]\';<br><br>
        &nbsp;&nbsp;// Insert values inside input fields<br>
        &nbsp;&nbsp;jQuery("input[name^=\'city\']").val(city);<br>
        &nbsp;&nbsp;jQuery("input[name^=\'country\']").val(country);<br>
        });<br>
        &lt;/script&gt;<br><br>
        </code>'); ?></p>
        
                            <h3><?php _e('Displaying Country Flags in text or like image',CFGP_NAME); ?></h3>
                            
                            <p class="manage-menus"><?php printf(__("If you like to display country flag in your text like icon, you can do that simple like: %s - and you will see flag in your text.",CFGP_NAME),'<br><code>[cfgeo_flag]</code>'); ?></p>
                            
                            <p class="manage-menus"><?php printf(__("If you like to display country flag in your content like image, you can do that also simple using %s or %s attributes like: %s - and you will see image flag in your content",CFGP_NAME),'<code>img</code>','<code>image</code>','<br><code>[cfgeo_flag img]</code>'); ?></p>
                            
                            <p class="manage-menus"><?php printf(__("You also can give custom sizes of flags in %s, %s, %s, %s or %s using %s attribute like this: %s - and you will see your flag in that size. %s",CFGP_NAME),'<code>%</code>','<code>px</code>','<code>in</code>','<code>pt</code>','<code>em</code>', '<code>size</code>','<br><code>[cfgeo_flag size="32px"]</code>','<br><strong>-'.__("You can use this size in image and normal text mode also.",CFGP_NAME).'</strong>'); ?></p>
                            
                            <p class="manage-menus"><?php printf(__("You also can display custom flag using %s attribute by placing country code simple like: %s - and you will see flag in your text or like image.",CFGP_NAME),'<code>country</code>', '<br><code>[cfgeo_flag country="ca"]</code>'); ?></p>
                            
                            <p class="manage-menus"><?php printf(__("We allow you also full controll of this flags and you can place %s, %s or %s attributes to be able use this in any kind of work like this: %s",CFGP_NAME),'<code>css</code>', '<code>class</code>', '<code>id</code>', '<br><code>[cfgeo_flag css="padding:10px;" class="your-custom-class custom-class custom" id="top-flag"]</code>'); ?></p>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                                <h3><?php _e('Experimental & Deprecate Shortcodes',CFGP_NAME); ?></h3>
                            <?php else : ?>
                            	<h3><?php _e('Deprecate Shortcodes',CFGP_NAME); ?></h3>
                            <?php endif; ?>
                                
                                <p class="manage-menus"><?php printf(__("Like you notice, this plugin have changed shortcode names but still support old <strong>deprecated</strong> shortcode name %s what you still can use but we strongly recommended to use new one %s.",CFGP_NAME), '<code>cg_geo</code>', '<code>cfgeo</code>'); ?></p>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                                <p class="manage-menus"><?php printf(__("Also we trying to made all works faster and more understandable and we also add <strong>experimental</strong> shortcode called %s that you can use insteand of %s like %s but if you have similar geo plugin installed beside CF GeoPlugin you can have a problems and interference.",CFGP_NAME), '<code>geo</code>', '<code>cfgeo</code>', '<code>[geo]</code>'); ?></p>
                            <?php endif; ?>
                            
                            <?php do_action('page-cf-geoplugin-tab-info-end'); ?>
                        </div>
                    </div>
                </div>
                <?php do_action('page-cf-geoplugin-tab-panel'); ?>
            </div>
            <?php do_action('page-cf-geoplugin-after-tab'); ?>
        </div>
        <div class="col-sm-3">
        	<?php do_action('page-cf-geoplugin-sidebar'); ?>
        </div>
    </div>
</div>