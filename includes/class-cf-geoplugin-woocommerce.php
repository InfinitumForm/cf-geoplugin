<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Metaboxes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( !class_exists( 'CF_Geoplugin_Woocommerce' ) ):
class CF_Geoplugin_Woocommerce extends CF_Geoplugin_Global
{
    function __construct()
    {
        $this->add_action( 'plugins_loaded', 'check_woocommerce_instalation' );
    }

    // Check if woocommerce is installed and active
    public function check_woocommerce_instalation()
    {
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'] ; $CFGEO = $GLOBALS['CFGEO'];
        if( class_exists( 'WooCommerce' ) )
        {
            $this->update_option( 'woocommerce_active', 1 );
            
            if( $CF_GEOPLUGIN_OPTIONS['enable_woocommerce'] == 1 && isset( $CFGEO['currency_converter'] ) && $CFGEO['currency_converter'] > 0 )
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
        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            $sale_price = '';
            $regular_price = wc_price( $product->get_regular_price() * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            
			if($sp = $product->get_sale_price()){
				$regular_price = '<del>' . $regular_price . '</del>';
            	$sale_price = '<ins>' . wc_price( $sp * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] )  ) . '</ins>';
			}
            if( is_admin() ) 
            {
                if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return $price . '<hr>' .  $regular_price . '<br>' . $sale_price;
                else return $regular_price . '<br>' . $sale_price;
            }
            
            if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . "\n\r" . $sale_price.'</div>';
            else return '<div class="woocommerce-original-price">' . $regular_price . "\n\r" . $sale_price.'</div>';
        }
        return $price;
    }

    // Convert price and currency symbol for items in cart
    public function convert_cart_item_price( $price, $cart_item, $cart_item_key )
    {
        $CFGEO = $GLOBALS['CFGEO'];

        $currency_args = $this->get_currency_and_symbol();
        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            if($sale_price = $cart_item['data']->get_sale_price() )
            {
                // Return raw price. In data is set WC_Cart object.
                $sale_price = wc_price( $sale_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
                if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $sale_price . '</div>';
                else return '<div class="woocommerce-original-price">' . $sale_price . '</div>';
            } 
            else
            {
                $regular_price = wc_price( $cart_item['data']->get_regular_price() * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
                if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . '</div>';
                else return '<div class="woocommerce-original-price">' . $regular_price . '</div>';
            }
        }
        return $price;
    }

    // Convert price and currency symbol for subtotal item price
    public function convert_cart_item_subtotal_price( $price, $cart_item, $cart_item_key )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $cart_item['quantity'] > 0 && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            if($sale_price = $cart_item['data']->get_sale_price() ) //In data is stored WC_Product_Simple object.
            {
                $sale_price = wc_price( $sale_price * $currency_args['currency_converter'] * $cart_item['quantity'] , array( 'currency' => $currency_args['currency_code'] ) );
                if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $sale_price . '</div>';
                else return '<div class="woocommerce-original-price">' . $sale_price . '</div>';
            } 
            else
            {
                $regular_price = wc_price( $cart_item['data']->get_regular_price() * $currency_args['currency_converter'] * $cart_item['quantity'], array( 'currency' => $currency_args['currency_code'] ) );
                if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $regular_price . '</div>';
                else return '<div class="woocommerce-original-price">' . $regular_price . '</div>';
            }
        }
        return $price;
    }

    // Convert currency and symbol for subtotal cart price
    public function convert_cart_subtotal_price( $price, $compound, $instance )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            $subtotal_price = $instance->get_subtotal(); // WC_Cart class
            $subtotal_price = wc_price( $subtotal_price * $currency_args['currency_converter'] , array( 'currency' => $currency_args['currency_code'] ) );
            if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $subtotal_price . '</div>';
            else return '<div class="woocommerce-original-price">' . $subtotal_price . '</div>';
        }
        return $price;
    }

    // Convert currency and symbol for total cart price
    public function convert_cart_total_price( $price )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            $total_price = $this->price_to_float( $price );
            $total_price = wc_price( $total_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $total_price . '</div>';
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

        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            $price = $coupon->get_amount();
            $coupon_price = wc_price( $price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );

            if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $discount_amount_html . '</div><div class="woocommerce-converted-price">' . '-' . $coupon_price . '</div><a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
            else return '<div class="woocommerce-original-price">' . '-' . $coupon_price . '</div><a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
            // This is WC way of generating coupon html => http://woocommerce.wp-a2z.org/oik_api/wc_cart_totals_coupon_html/
        }
        
        return $coupon_html;
    }

    // Converts currency and symbol for cart total tax
    public function convert_cart_total_tax( $price )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            $tax_price = $this->price_to_float( $price );
            $tax_price = wc_price( $tax_price * $currency_args['currency_converter'], array( 'currency' => $currency_args['currency_code'] ) );
            if( get_option( 'woocommerce_cf_geoplugin_conversion' ) == 'both' ) return '<div class="woocommerce-original-price">' . $price . '</div><div class="woocommerce-converted-price">' . $tax_price . '</div>';
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
                        'both'      => __( 'Show original and converted price', CFGP_NAME )
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

        if( $currency_args !== false && get_option( 'woocommerce_cf_geoplugin_conversion' ) !== 'original' )
        {
            echo '<div class="woocommerce-converted-price">';
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

    // Convert any price to float
	private function price_to_float($s)
	{
        $matches = array();
        $s = preg_match_all( "/([0-9,.]+)/", $s, $matches);
        if(!empty( $matches ) ) 
        {
            $price = $matches[0][0];
            $s = str_replace(',', '', $price);
            return (float) $s;
        } 
        return 0;
	}

}
endif;