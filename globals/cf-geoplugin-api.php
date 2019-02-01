<?php
/*
 * Allow developers to use plugin data inside PHP
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 5.0.0
 * @improved 7.0.0
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists( 'CF_Geoplugin' ) && class_exists( 'CF_Geoplugin_API' )) :
	final class CF_Geoplugin
	{
		private $int;
		
		function __construct($options = array()){
			$api = new CF_Geoplugin_API;
			$this->int = $api->run($options);
		}
		
		public function get(){
			return (object) $this->int;
		}
	}
endif;
