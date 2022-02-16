<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * License Response
 *
 * @since    8.0.0
 **/
add_action('admin_footer', function(){
	CFGP_License::print_response_errors();
	CFGP_License::print_response_success();
}, 30);

/**
 * License Page
 *
 * @since    8.0.0
 **/
add_action('cfgp/page/license/content', function(){

if( CFGP_U::api('lookup') != 'lifetime' ) :
	
	$select_options = array();

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if(wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-activate-license') !== false){
			CFGP_License::activate(CFGP_U::request_string('license_key'), CFGP_U::request_string('license_sku'));
		} else if(isset($_POST['deactivate_license']) && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-deactivate-license') !== false){
			CFGP_License::deactivate();
		}
		
		wp_safe_redirect( CFGP_U::admin_url('/admin.php?page=cf-geoplugin-activate') ); exit;
	}

	foreach(CFGP_License::get_product_data() as $i => $product){
		
		$name = CFGP_License::name($product['sku']);
		
		if(empty($name)) continue;
		
		if($product['price']['sale'] > 0){
			$price = "<del>{$product['price']['regular']}{$product['price']['currency']}</del><ins>{$product['price']['amount']}{$product['price']['currency']}</ins>";
		} else {
			$price = $product['price']['amount'].$product['price']['currency'];
		}
		
		$select_options[]=sprintf(
			'<label for="%1$s"><input type="radio" name="license_sku" id="%1$s" value="%3$s" data-url="%4$s"%5$s><div class="cfgp-form-product-checkbox-item"><h3>%2$s</h3><h4>%7$s:</h4><span class="cfgp-form-product-checkbox-price">%6$s</span></div><small><a href="%4$s" target="_blank">%8$s</a></small></label>',
			esc_attr($product['slug']),
			$name,
			esc_attr($product['sku']),
			(!empty($product['url']) ? esc_url($product['url']) : 'javascript:void();'),
			(CFGP_License::get('sku', CFGP_U::request_string('license_sku')) == $product['sku'] ? ' checked' : '')
			.(CFGP_License::activated() ? ' disabled' : ''),
			$price,
			__('Price', CFGP_NAME),
			(!empty($product['url']) ? (CFGP_DEV_MODE && $product['sku']=='CFGEODEV' ? __('You must become a developer for this license', CFGP_NAME) : __('Learn more about this product', CFGP_NAME)) : '')
		);
	}
?>
<form method="post" autocomplete="off"<?php echo (CFGP_License::activated() ? ' onsubmit="return confirm(\''.esc_attr__('Are you sure you want to deactivate your license? This decision can limit your plugin functions.', CFGP_NAME).'\');"' : ''); ?>>
<div class="cfgp-license-container">
	    
    <div class="cfgp-form-product-checkbox">
    	<?php echo join(PHP_EOL, $select_options); ?>
        <div class="cfgp-form-product-license">
        	<div class="cfgp-form-product-license-item">
            	<label for="license_key"><?php _e('License Key', CFGP_NAME); ?></label>
                <input type="text" name="license_key" id="license_key" value="<?php echo esc_attr(CFGP_License::get('key', CFGP_U::request_string('license_key'))); ?>" placeholder="<?php esc_attr_e('Insert your license key here', CFGP_NAME); ?>" autocomplete="off"<?php echo (CFGP_License::activated() ? ' disabled' : ''); ?>>
                <?php if(!CFGP_License::activated()) : ?>
                	<p>(<?php _e('License type must match to your license key that you ordered.', CFGP_NAME); ?>)</p>
                    <button type="submit" class="button button-primary cfgp-activate-license"><?php _e('Activate your license', CFGP_NAME); ?></button>
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(CFGP_NAME.'-activate-license'); ?>">
				<?php else: ?>
                	<button type="submit" class="button button-primary cfgp-deactivate-license"><?php _e('Dectivate current license', CFGP_NAME); ?></button>
                    <input type="hidden" name="deactivate_license" value="1">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(CFGP_NAME.'-deactivate-license'); ?>">
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>	
</form>
<?php
else : ?>
<p><?php _e('As one of the first users of our plugin, you have the honor of using a unique lifetime license that allows you unlimited lookup.', CFGP_NAME); ?></p>
<p><?php _e('Therefore, you have no option to change or deactivate the license.', CFGP_NAME); ?></p>
<?php endif; }, 1);

/**
 * License sidebar
 *
 * @since    8.0.0
 **/
add_action('cfgp/page/license/sidebar', function(){ if( CFGP_U::api('lookup') == 'lifetime' ) return;
?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('License Information', CFGP_NAME); ?></span></h3><hr>
	<div class="inside">
    	<?php if(CFGP_License::activated()) : ?>
        	<p><?php printf(
    __('Thank you for using an unlimited license. Your license is active until %1$s. It would be great to expand your license by that date. After expiration date you will experience plugin limitations.<br><br>To review or deactivate your license, please go to your %2$s.',CFGP_NAME),
    '<strong>' . (CFGP_License::get('expire') == 0 ? __('never',CFGP_NAME) : CFGP_License::expire_date()) . '</strong>',
	'<a href="' . CFGP_License::get('url') . '" target="_blank">' . __('CF Geo Plugin account',CFGP_NAME) . '</a>'
); ?></p>
			<p><?php printf(
            __('Do not forget that by purchasing and using the license you have agreed to our %2$s in accordance with the %1$s.'),
            '<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy', CFGP_NAME) . '</a></strong>',
            '<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions', CFGP_NAME) . '</a></strong>'
        ); ?></p>
		<?php elseif(CFGP_U::api('lookup') === 'unlimited') : ?>
		<p style="font-weight:600;"><?php _e('An update error occurred and your license was not recorded on your server.'); ?></p>
		<p><?php _e('This should not scare you because our API has recognized the problem and still gives you all the necessary information without restrictions.'); ?></p>
		<p><?php _e('But for the plugin to work properly, please re-enter your license and activate the plugin to unlock all internal features.'); ?></p>
		<?php else: ?>
        <p><?php printf(
            __('You currently use a free version of plugin with a limited number of lookups. Each free version of this plugin is limited to %1$s lookups per day and you have only %2$s lookups available for today. If you want to have unlimited lookup, please enter your license key. If you are unsure and do not understand what this is about, read %3$s.', CFGP_NAME),
            
            '<strong>'.CFGP_LIMIT.'</strong>',
            '<strong>'.CFGP_U::api('lookup').'</strong>',
            '<strong><a href="https://cfgeoplugin.com/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank">' . __('this article', CFGP_NAME) . '</a></strong>'
        ); ?></p>
        <p><?php printf(
            __('Before any action don\'t forget to read and agree with %1$s and %2$s.'),
            '<strong><a href="https://cfgeoplugin.com/privacy-policy/" target="_blank">' . __('Privacy Policy', CFGP_NAME) . '</a></strong>',
            '<strong><a href="https://cfgeoplugin.com/terms-and-conditions/" target="_blank">' . __('Terms & Conditions', CFGP_NAME) . '</a></strong>'
        ); ?></p>
        <?php endif; ?>
	</div>
</div>
<?php
}, 10);