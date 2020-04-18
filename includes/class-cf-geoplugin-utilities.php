<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Utilities Class
 *
 * @since      8.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Utilities')) :
class CF_Geoplugin_Utilities extends CF_Geoplugin_Global
{
	public function run() {
		/* WP Title */
		$this->add_filter('pre_get_document_title', 'the_title', 9999, 1);
		$this->add_filter('wp_title', 'the_title', 9999, 1);
		
		/* 
		 * Complete WP and Theme
		 *
		 * NOTE: This replace tags in complete template but problem is that Gutemberg made a error 
		 * and save current geo data in the database avoiding to save shortcode. Will keep this commented
		*/
	//	$this->add_action('wp_loaded', 'output_buffer_start');
	//	$this->add_action('shutdown', 'output_buffer_end');

		/* Content */
		$this->add_action('the_content', 'replace_tags_cache_control', 9999, 1);
		$this->add_action('the_title', 'replace_tags', 9999, 1);
		$this->add_action('the_slug', 'replace_tags', 9999, 1);
		$this->add_filter('single_term_title', 'replace_tags', 9999, 1 );
		$this->add_filter('widget_text', 'replace_tags', 9999, 1 );
		$this->add_filter('widget_title', 'replace_tags', 9999, 1 );
		
		/* Yoast SEO */
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
			$this->add_action('wpseo_register_extra_replacements', 'wpseo_register_extra_replacements', 99);
			$this->add_filter('wpseo_title', 'the_title', 9999, 1);
		}

		/* SEO Framework */
		if ( is_plugin_active( 'autodescription/autodescription.php' )) {
			$this->add_filter( 'the_seo_framework_title_from_custom_field', 'the_title', 9999, 1);
			$this->add_filter( 'the_seo_framework_title_from_generation', 'the_title', 9999, 1);
			$this->add_filter( 'the_seo_framework_use_title_branding', 'the_title', 9999, 1);
			
			$this->add_filter( 'the_seo_framework_custom_field_description', 'the_title', 9999, 1);
			$this->add_filter( 'the_seo_framework_generated_description', 'the_title', 9999, 1);
		}
		/* All in One SEO Pack */
		if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) || is_plugin_active( 'all-in-one-seo-pack-pro/all_in_one_seo_pack_pro.php' ) ) {
			$this->add_filter( 'aioseop_attachment_title', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_archive_title', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_home_page_title', 'the_title', 9999, 1);
			$this->add_filter( 'single_post_title', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_title_page', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_title', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_title_single', 'the_title', 9999, 1);
			$this->add_filter( 'aiosp_sitelinks_search_box', 'the_title', 9999, 1);
			
			$this->add_filter( 'aioseop_description', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_description_override', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_description_attributes', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_description_full', 'the_title', 9999, 1);
			
			$this->add_filter( 'aioseop_keywords_attributes', 'the_title', 9999, 1);
			$this->add_filter( 'aioseop_keywords', 'the_title', 9999, 1);
		}
		
		/* WordPress SEO Plugin – Rank Math */
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' )) {
			$this->add_filter( 'rank_math/frontend/title', 'the_title', 9999, 1);
			$this->add_filter( 'rank_math/frontend/description', 'the_title', 9999, 1);
			$this->add_action( 'rank_math/frontend/description', 'the_title', 9999, 1);
			$this->add_filter( 'rank_math/settings/titles/link_suggestions', 'the_title', 9999, 1);
			$this->add_filter( 'rank_math/frontend/breadcrumb/html', 'the_title', 9999, 1);
			$this->add_action( 'rank_math/review/text', 'the_title', 9999, 1);
			$this->add_action( 'rank_math/frontend/keywords', 'the_title', 9999, 1);
			$this->add_action( 'rank_math/review/html', 'the_title', 9999, 1);
			$this->add_action( 'frontend/seo_score/html', 'the_title', 9999, 1);
			$this->add_action( 'rank_math/frontend/rss/after_content', 'the_title', 9999, 1);
		}
	}
	
	public function wpseo_register_extra_replacements(){
		
		if(!function_exists('wpseo_register_var_replacement'))
		{
			if(file_exists(dirname(CFGP_ROOT).'/wordpress-seo/inc/class-wpseo-replace-vars.php'))
			{
				include_once(dirname(CFGP_ROOT).'/wordpress-seo/inc/class-wpseo-replace-vars.php');
			}
			else if(file_exists(dirname(CFGP_ROOT).'/wordpress-seo-premium/inc/class-wpseo-replace-vars.php'))
			{
				include_once(dirname(CFGP_ROOT).'/wordpress-seo-premium/inc/class-wpseo-replace-vars.php');
			}
			else return;
		}
		
		if(!function_exists('wpseo_register_var_replacement')) return;
		
		$CFGEO = $GLOBALS['CFGEO'];
		if(is_array($CFGEO))
		{
			$collection = array(); $i=0;
			
			foreach($CFGEO as $key => $val){
				wpseo_register_var_replacement( '%%' . $key . '%%', function() use ($val){
					return $val;
				}, 'advanced', $key );
			};		
		}
	}
	
	public function the_title ( $title ){
		return $this->replace_tags($title);
	}
	
	public function output_callback($buffer) {
		return $this->replace_tags($buffer);
	}
	
	public function replace_tags_cache_control ( $string ){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		$CFGEO = $GLOBALS['CFGEO'];
		if(!empty($string) && !is_array($string) && is_array($CFGEO))
		{
			$collection = array(); $i=0;
			
			foreach($CFGEO as $key => $val){
				$collection[0][$i] = '/%%' . $key . '%%/i';
				$collection[1][$i] = ((isset($CF_GEOPLUGIN_OPTIONS['enable_cache']) && $CF_GEOPLUGIN_OPTIONS['enable_cache'] == 1) ? '<!-- ' . W3TC_DYNAMIC_SECURITY . ' mfunc -->' . $val . '<!-- /mfunc ' . W3TC_DYNAMIC_SECURITY . ' -->' : $val);
				++$i;
			};
			
			$string = preg_replace($collection[0], $collection[1], $string);
		}
		
		return $string;
	}
	
	public function replace_tags ( $string ){
		$CFGEO = $GLOBALS['CFGEO'];
		if(!empty($string) && !is_array($string) && is_array($CFGEO))
		{
			$collection = array(); $i=0;
			
			foreach($CFGEO as $key => $val){
				$collection[0][$i] = '/%%' . $key . '%%/i';
				$collection[1][$i] = $val;
				++$i;
			};
			
			$string = preg_replace($collection[0], $collection[1], $string);
		}
		
		return $string;
	}
	
	public function output_buffer_start() { 
		ob_start(array(&$this, "output_callback")); 
	}
	
	public function output_buffer_end() { 
		ob_get_clean(); 
	}
}
endif;