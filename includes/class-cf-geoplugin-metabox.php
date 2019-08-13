<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Metaboxes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 * @edited     Ivijan-Stefan Stipic
 */

if( !class_exists( 'CF_Geoplugin_Metabox' ) ) :
class CF_Geoplugin_Metabox extends CF_Geoplugin_Global
{
    // CF Metaboxes prefix
    private $prefix = '';

    public function __construct()
    {
        $this->prefix = CFGP_METABOX;

        $this->add_action( 'add_meta_boxes', 'create_meta_box' );
        $this->add_action( 'save_post', 'meta_box_save' );
		$this->add_action( 'admin_enqueue_scripts', 'metabox_admin_scripts' );
		
		$this->add_action( 'admin_footer-post-new.php', 'custom_javascript' );
        $this->add_action( 'admin_footer-post.php', 'custom_javascript' );
    }
	
	// Set custom Javascript
	public function custom_javascript(){
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		?>
<script>
/* <![CDATA[ */
(function($){

}(jQuery || window.jQuery));
/* ]]> */
</script>
    <?php }
	
	// Add custom style to metabox
	public function metabox_admin_scripts( $hook_suffix ) {
        $screen = get_current_screen();
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
		if(!$CF_GEOPLUGIN_OPTIONS['enable_seo_redirection']) return;
		if( in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) && isset( $screen->post_type ) && $screen->post_type != 'cf-geoplugin-banner' )
		{            
            wp_register_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), '4.7.0' );
            wp_enqueue_style( CFGP_NAME . '-fontawesome' );
            
            wp_register_style( CFGP_NAME . '-choosen-style', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
			wp_enqueue_style( CFGP_NAME . '-choosen-style' );
			
			wp_register_style( CFGP_NAME . '-meta-box', CFGP_ASSETS . '/css/cf-geoplugin-meta-box.css', array(CFGP_NAME . '-choosen-style'), CFGP_VERSION );
			wp_enqueue_style( CFGP_NAME . '-meta-box' );
			
			wp_register_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
            wp_enqueue_script( CFGP_NAME . '-choosen' );
            
            wp_register_script( CFGP_NAME . '-meta-box', CFGP_ASSETS . '/js/cf-geoplugin-metabox.js', array( 'jquery' ), CFGP_VERSION, true );
            wp_localize_script( 
                CFGP_NAME . '-meta-box', 
                'CFGP_META',
                array(
                    'ajax_url'      		=> self_admin_url( 'admin-ajax.php' ),
                    'no_result'     		=> __( 'Nothing found!',CFGP_NAME ),
                    'remove_redirection'	=> __( 'Remove Redirection', CFGP_NAME ),
                    'add_redirection'       => __( 'Add New Redirection', CFGP_NAME ),
					'reset_redirection'		=> __( 'Reset Redirection', CFGP_NAME )
                )
            );
            wp_enqueue_script( CFGP_NAME . '-meta-box' );
		}
	}

    // Create meta box
    public function create_meta_box()
    {
        $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

        $args = array(
            'public'    => true
        );
        $screens = get_post_types( $args, 'names' );

        if( $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'] )
        {
            if( isset( $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] ) && is_array( $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] ) )
            {		
                foreach( $screens as $page )
                {
                    if(in_array( $page, $CF_GEOPLUGIN_OPTIONS['enable_seo_posts'] ))
                    {
                        add_meta_box(
                            CFGP_NAME . '-seo-redirection',
                            __( 'SEO Redirections', CFGP_NAME ),
                            array( &$this, 'meta_box_seo_redirection' ),
                            $page,
                            'normal',
                            'low'
                        );
                    }
                }
            }
        }
        
        if( isset( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) && is_array( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) )
        {
            foreach( $screens as $page )
            {
                if( in_array( $page, $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) )
                {
                    add_meta_box(
                        CFGP_NAME . '-geo-tags',
                        __( 'Geo Tags', CFGP_NAME ),
                        array( &$this, 'meta_box_geo_tags' ),
                        $page,
                        'normal',
                        'low'
                    );
                }
            }
        }
   
        add_meta_box(
            CFGP_NAME . '-banner-sc',
            __( 'CF Geoplugin Shortcode', CFGP_NAME ),
            array( &$this, 'banner_shortcode' ),
            CFGP_NAME . '-banner',
            'side',
            'high'
        ); 
            
    }
	
	// Add geo tags
	public function meta_box_geo_tags( $post ){ 
    $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS']; $CFGEO = $GLOBALS['CFGEO'];

    $cfgp_enable = get_post_meta( $post->ID, 'cfgp-geotag-enable', true );
    $cfgp_dc_title = get_post_meta( $post->ID, 'cfgp-dc-title', true );
    $cfgp_region = get_post_meta( $post->ID, 'cfgp-region', true );
    $cfgp_placename = get_post_meta( $post->ID, 'cfgp-placename', true );
    $cfgp_latitude = get_post_meta( $post->ID, 'cfgp-latitude', true );
    $cfgp_longitude = get_post_meta( $post->ID, 'cfgp-longitude', true );

    if( empty( $cfgp_dc_title ) && isset( $CFGEO['address'] ) ) $cfgp_dc_title = $CFGEO['address'];
    if( empty( $cfgp_region ) && isset( $CFGEO['country_code'] ) ) $cfgp_region = $CFGEO['country_code'];
    if( empty( $cfgp_placename ) && isset( $CFGEO['city'] ) ) $cfgp_placename = $CFGEO['city'];

    if( empty( $cfgp_latitude ) && isset( $CFGEO['latitude'] ) ) 
    {
        $cfgp_latitude = $CFGEO['latitude'];
    }
    elseif( empty( $cfgp_latitude ) && isset( $CF_GEOPLUGIN_OPTIONS['map_latitude'] ) && !empty( $CF_GEOPLUGIN_OPTIONS['map_latitude'] ) )
    {
        $cfgp_latitude = $CF_GEOPLUGIN_OPTIONS['map_latitude'];
    }
    elseif( empty( $cfgp_latitude ) )
    {
        $cfgp_latitude = '51.4825766';
    }


    if( empty( $cfgp_longitude ) && isset( $CFGEO['longitude'] ) ) 
    {
        $cfgp_longitude = $CFGEO['longitude'];
    }
    elseif( empty( $cfgp_longitude ) && isset( $CF_GEOPLUGIN_OPTIONS['map_longitude'] ) && !empty( $CF_GEOPLUGIN_OPTIONS['map_longitude'] ) )
    {
        $cfgp_longitude = $CF_GEOPLUGIN_OPTIONS['map_longitude'];
    }
    elseif( empty( $cfgp_longitude ) )
    {
        $cfgp_longitude = '-0.0076589';
    }
	?>
   <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #geo-tag-container #CFMetaMap {
        height: 400px;
      }
      #geo-tag-container #description {
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
      }

      #geo-tag-container #infowindow-content .title {
        font-weight: bold;
      }

      #geo-tag-container #infowindow-content {
        display: none;
      }

      #geo-tag-container #CFMetaMap #infowindow-content {
        display: inline;
      }

      #geo-tag-container .pac-card {
        margin: 10px 10px 0 0;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        background-color: #fff;
        font-family: Roboto;
      }

      #geo-tag-container #pac-container {
        padding-bottom: 12px;
        margin-right: 12px;
      }

      #geo-tag-container .pac-controls {
        display: inline-block;
        padding: 5px 11px;
      }
      #geo-tag-container .pac-controls label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
      #geo-tag-container #pac-input {
		font-family: Roboto;
        background-color: #fff;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 10px 11px 10px 13px;
        text-overflow: ellipsis;
        width: 500px;
      }
      #geo-tag-container #pac-input:focus {
        border-color: #4d90fe;
      }
      #geo-tag-container #title {
        color: #fff;
        background-color: #4d90fe;
        font-size: 25px;
        font-weight: 500;
        padding: 6px 12px;
      }
      #geo-tag-container #target {
        width: 345px;
      }
      #geo-tag-container .cfgp-input {
        width: 300px;
      }
    </style>
