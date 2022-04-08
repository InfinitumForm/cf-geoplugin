<?php
/**
 * Return deprecated CF_Geoplugin class to support the older projects
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CF_Geoplugin')) : class CF_Geoplugin{
	// Collect API data
	private $data = NULL;
	
	// Construct API response
	function __construct($options=array()){
		// Fetch new data via API
		if( !empty($options) ) {
			$property = array();
			
			if( isset($options['base_currency']) ){
				$property['base_currency']=$options['base_currency'];
			}
			
			if( isset($options['ip']) && ($NEW_API = CFGP_API::lookup( $options['ip'], $property )) ){
				$this->data = $NEW_API;
			} else {
				$this->data = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
			}
		}
		// Or load default data
		else {
			$this->data = CFGP_U::api(false, CFGP_Defaults::API_RETURN);
		}
	}
	
	// Get API data
	public function get(){
		return ((object)$this->data);
	}
	
} endif;