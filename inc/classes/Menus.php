<?php
/**
 * Add controls of the navigation menu
 *
 * @link          http://infinitumform.com/
 * @since         8.0.1
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

if (!class_exists('CFGP_Menus', false)) : class CFGP_Menus extends CFGP_Global
{
    // Save cached object data
    private $menu_options = [];

    public function __construct()
    {
        $this->add_action('wp_nav_menu_item_custom_fields', 'field__enble', 10, 2);
        $this->add_action('wp_update_nav_menu_item', 'update__nav_menu_item', 10, 3);

        $this->add_filter('wp_get_nav_menu_items', 'restrict_menu_items', 1, 3);
        $this->add_filter('template_redirect', 'restrict_page_access', 1, 0);

        $this->add_filter('after_menu_locations_table', 'after_menu_locations_table', 10);

        $this->add_action('wp_ajax_cfgp_geolocate_menu', 'ajax__geolocate_menu');
        $this->add_action('wp_ajax_cfgp_geolocate_remove_menu', 'ajax__geolocate_remove_menu');

        $this->add_filter('wp_nav_menu_args', 'wp_nav_menu_args');
    }

    /*
     * New select field for the enabling geo restriction
     */
    public function field__enble($item_id, $item)
    {

        if (!apply_filters('cfgp/current_theme_supports/menus', true)) {
            return;
        }

        if (!in_array($item->type, ['post_type', 'taxonomy', 'custom'], true)) {
            return;
        }
        ?>
<div class="cfgp-menu-item-restriction cfgp-menu-item-restriction-<?php echo esc_attr($item_id); ?>" data-id="<?php echo esc_attr($item_id); ?>" style="clear: both;">
	<p class="cfgp-menu-item cfgp-menu-item-enable-restriction">
		<label for="edit-menu-item-enable-restriction-<?php echo esc_attr($item_id); ?>">
			<input type="checkbox" id="edit-menu-item-enable-restriction-<?php echo esc_attr($item_id); ?>" name="cfgp_menu_enable_restriction[<?php echo esc_attr($item_id); ?>]" value="1" data-id="<?php echo esc_attr($item_id); ?>" <?php checked(1, $this->get_values($item->object_id, 'enable', null, $item->type)); ?>> <?php esc_html_e('Enable geographic location control', 'cf-geoplugin'); ?>
		</label>
	</p>
	<div class="cfgp-menu-item-restriction-locations cfgp-menu-item-restriction-locations-<?php echo esc_attr($item_id); ?> cfgp-country-region-city-multiple-form">
		<p class="cfgp-menu-item cfgp-menu-item-countrues" style="clear: both;">
			<label for="edit-menu-item-countrues-<?php echo esc_attr($item_id); ?>"><?php esc_html_e('Hide in Countries', 'cf-geoplugin'); ?></label><br>
			<?php CFGP_Form::select_countries([
                        'name' => "cfgp_menu_countries[{$item_id}]",
                        'id'   => "edit-menu-item-countrues-{$item_id}",
                    ], $this->get_values($item->object_id, 'countries', [], $item->type), true, true); ?><br>
			<button type="button" class="button cfgp-select-all" data-target="edit-menu-item-countrues-<?php echo esc_attr($item_id); ?>"><object data="<?php echo esc_url(CFGP_ASSETS . '/images/select.svg'); ?>" width="10" height="10"></object> <?php esc_attr_e('Select/Deselect all', 'cf-geoplugin'); ?></button>
		</p>
		<p class="cfgp-menu-item cfgp-menu-item-regions">
			<label for="edit-menu-item-regions-<?php echo esc_attr($item_id); ?>"><?php esc_html_e('Hide in Regions', 'cf-geoplugin'); ?></label><br>
			<?php CFGP_Form::select_regions([
                        'name'         => "cfgp_menu_regions[{$item_id}]",
                        'id'           => "edit-menu-item-regions-{$item_id}",
                        'country_code' => $this->get_values($item->object_id, 'countries', [], $item->type),
                    ], $this->get_values($item->object_id, 'regions', [], $item->type), true, true); ?>
		</p>
		<p class="cfgp-menu-item cfgp-menu-item-cities">
			<label for="edit-menu-item-cities-<?php echo esc_attr($item_id); ?>"><?php esc_html_e('Hide in Cities', 'cf-geoplugin'); ?></label><br>
			<?php CFGP_Form::select_cities([
                        'name'         => "cfgp_menu_cities[{$item_id}]",
                        'id'           => "edit-menu-item-cities-{$item_id}",
                        'country_code' => $this->get_values($item->object_id, 'countries', [], $item->type),
                    ], $this->get_values($item->object_id, 'cities', [], $item->type), true, true); ?>
		</p>
	</div>
	<p class="cfgp-menu-item-description"><?php esc_html_e('If you enable this option, in selected locations navigation will be hidden from the public as well as direct access to the URL.', 'cf-geoplugin'); ?></p>
</div>
		<?php
    }

    /*
     * Update menu items settings
     */
    public function update__nav_menu_item($menu_id, $menu_item_db_id, $item)
    {

        // Clear cache
        $this->menu_options = [];
        // Set controls
        $control = CFGP_Options::sanitize([
            'enable'    => sanitize_text_field($_POST['cfgp_menu_enable_restriction'][$menu_item_db_id] ?? null),
            'countries' => array_map('sanitize_text_field', ($_POST['cfgp_menu_countries'][$menu_item_db_id] ?? [])),
            'regions'   => array_map('sanitize_text_field', ($_POST['cfgp_menu_regions'][$menu_item_db_id] ?? [])),
            'cities'    => array_map('sanitize_text_field', ($_POST['cfgp_menu_cities'][$menu_item_db_id] ?? [])),
        ]);

        // Custom links need to be saved as post meta with menu ID
        if (in_array($item['menu-item-type'], ['custom'], true)) {
            if ($control['enable']) {
                update_post_meta($menu_item_db_id, 'cfgp_menu_item_control', $control);
            } else {
                if (empty($control['countries']) && empty($control['regions']) && empty($control['cities'])) {
                    delete_post_meta($menu_item_db_id, 'cfgp_menu_item_control');
                } else {
                    update_post_meta($menu_item_db_id, 'cfgp_menu_item_control', $control);
                }
            }
        }
        // Post types need to be saved as post meta with page ID
        elseif (in_array($item['menu-item-type'], ['post_type'], true)) {
            if ($control['enable']) {
                update_post_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control);
            } else {
                if (empty($control['countries']) && empty($control['regions']) && empty($control['cities'])) {
                    delete_post_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control');
                } else {
                    update_post_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control);
                }
            }
        }
        // Taxonomy need to be saved as term meta with term ID
        elseif (in_array($item['menu-item-type'], ['taxonomy'], true)) {
            if ($control['enable']) {
                update_term_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control);
            } else {
                if (empty($control['countries']) && empty($control['regions']) && empty($control['cities'])) {
                    delete_term_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control');
                } else {
                    update_term_meta(absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control);
                }
            }
        }
    }

    /*
     * New select field for the City
     */
    public function restrict_menu_items($items, $menu, $args)
    {
        if (!apply_filters('cfgp/current_theme_supports/menus', true)) {
            return $items;
        }

        // In admin menu we need this visible
        if (is_admin()) {
            return $items;
        }

        // Iterate over the items to search and destroy
        foreach ($items as $key => $item) {
            if ($control = $this->get_values((in_array($item->type, ['custom'], true) ? $item->ID : $item->object_id), null, [
                'enable'    => null,
                'countries' => [],
                'regions'   => [],
                'cities'    => [],
            ], $item->type)) {
                if ($control['enable'] == 1) {
                    $protect = false;

                    $mode = [ null, 'country', 'region', 'city' ];
                    $mode = $mode[ count(array_filter(array_map(
                        function ($obj) {
                            return !empty($obj);
                        },
                        [
                            $control['cities'],
                            $control['regions'],
                            $control['countries'],
                        ]
                    ))) ];

                    if (empty($control['regions']) && !empty($control['cities'])) {
                        $mode = 'country_city';
                    }

                    switch ($mode) {
                        case 'country':
                            if (CFGP_U::check_user_by_country($control['countries'])) {
                                $protect = true;
                            }
                            break;
                        case 'region':
                            if (
                                CFGP_U::check_user_by_region($control['regions'])
                                && CFGP_U::check_user_by_country($control['countries'])
                            ) {
                                $protect = true;
                            }
                            break;
                        case 'city':
                            if (
                                CFGP_U::check_user_by_city($control['cities'])
                                && CFGP_U::check_user_by_region($control['regions'])
                                && CFGP_U::check_user_by_country($control['countries'])
                            ) {
                                $protect = true;
                            }
                            break;
                        case 'country_city':
                            if (
                                CFGP_U::check_user_by_city($control['cities'])
                                && CFGP_U::check_user_by_country($control['countries'])
                            ) {
                                $protect = true;
                            }
                            break;
                    }

                    if ($protect) {
                        unset($items[$key]);
                    }
                }
            }
        }

        // Return
        return $items;
    }

    /*
     * Prevent pages to be seen
     */
    public function restrict_page_access()
    {
        if (!apply_filters('cfgp/current_theme_supports/menus', true)) {
            return;
        }

        global $wp_query;
        $page_id = get_the_ID();

        if ($control = $this->get_values($page_id, null, [
            'enable'    => null,
            'countries' => [],
            'regions'   => [],
            'cities'    => [],
        ])) {
            if ($control['enable'] == 1) {
                $protect = false;

                $mode = [ null, 'country', 'region', 'city' ];
                $mode = $mode[ count(array_filter(array_map(
                    function ($obj) {
                        return !empty($obj);
                    },
                    [
                        $control['cities'],
                        $control['regions'],
                        $control['countries'],
                    ]
                ))) ];

                if (empty($control['regions']) && !empty($control['cities'])) {
                    $mode = 'country_city';
                }

                switch ($mode) {
                    case 'country':
                        if (CFGP_U::check_user_by_country($control['countries'])) {
                            $protect = true;
                        }
                        break;
                    case 'region':
                        if (
                            CFGP_U::check_user_by_region($control['regions'])
                            && CFGP_U::check_user_by_country($control['countries'])
                        ) {
                            $protect = true;
                        }
                        break;
                    case 'city':
                        if (
                            CFGP_U::check_user_by_city($control['cities'])
                            && CFGP_U::check_user_by_region($control['regions'])
                            && CFGP_U::check_user_by_country($control['countries'])
                        ) {
                            $protect = true;
                        }
                        break;
                    case 'country_city':
                        if (
                            CFGP_U::check_user_by_city($control['cities'])
                            && CFGP_U::check_user_by_country($control['countries'])
                        ) {
                            $protect = true;
                        }
                        break;
                }

                if ($protect) {
                    $wp_query->set_404();
                    status_header(302);
                    get_template_part(404);
                    exit;
                }
            }
        }
    }

    /*
     * Include Geolocate Menus setting
     */
    public function after_menu_locations_table()
    {
        if (!apply_filters('cfgp/current_theme_supports/menus', true)) {
            return;
        }

        if (!current_user_can('edit_theme_options')) {
            return;
        }

        global $locations, $menu_locations, $num_locations, $wpdb;

        if ($num_locations === 0) {
            return;
        }

        if (empty($locations)) {
            $locations = get_registered_nav_menus();
        }

        /*
        $geolocate_menus = get_terms( array(
            'taxonomy' => 'nav_menu',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'country',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'location',
                    'compare' => 'EXISTS'
                )
            )
        ) );
        */

        $geolocate_menus = $wpdb->get_results("
			SELECT DISTINCT
				`menu`.*,
				`country`.`meta_value` AS `country`,
				`location`.`meta_value` AS `location`
			FROM
				`{$wpdb->terms}` `menu`
				INNER JOIN `{$wpdb->termmeta}` `country` ON `country`.`term_id` = `menu`.`term_id` AND `country`.`meta_key` = 'country'
				INNER JOIN `{$wpdb->termmeta}` `location` ON `location`.`term_id` = `menu`.`term_id` AND `location`.`meta_key` = 'location'
			ORDER BY `menu`.`term_id` DESC
		");

        $countries = CFGP_Library::get_countries();
        ?>
<div id="cfgp-menu-locations-wrap">
	<h3><?php esc_html_e('Geolocate Menus', 'cf-geoplugin'); ?></h3>
	<p><?php esc_html_e('Create a navigation menu based on the geolocation of your users.', 'cf-geoplugin'); ?></p>
	<p><?php esc_html_e('The principle is simple. Make the default menu first in the standard way. Then come back here and create the navigations for the geo locations you want. After that, fill those locations with Menu items and your users will always see a navigation menu based on the geo location.', 'cf-geoplugin'); ?></p>
	<table class="widefat fixed" id="menu-geo-locations-table">
		<thead></thead>
		<tbody>
			<tr>
				<td colspan="4">
					<table id="cfgp-add-new-menu-locations">
						<tr>
							<td class="cfgp-menu-locations-select">
								<select name="cfgp-menu-locations-select" id="cfgp-menu-locations-select">
									<option value="">—<?php esc_html_e('Select Location', 'cf-geoplugin'); ?>—</option>
								<?php foreach ($locations as $location => $description) : ?>
									<option value="<?php echo esc_attr($location); ?>"><?php echo esc_html($description); ?></option>
								<?php endforeach; ?>
								</select>
							</td>
							<td class="cfgp-menu-country-select"><?php
                                        CFGP_Form::select_countries([
                                            'name'  => 'cfgp-menu-country-select',
                                            'id'    => 'cfgp-menu-country-select',
                                            'class' => 'cfgp_select2',
                                        ], '');
        ?></td>
							<td  class="cfgp-menu-options" colspan="2" style="text-align:right;">
								<input type="button" name="nav-menu-locations" class="button button-primary right" id="cfgp-menu-add-location" data-nonce="<?php echo esc_attr(wp_create_nonce('cf-geoplugin-create-menus')); ?>" data-uid="<?php echo esc_attr(get_current_user_id()); ?>" value="<?php esc_attr_e('+ Add New', 'cf-geoplugin'); ?>">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</tbody>
		<thead>
			<tr>
				<th scope="col">
					<?php esc_html_e('Menu name', 'cf-geoplugin'); ?>
				</th>
				<th scope="col">
					<?php esc_html_e('Menu Location', 'cf-geoplugin'); ?>
				</th>
				<th scope="col">
					<?php esc_html_e('Country Location', 'cf-geoplugin'); ?>
				</th>
				<th scope="col" style="text-align: right;">
					<?php esc_html_e('Options', 'cf-geoplugin'); ?>
				</th>
			</tr>
		</thead>
		<tbody id="cfgp-menu-locations">
			<?php
                if ($geolocate_menus) :

                    foreach ($geolocate_menus as $i => $geo_menu) :
                        if (!isset($locations[$geo_menu->location])) {
                            continue;
                        }
                        ?>
			<tr class="cfgp-menu-location-item" data-country="<?php echo esc_attr($geo_menu->country); ?>" data-location="<?php echo esc_attr($geo_menu->location); ?>" data-id="<?php echo esc_attr($geo_menu->term_id); ?>" id="cfgp-menu-location-item-<?php echo esc_attr($geo_menu->country); ?>-<?php echo esc_attr($geo_menu->location); ?>">
				<td><?php echo esc_html($geo_menu->name); ?></td>
				<td><?php echo esc_html($locations[$geo_menu->location]); ?></td>
				<td><?php echo esc_html($countries[$geo_menu->country] ?? $geo_menu->country); ?></td>
				<td style="text-align: right;">
					<a href="javascript:void(0);" 
						class="submitdelete deletion right cfgp-menu-remove-location" 
						data-confirm="<?php esc_attr_e('Are you sure you want to delete the entire menu for this location?', 'cf-geoplugin'); ?>" 
						data-id="<?php echo esc_attr(absint($geo_menu->term_id)); ?>"
						data-nonce="<?php echo esc_attr(wp_create_nonce('cf-geoplugin-delete-menus-' . absint($geo_menu->term_id))); ?>"
						data-uid="<?php echo esc_attr(get_current_user_id()); ?>"><?php esc_html_e('Delete', 'cf-geoplugin'); ?></a>
				</td>
			</tr>
			<?php endforeach;
                else : ?>
			<tr>
				<td colspan="4"><?php esc_html_e('No menus for defined geolocations have been created yet.', 'cf-geoplugin'); ?></td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col">
					<?php esc_html_e('Menu name', 'cf-geoplugin'); ?>
				</th>
				<th scope="col">
					<?php esc_html_e('Menu Location', 'cf-geoplugin'); ?>
				</th>
				<th scope="col">
					<?php esc_html_e('Country Location', 'cf-geoplugin'); ?>
				</th>
				<th scope="col" style="text-align: right;">
					<?php esc_html_e('Options', 'cf-geoplugin'); ?>
				</th>
			</tr>
		</tfoot>
	</table>
</div>
<?php add_action('admin_footer', function () { ?>
<style>
/* <![CDATA[ */
#cfgp-add-new-menu-locations {
	width:100%;
}

#cfgp-add-new-menu-locations select {
	width:100%;
}

#cfgp-add-new-menu-locations tr > td:nth-child(1){
	width:25%;
}

#cfgp-add-new-menu-locations tr > td:nth-child(2){
	width:25%;
}
 