<div id="geo-tag-container">
	<br /><input type="checkbox" name="cfgp-geotag-enable" id="geo-tag-geotag-enable" value="1" <?php checked( $cfgp_enable, 1, true ); ?>>
    <label for="geo-tag-geotag-enable"><?php esc_html_e( 'Enable Geo Tag on this page', CFGP_NAME ); ?></label><br />
	<p><?php esc_html_e( 'The easiest way to start is using the address search function inside map. By march 2007 street level search is available for the following countries: Australia, Austria, Canada, France, Germany, Italy, Japan, Netherlands, New Zealand, Portugal, Spain, Sweden, Switzerland and the United States. If there is no result for your complete address, then try the combination: "city, country" or only the country name.', CFGP_NAME ); ?></p>
	<p><?php esc_html_e( 'After a successful address search many of the fields listed below should already be filled correctly. But you may modify them if you want to in the fields below. Google Map you see here is only for the preview purpose.', CFGP_NAME ); ?></p>
	
    <input id="pac-input" class="controls" type="text" placeholder="<?php esc_html_e( 'Search address, place or certain region...', CFGP_NAME ); ?>">
    <div id="CFMetaMap"></div>
	
    <br /><input type="text" name="cfgp-dc-title" id="geo-tag-dc-title" class="cfgp-input" value="<?php echo $cfgp_dc_title; ?>">
    <label for="geo-tag-dc-title"><?php esc_html_e( 'Address', CFGP_NAME ); ?></label><br />
    
    <input type="text" name="cfgp-region" id="geo-tag-region" class="cfgp-input" value="<?php echo $cfgp_region; ?>">
    <label for="geo-tag-region"><?php esc_html_e( 'Country code', CFGP_NAME ); ?></label><br />
    
    <input type="text" name="cfgp-placename" id="geo-tag-placename" class="cfgp-input" value="<?php echo $cfgp_placename; ?>">
    <label for="geo-tag-placename"><?php esc_html_e( 'Region', CFGP_NAME ); ?></label><br />
    
    <input type="text" name="cfgp-latitude" id="geo-tag-latitude" class="cfgp-input" value="<?php echo $cfgp_latitude; ?>">
    <label for="geo-tag-latitude"><?php esc_html_e( 'Latitude', CFGP_NAME ); ?></label><br />
    
    <input type="text" name="cfgp-longitude" id="geo-tag-longitude" class="cfgp-input" value="<?php echo $cfgp_longitude; ?>">
    <label for="geo-tag-longitude"><?php esc_html_e( 'Longitude', CFGP_NAME ); ?></label><br />
