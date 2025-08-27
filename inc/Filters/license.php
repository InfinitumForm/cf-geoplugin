<?php

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Response
 *
 * @since    8.0.0
 **/
add_action('admin_footer', function () {
    CFGP_License::print_response_errors();
    CFGP_License::print_response_success();
}, 30);

/**
 * License Page
 *
 * @since    8.0.0
 **/
add_action('cfgp/page/license/content', function () {

    if (CFGP_U::api('available_lookup') != 'lifetime') :

        $select_options = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-activate-license') !== false) {
                CFGP_License::activate(CFGP_U::request_string('license_key'), CFGP_U::request_string('license_sku'));
            } elseif (isset($_POST['deactivate_license']) && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-deactivate-license') !== false) {
                CFGP_License::deactivate();
            }

            if (CFGP_U::redirect(CFGP_U::admin_url('/admin.php?page=cf-geoplugin-activate'))) {
                exit;
            }
        }

    foreach (CFGP_License::get_product_data() as $i => $product) {

        $name = CFGP_License::name($product['sku']);

        if (empty($name)) {
            continue;
        }

        if ($product['price']['sale'] > 0) {
            $price = "<del>{$product['price']['regular']}{$product['price']['currency']}</del><ins>{$product['price']['amount']}{$product['price']['currency']}</ins>";
        } else {
            $price = $product['price']['amount'].$product['price']['currency'];
        }

        $select_options[] = sprintf(
            '<label for="%1$s"><input type="radio" name="license_sku" id="%1$s" value="%3$s" data-url="%4$s"%5$s><div class="cfgp-form-product-checkbox-item"><h3>%2$s</h3><h4>%7$s:</h4><span class="cfgp-form-product-checkbox-price">%6$s</span></div><small><a href="%4$s" target="_blank">%8$s</a></small></label>',
            esc_attr($product['slug']),
            wp_kses_post($name ?? ''),
            esc_attr($product['sku']),
            (!empty($product['url']) ? esc_url($product['url']) : 'javascript:void();'),
            (CFGP_License::get('sku', CFGP_U::request_string('license_sku')) == $product['sku'] ? ' checked' : '')
            .(CFGP_License::activated() || CFGP_IP::is_localhost() ? ' disabled' : ''),
            wp_kses_post($price ?? ''),
            __('Price', 'cf-geoplugin'),
            (!empty($product['url']) 
				? (CFGP_DEV_MODE && $product['sku'] === 'CFGEODEV' 
					? __('You must be a developer to use this license', 'cf-geoplugin') 
					: __('Learn more about this product', 'cf-geoplugin')
				) 
				: ''
			)

        );
    }
    ?>
<form method="post" autocomplete="off"<?php echo (CFGP_License::activated() ? ' onsubmit="return confirm(\'' . esc_attr__('Are you sure you want to deactivate your license? This action may limit the functionality of the plugin.', 'cf-geoplugin') . '\');"' : ''); ?>>
<div class="cfgp-license-container">
	    
    <div class="cfgp-form-product-checkbox">
    	<?php echo wp_kses(join(PHP_EOL, $select_options), CFGP_U::allowed_html_tags_for_page()); ?>
        <div class="cfgp-form-product-license">
        	<div class="cfgp-form-product-license-item">
            	<label for="license_key"><?php esc_html_e('License Key', 'cf-geoplugin'); ?></label>
				<?php if (CFGP_IP::is_localhost()) : ?>
					<p style="color:#cc0000;"><b><?php esc_html_e('You are using the plugin on a local server, which is exempt from lookups. License activation is only possible on live servers.', 'cf-geoplugin'); ?></b></p>
				<?php endif; ?>

				<input type="text" 
					   name="license_key" 
					   id="license_key" 
					   value="<?php echo esc_attr(CFGP_License::get('key', CFGP_U::request_string('license_key'))); ?>" 
					   placeholder="<?php esc_attr_e('Enter your license key here', 'cf-geoplugin'); ?>" 
					   autocomplete="off"
					   <?php echo (CFGP_License::activated() || CFGP_IP::is_localhost() ? ' disabled' : ''); ?>>

				<?php if (!CFGP_License::activated()) : ?>
					<p>(<?php esc_html_e('The license type must match the license key you ordered.', 'cf-geoplugin'); ?>)</p>
					<button type="submit" class="button button-primary cfgp-activate-license"><?php esc_html_e('Activate License', 'cf-geoplugin'); ?></button>
					<input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(CFGP_NAME . '-activate-license')); ?>">
				<?php else: ?>
					<button type="submit" class="button button-primary cfgp-deactivate-license"><?php esc_html_e('Deactivate Current License', 'cf-geoplugin'); ?></button>
					<input type="hidden" name="deactivate_license" value="1">
					<input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(CFGP_NAME . '-deactivate-license')); ?>">
				<?php endif; ?>

            </div>
        </div>
    </div>
    
