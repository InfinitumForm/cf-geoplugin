<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * WooCommerce integration
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( !class_exists( 'CF_Geoplugin_Woocommerce' ) ):
class CF_Geoplugin_Woocommerce extends CF_Geoplugin_Global
{
    /**
     * CF GeoPlugin converter option
     */
    private $cf_conversion = 'original';

    function __construct()
    {
        $this->add_action( 'plugins_loaded', 'check_woocommerce_instalation' );
        $this->cf_conversion = get_option( 'woocommerce_cf_geoplugin_conversion' );
    }

    // Check if woocommerce is installed and active
    public function check_woocommerce_instalation()
    {
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'] ; $CFGEO = $GLOBALS['CFGEO'];
        if( class_exists( 'WooCommerce' ) )
        {
            $this->update_option( 'woocommerce_active', 1 );
            
            if( isset( $CF_GEOPLUGIN_OPTIONS['enable_woocommerce'] ) && $CF_GEOPLUGIN_OPTIONS['enable_woocommerce'] == 1 )
            {
                if( isset( $CFGEO['currency_converter'] ) && $CFGEO['currency_converter'] > 0 )
                {
                    $this->update_option( 'base_currency', get_woocommerce_currency() );
                    
                    $this->add_filter( 'woocommerce_get_price_html', 'convert_item_price', 10, 2 ); // Item price on admin side
                    $this->add_filter( 'woocommerce_cart_item_price', 'convert_cart_item_price', 10, 3 ); // Cart Item price
                    $this->add_filter( 'woocommerce_cart_item_subtotal', 'convert_cart_item_subtotal_price', 10, 3 ); // Subtotal Item Price
                    $this->add_filter( 'woocommerce_cart_subtotal', 'convert_cart_subtotal_price', 10, 3 ); // Subtotal Cart Price
                    $this->add_filter( 'woocommerce_cart_total', 'convert_cart_total_price', 10 ); // Total Price
                    //$this->add_filter( 'woocommerce_package_rates', 'convert_shipping_price', 10, 2 ); // Shipping Price 
                    $this->add_filter( 'woocommerce_before_shipping_calculator', 'show_shipping_price', 10 ); // Show Converted Shipping Price
                    $this->add_filter( 'woocommerce_cart_totals_coupon_html', 'convert_coupon_price', 10, 3 ); // Coupon Price
                    $this->add_filter( 'woocommerce_cart_totals_taxes_total_html', 'convert_cart_total_tax', 10 ); // Cart Total Tax Price

                    $this->add_filter( 'woocommerce_general_settings', 'conversion_options', 10 ); // Add custom option for conversion system
                }
                $this->add_filter( 'woocommerce_settings_tabs_array', 'cfgp_woocommerce_tabs', 50 ); //  Add a settings tabs

                $this->add_action( 'woocommerce_settings_tabs_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings' ); // Add payment settings
                
                if( self::access_level( $CF_GEOPLUGIN_OPTIONS['license_sku'] ) >= 2 )
                {
                    $this->add_action( 'woocommerce_update_options_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings_save' ); // Save our settings for payments
                    $this->add_filter( 'woocommerce_available_payment_gateways', 'cfgp_woocommerce_payment_disable' ); // Disable payment gateways for specifis users
                }
            }
        }
        else
        {
            $this->update_option( 'woocommerce_active', 0 );
        }
    }
	

    // Convert price and currency symbol for items
    public function convert_item_price( $price, $product )
    {
        $currency_args = $this->get_currency_and_symbol();
        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $sale_price = '';
            $regular_price = wc_price( $product->get_regular_price() * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            
			if($sp = $product->get_sale_price()){
				$regular_price = '<del>' . $regular_price . '</del>';
            	$sale_price = '<ins>' . wc_price( $sp * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] )  ) . '</ins>';
			}
            if( is_admin() ) 
            {
                if( $this->cf_conversion == 'both' ) return $price . '<hr>' .  $regular_price . '<br>' . $sale_price;
                elseif( $this->cf_conversion == 'inversion' ) return $regular_price . '<br>' . $sale_price . '<hr>' . $price;
                else return $regular_price . '<br>' . $sale_price;
            }
            
