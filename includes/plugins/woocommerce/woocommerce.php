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
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$this->cache = false;
		if(isset($CF_GEOPLUGIN_OPTIONS['enable_cache']) ? $CF_GEOPLUGIN_OPTIONS['enable_cache'] : false) $this->cache = true;
		
        $this->add_action( 'init', 'check_woocommerce_instalation', 10 );
        $this->cf_conversion = get_option( 'woocommerce_cf_geoplugin_conversion' );
		$this->cf_conversion_rounded = get_option( 'woocommerce_cf_geoplugin_conversion_rounded' );
		$this->cf_conversion_rounded_option = get_option( 'woocommerce_cf_geoplugin_conversion_rounded_option' );
		$this->cf_conversion_adjust = get_option( 'woocommerce_cf_geoplugin_conversion_adjust' );
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
                    $this->add_filter( 'woocommerce_cart_item_subtotal', 'convert_cart_item_price', 10, 3 ); // Subtotal Item Price
                    $this->add_filter( 'woocommerce_cart_subtotal', 'convert_cart_subtotal_price', 10, 3 ); // Subtotal Cart Price
                    $this->add_filter( 'woocommerce_cart_total', 'convert_cart_total_price', 10 ); // Total Price
                    //$this->add_filter( 'woocommerce_package_rates', 'convert_shipping_price', 10, 2 ); // Shipping Price 
                    $this->add_filter( 'woocommerce_before_shipping_calculator', 'show_shipping_price', 10 ); // Show Converted Shipping Price
                    $this->add_filter( 'woocommerce_cart_totals_coupon_html', 'convert_coupon_price', 10, 3 ); // Coupon Price
                    $this->add_filter( 'woocommerce_cart_totals_taxes_total_html', 'convert_cart_total_tax', 10 ); // Cart Total Tax Price

                    $this->add_filter( 'woocommerce_general_settings', 'conversion_options', 10 ); // Add custom option for conversion system
					
					$this->add_filter( 'woocommerce_variable_sale_price_html', 'convert_variable_price_format', 10, 2 );
					$this->add_filter( 'woocommerce_variable_price_html', 'convert_variable_price_format', 10, 2 );
					
                }
                $this->add_filter( 'woocommerce_settings_tabs_array', 'cfgp_woocommerce_tabs', 50 ); //  Add a settings tabs

                $this->add_action( 'woocommerce_settings_tabs_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings' ); // Add payment settings
                
                if( self::access_level( $CF_GEOPLUGIN_OPTIONS['license_sku'] ) >= 2 )
                {
                    $this->add_action( 'woocommerce_update_options_cf_geoplugin_payment_restriction', 'cfgp_woocommerce_payment_settings_save' ); // Save our settings for payments
                    $this->add_filter( 'woocommerce_available_payment_gateways', 'cfgp_woocommerce_payment_disable' ); // Disable payment gateways for specifis users
                }
				
			//	$this->add_filter( 'raw_woocommerce_price', 'raw_woocommerce_price' );

				$this->add_filter('wp_geo_woocommerce_currency_and_symbol', 'calculate_conversions', 1);

				if( $this->cf_conversion_rounded == 'yes' )
				{
					$this->add_filter('wp_geo_woocommerce_currency_and_symbol', 'round_conversions', 2);
				}
            }
        }
        else
        {
            $this->update_option( 'woocommerce_active', 0 );
        }
    }

