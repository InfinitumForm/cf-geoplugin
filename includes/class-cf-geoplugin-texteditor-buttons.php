<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Generate Texteditor Shortcode Buttons
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Texteditor_Buttons')) :
class CF_Geoplugin_Texteditor_Buttons extends CF_Geoplugin_Global {
	public function __construct(){
		$this->add_action( 'admin_head', 'print_geoplugin_shortcodes_in_js' );
		$this->add_action( 'admin_head', 'print_geoplugin_banner_shortcodes_in_js' );
		$this->add_action( 'admin_head', 'add_tinymce' );
	}
	
	/*
	 * Add CF Geoplugin Shortcodes
	*/
	public function print_geoplugin_shortcodes_in_js(){
		global $CFGEO;
		$exclude = array_map('trim', explode(',','state,continentCode,areaCode,dmaCode,timezoneName,currencySymbol,currencyConverter,error,status,runtime,error_message'));
		$shortcodes=array(
			'sc' => array(),
			'nm' => array()
		);
		
		foreach($CFGEO as $name=>$value)
		{
			if(in_array($name, $exclude, true) === false){
				$shortcodes['sc'][]='"[cfgeo return=\"'.$name.'\"]"';
				$shortcodes['nm'][]='"' . strtoupper(str_replace('_',' ',$name)) . '"';
			}
		}
		
		$shortcodes['sc'][]='"[cfgeo_flag]"';
		$shortcodes['nm'][]='"COUNTRY FLAG"';
		
		?>
<script type="text/javascript">
/* <![CDATA[ */
	var cfgeo_shortcodes = [<?php echo join(",",$shortcodes['sc']); ?>];
	var cfgeo_shortcode_names = [<?php echo join(",",$shortcodes['nm']); ?>];
/* ]]> */
</script>
		<?php
	}
	
	/*
	 * Add CF Geoplugin Banner Shortcodes
	*/
	public static function print_geoplugin_banner_shortcodes_in_js(){
		$arguments = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	-1,
		  'post_status'		=> 'publish'
		);
		$shortcodes=array();
		$posts = get_posts($arguments);
		
		$shortcodes=array(
			'sc' => array(),
			'nm' => array()
		);
		
		if ( false!==$posts && count($posts)>0 )
		{
			foreach($posts as $post)
			{
				$shortcodes['sc'][]=sprintf('\'[cf_geo_banner title="%s" id="%u"][/cf_geo_banner]\'', esc_attr($post->post_name), $post->ID);
				$shortcodes['nm'][]='"' . esc_attr($post->post_title) . '"';
			}
			wp_reset_postdata();
		}
		?>
<script type="text/javascript">
/* <![CDATA[ */
	var cfgeo_banner_shortcode = [<?php echo join(",",$shortcodes['sc']); ?>];
	var cfgeo_banner_shortcode_names = [<?php echo join(",",$shortcodes['nm']); ?>];
/* ]]> */
</script>
		<?php
	}
	
	public function add_tinymce() {
		$this->add_filter( 'mce_external_plugins', 'add_tinymce_plugin' );
		$this->add_filter( 'mce_buttons', 'add_tinymce_button' );
	}
	 
	public function add_tinymce_plugin($plugin_array) {
		$version = get_bloginfo('version'); 
		if(version_compare($version, '3.9', '<'))
		{
			$plugin_array['cf_geoplugin'] = CFGP_ASSETS . '/js/cf-geo-tnmce.js';
			$plugin_array['cf_geoplugin_banner'] = CFGP_ASSETS . '/js/cf-geo-tnmce-banner.js';
		}
		else
		{
			$plugin_array['cf_geoplugin'] = CFGP_ASSETS . '/js/cf-geo-tnmce-3.9.js';
			$plugin_array['cf_geoplugin_banner'] = CFGP_ASSETS . '/js/cf-geo-tnmce-banner-3.9.js';
		}
		return $plugin_array;
	}
	 
	public function add_tinymce_button($buttons) {
		array_push($buttons, 'cf_geoplugin');
		array_push($buttons, 'cf_geoplugin_banner');
		return $buttons;
	}
}
endif;