<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * WooCommerce integration
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__woocommerce' ) ):
class CFGP__Plugin__woocommerce extends CFGP_Global
{	
    /**
     * CF Geo Plugin converter option
     */
    private $cf_conversion					= 'original';
	private $cf_conversion_rounded 			= 0;
	private $cf_conversion_rounded_option	= 'up';
	private $cf_conversion_adjust 			= 0;
	private $cf_conversion_in_admin 		= 'yes';
	private $woocommerce_currency;

    private function __construct()
    {		
        $this->add_action( 'init', 'check_woocommerce_instalation', 10 );
		
		$this->cf_conversion 				= get_option( 'woocommerce_cf_geoplugin_conversion', 'original');
		$this->cf_conversion_rounded 		= get_option( 'woocommerce_cf_geoplugin_conversion_rounded', 0);
		$this->cf_conversion_rounded_option	= get_option( 'woocommerce_cf_geoplugin_conversion_rounded_option', 'up');
		$this->cf_conversion_adjust 		= get_option( 'woocommerce_cf_geoplugin_conversion_adjust', 0);
		$this->cf_conversion_in_admin 		= get_option( 'woocommerce_cf_geoplugin_conversion_in_admin', 'yes' );
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
    }
	
	public function admin_footer(){ ?>
	<style>.woocommerce-converted-price{display: block;color: darkorange;}</style>
	<?php }
	
	public function change_api_run_options($options){
		
		if($this->woocommerce_currency && isset($options['base_currency'])){
			$options['base_currency'] = $this->woocommerce_currency;
		}
		
		return $options;
	}

