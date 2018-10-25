<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * SEO Redirections
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @edited     Ivijan-Stefan Stipic
 */

if( !class_exists( 'CF_Geoplugin_SEO_Redirection' ) ) :
class CF_Geoplugin_SEO_Redirection extends CF_Geoplugin_Global
{
    public function __construct()
    {
		$this->add_action( 'template_redirect', 'page_seo_redirection');
		$this->add_action( 'template_redirect', 'wp_seo_redirection');
	}
	
	// Page SEO Redirection
	public function page_seo_redirection(){
		$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if(!is_admin() && $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
		{
			$enable_seo 	= $this->get_post_meta('seo_redirect');
			if($enable_seo)
			{
				$redirect = array(
					'country'	=> strtolower( $this->get_post_meta('country') ),
					'region'	=> strtolower( $this->get_post_meta('region') ),
					'city'		=> strtolower( $this->get_post_meta('city') ),
					'url'		=> $this->get_post_meta('redirect_url'),
					'http_code'	=> $this->get_post_meta('http_code')
				);
				
				$this->check_user_redirection( $redirect );
			}
		}
	}
	
	// WordPress SEO Redirection
	public function wp_seo_redirection(){
		global $wpdb; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
		
		if(!is_admin() && $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
		{
			$table_name = self::TABLE['seo_redirection'];
            $redirects = $wpdb->get_results( "SELECT url, LOWER(country) AS country, LOWER(region) AS region, LOWER(city) AS city, http_code FROM {$wpdb->prefix}{$table_name};", ARRAY_A );
			if( $redirects !== NULL && $wpdb->num_rows > 0 && ( isset( $CFGEO['country'] ) || isset( $CFGEO['country_code'] ) ) )
			{
				foreach( $redirects as $redirect )
				{
					$this->check_user_redirection( $redirect );
				}
			}
		}
	}
	
	private function redirect($url, $http_code=302){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_cache'])
		{
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		}
		wp_redirect( $url, $http_code );
		exit;
	}

	private function check_user_redirection( $redirect )
	{
		$CFGEO = $GLOBALS['CFGEO'];
		$country_check = $this->check_user_by_country( $redirect['country'] );
		if(filter_var($redirect['url'], FILTER_VALIDATE_URL) && ( $country_check || empty( $redirect['country'] ) ) )
		{
			if( $this->check_user_by_city( $redirect['city'] ) && ( $this->check_user_by_region( $redirect['region'] ) || empty( $redirect['region'] ) ) )
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( empty( $redirect['city'] ) && $this->check_user_by_region( $redirect['region'] ) ) 
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( empty( $redirect['city'] ) && empty( $redirect['region'] ) && $country_check ) 
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
		}
	}
}
endif;