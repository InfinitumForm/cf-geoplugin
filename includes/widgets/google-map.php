<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Widget: Google Map
 *
 * @since      7.4.2
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
add_action( 'widgets_init', 'cfgp_widget_google_map' );

function cfgp_widget_google_map()
{
	register_widget( 'CF_Widget_GMAP' );
}

if( !class_exists( 'CF_Widget_GMAP' ) && class_exists( 'WP_Widget' ) ) :
class CF_Widget_GMAP extends WP_Widget
{
    /**
     * Class constructor
     */
    function __construct()
    {
        $widget_ops = array( 
			'classname' => 'cfgp_widget_google_map',
			'description' => esc_html__( 'Google Map', CFGP_NAME ),
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
		$CFGEO = $GLOBALS['CFGEO'];

		$instance['title'] = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Our location', CFGP_NAME ); 

		if( ( !isset( $instance['latitude'] ) || empty( $instance['latitude'] ) ) && isset( $CFGEO['latitude'] ) )
		{
			$instance['latitude'] = $CFGEO['latitude'];
		}
		

		if( ( !isset( $instance['longitude'] ) || empty( $instance['longitude'] ) ) && isset( $CFGEO['longitude'] ) )
		{
			$instance['longitude'] = $CFGEO['longitude'];
		}
		
		$instance['width'] = isset( $instance['width'] ) && !empty( $instance['width'] ) ? $instance['width'] : '100%'; 
		$instance['height'] = isset( $instance['height'] ) && !empty( $instance['height'] )  ? $instance['height'] : '400px';
		$instance['max_zoom'] = isset( $instance['max_zoom'] ) ? $instance['max_zoom'] : 8;
		$instance['zoom'] = isset( $instance['zoom'] ) ? $instance['zoom'] : '1';
		$instance['navigation'] = isset( $instance['navigation'] ) ? $instance['navigation'] : '1';
		$instance['map_type_control'] = isset( $instance['map_type_control'] ) ? $instance['map_type_control'] : '1';
		$instance['scale_control'] = isset( $instance['scale_control'] ) ? $instance['scale_control'] : '1';
		$instance['dragable'] = isset( $instance['dragable'] ) ? $instance['dragable'] : '1';
		$instance['info_max_width'] = isset( $instance['info_max_width'] ) ? $instance['info_max_width'] : '200';

		if( (int)$instance['info_max_width'] < 0 || (int)$instance['info_max_width'] >= 600 ) 
		{
			$instance['info_max_width'] = '200';
		}
		if( strpos( $instance['width'], '%' ) === false || strpos( $instance['width'], 'px' ) === false )
		{
			$instance['width'] = '100%';
		}
		if( strpos( $instance['height'], '%' ) === false || strpos( $instance['height'], 'px' ) === false ) 
		{
			$instance['height'] = '400px';
		}

        echo $args['before_widget'];
		echo do_shortcode( 
			sprintf( '[cfgeo_map latitude="%1$s" longitude="%2$s" scrollwheel="%3$s" zoom="%4$d" width="%5$s" height="%6$s" navigationControl="%7$s" mapTypeControl="%8$s" scaleControl="%9$s" draggable="%10$s" infoMaxWidth="%11$s"]%12$s[/cfgeo_map]',
				$instance['latitude'],
				$instance['longitude'],
				$instance['zoom'],
				$instance['max_zoom'],
				$instance['width'],
				$instance['height'],
				$instance['navigation'],
				$instance['map_type_control'],
				$instance['scale_control'],
				$instance['dragable'],
				$instance['info_max_width'],
				$instance['title']
			) 
		);
        echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
    public function form( $instance ) 
    {
		$CFGEO = $GLOBALS['CFGEO'];

		$title = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Our location', CFGP_NAME ); 

		$latitude = '';
		if( isset( $instance['latitude'] ) && !empty( $instance['latitude'] ) )
		{
			$latitude =  $instance['latitude'];
		}
		elseif( isset( $CFGEO['latitude'] ) )
		{
			$latitude = $CFGEO['latitude'];
		}


		$longitude = '';
		if( isset( $instance['longitude'] ) && !empty( $instance['longitude'] ) )
		{
			$longitude = $instance['longitude'];
		}
		elseif( isset( $CFGEO['longitude'] ) )
		{
			$longitude = $CFGEO['longitude'];
		}
		
		$width = isset( $instance['width'] ) && !empty( $instance['width'] ) ? $instance['width'] : '100%'; 
		$height = isset( $instance['height'] ) && !empty( $instance['height'] )  ? $instance['height'] : '400px';
		$max_zoom = isset( $instance['max_zoom'] ) ? $instance['max_zoom'] : 8;
		$zoom = isset( $instance['zoom'] ) ? $instance['zoom'] : '1';
		$navigation = isset( $instance['navigation'] ) ? $instance['navigation'] : '1';
		$map_type_control = isset( $instance['map_type_control'] ) ? $instance['map_type_control'] : '1';
		$scale_control = isset( $instance['scale_control'] ) ? $instance['scale_control'] : '1';
		$dragable = isset( $instance['dragable'] ) ? $instance['dragable'] : '1';
		$info_max_width = isset( $instance['info_max_width'] ) ? $instance['info_max_width'] : '200';

		if( (int)$info_max_width < 0 || (int)$info_max_width >= 600 ) 
		{
			$info_max_width = '200';
		}
		if( strpos( $width, '%' ) === false || strpos( $width, 'px' ) === false )
		{
			$width = '100%';
		}
		if( strpos( $height, '%' ) === false || strpos( $height, 'px' ) === false ) 
		{
			$height = '400px';
		}
		?> 
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Map pointer text:', CFGP_NAME ); ?></label> 
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>	
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'latitude' ) ); ?>"><?php esc_attr_e( 'Latitude:', CFGP_NAME ); ?></label> 
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'latitude' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'latitude' ) ); ?>" type="text" value="<?php echo esc_attr( $latitude ); ?>">
		</p>
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'Longitude' ) ); ?>"><?php esc_attr_e( 'Longitude:', CFGP_NAME ); ?></label> 
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'logintude' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'longitude' ) ); ?>" type="text" value="<?php echo esc_attr( $longitude ); ?>">
		</p>
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>"><?php esc_attr_e( 'Width:', CFGP_NAME ); ?></label> 
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>">
		</p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_attr_e( 'Height:', CFGP_NAME ); ?></label> 
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>">
		</p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'max_zoom' ) ); ?>"><?php esc_attr_e( 'Max zoom:', CFGP_NAME ); ?></label> 
		    <select id="<?php echo esc_attr( $this->get_field_id( 'max_zoom' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max_zoom' ) ); ?>">
                <?php
                    for( $i = 1; $i <= 18; $i++ )
                    {
						$selected = '';
						if( $i == $max_zoom ) $selected = ' selected';
                        printf( '<option value="%s" %s>%s</option>', $i, $selected, $i );
                    }
                ?>
            </select>
		</p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'zoom' ) ); ?>"><?php esc_attr_e( 'Disables scrollwheel zooming on the map:', CFGP_NAME ); ?></label> <br />
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'zoom' ) . '_true' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'zoom' ) ); ?>" type="radio" value="1" <?php checked( $zoom, '1', true ); ?>><?php _e( 'Enable', CFGP_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'zoom' ) . '_false' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'zoom' ) ); ?>" type="radio" value="0" <?php checked( $zoom, '0', true ); ?>><?php _e( 'Disable', CFGP_NAME ); ?>
        </p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'navigation' ) ); ?>"><?php esc_attr_e( 'Disables navigation on the map. The initial enabled/disabled state of the Map type control:', CFGP_NAME ); ?></label><br />
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'navigation' ) . '_true' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'navigation' ) ); ?>" type="radio" value="1" <?php checked( $navigation, '1', true ); ?>><?php _e( 'Enable', CFGP_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'navigation' ) . '_false' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'navigation' ) ); ?>" type="radio" value="0" <?php checked( $navigation, '0', true ); ?>><?php _e( 'Disable', CFGP_NAME ); ?>
        </p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'map_type_control' ) ); ?>"><?php esc_attr_e( 'The initial state of the Map type control:', CFGP_NAME ); ?></label><br />
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'map_type_control' ) . '_true' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'map_type_control' ) ); ?>" type="radio" value="1" <?php checked( $map_type_control, '1', true ); ?>><?php _e( 'Enable', CFGP_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'map_type_control' ) . '_false' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'map_type_control' ) ); ?>" type="radio" value="0" <?php checked( $map_type_control, '0', true ); ?>><?php _e( 'Disable', CFGP_NAME ); ?>
        </p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'scale_control' ) ); ?>"><?php esc_attr_e( 'The initial display options for the Scale control:', CFGP_NAME ); ?></label><br />
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'scale_control' ) . '_true' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'scale_control' ) ); ?>" type="radio" value="1" <?php checked( $scale_control, '1', true ); ?>><?php _e( 'Enable', CFGP_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'scale_control' ) . '_false' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'scale_control' ) ); ?>" type="radio" value="0" <?php checked( $scale_control, '0', true ); ?>><?php _e( 'Disable', CFGP_NAME ); ?>
        </p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'dragable' ) ); ?>"><?php esc_attr_e( 'Dragable - If disabled, the object can be dragged across the map and the underlying feature will have its geometry updated:', CFGP_NAME ); ?></label><br />
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'dragable' ) . '_true' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'dragable' ) ); ?>" type="radio" value="1" <?php checked( $dragable, '1', true ); ?>><?php _e( 'Enable', CFGP_NAME ); ?>&nbsp;&nbsp;&nbsp;
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'dragable' ) . '_false' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'dragable' ) ); ?>" type="radio" value="0" <?php checked( $dragable, '0', true ); ?>><?php _e( 'Disable', CFGP_NAME ); ?>
        </p>
        <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'info_max_width' ) ); ?>"><?php esc_attr_e( 'Info box max width:', CFGP_NAME ); ?></label> 
		    <input id="<?php echo esc_attr( $this->get_field_id( 'info_max_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'info_max_width' ) ); ?>" type="text" value="<?php echo esc_attr( $info_max_width ); ?>"><br><?php _e('Maximum width of info popup inside map (integer from 0 to 600).', CFGP_NAME); ?>
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
		$CFGEO = $GLOBALS['CFGEO'];

		$instance = array();
		$instance['title'] = isset( $new_instance['title'] ) ? $new_instance['title'] : esc_html__( 'Our location', CFGP_NAME );
		$instance['latitude'] = '';
		if( isset( $new_instance['latitude'] ) && !empty( $new_instance['latitude'] ) )
		{
			$instance['latitude'] = $new_instance['latitude'];
		}
		elseif( isset( $CFGEO['latitude'] ) )
		{
			$instance['latitude'] = $CFGEO['latitude'];
		}
		$instance['longitude'] = '';
		if( isset( $new_instance['longitude'] ) && !empty( $new_instance['longitude'] ) )
		{
			$instance['longitude'] = $new_instance['longitude'];
		}
		elseif( isset( $CFGEO['longitude'] ) )
		{
			$instance['longitude'] = $CFGEO['longitude'];
		}
		$instance['width'] = isset( $new_instance['width'] ) && !empty( $new_instance['width'] )  ? $new_instance['width'] : '100%';
		$instance['height'] = isset( $new_instance['height'] ) && !empty( $new_instance['height'] )  ? $new_instance['height'] : '100%';
		$instance['max_zoom'] = isset( $new_instance['max_zoom'] ) ? $new_instance['max_zoom'] : 8;
		$instance['zoom'] = isset( $new_instance['zoom'] ) ? $new_instance['zoom'] : '1';
		$instance['navigation'] = isset( $new_instance['navigation'] ) ? $new_instance['navigation'] : '1';
		$instance['map_type_control'] = isset( $new_instance['map_type_control'] ) ? $new_instance['map_type_control'] : '1';
		$instance['scale_control'] = isset( $new_instance['scale_control'] ) ? $new_instance['scale_control'] : '1';
		$instance['dragable'] = isset( $new_instance['dragable'] ) ? $new_instance['dragable'] : '1';
		$instance['info_max_width'] = isset( $new_instance['info_max_width'] ) ? $new_instance['info_max_width'] : '1';

		return $instance;
	}
}
endif;