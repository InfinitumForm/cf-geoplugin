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
	
    /**
     * Class constructor
     */
    function __construct()
    {
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
		echo $args['before_widget'];
		echo do_shortcode( sprintf( '[cfgeo_full_converter title="%s" before_title="%s" after_title="%s" amount="%s" from="%s" to="%s"][/cfgeo_full_converter]', $instance['title'], $args['before_title'], $args['after_title'], $instance['amount'], $instance['from'], $instance['to'] ) );
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) 
	{
		$title = ( isset( $instance['title'] ) && !empty( $instance['title'] ) ) ? esc_html( $instance['title'] ) : esc_html__( 'Currency Converter', CFGP_NAME );
		$amount = ( isset( $instance['amount'] ) && !empty( $instance['amount'] ) ) ? esc_html( $instance['amount'] ) : esc_html__( 'Amount', CFGP_NAME );
		$from = ( isset( $instance['from'] ) && !empty( $instance['from'] ) ) ? esc_html( $instance['from'] ) : esc_html__( 'From', CFGP_NAME );
		$to = ( isset( $instance['to'] ) && !empty( $instance['to'] ) ) ? esc_html( $instance['to'] ) : esc_html__( 'To', CFGP_NAME );

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
		$instance['title'] = ( isset( $new_instance['title'] ) && !empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : esc_html__( 'Currency Converter', CFGP_NAME );
		$instance['amount'] = ( isset( $new_instance['amount'] ) && !empty( $new_instance['amount'] ) ) ? sanitize_text_field( $new_instance['amount'] ) : esc_html__( 'Amount', CFGP_NAME );
		$instance['from'] = ( isset( $new_instance['from'] ) && !empty( $new_instance['from'] ) ) ? sanitize_text_field( $new_instance['from'] ) : esc_html__( 'From', CFGP_NAME );
		$instance['to'] = ( isset( $new_instance['to'] ) && !empty( $new_instance['to'] ) ) ? sanitize_text_field( $new_instance['to'] ) : esc_html__( 'To', CFGP_NAME );

		return $instance;
	}

}
endif;