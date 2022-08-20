<?php
/**
 * Widgets settings
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Widget_Currency_Converter')) :
class CFGP_Widget_Currency_Converter extends WP_Widget {
	
	// The construct part  
	function __construct() {
		parent::__construct(
			'CFGP_Widget_Currency_Converter', 
			__('Currency Converter', 'cf-geoplugin'), 
			array( 'description' => __( 'Convert any currency.', 'cf-geoplugin'), ) 
		);
	}
	  
	// Creating widget front-end
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo do_shortcode( sprintf(
			'[cfgeo_full_converter title="%s" before_title="%s" after_title="%s" amount="%s" from="%s" to="%s"][/cfgeo_full_converter]',
			esc_attr($instance['title'] ?? ''),
			esc_attr($args['before_title'] ?? ''),
			esc_attr($args['after_title'] ?? ''),
			esc_attr($instance['amount'] ?? ''),
			esc_attr($instance['from'] ?? ''),
			esc_attr($instance['to'] ?? '')
		) );
		
		echo $args['after_widget'];
	}
			  
	// Creating widget Backend 
	public function form( $instance ) {
		$title = $this->_sanitize_form_input($instance, 'title', 'text', esc_attr__( 'Currency Converter', 'cf-geoplugin'));
		$amount = $this->_sanitize_form_input($instance, 'amount', 'text', esc_attr__( 'Amount', 'cf-geoplugin'));
		$from = $this->_sanitize_form_input($instance, 'from', 'text', esc_attr__( 'From', 'cf-geoplugin'));
		$to = $this->_sanitize_form_input($instance, 'to', 'text', esc_attr__( 'To', 'cf-geoplugin'));
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'cf-geoplugin'); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>"><?php esc_attr_e( 'Amount Label:', 'cf-geoplugin'); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>" type="text" value="<?php echo esc_attr( $amount ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>"><?php esc_attr_e( 'From Label:', 'cf-geoplugin'); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" type="text" value="<?php echo esc_attr( $from ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>"><?php esc_attr_e( 'To Label:', 'cf-geoplugin'); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'to' ) ); ?>" type="text" value="<?php echo esc_attr( $to ); ?>">
		</p>
	<?php 
	}
		  
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = [];	
		
		$instance['title'] = $this->_sanitize_form_input($new_instance, 'title', 'text', esc_attr__( 'Currency Converter', 'cf-geoplugin'));
		$instance['amount'] = $this->_sanitize_form_input($new_instance, 'amount', 'text', esc_attr__( 'Amount', 'cf-geoplugin'));
		$instance['from'] = $this->_sanitize_form_input($new_instance, 'from', 'text', esc_attr__( 'From', 'cf-geoplugin'));
		$instance['to'] = $this->_sanitize_form_input($new_instance, 'to', 'text', esc_attr__( 'To', 'cf-geoplugin'));

		return $instance;
	}
	
	private function _sanitize_form_input($new_instance, $name, $sanitize='text', $default=NULL) {
		switch($sanitize) {
			default:
			case 'text':
				return sanitize_text_field(isset($new_instance[$name]) && !empty($new_instance[$name]) ? $new_instance[$name] : $default);
				break;
				
			case 'textarea':
				return sanitize_textarea_field(isset($new_instance[$name]) && !empty($new_instance[$name]) ? $new_instance[$name] : $default);
				break;
				
			case 'int':
			case 'integer':
				return absint(sanitize_text_field(isset($new_instance[$name]) && !empty($new_instance[$name]) ? $new_instance[$name] : $default));
				break;
		}
	}
}
endif;