            if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . "\n\r" . $sale_price .'</div>';
            elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $regular_price . "\n\r" . $sale_price . '</div><div class="woocommerce-converted-price">' . $price .'</div>';
            else return '<div class="woocommerce-original-price">' . $regular_price . "\n\r" . $sale_price.'</div>';
        }
        return $price;
    }

    // Convert price and currency symbol for items in cart
    public function convert_cart_item_price( $price, $cart_item, $cart_item_key )
    {
        $CFGEO = $GLOBALS['CFGEO'];

        $currency_args = $this->get_currency_and_symbol();
        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            if($sale_price = $cart_item['data']->get_sale_price() )
            {
                // Return raw price. In data is set WC_Cart object.
                $sale_price = wc_price( $sale_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
                if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $sale_price . '</div>';
                elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $sale_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
                else return '<div class="woocommerce-original-price">' . $sale_price . '</div>';
            } 
            else
            {
                $regular_price = wc_price( $cart_item['data']->get_regular_price() * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
                if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . '</div>';
                elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $regular_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
                else return '<div class="woocommerce-original-price">' . $regular_price . '</div>';
            }
        }
        return $price;
    }

    // Convert price and currency symbol for subtotal item price
    public function convert_cart_item_subtotal_price( $price, $cart_item, $cart_item_key )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $cart_item['quantity'] > 0 && $this->cf_conversion !== 'original' )
        {
            if($sale_price = $cart_item['data']->get_sale_price() ) //In data is stored WC_Product_Simple object.
            {
                $sale_price = wc_price( $sale_price * $currency_args['currency_converter'] * $cart_item['quantity'] , array( 'currency' => $currency_args['currency_code'] ) );
                if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $sale_price . '</div>';
                elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $sale_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
                else return '<div class="woocommerce-original-price">' . $sale_price . '</div>';
            } 
            else
            {
                $regular_price = wc_price( $cart_item['data']->get_regular_price() * $currency_args['currency_converter'] * $cart_item['quantity'], array( 'currency' => $currency_args['currency_code'] ) );
                if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . '</div>';
                elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $regular_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
                else return '<div class="woocommerce-original-price">' . $regular_price . '</div>';
            }
        }
        return $price;
    }

    // Convert currency and symbol for subtotal cart price
    public function convert_cart_subtotal_price( $price, $compound, $instance )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $subtotal_price = $instance->get_subtotal(); // WC_Cart class
            $subtotal_price = wc_price( $subtotal_price * $currency_args['currency_converter'] , array( 'currency' => $currency_args['currency_code'] ) );
            if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $subtotal_price . '</div>';
            elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $subtotal_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
            else return '<div class="woocommerce-original-price">' . $subtotal_price . '</div>';
        }
        return $price;
    }

    // Convert currency and symbol for total cart price
    public function convert_cart_total_price( $price )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $total_price = WC()->cart->total;
            $total_price = wc_price( $total_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $total_price . '</div>';
            elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $total_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
            else return '<div class="woocommerce-original-price">' . $total_price . '</div>';
        }
        return $price;
    }

    // Conver currency and symbol for shipping price
    public function convert_shipping_price( $rates, $package )
    {
        return $rates;
    }

    // Converts currency and symbol for coupon price
    public function convert_coupon_price(  $coupon_html, $coupon, $discount_amount_html )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $price = $coupon->get_amount();
            $coupon_price = wc_price( $price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );

            if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $discount_amount_html . '</div><div class="woocommerce-converted-price">' . '-' . $coupon_price . '</div><a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
            elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $coupon_price . '</div><div class="woocommerce-converted-price">' . '-' . $discount_amount_html . '</div><a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
            else return '<div class="woocommerce-original-price">' . '-' . $coupon_price . '</div><a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
            // This is WC way of generating coupon html => http://woocommerce.wp-a2z.org/oik_api/wc_cart_totals_coupon_html/
        }
        
        return $coupon_html;
    }

    // Converts currency and symbol for cart total tax
    public function convert_cart_total_tax( $price )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $tax_price = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ) ) - WC()->cart->get_total_ex_tax();
            $tax_price = wc_price( $tax_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            if( $this->cf_conversion == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $tax_price . '</div>';
            elseif( $this->cf_conversion == 'inversion' ) return '<div class="woocommerce-original-price">' . $tax_price . '</div><div class="woocommerce-converted-price">' . $price . '</div>';
            else return '<div class="woocommerce-original-price">' . $tax_price . '</div>';
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
                    'desc'     => __( 'This controls CF GeoPlugin conversion system', CFGP_NAME ),
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
            $all_rates = $WC->session->get('shipping_for_package_0')['rates'];
			foreach($all_rates  as $method_id => $rate ){
                if( $WC->session->get('chosen_shipping_methods')[0] == $method_id )
                {
                    $rate_label = $rate->label; // The shipping method label name
                    $rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
                    // The taxes cost
                    $rate_taxes = 0;
                    foreach ($rate->taxes as $rate_tax)
                        $rate_taxes += floatval($rate_tax);
                    // The cost including tax
                    $rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;
                    echo $rate_label . ': ' . wc_price( $rate_cost_incl_tax * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
                    break;
                }
            }
            echo '</div>';
        }
    }

    // Returns array of currency code ( 3 letters ) and converted rate
    public function get_currency_and_symbol()
    {
        $CFGEO = $GLOBALS['CFGEO'];

        $return_value = array();

        $currency_code =  ( isset( $CFGEO['currency'] ) ? strtoupper( (string)$CFGEO['currency'] ) : '' );
        $currency_converted = ( isset( $CFGEO['currency_converter'] ) ? (float)$CFGEO['currency_converter'] : '' );
        if( !empty( $currency_code )  && !empty( $currency_converted ) && $currency_code !== get_woocommerce_currency() )
        {
            $return_value['currency_code'] = $currency_code;
            $return_value['currency_converter'] = $currency_converted;
            return $return_value;
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
    public function cfgp_woocommerce_payment_settings()
    {
        woocommerce_admin_fields( $this->get_payment_settings() );
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
            if ( version_compare( $wp_version, '4.6', '>=' ) )
            {
                $all_countries = get_terms(array(
                    'taxonomy'		=> 'cf-geoplugin-country',
                    'hide_empty'	=> false
                ));
            }
            else
            {
                $all_countries = $this->cf_geo_get_terms(array(
                    'taxonomy'		=> 'cf-geoplugin-country',
                    'hide_empty'	=> false
                ));
            }
            
            if( !empty( $all_countries ) && is_array( $all_countries ) )
            {
                $countries_options = array();
                foreach( $all_countries as $key => $country )
                {
                    $countries_options[ $country->slug ] = sprintf( '%s - %s', $country->name, $country->description );
                }

                $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
                $custom_attributes = array();
                if( self::access_level( $CF_GEOPLUGIN_OPTIONS['license_sku'] ) < 2 )
                {
                    $custom_attributes['disabled'] = true;
                }
                
                $settings[] = array( 'name' => __( 'CF Geoplugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => __( 'Configure payment methods for countries', CFGP_NAME ), 'id' => 'cf_geoplugin_payment_restriction' );
                foreach( $enabled_gateways as $i => $gateway )
                {
                    $select_setting_id = sprintf( '%s_select', $gateway->id );
                    $method_setting_id = sprintf( '%s', $gateway->id );

                    $settings[ $method_setting_id ] = array(
                        'name'     => __( 'Choose desired method', CFGP_NAME ),
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s', $method_setting_id ),
                        'type'     => 'select',
                        'class'    => 'wc-enhanced-select',
                        'default'  => 'cfgp_payment_woo',
                        'css'      => 'min-width:400px;',
                        'type'     => 'select',
                        'options'  => array(
                            'cfgp_payment_woo'      => __( 'Woocommerce Default', CFGP_NAME ),
                            'cfgp_payment_enable'   => __( 'Enable only in selected countries', CFGP_NAME ),
                            'cfgp_payment_disable'  => __( 'Disable only for selected countries', CFGP_NAME ),
                        ),
                        'custom_attributes' => $custom_attributes,
                    );

                    $settings[ $select_setting_id ] = array(
                        'name'     => sprintf( '%s', esc_html( $gateway->method_title ) ),
                        'class'    => 'wc-enhanced-select',
                        'id'       => sprintf( 'woocommerce_cfgp_method_%s', $select_setting_id ),
                        'default'  => '',
                        'css'      => 'min-width:400px;',
                        'type'     => 'multiselect',
                        'options'  => $countries_options,
                        'custom_attributes' => $custom_attributes,
                    );

                }
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );

                return apply_filters( 'wc_cf_geoplugin_payment_restriction_settings', $settings );
            }
            else 
            {
                $settings[] = array( 'name' => __( 'CF Geoplugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => '<b>' . __( 'Currently we are not able to show desired options. Please try again later.', CFGP_NAME ) . '</b>', 'id' => 'cf_geoplugin_payment_restriction' );
                $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
                return apply_filters( 'wc_cf_geoplugin_payment_restriction_settings', $settings );
            }
        }
        else 
        {
            $settings[] = array( 'name' => __( 'CF Geoplugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => '<b>' . __( 'No enabled woocommerce payments yet.', CFGP_NAME ), 'id' => 'cf_geoplugin_payment_restriction' . '</b>' );
            $settings[] = array( 'type' => 'sectionend', 'id' => 'cf_geoplugin_payment_restriction' );
            return apply_filters( 'wc_cf_geoplugin_payment_restriction_settings', $settings );
        }
    }

    // Save payment settings
    public function cfgp_woocommerce_payment_settings_save()
    {
        woocommerce_update_options( $this->get_payment_settings() );
    }

    // Disable payments for specific users
    public function cfgp_woocommerce_payment_disable( $gateways )
    {
        $CFGEO = $GLOBALS['CFGEO'];
        if( ( !isset( $CFGEO['country_code'] ) || empty( $CFGEO['country_code'] ) ) && ( !isset( $CFGEO['country'] ) ) || empty( $CFGEO['country'] ) ) return $gateways;
        
        if( is_admin() ) return $gateways; // Very important check othervise will delete from settings and throw fatal error

        if( is_array( $gateways ) )
        {
            foreach( $gateways as $gateway )
            {
                $type = get_option( sprintf( 'woocommerce_cfgp_method_%s', $gateway->id ) );
                $countries = get_option( sprintf( 'woocommerce_cfgp_method_%s_select', $gateway->id ) );

                if( empty( $countries ) || $type == 'cfgp_payment_woo' ) continue;

                if( $type === 'cfgp_payment_disable' && $this->check_user_by_country( $countries ) ) 
                {
                    if( isset( $gateways[ $gateway->id ] ) ) unset( $gateways[ $gateway->id ] );
                }
                elseif( $type === 'cfgp_payment_enable' && !$this->check_user_by_country( $countries ) ) 
                {
                    if( isset( $gateways[ $gateway->id ] ) ) unset( $gateways[ $gateway->id ] );
                }
            }
        } 
        return $gateways; 
    }
}
endif;