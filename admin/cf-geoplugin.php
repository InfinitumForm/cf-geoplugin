<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page CF Geo Plugin
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
//echo '<pre>', var_dump(CF_Geoplugin_Global::set_license_and_access_levels()), '</pre>';
?>
<div class="clearfix"></div>
<div class="container-fluid">
	<div class="row">
    	<div class="col-12">
        	<h1 class="h5 mt-3"><i class="fa fa-map-marker"></i> <?php _e('CF Geo Plugin',CFGP_NAME); ?></h1>
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
									<small><?php _e('Lookup',CFGP_NAME); ?> | <a href="<?php echo self_admin_url('admin.php?page=cf-geoplugin-activate'); ?>" class="text-white text-strong"><strong><?php _e('GET UNLIMITED',CFGP_NAME); ?></strong></a></small>
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
				<li class="nav-item">
                    <a class="nav-link text-dark" href="#metatags" role="tab" data-toggle="tab"><span class="fa fa-tag"></span> <?php _e('Tags',CFGP_NAME); ?></a>
                </li>
                <?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#beta" role="tab" data-toggle="tab"><span class="fa fa-code"></span> <?php _e('Simple Shortcodes',CFGP_NAME); ?> <sup class="text-danger" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?php _e('This BETA options you can turn off inside your Settings under General tab',CFGP_NAME); ?>">BETA</sup></a>
                </li>
                <?php endif; ?>
				<li class="nav-item">
                    <a class="nav-link text-dark" href="#info" role="tab" data-toggle="tab"><span class="fa fa-book"></span> <?php _e('Documentation',CFGP_NAME); ?></a>
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
							<?php do_action('page-cf-geoplugin-shortcode-table-start'); ?>
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
							<?php do_action('page-cf-geoplugin-shortcode-table-address'); ?>
                            <tr>
                                <td><kbd>[cfgeo return="address"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['address']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="city"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['city']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="region"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['region']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="region_code"]</kbd></td>
                                <td><?php echo $CFGEO['region_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="country"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['country']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="country_code"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
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
                                <td><kbd>[cfgeo return="latitude"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['latitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="longitude"]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
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
                                <td><kbd>[cfgeo return="base_currency"]</kbd></td>
                                <td><?php echo $CFGEO['base_currency']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>[cfgeo return="base_currency_symbol"]</kbd></td>
                                <td><?php echo $CFGEO['base_currency_symbol']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo return="currency_converter"]</kbd></td>
                                <td><?php echo $CFGEO['currency_converter']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>[cfgeo return="vat_rate"]</kbd></td>
                                <td><abbr data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?php echo esc_attr(__('Standard VAT Rate in percentages (%)', CFGP_NAME)); ?>"><?php echo $CFGEO['vat_rate']; ?></abbr></td>
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
                            <tr>
                                <td><kbd>[cfgeo_converter from="<?php echo isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) ? $CF_GEOPLUGIN_OPTIONS['base_currency'] : ''; ?>"]1[/cfgeo_converter]</kbd></td>
                                <td><?php echo isset( $CFGEO['currency_converter'] ) && !empty( $CFGEO['currency_converter'] ) ? $CFGEO['currency_converter'] : __( 'Sorry currently we are not able to do conversion.', CFGP_NAME ); echo isset( $CFGEO['currency_symbol'] ) ? ' ' . $CFGEO['currency_symbol'] : ''; ?></td>
                            </tr>
							<?php do_action('page-cf-geoplugin-shortcode-table-end'); ?>
                        </tbody>
                        <thead>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                            </tr>
                        </thead>
                    </table>
                </div>
				
				<div role="tabpanel" class="tab-pane fade pt-3" id="metatags">
                	<h3 class="ml-3 mr-3"><?php _e('List of available tags',CFGP_NAME); ?></h3>
                    <p class="ml-3 mr-3 mb-1"><?php _e('These special tags are intended for quick insertion of geo information into pages and posts. These tags allow the use of geo information in the titles & content of pages, categories and other taxonomy. It can also be used in widgets, various page builders and supports several SEO plugins like Yoast, All in One Seo Pack, SEO Framework and WordPress SEO Plugin by Rank Math.',CFGP_NAME); ?></p>
					<p class="ml-3 mr-3 text-danger"><?php _e('NOTE: It does not currently support custom fields, but soon this option will be supported.',CFGP_NAME); ?></p>
                    <table width="100%" class="table table-striped table-sm">
                        <tbody>
							<?php do_action('page-cf-geoplugin-tag-table-start'); ?>
                            <tr>
                                <td><kbd>%%ip%%</kbd></td>
                                <td><?php echo $CFGEO['ip']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%ip_version%%</kbd></td>
                                <td><?php echo $CFGEO['ip_version']; ?></td>
                            </tr>
                            <?php if($CF_GEOPLUGIN_OPTIONS['enable_dns_lookup']) : ?>
                            <tr>
                                <td><kbd>%%ip_dns%%</kbd></td>
                                <td><?php echo $CFGEO['ip_dns']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%ip_dns_host%%</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%ip_dns_provider%%</kbd></td>
                                <td><?php echo $CFGEO['ip_dns_provider']; ?></td>
                            </tr>
                            <?php endif; ?>
							<?php do_action('page-cf-geoplugin-tag-table-address'); ?>
                            <tr>
                                <td><kbd>%%address%%</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['address']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%city%%</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['city']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%region%%</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['region']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%region_code%%</kbd></td>
                                <td><?php echo $CFGEO['region_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%country%%</kbd></td>
                                <td><?php echo $CFGEO['country']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%country_code%%</kbd></td>
                                <td><?php echo $CFGEO['country_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%continent%%</kbd></td>
                                <td><?php echo $CFGEO['continent']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%continent_code%%</kbd></td>
                                <td><?php echo $CFGEO['continent_code']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%latitude%%</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['latitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%longitude%%</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>

                                <td><?php echo $CFGEO['longitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%timezone%%</kbd></td>
                                <td><?php echo $CFGEO['timezone']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%locale%%</kbd></td>
                                <td><?php echo $CFGEO['locale']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%currency%%</kbd></td>
                                <td><?php echo $CFGEO['currency']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%currency_symbol%%</kbd></td>
                                <td><?php echo $CFGEO['currency_symbol']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>%%base_currency%%</kbd></td>
                                <td><?php echo $CFGEO['base_currency']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%base_currency_symbol%%</kbd></td>
                                <td><?php echo $CFGEO['base_currency_symbol']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>%%currency_converter%%</kbd></td>
                                <td><?php echo $CFGEO['currency_converter']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>%%vat_rate%%</kbd></td>
                                <td><abbr data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?php echo esc_attr(__('Standard VAT Rate in percentages (%)', CFGP_NAME)); ?>"><?php echo $CFGEO['vat_rate']; ?></abbr></td>
                            </tr>
                            <tr>
                                <td><kbd>%%host%%</kbd></td>
                                <td><?php echo $CFGEO['host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%ip_host%%</kbd></td>
                                <td><?php echo $CFGEO['ip_host']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%current_date%%</kbd></td>
                                <td><?php echo $CFGEO['current_date']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%current_time%%</kbd></td>
                                <td><?php echo $CFGEO['current_time']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>%%version%%</kbd></td>
                                <td><?php echo $CFGEO['version']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%accuracy_radius%%</kbd></td>
                                <td><?php echo $CFGEO['accuracy_radius']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%lookup%%</kbd></td>
                                <td><?php echo $CFGEO['lookup']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%runtime%%</kbd></td>
                                <td><?php echo $CFGEO['runtime']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%status%%</kbd></td>
                               <td><?php echo $CFGEO['status']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>%%credit%%</kbd></td>
                                <td><?php echo $CFGEO['credit']; ?></td>
                            </tr>
                            <?php do_action('page-cf-geoplugin-tag-table-end'); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
                                <th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
				
			<?php if($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_shortcode']) : ?>
                <div role="tabpanel" class="tab-pane fade pt-3" id="beta">
                	<h3 class="ml-3 mr-3"><?php _e('List of experimental shortcodes',CFGP_NAME); ?></h3>
                    <p class="ml-3 mr-3"><?php _e('This shortcodes only have purpose to return available geo-information. You can\'t do include, exclude or add default value. Just display geodata following with appropriate shortcodes. ',CFGP_NAME); ?></p>
                    <table width="100%" class="table table-striped table-sm">
                        <tbody>
							<?php do_action('page-cf-geoplugin-beta-shortcode-table-start'); ?>
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
							<?php do_action('page-cf-geoplugin-beta-shortcode-table-address'); ?>
                            <tr>
                                <td><kbd>[cfgeo_address]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['address']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_city]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['city']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_region]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
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
                                <td><kbd>[cfgeo_latitude]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>
                                <td><?php echo $CFGEO['latitude']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_longitude]</kbd><?php if($CFGEO['gps']): ?> <i class="badge">(GPS)</i><?php endif; ?></td>

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
                                <td><kbd>[cfgeo_base_currency]</kbd></td>
                                <td><?php echo $CFGEO['base_currency']; ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[cfgeo_base_currency_symbol]</kbd></td>
                                <td><?php echo $CFGEO['base_currency_symbol']; ?></td>
                            </tr>
							<tr>
                                <td><kbd>[is_vat]<?php _e('You are under VAT', CFGP_NAME); ?>[/is_vat]</kbd></td>
                                <td><?php echo do_shortcode('[is_vat]' .__('You are under VAT', CFGP_NAME). '[/is_vat]'); ?></td>
                            </tr>
							<tr>
                                <td><kbd>[vat_rate]</kbd></td>
                                <td><abbr data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?php echo esc_attr(__('Standard VAT Rate in percentages (%)', CFGP_NAME)); ?>"><?php echo $CFGEO['vat_rate']; ?></abbr></td>
                            </tr>
                            <tr>
                                <td><kbd>[is_not_vat]<?php _e('You are NOT under VAT', CFGP_NAME); ?>[/is_not_vat]</kbd></td>
                                <td><?php echo do_shortcode('[is_not_vat]' .__('You are NOT under VAT', CFGP_NAME). '[/is_not_vat]'); ?></td>
                            </tr>
							<tr>
                                <td><kbd>[in_eu]<?php _e('You are from the EU', CFGP_NAME); ?>[/in_eu]</kbd></td>
                                <td><?php echo do_shortcode('[in_eu]' .__('You are from the EU', CFGP_NAME). '[/in_eu]'); ?></td>
                            </tr>
                            <tr>
                                <td><kbd>[not_in_eu]<?php _e('You are NOT from the EU', CFGP_NAME); ?>[/not_in_eu]</kbd></td>
                                <td><?php echo do_shortcode('[not_in_eu]' .__('You are NOT from the EU', CFGP_NAME). '[/not_in_eu]'); ?></td>
                            </tr>
							<tr>
                                <td><kbd>[cfgeo_gps]<?php _e('GPS is enabled', CFGP_NAME); ?>[/cfgeo_gps]</kbd></td>
                                <td>
									<?php echo do_shortcode('[cfgeo_gps default="' .__('GPS in not enabled', CFGP_NAME). '"]' .__('GPS is enabled', CFGP_NAME). '[/cfgeo_gps]'); ?> 
									<span class="badge"><?php
										if(CF_Geoplugin_Global::is_plugin_active('cf-geoplugin-gps/cf-geoplugin-gps.php'))
										{
											if(!$CFGEO['gps'])
											{
											//	_e('', CFGP_NAME);
											}
										}
										else
										{
											printf( 
												sprintf(
													__('GPS is enabled only with %s extension', CFGP_NAME),
													sprintf(
														'<a href="%1$s" class="thickbox open-plugin-details-modal">CF Geo Plugin GPS</a>',
														admin_url('plugin-install.php?tab=plugin-information&plugin=cf-geoplugin-gps&TB_iframe=true&width=772&height=923')
													)
												)
											);
										}
									?></span>
								</td>
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
                            <?php do_action('page-cf-geoplugin-beta-shortcode-table-end'); ?>
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
                            <p><?php _e('The CF Geo Plugin comes with many options and it is best to study the following items in our documentation to get the best from plugin.',CFGP_NAME); ?></p>
                            <ul class="row">
							   <li class="col-lg-6 mt-3">
								  <a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/" class="docspress-archive-list-item-title">
									 <h4 class="mb-3">Quick Start</h4>
								  </a>
								  <ul>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/benefits-of-cf-geo-plugin/">Benefits of CF Geo Plugin</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/all-cf-geoplugin-features/">All CF Geo Plugin Features</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/how-to-install-cf-geoplugin/">How to install CF Geo Plugin</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/how-to-use-cf-geoplugin/">How to use CF Geo Plugin</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/what-information-cf-geoplugin-returns/">What Information CF Geo Plugin returns?</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/cf-geoplugin-shortcodes/">CF Geo Plugin Shortcodes</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/cf-geo-plugin-tags/">CF Geo Plugin Tags</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/wordpress-geo-plugin-compatibility/">CF Geo Plugin Compatibility</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/quick-start/what-do-i-get-from-unlimited-license/">What do I get from Unlimited License</a></li>
								  </ul>
							   </li>
							   <li class="col-lg-6 mt-3">
								  <a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/" class="docspress-archive-list-item-title">
									 <h4 class="mb-3">User Guide</h4>
								  </a>
								  <ul>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/cf-geo-plugin-user-guide/">CF Geo Plugin User Guide</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/cf-geoplugin-settings/">CF Geo Plugin Settings</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/google-map-settings/">Google Map Settings</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/plugin-usage/">Plugin Usage</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/user-guide/widgets/">Widgets</a></li>
								  </ul>
							   </li>
							   <li class="col-lg-6 mt-3">
								  <a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/" class="docspress-archive-list-item-title">
									 <h4 class="mb-3">Plugins Integration</h4>
								  </a>
								  <ul>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/woocommerce/">WooCommerce</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/wooplatnica/">Wooplatnica</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/yoast-seo/">Yoast SEO</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/all-in-one-seo-pack/">All in One SEO Pack</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/wordpress-seo-plugin-by-rank-math/">WordPress SEO Plugin by Rank Math</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/the-seo-framework/">The SEO Framework</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/wp-fastest-cache/">WP Fastest Cache</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/w3-total-cache-w3tc/">W3 Total Cache (W3TC)</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/plugins-integration/contact-form-7/">Contact Form 7</a></li>
								  </ul>
							   </li>
							   <li class="col-lg-6 mt-3">
								  <a target="_blank" href="https://cfgeoplugin.com/documentation/advanced-usage/" class="docspress-archive-list-item-title">
									 <h4 class="mb-3">Advanced Usage</h4>
								  </a>
								  <ul>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/advanced-usage/php-integration/">PHP Integration</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/advanced-usage/javascript-integration/">JavaScript Integration</a></li>
									 <li><a target="_blank" href="https://cfgeoplugin.com/documentation/advanced-usage/rest-api/">REST API</a></li>
								  </ul>
							   </li>
							</ul>
                            
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