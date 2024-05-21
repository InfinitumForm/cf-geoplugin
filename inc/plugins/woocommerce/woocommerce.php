<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * WooCommerce integration
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__woocommerce', false ) ):
class CFGP__Plugin__woocommerce extends CFGP_Global
{	
    /**
     * Geo Controller converter option
     */
    private $cf_conversion					= 'original';
	private $cf_conversion_adjust 			= 0;
	private $cf_save_location		 		= 'yes';
	private $woocommerce_currency;

    private function __construct()
    {
        $this->add_action( 'plugins_loaded', 'check_woocommerce_instalation', 99 );
		
		$this->cf_conversion 				= get_option( 'woocommerce_cf_geoplugin_conversion', 'original');
		$this->cf_conversion_adjust 		= get_option( 'woocommerce_cf_geoplugin_conversion_adjust', 0);
		$this->woocommerce_currency 		= get_option( 'woocommerce_currency');
		
		if($this->woocommerce_currency)
		{
			$this->add_filter('cf_geoplugin_api_run_options', 'change_api_run_options', 1);
			$this->add_filter('cf_geoplugin_default_options', 'change_api_run_options', 1);
			$this->add_filter('cf_geoplugin_get_option', 'change_api_run_options', 1);
		}
		
		if( is_admin() ) {
			$this->add_action( 'admin_footer', 'admin_footer', 10 );
		}
		
		$this->add_action( 'wp_footer', 'wp_footer', 50 );
	
		if('yes' === get_option( 'woocommerce_cf_geoplugin_save_checkout_location', 'no' )) {
			$this->add_action('woocommerce_checkout_create_order', 'woocommerce_geolocation_log', 20, 1);
			$this->add_action('add_meta_boxes', 'customer_order_info', 1);
		}
    }
	
	// Check if woocommerce is installed and active
    public function check_woocommerce_instalation()
    {
		if( CFGP_U::api('currency_converter') > 0 )
		{
			if($this->cf_conversion != 'original')
			{
				// All prices conversion
				if('yes' === get_option( 'woocommerce_cf_geoplugin_conversion_in_admin', 'yes' )) {
					if( !is_admin() ) $this->add_filter( 'wc_price', 'wc_price', 99, 3 );
				} else {
					$this->add_filter( 'wc_price', 'wc_price', 99, 3 );
				}
			}
			
			// Add custom option for conversion system
			$this->add_filter( 'woocommerce_general_settings', 'conversion_options', 10 );
		}
		
		// Add a settings tabs
		$this->add_filter( 'woocommerce_settings_tabs_array', 'cfgp_woocommerce_tabs', 50 );
		// Add payment settings
		$this->add_action( 'woocommerce_settings_tabs_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings' );
		
		// Add settings
		 if( CFGP_License::level() >= 2 || CFGP_U::dev_mode())
		{
			// Save our settings for payments
			$this->add_action( 'woocommerce_update_options_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings_save' );
			// Disable payment gateways for specifis users
			$this->add_filter( 'woocommerce_available_payment_gateways', 'cfgp_woocommerce_payment_disable' );
		}
		
		// Save base currency value
		$this->add_action( 'woocommerce_update_options_general', 'woocommerce_update_options_general' );

		$this->add_filter('cf_geoplugin_woocommerce_currency_and_symbol', 'calculate_conversions', 1);
		
		$this->add_filter('cf_geoplugin_raw_woocommerce_converted_price', 'calculate_and_modify_price', 1);
		$this->add_filter('cf_geoplugin_raw_woocommerce_price', 'calculate_and_modify_price', 1);
		
		// Add Geo Controller to settings
		$this->add_filter( 'woocommerce_general_settings', 'woocommerce_general_settings', 10, 2 );
		// Apply geolocation to woocommerce
		
		if('cf_geoplugin' === get_option( 'woocommerce_default_customer_address' )) {
			$this->add_filter( 'woocommerce_get_geolocation', 'woocommerce_get_geolocation', 10, 2 );
			$this->add_filter( 'woocommerce_geolocation_ajax_get_location_hash', 'woocommerce_geolocation_ajax_get_location_hash', 10, 1 );
			$this->add_filter( 'woocommerce_get_tax_location', 'woocommerce_get_tax_location', 10, 3 );
			$this->add_filter( 'woocommerce_customer_default_location', 'woocommerce_customer_default_location', 10, 1 );
			$this->add_action('woocommerce_checkout_create_order', 'woocommerce_change_ip', 100, 1);
		}
    }
	
