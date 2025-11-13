<?php

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (CFGP_U::dev_mode()) {
    $remove_tags = [];
} else {
    $remove_tags = [
        'is_eu',
        'is_vat',
        'is_mobile',
        'is_proxy',
        'is_spam',
        'license_hash',
    ];
}

$remove_tags = apply_filters('cfgp/debug/remove_tags', $remove_tags);

$API = CFGP_Cache::get('API');

if ($NEW_API = CFGP_API::lookup(CFGP_U::request_string('cfgp_lookup'))) {
    $API = $NEW_API;
}

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? null)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-globe"></i> <?php esc_html_e('Debug', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

        	<div id="post-body">
            	<div id="post-body-content">
					
                    <div class="tablenav top">
                    	<form method="get" autocomplete="off">
                        	<input type="text" value="<?php echo esc_attr(CFGP_U::request_string('cfgp_lookup')); ?>" name="cfgp_lookup" placeholder="<?php esc_attr_e('IP Lookup', 'cf-geoplugin'); ?>: <?php echo esc_attr($API['ip'] ?? null); ?>" autocomplete="off"> 
                            <button type="submit" class="button button-primary"><?php esc_html_e('Lookup', 'cf-geoplugin'); ?></button>
                            <a href="<?php echo esc_url(CFGP_U::admin_url('admin.php?page=cf-geoplugin-debug')); ?>" target="_self" class="button" title=""><i class="cfa cfa-refresh"></i></a>
                            <input type="hidden" name="page" value="cf-geoplugin-debug">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(CFGP_NAME . '-lookup')); ?>">
                        </form>
                    </div>
                    
                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
						<?php do_action('cfgp/debug/nav-tab/before'); ?>
                        	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#recived-data"><i class="cfa cfa-database"></i><span class="label"> <?php esc_html_e('Recived data', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#sent-data"><i class="cfa cfa-share-square"></i><span class="label"> <?php esc_html_e('Sent data', 'cf-geoplugin'); ?></span></a>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#server-statistics"><i class="cfa cfa-server"></i><span class="label"> <?php esc_html_e('Server statistics', 'cf-geoplugin'); ?></span></a>
                            <?php if (CFGP_Options::get('enable_gmap', 0)): ?>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#google-map"><i class="cfa cfa-globe"></i><span class="label"> <?php esc_html_e('Google map', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                       <!--     <a href="javascript:void(0);" class="nav-tab" data-id="#debugger"><i class="cfa cfa-bug"></i><span class="label"> <?php esc_html_e('Debugger', 'cf-geoplugin'); ?></span></a>  -->
					   <?php do_action('cfgp/debug/nav-tab/after'); ?>
                        </nav>
                        
						<?php do_action('cfgp/debug/tab-panel/before'); ?>
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="recived-data">
                        <p><?php echo wp_kses_post(sprintf(__('Information that the Geo Controller API ver.%s receives', 'cf-geoplugin'), esc_html(CFGP_VERSION))); ?></p>
                        <?php if ($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-recived-data">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Return Field', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (apply_filters('cfgp/table/debug', array_merge(
                                        ['cfgeo_flag' => CFGP_U::admin_country_flag($API['country_code'] ?? null)],
                                        $API
                                    ), $API) as $key => $value) : if (in_array($key, $remove_tags, true)) {
                                        continue;
                                    } ?>
                                    <tr>
                                    <?php if (in_array($key, ['cfgeo_flag'], true)) : ?>
                                    	<td>&nbsp;</td>
                                    <?php else : ?>
                                    	<td><b><?php echo esc_attr($key); ?></b></td>
                                    <?php endif; ?>
                                        <td><?php
                                            if (in_array($key, ['cfgeo_flag', 'credit', 'error_message'], true)) {
                                                echo wp_kses_post($value || is_numeric($value) ? $value : '-');
                                            } else {
                                                echo esc_html($value || is_numeric($value) ? $value : '-');
                                            }
                                        ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php esc_html_e('Return Field', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif; ?>
                        </div>
                        
                        <div class="cfgp-tab-panel" id="sent-data">
                        	<p><?php esc_html_e('This information are sent to Geo Controller API. All of this informations (hostname, IP and timezone) are available for general public, world wide and we only use them for API purpose which helps plugin to determine the exact location of the visitors and prevent accidental collapse between the IP address. Your IP and email address is also a guarantee that you\'re not a robot or some spamming software.', 'cf-geoplugin'); ?></p>
                            <p><?php echo wp_kses_post(sprintf(__('If you are concerned about your private informations, please read the %s', 'cf-geoplugin'), '<a href="http://wpgeocontroller.com/privacy-policy" target="_blank">'.esc_html__('Privacy Policy', 'cf-geoplugin').'</a>')); ?></p>
                            
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics"> 
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Name', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Value', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Info', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?php esc_html_e('IP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_U::request_string('cfgp_lookup', CFGP_IP::get())); ?></td>
                                        <td><?php esc_html_e('Your or Visitor\'s IP Address', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Timestamp', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_TIME); ?></td>
                                        <td><?php esc_html_e('Server Current Unix Timestamp', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('SIP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_IP::server()) . (CFGP_U::proxy() ? ' <strong><a class="text-danger" href="'.esc_url(admin_url('admin.php?page=cf-geoplugin-settings')).'">('.esc_html__('Proxy Enabled', 'cf-geoplugin').')</a></strong> ' : ''); ?></td>
                                        <td><?php esc_html_e('Server IP Address', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Host', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_U::get_host(true)); ?></td>
                                        <td><?php esc_html_e('Server Host Name', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('Email'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('admin_email')); ?></td>
                                        <td><?php esc_html_e('Admin e-mail address.', 'cf-geoplugin'); ?> <?php esc_html_e('Only reason why we collect your email address is because plugin support and robot prevention. Your email address will NOT be spammed or shared with 3rd party in any case and you can any time request from us on email <a href="mailto:support@wpgeocontroller.com">support@wpgeocontroller.com</a> to remove your all personal data from our system by GDPR rules.', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Plugin Version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_VERSION); ?></td>
                                        <td><?php esc_html_e('Geo Controller Version', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('WordPress Version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                                        <td><?php esc_html_e('We use the WordPress version for statistics and debugging.', 'cf-geoplugin'); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('Spam Check', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(((
                                            CFGP_Options::get('enable_spam_ip', 0)
                                            && CFGP_Options::get('enable_defender', 0)
                                            && CFGP_License::level(CFGP_Options::get('license_sku')) > 0
                                        ) ? 'true' : 'false')); ?></td>
                                        <td><?php esc_html_e('Sends a parameter that triggers a spam check on the site.', 'cf-geoplugin'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('License', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
                                            if (CFGP_DEFENDER_ACTIVATED) {
                                                echo esc_html(get_option('cf_geo_defender_api_key'));
                                            } else {
                                                echo esc_html(CFGP_Options::get('license_key'));
                                            }
?></td>
                                        <td>
											<?php esc_html_e('Geo Controller License Key', 'cf-geoplugin'); ?>
											<?php
    if (CFGP_DEFENDER_ACTIVATED) {
        esc_html_e('Lifetime', 'cf-geoplugin');
    } else {
        echo (!empty(CFGP_License::expire_date()) ? '<br><small>('.esc_html__('License Expire', 'cf-geoplugin') . ': <b>' . esc_html(date('r', strtotime(CFGP_License::expire_date()))).'</b>)</small>' : '');
    }
?>
										</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <div class="cfgp-tab-panel" id="server-statistics">
                        	<p><?php esc_html_e('Information of your WordPress installation, server and browser', 'cf-geoplugin'); ?></p>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-debug-server-statistics">
                                <thead>
                                    <tr>
                                        <th width="30%"><?php esc_html_e('Field', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Value', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<tr>
                                        <td><strong><?php esc_html_e('Plugin ID', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_U::ID()); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Plugin installed', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
    $plugin_installed = get_option(CFGP_NAME . '-activation');

if ($plugin_installed && is_array($plugin_installed)) {
    $plugin_installed = array_shift($plugin_installed);
    echo esc_html(date(CFGP_DATE_TIME_FORMAT, strtotime($plugin_installed)));
} else {
    $plugin_installed = null;
    echo '-';
}
?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Plugin updated', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php
    $plugin_activation = get_option(CFGP_NAME . '-activation');

if ($plugin_activation && is_array($plugin_activation)) {
    $plugin_activation = end($plugin_activation);

    if ($plugin_activation != $plugin_installed) {
        echo esc_html(date(CFGP_DATE_TIME_FORMAT, strtotime($plugin_activation)));
    } else {
        echo '-';
    }
} else {
    echo '-';
}
?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Deprecated support', 'cf-geoplugin'); ?></strong></td>
                                        <td>
										<?php if (defined('CFGP_ALLOW_DEPRECATED_METHODS') && CFGP_ALLOW_DEPRECATED_METHODS) : ?>
											<strong class="text-success"><?php esc_html_e('Deprecated code support is activated.', 'cf-geoplugin'); ?></strong>
											<br><?php esc_html_e('For now, we\'ve approved the use of deprecated code by default to make transition easier. We recommend that you switch your project to new code as we will be removing support for deprecated code in the future.', 'cf-geoplugin');

											printf(
												' <a href="%1$s" target="_blank">%2$s</a>',
												CFGP_STORE . '/documentation/advanced-usage/deprecated-code-notice',
												esc_html__('Read more...', 'cf-geoplugin')
											); ?>
										<?php else : ?>
											<strong class="text-default"><?php esc_html_e('Deprecated code support is not active.', 'cf-geoplugin'); ?></strong>
										<?php endif; ?>
										</td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('Server type', 'cf-geoplugin'); ?></strong></td>
                                        <td>
										<?php if (CFGP_IP::is_localhost()) : ?>
											<strong class="text-danger"><?php esc_html_e('Local Server', 'cf-geoplugin'); ?></strong>
										<?php else : ?>
											<strong class="text-success"><?php esc_html_e('Production Server', 'cf-geoplugin'); ?></strong>
										<?php endif; ?>
										</td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('Site title', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('name')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Tagline', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('description')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress address (URL)', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('wpurl')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress host', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_U::get_host()); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('Server IP', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_IP::server()) . (CFGP_U::proxy() ? ' <strong><a class="text-danger" href="'.esc_url(admin_url('admin.php?page=cf-geoplugin-settings')).'">('.esc_html__('Proxy Enabled', 'cf-geoplugin').')</a></strong> ' : ''); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress multisite', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo(CFGP_MULTISITE ? esc_html__('Enabled', 'cf-geoplugin') : esc_html__('Disabled', 'cf-geoplugin')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Admin email', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('admin_email')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Encoding for pages and feeds', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('charset')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress version', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Content-Type', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('html_type')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Language', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(get_bloginfo('language')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Server time', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(date('r')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress directory path', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(ABSPATH); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('PHP: Version', 'cf-geoplugin'); ?></strong></td>
                                        <td>PHP <?php echo esc_html(PHP_VERSION); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('PHP: Version ID', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(PHP_VERSION_ID); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('PHP: Architecture', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php printf(esc_html__('%dbit', 'cf-geoplugin'), (CFGP_OS::is_php64() ? 64 : 32)); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('PHP: Memory usage of the plugin', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_U::filesize(CFGP_Cache::get_size(), 2)); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('PHP: Operting system', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_OS::get()); ?> <?php printf(esc_html__('%dbit', 'cf-geoplugin'), esc_html(CFGP_OS::architecture())); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('PHP: Server Cache', 'cf-geoplugin'); ?></strong></td>
                                        <td><strong><?php echo(
                                            extension_loaded('redis')
                                            ? '<span class="text-success">'
                                                . esc_html__('Redis Cache', 'cf-geoplugin')
                                            . '</span>'
                                            : (
                                                class_exists('Memcached', false)
                                                ? '<span class="text-success">'
                                                    . esc_html__('Memcached', 'cf-geoplugin')
                                                . '</span>'
                                                : '<span class="text-danger">'
                                                    . esc_html__('No', 'cf-geoplugin')
                                                . '</span><br>' . sprintf(
                                                    esc_html__('And if our plugin has an internal cache system, we strongly recommend using %1$s or %2$s to get the best performance.', 'cf-geoplugin'),
                                                    '<a href="https://www.php.net/manual/en/book.memcached.php" target="_blank">Memcache</a>',
                                                    '<a href="https://redis.io/" target="_blank">Redis Cache</a>'
                                                )
                                            )
                                        ); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Browser', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo wp_kses_post(sprintf(_x('%1$s (%2$s)', 'Debug: User agent (Browser)', 'cf-geoplugin'), esc_html(CFGP_Browser::instance()->getBrowser()), esc_html(CFGP_Browser::instance()->getVersion()))); ?></td>
                                    </tr>
									<tr>
                                        <td><strong><?php esc_html_e('User platform', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(trim(sprintf(
                                            '%1$s %2$s',
                                            esc_html(CFGP_Browser::instance()->getPlatform()),
                                            (CFGP_Browser::instance()->isMobile() ? esc_html__('(mobile device)', 'cf-geoplugin') : '')
                                        ))); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('WordPress debug', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo(WP_DEBUG ? '<strong><span class="text-danger">' . esc_html__('On', 'cf-geoplugin') . '</span></strong>' : esc_html__('Off', 'cf-geoplugin')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Plugin directory path', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php echo esc_html(CFGP_ROOT); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Session API expire', 'cf-geoplugin'); ?></strong></td>
                                        <td><?php printf(esc_html__('%d minutes', 'cf-geoplugin'), esc_html(CFGP_SESSION)); ?></td>
                                    </tr>
                                </tbody>
                        	</table>
                        </div>
                        <?php if (CFGP_Options::get('enable_gmap', 0)): ?>
                        <div class="cfgp-tab-panel" id="google-map">
                        <?php
                            echo do_shortcode('[cfgeo_map width="100%" height="600px" longitude="'.esc_attr($API['longitude'] ?? null).'" latitude="'.esc_attr($API['latitude'] ?? null).'"]
								<address>
									<strong><big>'.CFGP_U::admin_country_flag($API['country_code'] ?? null).' '.esc_html($API['ip'] ?? null).'</big></strong><br /><br />
									'.esc_html($API['city'] ?? null).'<br />
									'.esc_html($API['region'] ?? null).(!empty($API['region_code'] ?? null) ? ' ('.$API['region_code'] ?? null.')' : '').'<br />
									'.esc_html($API['country'] ?? null).'<br />
									'.esc_html($API['continent'] ?? null).(!empty($API['country_code'] ?? null) ? ' ('.esc_html($API['country_code'] ?? null).')' : '').'<br /><br />
									'.esc_html(($API['longitude'] ?? null).', '.($API['latitude'] ?? null)).'<br /><br />
									'.esc_html($API['timezone'] ?? null).'
								</address>
							[/cfgeo_map]');
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
