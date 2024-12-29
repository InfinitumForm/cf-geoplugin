<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists('CFGP_Elementor_Content_Widget', false) ) :

class CFGP_Elementor_Content_Widget extends \Elementor\Widget_Base {

	public static $slug = 'cf-geoplugin-elementor-content';

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
	public function get_title() { return __('Geo Text Editor', 'cf-geoplugin'); }

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
	public function get_icon() { return 'eicon-post-content'; }

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
				'label' => __( 'Image settings', 'cf-geoplugin'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'content',
				[
					'label' => __( 'Text Editor', 'cf-geoplugin'),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Set the content that appears for the defined location.', 'cf-geoplugin'),
				]
			);
			
			$repeater->add_control(
				'location',
				[
					'label' => __( 'Display Location', 'cf-geoplugin'),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Comma separated city, region, country, continent name or the code where you want to display this image.', 'cf-geoplugin'),
					'placeholder' => __( 'US, Toronto, Europe...', 'cf-geoplugin'),
				]
			);
			
		$this->add_control(
			'list',
			[
				'label' => __( 'Add content for each geolocation', 'cf-geoplugin'),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ location }}}',
			]
		);
		
		$this->add_control(
			'default_options',
			[
				'label' => __( 'Default Options', 'cf-geoplugin'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		
		$this->add_control(
			'enable_default_content',
			[
				'label' => __( 'Enable Default Content', 'cf-geoplugin'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'cf-geoplugin'),
				'label_off' => __( 'No', 'cf-geoplugin'),
				'return_value' => 'yes',
				'default' => '',
			]
		);
		
		$this->add_control(
			'default_content',
			[
				'label' => __( 'Text Editor', 'cf-geoplugin'),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => __( 'Set the content that appears for the defined location.', 'cf-geoplugin'),
			]
		);
		
		$this->end_controls_section();
		
		$slug = self::$slug;
		
		$class = '{{WRAPPER}}';
		
		
		/*
		 * STYLE
		 */
		$class = '{{WRAPPER}} ' . ".{$slug}";
		$this->start_controls_section(
			'style_section_0',
			array(
				'label' => __( 'Content style', 'cf-geoplugin'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'typography',
					'label' => __( 'Typography', 'cf-geoplugin'),
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
					],
					'selector' => "{$class}, {$class} p"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'typography_link',
					'label' => __( 'Link typography', 'cf-geoplugin'),
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
					],
					'selector' => "{$class} a, {$class} a:active"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'typography_link_hover',
					'label' => __( 'Link hover typography', 'cf-geoplugin'),
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
					],
					'selector' => "{$class} a:hover, {$class} a:focus"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				array(
					'name' => 'text_shadow',
					'label' => __( 'Text Shadow', 'cf-geoplugin'),
					'selector' => "{$class}, {$class} p"
				)
			);
		
		$this->end_controls_section();
		
		$this->start_controls_section(
			'style_section_1',
			array(
				'label' => __( 'Headers', 'cf-geoplugin'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		
		for($h=1; $h<6; $h++)
		{
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'typography_header_' . $h,
					'label' => 'H' . $h,
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
					],
					'selector' => "{$class} h{$h}"
				)
			);
		}
		
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
		
		$content = NULL;
		
		if($settings['enable_default_content'] == 'yes')
		{	
			$content = $settings['default_content'];
		}
		
		foreach($settings['list'] as $i=>$fetch)
		{
			if(CFGP_U::recursive_array_search($fetch['location'], CFGP_U::api(false, CFGP_Defaults::API_RETURN))){		
				$content = $fetch['content'];
				break;
			}
		}
		
		if(!$content) return;
		
		printf('<div class="%1$s">%2$s</div>', esc_attr(self::$slug), wp_kses_post($content ?? ''));
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