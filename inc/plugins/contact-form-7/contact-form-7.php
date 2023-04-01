<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Contact Form 7 integration
 *
 * @since      7.9.5
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if( !class_exists( 'CFGP__Plugin__contact_form_7', false ) ):
class CFGP__Plugin__contact_form_7 extends CFGP_Global{
	
	private $remove = 'state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,is_proxy,is_mobile,in_eu,is_vat,gps,error,error_message,lookup,status,runtime,accuracy_radius,credit,official_url,available_lookup,limited,limit,license_hash,request_url';
	private $excluded;
	
	private function __construct(){
		$this->add_filter( 'wpcf7_init', 'add_wpcf7_shortcode', 99, 3 );
		$this->add_action( 'wpcf7_admin_init', 'tag_generator', 999 );
		$this->add_filter( 'wpcf7_form_elements', 'cf7_support', 10 );
	}
	
	/**
	 * Add support for Contact Form 7
	 *
	 * @since    4.0.0
	 */
	public function cf7_support( $form ) {
		return do_shortcode( $form );
	}
	
	/**
	 * Excluded shortcodes
	 *
	 * @since    4.0.0
	 */
	public function get_excluded(){
		if(!$this->excluded) {
			$this->excluded = array_filter(array_map('trim', explode(',', $this->remove)));
		}
		return apply_filters('cfgeo_wpcf7_excluded', $this->excluded);
	}
	
	/**
	 * Contact Form 7 Shortcodes
	 *
	 * @since    4.0.0
	 */
	public function add_wpcf7_shortcode() {
		if(function_exists('wpcf7_add_form_tag'))
		{
			$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
			
			if(empty($CFGEO) || !is_array($CFGEO)) return '';
			
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
					$value = CFGP_U::api($key, NULL);
					
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
	
	/**
	 * Contact Form 7 tags
	 *
	 * @since    4.0.0
	 */
	public function tag_generator() {
		$CFGEO = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		
		$tag_generator = WPCF7_TagGenerator::get_instance();
		
		foreach($CFGEO as $key => $value)
		{
			if( in_array($key, $this->get_excluded()) !== false ) continue;
			
			$tag_generator->add(
				$key,
				$key,
				function ( $contact_form, $args = '' ) {
					$args = wp_parse_args( $args, [] );
				?>
					<div class="control-box">
					<fieldset>
					<legend><?php
						printf(esc_html__( 'To use "%s" tag added by Geo Controller, you need to define tag name and use it into your form. This tag will create hidden input field what will pickup geo information from the your visitor.', 'cf-geoplugin'), '<b>' . $args['id'] . '</b>');
					?></legend>

					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php esc_html_e( 'Name', 'cf-geoplugin'); ?></label></th>
								<td><input type="text" name="name" class="namevalue tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>"></td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php esc_html_e( 'Id attribute', 'cf-geoplugin'); ?></label></th>
								<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php esc_html_e( 'Class attribute', 'cf-geoplugin'); ?></label></th>
								<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
							</tr>
						</tbody>
					</table>

					</fieldset>
					</div>

					<div class="insert-box">
						<input type="text" name="<?php echo esc_attr($args['id']); ?>" class="tag code" readonly onfocus="this.select()" />

						<div class="submitbox">
						<input type="button" class="button button-primary insert-tag" value="<?php
							/* translators: Insert shortcode tag into the page content */
							esc_attr_e( 'Insert Tag', 'cf-geoplugin');
						?>" />
						</div>
					</div>
				<?php
				},
				array( 'nameless' => 1 )
			);
		}

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