<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
 * Special dedicated short codes
 *
 * @since    8.0.0
 **/
add_action('cfgp/table/after/shortcodes', function($API){ ?>
<tr>
	<th colspan="2">
		<h3><?php esc_html_e('Special dedicated short codes', 'cf-geoplugin'); ?></h3>
	</th>
</tr>
<tr>
    <td><code>[cfgeo_is_mobile]<?php esc_html_e('You using mobile phone', 'cf-geoplugin'); ?>[/cfgeo_is_mobile]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_mobile default="-"]' .esc_html__('You using mobile phone', 'cf-geoplugin'). '[/cfgeo_is_mobile]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_desktop]<?php esc_html_e('You using Desktop', 'cf-geoplugin'); ?>[/cfgeo_is_desktop]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_desktop default="-"]' .esc_html__('You using Desktop', 'cf-geoplugin'). '[/cfgeo_is_desktop]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_vat]<?php esc_html_e('You are under VAT', 'cf-geoplugin'); ?>[/cfgeo_is_vat]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_vat default="-"]' .esc_html__('You are under VAT', 'cf-geoplugin'). '[/cfgeo_is_vat]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_not_vat]<?php esc_html_e('You are NOT under VAT', 'cf-geoplugin'); ?>[/cfgeo_is_not_vat]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_not_vat default="-"]' .esc_html__('You are NOT under VAT', 'cf-geoplugin'). '[/cfgeo_is_not_vat]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_in_eu]<?php esc_html_e('You are from the EU', 'cf-geoplugin'); ?>[/cfgeo_in_eu]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_in_eu default="-"]' .esc_html__('You are from the EU', 'cf-geoplugin'). '[/cfgeo_in_eu]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_not_in_eu]<?php esc_html_e('You are NOT from the EU', 'cf-geoplugin'); ?>[/cfgeo_not_in_eu]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_not_in_eu default="-"]' .esc_html__('You are NOT from the EU', 'cf-geoplugin'). '[/cfgeo_not_in_eu]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_tor]<?php esc_html_e('This is TOR network!', 'cf-geoplugin'); ?>[/cfgeo_is_tor]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_tor default="-"]' .esc_html__('This is TOR network!', 'cf-geoplugin'). '[/cfgeo_is_tor]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_not_tor]<?php esc_html_e('This is World-Wide-Web network!', 'cf-geoplugin'); ?>[/cfgeo_is_not_tor]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_not_tor default="-"]' .esc_html__('This is World-Wide-Web network', 'cf-geoplugin'). '[/cfgeo_is_not_tor]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_gps]<?php esc_html_e('GPS is enabled', 'cf-geoplugin'); ?>[/cfgeo_gps]</code></td>
    <td>
        <?php echo esc_html( do_shortcode('[cfgeo_gps]' .esc_html__('GPS is enabled.', 'cf-geoplugin'). '[/cfgeo_gps]') ); ?> 
        <span class="badge"><?php
            if(CFGP_U::is_plugin_active('cf-geoplugin-gps/cf-geoplugin-gps.php'))
            {
                echo esc_html( do_shortcode('[cfgeo_gps default="' .esc_attr__('GPS is NOT enabled', 'cf-geoplugin'). '"]' .esc_html__('GPS is enabled', 'cf-geoplugin'). '[/cfgeo_gps]') );
            }
            else
            {
                echo wp_kses_post( sprintf( 
                    sprintf(
                        ' ' . __('GPS is enabled only with %s extension', 'cf-geoplugin'),
                        sprintf(
                            '<a href="%1$s" class="thickbox open-plugin-details-modal" target="_blank">' . esc_html__('Geo Controller GPS', 'cf-geoplugin') . '</a>',
                            esc_url( CFGP_U::admin_url('plugin-install.php?tab=plugin-information&plugin=cf-geoplugin-gps&TB_iframe=true&width=772&height=923') )
                        )
                    )
                ) );
            }
        ?></span>
    </td>
</tr>
<tr>
    <td><code>[cfgeo_is_proxy]<?php esc_html_e('Proxy connection', 'cf-geoplugin'); ?>[/cfgeo_is_proxy]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_proxy default="-"]' .esc_html__('Proxy connection', 'cf-geoplugin'). '[/cfgeo_is_proxy]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_is_not_proxy]<?php esc_html_e('Is not proxy connection', 'cf-geoplugin'); ?>[/cfgeo_is_not_proxy]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_is_not_proxy default="-"]' .esc_html__('Is not proxy connection', 'cf-geoplugin'). '[/cfgeo_is_not_proxy]') ); ?></td>
</tr>
<tr>
    <td><code>[cfgeo_converter from="<?php echo esc_attr(CFGP_Options::get('base_currency')); ?>" to="<?php echo esc_attr(CFGP_U::api('currency')); ?>"]10[/cfgeo_converter]</code></td>
    <td><?php echo esc_html( do_shortcode('[cfgeo_converter from="' . esc_attr(CFGP_Options::get('base_currency')) . '" to="' . esc_attr(CFGP_U::api('currency')) . '"]10[/cfgeo_converter]') ); ?></td>
