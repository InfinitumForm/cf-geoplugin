<?php
/**
 * Class for TinyMce buttons and actions.
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.1.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin/includes
 */
 
class CF_Geoplugin_TinyMce_Shortcodes {
	public function __construct(){
		add_action( 'admin_head', array('CF_Geoplugin_TinyMce_Shortcodes', 'cf_geo_print_shortcodes_in_js') );
		add_action( 'admin_head', array('CF_Geoplugin_TinyMce_Shortcodes', 'cf_geo_add_tinymce') );
	}
	public static function cf_geo_print_shortcodes_in_js(){
		$set=array();
		$gp=new CF_Geoplugin_API($set);
		$gpReturn=$gp->returns;
		foreach(array('state','continentCode','areaCode','dmaCode','timezoneName','currencySymbol','currencyConverter','ip_number') as $rm){
			unset($gpReturn[$rm]);
		}
		$shortcodes=array();
		foreach($gpReturn as $name=>$value)
		{
			$shortcodes[]='"[cf_geo return=\"'.$name.'\"]"';
		}
		?>
		<script type="text/javascript">
			var cf_geo_shortcodes = [<?php echo join(",",$shortcodes); ?>];
		</script>
		<?php
	}
	
	public static function cf_geo_add_tinymce() {
		add_filter( 'mce_external_plugins', array('CF_Geoplugin_TinyMce_Shortcodes', 'cf_geo_add_tinymce_plugin') );
		add_filter( 'mce_buttons', array('CF_Geoplugin_TinyMce_Shortcodes', 'cf_geo_add_tinymce_button') );
	}
	 
	public static function cf_geo_add_tinymce_plugin($plugin_array) {
		$version = get_bloginfo('version'); 
		if($version<3.9)
			$plugin_array['cf_geoplugin'] = plugin_dir_url( 'cf-geoplugin/admin/js/' ) . 'js/cf-geo-tnmce.js';
		else
			$plugin_array['cf_geoplugin'] = plugin_dir_url( 'cf-geoplugin/admin/js/' ) . 'js/cf-geo-tnmce-3.9.js';
			
		return $plugin_array;
	}
	 
	public static function cf_geo_add_tinymce_button($buttons) {
		array_push($buttons, 'cf_geoplugin');
		return $buttons;
	}
}

class CF_Geoplugin_TinyMce_Banners {
	public function __construct(){
		add_action( 'admin_head', array('CF_Geoplugin_TinyMce_Banners', 'cf_geo_print_shortcodes_in_js'));
		add_action( 'admin_head', array('CF_Geoplugin_TinyMce_Banners', 'cf_geo_add_tinymce'));
	}
	public static function cf_geo_print_shortcodes_in_js(){
		$arguments = array(
		  'post_type'		=> 'cf-geoplugin-banner',
		  'posts_per_page'	=>	-1,
		  'post_status'		=> 'publish'
		);
		$shortcodes=array();
		$posts = get_posts($arguments);
		if ( false!==$posts && count($posts)>0 )
		{
			foreach($posts as $post)
			{
				$shortcodes[]= sprintf('\'[cf_geo_banner title="%s" id="%u"][/cf_geo_banner]\'', $post->post_name, $post->ID);
			}
			wp_reset_postdata();
		}
		?>
		<script type="text/javascript">
			var cf_geo_banner_shortcodes = [<?php echo join(",",$shortcodes); ?>];
		</script>
		<?php
	}
	
	public static function cf_geo_add_tinymce() {
		add_filter( 'mce_external_plugins', array('CF_Geoplugin_TinyMce_Banners', 'cf_geo_add_tinymce_plugin') );
		add_filter( 'mce_buttons', array('CF_Geoplugin_TinyMce_Banners', 'cf_geo_add_tinymce_button') );
	}
	 
	public static function cf_geo_add_tinymce_plugin($plugin_array) {
		$version = get_bloginfo('version'); 
		if($version<3.9)
			$plugin_array['cf_geo_banner'] = plugin_dir_url( 'cf-geoplugin/admin/js/' ) . 'js/cf-geo-tnmce-banner.js';
		else
			$plugin_array['cf_geo_banner'] = plugin_dir_url( 'cf-geoplugin/admin/js/' ) . 'js/cf-geo-tnmce-banner-3.9.js';
			
		return $plugin_array;
	}
	 
	public static function cf_geo_add_tinymce_button($buttons) {
		array_push($buttons, 'cf_geo_banner');
		return $buttons;
	}
}