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
        'error',
        'error_message',
        'is_eu',
        'is_vat',
        'is_mobile',
        'is_proxy',
        'is_spam',
        'is_tor',
        'limited',
        'gps',
        'license_hash',
        'is_local_server',
    ];
}

$remove_tags = apply_filters('cfgp/main_page/remove_tags', $remove_tags);

$gps_keys = apply_filters('cfgp/main_page/gps_keys', [
    'address',
    'latitude',
    'longitude',
    'region',
    'state',
    'street',
    'street_number',
    'country_code',
    'country',
    'city',
    'city_code',
    'district',
]);

$API = CFGP_U::api(false, CFGP_Defaults::API_RETURN);

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? null)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-map-marker"></i> <?php esc_html_e('Geo Controller', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">

    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
			
        	<div id="post-body">
            	<div id="post-body-content">

                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
                        	<?php do_action('cfgp/main_page/nav-tab/before', $API, $remove_tags, $gps_keys); ?>
                            <a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#shortcodes"><i class="cfa cfa-code"></i><span class="label"> <?php esc_html_e('Shortcodes', 'cf-geoplugin'); ?></span></a>
                            <?php if (CFGP_Options::get_beta('enable_simple_shortcode')) : ?>
                            	<a href="javascript:void(0);" class="nav-tab" data-id="#simple-shortcodes"><i class="cfa cfa-code"></i><span class="label"> <?php esc_html_e('Simple Shortcodes', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#tags"><i class="cfa cfa-tag"></i><span class="label"> <?php esc_html_e('Tags', 'cf-geoplugin'); ?></span></a>
							<?php if (CFGP_Options::get('enable_css')) : ?>
                            	<a href="javascript:void(0);" class="nav-tab" data-id="#css-property"><i class="cfa cfa-css3"></i><span class="label"> <?php esc_html_e('CSS property', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                            <?php do_action('cfgp/main_page/nav-tab/after', $API, $remove_tags, $gps_keys); ?>
                        </nav>
                        <?php do_action('cfgp/main_page/tab-panel/before', $API, $remove_tags, $gps_keys); ?>
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="shortcodes">
                        	<p>
								<?php esc_html_e('These are the shortcodes available for use in places where shortcodes can be executed.', 'cf-geoplugin'); ?>
								<?php printf(
									esc_html__('The usage and functionality of these shortcodes are explained in our %s.', 'cf-geoplugin'),
									'<a href="' . CFGP_STORE . '/documentation/quick-start/geo-controller-shortcodes" target="_blank">' . esc_html__('documentation', 'cf-geoplugin') . '</a>'
								); ?>
							</p>

                            <?php if ($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php do_action('cfgp/table/before/shortcodes', $API); ?>
                                    <?php foreach (apply_filters('cfgp/table/shortcodes', array_merge(
                                        ['cfgeo_flag' => CFGP_U::admin_country_flag($API['country_code'])],
                                        $API
                                    ), $API) as $key => $value) : if (in_array($key, $remove_tags, true)) {
                                        continue;
                                    } ?>
                                    <tr>
                                    <?php if (in_array($key, ['cfgeo_flag'], true)) : ?>
                                    	<td><code>[<?php echo esc_html($key); ?>]</code></td>
                                    <?php else : ?>
                                    	<td>
											<code>[cfgeo return="<?php echo esc_attr($key); ?>"]</code>
										</td>
                                    <?php endif; ?>
                                        <td>
											<span class="cfgp-value"><?php
                                                if (in_array($key, ['cfgeo_flag', 'credit'], true)) {
                                                    echo wp_kses_post($value ?? '-');
                                                } else {
                                                    echo esc_html($value ?? '-');
                                                }
                                        ?></span>
											<?php if ($API['gps'] == 1 && in_array($key, $gps_keys, true)) : ?>
											<sup class="cfgp-gps-marker"><b><?php esc_html_e('GPS', 'cf-geoplugin'); ?></b></sup>
											<?php endif; ?>
										</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php do_action('cfgp/table/after/shortcodes', $API); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php endif; ?>
                        </div>
                        <?php if (CFGP_Options::get_beta('enable_simple_shortcode')) : ?>
                            <div class="cfgp-tab-panel" id="simple-shortcodes">
                                <p>
									<?php esc_html_e('These are the shortcodes available for use in places where shortcodes can be executed.', 'cf-geoplugin'); ?>
									<?php echo wp_kses_post(sprintf(
										__('The usage and functionality of these shortcodes are explained in our %s.', 'cf-geoplugin'),
										'<a href="' . CFGP_STORE . '/documentation/quick-start/geo-controller-shortcodes/cfgeo_property" target="_blank">' . esc_html__('documentation', 'cf-geoplugin') . '</a>'
									)); ?>
								</p>
								<p>
									<?php esc_html_e('These shortcodes are only intended to return available geo-information. You cannot include, exclude, or add default values. They simply display geodata according to the appropriate shortcode.', 'cf-geoplugin'); ?>
								</p>

                                <?php if ($API) : ?>
                                <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                            <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    	<?php do_action('cfgp/table/before/simple_shortcodes', $API); ?>
                                        <?php foreach (apply_filters('cfgp/table/simple_shortcodes', array_merge(
                                            ['country_flag' => CFGP_U::admin_country_flag($API['country_code'])],
                                            $API
                                        ), $API) as $key => $value) : if (in_array($key, $remove_tags, true)) {
                                            continue;
                                        } ?>
                                        <tr>
                                            <td><code>[cfgeo_<?php echo esc_attr($key); ?>]</code></td>
                                            <td>
												<span class="cfgp-value"><?php
                                                if (in_array($key, ['country_flag', 'credit'], true)) {
                                                    echo wp_kses_post($value ?? '-');
                                                } else {
                                                    echo esc_html($value ?? '-');
                                                }
                                            ?></span>
												<?php if ($API['gps'] == 1 && in_array($key, $gps_keys, true)) : ?>
												<sup class="cfgp-gps-marker"><b><?php esc_html_e('GPS', 'cf-geoplugin'); ?></b></sup>
												<?php endif; ?>
											</td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php do_action('cfgp/table/after/simple_shortcodes', $API); ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                            <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="cfgp-tab-panel" id="tags">
                       		<p>
								<?php echo wp_kses_post(sprintf(
									__('These special tags are designed for quickly inserting geo-information into pages and posts. They allow the use of geo-information in page titles and content, categories, and other taxonomies. They can also be used in widgets, various page builders, and are supported by several SEO plugins such as Yoast, All in One SEO Pack, SEO Framework, and Rank Math. For more details, please read our %s.', 'cf-geoplugin'),
									'<a href="' . CFGP_STORE . '/documentation/quick-start/cf-geo-plugin-tags" target="_blank">' . esc_html__('documentation', 'cf-geoplugin') . '</a>'
								)); ?>
							</p>

                            <?php if ($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php do_action('cfgp/table/before/tags', $API); ?>
                                    <?php foreach (apply_filters('cfgp/table/tags', $API) as $key => $value) : if (in_array($key, $remove_tags, true)) {
                                        continue;
                                    } ?>
                                    <tr>
                                        <td><code>%%<?php echo esc_html($key); ?>%%</code></td>
                                        <td>
											<span class="cfgp-value"><?php
                                                if (in_array($key, ['cfgeo_flag', 'credit'], true)) {
                                                    echo wp_kses_post($value ?? '-');
                                                } else {
                                                    echo esc_html($value ?? '-');
                                                }
                                        ?></span>
											<?php if ($API['gps'] == 1 && in_array($key, $gps_keys, true)) : ?>
											<sup class="cfgp-gps-marker"><b><?php esc_html_e('GPS', 'cf-geoplugin'); ?></b></sup>
											<?php endif; ?>
										</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php do_action('cfgp/table/after/tags', $API); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php esc_html_e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php endif; ?>
                        </div>

						<?php if (CFGP_Options::get('enable_css')) : ?>
						<div class="cfgp-tab-panel" id="css-property">
							
							<p><?php esc_html_e('Geo Controller provides dynamic CSS settings that can hide or display content when used correctly.', 'cf-geoplugin'); ?></p>

							<p><b><big><?php esc_html_e('How to use it?', 'cf-geoplugin'); ?></big></b></p>

							<p><?php esc_html_e('These CSS settings are dynamic and depend on the visitor’s geolocation.', 'cf-geoplugin'); ?></p>

							<p>
								<?php echo wp_kses_post(sprintf(
									__('A different CSS class is generated for each state, city, or region following this principle: %s or %s, where %s represents the geo-location name in lowercase letters with multiple words separated by a dash.', 'cf-geoplugin'),
									'<code>cfgeo-show-in-' . esc_html__('{property}', 'cf-geoplugin') . '</code>',
									'<code>cfgeo-hide-from-' . esc_html__('{property}', 'cf-geoplugin') . '</code>',
									'<code>' . esc_html__('{property}', 'cf-geoplugin') . '</code>'
								)); ?>
							</p>

							<p><?php esc_html_e('You can insert these CSS classes inside your HTML via the class attribute, just like any other CSS rule.', 'cf-geoplugin'); ?></p>


							<table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Show content', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Hide content', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php
                                    do_action('cfgp/table/before/css_property', $API);
						    $CFGEO       = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
						    $allowed_css = apply_filters('cfgp/public/css/allowed', [
						        'country',
						        'country_code',
						        'region',
						        'city',
						        'continent',
						        'continent_code',
						        'currency',
						        'base_currency',
						    ]);

						    foreach ($CFGEO as $key => $geo) :
						        if (empty($geo) || !in_array($key, $allowed_css, true) !== false) {
						            continue;
						        }
						        $geo = sanitize_title($geo);
						        ?>
                                    <tr>
                                        <td><code>cfgeo-show-in-<?php echo esc_html($geo); ?></code></td>
                                        <td><code>cfgeo-hide-from-<?php echo esc_html($geo); ?></code></td>
                                    </tr>
                                    <?php endforeach;
						    do_action('cfgp/table/after/css_property', $API); ?>
									<tr>
                                        <td><code>cfgeo-show-in-tor</code></td>
                                        <td><code>cfgeo-hide-from-tor</code></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php esc_html_e('Show content', 'cf-geoplugin'); ?></th>
                                        <th><?php esc_html_e('Hide content', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
						</div>
						<?php endif; ?>

                        <?php do_action('cfgp/main_page/tab-panel/after', $API, $remove_tags, $gps_keys); ?>
                   	</div>

                </div>

            </div>
			
			<div class="inner-sidebar" id="<?php echo esc_attr(CFGP_NAME); ?>-main-page-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action('cfgp/page/main_page/sidebar'); ?>
				</div>
			</div>
				
            <br class="clear">
        </div>
    </div>
</div>