</div>	
</form>
<?php else : ?>
<p><?php esc_html_e('As one of the first users of our plugin, you have the privilege of a unique lifetime license that gives you unlimited lookups.', 'cf-geoplugin'); ?></p>
<p><?php esc_html_e('Therefore, you do not have the option to change or deactivate this license.', 'cf-geoplugin'); ?></p>
<?php endif;
}, 1);

/**
 * License sidebar
 *
 * @since    8.0.0
 **/
add_action('cfgp/page/license/sidebar', function () {
    if (CFGP_U::api('available_lookup') == 'lifetime') {
        return;
    }
    ?>
<div class="postbox" id="cfgp-postbox-license">
	<div class="postbox-header">
		<h2 class="hndle"><span><?php esc_html_e('License Information', 'cf-geoplugin'); ?></span></h2>
	</div>
	<div class="inside">
    	<?php if (CFGP_License::activated()) : ?>
			<p><?php echo wp_kses_post(sprintf(
				__('Thank you for using an unlimited license. Your license is active until %1$s. We recommend extending your license before that date. After the expiration date, the plugin will be limited.<br><br>To review or deactivate your license, please visit your %2$s.', 'cf-geoplugin'),
				'<strong>' . (CFGP_License::get('expire') == 0 ? esc_html__('never', 'cf-geoplugin') : CFGP_License::expire_date()) . '</strong>',
				'<a href="' . esc_url(CFGP_License::get('url')) . '" target="_blank">' . esc_html__('Geo Controller account', 'cf-geoplugin') . '</a>'
			)); ?></p>

			<p><?php echo wp_kses_post(sprintf(
				__('By purchasing and using this license, you have agreed to our %2$s in accordance with the %1$s.', 'cf-geoplugin'),
				'<strong><a href="https://wpgeocontroller.com/privacy-policy/" target="_blank">' . esc_html__('Privacy Policy', 'cf-geoplugin') . '</a></strong>',
				'<strong><a href="https://wpgeocontroller.com/terms-and-conditions/" target="_blank">' . esc_html__('Terms & Conditions', 'cf-geoplugin') . '</a></strong>'
			)); ?></p>

		<?php elseif (CFGP_U::api('available_lookup') === 'unlimited') : ?>
			<p style="font-weight:600;"><?php esc_html_e('An update error occurred, and your license was not recorded on your server.', 'cf-geoplugin'); ?></p>
			<p><?php esc_html_e('Don’t worry, our API recognized the issue and continues to provide all necessary information without restrictions.', 'cf-geoplugin'); ?></p>
			<p><?php esc_html_e('However, for the plugin to function properly, please re-enter your license key and activate the plugin to unlock all internal features.', 'cf-geoplugin'); ?></p>

		<?php else: ?>
			<p><?php echo wp_kses_post(sprintf(
				__('You are currently using the free version of the plugin, which has a limited number of lookups. Each free version is limited to %1$s lookups per day, and you have only %2$s lookups left for today. To unlock unlimited lookups, please enter your license key. If you are unsure what this means, read %3$s.', 'cf-geoplugin'),
				'<strong>' . esc_html(CFGP_LIMIT) . '</strong>',
				'<strong>' . esc_html(CFGP_U::api('available_lookup')) . '</strong>',
				'<strong><a href="https://wpgeocontroller.com/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank">' . __('this article', 'cf-geoplugin') . '</a></strong>'
			)); ?></p>

			<p><?php echo wp_kses_post(sprintf(
				__('Before taking any action, please read and agree to the %1$s and %2$s.', 'cf-geoplugin'),
				'<strong><a href="https://wpgeocontroller.com/privacy-policy/" target="_blank">' . __('Privacy Policy', 'cf-geoplugin') . '</a></strong>',
				'<strong><a href="https://wpgeocontroller.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions', 'cf-geoplugin') . '</a></strong>'
			)); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
}, 10);
