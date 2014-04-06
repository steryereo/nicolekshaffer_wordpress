<?php

/**
 * The GOG_Widget class.
 *
 * @category Good Old Gallery Wordpress Plugin
 * @package  Good Old Gallery
 * @author   Linus Lundahl
 *
 */
class GOG_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
			parent::__construct(
			'GoodOldGalleryWidget',
			__( GOG_PLUGIN_NAME, 'goodoldgallery' ),
			array(
				'classname' => 'good-old-gallery-widget',
				'description' => __( 'Widget that displays a selected gallery.', 'goodoldgallery' ),
			)
		);
	}

	/**
	 * Widget output.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		$show = FALSE;
		if ( isset( $instance['gallery-pages'] ) ) {
			$paths = goodold_gallery_paths( $instance['gallery-pages'] );
			if ( in_array( $_SERVER['REQUEST_URI'], $paths ) ) {
				$show = TRUE;
			}
		}
		else {
			$show = TRUE;
		}

		// Build settings for shortcode
		if ( $show ) {
			$settings = '';
			foreach ( $instance as $key => $value ) {
				$key = strtolower( $key );
				if ( $key != 'title' ) {
					if ( $value ) {
						$settings .= $key . '="' . $value . '" ';
					}
					else if ( $value === NULL ) {
						$settings .= $key . '="false" ';
					}
					else if ( $value === '' ) {
						// Print nothing
					}
					else {
						$settings .= $key . '="false" ';
					}

					$settings = str_replace( '"on"', '"true"', $settings );
				}
			}

			echo $before_widget;
			echo do_shortcode( '[good-old-gallery ' . rtrim( $settings ) . ']' );
			echo $after_widget;
		}
	}

	/**
	 * Save settings form.
	 */
	public function update( $new_instance, $old_instance ) {
		global $gog;

		if ( isset( $new_instance['theme'] ) ) {
			$instance['theme'] = $new_instance['theme'];
		}
		else if ( !empty( $gog->settings->themes['default'] ) ) {
			$instance['theme'] = $gog->settings->themes['theme']['class'];
		}

		$instance['title'] = $new_instance['title'];
		$instance['id']    = $new_instance['id'];
		$instance['size']  = $new_instance['size'];

		foreach ( $gog->settings->plugin['settings_form'] as $section ) {
			foreach ( $section['fields'] as $setting => $item ) {
				if ( !isset( $item['ignore'] ) ) {
					$setting = $setting == 'title' ? 'g' . $setting : $setting;
					$instance[$setting] = $new_instance[$setting];
				}
			}
		}

		return $instance;
	}

	/**
	 * Settings form.
	 */
	public function form( $instance ) {
		global $gog;

		// Build dropdown with galleries
		$posts = GOG_Helpers::getGalleries( 'publish' );

		if ( !$posts ) {
			echo '<p>' . __( 'No galleries found.', 'goodoldgallery' ) . '</p>';
		}
		else {
			$gallery_options = array();
			foreach ( $posts as $p ) {
				$gallery_options[$p->ID] = $p->post_title;
			}

			$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );

			// Prepare widget form
			$widget_form = array(
				'basic_settings' => array(
					'title'    => 'Basic Settings',
					'fields'   => array(
						'title' => array(
							'title' => 'Title of the widget',
							'type'  => 'text',
							'args'  => array(
								'desc' => 'The title is only used in the administration.',
								'size' => 28,
							),
						),
						'id' => array(
							'title' => 'Select gallery',
							'type'  => 'select',
							'args'  => array(
								'items' => $gallery_options,
							),
						),
						'size' => array(
							'title' => 'Size',
							'type'  => 'select',
							'args'  => array(
								'items' => array(
									'thumbnail' => 'Thumbnail',
									'medium' => 'Medium',
									'large' => 'Large',
									'full' => 'Full',
								),
								'desc' => 'Image size used for the galleries.',
							),
						),
					),
				),
			);

			// Add theme options if all themes are activated
			if ( $gog->settings->themes['themes'] !== 'false' || $gog->settings->themes['themes'] === '' ) {
				$themes = $gog->settings->GetThemes();
				$theme_options = array();
				foreach ( $themes as $theme ) {
					$theme_options[$theme['Class']] = $theme['Name'];
				}

				if ( $theme_options ) {
					$widget_form['basic_settings']['fields'] += array(
						'theme' => array(
							'title' => 'Select theme',
							'type'  => 'select',
							'args'  => array(
								'items' => $theme_options,
							),
						),
					);
				}
			}

			// Add plugin id and name to settings form for plugin
			$widget_form += $gog->settings->plugin['settings_form'];
			foreach ( $widget_form as $section => $form ) {
				foreach ( $widget_form[$section]['fields'] as $key => $item) {
					$widget_form[$section]['fields'][$key]['widget_id'] = $this->get_field_id( $key );
					$widget_form[$section]['fields'][$key]['widget_name'] = $this->get_field_name( $key );
				}
			}

			// Build the form
			GOG_FormBuilder::parseFormCustom( $widget_form, $instance );
		}
	}
}
