<?php
/**
 * Metaboxes
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       1.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Metabox', false)) : class CFGP_Metabox extends CFGP_Global
{
    public $metabox = 'cfgp-seo-redirection';

    public function __construct()
    {
        $this->add_action('add_meta_boxes', 'add_seo_redirection', 1);
        $this->add_action('add_meta_boxes', 'add_geo_tags', 1);
        //	$this->add_action('add_meta_boxes', 'add_page_restriction', 1);
        $this->add_action('admin_enqueue_scripts', 'register_style');
        $this->add_action('save_post', 'save_post');
    }

    /**
     * Hook for the post save/update
     */
    public function save_post($post_id)
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $post_type = get_post_type($post_id);

        // SEO Redirection
        if (in_array($post_type, CFGP_Options::get('enable_seo_posts', []), true)) {
            $save = [];
            $i    = 0;

            if ($prepared_data = CFGP_U::request($this->metabox, [])) {
                if (is_array($prepared_data)) {
                    $prepared_data = array_filter($prepared_data);

                    foreach ($prepared_data as $data) {
                        if (isset($data['url']) && !empty($data['url'])) {
                            $save[$i] = CFGP_Options::sanitize($data);
                            ++$i;
                        }
                    }
                }
            }

            update_post_meta($post_id, "{$this->metabox}-enabled", !empty($save));
            update_post_meta($post_id, $this->metabox, $save);

            delete_post_meta($post_id, CFGP_METABOX . 'redirection');
        }

        // Geo Tags
        if (in_array($post_type, CFGP_Options::get('enable_geo_tag', []), true)) {
            if (CFGP_U::request('cfgp-geotag-enable', false)) {
                foreach ([
                    'cfgp-dc-title',
                    'cfgp-region',
                    'cfgp-placename',
                    'cfgp-latitude',
                    'cfgp-longitude',
                ] as $meta) {
                    update_post_meta($post_id, $meta, CFGP_Options::sanitize(CFGP_U::request($meta, false)));
                }
                update_post_meta($post_id, 'cfgp-geotag-enable', 1);
            } else {
                update_post_meta($post_id, 'cfgp-geotag-enable', 0);
            }
        }
    }

    /**
     * Add SEO Redirection
     */
    public function add_seo_redirection()
    {
        $screen           = get_current_screen();
        $enable_seo_posts = CFGP_Options::get('enable_seo_posts', []);

        if ($enable_seo_posts && isset($screen->post_type) && in_array($screen->post_type, $enable_seo_posts, true)) {
            $this->add_meta_box(
                CFGP_NAME . '-page-seo-redirection',			// Unique ID
                __('SEO Redirection', 'cf-geoplugin'),				// Box title
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
    public function add_geo_tags()
    {
        $screen         = get_current_screen();
        $enable_geo_tag = CFGP_Options::get('enable_geo_tag', []);

        if ($enable_geo_tag && isset($screen->post_type) && in_array($screen->post_type, $enable_geo_tag, true)) {
            $this->add_meta_box(
                CFGP_NAME . '-geo-tags',						// Unique ID
                __('Geo Tags', 'cf-geoplugin'),					// Box title
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
    public function add_geo_tags__callback($post)
    {

        $screen = get_current_screen();

        if (isset($screen->post_type) && $screen->post_type === 'cf-geoplugin-banner') {
            return;
        }

        $cfgp_enable = get_post_meta($post->ID, 'cfgp-geotag-enable', true);

        // Set full address
        $cfgp_dc_title = get_post_meta($post->ID, 'cfgp-dc-title', true);

        if (empty($cfgp_dc_title)) {
            $cfgp_dc_title = CFGP_U::api('address');
        }

        // Set region
        $cfgp_region = get_post_meta($post->ID, 'cfgp-region', true);

        if (empty($cfgp_region)) {
            $cfgp_region = CFGP_U::api('country_code');
        }

        // Set placename
        $cfgp_placename = get_post_meta($post->ID, 'cfgp-placename', true);

        if (empty($cfgp_placename)) {
            $cfgp_placename = CFGP_U::api('city');
        }

        // Set latitude
        $cfgp_latitude = get_post_meta($post->ID, 'cfgp-latitude', true);

        if (empty($cfgp_latitude)) {
            $cfgp_latitude = CFGP_U::api('latitude');
        }

        if (empty($cfgp_latitude)) {
            $cfgp_latitude = CFGP_Options::get('map_latitude', '51.4825766');
        }

        // Set longitude
        $cfgp_longitude = get_post_meta($post->ID, 'cfgp-longitude', true);

        if (empty($cfgp_longitude)) {
            $cfgp_longitude = CFGP_U::api('longitude');
        }

        if (empty($cfgp_longitude)) {
            $cfgp_longitude = CFGP_Options::get('map_longitude', '-0.0076589');
        }
        ?>
<div id="cfgp-geo-tag-container">
    <label for="geo-tag-geotag-enable"><input type="checkbox" name="cfgp-geotag-enable" id="geo-tag-geotag-enable" value="1" <?php checked($cfgp_enable, 1, true); ?>> <?php esc_html_e('Enable Geo Tag on this page', 'cf-geoplugin'); ?></label>
	<div id="cfgp-geo-tag-map-container"<?php echo(!$cfgp_enable ? ' style="display: none;"' : ''); ?>>
		<p><?php esc_html_e('The easiest way to start is using the address search function inside map. By march 2007 street level search is available for the following countries: Australia, Austria, Canada, France, Germany, Italy, Japan, Netherlands, New Zealand, Portugal, Spain, Sweden, Switzerland and the United States. If there is no result for your complete address, then try the combination: "city, country" or only the country name.', 'cf-geoplugin'); ?></p>
		<p><?php esc_html_e('After a successful address search many of the fields listed below should already be filled correctly. But you may modify them if you want to in the fields below. Google Map you see here is only for the preview purpose.', 'cf-geoplugin'); ?></p>
		<input id="pac-input" class="controls" type="text" placeholder="<?php esc_html_e('Search address, place or certain region...', 'cf-geoplugin'); ?>">
		<div id="CFGP_Geo_Tag_Gmap"></div>
		
		<br><input type="text" name="cfgp-dc-title" id="geo-tag-dc-title" class="cfgp-input" value="<?php echo esc_attr($cfgp_dc_title); ?>"<?php echo(!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-dc-title"><?php esc_html_e('Address', 'cf-geoplugin'); ?></label><br>
		
		<input type="text" name="cfgp-region" id="geo-tag-region" class="cfgp-input" value="<?php echo esc_attr($cfgp_region); ?>"<?php echo(!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-region"><?php esc_html_e('Country code', 'cf-geoplugin'); ?></label><br>
		
		<input type="text" name="cfgp-placename" id="geo-tag-placename" class="cfgp-input" value="<?php echo esc_attr($cfgp_placename); ?>"<?php echo(!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-placename"><?php esc_html_e('Region', 'cf-geoplugin'); ?></label><br>
		
		<input type="text" name="cfgp-latitude" id="geo-tag-latitude" class="cfgp-input" value="<?php echo (float)esc_attr($cfgp_latitude); ?>"<?php echo(!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-latitude"><?php esc_html_e('Latitude', 'cf-geoplugin'); ?></label><br>
		
		<input type="text" name="cfgp-longitude" id="geo-tag-longitude" class="cfgp-input" value="<?php echo (float)esc_attr($cfgp_longitude); ?>"<?php echo(!$cfgp_enable ? ' disabled' : ''); ?>>
		<label for="geo-tag-longitude"><?php esc_html_e('Longitude', 'cf-geoplugin'); ?></label><br>
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
		var url = '<?php
                echo esc_attr(CFGP_Defaults::API['googleapis_map']);
        ?>/api/js?key=<?php
            echo esc_attr(CFGP_Options::get('map_api_key'));
        ?>',
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
    public function add_seo_redirection__callback($post)
    {

        $seo_redirection = get_post_meta($post->ID, $this->metabox, true);

        if (empty($seo_redirection)) {
            $seo_redirection = get_post_meta($post->ID, CFGP_METABOX . 'redirection', true); // Depricated (it will be removed in the future)
        }

        if (empty($seo_redirection)) {
            $seo_redirection = [
                [
                    'country'     => null,
                    'region'      => null,
                    'city'        => null,
                    'postcode'    => null,
                    'url'         => null,
                    'http_code'   => 302,
                    'only_once'   => 0,
                    'active'      => 1,
                    'search_type' => 'exact',
                ],
            ];
        }
        ?>
<div class="cfgp-container cfgp-repeater">
	<?php foreach ($seo_redirection as $i => $data):

	    $country  = array_map('sanitize_text_field', $data['country'] ?? []);
	    $region   = array_map('sanitize_text_field', $data['region'] ?? []);
	    $city     = array_map('sanitize_text_field', $data['city'] ?? []);
	    $postcode = array_map('sanitize_text_field', $data['postcode'] ?? []);

	    $url         = sanitize_text_field($data['url'] ?? '');
	    $http_code   = sanitize_text_field($data['http_code'] ?? 302);
	    $only_once   = sanitize_text_field($data['only_once'] ?? 0);
	    $active      = sanitize_text_field($data['active'] ?? 1);
	    $search_type = sanitize_text_field($data['search_type'] ?? 'exact');

	    $exclude_country  = sanitize_text_field($data['exclude_country'] ?? null);
	    $exclude_region   = sanitize_text_field($data['exclude_region'] ?? null);
	    $exclude_city     = sanitize_text_field($data['exclude_city'] ?? null);
	    $exclude_postcode = sanitize_text_field($data['exclude_postcode'] ?? null);
	    ?>
    <div class="cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form" data-id="<?php echo absint($i); ?>">
        <div class="cfgp-col cfgp-col-4">
            <label for="country"><?php esc_html_e('Choose Countries', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::select_countries(['name' => "{$this->metabox}[{$i}][country]", 'id' => "{$this->metabox}-{$i}-country"], $country, true);?>
            <span class="description"><?php esc_html_e('Select the countries you want to redirect.', 'cf-geoplugin'); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo esc_attr("{$this->metabox}-{$i}-country"); ?>"><object data="<?php echo esc_url(CFGP_ASSETS . '/images/select.svg'); ?>" width="10" height="10"></object> <?php esc_attr_e('Select all', 'cf-geoplugin'); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
	                CFGP_Form::checkbox(
	                    [
	                        "{$this->metabox}[{$i}][exclude_country]" => [
	                            'label'   => __('Exclude from redirection', 'cf-geoplugin'),
	                            'value'   => 1,
	                            'checked' => $exclude_country,
	                            'id'      => "{$this->metabox}-{$i}-exclude_country",
	                        ],
	                    ],
	                    true
	                );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="region"><?php esc_html_e('Choose Regions', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::select_regions(['name' => "{$this->metabox}[{$i}][region]", 'id' => "{$this->metabox}-{$i}-region", 'country_code' => $country], $region, true); ?>
            <span class="description"><?php esc_html_e('Select the regions you want to redirect.', 'cf-geoplugin'); ?></span>
			<?php
	        CFGP_Form::checkbox(
	            [
	                "{$this->metabox}[{$i}][exclude_region]" => [
	                    'label'   => __('Exclude from redirection', 'cf-geoplugin'),
	                    'value'   => 1,
	                    'checked' => $exclude_region,
	                    'id'      => "{$this->metabox}-{$i}-exclude_region",
	                ],
	            ],
	            true
	        );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="city"><?php esc_html_e('Choose Cities', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::select_cities(['name' => "{$this->metabox}[{$i}][city]", 'id' => "{$this->metabox}-{$i}-city", 'country_code' => $country], $city, true); ?>
            <span class="description"><?php esc_html_e('Select the cities you want to redirect.', 'cf-geoplugin'); ?></span>
			<?php
	        CFGP_Form::checkbox(
	            [
	                "{$this->metabox}[{$i}][exclude_city]" => [
	                    'label'   => __('Exclude from redirection', 'cf-geoplugin'),
	                    'value'   => 1,
	                    'checked' => $exclude_city,
	                    'id'      => "{$this->metabox}-{$i}-exclude_city",
	                ],
	            ],
	            true
	        );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="postcode"><?php esc_html_e('Choose Postcodes', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::select_postcodes(['name' => "{$this->metabox}[{$i}][postcode]", 'id' => "{$this->metabox}-{$i}-postcode"], $postcode, true); ?>
            <span class="description"><?php esc_html_e('Select the postcodes you want to redirect.', 'cf-geoplugin'); ?></span>
			<?php
	        CFGP_Form::checkbox(
	            [
	                "{$this->metabox}[{$i}][exclude_postcode]" => [
	                    'label'   => __('Exclude from redirection', 'cf-geoplugin'),
	                    'value'   => 1,
	                    'checked' => $exclude_postcode,
	                    'id'      => "{$this->metabox}-{$i}-exclude_postcode",
	                ],
	            ],
	            true
	        );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="url"><?php esc_html_e('Define Redirect URL', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::input('url', ['name' => "{$this->metabox}[{$i}][url]",'value' => $url, 'id' => "{$this->metabox}-{$i}-url", 'class' => 'required-field']); ?>
            <span class="description"><?php esc_html_e('URL where you want to redirect.', 'cf-geoplugin'); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="http_code"><?php esc_html_e('HTTP Code', 'cf-geoplugin'); ?></label>
            <?php CFGP_Form::select_http_code(['name' => "{$this->metabox}[{$i}][http_code]", 'id' => "{$this->metabox}-{$i}-http_code"], $http_code); ?>
            <span class="description"><?php esc_html_e('Select the desired HTTP redirection.', 'cf-geoplugin'); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-3">
            <label><?php esc_html_e('Enable this redirection', 'cf-geoplugin'); ?></label>
            <?php
	        CFGP_Form::radio(
	            [
	                1 => __('Enable', 'cf-geoplugin'),
	                0 => __('Disable', 'cf-geoplugin'),
	            ],
	            ['name' => "{$this->metabox}[{$i}][active]", 'id' => "{$this->metabox}-{$i}-active"],
	            $active
	        );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3">
            <label><?php esc_html_e('Redirect only once', 'cf-geoplugin'); ?></label>
            <?php
	        CFGP_Form::radio(
	            [
	                1 => __('Enable', 'cf-geoplugin'),
	                0 => __('Disable', 'cf-geoplugin'),
	            ],
	            ['name' => "{$this->metabox}[{$i}][only_once]"],
	            $only_once
	        );
	    ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-6 cfgp-col-content-right cfgp-repeater-actions">
        	<button type="button" class="button button-link cfgp-remove-seo-redirection"><i class="cfa cfa-times"></i> <?php esc_html_e('Remove', 'cf-geoplugin'); ?></button>
        	<button type="button" class="button button-primary cfgp-add-seo-redirection"><i class="cfa cfa-plus"></i> <?php esc_html_e('Add New Redirection', 'cf-geoplugin'); ?></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
	<?php }

    /**
     * Add page restriction
     */
    public function add_page_restriction()
    {
        $screen = get_current_screen();

        if (isset($screen->post_type) && $screen->post_type === 'cf-geoplugin-banner') {
            $this->add_meta_box(
                CFGP_NAME . '-page-restriction',				// Unique ID
                __('Page Restriction', 'cf-geoplugin'),			// Box title
                'add_page_restriction__callback',				// Content callback, must be of type callable
                $screen->post_type,								// Post type
                'side'
            );
        }
    }

    /**
     * Add page restriction callback
     */
    public function add_page_restriction__callback($post)
    {
        ?>
<div class="cfgp-container cfgp-page-restriction-container cfgp-country-region-city-multiple-form">
	<p><?php esc_html_e('Use these options if you want to hide the current page from visitors from a specific geo location without SEO redirection.', 'cf-geoplugin'); ?></p>
	<p>
		<label for="cfgp_hide_in_countries"><?php esc_html_e('Select Countries', 'cf-geoplugin'); ?></label>
        <?php CFGP_Form::select_countries(['name' => 'cfgp_hide_in_countries', 'id' => 'cfgp_hide_in_countries'], $country, true);?>
	</p>
	<p>
		<label for="cfgp_hide_in_regions"><?php esc_html_e('Select Regions', 'cf-geoplugin'); ?></label>
        <?php CFGP_Form::select_regions(['name' => 'cfgp_hide_in_regions', 'id' => 'cfgp_hide_in_regions'], $country, true);?>
	</p>
	<p>
		<label for="cfgp_hide_in_cities"><?php esc_html_e('Select Cities', 'cf-geoplugin'); ?></label>
        <?php CFGP_Form::select_cities(['name' => 'cfgp_hide_in_cities', 'id' => 'cfgp_hide_in_cities'], $country, true);?>
	</p>
	<p>
		<label for="cfgp_default_page"><?php esc_html_e('Select page', 'cf-geoplugin'); ?></label><br>
		<select name="cfgp_default_page" id="cfgp_default_page" class="cfgp_select2">
			<option value="page_404"><?php esc_html_e('404 Page (default)', 'cf-geoplugin'); ?></option>
			<?php if ($pages = get_pages()) : foreach ($pages as $page) : ?>
			<option value="<?php echo absint($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
			<?php endforeach; endif; ?>
		</select>
		<span class="description cfgp-description"><?php esc_html_e('Select the page to be displayed instead of the current one for the defined locations.', 'cf-geoplugin'); ?></span>
	</p>
</div>
	<?php }

    /**
     * Register style
     */
    public function register_style()
    {
        $screen = get_current_screen();

        $enable_seo_posts = CFGP_Options::get('enable_seo_posts', []);

        if (!is_array($enable_seo_posts)) {
            $enable_seo_posts = [];
        }

        $enable_geo_tag = CFGP_Options::get('enable_geo_tag', []);

        if (!is_array($enable_geo_tag)) {
            $enable_geo_tag = [];
        }

        if (
            isset($screen->post_type)
            && (
                in_array($screen->post_type, $enable_seo_posts, true)
                || in_array($screen->post_type, $enable_geo_tag, true)
                || $screen->post_type === 'cf-geoplugin-banner'
            )
        ) {
            $url = CFGP_U::get_url();

            wp_enqueue_style(CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/fonts.min.css', [], (string)CFGP_VERSION);
            wp_enqueue_style(CFGP_NAME . '-metabox', CFGP_ASSETS . '/css/style-metabox.css', [CFGP_NAME . '-fontawesome'], (string)CFGP_VERSION, false);

            wp_enqueue_style(CFGP_NAME . '-select2', CFGP_ASSETS . '/css/select2.min.css', 1, '4.1.0-rc.0');
            wp_enqueue_script(CFGP_NAME . '-select2', CFGP_ASSETS . '/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);

            wp_enqueue_script(CFGP_NAME . '-metabox', CFGP_ASSETS . '/js/script-metabox.js', ['jquery', CFGP_NAME . '-select2'], (string)CFGP_VERSION, true);
            wp_localize_script(CFGP_NAME . '-metabox', 'CFGP', [
                'ajaxurl'  => admin_url('admin-ajax.php'),
                'adminurl' => self_admin_url('/'),
                'label'    => [
                    'unload'    => esc_attr__('Data will lost , Do you wish to continue?', 'cf-geoplugin'),
                    'loading'   => esc_attr__('Loading...', 'cf-geoplugin'),
                    'not_found' => esc_attr__('Not Found!', 'cf-geoplugin'),
                    'select2'   => [
                        'not_found' => [
                            'country'  => esc_attr__('Country not found.', 'cf-geoplugin'),
                            'region'   => esc_attr__('Region not found.', 'cf-geoplugin'),
                            'city'     => esc_attr__('City not found.', 'cf-geoplugin'),
                            'postcode' => esc_attr__('Postcode not found.', 'cf-geoplugin'),
                        ],
                        'type_to_search' => [
                            'country'  => esc_attr__('Start typing the name of the country.', 'cf-geoplugin'),
                            'region'   => esc_attr__('Start typing the name of the region.', 'cf-geoplugin'),
                            'city'     => esc_attr__('Start typing the name of a city.', 'cf-geoplugin'),
                            'postcode' => esc_attr__('Start typing the postcode.', 'cf-geoplugin'),
                        ],
                        'searching'      => __('Searching, please wait...', 'cf-geoplugin'),
                        'removeItem'     => __('Remove Item', 'cf-geoplugin'),
                        'removeAllItems' => __('Remove all items', 'cf-geoplugin'),
                        'loadingMore'    => __('Loading more results, please wait...', 'cf-geoplugin'),
                    ],
                ],
            ]);
        }
    }

    /*
     * Instance
     * @verson    1.0.0
     */
    public static function instance()
    {
        $class    = self::class;
        $instance = CFGP_Cache::get($class);

        if (!$instance) {
            $instance = CFGP_Cache::set($class, new self());
        }

        return $instance;
    }
} endif;
