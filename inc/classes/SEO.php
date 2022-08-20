<?php
/**
 * Main API class
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_SEO')) :
class CFGP_SEO extends CFGP_Global {
	
	public function __construct(){
		$this->add_action('init', 'export_csv');
		$this->add_action('init', 'save_form');
		$this->add_action('wp_ajax_cfgp_seo_redirection_csv_upload', 'ajax__csv_upload');
	}
	
	/*
	 * AJAX: CSV Upload
	 */
	public function ajax__csv_upload(){
		if(wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-import-csv') !== false)
		{
			if($url = CFGP_U::request_string('attachment_url'))
			{
				// Parse CSV
				if(!class_exists('CFGP_CSV')) {
					CFGP_U::include_once(CFGP_CLASS . '/CSV.php');
				}
				
				if($csv = CFGP_CSV::import($url ,false, ','))
				{
					// We need time for this
					if(function_exists('ignore_user_abort')) ignore_user_abort(true);
					if(function_exists('set_time_limit')) set_time_limit(0);
					if(function_exists('ini_set')) ini_set('max_execution_time', 0);
				
					// Active columns in the exact order
					$columns = array('country', 'region', 'city', 'postcode', 'url', 'http_code', 'active', 'only_once');
					$columns_max = count($columns);
					
					// Remove headers
					foreach($csv as $i=>$column){
						foreach($column as $val){
							if(in_array($val, $columns, true) !== false) {
								unset($csv[$i]);
								break;
							}
						}
					}
					
					// Asign fields
					foreach($csv as $i=>$data){
						// We need exact number of columns
						if(count($data) !== $columns_max){
							unset($csv[$i]);
							continue;
						}
						// Now assign data to columns
						foreach($columns as $x=>$column){
							if (filter_var($data[$x], FILTER_VALIDATE_URL) !== false || is_numeric($data[$x])){
								$csv[$i][$column]=CFGP_Options::sanitize($data[$x]);
							} else {
								$csv[$i][$column]=CFGP_Options::sanitize(strtolower(sanitize_title($data[$x])));
							}
							unset($csv[$i][$x]);
						}
					}
					
					// Let's try clean database and add new data
					if(!empty($csv))
					{
						global $wpdb;
						// Define table name
						$table = $wpdb->cfgp_seo_redirection;
						// We need old data prepared to return if we have some error
						$original_data = $wpdb->query("SELECT * FROM `{$table}` WHERE 1;");
						// Let's clean table
						$wpdb->query("TRUNCATE TABLE {$table};");
						// Now we can save new data
						$num_saved=0;
						foreach($csv as $save) {
							if($wpdb->insert($table, $save, array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'))){
								++$num_saved;
							}
						}
						// Validate
						if($num_saved > 0)
						{
						//	CFGP_DB_Cache::set('cfgp-seo-form-success', __('CSV uploaded successfully.', 'cf-geoplugin'), YEAR_IN_SECONDS);
							
							wp_send_json(array(
								'return'=>true,
								'message' => __('An error occurred while saving data to the database. We have restored your old parameters. There is no change here.', 'cf-geoplugin')
							));
						}
						else
						{
							// We need old data back on the error
							if(!empty($original_data)) {
								foreach($original_data as $data){
									$wpdb->insert($table, $data);
								}
							}
							
							wp_send_json(array(
								'return'=>false,
								'message' => __('An error occurred while saving data to the database. We have restored your old parameters. There is no change here.', 'cf-geoplugin')
							));
						}
					}
					else
					{
						wp_send_json(array(
							'return'=>false,
							'message' => sprintf(__('We did not find any valid CSV data. We did not make any changes. Make sure you have all %d columns properly defined.', 'cf-geoplugin'), $columns_max)
						));
					}
				}
				else
				{
					wp_send_json(array(
						'return'=>false,
					'message' => __('There was an error unpacking the CSV file. Check the format of your CSV file.', 'cf-geoplugin')
					));
				}
			}
			else
			{
				wp_send_json(array(
					'return'=>false,
					'message' => __('CSV file not defined.', 'cf-geoplugin')
				));
			}
		}
		else
		{
			wp_send_json(array(
				'return'=>false,
				'message' => __('There was an error validating the data. Please refresh the page and try again.', 'cf-geoplugin')
			));
		}
	}
	
	/*
	 * Edit/Save form
	 */
	public function save_form(){
		if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			if (CFGP_U::request_string('action') == 'new' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-new') !== false) {
				$action = CFGP_U::request_string('action');
				$redirection_url = CFGP_U::request_string('url');
			
				if(empty($redirection_url)) {
					CFGP_DB_Cache::set('cfgp-seo-form-error', __('Redirect URL is a required field.', 'cf-geoplugin'), YEAR_IN_SECONDS);
				}
				else
				{
					$select_country = strtolower(sanitize_title(CFGP_U::request_string('country')));
					$select_region = strtolower(sanitize_title(CFGP_U::request_string('region')));
					$select_city = strtolower(sanitize_title(CFGP_U::request_string('city')));
					$select_postcode = CFGP_U::request_string('postcode');
					$http_code = CFGP_U::request_string('http_code', 302);
					$only_once = CFGP_U::request_int('only_once', 0);
					$redirect_enable = CFGP_U::request_int('redirect_enable', 1);
					
					$save = CFGP_SEO::save($redirection_url, $select_country, $select_region, $select_city, $select_postcode, $http_code, $only_once, $redirect_enable);
					
					if(is_wp_error($save))
					{
						CFGP_DB_Cache::set('cfgp-seo-form-error', __('This redirection was not saved due to a database error. Please try again.', 'cf-geoplugin'), YEAR_IN_SECONDS);
					}
					else
					{
						CFGP_DB_Cache::set('cfgp-seo-form-success', __('Settings saved.', 'cf-geoplugin'), YEAR_IN_SECONDS);
						
						$parse_url = CFGP_U::parse_url();
						$url = $parse_url['url'];
						
						$url = remove_query_arg('action', $url);
						$url = remove_query_arg('nonce', $url);
						
						if(!headers_sent()) {
							wp_safe_redirect($url);
						} else {
							echo '
							<meta http-equiv="refresh" content="0; URL='.$url.'" />
							<script>if(!(window.location.href = "'.$url.'")){window.location.replace("'.$url.'");}</script>
							';
							exit;
						}
					}
				}
			} else if (CFGP_U::request_string('action') == 'edit' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-edit') !== false) {
				$redirection_url = CFGP_U::request_string('url');
				
				if(empty($redirection_url)) {
					CFGP_DB_Cache::set('cfgp-seo-form-error', __('Redirect URL is a required field.', 'cf-geoplugin'), YEAR_IN_SECONDS);
				}
				else
				{
					$ID = CFGP_U::request_int('id', 0);
					$select_country = strtolower(sanitize_title(CFGP_U::request_string('country')));
					$select_region = strtolower(sanitize_title(CFGP_U::request_string('region')));
					$select_city = strtolower(sanitize_title(CFGP_U::request_string('city')));
					$select_postcode = CFGP_U::request_string('postcode');
					$http_code = CFGP_U::request_string('http_code', 302);
					$only_once = CFGP_U::request_int('only_once', 0);
					$redirect_enable = CFGP_U::request_int('redirect_enable', 1);
					
					$save = CFGP_SEO::update($ID, $redirection_url, $select_country, $select_region, $select_city, $select_postcode, $http_code, $only_once, $redirect_enable);
					
					if(is_wp_error($save))
					{
						CFGP_DB_Cache::set('cfgp-seo-form-error', __('This redirection was not saved due to a database error. Please try again.', 'cf-geoplugin'), YEAR_IN_SECONDS);
					}
					else
					{
						CFGP_DB_Cache::set('cfgp-seo-form-success', __('Settings saved.', 'cf-geoplugin'), YEAR_IN_SECONDS);
					}
				}
			}
		}
		
		if(CFGP_U::request_string('action') === 'delete' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-delete') !== false)
		{
			$ID = CFGP_U::request_int('id', 0);
			$delete = CFGP_SEO::delete($ID);
			
			$parse_url = CFGP_U::parse_url();
			$url = $parse_url['url'];
			
			$url = remove_query_arg('action', $url);
			$url = remove_query_arg('nonce', $url);
			$url = remove_query_arg('id', $url);
			
			CFGP_DB_Cache::set('cfgp-seo-form-success', __('Deleted.', 'cf-geoplugin'), YEAR_IN_SECONDS);
			
			if(!headers_sent()) {
				wp_safe_redirect($url);
			} else {
				echo '
				<meta http-equiv="refresh" content="0; URL='.$url.'" />
				<script>if(!(window.location.href = "'.$url.'")){window.location.replace("'.$url.'");}</script>
				';
				exit;
			}
		}
	}
	
	/*
	 * CSV Download
	 */
	public function export_csv(){
		if(CFGP_U::request_string('action') === 'export' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-export-csv') !== false){
			// We need time for this
			if(function_exists('ignore_user_abort')) ignore_user_abort(true);
			if(function_exists('set_time_limit')) set_time_limit(0);
			if(function_exists('ini_set')) ini_set('max_execution_time', 0);
					
			global $wpdb;
			$table = $wpdb->cfgp_seo_redirection;
			$result = $wpdb->get_results("SELECT country, region, city, postcode, url, http_code, active, only_once FROM {$table} WHERE 1", ARRAY_A);
			
			$num_fields = count($result); 
			$headers = []; 
			foreach($result[0] as $header => $value) 
			{     
				$headers[] = $header; 
			} 
			$fp = fopen('php://output', 'w'); 
			if ($fp && $result) 
			{     
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="cfgeo_seo_export_'.date('Y-m-d').'_'.CFGP_TIME.'.csv"');
				header('Pragma: no-cache');
				header('Expires: 0');
				fputcsv($fp, $headers); 
				foreach($result as $i => $row) 
				{
					fputcsv($fp, $row); 
				}
				fclose($fp);
				exit;
			}
		}
	}
	
	// Response Error
	public static function response_error(){
		$response = CFGP_DB_Cache::get('cfgp-seo-form-error');
		if($response) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				$response
			);
			CFGP_DB_Cache::delete('cfgp-seo-form-error');
		}
	}
	
	// Response Success
	public static function response_success(){
		$response = CFGP_DB_Cache::get('cfgp-seo-form-success');
		if($response) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				$response
			);
			CFGP_DB_Cache::delete('cfgp-seo-form-success');
		}
	}
	
	/*
	 * Get from the database by ID
	 */
	public static function get($ID){
		global $wpdb;
		$get = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->cfgp_seo_redirection} WHERE ID = %d",
			$ID
		));
		return isset($get->ID) ? $get : false;
	}
	
	/*
	 * Save SEO redirection to the Database
	 */
	public static function save($url, $country = '', $region = '', $city = '', $postcode = '', $http_code = 302, $only_once = 0, $active = 1){
		global $wpdb;
		return $wpdb->insert(
			$wpdb->cfgp_seo_redirection,
			array(
				'url' => $url,
				'country' => $country,
				'region' => $region,
				'city' => $city,
				'postcode' => $postcode,
				'http_code' => $http_code,
				'only_once' => $only_once,
				'active' => $active
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d'
			)
		);
	}
	
	/*
	 * Delete SEO redirection from the Database
	 */
	public static function delete($ID){
		global $wpdb;
		return $wpdb->delete(
			$wpdb->cfgp_seo_redirection,
			array(
				'ID' => $ID
			),
			array(
				'%d'
			)
		);
	}
	
	/*
	 * Update SEO redirection in the Database
	 */
	public static function update($ID, $url, $country = '', $region = '', $city = '', $postcode = '', $http_code = 302, $only_once = 0, $active = 1){
		global $wpdb;
		return $wpdb->update(
			$wpdb->cfgp_seo_redirection,
			array(
				'url' => $url,
				'country' => $country,
				'region' => $region,
				'city' => $city,
				'postcode' => $postcode,
				'http_code' => $http_code,
				'only_once' => $only_once,
				'active' => $active
			),
			array(
				'ID' => $ID
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d'
			),
			array(
				'%d'
			)
		);
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