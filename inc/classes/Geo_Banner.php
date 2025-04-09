<?php
/**
 * Register custom post type
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

if (!class_exists('CFGP_Geo_Banner', false)) : class CFGP_Geo_Banner extends CFGP_Global
{
    public function __construct()
    {
        $this->add_action('init', 'register');

        $this->add_filter('manage_posts_columns', 'columns_banner');
        $this->add_action('manage_posts_custom_column', 'columns_banner_content', 10, 2);
        $this->add_action('delete_post', 'delete_post', 10);
        $this->add_action('add_meta_boxes', 'add_meta_boxes', 1);
        $this->add_action('save_post', 'save_post');

        $this->add_action('wp_ajax_cf_geoplugin_banner_cache', 'ajax__geoplugin_banner_cache');
        $this->add_action('wp_ajax_nopriv_cf_geoplugin_banner_cache', 'ajax__geoplugin_banner_cache');

        $this->add_filter('single_template', 'add_custom_single_template', 20, 1);
    }

    /*
     * Register elementor geo banner page
     * @verson    1.0.0
     */
    public function add_custom_single_template($template)
    {
        global $post;

        if (($post->post_type === 'cf-geoplugin-banner') && file_exists(CFGP_PLUGINS . '/elementor/page/cfgp-banner.php')) {
            $template = CFGP_PLUGINS . '/elementor/page/cfgp-banner.php';
        }

        return $template;
    }

    /**
     * AJAX - Fix cache on cached websites
     */
    public function ajax__geoplugin_banner_cache()
    {
        header('Cache-Control: max-age=900, must-revalidate');

        global $wpdb;

        // Check action
        if (CFGP_U::request_string('action') != 'cf_geoplugin_banner_cache') {
            header_remove('Cache-Control');
            wp_send_json_error([
                'error'         => true,
                'error_message' => __('You are not authorized to access this information!', 'cf-geoplugin'),
                'status'        => 403,
            ]);
            exit;
        }

        // Get transient ID
        $transient_id = CFGP_U::request_string('nonce');
        $data         = get_transient('cfgp-' . $transient_id);

        // Check if transient exists
        if (!$data) {
            header_remove('Cache-Control');
            wp_send_json_error([
                'error'         => true,
                'error_message' => __('You are not authorized to access this information.', 'cf-geoplugin'),
                'status'        => 403,
            ]);
            exit;
        }

        // Check secret key
        if (sanitize_text_field($data['key']) !== CFGP_U::CACHE_KEY()) {
            delete_transient('cfgp-' . $transient_id);
            header_remove('Cache-Control');
            wp_send_json_error([
                'error'         => true,
                'error_message' => __('Invalid key. You are not authorized to access this information.', 'cf-geoplugin'),
                'status'        => 400,
            ]);
            exit;
        }

        // Check hash
        if (sanitize_text_field($data['hash']) !== $transient_id) {
            delete_transient('cfgp-' . $transient_id);
            header_remove('Cache-Control');
            wp_send_json_error([
                'error'         => true,
                'error_message' => __('Hash mismatch. You are not authorized to access this information.', 'cf-geoplugin'),
                'status'        => 400,
            ]);
            exit;
        }

        // Define data
        $setup = [
            'id'             => absint(sanitize_text_field($data['id'])),
            'posts_per_page' => absint(sanitize_text_field($data['posts_per_page'])),
            'class'          => sanitize_text_field($data['class']),
        ];

        $cont = sanitize_textarea_field($data['content']);

        // ID is the valid
        if (intval($setup['id']) <= 0) {
            wp_send_json_success([
                'response' => $cont,
                'error'    => false,
                'status'   => 200,
            ]);
            exit;
        }

        // Update
        foreach ([
            'cf-geoplugin-country' => 'cfgp-banner-location-country',
            'cf-geoplugin-region'  => 'cfgp-banner-location-region',
            'cf-geoplugin-city'    => 'cfgp-banner-location-city',
        ] as $get_post_terms => $update_post_meta) {
            if ($all_terms = wp_get_post_terms($setup['id'], $get_post_terms, ['fields' => 'all'])) {
                $tax_collection = [];

                foreach ($all_terms as $fetch) {
                    $tax_collection[] = $fetch->slug;
                }

                if (!empty($tax_collection)) {
                    update_post_meta($setup['id'], $update_post_meta, $tax_collection);
                } else {
                    delete_post_meta($setup['id'], $update_post_meta);
                }
                wp_set_post_terms($setup['id'], '', $get_post_terms);
            }
        }

        // Build SQL search
        $country_sql = '%"' . $wpdb->esc_like(CFGP_U::api('country_code')) . '"%';
        $region_sql  = '%"' . $wpdb->esc_like(sanitize_title(CFGP_U::transliterate(CFGP_U::api('region')))) . '"%';
        $city_sql    = '%"' . $wpdb->esc_like(sanitize_title(CFGP_U::transliterate(CFGP_U::api('city')))) . '"%';

        // Execute
        $post = $wpdb->get_row($wpdb->prepare("
			SELECT
				`banner`.`ID`,
				`banner`.`post_title`,
				`banner`.`post_content`
			FROM
				`{$wpdb->posts}` AS `banner`
			LEFT JOIN
				`{$wpdb->postmeta}` AS `c` ON `c`.`post_id` = `banner`.`ID` AND `c`.`meta_key` = 'cfgp-banner-location-country'
			LEFT JOIN
				`{$wpdb->postmeta}` AS `r` ON `r`.`post_id` = `banner`.`ID` AND `r`.`meta_key` = 'cfgp-banner-location-region'
			LEFT JOIN
				`{$wpdb->postmeta}` AS `s` ON `s`.`post_id` = `banner`.`ID` AND `s`.`meta_key` = 'cfgp-banner-location-city'
			WHERE
				`banner`.`ID` = %d
				AND `banner`.`post_type` = 'cf-geoplugin-banner'
				AND `post_status` = 'publish'
				AND (`c`.`meta_value` LIKE %s OR `c`.`meta_value` IS NULL)
				AND (`r`.`meta_value` LIKE %s OR `r`.`meta_value` IS NULL)
				AND (`s`.`meta_value` LIKE %s OR `s`.`meta_value` IS NULL)
			LIMIT 1
		", absint($setup['id']), $country_sql, $region_sql, $city_sql));

        $content = '';

        if ($post) {
            $post->post_content = do_shortcode($post->post_content);
            $post->post_content = CFGP_U::the_content($post->post_content);
            $content            = CFGP_U::fragment_caching($post->post_content, false);
        }

        if (empty($content) && !empty($cont)) {
            $content = do_shortcode($cont);
            $content = CFGP_U::the_content($content);
        }

        wp_send_json_success([
            'response' => wp_kses_post($content),
            'error'    => false,
            'status'   => 200,
        ]);
        exit;
    }

    /**
     * Register post type
     */
    public function register()
    {

        $elementor_support = [];

        if (is_plugin_active('elementor/elementor.php')) {
            if ($es_support = get_option('elementor_cpt_support')) {
                $elementor_support = $es_support;
            }
        }

        $projects = [
            'labels' => [
                'name'                  => __('Geo Banner', 'cf-geoplugin'),
                'singular_name'         => __('Geo Banner', 'cf-geoplugin'),
                'add_new'               => __('Add New Banner', 'cf-geoplugin'),
                'add_new_item'          => __('Add New Banner', 'cf-geoplugin'),
                'edit_item'             => __('Edit Banner', 'cf-geoplugin'),
                'new_item'              => __('New Banner', 'cf-geoplugin'),
                'view_item'             => __('View Banner', 'cf-geoplugin'),
                'search_items'          => __('Search Banner', 'cf-geoplugin'),
                'not_found'             => __('No Banner Found', 'cf-geoplugin'),
                'not_found_in_trash'    => __('No Banner Found in Trash', 'cf-geoplugin'),
                'parent_item_colon'     => '',
                'featured_image'        => __('Banner Image', 'cf-geoplugin'),
                'set_featured_image'    => __('Select Banner Image', 'cf-geoplugin'),
                'remove_featured_image' => __('Remove Banner Image', 'cf-geoplugin'),
                'use_featured_image'    => __('Use Banner Image', 'cf-geoplugin'),
                'insert_into_item'      => __('Insert Into Banner', 'cf-geoplugin'),
            ],
            'public'              => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => in_array('cf-geoplugin-banner', $elementor_support, true),
            'show_in_nav_menus'   => false,
            'show_ui'             => (CFGP_Options::get('enable_banner', 1) ? true : false),
            'query_var'           => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'capability_type'     => 'post',
            'supports'            => [ 'title', 'editor', 'tags' ],
            'menu_icon'           => 'dashicons-pressthis',
            'show_in_menu'        => false,
        ];

        if (!post_type_exists('cf-geoplugin-banner')) {
            register_post_type('cf-geoplugin-banner', $projects);
            flush_rewrite_rules();
        }
    }

    /**
     * Hook for the post delete
     */
    public function delete_post($id)
    {
        // Remove cookie if they exists
        if (isset($_COOKIE) && !empty($_COOKIE)) {
            $cookie_name = '__cfgp_seo_' . esc_attr($id) . '_once_';

            foreach (array_keys($_COOKIE) as $key) {
                $key = sanitize_text_field($key);

                if (strpos($key, $cookie_name) !== false) {
                    CFGP_U::setcookie($key, CFGP_TIME . '', (CFGP_TIME - ((365 * DAY_IN_SECONDS) * 2)));
                    unset($_COOKIE[$key]);
                }
            }
        }
    }

    /**
     * Hook for the post save/update
     */
    public function save_post($post_id)
    {
        if (
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can('edit_post', $post_id)
            || (get_post_type($post_id) !== 'cf-geoplugin-banner')
        ) {
            return;
        }

        $fields = [
            'cfgp-banner-default-content'   => 'cfgp-banner-default',
            'cfgp-banner-location-country'  => 'cfgp-banner-location-country',
            'cfgp-banner-location-region'   => 'cfgp-banner-location-region',
            'cfgp-banner-location-city'     => 'cfgp-banner-location-city',
            'cfgp-banner-location-postcode' => 'cfgp-banner-location-postcode',
        ];

        foreach ($fields as $requestKey => $metaKey) {
            $value = CFGP_U::request($requestKey, '');

            if ($value && isset($fields[$requestKey])) {
                $sanitizedValue = ($requestKey === 'cfgp-banner-default-content') ? wp_kses_post($value) : CFGP_Options::sanitize($value);
                update_post_meta($post_id, $metaKey, $sanitizedValue);
            } else {
                delete_post_meta($post_id, $metaKey);
            }
        }

        $taxonomies = ['cf-geoplugin-country', 'cf-geoplugin-region', 'cf-geoplugin-city', 'cf-geoplugin-postcode'];

        foreach ($taxonomies as $taxonomy) {
            wp_set_post_terms($post_id, '', $taxonomy);
        }
    }

    /**
     * Banner Head
     *
     * @since    4.0.0
     */
    public function columns_banner($column_name)
    {
        $url = CFGP_U::parse_url();

        if (strpos($url['url'], 'post_type=cf-geoplugin-banner') !== false) {
            $column_name['cf_geo_banner_shortcode'] = __('Shortcode', 'cf-geoplugin');
            $column_name['cf_geo_banner_locations'] = __('Locations', 'cf-geoplugin');
        }

        return $column_name;
    }

    /**
     * Banner head content
     *
     * @since    4.0.0
     */
    public function columns_banner_content($column_name, $post_ID)
    {
        $url = CFGP_U::parse_url();

        if (strpos($url['url'], 'post_type=cf-geoplugin-banner') === false) {
            return;
        }

        switch ($column_name) {
            case 'cf_geo_banner_shortcode':
                echo '<ul>';
                echo '<li><strong>' . esc_html__('Standard', 'cf-geoplugin') . ':</strong><br><code>[cfgeo_banner id="' . esc_attr($post_ID) . '"]</code></li>';
                echo '<li><strong>' . esc_html__('Advanced', 'cf-geoplugin') . ':</strong><br><code>[cfgeo_banner id="' . esc_attr($post_ID) . '"]' . esc_html__('Default content', 'cf-geoplugin') . '[/cfgeo_banner]</code></li>';
                echo '</ul>';
                break;

            case 'cf_geo_banner_locations':
                $fields = [
                    esc_html__('Countries', 'cf-geoplugin') => 'country',
                    esc_html__('Regions', 'cf-geoplugin')   => 'region',
                    esc_html__('Cities', 'cf-geoplugin')    => 'city',
                    esc_html__('Postcodes', 'cf-geoplugin') => 'postcode',
                ];
                $print = [];

                foreach ($fields as $name => $field) {
                    $data = get_post_meta($post_ID, "cfgp-banner-location-{$field}", true);

                    if ($data) {
                        $formatted = $this->formatData($field, $data);
                        $print[]   = '<li><strong>' . esc_html($name) . ':</strong><br>' . wp_kses_post(join(', ', $formatted)) . '</li>';
                    }
                }

                if (empty($print)) {
                    $print = $this->fetchTaxonomyData($post_ID);
                }

                echo empty($print) ? '( ' . esc_html__('undefined', 'cf-geoplugin') . ' )' : '<ul>' . wp_kses_post(join("\r\n", $print)) . '</ul>';
                break;
        }
    }

    /**
     * Format data
     */
    private function formatData($field, $data)
    {
        switch ($field) {
            case 'country':
                return array_map('strtoupper', $data);

            case 'region':
            case 'city':
                return array_map(function ($match) {
                    return ucwords(str_replace('-', ' ', $match));
                }, $data);

            default:
                return $data;
        }
    }

    /**
     * Fetch taxonomy data
     */
    private function fetchTaxonomyData($post_ID)
    {
        $taxonomies = [
            __('Countries', 'cf-geoplugin') => 'cf-geoplugin-country',
            __('Regions', 'cf-geoplugin')   => 'cf-geoplugin-region',
            __('Cities', 'cf-geoplugin')    => 'cf-geoplugin-city',
            __('Postcode', 'cf-geoplugin')  => 'cf-geoplugin-postcode',
        ];
        $print = [];

        foreach ($taxonomies as $name => $taxonomy) {
            $terms = wp_get_post_terms($post_ID, $taxonomy, ['fields' => 'all']);
            $links = [];

            foreach ($terms as $term) {
                $editLink = get_edit_term_link($term->term_id, $taxonomy, 'cf-geoplugin-banner');
                $links[]  = '<a href="' . esc_url($editLink) . '">' . esc_html($term->name . (empty($term->description) ? '' : " ({$term->description})")) . '</a>';
            }

            if ($links) {
                $print[] = '<li><strong>' . esc_html($name) . ':</strong><br>' . join(', ', $links) . '</li>';
            }
        }

        return $print;
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes()
    {
        $screen = get_current_screen();

        if (isset($screen->post_type) && $screen->post_type === 'cf-geoplugin-banner') {
            $this->add_meta_box(
                CFGP_NAME . '-banner-default-content',					// Unique ID
                __('Geo Banner default content', 'cf-geoplugin'),			// Box title
                'add_meta_box__default_content',						// Content callback, must be of type callable
                'cf-geoplugin-banner',									// Post type
                'advanced',
                'high'
            );

            $this->add_meta_box(
                CFGP_NAME . '-banner-sc',			// Unique ID
                __('Shortcodes', 'cf-geoplugin'),		// Box title
                'add_meta_box__shortcode',			// Content callback, must be of type callable
                'cf-geoplugin-banner',				// Post type
                'advanced',
                'high'
            );
            $this->add_meta_box(
                CFGP_NAME . '-banner-settings',		// Unique ID
                __('Settings', 'cf-geoplugin'),		// Box title
                'add_meta_box__settings',			// Content callback, must be of type callable
                'cf-geoplugin-banner',				// Post type
                'side'
            );
        }

        return;
    }

    /**
     * Default banner content
     */
    public function add_meta_box__default_content($post)
    {
        $banner = get_post_meta($post->ID, 'cfgp-banner-default', true);

        if (!$banner) {
            $banner = get_post_meta($post->ID, CFGP_METABOX . 'banner_default', true);
        }
        ?>
<p style="color:#550000;"><?php esc_html_e('This content is shown only when the selected location is not found. This means that anyone who is not from the set location will see this content.', 'cf-geoplugin'); ?></p>
<?php wp_editor($banner, 'cfgp-banner-default-content', $settings = ['textarea_name' => 'cfgp-banner-default-content']);
    }

    /**
     * Geo banner settings
     */
    public function add_meta_box__settings($post)
    {
        $country_code = [];

        // Get old taxonomies from the prevous version and merge with new one
        $taxonomy_list = [
            __('Select Countries', 'cf-geoplugin') => [
                'taxonomy'  => 'cf-geoplugin-country',
                'post_meta' => 'cfgp-banner-location',
                'field'     => 'country',
                'function'  => 'select_countries',
            ],
            __('Select Regions', 'cf-geoplugin') => [
                'taxonomy'  => 'cf-geoplugin-region',
                'post_meta' => 'cfgp-banner-location',
                'field'     => 'region',
                'function'  => 'select_regions',
            ],
            __('Select Cites', 'cf-geoplugin') => [
                'taxonomy'  => 'cf-geoplugin-city',
                'post_meta' => 'cfgp-banner-location',
                'field'     => 'city',
                'function'  => 'select_cities',
            ],
            __('Select Postcodes', 'cf-geoplugin') => [
                'taxonomy'  => 'cf-geoplugin-postcode',
                'post_meta' => 'cfgp-banner-location',
                'field'     => 'postcode',
                'function'  => 'select_postcodes',
            ],
        ];

        echo '<div class="cfgp-country-region-city-multiple-form">';

        // list taxonomies
        foreach ($taxonomy_list as $name => $option) {
            // list all terms
            $all_terms = wp_get_post_terms($post->ID, $option['taxonomy'], ['fields' => 'all']);
            $data      = [];

            foreach ($all_terms as $i => $fetch) {
                $data[] = $fetch->slug;
            }

            $get_post_meta = get_post_meta($post->ID, esc_attr($option['post_meta'].'-'.$option['field']), true);

            if (!empty($get_post_meta)) {
                $data = array_merge($data, $get_post_meta);
            }

            if ('cf-geoplugin-country' == $option['taxonomy']) {
                $country_code = $data;
            }

            if (is_array($country_code)) {
                $country_code = join(',', $country_code);
            }

            $fn = $option['function'];

            // CFGP_Form is already escaped inside a function
            printf(
                '<p class="post-attributes-label-wrapper cfgp-banner-label-wrapper-%s">%s%s</p>',
                esc_attr($option['field']),
                sprintf(
                    '<label for="%s">%s</label>',
                    esc_attr($option['taxonomy']),
                    esc_html($name)
                ),
                wp_kses(CFGP_Form::$fn([
                    'name'         => esc_attr($option['post_meta'].'-'.$option['field']),
                    'id'           => esc_attr($option['taxonomy']),
                    'country_code' => esc_attr($country_code),
                ], array_map('esc_html', $data), true, false), CFGP_U::allowed_html_tags_for_page())
            );
        }
        echo '</div>';
    }

    /**
     * Geo banner shortcode metabox
     */
    public function add_meta_box__shortcode($post)
    {
        echo '<ul>';
        echo '<li><strong>' . esc_html__('Standard', 'cf-geoplugin') . ':</strong><br><code>[cfgeo_banner id="'.esc_attr($post->ID).'"]</code></li>';
        echo '<li><strong>' . esc_html__('Advanced', 'cf-geoplugin') . ':</strong><br><code>[cfgeo_banner id="'.esc_attr($post->ID).'"]' . esc_html__('Default content', 'cf-geoplugin') . '[/cfgeo_banner]</code></li>';
        echo '<li><strong>' . esc_html__('Enable Cache', 'cf-geoplugin') . ':</strong><br><code>[cfgeo_banner id="'.esc_attr($post->ID).'" cache]' . esc_html__('Default content', 'cf-geoplugin') . '[/cfgeo_banner]</code></li>';
        echo '</ul>';
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

}
endif;
