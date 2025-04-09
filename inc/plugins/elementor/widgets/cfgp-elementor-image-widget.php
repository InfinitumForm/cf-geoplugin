<?php

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!class_exists('CFGP_Elementor_Image_Widget', false)) :

    class CFGP_Elementor_Image_Widget extends \Elementor\Widget_Base
    {
        public static $slug = 'cfgp-elementor-image';

        /**
         * Get widget name.
         *
         * Retrieve oEmbed widget name.
         *
         * @since 1.0.0
         *
         * @access public
         *
         * @return string Widget name.
         */
        public function get_name()
        {
            return self::$slug;
        }

        /**
         * Get widget title.
         *
         * Retrieve oEmbed widget title.
         *
         * @since 1.0.0
         *
         * @access public
         *
         * @return string Widget title.
         */
        public function get_title()
        {
            return __('Geo Image', 'cf-geoplugin');
        }

        /**
         * Get widget icon.
         *
         * Retrieve oEmbed widget icon.
         *
         * @since 1.0.0
         *
         * @access public
         *
         * @return string Widget icon.
         */
        public function get_icon()
        {
            return 'eicon-image-hotspot';
        }

        /**
         * Get widget categories.
         *
         * Retrieve the list of categories the oEmbed widget belongs to.
         *
         * @since 1.0.0
         *
         * @access public
         *
         * @return array Widget categories.
         */
        public function get_categories()
        {
            return [ 'cf-geoplugin' ];
        }

        /**
         * Register oEmbed widget controls.
         *
         * Adds different input fields to allow the user to change and customize the widget settings.
         *
         * @since 1.0.0
         *
         * @access protected
         */
        protected function _register_controls()
        {
            global $_wp_additional_image_sizes;

            $default_image_sizes = get_intermediate_image_sizes();
            $image_sizes         = $image_sizes_prep = [];

            foreach ($default_image_sizes as $size) {
                $image_sizes_prep[ $size ][ 'width' ]  = intval(get_option("{$size}_size_w"));
                $image_sizes_prep[ $size ][ 'height' ] = intval(get_option("{$size}_size_h"));
                $image_sizes_prep[ $size ][ 'crop' ]   = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
            }

            if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
                $image_sizes_prep = array_merge($image_sizes_prep, $_wp_additional_image_sizes);
            }

            foreach ($image_sizes_prep as $key => $prep) {
                $image_sizes[$key] = $key;
            }

            $slug = self::$slug;

            $this->start_controls_section(
                'content_section',
                [
                    'label' => __('Image settings', 'cf-geoplugin'),
                    'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $repeater = new \Elementor\Repeater();

            $repeater->add_control(
                'image',
                [
                    'label'   => __('Choose Image', 'cf-geoplugin'),
                    'type'    => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );

            $repeater->add_control(
                'image_size',
                [
                    'label'   => __('Image Size', 'cf-geoplugin'),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => 'large',
                    'options' => $image_sizes,
                ]
            );

            $repeater->add_control(
                'location',
                [
                    'label'       => __('Display Location', 'cf-geoplugin'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'description' => __('Comma separated city, region, country, continent name or the code where you want to display this image.', 'cf-geoplugin'),
                    'placeholder' => __('US, Toronto, Europe...', 'cf-geoplugin'),
                ]
            );

            $repeater->add_control(
                'alt',
                [
                    'label'       => __('Alt', 'cf-geoplugin'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'description' => __('Alternate image title.', 'cf-geoplugin'),
                ]
            );

            $repeater->add_control(
                'link',
                [
                    'label'         => __('Link', 'cf-geoplugin'),
                    'type'          => \Elementor\Controls_Manager::URL,
                    'placeholder'   => __('https://your-link.com', 'cf-geoplugin'),
                    'show_external' => true,
                    'default'       => [
                        'url' => '',
                    ],
                ]
            );

            $this->add_control(
                'list',
                [
                    'label'       => __('Coose image for each geolocation', 'cf-geoplugin'),
                    'type'        => \Elementor\Controls_Manager::REPEATER,
                    'fields'      => $repeater->get_controls(),
                    'default'     => [],
                    'title_field' => '{{{ location }}}',
                ]
            );

            $this->add_control(
                'default_options',
                [
                    'label'     => __('Default Options', 'cf-geoplugin'),
                    'type'      => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'enable_default_image',
                [
                    'label'        => __('Enable Default Image', 'cf-geoplugin'),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => __('Yes', 'cf-geoplugin'),
                    'label_off'    => __('No', 'cf-geoplugin'),
                    'return_value' => 'yes',
                    'default'      => '',
                ]
            );

            $this->add_control(
                'default_image',
                [
                    'label'   => __('Default Image', 'cf-geoplugin'),
                    'type'    => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );

            $this->add_control(
                'default_image_size',
                [
                    'label'   => __('Default Image Size', 'cf-geoplugin'),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => 'large',
                    'options' => $image_sizes,
                ]
            );

            $this->add_control(
                'default_alt',
                [
                    'label'       => __('Default Alt', 'cf-geoplugin'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'description' => __('Default alternate image title.', 'cf-geoplugin'),
                ]
            );

            $this->add_control(
                'default_link',
                [
                    'label'         => __('Default Link', 'cf-geoplugin'),
                    'type'          => \Elementor\Controls_Manager::URL,
                    'placeholder'   => __('https://your-link.com', 'cf-geoplugin'),
                    'show_external' => true,
                    'default'       => [
                        'url' => '',
                    ],
                ]
            );

            $this->end_controls_section();

            $slug = self::$slug;

            $class = '{{WRAPPER}} ' . ".{$slug}";

            $this->start_controls_section(
                'style_section_0',
                [
                    'label' => __('Image Settings', 'cf-geoplugin'),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_responsive_control(
                'image_align',
                [
                    'label'   => __('Image Alignment', 'cf-geoplugin'),
                    'type'    => \Elementor\Controls_Manager::CHOOSE,
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'options' => [
                        '0 auto 0 0' => [
                            'title' => __('Left', 'cf-geoplugin'),
                            'icon'  => 'eicon-h-align-left',
                        ],
                        '0 auto' => [
                            'title' => __('Center', 'cf-geoplugin'),
                            'icon'  => 'eicon-h-align-center',
                        ],
                        '0 0 0 auto' => [
                            'title' => __('Right', 'cf-geoplugin'),
                            'icon'  => 'eicon-h-align-right',
                        ],
                    ],
                    'default'   => '0 auto',
                    'toggle'    => true,
                    'selectors' => [
                        "{$class}" => 'margin: {{VALUE}} !important',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'     => 'image_border',
                    'devices'  => [ 'desktop', 'tablet', 'mobile' ],
                    'label'    => __('Image Border', 'cf-geoplugin'),
                    'selector' => "{$class}",
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name'     => 'image_shadow',
                    'devices'  => [ 'desktop', 'tablet', 'mobile' ],
                    'label'    => __('Image Shadow', 'cf-geoplugin'),
                    'selector' => "{$class}",
                ]
            );

            $this->end_controls_section();
        }

        /**
         * Render oEmbed widget output on the frontend.
         *
         * Written in PHP and used to generate the final HTML.
         *
         * @since 1.0.0
         *
         * @access protected
         */
        protected function render()
        {
            $settings = $this->get_settings_for_display();

            $image = [
                'ID'   => null,
                'url'  => null,
                'size' => null,
                'alt'  => null,
                'link' => null,
            ];

            if ($settings['enable_default_image'] == 'yes') {
                if (empty($settings['default_image']['id'])) {
                    echo '<strong>-- ' . esc_html__('You must define a default image for this option to work.', 'cf-geoplugin') . ' --</strong>';

                    return;
                }

                $image = [
                    'ID'   => $settings['default_image']['id'],
                    'url'  => $settings['default_image']['url'],
                    'size' => $settings['default_image_size'],
                    'alt'  => $settings['default_alt'],
                    'link' => $settings['default_link'],
                ];
            } else {
                if (empty($settings['list'])) {
                    echo '<strong>' . esc_html__('Please define one or more images.', 'cf-geoplugin') . '</strong>';

                    return;
                }
            }

            foreach ($settings['list'] as $i => $fetch) {
                if (CFGP_U::recursive_array_search($fetch['location'], CFGP_U::api(false, CFGP_Defaults::API_RETURN))) {
                    $image = [
                        'ID'   => esc_attr($fetch['image']['id']),
                        'url'  => esc_attr($fetch['image']['url']),
                        'size' => esc_attr($fetch['image_size']),
                        'alt'  => esc_attr($fetch['alt']),
                        'link' => esc_attr($fetch['link']),
                    ];
                    break;
                }
            }

            $target   = $image['link']['is_external'] ? ' target="_blank"' : '';
            $nofollow = $image['link']['nofollow'] ? ' rel="nofollow"' : '';

            if (!empty($image['link']['url'])) {
                echo '<a href="' . esc_url($image['link']['url']) . '"' .esc_html($target . $nofollow) . '>';
            }

            echo wp_get_attachment_image(
                $image['ID'],
                $image['size'],
                false,
                [
                    'class' => esc_attr(self::$slug),
                    'alt'   => esc_attr($image['alt']),
                ]
            );

            if (!empty($image['link']['url'])) {
                echo '</a>';
            }
        }

        /**
         * Check if is in edit mode
         *
         * Return true/false
         *
         * @since 1.0.0
         *
         * @access private
         */
        private function is_edit()
        {
            return \Elementor\Plugin::$instance->editor->is_edit_mode();
        }
    }

endif;
