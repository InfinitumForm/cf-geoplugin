<?php


add_filter( 'wp_nav_menu_args', function ( $args = [] ) {
	// Uncomment this for debug to find desired menu location
	//	echo '<pre>', var_dump($args), '</pre>';
	
	// If CF Geo Plugin is disabled, prevent errors
	if( !class_exists('CFGP_U') ){
       return $args;
    }
  
	// This retun 2 letter country code from CF Geo Plugin
 	$country_code = strtolower(CFGP_U::api('country_code'));
	
	// Inside dump you need to find 'theme_location' name and setup here
	$theme_location = 'menu-1';
	
	if($args['theme_location'] === $theme_location) {
		/*
		 * Here we are changing the navigation by country.
		 *
		 * How does this work?
		 * 
		 * For each country you need to create in WP Admin -> Apperance -> Menus 
		 * a new menu with Menu Name defined in the variable and put items.
		 * After that, a different menu will be automatically displayed in those countries.
		 *
		 */
		switch ( $country_code ) {
			// Canada               Menu Name: geo-menu-ca
			case 'ca' :
			// Denmark              Menu Name: geo-menu-de
			case 'de' :
			// United States        Menu Name: geo-menu-us
			case 'us' :
				$args['menu'] = "geo-menu-{$country_code}";
				break;
			// and so on...
		}
	}
	
	// Return
	return $args;
}, 10, 1 ); 