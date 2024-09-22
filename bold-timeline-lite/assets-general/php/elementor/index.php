<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Add an elementbold_timeline to elementor page builder.
 *
 */
if ( ! class_exists( 'Bold_Timeline_Elementor' ) ) {
	class Bold_Timeline_Elementor extends \Elementor\Widget_Base { 
	  
			/**
			 * Get widget name.	
			 * Retrieve Bold Timeline widget name.
			 * @return string Widget name.
			 */
			public function get_name() {
				return 'bold-timeline';
			}

			/**
			 * Get widget title.
			 * Retrieve Bold Timeline widget title.
			 * @return string Widget title.
			 */
			public function get_title() {				
				return BOLD_TIMELINE_ELEMENT_NAME;
			}

			/**
			 * Get widget icon.
			 * Retrieve Bold Timeline widget icon.
			 * @return string Widget icon.
			 */
			public function get_icon() {
				return 'eicon-toggle';
			}

			/**
			 * Get custom help URL.
			 * Retrieve a URL where the user can get more information about the widget.
			 * @return string Widget help URL.
			 */
			public function get_custom_help_url() {
				return 'bold-themes.com/documentation.bold-themes.com/bold-timeline';
			}

			/**
			 * Get widget categories.
			 * Retrieve the list of categories the Bold Timeline widget belongs to.
			 * @return array Widget categories.
			 */
			public function get_categories() {
				return [ 'bold-themes' ];
			}

			/**
			 * Get widget keywords.
			 * Retrieve the list of keywords the Bold Timeline widget belongs to.
			 * @return array Widget keywords.
			 */
			public function get_keywords() {
				return [ 'timeline', 'bold', 'themes' ];
			}

			/**
			 * Register Bold Timeline widget controls.
			 *
			 */
			protected function register_controls() {
					$this->start_controls_section(
						'content_section',
						[
							'label' => esc_html__( 'Content', 'bold-timeline' ),
							'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
						]
					);
					$this->add_control(
						'bold_timeline',
						array(
							'label' => BOLD_TIMELINE_FIELD_TITLE,
							'type' => \Elementor\Controls_Manager::SELECT,
							'default' => '',
							'options' => bold_timeline_get_bold_timelines(),
							'style_transfer' => true,
						)
					);
					$this->end_controls_section();
			}

			/**
			 * Render Bold Timeline widget output on the frontend.
			 *
			 */
			protected function render() {
				

				$settings = $this->get_settings_for_display();	
				$bold_timeline	= $settings['bold_timeline'];

				if ( intval($bold_timeline) > 0 ) {
				?>
					<div class="<?php echo esc_attr( BOLD_TIMELINE_ELEMENT_CLASS );?> elementor-shortcode">
						<?php echo do_shortcode( '[bold_timeline id="' . $bold_timeline . '"]' );?>
					</div>
				<?php
				
				wp_enqueue_script( 
					'swiper',
					plugin_dir_url( __FILE__ ) . '../js/swiper-bundle.min.js',
					array( 'jquery', 'elementor-frontend' )
				);
				wp_enqueue_script( 
					'hero-posts-swiper-helper',
					plugin_dir_url( __FILE__ ) . '../js/bold-timeline-swiper-helper.js',
					array( 'jquery', 'swiper', 'elementor-frontend' )
				);
				wp_enqueue_style( 
					'hero-posts-swiper', 
					plugin_dir_url( __FILE__ ) . '../css/swiper-bundle.min.css'
				);
				}
			}

			protected function content_template() {
				
			}
	}
}