	public function woocommerce_change_ip ( $order ) {
		$order->update_meta_data('_customer_ip_address', CFGP_U::api('ip'));
	}
	
	public function woocommerce_update_options_general ($settings) {
		CFGP_Options::set('base_currency', get_option('woocommerce_currency'));
		CFGP_U::flush_plugin_cache();
	}
	
	// Add Geo Controller option to general settings
	public function woocommerce_general_settings ($settings) {
		
		foreach($settings as &$option) {
			if($option['id'] === 'woocommerce_default_customer_address') {
				$option['options'] = array_merge($option['options'], array(
					'cf_geoplugin' => __('Geolocate (by Geo Controller)', 'cf-geoplugin')
				));
			}
		}
		
		return $settings;
	}
	
	// Let's change geolocations to woocommerce
	public function woocommerce_get_geolocation( $geolocation, $ip_address ){
		return array(
			'country'  => CFGP_U::api('country_code'),
			'state'    => CFGP_U::api('region'),
			'city'     => CFGP_U::api('city'),
			'postcode' => CFGP_U::api('postcode')
		);
	}
	// Get a hash of the customer location.
	public function woocommerce_geolocation_ajax_get_location_hash( $geolocation ){
		substr( md5( implode( '', array(
			'country'  => CFGP_U::api('country_code'),
			'state'    => CFGP_U::api('region'),
			'city'     => CFGP_U::api('city'),
			'postcode' => CFGP_U::api('postcode')
		) ) ), 0, 12 );
	}
	// Change tax location
	public function woocommerce_get_tax_location( $location, $tax_class = '', $customer = null ){
		
		if ( is_null( $customer ) && WC()->customer ) {
			$customer = WC()->customer;
		}

		if ( !empty( $customer ) && $customer->get_id() ) {
			// Void
		} else {
			$location = array(
				CFGP_U::api('country_code'),
				CFGP_U::api('region'),
				CFGP_U::api('postcode'),
				CFGP_U::api('city')
			);
			
			WC()->customer->set_billing_country(CFGP_U::api('country_code'));
			WC()->customer->set_shipping_country(CFGP_U::api('country_code'));
		}
		
		return $location;
	}
	// Change default customer location
	public function woocommerce_customer_default_location ($default_location) {
		return CFGP_U::api('continent_code') . ':' . CFGP_U::api('country_code');
	}
	
	/**
     * Customer Order information
     */
	public function customer_order_info(){
		$screen = get_current_screen();
		if( isset( $screen->post_type ) && in_array($screen->post_type, ['shop_order']) ){
			$this->add_meta_box(
				CFGP_NAME . '-log',								// Unique ID
				__( 'GEO Location Info', 'cf-geoplugin'),			// Box title
				'geo_location_info__callback',					// Content callback, must be of type callable
				$screen->post_type,								// Post type
				'side',
				'high'
			);
		}
		
		return;
	}
	
