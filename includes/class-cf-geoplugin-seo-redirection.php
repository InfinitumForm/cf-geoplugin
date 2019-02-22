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
		$this->add_action( 'template_redirect', 'page_seo_redirection', 1);
		$this->add_action( 'template_redirect', 'wp_seo_redirection', 1);
	}
	
	// Page SEO Redirection
	public function page_seo_redirection(){
		$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		if(!is_admin() && $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
		{
			$redirect_data 	= $this->get_post_meta( 'redirection' );
			if( is_array( $redirect_data ) )
			{
				foreach( $redirect_data as $i => $value )
				{
					if( !isset( $value['seo_redirect'] ) || $value['seo_redirect'] != '1' ) continue;

					if( !isset( $value['country'] ) ) $value['country'] = NULL;
					if( !isset( $value['region'] ) ) $value['region'] = NULL;
					if( !isset( $value['city'] ) ) $value['city'] = NULL;
					$this->check_user_redirection( $value );
				}
			}

			$old_redirection = array(
				'country',
				'region',
				'city',
				'redirect_url',
				'http_code',
				'seo_redirect',
			);
			$redirection = array();

			foreach( $old_redirection as $i => $meta_key )
			{
				$meta_value = $this->get_post_meta( $meta_key );

				if( $meta_key == 'redirect_url' ) $meta_key = 'url';
				if( $meta_value )
				{
					if( in_array( $meta_key, array( 'country', 'region', 'city' ) ) ) $meta_value = array( $meta_value );

					$redirection[ $meta_key ] = $meta_value;
				}
				else $redirection[ $meta_key ] = NULL;
			}
			if( isset( $redirection['seo_redirect'] ) && $redirection['seo_redirect'] == '1' ) $this->check_user_redirection( $redirection );
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
		
		if(
			isset($redirect['country'])		=== false
			&& isset($redirect['region']) 	=== false
			&& isset($redirect['city']) 	=== false
		) return;
		
		$country_check = $this->check_user_by_country( $redirect['country'] );

		$country_empty = false;
		$region_empty = false;
		$city_empty = false;
		if( ( isset( $redirect['country'][0] ) && empty( $redirect['country'][0] ) ) || ( empty( $redirect['country'] ) ) ) $country_empty = true;
		if( ( isset( $redirect['region'][0] ) && empty( $redirect['region'][0] ) ) || ( empty( $redirect['region'] ) ) ) $region_empty = true;
		if( ( isset( $redirect['city'][0] ) && empty( $redirect['city'][0] ) ) || ( empty( $redirect['city'] ) ) ) $city_empty = true;

		if( isset( $redirect['url'] ) && filter_var($redirect['url'], FILTER_VALIDATE_URL) && ( $country_check || $country_empty ) )
		{
			if( $this->check_user_by_city( $redirect['city'] ) && ( $this->check_user_by_region( $redirect['region'] ) || $region_empty ) )
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( $city_empty && $this->check_user_by_region( $redirect['region'] ) ) 
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif( $region_empty && $city_empty && $country_check ) 
			{
				$this->redirect( $redirect['url'], $redirect['http_code'] );
			}
		}
	}
}
endif;