/**
	public function raw_woocommerce_price( $price ) {
		$currency_args = $this->get_currency_and_symbol();
		if( $currency_args !== false && $this->cf_conversion !== 'original')
		{
			return $price * $currency_args['currency_converter'];
		}
		return $price;
	}
**/
	
	// Calculate % increment
	public function calculate_conversions($currency_args){
		if($this->cf_conversion_adjust && is_numeric($this->cf_conversion_adjust) && intval($this->cf_conversion_adjust) == $this->cf_conversion_adjust && $this->cf_conversion_adjust > 0)
		{
			$this->cf_conversion_adjust = ( $this->cf_conversion_adjust >= 100 ? 100 : intval($this->cf_conversion_adjust) );
			$percentage = (($this->cf_conversion_adjust / 100) * $currency_args['currency_converter']);
			$currency_args['currency_converter'] = $currency_args['currency_converter'] + $percentage;
		}
		
		return $currency_args;
	}
	
	// Put conversion  to round number
	public function round_conversions($currency_args){
		switch($this->cf_conversion_rounded_option)
		{
			default:
			case 'up':
				$currency_args['currency_converter'] = ceil($currency_args['currency_converter']);
				break;
			case 'nearest':
				$currency_args['currency_converter'] = round($currency_args['currency_converter']);
				break;
			case 'down':
				$currency_args['currency_converter'] = floor($currency_args['currency_converter']);
				break;
		}
		return $currency_args;
	}
	
	// Format variabile products
	public function convert_variable_price_format ( $price, $product ){
		$currency_args = $this->get_currency_and_symbol();

		$max_price = $product->get_variation_price( 'max', true );
		$min_price = $product->get_variation_price( 'min', true );
		
		if($currency_args !== false && $this->cf_conversion !== 'original')
		{
			$convert_min_price = apply_filters(
				'wp_geo_woocommerce_convert_variable_min_price_format',
				((float)$min_price * (float)$currency_args['currency_converter']),
				$min_price,
				$currency_args['currency_converter'],
				$currency_args['currency_code']
			);
			$convert_max_price = apply_filters(
				'wp_geo_woocommerce_convert_variable_max_price_format',
				((float)$max_price * (float)$currency_args['currency_converter']),
				$max_price,
				$currency_args['currency_converter'],
				$currency_args['currency_code']
			);
			
			if( $this->cf_conversion == 'both' )
			{
				if($this->cache)
				{
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
					<div class="woocommerce-original-price">
						' . wc_price($min_price) . '  –  ' . wc_price($max_price) . '
					</div>
					' . (is_admin() ? '<hr>' : NULL) . '
					<div class="woocommerce-converted-price">
						' . wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] )) . '
					</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				}
				else
				{
					return '
					<div class="woocommerce-original-price">
						' . wc_price($min_price) . '  –  ' . wc_price($max_price) . '
					</div>
					' . (is_admin() ? '<hr>' : NULL) . '
					<div class="woocommerce-converted-price">
						' . wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] )) . '
					</div>';
				}
			}
			else if( $this->cf_conversion == 'inversion' )
			{
				if($this->cache)
				{
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
					<div class="woocommerce-original-price">
						' . wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] )) . '
					</div>
					' . (is_admin() ? '<hr>' : NULL) . '
					<div class="woocommerce-converted-price">
						' . wc_price($min_price) . '  –  ' . wc_price($max_price) . '
					</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				}
				else
				{
					return '
					<div class="woocommerce-original-price">
						' . wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] )) . '
					</div>
					' . (is_admin() ? '<hr>' : NULL) . '
					<div class="woocommerce-converted-price">
						' . wc_price($min_price) . '  –  ' . wc_price($max_price) . '
					</div>';
				}
			}
			else if( $this->cf_conversion == 'converted' )
			{	if($this->cache)
				{
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' . wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] )) . '<!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				}
				else
				{
					return wc_price($convert_min_price, array( 'currency' => $currency_args['currency_code'] )) . '  –  ' . wc_price($convert_max_price, array( 'currency' => $currency_args['currency_code'] ));
				}
			}
		}
		
		
		return $this->cache ? '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' . $price . '<!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : $price;
	}

    // Convert price and currency symbol for items
    public function convert_item_price( $price, $product )
    {
		if($product->is_type( 'variable' )) return $this->convert_variable_price_format($price, $product);
		
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original')
        {
            $sale_price = '';
            $regular_price = wc_price( 
				apply_filters(
					'wp_geo_woocommerce_convert_item_regular_price',
					((float)$product->get_regular_price() * (float)$currency_args['currency_converter']),
					$product->get_regular_price(),
					$currency_args['currency_converter'],
					$currency_args['currency_code']
				),
				array( 'currency' => $currency_args['currency_code'] )
			);
            
			if($sp = $product->get_sale_price()){
				$regular_price = '<del>' . $regular_price . '</del>';
            	$sale_price = '<ins>' . wc_price(
					apply_filters(
						'wp_geo_woocommerce_convert_item_sale_price',
						((float)$sp * (float)$currency_args['currency_converter']),
						$sp,
						$currency_args['currency_converter'],
						$currency_args['currency_code']
					),
					array( 'currency' => $currency_args['currency_code'] )
				) . '</ins>';
			}
            if( is_admin() ) 
            {
                if( $this->cf_conversion == 'both' ) return $price . '<hr>' .  $regular_price . '<br>' . $sale_price;
                elseif( $this->cf_conversion == 'inversion' ) return $regular_price . '<br>' . $sale_price . '<hr>' . $price;
                else return $regular_price . '<br>' . $sale_price;
            }
            
            if( $this->cf_conversion == 'both' )
			{
				if($this->cache)
				{
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
					<div class="woocommerce-original-price">
						' . $price . '
					</div>
					<div class="woocommerce-converted-price">
						' . $regular_price . "\n\r" . $sale_price .'
					</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				}
				else
				{
					return '
					<div class="woocommerce-original-price">
						' . $price . '
					</div>
					<div class="woocommerce-converted-price">
						' . $regular_price . "\n\r" . $sale_price .'
					</div>';
				}
			}
			elseif( $this->cf_conversion == 'inversion' )
			{
				if($this->cache)
				{
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
					<div class="woocommerce-original-price">
						' . $regular_price . "\n\r" . $sale_price . '
					</div>
					<div class="woocommerce-converted-price">
						' . $price .'
					</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				}
				else
				{
					return '
					<div class="woocommerce-original-price">
						' . $regular_price . "\n\r" . $sale_price . '
					</div>
					<div class="woocommerce-converted-price">
						' . $price .'
					</div>';
				}
			}
			else
			{
				if($this->cache)
					return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' --><div class="woocommerce-original-price">' . $regular_price . "\n\r" . $sale_price.'</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
				else
					return '<div class="woocommerce-original-price">' . $regular_price . "\n\r" . $sale_price.'</div>';
			}
        }
		
        return $this->cache ? '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' . $price . '<!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : $price;
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
                $sale_price = wc_price(
					apply_filters(
						'wp_geo_woocommerce_convert_cart_item_sale_price',
						((float)$sale_price * (float)$currency_args['currency_converter']),
						$sale_price,
						$currency_args['currency_converter'],
						$currency_args['currency_code']
					),
					array( 'currency' => $currency_args['currency_code'] )
				);
				
                if( $this->cf_conversion == 'both' )
				{
					if($this->cache)
					{
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
						<div class="woocommerce-original-price">
							' . $price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $sale_price . '
						</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					}
					else
					{
						return '
						<div class="woocommerce-original-price">
							' . $price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $sale_price . '
						</div>';
					}
				}
				elseif( $this->cf_conversion == 'inversion' )
				{
					if($this->cache)
					{
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
						<div class="woocommerce-original-price">
							' . $sale_price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $price . '
						</div><!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					}
					else
					{
						return '
						<div class="woocommerce-original-price">
							' . $sale_price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $price . '
						</div>';
					}
				}
				else
				{
					if($this->cache)
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' --><div class="woocommerce-original-price">' . $sale_price . '</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					else
						return '<div class="woocommerce-original-price">' . $sale_price . '</div>';
				}
            } 
            else
            {
                $regular_price = wc_price(
					apply_filters(
						'wp_geo_woocommerce_convert_cart_item_regular_price',
						((float)$cart_item['data']->get_regular_price() * (float)$currency_args['currency_converter']),
						$cart_item['data']->get_regular_price(),
						$currency_args['currency_converter'],
						$currency_args['currency_code']
					),
					array( 'currency' => $currency_args['currency_code'] )
				);
                if( $this->cf_conversion == 'both' )
				{
					if($this->cache)
					{
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
						<div class="woocommerce-original-price">
							' . $price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $regular_price . '
						</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					}
					else
					{
						return '
						<div class="woocommerce-original-price">
							' . $price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $regular_price . '
						</div>';
					}
				}
				elseif( $this->cf_conversion == 'inversion' )
				{
					if($this->cache)
					{
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->
						<div class="woocommerce-original-price">
							' . $regular_price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $price . '
						</div><!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					}
					else
					{
						return '
						<div class="woocommerce-original-price">
							' . $regular_price . '
						</div>
						<div class="woocommerce-converted-price">
							' . $price . '
						</div>';
					}
				}
				else
				{
					if($this->cache)
						return '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' --><div class="woocommerce-original-price">' . $regular_price . '</div><!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->';
					else
						return '<div class="woocommerce-original-price">' . $regular_price . '</div>';
				}
            }
        }
        return $this->cache ? '<!--mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' . $price . '<!--/mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : $price;
    }

    // Convert currency and symbol for subtotal cart price
    public function convert_cart_subtotal_price( $price, $compound, $instance )
    {
        $currency_args = $this->get_currency_and_symbol();

        if( $currency_args !== false && $this->cf_conversion !== 'original' )
        {
            $subtotal_price = $instance->get_subtotal(); // WC_Cart class
            $subtotal_price = wc_price(
				apply_filters(
					'wp_geo_woocommerce_convert_cart_subtotal_price',
					($subtotal_price * $currency_args['currency_converter']),
					$subtotal_price,
					$currency_args['currency_converter'],
					$currency_args['currency_code']
				),
				array( 'currency' => $currency_args['currency_code'] )
			);
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
            $total_price = wc_price(
				apply_filters(
					'wp_geo_woocommerce_convert_cart_total_price',
					($total_price * $currency_args['currency_converter']),
					$total_price,
					$currency_args['currency_converter'],
					$currency_args['currency_code']
				),
				array( 'currency' => $currency_args['currency_code'] )
			);
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
            $coupon_price = wc_price(
				apply_filters(
					'wp_geo_woocommerce_convert_coupon_price',
					($price * $currency_args['currency_converter']),
					$price,
					$currency_args['currency_converter'],
					$currency_args['currency_code']
				),
				array( 'currency' => $currency_args['currency_code'] )
			);

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
            $tax_price = wc_price(
				apply_filters(
					'wp_geo_woocommerce_convert_cart_total_tax',
					($tax_price * $currency_args['currency_converter']),
					$tax_price,
					$currency_args['currency_converter'],
					$currency_args['currency_code']
				),
				array( 'currency' => $currency_args['currency_code'] )
			);
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
                    'title'    => __( 'Convert to round priced', CFGP_NAME ),
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
                    echo $rate_label . ': ' . wc_price(
						apply_filters(
							'wp_geo_woocommerce_show_shipping_price',
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
        $CFGEO = $GLOBALS['CFGEO'];

        $return_value = array();

        $currency_code =  ( isset( $CFGEO['currency'] ) ? strtoupper( (string)$CFGEO['currency'] ) : '' );
        $currency_converted = ( isset( $CFGEO['currency_converter'] ) ? (float)$CFGEO['currency_converter'] : '' );
        if( !empty( $currency_code )  && !empty( $currency_converted ) && $currency_code !== get_woocommerce_currency() )
        {
            $return_value['currency_code'] = $currency_code;
            $return_value['currency_converter'] = $currency_converted;
            return apply_filters('wp_geo_woocommerce_currency_and_symbol', $return_value, get_woocommerce_currency());
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
                
                $settings[] = array( 'name' => __( 'CF Geoplugin Payments Control', CFGP_NAME ), 'type' => 'title', 'desc' => __( 'Configure payment methods for each country. Show or hide payment methods by country to prevent unwanted transactions.', CFGP_NAME ) . (
				isset($custom_attributes['disabled']) && $custom_attributes['disabled']
				? ' <br><span style="color:#dc3545;">' . sprintf(__('This option is only enabled with the licensed version of the %s. You must use 1 year license or above.', CFGP_NAME), '<a href="' . admin_url('admin.php?page=cf-geoplugin-activate') . '">WordPress Geo Plugin</a>') . '</span>'
				: ''
				), 'id' => 'cf_geoplugin_payment_restriction' );
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
		$original_gateways = $gateways;
		
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
        return apply_filters( 'wc_cf_geoplugin_woocommerce_payment_disable', $gateways, $original_gateways, $CFGEO ); 
    }
}
endif;