	// Customer Order information callback
	public function geo_location_info__callback($post) {
		if($GEO = get_post_meta($post->ID, '_cfgp_location_log', true)):
		$GEO = (object)$GEO;
?>
<p><strong><?php esc_html_e( 'Order IP address:', 'cf-geoplugin'); ?></strong><br><?php
if($flag = CFGP_U::admin_country_flag($GEO->country_code)) {
	echo wp_kses_post($flag ?? '');
} else {
	echo '<span class="cfa cfa-globe"></span>';
}
?>&nbsp;&nbsp;<big><?php echo esc_html($GEO->ip); ?></big></p>
<p><strong><?php esc_html_e( 'Order Timestamp:', 'cf-geoplugin'); ?></strong><br><?php echo esc_html($GEO->timestamp_readable); ?></p>
<p><strong><?php esc_html_e( 'Order Location:', 'cf-geoplugin'); ?></strong><br><?php echo esc_html($GEO->address); ?></p>
<p><strong><?php esc_html_e( 'Timezone:', 'cf-geoplugin'); ?></strong><br><?php echo esc_html($GEO->timezone); ?></p>
<p><strong><?php esc_html_e( 'Customer User Agent:', 'cf-geoplugin'); ?></strong>
	<br><?php esc_html_e( 'Platform:', 'cf-geoplugin'); ?> <?php echo esc_html($GEO->platform); ?>
	<br><?php esc_html_e( 'Browser:', 'cf-geoplugin'); ?> <?php echo esc_html($GEO->browser); ?>
	<br><?php esc_html_e( 'Version:', 'cf-geoplugin'); ?> <?php echo esc_html($GEO->browser_version); ?>
</p>
	<?php
		else :
		$_customer_user_agent = get_post_meta($post->ID, '_customer_user_agent', true);
	?>
<p><strong><?php esc_html_e( 'Order IP address:', 'cf-geoplugin'); ?></strong><br><?php
if($flag = CFGP_U::admin_country_flag(get_post_meta($post->ID, '_billing_country', true))) {
	echo wp_kses_post($flag ?? '');
} else {
	echo '<span class="cfa cfa-globe"></span>';
}
?>&nbsp;&nbsp;<big><?php echo esc_html(get_post_meta($post->ID, '_customer_ip_address', true)); ?></big></p>
<p><strong><?php esc_html_e( 'Order Timestamp:', 'cf-geoplugin'); ?></strong><br><?php echo esc_attr(date('D, j M Y, H:i:s O', strtotime($post->post_date_gmt))); ?></p>
<p><strong><?php esc_html_e( 'Order Location:', 'cf-geoplugin'); ?></strong><br><?php
	$country = get_post_meta($post->ID, '_billing_country', true);
	$location = array(
		get_post_meta($post->ID, '_billing_city', true),
		(WC()->countries->get_states( $country )[get_post_meta($post->ID, '_billing_state', true)] ?? NULL),
		(WC()->countries->countries[$country] ?? NULL) . ' (' . $country . ')'
	);
	$location = array_map('trim', $location);
	$location = array_filter($location);
	echo esc_html( join(', ', $location) );
?></p>
<?php if($_customer_user_agent) : $browser = CFGP_Browser::instance( $_customer_user_agent ); ?>
<p><strong><?php esc_html_e( 'Customer User Agent:', 'cf-geoplugin'); ?></strong>
	<br><?php esc_html_e( 'Platform:', 'cf-geoplugin'); ?> <?php echo esc_html($browser->getPlatform()); ?>
	<br><?php esc_html_e( 'Browser:', 'cf-geoplugin'); ?> <?php echo esc_html($browser->getBrowser()); ?>
	<br><?php esc_html_e( 'Version:', 'cf-geoplugin'); ?> <?php echo esc_html($browser->getVersion()); ?>
</p>
<?php endif; ?>
<hr>
<p class="description"><?php esc_html_e( 'NOTE: This geo location is based on the default WooCommerce algorithm.', 'cf-geoplugin'); ?></p>
	<?php endif;
	}
	
	// Log customer geolocation on order
    public function woocommerce_geolocation_log( $order ) {
		$order->update_meta_data( 
			'_cfgp_location_log',
			apply_filters('cf_geoplugin_woocommerce_checkout_log', CFGP_U::api())
		);
	}
	
	// Adding extra code to footer
	public function admin_footer(){ ?>
	<style>.woocommerce-converted-price{display: block;color: darkorange;}</style>
	<?php }
	
	// Change API options
	public function change_api_run_options($options){
		
		if($this->woocommerce_currency && isset($options['base_currency'])){
			$options['base_currency'] = $this->woocommerce_currency;
		}
		
		return $options;
	}
	
