<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


add_action('cfgp/page/license/content', function(){
$select_options = array();
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
		esc_url($product['url']),
		(CFGP_Options::get('license_sku') == $product['sku'] ? ' checked' : ''),
		$price,
		__('Price', CFGP_NAME),
		__('Learn more about this product', CFGP_NAME)
	);
}

?>
<form method="post">
<div class="cfgp-license-container">
	    
    <div class="cfgp-form-product-checkbox">
    	<?php echo join(PHP_EOL, $select_options); ?>
        <div class="cfgp-form-product-license">
        	<div class="cfgp-form-product-license-item">
            	<label for="license_key"><?php _e('License Key', CFGP_NAME); ?></label>
                <input type="text" name="license_key" id="license_key" value="" placeholder="<?php esc_attr_e('Insert your license key here', CFGP_NAME); ?>">
                <?php if(!CFGP_License::activated()) : ?>
                	<p>(<?php _e('License type must match to your license key that you ordered.', CFGP_NAME); ?>)</p>
                    <button type="button" class="button button-primary cfgp-activate-license"><?php _e('Activate your license', CFGP_NAME); ?></button>
				<?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if(!CFGP_License::activated()) : ?>
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
</form>
<?php });