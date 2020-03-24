<?php
/**
 * Covid 19 tracker
 *
 * @since      7.9.9
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_Covid_19')) :
class CF_Geoplugin_Covid_19 extends CF_Geoplugin_Global
{
	private $cache_hours = 1;
	private $URL = 'https://thevirustracker.com';
	
	private $covid19_global_statistic = array();
	
	private static $default_fields = array(
		'covid19_total_cases' 				=> 0,
		'covid19_total_recovered' 			=> 0,
		'covid19_total_unresolved' 			=> 0,
		'covid19_total_deaths' 				=> 0,
		'covid19_total_new_cases_today' 	=> 0,
		'covid19_total_new_deaths_today' 	=> 0,
		'covid19_total_active_cases'		=> 0,
		'covid19_total_serious_cases'		=> 0,
	);
	
	private static $default_global_fields = array(
		'covid19_global_total_cases' 				=> 0,
		'covid19_global_total_recovered' 			=> 0,
		'covid19_global_total_unresolved' 			=> 0,
		'covid19_global_total_deaths' 				=> 0,
		'covid19_global_total_new_cases_today' 		=> 0,
		'covid19_global_total_new_deaths_today' 	=> 0,
		'covid19_global_total_active_cases'			=> 0,
		'covid19_global_total_serious_cases'		=> 0,
	);
	
	public function run(){
		if(CF_Geoplugin_Global::get_the_option('covid19', 1))
		{
			$this->add_filter('cf_geoplugin_api_response', 'set_response', 2);
			$this->add_filter('cf_geoplugin_api_response', 'set_response_global', 2);
			$this->add_action('page-cf-geoplugin-tab', 'cf_geoplugin_tab');
			$this->add_action('page-cf-geoplugin-tab-panel', 'cf_geoplugin_tab_panel');
			$this->add_action('page-cf-geoplugin-tag-table-end', 'cf_geoplugin_tag_table_end');
		}
	}
	
	public function cf_geoplugin_tab ()
	{ ?>
		<li class="nav-item">
			<a class="nav-link text-dark" href="#covid19" role="tab" data-toggle="tab"><span class="fa fa-sun-o"></span> <?php _e('COVID-19',CFGP_NAME); ?></a>
		</li>
	<?php }
	
	public function cf_geoplugin_tab_panel ()
	{
		$CFGEO = $GLOBALS['CFGEO'];
		?>
		<div role="tabpanel" class="tab-pane fade pt-3" id="covid19">
			<h3 class="ml-3 mr-3"><?php _e('Coronavirus outbreak tracker',CFGP_NAME); ?></h3>
			<p class="ml-3 mr-3"><?php _e('You can display global or country based informations of the Coronavirus (COVID-19).',CFGP_NAME); ?></p>
			<table width="100%" class="table table-striped table-sm">
				<thead>
					<tr>
						<th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
						<th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
					</tr>
				</thead>
				<tbody>
				<?php $test = array_keys( array_merge(self::$default_fields, self::$default_global_fields) ); foreach($CFGEO as $key => $val) : if(!in_array($key, $test)) continue;  ?>
					<tr>
						<td><kbd>[cfgeo_<?php echo $key; ?>]</kbd></td>
						<td><?php echo $val; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<thead>
					<tr>
						<th class="manage-column column-shortcode column-primary" width="40%"><strong><?php _e('Shortcode',CFGP_NAME); ?></strong></th>
						<th class="manage-column column-returns column-primary"><strong><?php _e('Returns',CFGP_NAME); ?></strong></th>
					</tr>
				</thead>
			</table>
		</div>
	<?php }
	
	public function cf_geoplugin_tag_table_end ()
	{
		$CFGEO = $GLOBALS['CFGEO'];
		$test = array_keys( array_merge(self::$default_fields, self::$default_global_fields) ); foreach($CFGEO as $key => $val) : if(!in_array($key, $test)) continue; ?>
		<tr>
			<td><kbd>%%<?php echo $key; ?>%%</kbd></td>
			<td><?php echo $val; ?></td>
		</tr>
	<?php endforeach;
	}
	
	public function set_response ( $data, $default = array() )
	{
		if (version_compare(PHP_VERSION, '7.0.0', '>='))
		{
			$data=array_replace($data, self::$default_fields);
		}
		else
		{
			$data=array_merge($data, self::$default_fields);
		}
		
		if(empty($data))
			return $data;
		if(!isset($data['country_code']))
			return $data;
		if(empty($data['country_code']))
			return $data;
		
		if($statistic = $this->get_country_statistic($data['country_code']))
		{
			if (version_compare(PHP_VERSION, '7.0.0', '>='))
			{
				$data=array_replace($data, $statistic);
			}
			else
			{
				$data=array_merge($data, $statistic);
			}
		}
		
		return $data;
	}
	
	public function set_response_global ( $data, $default = array() )
	{
		if (version_compare(PHP_VERSION, '7.0.0', '>='))
		{
			$data=array_replace($data, self::$default_global_fields);
		}
		else
		{
			$data=array_merge($data, self::$default_global_fields);
		}
		
		if($statistic = $this->get_global_statistic())
		{
			if (version_compare(PHP_VERSION, '7.0.0', '>='))
			{
				$data=array_replace($data, $statistic);
			}
			else
			{
				$data=array_merge($data, $statistic);
			}
		}
		
		return $data;
	}
	
	public function get_global_statistic()
	{
		// delete_transient("cfgp-covid19-global-statistic");
		
		if(!empty($this->covid19_global_statistic))
			return apply_filters( 'cf_geoplugin_covid19_global_statistic', $this->covid19_global_statistic, self::$default_global_fields);
		
		if( $cache = get_transient('cfgp-covid19-global-statistic') )
		{
			$this->covid19_global_statistic = $cache;
			return apply_filters( 'cf_geoplugin_covid19_global_statistic', $this->covid19_global_statistic, self::$default_global_fields);
		}
		else
		{
			$response = $this->curl_get($this->URL . '/free-api?global=stats');
			if(!empty($response))
			{
				$response = json_decode($response, true);
				if(isset($response['results']) && !empty($response['results']) && isset($response['results'][0]))
				{
					if(isset($response['countrynewsitems'])) unset($response['countrynewsitems']);
					if(isset($response['countrydata'][0]['info'])) unset($response['countrydata'][0]['info']);
					if(isset($response['results'][0]['source'])) unset($response['results'][0]['source']);

					$data = array();
					foreach($response['results'][0] as $key => $val)
					{
						if(!in_array("covid19_global_{$key}", array_keys(self::$default_global_fields))) continue;
						$this->covid19_global_statistic["covid19_global_{$key}"]=$val; 
					}
					set_transient('cfgp-covid19-global-statistic', $this->covid19_global_statistic, (HOUR_IN_SECONDS * $this->cache_hours));
					return apply_filters( 'cf_geoplugin_covid19_global_statistic', $this->covid19_global_statistic, self::$default_global_fields);
				}
			}
		}
		return false;
	}
	
	public function get_country_statistic( $country )
	{
		// delete_transient("cfgp-covid19-{$country}-statistic");
		
		if(empty($country)) return false;
		
		if( $cache = get_transient("cfgp-covid19-{$country}-statistic") )
		{
			return apply_filters( "cf_geoplugin_covid19_{$country}_statistic", $cache, self::$default_fields);
		}
		else
		{
			$response = $this->curl_get($this->URL . "/free-api?countryTotal={$country}");
			if(!empty($response))
			{
				$response = json_decode($response, true);
				if(isset($response['countrydata']) && !empty($response['countrydata']) && isset($response['countrydata'][0]))
				{
					if(isset($response['countrynewsitems'])) unset($response['countrynewsitems']);
					if(isset($response['countrydata'][0]['info'])) unset($response['countrydata'][0]['info']);
					
					$data = array();
					foreach($response['countrydata'][0] as $key => $val)
					{
						if(!in_array("covid19_{$key}", array_keys(self::$default_fields))) continue;
						$data["covid19_{$key}"]=$val; 
					}
					
					set_transient("cfgp-covid19-{$country}-statistic", $data, (HOUR_IN_SECONDS * $this->cache_hours));
					return apply_filters( "cf_geoplugin_covid19_{$country}_statistic", $data, self::$default_fields);
				}
			}
		}
		return false;
	}
}
endif;