#cfgp-add-new-menu-locations tr > td:nth-child(3){
	width:50%;
}

@media all and (max-width: 1320px) {
	#cfgp-add-new-menu-locations tr > td:nth-child(1){
		width:33.33%;
	}

	#cfgp-add-new-menu-locations tr > td:nth-child(2){
		width:33.33%;
	}
	 
	#cfgp-add-new-menu-locations tr > td:nth-child(3){
		width:33.33%;
	}
}
/* ]]> */
</style>
<?php });
    }

    /*
     * Geolocate Menus
     */
    public function wp_nav_menu_args($args = [])
    {
        if (!apply_filters('cfgp/current_theme_supports/menus', true)) {
            return $args;
        }

        global $wpdb;

        if ($geolocate_menus = $wpdb->get_results("
			SELECT DISTINCT
				`menu`.*,
				`country`.`meta_value` AS `country`,
				`location`.`meta_value` AS `location`
			FROM
				`{$wpdb->terms}` `menu`
				INNER JOIN `{$wpdb->termmeta}` `country` ON `country`.`term_id` = `menu`.`term_id` AND `country`.`meta_key` = 'country'
				INNER JOIN `{$wpdb->termmeta}` `location` ON `location`.`term_id` = `menu`.`term_id` AND `location`.`meta_key` = 'location'
			ORDER BY `menu`.`term_id` DESC
		")) :

            // This retun 2 letter country code from Geo Controller
            $country_code = strtolower(CFGP_U::api('country_code'));

            // Assign location
            foreach ($geolocate_menus as $i => $geo_menu) :
                $theme_location = get_term_meta($geo_menu->term_id, 'location', true);
                $theme_country  = get_term_meta($geo_menu->term_id, 'country', true);

                if ($args['theme_location'] === $theme_location && $theme_country === $country_code) {
                    $args['menu'] = $geo_menu->slug;
                }
            endforeach;
        endif;

        // Return
        return $args;
    }

    /*
     * Geolocate Menus add/show menus
     */
    public function ajax__geolocate_menu($call_direct_via_function = false)
    {

        if (!wp_verify_nonce(sanitize_text_field($_POST['cf_nonce'] ?? 'nope'), 'cf-geoplugin-create-menus') && !$call_direct_via_function) {
            exit;
        }

        $uid = absint(sanitize_text_field($_POST['uid'] ?? 0));

        if (!user_can($uid, 'edit_theme_options')) {
            exit;
        }

        $locations = get_registered_nav_menus();
        $countries = CFGP_Library::get_countries();

        $country  = sanitize_text_field($_POST['country'] ?? null);
        $location = sanitize_text_field($_POST['location'] ?? null);

        if ($country && $location) {
            $menu_name = sprintf(
                __('%s for %s', 'cf-geoplugin'),
                esc_html($locations[$location] ?? $location),
                esc_html($countries[$country] ?? $country)
            );

            $menu_slug = sanitize_title(join('-', ['cfgp', $location, $country]));

            if ($nav_menu = wp_insert_term(
                $menu_name,
                'nav_menu',
                [
                    'slug' => $menu_slug,
                ]
            )) {
                if (!is_wp_error($nav_menu)) {
                    update_term_meta($nav_menu['term_id'], 'country', $country);
                    update_term_meta($nav_menu['term_id'], 'location', $location);
                } else {
                    //	CFGP_U::dump($nav_menu);
                }
            }
        }

        global $wpdb;

        if ($geolocate_menus = $wpdb->get_results("
			SELECT DISTINCT
				`menu`.*,
				`country`.`meta_value` AS `country`,
				`location`.`meta_value` AS `location`
			FROM
				`{$wpdb->terms}` `menu`
				INNER JOIN `{$wpdb->termmeta}` `country` ON `country`.`term_id` = `menu`.`term_id` AND `country`.`meta_key` = 'country'
				INNER JOIN `{$wpdb->termmeta}` `location` ON `location`.`term_id` = `menu`.`term_id` AND `location`.`meta_key` = 'location'
			ORDER BY `menu`.`term_id` DESC
		")) :

            foreach ($geolocate_menus as $i => $geo_menu) :
                $geo_menu_location = get_term_meta($geo_menu->term_id, 'location', true);
                $geo_menu_country  = get_term_meta($geo_menu->term_id, 'country', true);

                if (!isset($locations[$geo_menu_location])) {
                    continue;
                }
                ?>
		<tr class="cfgp-menu-location-item" data-country="<?php echo esc_attr($geo_menu_country); ?>" data-location="<?php echo esc_attr($geo_menu_location); ?>" data-id="<?php echo esc_attr($geo_menu->term_id); ?>" id="cfgp-menu-location-item-<?php echo esc_attr($geo_menu_country); ?>-<?php echo esc_attr($geo_menu_location); ?>">
			<td><?php echo esc_html($geo_menu->name); ?></td>
			<td><?php echo esc_html($locations[$geo_menu_location] ?? $geo_menu_location); ?></td>
			<td><?php echo esc_html($countries[$geo_menu_country] ?? $geo_menu_country); ?></td>
			<td style="text-align: right;">
				<a href="javascript:void(0);" 
					class="submitdelete deletion right cfgp-menu-remove-location" 
					data-confirm="<?php esc_attr_e('Are you sure you want to delete the entire menu for this location?', 'cf-geoplugin'); ?>" 
					data-id="<?php echo esc_attr(absint($geo_menu->term_id)); ?>" 
					data-nonce="<?php echo esc_attr(wp_create_nonce('cf-geoplugin-delete-menus-' . absint($geo_menu->term_id))); ?>" 
					data-uid="<?php echo esc_attr($uid); ?>"><?php esc_html_e('Delete', 'cf-geoplugin'); ?></a>
			</td>
		</tr>
		<?php endforeach;
        else : ?>
		<tr>
			<td colspan="4"><?php esc_html_e('No menus for defined geolocations have been created yet.', 'cf-geoplugin'); ?></td>
		</tr>
		<?php endif;
        exit;
    }

    /*
     * Geolocate Menus remove menu
     */
    public function ajax__geolocate_remove_menu()
    {

        $term_id = absint(sanitize_text_field($_POST['term_id'] ?? 0));

        if (!wp_verify_nonce(sanitize_text_field($_POST['cf_nonce'] ?? 'nope'), 'cf-geoplugin-delete-menus-' . $term_id)) {
            exit;
        }

        $uid = absint(sanitize_text_field($_POST['uid'] ?? 0));

        if (!user_can($uid, 'edit_theme_options')) {
            exit;
        }

        if ($term_id) {
            if ($nav_menu_items = get_posts([
                'post_type'   => 'nav_menu_item',
                'numberposts' => -1,
                'tax_query'   => [
                    [
                        'taxonomy' => 'nav_menu',
                        'field'    => 'term_id',
                        'terms'    => $term_id,
                    ],
                ],
            ])) {
                foreach ($nav_menu_items as $item) {
                    wp_delete_post($item->ID, true);
                }
            }

            wp_delete_term(
                $term_id,
                'nav_menu'
            );
        }

        $this->ajax__geolocate_menu(true);
    }

    /*
     * Get values for input fields
     */
    private function get_values($item_id, $option, $default = null, $type = 'post_type')
    {

        // Post types and custom links are inside post meta
        $function = 'get_post_meta';

        // Taxonomy need to be look at term meta
        if ($type == 'taxonomy') {
            $function = 'get_term_meta';
        }

        $key = "{$function}_{$item_id}";

        if ($this->menu_options[$key] ?? null) {
            if (null === $option) {
                return $this->menu_options[$key];
            } else {
                return $this->menu_options[$key][$option];
            }
        } elseif ($control = $function($item_id, 'cfgp_menu_item_control', true)) {
            $this->menu_options[$key] = CFGP_Options::sanitize($control);

            if (null === $option) {
                return $this->menu_options[$key];
            } else {
                return $this->menu_options[$key][$option];
            }
        }

        return $default;
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
