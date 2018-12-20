<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Widget: Converter
 *
 * @since      7.4.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
add_action( 'widgets_init', 'cfgp_widget_converter' );

function cfgp_widget_converter()
{
	register_widget( 'CF_Geoplugin_Converter' );
}

if( !class_exists( 'CF_Geoplugin_Converter' ) && class_exists( 'WP_Widget' ) ) :
class CF_Geoplugin_Converter extends WP_Widget
{
	/**
	 * Properties
	 */

    // Global class instance
	private $CF_Global = NULL;
	
    /**
     * Class constructor
     */
    function __construct()
    {
		/**
		 * Actions
		 */
		add_action( 'wp_ajax_cfgp_currency_converter', array( &$this, 'cfgp_currency_converter' ) );

        $this->CF_Global = CF_Geoplugin_Global::get_instance();

        $widget_ops = array( 
			'classname' => 'cfgp_converter',
			'description' => esc_html__( 'Currency Converter', CFGP_NAME ),
		);
		parent::__construct( $widget_ops['classname'], $widget_ops['description'], $widget_ops );
    }

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) 
	{
		$currency_symbols = CF_Geplugin_Library::CURRENCY_SYMBOL;

		$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		echo $args['before_widget'];
		?>
		<div class="cfgp-container-fluid mt-3 w-100">
			<div class="cfgp-card w-100 text-white bg-info">
				<div class="cfgp-card-body">
					<?php 
						$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Currency Converter', CFGP_NAME );
						echo $args['before_title'];
						printf( '%s', apply_filters( 'widget_title', $title ) ); 
						echo $args['after_title'];
					?>
					<div class="cfgp-row">
						<div class="cfgp-col-12">
						<form action="<?php self_admin_url( 'admin-ajax.php?action=cfgp_currency_converter' ); ?>" class="cfgp-currency-form" method="post">
							<div class="cfgp-form-group">
								<?php 
									$label_amount = sprintf( '%s-%s', 'cfgp-currency-amount', $this->CF_Global->generate_token(5) );
									$amount = ( isset( $instance['amount'] ) && !empty( $instance['amount'] ) ) ? $instance['amount'] : esc_html__( 'Amount', CFGP_NAME );
								?>
								<label class="cfgp-text-dark" for="<?php echo $label_amount; ?>"><?php echo $amount ?></label>
								<input type="text" name="cfgp_currency_amount" class="cfgp-form-control" id="<?php echo $label_amount; ?>" placeholder="<?php echo $amount; ?>">
							</div>
							
							<?php $label_from = sprintf( '%s-%s', 'cfgp-currency-from', $this->CF_Global->generate_token(5) ); ?>
							<label class="cfgp-text-dark" for="<?php echo $label_from; ?>"><?php echo ( isset( $instance['from'] ) && !empty( $instance['from'] ) ) ? $instance['from'] : esc_html__( 'From', CFGP_NAME ); ?></label>
							<div class="cfgp-form-group">
								<select name="cfgp_currency_from" class="cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-from" id="<?php echo $label_from; ?>" data-show-subtext="true">
									<?php
										foreach( $currency_symbols as $key => $countries )
										{
											$selected = '';
											if( isset( $CF_GEOPLUGIN_OPTIONS['base_currency'] ) && $CF_GEOPLUGIN_OPTIONS['base_currency'] == $key ) $selected = ' selected';

											$symbol = '';
											if( isset( $currency_symbols[ $key ] ) && !empty( $currency_symbols[ $key ] ) ) $symbol = sprintf( '- %s', $currency_symbols[ $key ] );
											printf( '<option value="%s" %s>%s %s</option>', $key, $selected, $key, $symbol );
										}
									?>
								</select>
							</div>
	
							<?php $label_to = sprintf( '%s-%s', 'cfgp-currency-to', $this->CF_Global->generate_token(5) ); ?>
							<label class="cfgp-text-dark" for="<?php echo $label_to; ?>"><?php echo ( isset( $instance['to'] ) && !empty( $instance['to'] ) ) ? $instance['to'] : esc_html__( 'To', CFGP_NAME ); ?></label>
							<div class="cfgp-form-group">
								<select name="cfgp_currency_to" class="cfgp-form-control cfgp-custom-select cfgp-col-10 cfgp-currency-to" id="<?php echo $label_to; ?>" data-show-subtext="true">
									<?php
										foreach( $currency_symbols as $key => $countries )
										{
											$selected = '';
											if( isset( $CFGEO['currency'] ) && $CFGEO['currency'] == $key ) $selected = ' selected';

											$symbol = '';
											if( isset( $currency_symbols[ $key ] ) && !empty( $currency_symbols[ $key ] ) ) $symbol = sprintf( '- %s', $currency_symbols[ $key ] );
											printf( '<option value="%s" %s>%s %s</option>', $key, $selected, $key, $symbol );
										}
									?>
								</select>
							</div>
							<div class="cfgp-form-group">
								<button type="submit" class="cfgp-btn cfgp-btn-danger"><?php esc_html_e( 'Convert', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;
								<img style="height:50px;" src="<?php echo esc_url( CFGP_ASSETS . '/images/switch-arrows.png' ); ?>" class="cfgp-exchange-currency" /> 
							</div>
							<?php wp_nonce_field( 'cfgp_currency_converter' ); ?>
							<p class="cfgp-currency-converted"></p>
						</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) 
	{
		$title = ( isset( $instance['title'] ) && !empty( $instance['title'] ) ) ? $instance['title'] : esc_html__( 'Currency Converter', CFGP_NAME );
		$amount = ( isset( $instance['amount'] ) && !empty( $instance['amount'] ) ) ? $instance['amount'] : esc_html__( 'Amount', CFGP_NAME );
		$from = ( isset( $instance['from'] ) && !empty( $instance['from'] ) ) ? $instance['from'] : esc_html__( 'From', CFGP_NAME );
		$to = ( isset( $instance['to'] ) && !empty( $instance['to'] ) ) ? $instance['to'] : esc_html__( 'To', CFGP_NAME );

		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', CFGP_NAME ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>"><?php esc_attr_e( 'Amount Label:', CFGP_NAME ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>" type="text" value="<?php echo esc_attr( $amount ); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>"><?php esc_attr_e( 'From Label:', CFGP_NAME ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" type="text" value="<?php echo esc_attr( $from ); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>"><?php esc_attr_e( 'To Label:', CFGP_NAME ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'to' ) ); ?>" type="text" value="<?php echo esc_attr( $to ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();
		$instance['title'] = ( isset( $new_instance['title'] ) && !empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['amount'] = ( isset( $new_instance['amount'] ) && !empty( $new_instance['amount'] ) ) ? sanitize_text_field( $new_instance['amount'] ) : '';
		$instance['from'] = ( isset( $new_instance['from'] ) && !empty( $new_instance['from'] ) ) ? sanitize_text_field( $new_instance['from'] ) : '';
		$instance['to'] = ( isset( $new_instance['to'] ) && !empty( $new_instance['to'] ) ) ? sanitize_text_field( $new_instance['to'] ) : '';

		return $instance;
	}

	/**
	 * Ajax call for currency conversion
	 */
	public function cfgp_currency_converter()
	{
		if( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'cfgp_currency_converter' ) )
		{
			$this->show_conversion_card_message( 'error_direct' );
			wp_die();
		}

		$amount = filter_var( $_REQUEST['cfgp_currency_amount'], FILTER_SANITIZE_NUMBER_FLOAT,  FILTER_FLAG_ALLOW_FRACTION );

		if( empty( $amount ) )
		{
			$this->show_conversion_card_message( 'error_user' );
			wp_die();
		}

		$amount = str_replace( '-', '', $amount );
		$api_params = array(
			'from'		=> strtoupper( $_REQUEST['cfgp_currency_from'] ),
			'to'		=> strtoupper( $_REQUEST['cfgp_currency_to'] ),
			'amount'	=> $amount
		);
		$api_url = add_query_arg( $api_params, 'http://cdn-cfgeoplugin.com/api6.0/convert.php' );

		$result = $this->CF_Global->curl_get( $api_url );
		
		$result = json_decode( $result, true );

		if( isset( $result['return'] ) )
		{
			if( $result['return'] == false ) $this->show_conversion_card_message( 'error_api' );
			else
			{
				$this->show_conversion_card_message( 'success', $result );
			}
		}
		else $this->show_conversion_card_message( 'error_api' );
		wp_die();
	}

	/**
	 * Show conversion card message
	 */
	public function show_conversion_card_message( $message_type, $result = array() )
	{
		$card_type = 'bg-danger';

		switch( $message_type )
		{
			case 'error_direct':
				$message = '<b>' . esc_html__( 'Direct access is forbidden!', CFGP_NAME ) . '</b>';
			break;
			case 'error_user': 
				$message = '<b>' . esc_html__( 'Please enter valid decimal or integer format.', CFGP_NAME ) . '</b>';
			break;
			case 'error_api':
				$message = '<b>' . esc_html__( 'Sorry currently we are not able to do conversion. Please try again later.', CFGP_NAME ) . '</b>';
			break;
			case 'success':
				if( !isset( $result['from_amount'] ) || empty( $result['from_amount'] ) ) 
				{
					$result['from_amount'] = '1';
					$result['to_amount'] = '1';
				}
				if( !isset( $result['to_amount'] ) || empty( $result['to_amount'] ) )
				{
					$result['from_amount'] = '1';
					$result['to_amount'] = '1';
				}
		
				if( !isset( $result['from_name'] ) || empty( $result['from_name'] ) ) $result['from_name'] = esc_html__( 'Undefined', CFGP_NAME );
				if( !isset( $result['to_name'] ) || empty( $result['to_name'] ) ) $result['to_name'] = esc_html__( 'Undefined', CFGP_NAME );;
		
				if( !isset( $result['from_code'] ) || empty( $result['from_code'] ) ) $result['from_code'] = 'X';
				if( !isset( $result['to_code'] ) || empty( $result['to_code'] ) ) $result['to_code'] = 'X';

				$message = sprintf( '<p><b>%s %s = %s %s</b></p><p><b>%s <img style="height:12px;" src="%s" /> %s</b></p>', $result['from_amount'], $result['from_code'], $result['to_amount'], $result['to_code'], $result['from_name'], esc_url( CFGP_ASSETS . '/images/right-arrow.png' ), $result['to_name'] );
				$card_type = 'bg-secondary';
			break;
			default:
				$message = '<b>' . esc_html__( 'Sorry currently we are not able to do conversion. Please try again later.', CFGP_NAME ) . '</b>';
			break;
		}
		?>
		<div class="card w-100 text-white <?php echo esc_attr( $card_type ); ?>">
			<div class="card-body text-center">
				<p class="card-text"><?php echo $message; ?></p>
			</div>
		</div>
		<?php
	}
}
endif;