</div>
<script>
  // This example adds a search box to a map, using the Google Place Autocomplete
  // feature. People can enter geographical searches. The search box will return a
  // pick list containing a mix of places and predicted search terms.

  // This example requires the Places library. Include the libraries=places
  // parameter when you first load the API. For example:
  // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

function CF_GeoPlugin_Google_Map_GeoTag() {
    var map = new google.maps.Map(document.getElementById('CFMetaMap'), {
        center: {
            lat: <?php echo $cfgp_latitude; ?>,
            lng: <?php echo $cfgp_longitude; ?>
        },
        zoom: 13,
        disableDefaultUI: true,
        mapTypeId: 'roadmap'
    });

    var markers = [];
    // Create a marker for each place.
    markers.push(new google.maps.Marker({
        map: map,
        title: "<?php echo $cfgp_dc_title; ?>",
        position: {
            lat: <?php echo $cfgp_latitude; ?>,
            lng: <?php echo $cfgp_longitude; ?>
        },
    }));

    map.setOptions({
        draggable: false
    });

    // Create the search box and link it to the UI element.
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);

    input.style.marginTop = '9px';

    map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        markers.forEach(function(marker) {
            marker.setMap(null);
        });
        markers = [];

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
                return;
            }

            var countryCode = '';
            if (typeof place.address_components != 'undefined') {
                var componentsLength = place.address_components.length;
                for (var i = 0; i < componentsLength; i++) {
                    if (place.address_components[i] == 'undefined') continue;

                    if (place.address_components[i].types.indexOf('country') >= 0) {
                        countryCode = place.address_components[i].short_name;
                        break;
                    }
                }
            }

			var city = (typeof place.address_components[0] != 'undefined' ? place.address_components[0].long_name : ''),
				region = (typeof place.address_components[2] != 'undefined' ? place.address_components[2].long_name : ''),
				country = place.address_components[place.address_components.length-1].long_name || '';
				
			if((place.address_components.length-1) === 2)
				region = '';

            document.getElementById('geo-tag-dc-title').value = place.formatted_address;
            document.getElementById('geo-tag-region').value = countryCode;
            document.getElementById('geo-tag-placename').value = city;

            document.getElementById('geo-tag-latitude').value = place.geometry.location.lat();
            document.getElementById('geo-tag-longitude').value = place.geometry.location.lng();

            var icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };
            // Create a marker for each place.
            markers.push(new google.maps.Marker({
                map: map,
                icon: icon,
                title: place.name,
                position: place.geometry.location
            }));

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}
(function(position, callback){
		
	if( typeof google !== 'undefined' )
	{
		if(typeof callback === 'function') {
			callback(google,{});
		}
	}
	else
	{
		var url = '//maps.googleapis.com/maps/api/js?key=<?php echo isset( $CF_GEOPLUGIN_OPTIONS['map_api_key'] ) ? esc_attr($CF_GEOPLUGIN_OPTIONS['map_api_key']) : ''; ?>',
			head = document.getElementsByTagName('head')[0],
			script = document.createElement("script");
		
		position = position || 0;
		
		script.src = url + '&libraries=places';
		script.type = 'text/javascript';
		script.charset = 'UTF-8';
		script.async = true;
		script.defer = true;
		head.appendChild(script);
		head.insertBefore(script,head.childNodes[position]);		
		script.onload = function(){
			if(typeof callback == 'function') {
				callback(google, script);
			}
		};
		script.onerror = function(){
			if(typeof callback == 'function') {
				callback(undefined, script);
			}
		};
	}
}(0, function($this){
	if( typeof $this != 'undefined' ) $this.maps.event.addDomListener(window, 'load', CF_GeoPlugin_Google_Map_GeoTag);
}));
</script>
	<?php 
    }

    // Meta box content
    public function meta_box_seo_redirection( $post )
    {
        $this->add_redirections();
    }

    // Save meta box values
    public function meta_box_save( $id )
    {
		$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
        $post = get_post( $id );

        if( $CF_GEOPLUGIN_OPTIONS['enable_seo_redirection'] )
        {
            // Delete old data
            $this->get_old_seo_meta( $id );
            
            if( isset( $_POST[ $this->prefix ] ) ) 
            {
                foreach( $_POST[ $this->prefix ] as $i => $value )
                {
                    if( isset( $value['redirect_url'] ) ) 
                    {
                        $value['redirect_url'] = $this->addhttp( $value['redirect_url'] );
                        $value['redirect_url'] = esc_url_raw( $value['redirect_url'] );
                    }
                }
                update_post_meta( $id, $this->prefix . 'redirection', array_values( $_POST[ $this->prefix ] ) ); // Reindex array beacuse of deleted repeaters. 0 = start
            }/*
			else
				update_post_meta( $id, $this->prefix . 'redirection', NULL );*/
        }

        if( isset( $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) && in_array( $post->post_type, $CF_GEOPLUGIN_OPTIONS['enable_geo_tag'] ) )
        {
            $cfgp_geotag_enable = 0;
			
			if(isset($_POST['cfgp-geotag-enable'])) {
				$cfgp_geotag_enable = 1;
			} else {
				if( isset($_POST['cfgp-dc-title']) )	unset($_POST['cfgp-dc-title']);
				if( isset($_POST['cfgp-region']) )		unset($_POST['cfgp-region']);
				if( isset($_POST['cfgp-placename']) )	unset($_POST['cfgp-placename']);
				if( isset($_POST['cfgp-latitude']) )	unset($_POST['cfgp-latitude']);
				if( isset($_POST['cfgp-longitude']) )	unset($_POST['cfgp-longitude']);
			}
			
            foreach( $_POST as $key => $val )
            {
                if( strpos( $key, 'cfgp-' ) !== false && $key != 'cfgp-geotag-enable' )
                {
                    update_post_meta( $id, $key, $val );
                }
            }
            update_post_meta( $id, 'cfgp-geotag-enable', $cfgp_geotag_enable );
        }
    }

    /**
     * Add blank redirection form
     */
    public function add_redirections()
    {
        $redirection_data = $this->get_post_meta( 'redirection' );

        global $wp_version;
        if( version_compare( $wp_version, '4.6', '>=' ) )
        {
            $all_countries = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-country',
                'hide_empty'    => false
            ));

            $all_regions = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-region',
                'hide_empty'    => false
            ));

            $all_cities = get_terms(array(
                'taxonomy'      => 'cf-geoplugin-city',
                'hide_empty'    => false
            ));
        }
        else
        {
            $all_countries = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-country',
                'hide_empty'    => false
            ));

            $all_regions = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-region',
                'hide_empty'    => false
            ));

            $all_cities = $this->cf_geo_get_terms(array(
                'taxonomy'      => 'cf-geoplugin-city',
                'hide_empty'    => false
            ));
        }
        
        $init = false;
        if( empty( $redirection_data ) || !is_array( $redirection_data ) ) 
        {
            $redirection_data = array( 0 => array( 'country' => array(), 'region' => array(), 'city' => array(), 'http_code' => '302', 'seo_redirect' => '0' ) ); // Make sure to execute system below at least once
            $init = true;
        }

        $add_redirection = $this->get_old_seo_meta( false );
        if( !empty( $add_redirection ) && $init ) $redirection_data[0] = $add_redirection;
        elseif( !empty( $add_redirection ) ) $redirection_data[] = $add_redirection; // If someone decide to switch to old version and then go back to new prevent data collision

        $end = count( $redirection_data );
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed posts striped cfgeo-post-redirect-table">
        <?php
            foreach( $redirection_data as $i => $value ) 
            {
        ?>
            <tbody>
            <tr class="repeating">
                <td>
                    <table class="wp-list-table widefat fixed posts cfgeo-post-redirect-table-form">
                        <tbody>
                        <tr>
                            <td>
                                <!-- COUNTRY -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country]"><?php _e( 'Choose Countries', CFGP_NAME ); ?></label><br>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country][]" placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][country]" class="widefat fixed chosen-select" multiple>
                                <?php
                                    if( is_array( $all_countries ) && !empty( $all_countries ) )
                                    {
                                        foreach( $all_countries as $key => $country )
                                        {
                                            echo '<option id="'
												.$country->slug
												.'" value="'
												.$country->slug
												.'"'
												.( isset( $value['country'] ) && in_array( $country->slug, $value['country'] ) ? ' selected':'')
												.'>'
												.$country->name
												.' - '.$country->description.'</option>';
                                        }
                                    }
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the country you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <!-- REGION -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region]"><?php _e( 'Choose Regions', CFGP_NAME ); ?></label>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region][]" placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][region]" class="chosen-select widefat fixed" multiple>
                                <?php
                                if( is_array( $all_regions ) &&  !empty( $all_regions ) ):
                                    foreach( $all_regions as $key => $region )
                                    {
                                        echo '<option id="'
											.$region->slug
											.'" value="'
											.$region->slug
											.'"'
											.( isset( $value['region'] ) && in_array( $region->slug, $value['region'] ) ? ' selected':'')
											.'>'
											.$region->name
											.' - '.$region->description.'</option>';
                                    }
                                endif;
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the region you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <!-- CITY -->
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city]"><?php _e( 'Choose City', CFGP_NAME ); ?></label>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city][]" placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" data-placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][city]" class="chosen-select widefat fixed" multiple>
                                <?php
                                    if( is_array( $all_cities ) && !empty( $all_cities ) ):
                                        foreach( $all_cities as $key => $city )
                                        {
                                            echo '<option id="'
												.$city->slug
												.'" value="'
												.$city->slug
												.'"'
												.( isset( $value['city'] ) && in_array( $city->slug, $value['city'] ) ? ' selected':'')
												.'>'
												.$city->name
												.' - '.$city->description.'</option>';
                                        }
                                    endif;
                                ?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the city you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]"><?php _e( 'Redirect URL', CFGP_NAME ); ?></label>
                                <input type="text" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][url]" class="large-text" value="<?php echo isset( $value['url'] ) ? $value['url'] : ''; ?>"  placeholder="http://" />
                                <span class="description"><?php esc_attr_e( 'URL where you want to redirect.', CFGP_NAME ); ?></span>
                            </td>
                            <td>
                                <label for="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]"><?php _e( 'HTTP Code' ); ?></label>
                                <?php
                                    if( !isset( $value['http_code'] ) || empty( $value['http_code'] ) ) $value['http_code'] = '302';
                                ?>
                                <select name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]" id="<?php echo $this->prefix; ?>[<?php echo $i; ?>][http_code]" class="cfgp-chosen widefat http_select">
                                <?php
									foreach(CF_Geoplugin_Global::get_http_codes() as $http_code => $http_name)
									{
										echo '<option value="' . $http_code . '" ' . selected( $value['http_code'], $http_code ) .'>' . $http_name . '</option>';
									}
								?>
                                </select>
                                <span class="description"><?php esc_attr_e( 'Select the desired HTTP redirection. (HTTP Code 302 is recommended)', CFGP_NAME ); ?>
                                <br>
								<?php printf(__( 'If you are not sure which redirect code to use, <a href=\'%1$s\' target=\'_blank\'>check out this article</a>.', CFGP_NAME ), CFGP_STORE.'/information/seo-redirection-in-wordpress/'); ?></span>
                            </td>
                            <td>

								<table width="100%">
									<tr>
										<td width="50%">
											<label><?php _e( 'Redirect Only Once', CFGP_NAME ); ?></label><br />
											<?php
												if( !isset( $value['only_once'] ) || empty( $value['only_once'] ) ) $value['only_once'] = '0';
											?>
											<div class="cfgp-enable-redirection">
												<label for="only_once_checkbox_<?php echo $i; ?>_1"><input id="only_once_checkbox_<?php echo $i; ?>_1" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][only_once]" value="1" <?php checked( $value['only_once'], '1' ); ?> /> <?php _e( 'Enable', CFGP_NAME ); ?> </label>&nbsp;&nbsp;
												</label for="only_once_checkbox_><?php echo $i; ?>_0"><input id="only_once_checkbox_<?php echo $i; ?>_0" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][only_once]" value="0" <?php checked( $value['only_once'], '0' ); ?> /> <?php _e( 'Disable', CFGP_NAME ); ?></label>
											</div>
										</td>
										<td width="50%">
											<label><?php _e( 'Enable SEO Redirect', CFGP_NAME ); ?></label><br />
											<?php
												if( !isset( $value['seo_redirect'] ) || empty( $value['seo_redirect'] ) ) $value['seo_redirect'] = '0';
											?>
											<div class="cfgp-enable-redirection">
												<label for="seo_redirect_checkbox_<?php echo $i; ?>_1"><input id="seo_redirect_checkbox_<?php echo $i; ?>_1" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][seo_redirect]" value="1" <?php checked( $value['seo_redirect'], '1' ); ?> /> <?php _e( 'Enable', CFGP_NAME ); ?> </label>&nbsp;&nbsp;
												</label for="seo_redirect_checkbox_><?php echo $i; ?>_0"><input id="seo_redirect_checkbox_<?php echo $i; ?>_0" type="radio" name="<?php echo $this->prefix; ?>[<?php echo $i; ?>][seo_redirect]" value="0" <?php checked( $value['seo_redirect'], '0' ); ?> /> <?php _e( 'Disable', CFGP_NAME ); ?></label>
											</div>
										</td>
									</tr>
								</table>

                            </td>
                        </tr>
						<tr>
							<td colspan="3">
								<div class="cfgp-add-remove-redirection" style="text-align:right">
									<?php
										if( $i+1 == $end ) printf( '<a class="cfgp-repeat cfgp-first-repeater" href="#" title="%s"><i class="fa fa-plus-circle fa-2x" style="color: green;"></i></a>&nbsp;&nbsp;&nbsp', __( 'Add New Redirection', CFGP_NAME ) );
										
										if( $i == 0 && $end == 1 ) printf('<a class="cfgp-reset-fields" href="#" title="%s"><i class="fa fa-repeat fa-2x" style="color: red;"></i></a>', __( 'Reset Redirection', CFGP_NAME ) );
										else printf( '<a class="cfgp-destroy-repeat" href="#" title="%s"><i class="fa fa-minus-circle fa-2x" style="color: red;"></i></a>', __( 'Remove Redirection', CFGP_NAME ) );
									?>
								</div>
							</td>
						</tr>
                        <?php
                            if( !empty( $add_redirection ) )
                            {
                                ?>
                                <tr>
                                    <td colspan="3">
                                        <h4><?php _e( 'Old redirection data is found. If you want to merge update post, after save/update old data will be deleted. Redirection will work normally without save by old data.', CFGP_NAME ); ?></h4>
                                    </td>
                                </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        <?php } ?>
        </table>
        <?php
        echo ob_get_clean();
    }

    /**
     * Get old seo redirection postmeta
     */
    public function get_old_seo_meta( $delete )
    {
        $old_redirection = array(
            'country',
            'region',
            'city',
            'redirect_url',
            'http_code',
            'seo_redirect',
        );
        $add_redirection = array();
        
        foreach( $old_redirection as $i => $meta_key )
        {
            if( $delete !== false ) 
            {
                delete_post_meta( $delete, $this->prefix . $meta_key );
                continue;
            }

            $meta_value = $this->get_post_meta( $meta_key );
        
            if( $meta_key == 'redirect_url' ) $meta_key = 'url';

            if( !empty( $meta_value ) ) 
            {
                if( in_array( $meta_key, array( 'country', 'region', 'city' ) ) ) $meta_value = array( $meta_value );

                $add_redirection[ $meta_key ] = $meta_value;
            }
        }

        if( $delete === false ) return $add_redirection;
    }

    /**
     * Geo banner shortcode metabox
     */
    public function banner_shortcode( $post )
    {
        echo '<ul>';
        echo '<li><strong>' . __('Standard',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]</code></li>';
        echo '<li><strong>' . __('Advanced',CFGP_NAME) . ':</strong><br><code>[cfgeo_banner id="'.$post->ID.'"]' . __('Default content',CFGP_NAME) . '[/cfgeo_banner]</code></li>';
        echo '</ul>';
    }
}
endif;