<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( CFGP_U::dev_mode() ) {
	$remove_tags = [];
} else {
	$remove_tags = array(
		'error',
		'error_message',
		'is_eu',
		'is_vat',
		'is_mobile',
		'is_proxy',
		'is_spam',
		'limited',
		'gps',
		'license_hash',
		'is_local_server'
	);
}

$remove_tags = apply_filters('cfgp/main_page/remove_tags', $remove_tags);

$gps_keys = apply_filters('cfgp/main_page/gps_keys', array(
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
	'district'
));

$API = CFGP_U::api(false, CFGP_Defaults::API_RETURN);

?>
<div class="wrap cfgp-wrap" id="<?php echo esc_attr(sanitize_text_field($_GET['page'] ?? NULL)); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-map-marker"></i> <?php _e('Geo Controller', 'cf-geoplugin'); ?></h1>
    <hr class="wp-header-end">

    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
			
        	<div id="post-body">
            	<div id="post-body-content">

                    <div class="nav-tab-wrapper-chosen">
                        <nav class="nav-tab-wrapper">
                        	<?php do_action('cfgp/main_page/nav-tab/before', $API, $remove_tags, $gps_keys); ?>
                            <a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#shortcodes"><i class="cfa cfa-code"></i><span class="label"> <?php _e('Shortcodes', 'cf-geoplugin'); ?></span></a>
                            <?php if(CFGP_Options::get_beta('enable_simple_shortcode')) : ?>
                            	<a href="javascript:void(0);" class="nav-tab" data-id="#simple-shortcodes"><i class="cfa cfa-code"></i><span class="label"> <?php _e('Simple Shortcodes', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                            <a href="javascript:void(0);" class="nav-tab" data-id="#tags"><i class="cfa cfa-tag"></i><span class="label"> <?php _e('Tags', 'cf-geoplugin'); ?></span></a>
							<?php if(CFGP_Options::get('enable_css')) : ?>
                            	<a href="javascript:void(0);" class="nav-tab" data-id="#css-property"><i class="cfa cfa-css3"></i><span class="label"> <?php _e('CSS property', 'cf-geoplugin'); ?></span></a>
                            <?php endif; ?>
                            <?php do_action('cfgp/main_page/nav-tab/after', $API, $remove_tags, $gps_keys); ?>
                        </nav>
                        <?php do_action('cfgp/main_page/tab-panel/before', $API, $remove_tags, $gps_keys); ?>
                        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="shortcodes">
                        	<p><?php _e('These are short codes available for use in places where short codes can be executed.', 'cf-geoplugin'); ?> <?php printf(__('The use and functionality of these short codes are described in our %s.', 'cf-geoplugin'), '<a href="https://cfgeoplugin.com/documentation/quick-start/cf-geoplugin-shortcodes" target="_blank">' . __('documentation', 'cf-geoplugin') . '</a>'); ?></p>
                            <?php if($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php do_action('cfgp/table/before/shortcodes', $API); ?>
                                    <?php foreach(apply_filters('cfgp/table/shortcodes', array_merge(
										array('cfgeo_flag' => CFGP_U::admin_country_flag($API['country_code'])), 
										$API
									), $API) as $key => $value) : if(in_array($key, $remove_tags)) continue; ?>
                                    <tr>
                                    <?php if(in_array($key, array('cfgeo_flag'))) : ?>
                                    	<td><code>[<?php echo esc_html($key); ?>]</code></td>
                                    <?php else : ?>
                                    	<td>
											<code>[cfgeo return="<?php echo esc_attr($key); ?>"]</code>
										</td>
                                    <?php endif; ?>
                                        <td>
											<span class="cfgp-value"><?php
												if( in_array($key, ['cfgeo_flag', 'credit']) ) {
													echo wp_kses_post( $value ?? '-' );
												} else {
													echo esc_html( $value ?? '-' );
												}
											?></span>
											<?php if($API['gps'] == 1 && in_array($key, $gps_keys)) : ?>
											<sup class="cfgp-gps-marker"><b><?php _e('GPS', 'cf-geoplugin'); ?></b></sup>
											<?php endif; ?>
										</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php do_action('cfgp/table/after/shortcodes', $API); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php endif; ?>
                        </div>
                        <?php if(CFGP_Options::get_beta('enable_simple_shortcode')) : ?>
                            <div class="cfgp-tab-panel" id="simple-shortcodes">
                                <p><?php _e('These are short codes available for use in places where short codes can be executed.', 'cf-geoplugin'); ?> <?php printf(__('The use and functionality of these short codes are described in our %s.', 'cf-geoplugin'), '<a href="https://cfgeoplugin.com/documentation/quick-start/cf-geoplugin-shortcodes" target="_blank">' . __('documentation', 'cf-geoplugin') . '</a>'); ?></p>
                                <p><?php _e('These shortcodes only have the purpose to return available geo-information. You can\'t include, exclude or add default values. Just display geodata following with appropriate shortcodes.', 'cf-geoplugin'); ?></p>
                                <?php if($API) : ?>
                                <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                            <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    	<?php do_action('cfgp/table/before/simple_shortcodes', $API); ?>
                                        <?php foreach(apply_filters('cfgp/table/simple_shortcodes', array_merge(
                                                array('country_flag' => CFGP_U::admin_country_flag($API['country_code'])), 
                                                $API
                                            ), $API) as $key => $value) : if(in_array($key, $remove_tags)) continue; ?>
                                        <tr>
                                            <td><code>[cfgeo_<?php echo esc_attr($key); ?>]</code></td>
                                            <td>
												<span class="cfgp-value"><?php
													if( in_array($key, ['country_flag', 'credit']) ) {
														echo wp_kses_post( $value ?? '-' );
													} else {
														echo esc_html( $value ?? '-' );
													}
												?></span>
												<?php if($API['gps'] == 1 && in_array($key, $gps_keys)) : ?>
												<sup class="cfgp-gps-marker"><b><?php _e('GPS', 'cf-geoplugin'); ?></b></sup>
												<?php endif; ?>
											</td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php do_action('cfgp/table/after/simple_shortcodes', $API); ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                            <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="cfgp-tab-panel" id="tags">
                       		<p><?php _e('These special tags are intended for quick insertion of geo information into pages and posts. These tags allow the use of geo information in the titles & content of pages, categories and other taxonomy. It can also be used in widgets, various page builders and supports several SEO plugins like Yoast, All in One Seo Pack, SEO Framework and WordPress SEO Plugin by Rank Math.', 'cf-geoplugin'); ?></p>
                            <?php if($API) : ?>
                            <table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php do_action('cfgp/table/before/tags', $API); ?>
                                    <?php foreach(apply_filters('cfgp/table/tags', $API) as $key => $value) : if(in_array($key, $remove_tags)) continue; ?>
                                    <tr>
                                        <td><code>%%<?php echo esc_html($key); ?>%%</code></td>
                                        <td>
											<span class="cfgp-value"><?php
												if( in_array($key, ['cfgeo_flag', 'credit']) ) {
													echo wp_kses_post( $value ?? '-' );
												} else {
													echo esc_html( $value ?? '-' );
												}
											?></span>
											<?php if($API['gps'] == 1 && in_array($key, $gps_keys)) : ?>
											<sup class="cfgp-gps-marker"><b><?php _e('GPS', 'cf-geoplugin'); ?></b></sup>
											<?php endif; ?>
										</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php do_action('cfgp/table/after/tags', $API); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Shortcode', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Return', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php endif; ?>
                        </div>

						<?php if(CFGP_Options::get('enable_css')) : ?>
						<div class="cfgp-tab-panel" id="css-property">
							
							<p><?php _e('The Geo Controller has dynamic CSS settings that can hide or display some content if you use it properly.', 'cf-geoplugin'); ?></p>
							<p><b><big><?php _e('How to use it?', 'cf-geoplugin'); ?></big></b></p>
							<p><?php _e('These CSS settings are dynamic and depend on the geolocation of the visitor.', 'cf-geoplugin'); ?></p>
							<p><?php printf(__('A different CSS setting is generated for each state, city, region according to the following principle: %s or %s, where the %s is actually a geo-location name in lowercase letters and multiple words separated by a minus sign.', 'cf-geoplugin'), '<code>cfgeo-show-in-' . __('{property}', 'cf-geoplugin') . '</code>', '<code>cfgeo-hide-from-' . __('{property}', 'cf-geoplugin') . '</code>', '<code>' . __('{property}', 'cf-geoplugin') . '</code>'); ?></p>
							<p><?php _e('These CSS settings you can insert inside your HTML via class attribute just like any other CSS setting.', 'cf-geoplugin'); ?></p>

							<table class="wp-list-table widefat fixed striped table-view-list posts table-cf-geoplugin-shortcodes">
                                <thead>
                                    <tr>
                                        <th><?php _e('Show content', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Hide content', 'cf-geoplugin'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php
									do_action('cfgp/table/before/css_property', $API);
									$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
									$allowed_css = apply_filters( 'cfgp/public/css/allowed', array(
										'country',
										'country_code',
										'region',
										'city',
										'continent',
										'continent_code',
										'currency',
										'base_currency'
									));
									foreach($CFGEO as $key=>$geo) :
									if( empty($geo) || !in_array($key, $allowed_css,true)!==false ) continue;
									$geo = sanitize_title($geo);
									?>
                                    <tr>
                                        <td><code>cfgeo-show-in-<?php echo esc_html($geo); ?></code></td>
                                        <td><code>cfgeo-hide-from-<?php echo esc_html($geo); ?></code></td>
                                    </tr>
                                    <?php endforeach; do_action('cfgp/table/after/css_property', $API); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><?php _e('Show content', 'cf-geoplugin'); ?></th>
                                        <th><?php _e('Hide content', 'cf-geoplugin'); ?></th>
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
