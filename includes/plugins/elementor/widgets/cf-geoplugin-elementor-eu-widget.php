<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

use Elementor\Repeater;
use Elementor\Widget_Base;

if( !class_exists('CF_Geoplugin_Elementor_Eu_Widget') ) :

class CF_Geoplugin_Elementor_Eu_Widget extends Widget_Base {

	public static $slug = 'elementor-show-in-eu';

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
	public function get_title() { return __('European Union control', CFGP_NAME); }

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
	public function get_icon() { return 'fa fa-globe'; }

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

		$slug = self::$slug;


		/*
		 * CONTENT
		 */
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Options', CFGP_NAME ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
			$this->add_control(
				'eu_control',
				array(
					'label'	=> __( 'Show or hide', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', CFGP_NAME ),
					'label_off' => __( 'Hide', CFGP_NAME ),
					'return_value' => true,
					'default' => true,
					'description' => __( 'You can chose, do you want to show or hide this widget for the European Union visitors.', CFGP_NAME )
				)
			);
			
			$this->add_control(
				'content',
				array(
					'label' => __( 'Content', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => __( 'Your content goes here...', CFGP_NAME ),
					'placeholder' => __( 'Place your content.', CFGP_NAME ),
				)
			);
			
			$this->add_control(
				'preview',
				array(
					'label'	=> __( 'Preview mode', CFGP_NAME ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Preview', CFGP_NAME ),
					'label_off' => __( 'Normal', CFGP_NAME ),
					'return_value' => true,
					'default' => true,
					'description' => __( 'This is an administrator-only option. Leave it enabled so you can see the content you are editing.', CFGP_NAME )
				)
			);
		$this->end_controls_section();
		
		
		/*
		 * STYLE
		 */
		$class = '{{WRAPPER}} ' . ".cf-geoplugin-{$slug}";
		$this->start_controls_section(
			'style_section_0',
			array(
				'label' => __( 'Content style', CFGP_NAME ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'content_typography',
					'label' => __( 'Typography', CFGP_NAME ),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_3,
					'selector' => "{$class}, {$class} p"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'content_typography_link',
					'label' => __( 'Link typography', CFGP_NAME ),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_3,
					'selector' => "{$class} a, {$class} a:active"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'content_typography_link_hover',
					'label' => __( 'Link hover typography', CFGP_NAME ),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_3,
					'selector' => "{$class} a:hover, {$class} a:focus"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				array(
					'name' => 'text_shadow',
					'label' => __( 'Text Shadow', CFGP_NAME ),
					'selector' => "{$class}, {$class} p"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name' => 'p_border',
					'label' => __( 'Border', CFGP_NAME ),
					'selector' => "{$class}, {$class} p"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'p_background',
					'label' => __( 'Background', CFGP_NAME ),
					'types' => [ 'classic', 'gradient', 'video' ],
					'selector' => "{$class}, {$class} p"
				]
			);
		
		$this->end_controls_section();

		for($i = 1; $i<=6; $i++)
		{
			$this->start_controls_section(
				"style_section_{$i}",
				array(
					'label' => __( "Heading H{$i}", CFGP_NAME ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);
			
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name' => "heading_typography_{$i}",
						'label' => __( 'Typography', CFGP_NAME ),
						'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
						'selector' => "{$class} h{$i}"
					)
				);
				
				$this->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name' => "heading_text_shadow_{$i}",
						'label' => __( 'Text Shadow', CFGP_NAME ),
						'selector' => "{$class} h{$i}"
					)
				);
				
				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					array(
						'name' => "heading_border_{$i}",
						'label' => __( 'Border', CFGP_NAME ),
						'selector' => "{$class} h{$i}"
					)
				);
				
				$this->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					array(
						'name' => "heading_background_{$i}",
						'label' => __( 'Background', CFGP_NAME ),
						'types' => [ 'classic', 'gradient', 'video' ],
						'selector' => "{$class} h{$i}"
					)
				);
			
			$this->end_controls_section();
		}

		$this->start_controls_section(
			'style_section_blockquote',
			array(
				'label' => __( 'Blockquote', CFGP_NAME ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name' => 'content_typography_blockquote',
					'label' => __( 'Typography', CFGP_NAME ),
					'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_3,
					'selector' => "{$class} blockquote"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				array(
					'name' => 'text_shadow_blockquote',
					'label' => __( 'Text Shadow', CFGP_NAME ),
					'selector' => "{$class} blockquote"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name' => 'blockquote_border',
					'label' => __( 'Border', CFGP_NAME ),
					'selector' => "{$class} blockquote"
				)
			);
			
			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'blockquote_background',
					'label' => __( 'Background', CFGP_NAME ),
					'types' => [ 'classic', 'gradient', 'video' ],
					'selector' => "{$class} blockquote"
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
		global $post;
		$settings = $this->get_settings_for_display();
		
		$CFGEO = $GLOBALS['CFGEO'];
		
		if(empty($CFGEO)) return;
		
		$show = false;
		
		if($settings['eu_control'] && isset($CFGEO['in_eu']) && $CFGEO['in_eu'] == 1)
			$show = true;
		
		if(!$settings['eu_control'] && isset($CFGEO['in_eu']) && $CFGEO['in_eu'] == 0)
			$show = true;

		if(self::is_edit() && $settings['preview'])
			$show = true;
		
		if( $show && !empty($settings['content'])) : ?>
			<div class="elementor-text-editor elementor-clearfix elementor-inline-editing <?php echo self::$slug; ?> cf-geoplugin-<?php echo self::$slug; ?>">
				<?php echo do_shortcode($settings['content']); ?>
			</div>
		<?php endif;
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