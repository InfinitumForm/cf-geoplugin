<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Generate Texteditor Shortcode Buttons
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
if(!class_exists('CF_Geoplugin_REST')) :
class CF_Geoplugin_REST extends CF_Geoplugin_Global {
	
	private $json = array(
		'error' => true,
		'code' => 400,
		'error_message' => 'Bad Request!'
	);
	
	public function run(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(parent::get_the_option('enable_rest',0))
		{
			if(self::access_level($CF_GEOPLUGIN_OPTIONS['license_sku']) >= 4)
			{
				/*
				 * Request access token
				 * https://somesite.com/wp-admin/admin-ajax.php?action=cf_geoplugin_authenticate
				 *
				 * Accept POST/GET:
				 * @pharam    string  action            -API action
				 * @pharam    string  api_key           -API KEY
				 * @pharam    string  secret_key        -API SECRET KEY
				 * @pharam    string  app_name          -Name of the external app
				 *
				 * JSON:
				 * @return    bool    error             -true/false
				 * @return    string  error_message     -Return only when error exists
				 * @return    int     code              -Allways exists
				 * @return    string  access_token      -Return only when authentication is successful
				 * @return    string  message           -Return only when authentication is successful
				 */
				$this->add_action( 'wp_ajax_cf_geoplugin_authenticate', 'authenticate' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'authenticate' );
				
				/*
				 * Lookup IP address
				 * https://somesite.com/wp-admin/admin-ajax.php?action=cf_geoplugin_lookup
				 *
				 * Accept POST/GET:
				 * @pharam    string  action            -API action
				 * @pharam    string  api_key           -API KEY
				 * @pharam    string  access_token      -API Access token
				 * @pharam    string  ip                -IP
				 * @pharam    string  base_currency     -Base currency
				 *
				 * JSON:
				 * @return    bool    error             -true/false
				 * @return    string  error_message     -Return only when error exists
				 * @return    int     code              -Allways exists
				 * @return    Geo informations
				 */
				$this->add_action( 'wp_ajax_cf_geoplugin_lookup', 'lookup' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup', 'lookup' );
			}
			else
			{
				$this->add_action( 'wp_ajax_cf_geoplugin_authenticate', 'license' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_authenticate', 'license' );
				$this->add_action( 'wp_ajax_cf_geoplugin_lookup', 'license' );
				$this->add_action( 'wp_ajax_nopriv_cf_geoplugin_lookup', 'license' );
			}
		}
		
		$this->add_action( 'wp_ajax_cf_geoplugin_generate_secret_key', 'generate_secret_key' );
		$this->add_action( 'wp_ajax_cf_geoplugin_delete_access_token', 'delete_access_token' );
	}
	
	// Delete Access token
	public function delete_access_token(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(isset($_POST['token']) && !empty($_POST['token']))
		{
			$token = filter_var($_POST['token'], FILTER_SANITIZE_STRING, array('options' => array('default' => false)));
			if(
				isset($CF_GEOPLUGIN_OPTIONS['rest_token_info'][$token]) 
				&& isset($CF_GEOPLUGIN_OPTIONS['rest_token']) 
				&& is_array($CF_GEOPLUGIN_OPTIONS['rest_token']) 
				&& in_array($token, $CF_GEOPLUGIN_OPTIONS['rest_token'], true) !== false
			)
			{				
				if (($key = array_search($token, $CF_GEOPLUGIN_OPTIONS['rest_token'])) !== false) {
					unset($CF_GEOPLUGIN_OPTIONS['rest_token'][$key]);
					unset($CF_GEOPLUGIN_OPTIONS['rest_token_info'][$token]);
					
					$this->update_option('rest_token', $CF_GEOPLUGIN_OPTIONS['rest_token']);
					$this->update_option('rest_token_info', $CF_GEOPLUGIN_OPTIONS['rest_token_info']);
					echo 1; exit;
				}
			}
		}
		echo 0;
		exit;
	}
	
	// Generate REST Secret key
	public function generate_secret_key(){
		$secret_key = $this->generate_token(28);
		$this->update_option('rest_secret', $secret_key);
		
		echo $secret_key;
		exit;
	}
	
	/*
	 * Display License Informations
	 */
	public function license()
	{
		$this->save('error_message','You do not have the appropriate CF GeoPlugin license. REST API is allowed only for the BUSINESS LICENSE.');
		$this->save('code',400);
		$this->json();
		exit;
	}
	
	/*
	 * Get Geo Informations
	 */
	public function lookup()
	{
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		//protect REQUEST
		$allowed = array('api_key', 'access_token', 'ip', 'base_currency');
		$GET = array();
		foreach($_REQUEST as $key=>$val)
		{
			if(!is_array($val) && in_array($key, $allowed))
			{
				$GET[$key] = filter_var($val, FILTER_SANITIZE_STRING, array('options' => array('default' => false)));
			}
		}
		
		// Check and compare keys
		if(isset($GET['api_key']) && isset($GET['access_token']) && $GET['api_key'] !== false && $GET['access_token'] !== false)
		{
			$rest_token = array();
			if(isset($CF_GEOPLUGIN_OPTIONS['rest_token']) && is_array($CF_GEOPLUGIN_OPTIONS['rest_token']))
				$rest_token = $CF_GEOPLUGIN_OPTIONS['rest_token'];
				
			if(in_array($GET['access_token'], $rest_token, true) !== false)
			{
				if($GET['api_key'] === $CF_GEOPLUGIN_OPTIONS['id'])
				{
					if(isset($GET['ip']) && !empty($GET['ip']))
					{
						$GP = new CF_Geoplugin_API;
						
						$pharam = array(
							'ip'    => $GET['ip'],
							'debug' => true
						);
						if(isset($GET['base_currency']) && !empty($GET['base_currency']))
							$pharam['base_currency'] = $GET['base_currency'];
						
						$CFGEO = $GP->run($pharam);
						
						$remove = apply_filters( 'cf_geoplugin_rest_remove_values', array(
							'status',
							'lookup',
							'version',
							'credit',
							'dmaCode',
							'areaCode',
							'continentCode',
							'currencySymbol',
							'currencyConverter'
						));
						
						foreach($CFGEO as $key => $value)
						{
							if(!(in_array($key, $remove, true) !== false))
								$this->save($key, $value);
						}
						
						$this->save('code',$CFGEO['status']);
						
						$this->json();
						exit;
					}
					else
					{
						$this->save('error_message','You must set the "IP" parameter and forward the IP address.');
						$this->save('code',401);
					}
				}
				else
				{
					$this->save('error_message','The API key is not valid. Check your parameters.');
					$this->save('code',401);
				}
			}
			else
			{
				$this->save('error_message','Access token does not match. You must re-authenticate.');
				$this->save('code',401);
			}
		}
		else
		{
			$this->save('error_message','Unauthorized Access.');
			$this->save('code',401);
		}
		
		$this->json();
		exit;
	}
	
	/*
	 * Generate access token
	 */
	public function authenticate(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		
		//protect REQUEST
		$allowed = array('api_key','secret_key','app_name');
		$GET = array();
		foreach($_REQUEST as $key=>$val)
		{
			if(!is_array($val) && in_array($key, $allowed))
			{
				$GET[$key] = filter_var($val, FILTER_SANITIZE_STRING, array('options' => array('default' => false)));
			}
		}
		
		// Check and compare keys
		if(isset($GET['api_key']) && isset($GET['secret_key']) && isset($GET['app_name']) && $GET['app_name'] !== false && $GET['api_key'] !== false && $GET['secret_key'] !== false)
		{
			$rest_secret = array();
			if(isset($CF_GEOPLUGIN_OPTIONS['rest_secret']) && !empty($CF_GEOPLUGIN_OPTIONS['rest_secret']))
				$rest_secret = $CF_GEOPLUGIN_OPTIONS['rest_secret'];
			
			if($GET['api_key'] === $CF_GEOPLUGIN_OPTIONS['id'] && $GET['secret_key'] === $rest_secret)
			{
				// All OK - Generate token
				$token = $this->generate_token(28);
					
				$rest_token = array();
				if(isset($CF_GEOPLUGIN_OPTIONS['rest_token']) && is_array($CF_GEOPLUGIN_OPTIONS['rest_token']))
					$rest_token = $CF_GEOPLUGIN_OPTIONS['rest_token'];
					
				$rest_token_info = array();
				if(isset($CF_GEOPLUGIN_OPTIONS['rest_token_info']) && is_array($CF_GEOPLUGIN_OPTIONS['rest_token_info']))
					$rest_token_info = $CF_GEOPLUGIN_OPTIONS['rest_token_info'];
				
				$this->save('error',false);
				$this->save('error_message',NULL);
				$this->save('code',200);
				$this->save('access_token',$token);
				$this->save('message','Successful Authentication.');
				
				array_push($rest_token, $token);
				$rest_token_info[$token] = array(
					'time' => CFGP_TIME,
					'app_name' => $GET['app_name'],
				);
				
				$this->update_option('rest_token', $rest_token);
				$this->update_option('rest_token_info', $rest_token_info);
				
				$this->json();
				exit;
			}
			else
			{
				$this->save('error_message','API key or Secret key not match! Check your API credentials and try again.');
				$this->save('code',401);
			}
		}
		else
		{
			$this->save('error_message','Unauthorized Access.');
			$this->save('code',401);
		}
		$this->json();
		exit;
	}
	
	
	
	// Save JSON data
	private function save($name, $val){
		$this->json[$name] = $val;
	}
	
	// Print JSON data
	private function json($echo=true){
		header('Content-Type: application/json;charset=utf-8');
		
		$json = $this->json;
		$json = array_filter($json);
		$json = json_encode($this->json);
		if ($json === false) {
			// Avoid echo of empty string (which is invalid JSON), and
			// JSONify the error message instead:
			$json = json_encode(array("error"=>true,"code"=>500,"error_message"=>(!function_exists('json_last_error_msg') ? $this->json_last_error_msg() : json_last_error_msg())));
			if ($json === false) {
				// This should not happen, but we go all the way now:
				$json = '{"error":true,"code":500,"error_message":"unknown"}';
			}
			// Set HTTP response status code to: 500 - Internal Server Error
			if(!function_exists('http_response_code'))
				$this->http_response_code(500);
			else
				http_response_code(500);
		}
		
		if($echo === true)
			echo $json;
		else
			return $json;
	}
	
	/*
	 * PHP HOOK - Returns the error string of the last json_encode() or json_decode() call
	 * @link http://php.net/manual/en/function.json-last-error-msg.php
	*/
	private function json_last_error_msg() {
		static $ERRORS = array(
			JSON_ERROR_NONE => 'No error',
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error',
			JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
		);

		$error = json_last_error();
		return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
	}
	
	/*
	 * PHP HOOK - Get or Set the HTTP response code
	 * @link http://php.net/manual/en/function.http-response-code.php
	*/
	private function http_response_code($code = NULL) {
		if ($code !== NULL) {

			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					exit('Unknown http status code "' . htmlentities($code) . '"');
					break;
			}
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . $code . ' ' . $text);
			$GLOBALS['http_response_code'] = $code;
		} else {
			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
		}
		return $code;
	}
}
endif;