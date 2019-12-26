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
		
		if((isset($CF_GEOPLUGIN_OPTIONS['redirect_disable_bots']) ? ($CF_GEOPLUGIN_OPTIONS['redirect_disable_bots'] == 1) : false) && parent::is_bot()) return;
		
		$page_id = $this->get_current_page_ID();
		if(!is_admin() && $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
		{
			$redirect_data 	= $this->get_post_meta( 'redirection' );
			if( is_array( $redirect_data ) )
			{
				foreach( $redirect_data as $i => $value )
				{
					if( !isset( $value['seo_redirect'] )) continue;
					if( !$value['seo_redirect'] || $value['seo_redirect'] == 0 ) continue;

					if( !isset( $value['country'] ) ) $value['country'] = NULL;
					if( !isset( $value['region'] ) ) $value['region'] = NULL;
					if( !isset( $value['city'] ) ) $value['city'] = NULL;

					if( !isset( $value['only_once'] ) ) $value['only_once'] = 0;
					
					$value['ID'] = $i;
					$value['page_id'] = $page_id;
					
					$this->do_redirection( $value );
				}
			}

			$old_redirection = array(
				'country',
				'region',
				'city',
				'redirect_url',
				'http_code',
				'seo_redirect',
				'page_id',
				'ID'
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
			$redirection['ID'] = 0;
			$redirection['page_id'] = $page_id;
			if( isset( $redirection['seo_redirect'] ) && $redirection['seo_redirect'] == '1' ) $this->do_redirection( $redirection );
		}
	}
	
	// WordPress SEO Redirection
	public function wp_seo_redirection(){
		global $wpdb; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];
		
		if((isset($CF_GEOPLUGIN_OPTIONS['redirect_disable_bots']) ? ($CF_GEOPLUGIN_OPTIONS['redirect_disable_bots'] == 1) : false) && parent::is_bot()) return;

		if(!is_admin() && $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'])
		{
			$country = (isset($CFGEO['country']) && !empty($CFGEO['country']) ? strtolower($CFGEO['country']) : NULL);
			$country_code = (isset($CFGEO['country_code']) && !empty($CFGEO['country_code']) ? strtolower($CFGEO['country_code']) : NULL);
			
			$region = (isset($CFGEO['region']) && !empty($CFGEO['region']) ? strtolower($CFGEO['region']) : NULL);
			$region_code = (isset($CFGEO['region_code']) && !empty($CFGEO['region_code']) ? strtolower($CFGEO['region_code']) : NULL);
			
			$city = (isset($CFGEO['city']) && !empty($CFGEO['city']) ? strtolower($CFGEO['city']) : NULL);
			
			$where = array();
			
			if($country || $country_code)
			{
				if($country) $where[]=$wpdb->prepare('TRIM(LOWER(country)) = %s', $country);
				if($country_code) $where[]=$wpdb->prepare('TRIM(LOWER(country)) = %s', $country_code);
			}
			
			if($region || $region_code)
			{
				if($region) $where[]=$wpdb->prepare('TRIM(LOWER(region)) = %s', $region);
				if($region_code) $where[]=$wpdb->prepare('TRIM(LOWER(region)) = %s', $region_code);
			}
			
			if($city) $where[]=$wpdb->prepare('TRIM(LOWER(city)) = %s', $city);
			
			if(!empty($where)) {
				$where = ' AND (' . join(' OR ', $where) . ')';
			} else {
				$where = '';
			}
 
			$table_name = self::TABLE['seo_redirection'];
            $redirects = $wpdb->get_results("
			SELECT 
				TRIM(url) AS url,
				TRIM(LOWER(country)) AS country,
				TRIM(LOWER(region)) AS region,
				TRIM(LOWER(city)) AS city,
				http_code AS http_code,
				only_once
			FROM 
				{$wpdb->prefix}{$table_name}
			WHERE
				active = 1{$where}", ARRAY_A );

			if( $redirects !== NULL && $wpdb->num_rows > 0 && ( isset( $CFGEO['country'] ) || isset( $CFGEO['country_code'] ) ) )
			{
				foreach( $redirects as $redirect )
				{
					$this->do_redirection( $redirect );
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
		if (wp_redirect( $url, $http_code )) exit;
	}

	private function do_redirection( $redirect )
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
		
		if( $this->is_object_empty($redirect, 'country') ) $country_empty = true;
		if( $this->is_object_empty($redirect, 'region') ) $region_empty = true;
		if( $this->is_object_empty($redirect, 'city') ) $city_empty = true;

		if( isset( $redirect['url'] ) && filter_var($redirect['url'], FILTER_VALIDATE_URL) && ( $country_check || $country_empty ) )
		{			
			if($this->check_user_by_city( $redirect['city'] ) && ( $this->check_user_by_region( $redirect['region'] ) || $region_empty ) )
			{
				if($this->control_redirection( $redirect )) $this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif($city_empty && $this->check_user_by_region( $redirect['region'] ) ) 
			{
				if($this->control_redirection( $redirect )) $this->redirect( $redirect['url'], $redirect['http_code'] );
			}
			elseif($region_empty && $city_empty && $country_check ) 
			{
				if($this->control_redirection( $redirect )) $this->redirect( $redirect['url'], $redirect['http_code'] );
			}
		}
	}
	
	private function control_redirection( $redirect )
	{		
		if(isset( $redirect['only_once'] ) && $redirect['only_once'] == 1){
			$cookie_name = '__cfgp_seo_' . $redirect['page_id'] . '_once_' . $redirect['ID'];
			
			if(isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])){
				return false;
			} else {
				setcookie( $cookie_name, time() . '_' . (time()+((365 * DAY_IN_SECONDS) * 2)), (time()+((365 * DAY_IN_SECONDS) * 2)), COOKIEPATH, COOKIE_DOMAIN );
			}
		}
		
		return true;
	}
	
	private function is_object_empty($obj,$name){
		return ( ( isset( $obj[$name][0] ) && empty( $obj[$name][0] ) ) || ( empty( $obj[$name] ) ) );
	}
}
endif;