</tr>
<?php }, 30);

/**
 * Special dedicated simple short codes
 *
 * @since    8.0.0
 **/
add_action('cfgp/table/after/simple_shortcodes', function($API){ ?>
<tr>
	<th colspan="2">
		<h3><?php esc_html_e('Special dedicated short codes', 'cf-geoplugin'); ?></h3>
	</th>
</tr>
<tr>
    <td><code>[is_mobile]<?php esc_html_e('You using mobile phone', 'cf-geoplugin'); ?>[/is_mobile]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_mobile default="-"]' .esc_html__('You using mobile phone', 'cf-geoplugin'). '[/is_mobile]') ); ?></td>
</tr>
<tr>
    <td><code>[is_desktop]<?php esc_html_e('You using Desktop', 'cf-geoplugin'); ?>[/is_desktop]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_desktop default="-"]' .esc_html__('You using Desktop', 'cf-geoplugin'). '[/is_desktop]') ); ?></td>
</tr>
<tr>
    <td><code>[is_vat]<?php esc_html_e('You are under VAT', 'cf-geoplugin'); ?>[/is_vat]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_vat default="-"]' .esc_html__('You are under VAT', 'cf-geoplugin'). '[/is_vat]') ); ?></td>
</tr>
<tr>
    <td><code>[is_not_vat]<?php esc_html_e('You are NOT under VAT', 'cf-geoplugin'); ?>[/is_not_vat]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_not_vat default="-"]' .esc_html__('You are NOT under VAT', 'cf-geoplugin'). '[/is_not_vat]') ); ?></td>
</tr>
<tr>
    <td><code>[in_eu]<?php esc_html_e('You are from the EU', 'cf-geoplugin'); ?>[/in_eu]</code></td>
    <td><?php echo esc_html( do_shortcode('[in_eu default="-"]' .esc_html__('You are from the EU', 'cf-geoplugin'). '[/in_eu]') ); ?></td>
</tr>
<tr>
    <td><code>[not_in_eu]<?php esc_html_e('You are NOT from the EU', 'cf-geoplugin'); ?>[/not_in_eu]</code></td>
    <td><?php echo esc_html( do_shortcode('[not_in_eu default="-"]' .esc_html__('You are NOT from the EU', 'cf-geoplugin'). '[/not_in_eu]') ); ?></td>
</tr>
<tr>
    <td><code>[is_tor]<?php esc_html_e('This is TOR network!', 'cf-geoplugin'); ?>[/is_tor]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_tor default="-"]' .esc_html__('This is TOR network!', 'cf-geoplugin'). '[/is_tor]') ); ?></td>
</tr>
<tr>
    <td><code>[is_not_tor]<?php esc_html_e('This is World-Wide-Web network!', 'cf-geoplugin'); ?>[/is_not_tor]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_not_tor default="-"]' .esc_html__('This is World-Wide-Web network', 'cf-geoplugin'). '[/is_not_tor]') ); ?></td>
</tr>
<tr>
    <td><code>[gps]<?php esc_html_e('GPS is enabled', 'cf-geoplugin'); ?>[/gps]</code></td>
    <td>
        <?php echo esc_html( do_shortcode('[gps]' .esc_html__('GPS is enabled.', 'cf-geoplugin'). '[/gps]') ); ?> 
        <span class="badge"><?php
            if(CFGP_U::is_plugin_active('cf-geoplugin-gps/cf-geoplugin-gps.php'))
            {
                echo esc_html( do_shortcode('[gps default="' .esc_attr__('GPS is NOT enabled', 'cf-geoplugin'). '"]' . esc_html__('GPS is enabled', 'cf-geoplugin'). '[/gps]') );
            }
            else
            {
                echo wp_kses_post( sprintf( 
                    sprintf(
                        ' ' . __('GPS is enabled only with %s extension', 'cf-geoplugin'),
                        sprintf(
                            '<a href="%1$s" class="thickbox open-plugin-details-modal" target="_blank">' . esc_html__('Geo Controller GPS', 'cf-geoplugin') . '</a>',
                            esc_url( CFGP_U::admin_url('plugin-install.php?tab=plugin-information&plugin=cf-geoplugin-gps&TB_iframe=true&width=772&height=923') )
                        )
                    )
                ) );
            }
        ?></span>
    </td>
</tr>
<tr>
    <td><code>[is_proxy]<?php esc_html_e('Proxy connection', 'cf-geoplugin'); ?>[/is_proxy]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_proxy default="-"]' .esc_html__('Proxy connection', 'cf-geoplugin'). '[/is_proxy]') ); ?></td>
</tr>
<tr>
    <td><code>[is_not_proxy]<?php esc_html_e('Is not proxy connection', 'cf-geoplugin'); ?>[/is_not_proxy]</code></td>
    <td><?php echo esc_html( do_shortcode('[is_not_proxy default="-"]' .esc_html__('Is not proxy connection', 'cf-geoplugin'). '[/is_not_proxy]') ); ?></td>
</tr>
<?php }, 30);