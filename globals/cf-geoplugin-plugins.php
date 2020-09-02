<?php
/*
 * Plugin integrations
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since 7.5.7
*/


// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Lets use debug
$debug = $GLOBALS['debug'];

// Include important function
if(!function_exists('is_plugin_active'))
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Include WooCommerce integratin
if( file_exists( CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php' ) )
{
	if( is_plugin_active('woocommerce/woocommerce.php') )
	{
		include_once CFGP_INCLUDES . '/plugins/woocommerce/woocommerce.php';
		new CF_Geoplugin_Woocommerce;
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'WooCommerce integration loaded' );

		// Include CF_Geoplugin_Wooplatnica integration
		if( file_exists( CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php' ) )
		{
			if( is_plugin_active('wooplatnica/wooplatnica.php') )
			{
				include_once CFGP_INCLUDES . '/plugins/wooplatnica/wooplatnica.php';
				new CF_Geoplugin_Wooplatnica;
				if($debug && property_exists($debug, 'save'))
					$debug->save( 'Wooplatnica integration loaded' );
			}
			else
			{
				if($debug && property_exists($debug, 'save'))
					$debug->save( 'Wooplatnica integration not loaded - Plugin does not exists' );
			}
		}
		else
		{
			if($debug && property_exists($debug, 'save'))
				$debug->save(  'Wooplatnica integration not loaded - File does not exists' );
		}		
	}
	else
	{
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'WooCommerce integration not loaded - Plugin does not exists' );
	}
}
else
{
	if($debug && property_exists($debug, 'save'))
		$debug->save(  'WooCommerce integration not loaded - File does not exists' );
}

// Include CF_Geoplugin_Monarch integration
if( file_exists( CFGP_INCLUDES . '/plugins/monarch/monarch.php' ) )
{
	if( is_plugin_active('monarch/monarch.php') )
	{
		include_once CFGP_INCLUDES . '/plugins/monarch/monarch.php';
		new CF_Geoplugin_Monarch;
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Monarch integration loaded' );
	}
	else
	{
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Monarch integration not loaded - Plugin does not exists' );
	}
}
else
{
	if($debug && property_exists($debug, 'save'))
		$debug->save(  'Monarch integration not loaded - File does not exists' );
}

// Include CF_Geoplugin_Contact_Form_7 integration
if( file_exists( CFGP_INCLUDES . '/plugins/contact-form-7/contact-form-7.php' ) )
{
	if( is_plugin_active('contact-form-7/wp-contact-form-7.php') )
	{
		include_once CFGP_INCLUDES . '/plugins/contact-form-7/contact-form-7.php';
		new CF_Geoplugin_Contact_Form_7;
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Contact Form 7 integration loaded' );
	}
	else
	{
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Contact Form 7 integration not loaded - Plugin does not exists' );
	}
}
else
{
	if($debug && property_exists($debug, 'save'))
		$debug->save(  'Contact Form 7 integration not loaded - File does not exists' );
}


// Include Elementor integration
if( file_exists( CFGP_INCLUDES . '/plugins/elementor/elementor.php' ))
{
	if( is_plugin_active('elementor/elementor.php') || is_plugin_active('elementor-pro/elementor-pro.php') )
	{
		include_once CFGP_INCLUDES . '/plugins/elementor/elementor.php';
		CF_Geoplugin_Elementor::instance();
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Elementor integration loaded' );
	}
	else
	{
		if($debug && property_exists($debug, 'save'))
			$debug->save( 'Elementor integration not loaded - Plugin does not exists' );
	}
}
else
{
	if($debug && property_exists($debug, 'save'))
		$debug->save(  'Elementor integration not loaded - File does not exists' );
}