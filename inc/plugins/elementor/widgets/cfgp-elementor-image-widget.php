<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists('CFGP_Elementor_Image_Widget') ) :

class CFGP_Elementor_Image_Widget extends \Elementor\Widget_Base {

	public static $slug = 'cfgp-elementor-image';

	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() { return self::$slug; }

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() { return __('Geo Image', CFGP_NAME); }

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() { return 'eicon-image-hotspot'; }

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() { return [ 'cf-geoplugin' ]; }
	
	/**
	 * Register oEmbed widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();
		$image_sizes = $image_sizes_prep = [];
	
		foreach ( $default_image_sizes as $size ) {
			$image_sizes_prep[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
			$image_sizes_prep[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes_prep[ $size ][ 'crop' ] = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}
	
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes_prep = array_merge( $image_sizes_prep, $_wp_additional_image_sizes );
		}
		
		foreach($image_sizes_prep as $key=>$prep){
			$image_sizes[$key]= $key;
		}

		$slug = self::$slug;
		
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Image settings', CFGP_NAME ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'image',
				[
					'label' => __( 'Choose Image', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);
		
			$repeater->add_control(
				'image_size',
				[
					'label' => __( 'Image Size', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'large',
					'options' => $image_sizes,
				]
			);
			
			$repeater->add_control(
				'location',
				[
					'label' => __( 'Display Location', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Comma separated city, region, country, continent name or the code where you want to display this image.', CFGP_NAME ),
					'placeholder' => __( 'US, Toronto, Europe...', CFGP_NAME ),
				]
			);
			
			$repeater->add_control(
				'alt',
				[
					'label' => __( 'Alt', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Alternate image title.', CFGP_NAME ),
				]
			);
			
			$repeater->add_control(
				'link',
				[
					'label' => __( 'Link', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::URL,
					'placeholder' => __( 'https://your-link.com', CFGP_NAME ),
					'show_external' => true,
					'default' => [
						'url' => '',
					],
				]
			);
			
		$this->add_control(
			'list',
			[
				'label' => __( 'Coose image for each geolocation', CFGP_NAME ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ location }}}',
			]
		);
		
		$this->add_control(
			'default_options',
			[
				'label' => __( 'Default Options', CFGP_NAME ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		
			$this->add_control(
				'enable_default_image',
				[
					'label' => __( 'Enable Default Image', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', CFGP_NAME ),
					'label_off' => __( 'No', CFGP_NAME ),
					'return_value' => 'yes',
					'default' => '',
				]
			);
			
			$this->add_control(
				'default_image',
				[
					'label' => __( 'Default Image', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);
		
			$this->add_control(
				'default_image_size',
				[
					'label' => __( 'Default Image Size', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'large',
					'options' => $image_sizes,
				]
			);
			
			$this->add_control(
				'default_alt',
				[
					'label' => __( 'Default Alt', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Default alternate image title.', CFGP_NAME ),
				]
			);
			
			$this->add_control(
				'default_link',
				[
					'label' => __( 'Default Link', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::URL,
					'placeholder' => __( 'https://your-link.com', CFGP_NAME ),
					'show_external' => true,
					'default' => [
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
				'label' => __( 'Image Settings', CFGP_NAME ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
			
			$this->add_responsive_control(
				'image_align',
				[
					'label' => __( 'Image Alignment', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'options' => [
						'0 auto 0 0' => [
							'title' => __( 'Left', CFGP_NAME ),
							'icon' => 'eicon-h-align-left',
						],
						'0 auto' => [
							'title' => __( 'Center', CFGP_NAME ),
							'icon' => 'eicon-h-align-center',
						],
						'0 0 0 auto' => [
							'title' => __( 'Right', CFGP_NAME ),
							'icon' => 'eicon-h-align-right',
						],
					],
					'default' => '0 auto',
					'toggle' => true,
					'selectors' => [
						"{$class}" => 'margin: {{VALUE}} !important',
					],
				]
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'image_border',
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'label' => __( 'Image Border', CFGP_NAME ),
					'selector' => "{$class}"
				]
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'image_shadow',
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'label' => __( 'Image Shadow', CFGP_NAME ),
					'selector' => "{$class}"
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
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$image = [
			'ID' => NULL,
			'url' => NULL,
			'size' => NULL,
			'alt' => NULL,
			'link' => NULL
		];

		if($settings['enable_default_image'] == 'yes')
		{
			if(empty($settings['default_image']['id'])){
				echo '<strong>-- ' . __('You must define a default image for this option to work.', CFGP_NAME) . ' --</strong>';
				return;
			}
			
			$image = [
				'ID' => $settings['default_image']['id'],
				'url' => $settings['default_image']['url'],
				'size' => $settings['default_image_size'],
				'alt' => $settings['default_alt'],
				'link' => $settings['default_link']
			];
		}
		else
		{
			if(empty($settings['list'])){
				echo '<strong>' . __('Please define one or more images.', CFGP_NAME) . '</strong>';
				return;
			}
		}
		
		foreach($settings['list'] as $i=>$fetch)
		{
			if(CFGP_U::recursive_array_search($fetch['location'], CFGP_U::api())){		
				$image = [
					'ID' => $fetch['image']['id'],
					'url' => $fetch['image']['url'],
					'size' => $fetch['image_size'],
					'alt' => $fetch['alt'],
					'link' => $fetch['link']
				];
				break;
			}
		}
		
		$target = $image['link']['is_external'] ? ' target="_blank"' : '';
		$nofollow = $image['link']['nofollow'] ? ' rel="nofollow"' : '';
		
//		echo '<pre>', var_dump($image), '</pre>';

		if(!empty($image['link']['url'])){
			echo '<a href="' . $image['link']['url'] . '"' . $target . $nofollow . '>';
		}

		echo wp_get_attachment_image(
			$image['ID'],
			$image['size'],
			false,
			[
				'class' => self::$slug,
				'alt'	=> $image['alt']
			]
		);
		
		if(!empty($image['link']['url'])){
			echo '</a>';
		}
	}
	
	/**
	 * Check if is in edit mode
	 *
	 * Return true/false
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function is_edit()
	{
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}
} 

endif;