    // Check if woocommerce is installed and active
    public function check_woocommerce_instalation()
    {
		if( CFGP_U::api('currency_converter') > 0 )
		{
			if($this->cf_conversion != 'original')
			{
				// All prices conversion
				if($this->cf_conversion_in_admin == 'yes') {
					if( !is_admin() ) $this->add_filter( 'wc_price', 'wc_price', 99, 3 );
				} else {
					$this->add_filter( 'wc_price', 'wc_price', 99, 3 );
				}
			}
			
			// Add custom option for conversion system
			$this->add_filter( 'woocommerce_general_settings', 'conversion_options', 10 );
		}
		
		//  Add a settings tabs
		$this->add_filter( 'woocommerce_settings_tabs_array', 'cfgp_woocommerce_tabs', 50 );
		// Add payment settings
		$this->add_action( 'woocommerce_settings_tabs_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings' );
		
		// Add settings
		 if( CFGP_License::level() >= 2 || (defined('CFGP_DEV_MODE') && CFGP_DEV_MODE))
		{
			// Save our settings for payments
			$this->add_action( 'woocommerce_update_options_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings_save' );
			// Disable payment gateways for specifis users
			$this->add_filter( 'woocommerce_available_payment_gateways', 'cfgp_woocommerce_payment_disable' );
		}

		$this->add_filter('cf_geoplugin_woocommerce_currency_and_symbol', 'calculate_conversions', 1);
		
		$this->add_filter('cf_geoplugin_raw_woocommerce_converted_price', 'calculate_and_modify_price', 1);
		$this->add_filter('cf_geoplugin_raw_woocommerce_price', 'calculate_and_modify_price', 1);
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
		
		if( $price && $this->cf_conversion_rounded == 'yes' )
		{
			switch($this->cf_conversion_rounded_option)
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

            if( $values['id'] == 'woocommerce_currency_pos' )
            {
                $new_settings[$key] = array(
                    'title'    => __( 'Currency conversion options', CFGP_NAME ),
                    'desc'     => __( 'This controls WordPress Geo Plugin conversion system', CFGP_NAME ),
                    'css'      => 'min-width:350px',
                    'class'    => 'wc-enhanced-select',
                    'id'       => 'woocommerce_cf_geoplugin_conversion',
                    'default'  => 'original',
                    'type'     => 'select',
                    'options'  => array(
                        'original'  => __( 'Show original price only', CFGP_NAME ),
                        'converted' => __( 'Show converted price only', CFGP_NAME ),
                        'both'      => __( 'Show original and converted price', CFGP_NAME ),
                        'inversion' => __( 'Show converted and original price', CFGP_NAME )
                    ),
                    'desc_tip' => true,
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Convert to round price', CFGP_NAME ),
                    'desc'     => __( 'Force all converted prices to be round number.', CFGP_NAME ),
                    'class'    => 'wc-enhanced-checkbox',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_rounded',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'These option is added by the WordPress Geo Plugin.', CFGP_NAME )
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Round price option', CFGP_NAME ),
                    'desc'     => __( 'Set the round price to the desired increase.', CFGP_NAME ),
                    'css'      => 'min-width:150px; max-width:200px;',
                    'class'    => 'wc-enhanced-select',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_rounded_option',
                    'default'  => 'up',
                    'type'     => 'select',
                    'options'  => array(
                        'up'		=> __( 'Round up', CFGP_NAME ),
                        'neares'	=> __( 'Round nearest', CFGP_NAME ),
                        'down'		=> __( 'Round down', CFGP_NAME )
                    ),
                    'desc_tip' => __( 'These option is added by the WordPress Geo Plugin.', CFGP_NAME )
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Currency converter adjust', CFGP_NAME ),
                    'desc'     => __( 'Put the number in percent (%) to regulate the converted price. (This increase price by ##%)', CFGP_NAME ),
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
                    'desc_tip' => __( 'These option is added by the WordPress Geo Plugin.', CFGP_NAME )
                );
                $key++;
				$new_settings[$key] = array(
                    'title'    => __( 'Not convert in wp-admin', CFGP_NAME ),
                    'desc'     => __( 'Remove conversion price from admin panel and show only original prices.', CFGP_NAME ),
                    'class'    => 'wc-enhanced-checkbox',
                    'id'       => 'woocommerce_cf_geoplugin_conversion_in_admin',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'These option is added by the WordPress Geo Plugin.', CFGP_NAME )
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
                    echo $rate_label . ': ' . wc_price(
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
        $return_value = array();

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
        $settings_tabs['cf_geoplugin_payment_restriction'] = __( 'Payments Control', CFGP_NAME );
       
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

        $settings = array();

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $enabled_gateways = array();

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
                $countries_options = array();
                foreach( $all_countries as $country_code => $country_name )
                {
                    $countries_options[ $country_code ] = sprintf( '%s - %s', $country_code, $country_name );
                }


                $custom_attributes = array();
                if( CFGP_License::level() < 2 && CFGP_License::activated() )
                {
                    $custom_attributes['disabled'] = true;
                }
				
				if(defined('CFGP_DEV_MODE') && CFGP_DEV_MODE && isset($custom_attributes['disabled'])){
					unset($custom_attributes['disabled']);
				}
                
                $settings[] = array( 'name' => __( 'CF Geo Plugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => __( 'Configure payment methods for each country. Show or hide payment methods by country to prevent unwanted transactions.', CFGP_NAME ) . (
				(isset($custom_attributes['disabled']) && $custom_attributes['disabled']) || (defined('CFGP_DEV_MODE') && CFGP_DEV_MODE)
				? ' <br><span style="color:#dc3545;">' . sprintf(__('This option is only enabled with the licensed version of the %s. You must use 1 year license or above.', CFGP_NAME), '<a href="' . CFGP_U::admin_url('admin.php?page=cf-geoplugin-activate') . '">WordPress Geo Plugin</a>') . '</span>'
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
                        'name'     => __( 'Choose desired method', CFGP_NAME ),
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s', $method_setting_id ),
                        'type'     => 'select',
                        'class'    => 'wc-enhanced-select',
                        'default'  => 'cfgp_payment_woo',
                        'css'      => 'min-width:400px;',
                        'options'  => array(
                            'cfgp_payment_woo'      => __( 'Woocommerce Default', CFGP_NAME ),
                            'cfgp_payment_enable'   => __( 'Enable only in selected countries', CFGP_NAME ),
                            'cfgp_payment_disable'  => __( 'Disable only for selected countries', CFGP_NAME ),
                        ),
                        'custom_attributes' => $custom_attributes,
                    );

                    $settings[ $select_setting_id ] = array(
                        'name'     => __( 'Select countries', CFGP_NAME ),
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
                        'name'     => __( 'Disable currency conversion for this method', CFGP_NAME ),
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s_disable_currency', $method_setting_id ),
                        'type'     => 'checkbox',
                        'class'    => 'wc-enhanced-checkbox',
                        'default'  => '',
                        'custom_attributes' => $custom_attributes,
                    );
*/				
					$settings[ $hr_id ] = array( 'name' => sprintf( '%s', esc_html( $gateway->method_title ) ), 'type' => 'title', 'desc' => '', 'id' => 'cf_geoplugin_payment_restriction' );
					if($count != $x) $settings[ $hr_id.'_end' ] = array( 'name' => '', 'type' => 'title', 'desc' => '<hr>', 'id' => 'cf_geoplugin_payment_restriction' );

                }
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );

                return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
            }
            else 
            {
                $settings[] = array( 'name' => __( 'CF Geo Plugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => '<b>' . __( 'Currently we are not able to show desired options. Please try again later.', CFGP_NAME ) . '</b>', 'id' => 'cf_geoplugin_payment_restriction' );
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
                return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
            }
        }
        else 
        {
            $settings[] = array( 'name' => __( 'CF Geo Plugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => '<b>' . __( 'No enabled woocommerce payments yet.', CFGP_NAME ), 'id' => 'cf_geoplugin_payment_restriction' . '</b>' );
            $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
            return apply_filters( 'cf_geoplugin_payment_restriction_settings', $settings );
        }
    }

    // Disable payments for specific users
    public function cfgp_woocommerce_payment_disable( $gateways )
    {
		$original_gateways = $gateways;
        
		if( !CFGP_U::api('country_code', NULL) || !CFGP_U::api('country', NULL) ) return $gateways;
        
        if( is_admin() ) return $gateways; // Very important check othervise will delete from settings and throw fatal error

        if( is_array( $gateways ) )
        {
            foreach( $gateways as $gateway )
            {
                $type = get_option( sprintf( 'woocommerce_cfgp_method_%s', $gateway->id ) );
                $countries = get_option( sprintf( 'woocommerce_cfgp_method_%s_select', $gateway->id ) );

                if( empty( $countries ) || $type == 'cfgp_payment_woo' ) continue;

                if( $type === 'cfgp_payment_disable' && CFGP_U::check_user_by_country( $countries ) ) 
                {
                    if( isset( $gateways[ $gateway->id ] ) ) unset( $gateways[ $gateway->id ] );
                }
                elseif( $type === 'cfgp_payment_enable' && !CFGP_U::check_user_by_country( $countries ) ) 
                {
                    if( isset( $gateways[ $gateway->id ] ) ) unset( $gateways[ $gateway->id ] );
                }
            }
        }
        return apply_filters( 'cf_geoplugin_woocommerce_payment_disable', $gateways, $original_gateways, CFGP_U::api(false, CFGP_Defaults::API_RETURN) ); 
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
<?php echo $css; ?>{display:none !important;}
/* ]]> */
</style>
<script id="cfgp-woocommerce-disable-payment-gateway-js" type="text/javascript">
/* <![CDATA[ */
(function(jCFGP){if(jCFGP){jCFGP(document).ready(function(){jCFGP(<?php printf('"%s"', $css); ?>).remove();});}}(jQuery||window.jQuery));
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