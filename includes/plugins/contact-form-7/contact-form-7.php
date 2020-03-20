<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Contact Form 7 integration
 *
 * @since      7.9.5
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CF_Geoplugin_Contact_Form_7' ) ):
class CF_Geoplugin_Contact_Form_7 extends CF_Geoplugin_Global{
	
	private $remove = 'state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,in_eu,is_vat,gps,error,error_message,lookup,status,runtime,accuracy_radius,credit';
	private $excluded;
	
	function __construct(){
		if(parent::get_the_option('enable_cf7', 0))
		{
			$this->add_filter( 'wpcf7_init', 'add_wpcf7_shortcode', 99, 3 );
			$this->add_action( 'wpcf7_admin_init', 'tag_generator', 999 );
		}
	}
	
	function get_excluded(){
		if(!$this->excluded)
			$this->excluded = array_filter(array_map('trim', explode(',',$this->remove)));
		return $this->excluded;
	}
	
	function add_wpcf7_shortcode() {
		if(function_exists('wpcf7_add_form_tag'))
		{
			$CFGEO = $GLOBALS['CFGEO'];
			foreach($CFGEO as $key => $value)
			{
				if( in_array($key, $this->get_excluded()) !== false ) continue;
				
				wpcf7_add_form_tag( $key, function ( $tag )
				{
					$tag = new WPCF7_FormTag( $tag );
					
					if ( empty( $tag->name ) ) {
						return '';
					}
					
					$key = $tag->type;
					$value = (isset($GLOBALS['CFGEO'][$key]) ? $GLOBALS['CFGEO'][$key] : NULL);
					
					$class = wpcf7_form_controls_class( $key );
					
					if(!empty($tag->values))
						$value = join(' ', $tag->values);
					
					$attr = array(
						'name'						=> esc_attr( $tag->name ),
						'value' 					=> esc_attr( $value ),
						'data-cf_geoplugin_key'		=> esc_attr( $key ),
						'id' 						=> esc_attr( "wpcf7-cfgeo-{$tag->name}-{$key}" ),
						'class' 					=> $class . esc_attr( " wpcf7-{$tag->name}-{$key}" )
					);
					
					foreach($tag->options as $i => $option)
					{
						$spl = explode(':', $option);
						$spl = array_map('trim', $spl);
						$spl = array_filter($spl);
						
						if(isset($spl[1]))
							$attr[$spl[0]]=$spl[1];
					}
					
					return sprintf('<input type="hidden" %s>',  wpcf7_format_atts($attr) );
				}, true);
			}
		}
	}
	
	function tag_generator() {
		$CFGEO = $GLOBALS['CFGEO'];
		
		$tag_generator = WPCF7_TagGenerator::get_instance();
		
		foreach($CFGEO as $key => $value)
		{
			if( in_array($key, $this->get_excluded()) !== false ) continue;
			
			$tag_generator->add(
				$key,
				$key,
				function ( $contact_form, $args = '' ) {
					$args = wp_parse_args( $args, array() );
				?>
					<div class="control-box">
					<fieldset>
					<legend><?php
						printf(esc_html__( 'To use "%s" tag added by CF Geo Plugin, you need to define tag name and use it into your form. This tag will create hidden input field what will pickup geo information from the your visitor.', CFGP_NAME ), '<b>' . $args['id'] . '</b>');
					?></legend>

					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php esc_html_e( 'Name', CFGP_NAME ); ?></label></th>
								<td><input type="text" name="name" class="namevalue tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>"></td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php esc_html_e( 'Id attribute', CFGP_NAME ); ?></label></th>
								<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php esc_html_e( 'Class attribute', CFGP_NAME ); ?></label></th>
								<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
							</tr>
						</tbody>
					</table>

					</fieldset>
					</div>

					<div class="insert-box">
						<input type="text" name="<?php echo $args['id']; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

						<div class="submitbox">
						<input type="button" class="button button-primary insert-tag" value="<?php
							/* translators: Insert shortcode tag into the page content */
							esc_attr_e( 'Insert Tag', CFGP_NAME );
						?>" />
						</div>
					</div>
				<?php
				},
				array( 'nameless' => 1 )
			);
		}

	}
}
endif;