	/* We must recreate wc_price in order to perform conversion wisely */
	function wc_price( $original_formatted_price, $price, $args  ) {
		global $product;
		
		$return = '';
		$SKU = NULL;
		$PID = NULL;

		// We must have float number for all cases
		$price_split = explode($args['decimal_separator'], $price);	
		$price = floatval(preg_replace('/[^0-9]+/','',$price_split[0]) . '.' . (isset($price_split[1]) && !empty($price_split[1]) ? $price_split[1] : '00'));
		
		if(is_object($product))
		{
			if(property_exists($product, 'get_id')) $PID = $product->get_id();
			if(property_exists($product, 'get_sku')) $SKU = $product->get_sku();
		}
		
		$currency_args = $this->get_currency_and_symbol();
		
		$unformatted_price = $price;
		
		// Let's do direct conversion
		if( $currency_args !== false && $this->cf_conversion === 'converted')
		{
			$price = ($price * $currency_args['currency_converter']);
			$args['currency'] = $currency_args['currency_code'];
		}
		
		// We show it in the both conversions
		if( $currency_args !== false && $this->cf_conversion == 'inversion' )
		{
			$converted_price = ($unformatted_price * $currency_args['currency_converter']);
			
			$converted_negative = $converted_price < 0;
			$converted_price = apply_filters( 'cf_geoplugin_raw_woocommerce_converted_price', floatval( $converted_negative ? $converted_price * -1 : $converted_price ) );
			$converted_price = apply_filters( 'cf_geoplugin_formatted_woocommerce_converted_price', number_format( $converted_price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $converted_price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );
			
			if ( apply_filters( 'cf_geoplugin_woocommerce_converted_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
				$converted_price = wc_trim_zeros( $converted_price );
			}
			
			$return .= '<span class="woocommerce-original-price" data-id="' . $PID . '"' . ($SKU ?  ' data-sku="' . $SKU . '"':NULL) . '>';
			$converted_formatted_price = ( $converted_negative ? '-' : '' ) . sprintf( $args['price_format'], '<span class="woocommerce-Price-currencySymbol" data-id="' . $PID . '"' . ($SKU ?  ' data-sku="' . $SKU . '"':NULL) . '>' . get_woocommerce_currency_symbol( $currency_args['currency_code'] ) . '</span>', $converted_price );
			$return .= '<span class="woocommerce-Price-amount amount">' . $converted_formatted_price . '</span>';
			
			if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
				$return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
			$return .= '</span>';
		}
		
		// Original price
		$negative = $price < 0;
		$price = apply_filters( 'cf_geoplugin_raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
		$price = apply_filters( 'cf_geoplugin_formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );
		
		if ( apply_filters( 'cf_geoplugin_woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
			$price = wc_trim_zeros( $price );
		}
		
		$return .= '<span class="woocommerce-' . (( $currency_args !== false && $this->cf_conversion == 'inversion' ) ? 'converted' : 'original') . '-price" data-id="' . $PID . '"' . ($SKU ?  ' data-sku="' . $SKU . '"':NULL) . '>';
		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol( $args['currency'] ) . '</span>', $price );
		$return .= '<span class="woocommerce-Price-amount amount">' . $formatted_price . '</span>';
		
		if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
			$return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}
		$return .= '</span>';
		
		// We show it in the both conversions
		if( $currency_args !== false && $this->cf_conversion == 'both' )
		{
			$converted_price = ($unformatted_price * $currency_args['currency_converter']);
			
			$converted_negative = $converted_price < 0;
			$converted_price = apply_filters( 'cf_geoplugin_raw_woocommerce_converted_price', floatval( $converted_negative ? $converted_price * -1 : $converted_price ) );
			$converted_price = apply_filters( 'cf_geoplugin_formatted_woocommerce_converted_price', number_format( $converted_price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $converted_price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );
			
			if ( apply_filters( 'cf_geoplugin_woocommerce_converted_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
				$converted_price = wc_trim_zeros( $converted_price );
			}
			
			$return .= '<span class="woocommerce-converted-price" data-id="' . $PID . '"' . ($SKU ?  ' data-sku="' . $SKU . '"' : NULL) . '>';
			$converted_formatted_price = ( $converted_negative ? '-' : '' ) . sprintf( $args['price_format'], '<span class="woocommerce-Price-currencySymbol" data-id="' . $PID . '"' . ($SKU ?  ' data-sku="' . $SKU . '"':NULL) . '>' . get_woocommerce_currency_symbol( $currency_args['currency_code'] ) . '</span>', $converted_price );
			$return .= '<span class="woocommerce-Price-amount amount">' . $converted_formatted_price . '</span>';
			
			if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
				$return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
			$return .= '</span>';
		}
		
		/**
		 * Filters the string of price markup.
		 */
		return apply_filters(
			'cf_geoplugin_woocommerce_formatted_price',
			$return,
			$price,
			$args,
			$currency_args
		);
	}
	
	// Calculate % increment
	public function calculate_conversions($currency_args){
		if($this->cf_conversion_adjust && is_numeric($this->cf_conversion_adjust) && intval($this->cf_conversion_adjust) == $this->cf_conversion_adjust && $this->cf_conversion_adjust > 0)
		{
			$this->cf_conversion_adjust = ( $this->cf_conversion_adjust >= 100 ? 100 : intval($this->cf_conversion_adjust) );
			$percentage = (($this->cf_conversion_adjust / 100) * $currency_args['currency_converter']);
			$currency_args['currency_converter'] = ($currency_args['currency_converter'] + $percentage);
		}
		
		return $currency_args;
	}
	
	// Modify raw price
	public function calculate_and_modify_price($price){
		
		if( $price && 'yes' === get_option( 'woocommerce_cf_geoplugin_conversion_rounded', 'no') )
		{
			switch( get_option( 'woocommerce_cf_geoplugin_conversion_rounded_option', 'up') )
			{
				default:
				case 'up':
					$price = ceil($price);
					break;
				case 'nearest':
					$price = round($price);
					break;
				case 'down':
					$price = floor($price);
					break;
			}
		}
		
		return $price;
	}

    // Add custom option to general woocommerce options
    public function conversion_options( $settings )
    {
        $key = 0;

        foreach( $settings as $values )
        {
            $new_settings[$key] = $values;
            $key++;

            if( $values['id'] === 'woocommerce_default_customer_address' )
            {
				$new_settings[$key] = array(
                    'title'    => __( 'GEO location info log', 'cf-geoplugin'),
                    'desc'     => __( 'Activate your customer\'s geo location log.', 'cf-geoplugin'),
                    'class'    => 'wc-enhanced-checkbox',
                    'id'       => 'woocommerce_cf_geoplugin_save_checkout_location',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'By activating this option, you enable the geolocation of your customers to be anonymously logged and displayed within the orders.', 'cf-geoplugin')
                );
				$key++;
			}
			else if( $values['id'] === 'woocommerce_currency_pos' )
            {
                $new_settings[$key] = array(
                    'title'    => __( 'Currency conversion options', 'cf-geoplugin'),
                    'desc'     => __( 'This controls Geo Controller conversion system', 'cf-geoplugin'),
                    'css'      => 'min-width:350px',
                    'class'    => 'wc-enhanced-select',
                    'id'       => 'woocommerce_cf_geoplugin_conversion',
                    'default'  => 'original',
                    'type'     => 'select',
                    'options'  => array(
                        'original'  => __( 'Show original price only', 'cf-geoplugin'),
                        'converted' => __( 'Show converted price only', 'cf-geoplugin'),
                        'both'      => __( 'Show original and converted price', 'cf-geoplugin'),
                        'inversion' => __( 'Show converted and original price', 'cf-geoplugin')
                    ),
                    'desc_tip' => true,
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Convert to round price', 'cf-geoplugin'),
                    'desc'     => __( 'Force all converted prices to be round number.', 'cf-geoplugin'),
                    'class'    => 'wc-enhanced-checkbox',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_rounded',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'These option is added by the Geo Controller.', 'cf-geoplugin')
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Round price option', 'cf-geoplugin'),
                    'desc'     => __( 'Set the round price to the desired increase.', 'cf-geoplugin'),
                    'css'      => 'min-width:150px; max-width:200px;',
                    'class'    => 'wc-enhanced-select',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_rounded_option',
                    'default'  => 'up',
                    'type'     => 'select',
                    'options'  => array(
                        'up'		=> __( 'Round up', 'cf-geoplugin'),
                        'neares'	=> __( 'Round nearest', 'cf-geoplugin'),
                        'down'		=> __( 'Round down', 'cf-geoplugin')
                    ),
                    'desc_tip' => __( 'These option is added by the Geo Controller.', 'cf-geoplugin')
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Currency converter adjust', 'cf-geoplugin'),
                    'desc'     => __( 'Put the number in percent (%) to regulate the converted price. (This increase price by ##%)', 'cf-geoplugin'),
                    'css'      => 'min-width:50px; max-width:80px;',
                    'class'    => 'wc-enhanced-text',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_adjust',
                    'default'  => 0,
                    'type'     => 'number',
					'custom_attributes' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
						'autocomplete' => 'off'
					),
                    'desc_tip' => __( 'These option is added by the Geo Controller.', 'cf-geoplugin')
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Not convert in wp-admin', 'cf-geoplugin'),
                    'desc'     => __( 'Remove conversion price from admin panel and show only original prices.', 'cf-geoplugin'),
                    'class'    => 'wc-enhanced-checkbox',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_in_admin',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'These option is added by the Geo Controller.', 'cf-geoplugin')
                );
                $key++;
            }
        }

        return $new_settings;
    }

    // Show shipping price/s
    public function show_shipping_price()
    {
		$WC = WC();
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            echo '<div class="woocommerce-original-price">';
            $all_rates = $WC->session->get('shipping_for_package_0');
			$all_rates = $all_rates['rates'];
			foreach($all_rates  as $method_id => $rate ){
				$chosen_shipping_methods = $WC->session->get('chosen_shipping_methods');
                if( $chosen_shipping_methods[0] == $method_id )
                {
                    $rate_label = $rate->label; // The shipping method label name
                    $rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
                    // The taxes cost
                    $rate_taxes = 0;
                    foreach ($rate->taxes as $rate_tax)
                        $rate_taxes += floatval($rate_tax);
                    // The cost including tax
                    $rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;
                    echo wp_kses_post($rate_label ?? '') . ': ' . wc_price(
						apply_filters(
							'cf_geoplugin_woocommerce_show_shipping_price',
							($rate_cost_incl_tax * $currency_args['currency_converter']),
							$rate_cost_incl_tax,
							$currency_args['currency_converter'],
							$currency_args['currency_code']
						),
						array( 'currency' => $currency_args['currency_code'] )
					);
                    break;
                }
            }
            echo '</div>';
        }
    }

    // Returns array of currency code ( 3 letters ) and converted rate
    public function get_currency_and_symbol()
    {
        $return_value = [];

        $currency_code =  (CFGP_U::api('currency') ? strtoupper( (string)CFGP_U::api('currency') ) : '' );
        $currency_converted = ( (float)CFGP_U::api('currency_converter') > 0 ? (float)CFGP_U::api('currency_converter') : 1 );
        if( !empty( $currency_code )  && !empty( $currency_converted ) && $currency_code !== get_woocommerce_currency() )
        {
            $return_value['currency_code'] = $currency_code;
            $return_value['currency_converter'] = $currency_converted;
            return apply_filters('cf_geoplugin_woocommerce_currency_and_symbol', $return_value, get_woocommerce_currency());
        }
        return false;
    }

    // Show our settings tabs
    public function cfgp_woocommerce_tabs( $settings_tabs )
    {
		$new_tab = array('cf_geoplugin_payment_restriction' => __('Payments Control', 'cf-geoplugin'));
		
		// Find "Payments" tab possition
		$payments_position = array_search('checkout', array_keys($settings_tabs)) + 1;

		// Add new tab after "Payments" tab
		$settings_tabs = array_slice($settings_tabs, 0, $payments_position, true) +
						 $new_tab +
						 array_slice($settings_tabs, $payments_position, null, true);
       
        return $settings_tabs;
    }

    // Show options for payment tab
    public function cfgp_woocommerce_payment_settings() {
        woocommerce_admin_fields( $this->get_payment_settings() );
    }
	
	// Save payment settings (only when plugin is active)
    public function cfgp_woocommerce_payment_settings_save() {
        woocommerce_update_options( $this->get_payment_settings() );
    }

    // Generate option fields for payment
    public function get_payment_settings()
    {
        global $wp_version;
        $WC = WC();

        $settings = [];

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $enabled_gateways = [];

        if( !empty( $gateways ) && is_array( $gateways ) ) 
        {
            foreach( $gateways as $i => $gateway ) 
            {
                if( $gateway->enabled == 'yes' ) 
                {
                    $enabled_gateways[] = $gateway;
                }
            }
        }

        if( !empty( $enabled_gateways ) && is_array( $enabled_gateways ) )
        {
			$all_countries = CFGP_Library::get_countries();
            
            if( !empty( $all_countries ) && is_array( $all_countries ) )
            {
                $countries_options = [];
                foreach( $all_countries as $country_code => $country_name )
                {
                    $countries_options[ $country_code ] = sprintf( '%s - %s', $country_code, $country_name );
                }

                $custom_attributes = [];

                if( CFGP_License::level() < 2 && !CFGP_License::activated() )
                {
                    $custom_attributes['disabled'] = true;
                }
				
				if( isset($custom_attributes['disabled']) && CFGP_U::dev_mode() ){
					unset($custom_attributes['disabled']);
				}
                
                $settings[] = array( 'name' => __( 'Geo Controller Payments Control', 'cf-geoplugin'), 'type' => 'title', 'desc' => __( 'Configure payment methods for each country in a detailed and precise manner. You can show or hide specific payment methods based on the country, allowing you to prevent unwanted transactions and ensure that only appropriate payment options are available to your customers in each region.', 'cf-geoplugin') . (
				(isset($custom_attributes['disabled']) && $custom_attributes['disabled']) || CFGP_U::dev_mode()
				? ' <br><span style="color:#dc3545;">' . sprintf(__('This option is only available with the licensed version of the %s. A license valid for at least one year is required to enable this feature.', 'cf-geoplugin'), '<a href="' . CFGP_U::admin_url('admin.php?page=cf-geoplugin-activate') . '">Geo Controller</a>') . '</span>'
				: ''
				) . '<hr>', 'id' => 'cf_geoplugin_payment_restriction' );
				$count = count($enabled_gateways); $x = 0;
                foreach( $enabled_gateways as $i => $gateway )
                {
					++$x;
					$hr_id = sprintf( '%s_hr', $gateway->id );
                    $select_setting_id = sprintf( '%s_select', $gateway->id );
					$checkbox_setting_id = sprintf( '%s_checkbox', $gateway->id );
                    $method_setting_id = sprintf( '%s', $gateway->id );
					
                    $settings[ $method_setting_id ] = array(
                        'name'     => __( 'Choose desired method', 'cf-geoplugin'),
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s', $method_setting_id ),
                        'type'     => 'select',
                        'class'    => 'wc-enhanced-select',
                        'default'  => 'cfgp_payment_woo',
                        'css'      => 'min-width:400px;',
                        'options'  => array(
                            'cfgp_payment_woo'      => __( 'Woocommerce Default', 'cf-geoplugin'),
                            'cfgp_payment_enable'   => __( 'Enable only in selected countries', 'cf-geoplugin'),
                            'cfgp_payment_disable'  => __( 'Disable only for selected countries', 'cf-geoplugin'),
                        ),
                        'custom_attributes' => $custom_attributes,
                    );

                    $settings[ $select_setting_id ] = array(
                        'name'     => __( 'Select countries', 'cf-geoplugin'),
                        'class'    => 'wc-enhanced-select',
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s', $select_setting_id ),
                        'default'  => '',
                        'css'      => 'min-width:400px;',
                        'type'     => 'multiselect',
                        'options'  => $countries_options,
                        'custom_attributes' => $custom_attributes,
                    );
/*
					$settings[ $checkbox_setting_id ] = array(
                        'name'     => __( 'Disable currency conversion for this method', 'cf-geoplugin'),
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s_disable_currency', $method_setting_id ),
                        'type'     => 'checkbox',
                        'class'    => 'wc-enhanced-checkbox',
                        'default'  => '',
                        'custom_attributes' => $custom_attributes,
                    );
*/				
					$settings[ $hr_id ] = array(
						'name' => sprintf( '%s', esc_html( $gateway->method_title ) ),
						'type' => 'title',
						'desc' => '',
						'id' => 'cf_geoplugin_payment_restriction'
					);
					
					if($count != $x) $settings[ $hr_id.'_end' ] = array(
						'name' => '',
						'type' => 'title',
						'desc' => '<hr>',
						'id' => 'cf_geoplugin_payment_restriction'
					);

                }
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );

                return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
            }
            else 
            {
                $settings[] = array( 'name' => __( 'Geo Controller Payments Control', 'cf-geoplugin'), 'type' => 'title', 'desc' => '<b>' . __( 'Currently we are not able to show desired options. Please try again later.', 'cf-geoplugin') . '</b>', 'id' => 'cf_geoplugin_payment_restriction' );
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
                return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
            }
        }
        else 
        {
            $settings[] = array( 'name' => __( 'Geo Controller Payments Control', 'cf-geoplugin'), 'type' => 'title', 'desc' => '<b>' . __( 'No enabled woocommerce payments yet.', 'cf-geoplugin'), 'id' => 'cf_geoplugin_payment_restriction' . '</b>' );
            $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
            return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
        }
    }

    // Disable payments for specific users
    public function cfgp_woocommerce_payment_disable( $gateways )
    {
		$original_gateways = $gateways;
		
		// If plugin is not available return original
		if( !CFGP_U::api('country_code', NULL) ) {
			return $gateways;
		}

		// Very important check othervise will delete from settings and throw fatal error
        if( is_admin() ) {
			return $gateways;
		}
		
		// $tax_based_on = get_option( 'woocommerce_tax_based_on' );
		
		if( !is_user_logged_in() ) {
			$current_country = sanitize_text_field( $_POST['s_country'] ?? $_POST['billing_country'] ?? CFGP_U::api('country_code', NULL) ?? WC()->countries->get_base_country() );
		} else if( isset(WC()->customer) ) {
			if( !( $current_country = sanitize_text_field( $_POST['s_country'] ?? $_POST['billing_country'] ?? WC()->customer->get_billing_country() ) ) ) {
				$current_country = CFGP_U::api('country_code', NULL) ?? WC()->countries->get_base_country();
			}
		} else {
			$current_country = CFGP_U::api('country_code', NULL) ?? WC()->countries->get_base_country();
		}
		
		$current_country = strtolower($current_country);
		
        if( is_array( $gateways ) )
        {
            foreach( $gateways as $gateway )
            {
                $type = get_option( sprintf( 'woocommerce_cfgp_method_%s', $gateway->id ) );
                $countries = get_option( sprintf( 'woocommerce_cfgp_method_%s_select', $gateway->id ) );

                if( empty( $countries ) || $type == 'cfgp_payment_woo' ) continue;

                if( 
					$type === 'cfgp_payment_disable' 
					&& in_array(
						$current_country,
						$countries
					)
				)
                {
                    if( isset( $gateways[ $gateway->id ] ) ) {
						unset( $gateways[ $gateway->id ] );
					}
                }
                elseif(
					$type === 'cfgp_payment_enable'
					&& !in_array(
						$current_country,
						$countries
					) 
				) 
                {
                    if( isset( $gateways[ $gateway->id ] ) ) {
						unset( $gateways[ $gateway->id ] );
					}
                }
            }
        }
		
        return apply_filters(
			'cf_geoplugin_woocommerce_payment_disable',
			$gateways,
			$current_country,
			$original_gateways,
			CFGP_U::api(false, CFGP_Defaults::API_RETURN)
		); 
    }
	
	// Control of the some additional Woocommerce addons
	public function wp_footer () {
		
		$gateways = apply_filters( 'cf_geoplugin_woocommerce_disable_cart_buttons', [
			'ppcp-gateway' => '.wc-proceed-to-checkout #ppc-button, #ppc-button'
		]);

		$css = [];

		foreach($gateways as $gateway => $style) {
			
			$type = get_option( sprintf( 'woocommerce_cfgp_method_%s', $gateway ) );
			$countries = get_option( sprintf( 'woocommerce_cfgp_method_%s_select', $gateway ) );

			if( empty( $countries ) || $type == 'cfgp_payment_woo' ) continue;
			
			if( $type === 'cfgp_payment_disable' && CFGP_U::check_user_by_country( $countries ) )
			{
				$css[]=$style;
			}
			elseif( $type === 'cfgp_payment_enable' && !CFGP_U::check_user_by_country( $countries ) )
			{
				$css[]=$style;
			}
		}
		
		if( !empty($css) ) : $css = join(', ', $css); ?>
<style id="cfgp-woocommerce-disable-payment-gateway-css" media="all">
/* <![CDATA[ */
<?php echo esc_html($css); ?>{display:none !important;}
/* ]]> */
</style>
<script id="cfgp-woocommerce-disable-payment-gateway-js" type="text/javascript">
/* <![CDATA[ */
(function(jCFGP){if(jCFGP){jCFGP(document).ready(function(){jCFGP(<?php printf('"%s"', esc_html($css)); ?>).remove();});}}(jQuery||window.jQuery));
/* ]]> */
</script>
	<?php endif;
	}
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;