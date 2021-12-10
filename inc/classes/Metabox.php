<?php
/**
 * Metaboxes
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

if(!class_exists('CFGP_Metabox')) :
class CFGP_Metabox extends CFGP_Global {
	public $metabox_seo_redirection = 'cfgp-seo-redirection';
	public $metabox_geo_tag = 'cfgp-geo-tag';
	
	public function __construct(){
		$this->add_action('add_meta_boxes', 'add_seo_redirection', 1);
		$this->add_action('add_meta_boxes', 'add_geo_tags', 1);
		$this->add_action('admin_enqueue_scripts', 'register_style');
		$this->add_action('save_post', 'save_post');
	}
	
	/**
	 * Hook for the post save/update
	 */
	public function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$post_type = get_post_type($post_id);
		
		// SEO Redirection
		if(in_array($post_type, CFGP_Options::get('enable_seo_posts', array()))) {
			$save = array(); $i=0;
			if($prepared_data = CFGP_U::request($this->metabox_seo_redirection, array()))
			{
				if(is_array($prepared_data))
				{
					$prepared_data = array_filter($prepared_data);
					foreach($prepared_data as $data){
						if(isset($data['url']) && !empty($data['url'])) {
							$save[$i]=CFGP_Options::sanitize($data);
							++$i;
						}
					}
				}
			}
			
			update_post_meta( $post_id, "{$this->metabox_seo_redirection}-enabled", !empty($save));
			update_post_meta( $post_id, $this->metabox_seo_redirection, $save);
			
			delete_post_meta($post_id, CFGP_METABOX . 'redirection');
		}
		
		// Geo Tags
		if(in_array($post_type, CFGP_Options::get('enable_geo_tag', array()))) {
			if( CFGP_U::request('cfgp-geotag-enable', false) ) {
				foreach(array(
					'cfgp-dc-title',
					'cfgp-region',
					'cfgp-placename',
					'cfgp-latitude',
					'cfgp-longitude'
				) as $meta) {
					update_post_meta( $post_id, $meta, CFGP_Options::sanitize(CFGP_U::request($meta, false)) );
				}
				update_post_meta( $post_id, 'cfgp-geotag-enable', 1 );
			} else {
				update_post_meta( $post_id, 'cfgp-geotag-enable', 0 );
			}
		}
	}
	
	
	/**
     * Add SEO Redirection
     */
	public function add_seo_redirection(){
		$screen = get_current_screen();
		if(isset( $screen->post_type ) && in_array($screen->post_type, CFGP_Options::get('enable_seo_posts', array()))){
			$this->add_meta_box(
				CFGP_NAME . '-page-seo-redirection',			// Unique ID
				__( 'SEO Redirection', CFGP_NAME ),				// Box title
				'add_seo_redirection__callback',				// Content callback, must be of type callable
				$screen->post_type,								// Post type
				'advanced',
				'high'
			);
		}
		
		return;
	}
	
	/**
     * Add Geo Tags
     */
	public function add_geo_tags(){
		$screen = get_current_screen();
		if(isset( $screen->post_type ) && in_array($screen->post_type, CFGP_Options::get('enable_geo_tag', array()))){
			$this->add_meta_box(
				CFGP_NAME . '-geo-tags',						// Unique ID
				__( 'Geo Tags', CFGP_NAME ),					// Box title
				'add_geo_tags__callback',						// Content callback, must be of type callable
				$screen->post_type,								// Post type
				'advanced',
				'low'
			);
		}
		
		return;
	}
	
	/**
	 * GEO Tag metabox callback
	 */
	public function add_geo_tags__callback( $post ){
		
		$screen = get_current_screen();
			
		if(isset( $screen->post_type ) && $screen->post_type === 'cf-geoplugin-banner') return;

		$cfgp_enable = get_post_meta( $post->ID, 'cfgp-geotag-enable', true );
		
		// Set full address
		$cfgp_dc_title = get_post_meta( $post->ID, 'cfgp-dc-title', true );
		if( empty( $cfgp_dc_title ) ) {
			$cfgp_dc_title = CFGP_U::api('address');
		}
		
		// Set region
		$cfgp_region = get_post_meta( $post->ID, 'cfgp-region', true );
		if( empty( $cfgp_region ) ) {
			$cfgp_region = CFGP_U::api('country_code');
		}
		
		// Set placename
		$cfgp_placename = get_post_meta( $post->ID, 'cfgp-placename', true );
		if( empty( $cfgp_placename ) ) {
			$cfgp_placename = CFGP_U::api('city');
		}

		// Set latitude
		$cfgp_latitude = get_post_meta( $post->ID, 'cfgp-latitude', true );
		if( empty( $cfgp_latitude ) ) {
			$cfgp_latitude = CFGP_U::api('latitude');
		}
		if( empty( $cfgp_latitude ) ) {
			$cfgp_latitude = CFGP_Options::get('map_latitude', '51.4825766');
		}

		// Set longitude
		$cfgp_longitude = get_post_meta( $post->ID, 'cfgp-longitude', true );
		if( empty( $cfgp_longitude ) ) {
			$cfgp_longitude = CFGP_U::api('longitude');
		}
		if( empty( $cfgp_longitude ) ) {
			$cfgp_longitude = CFGP_Options::get('map_longitude', '-0.0076589');
		}
	?>
<div id="cfgp-geo-tag-container">
    <label for="geo-tag-geotag-enable"><input type="checkbox" name="cfgp-geotag-enable" id="geo-tag-geotag-enable" value="1" <?php checked( $cfgp_enable, 1, true ); ?>> <?php esc_html_e( 'Enable Geo Tag on this page', CFGP_NAME ); ?></label>
	<div id="cfgp-geo-tag-map-container"<?php echo (!$cfgp_enable ? ' style="display: none;"' : ''); ?>>
		<p><?php esc_html_e( 'The easiest way to start is using the address search function inside map. By march 2007 street level search is available for the following countries: Australia, Austria, Canada, France, Germany, Italy, Japan, Netherlands, New Zealand, Portugal, Spain, Sweden, Switzerland and the United States. If there is no result for your complete address, then try the combination: "city, country" or only the country name.', CFGP_NAME ); ?></p>
		<p><?php esc_html_e( 'After a successful address search many of the fields listed below should already be filled correctly. But you may modify them if you want to in the fields below. Google Map you see here is only for the preview purpose.', CFGP_NAME ); ?></p>
		<input id="pac-input" class="controls" type="text" placeholder="<?php esc_html_e( 'Search address, place or certain region...', CFGP_NAME ); ?>">
		<div id="CFGP_Geo_Tag_Gmap"></div>
		
		<br><input type="text" name="cfgp-dc-title" id="geo-tag-dc-title" class="cfgp-input" value="<?php echo esc_attr($cfgp_dc_title); ?>"<?php echo (!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-dc-title"><?php esc_html_e( 'Address', CFGP_NAME ); ?></label><br>
		
		<input type="text" name="cfgp-region" id="geo-tag-region" class="cfgp-input" value="<?php echo esc_attr($cfgp_region); ?>"<?php echo (!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-region"><?php esc_html_e( 'Country code', CFGP_NAME ); ?></label><br>
		
		<input type="text" name="cfgp-placename" id="geo-tag-placename" class="cfgp-input" value="<?php echo esc_attr($cfgp_placename); ?>"<?php echo (!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-placename"><?php esc_html_e( 'Region', CFGP_NAME ); ?></label><br>
		
		<input type="text" name="cfgp-latitude" id="geo-tag-latitude" class="cfgp-input" value="<?php echo (float)esc_attr($cfgp_latitude); ?>"<?php echo (!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-latitude"><?php esc_html_e( 'Latitude', CFGP_NAME ); ?></label><br>
		
		<input type="text" name="cfgp-longitude" id="geo-tag-longitude" class="cfgp-input" value="<?php echo (float)esc_attr($cfgp_longitude); ?>"<?php echo (!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-longitude"><?php esc_html_e( 'Longitude', CFGP_NAME ); ?></label><br>
	</div>
</div>
<script>
  // This example adds a search box to a map, using the Google Place Autocomplete
  // feature. People can enter geographical searches. The search box will return a
  // pick list containing a mix of places and predicted search terms.

  // This example requires the Places library. Include the libraries=places
  // parameter when you first load the API. For example:
  // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

function CF_GeoPlugin_Google_Map_GeoTag() {
    var map = new google.maps.Map(document.getElementById('CFGP_Geo_Tag_Gmap'), {
        center: {
            lat: <?php echo (float)esc_attr($cfgp_latitude); ?>,
            lng: <?php echo (float)esc_attr($cfgp_longitude); ?>
        },
        zoom: 13,
        disableDefaultUI: true,
        mapTypeId: 'roadmap'
    });

    var markers = [];
    // Create a marker for each place.
    markers.push(new google.maps.Marker({
        map: map,
        title: "<?php echo esc_attr($cfgp_dc_title); ?>",
        position: {
            lat: <?php echo (float)esc_attr($cfgp_latitude); ?>,
            lng: <?php echo (float)esc_attr($cfgp_longitude); ?>
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
		var url = '//maps.googleapis.com/maps/api/js?key=<?php echo esc_attr(CFGP_Options::get('map_api_key')); ?>',
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
	
	/**
	 * SEO redirection metabox callback
	 */
	public function add_seo_redirection__callback( $post ){
		
		$seo_redirection = get_post_meta($post->ID, $this->metabox_seo_redirection, true);
		if(empty($seo_redirection)){
			$seo_redirection = get_post_meta($post->ID, CFGP_METABOX . 'redirection', true); // Depricated (it will be removed in the future)
		}
		
		if(empty($seo_redirection)){
			$seo_redirection = array(
				array(
					'country' => NULL,
					'region' => NULL,
					'city' => NULL,
					'postcode' => NULL,
					'url' => NULL,
					'http_code' => 302,
					'only_once' => 0,
					'active' => 1,
					'search_type' => 'exact'
				)
			);
		}
	?>
<div class="cfgp-container cfgp-repeater">
	<?php foreach($seo_redirection as $i=>$data):
		$country 	= (isset($data['country']) ? $data['country'] : '');
		$region 	= (isset($data['region']) ? $data['region'] : '');
		$city 		= (isset($data['city']) ? $data['city'] : '');
		$postcode 	= (isset($data['postcode']) ? $data['postcode'] : '');
		$url 		= (isset($data['url']) ? $data['url'] : '');
		$http_code 	= (isset($data['http_code']) ? $data['http_code'] : 302);
		$only_once 	= (isset($data['only_once']) ? $data['only_once'] : 0);
		$active 	= (isset($data['active']) ? $data['active'] : 1);
		$search_type = (isset($data['search_type']) ? $data['search_type'] : 'exact');
		
		$exclude_country = (isset($data['exclude_country']) ? $data['exclude_country'] : NULL);
		$exclude_region = (isset($data['exclude_region']) ? $data['exclude_region'] : NULL);
		$exclude_city = (isset($data['exclude_city']) ? $data['exclude_city'] : NULL);
		$exclude_postcode = (isset($data['exclude_postcode']) ? $data['exclude_postcode'] : NULL);
	?>
    <div class="cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form">
        <div class="cfgp-col cfgp-col-4">
            <label for="country"><?php _e('Choose Countries', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_countries(array('name'=>"{$this->metabox_seo_redirection}[{$i}][country]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-country"), $country, true);?>
            <span class="description"><?php _e( 'Select the countries you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox_seo_redirection}-{$i}-country"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox_seo_redirection}[{$i}][exclude_country]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_country,
							'id' => "{$this->metabox_seo_redirection}-{$i}-exclude_country",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="region"><?php _e('Choose Regions', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_regions(array('name'=>"{$this->metabox_seo_redirection}[{$i}][region]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-region", 'country_code' => $country), $region, true); ?>
            <span class="description"><?php _e( 'Select the regions you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox_seo_redirection}-{$i}-region"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox_seo_redirection}[{$i}][exclude_region]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_region,
							'id' => "{$this->metabox_seo_redirection}-{$i}-exclude_region",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="city"><?php _e('Choose Cities', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_cities(array('name'=>"{$this->metabox_seo_redirection}[{$i}][city]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-city", 'country_code' => $country), $city, true); ?>
            <span class="description"><?php _e( 'Select the cities you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox_seo_redirection}-{$i}-city"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox_seo_redirection}[{$i}][exclude_city]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_city,
							'id' => "{$this->metabox_seo_redirection}-{$i}-exclude_city",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="postcode"><?php _e('Choose Postcodes', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_postcodes(array('name'=>"{$this->metabox_seo_redirection}[{$i}][postcode]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-postcode"), $postcode, true); ?>
            <span class="description"><?php _e( 'Select the postcodes you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox_seo_redirection}-{$i}-postcode"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox_seo_redirection}[{$i}][exclude_postcode]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_postcode,
							'id' => "{$this->metabox_seo_redirection}-{$i}-exclude_postcode",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="url"><?php _e('Define Redirect URL', CFGP_NAME); ?></label>
            <?php CFGP_Form::input('url', array('name'=>"{$this->metabox_seo_redirection}[{$i}][url]",'value'=>$url, 'id'=>"{$this->metabox_seo_redirection}-{$i}-url", 'class'=>'required-field')); ?>
            <span class="description"><?php _e( 'URL where you want to redirect.', CFGP_NAME ); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="http_code"><?php _e('HTTP Code', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_http_code(array('name'=>"{$this->metabox_seo_redirection}[{$i}][http_code]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-http_code"), $http_code); ?>
            <span class="description"><?php _e( 'Select the desired HTTP redirection.', CFGP_NAME ); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-3 input-radio">
            <label><?php _e('Enable this redirection', CFGP_NAME); ?></label>
            <?php
                CFGP_Form::radio(
                    array(
                        1 => __('Enable', CFGP_NAME),
                        0 => __('Disable', CFGP_NAME)
                    ),
                    array('name'=>"{$this->metabox_seo_redirection}[{$i}][active]", 'id'=>"{$this->metabox_seo_redirection}-{$i}-active"),
                    $active
                );
            ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 input-radio">
            <label><?php _e('Redirect only once', CFGP_NAME); ?></label>
            <?php
                CFGP_Form::radio(
                    array(
                        1 => __('Enable', CFGP_NAME),
                        0 => __('Disable', CFGP_NAME)
                    ),
                    array('name'=>"{$this->metabox_seo_redirection}[{$i}][only_once]"),
                    $only_once
                );
            ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 input-radio"></div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 cfgp-col-content-right cfgp-repeater-actions">
        	<button type="button" class="button button-link cfgp-remove-seo-redirection"><i class="fa fa-times"></i> <?php _e( 'Remove', CFGP_NAME ); ?></button>
        	<button type="button" class="button button-primary cfgp-add-seo-redirection"><i class="fa fa-plus"></i> <?php _e( 'Add New Redirection', CFGP_NAME ); ?></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
	<?php }
	
	/**
	 * Register style
	 */
	public function register_style(){
		$screen = get_current_screen();
		if(
			isset( $screen->post_type )
			&& (
				in_array($screen->post_type, CFGP_Options::get('enable_seo_posts', array()))
				|| in_array($screen->post_type, CFGP_Options::get('enable_geo_tag', array()))
				|| $screen->post_type === 'cf-geoplugin-banner'
			)
		){
			$url = CFGP_U::get_url();
			
			
			wp_enqueue_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), (string)CFGP_VERSION );
			wp_enqueue_style( CFGP_NAME . '-metabox', CFGP_ASSETS . '/css/style-metabox.css', array(CFGP_NAME . '-fontawesome'), (string)CFGP_VERSION, false );
			wp_enqueue_style( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
			
			wp_enqueue_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
			wp_enqueue_script( CFGP_NAME . '-metabox', CFGP_ASSETS . '/js/script-metabox.js', array('jquery', CFGP_NAME . '-choosen'), (string)CFGP_VERSION, true );
			wp_localize_script(CFGP_NAME . '-metabox', 'CFGP', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'adminurl' => self_admin_url('/'),
				'label' => array(
					'unload' => esc_attr__('Data will lost , Do you wish to continue?',CFGP_NAME),
					'loading' => esc_attr__('Loading...',CFGP_NAME),
					'not_found' => esc_attr__('Not Found!',CFGP_NAME),
					'chosen' => array(
						'not_found' 		=> esc_attr__('Nothing found!',CFGP_NAME),
						'choose' 			=> esc_attr__('Choose...',CFGP_NAME),
						'choose_first' 		=> esc_attr__('Choose countries first!',CFGP_NAME),
						'choose_countries' 	=> esc_attr__('Choose countries...',CFGP_NAME),
						'choose_regions' 	=> esc_attr__('Choose regions...',CFGP_NAME),
						'choose_cities' 	=> esc_attr__('Choose cities...',CFGP_NAME),
						'choose_postcodes' 	=> esc_attr__('Choose postcodes...',CFGP_NAME),
					)
				)
			));
			
			// Load geodata
			if(strpos($url, 'post-new.php') !== false || (strpos($url, 'action=edit') !== false && strpos($url, 'post=') !== false)){
				wp_localize_script(CFGP_NAME . '-metabox', 'CFGP_GEODATA', CFGP_Library::all_geodata());
			}
			
			
		}
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