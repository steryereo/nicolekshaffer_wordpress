<?php

class GOG_Shortcode {
	protected $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		// Register shortcode
		add_shortcode( 'good-old-gallery', array( $this, 'shortcode' ) );
	}

	/**
	 * Shortcode that handles output for galleries.
	 */
	public function shortcode( $attr ) {
		global $post;

		$s = $this->settings;

		static $i = 1;

		// Build Slider default settings
		$slider = array();
		foreach ( $s->plugin['settings'] as $key => $value ) {
			$new_key = strtolower( $key );
			$slider[$new_key] = $key;
		}

		$settings = array();
		foreach ( $slider as $key => $large ) {
			if ( isset($s->settings[$large] ) ) {
				$settings[$key] = array(
					'key' => $large,
					'val' => $s->settings[$large],
				);
			}
		}

		// Get settings from shortcode
		$attr = shortcode_atts( array(
			'id'                => null,
			'order'             => 'ASC',
			'orderby'           => 'menu_order ID',
			'exclude'           => array(),
			'theme'             => !empty( $s->themes['theme']['class'] ) ? $s->themes['theme']['class'] : '',
			'size'              => $s->settings['size'],
			'set_width'         => $s->settings['set_width'],
			'set_height'        => $s->settings['set_height'],
			'title'             => $s->settings['title'],
			'description'       => $s->settings['description'],
		) + $settings, $attr );

		// Setup Slider settings array
		$settings = array_slice( $attr, 10 );
		foreach ( $settings as $key => $setting ) {
			if ( isset( $slider[$key] ) ) {
				$settings[$key] = array(
					'key' => $slider[$key],
					'val' => is_array( $setting ) ? $setting['val'] : $setting,
				);
			}
		}

		// Extract GOG settings to vars
		extract( array_slice( $attr, 0, 10 ) );

		// Use post_id if no id is set in shortcode.
		$id = ( !$id && $post->ID ) ? $post->ID : $id;

		// Kill the function if an id is still not found.
		if ( !$id ) {
			return;
		}

		// Add settings from plugin shortcode extras function
		$sc_extra_settings = array(
			'id' => $id,
			'i' => $i,
			'settings' => $settings,
		);

		$sc_extras = $s->LoadPlugin( array( 'function' => 'shortcode_extras', 'settings' => $sc_extra_settings ) );
		if ( is_array( $sc_extras ) && !empty( $sc_extras ) ) {
			extract( $sc_extras );
			$settings += $settings_extras;
		}

		$ret = '';
		if ( isset( $settings['animation'] ) && ( $settings['animation']['val'] == 'none' || empty( $s->settings['plugin'] ) ) ) {
			$ret .= do_shortcode( '[gallery id="' . $id . '"]' );
		}
		else {
			$attachments = get_children( array(
				'post_parent'     => $id,
				'post_status'     => 'inherit',
				'post_type'       => 'attachment',
				'post_mime_type'  => 'image',
				'order'           => $order,
				'orderby'         => $orderby,
				'exclude'         => $exclude
			) );

			// Add classes.
			$classes = ' go-gallery-' . $id;
			$classes .= $theme                                  ? " $theme"                           : '';
			$classes .= $navigation                             ? " has-nav"                          : '';
			$classes .= $pager                                  ? " has-pager"                        : '';
			$classes .= !empty( $settings['animation']['val'] ) ? " " . $settings['animation']['val'] : '';
			$classes .= $s->settings['plugin']                  ? " " . $s->settings['plugin']        : '';

			if ( $attachments ) {

				// Generate images
				$width = $height = 0;
				$images = '';
				foreach ( $attachments as $gallery_id => $attachment ) {
					$link = get_post_meta( $attachment->ID, "_goodold_gallery_image_link", true );

					// Start list item
					$images .= '<li>' . "\n";

						// Sort fields in set order
						$order = GOG_Helpers::orderFields( $s->settings );
						$order = array_flip( $order );
						foreach ( $order as $field => $key ) {
							$order[$field] = '';
						}

						// Add title
						if ( $title != 'false' && $attachment->post_title ) {
							$order['title'] = '<div class="title">' . $attachment->post_title . '</div>' . "\n";
						}

						// Add description
						if ( $description != 'false' && $attachment->post_content ) {
							$order['desc'] = '<div class="description">' . $attachment->post_content . '</div>' . "\n";
						}

					$order['image'] .= '<div class="image">';

					// Start link
					if ( $link ) {
						$order['image'] .= '<a href="' . $link . '">' . "\n";
					}

					// Add image
					$order['image'] .= wp_get_attachment_image( $gallery_id, $size, false ) . "\n";

					// End link
					if ( $link ) {
						$order['image'] .= '</a>' . "\n";
					}

					$order['image'] .= '</div>';

					foreach ( $order as $field ) {
						$images .= !is_numeric( $field ) ? $field : '';
					}

					// End list item
					$images .= '</li>' . "\n";

					// Check for extra style attr (fixed width/height).
					$gallery_style = '';
					if ( $set_width || $set_height ) {
						$img_data = wp_get_attachment_image_src( $gallery_id, $size, false );
						// Get width
						if ( $set_width ) {
							$width = GOG_Helpers::getImgSize( $set_width, $img_data[1], $width );
							$gallery_style .= 'width: ' . $width . 'px;';
						}
						// Get height
						if ( $set_height ) {
							$height = GOG_Helpers::getImgSize( $set_height, $img_data[2], $height );
							$gallery_style .= ' height: ' . $height . 'px;';
						}
					}

				}

				$gallery_style = !empty( $gallery_style ) ? ' style="' . $gallery_style . '"' : '';

				// Begin gallery div and ul
				$ret .= '<div id="go-gallery-' . $id . '-' . $i . '" class="go-gallery-container' . $classes . '"' . $gallery_style . '>' . "\n";
				$ret .= '<div class="go-gallery-inner">' . "\n";
				$ret .= '<ul class="slides">' . "\n";

				// Insert images
				$ret .= $images;

				// End gallery ul
				$ret .= '</ul>' . "\n";

				// Add pager and navigation markup if set
				$ret .= is_string( $pager ) ? $pager : '';
				$ret .= is_string( $navigation ) ? $navigation : '';

				$ret .= '</div>' . "\n";

				// Build javascript
				$script = '';
				foreach ( $settings as $key => $setting ) {
					if ( !empty( $setting ) ) {
						if ( !empty( $setting['key'] ) && !empty( $setting['val'] ) ) {
							$script .= $setting['key'];
							if ( $setting['val'] == 'true' ) {
								$script .= ': true, ';
							}
							else if ( $setting['val'] == 'false' ) {
								$script .= ': false, ';
							}
							else {
								$script .= ': "' . $setting['val'] . '", ';
							}
						}
					}
				}

				// Add script extras if set
				$script .= $script_extras;

				// Finish script
				$ret .= '<script type="text/javascript" charset="utf-8">jQuery(function($) { $(function () { $("#go-gallery-' . $id . '-' . $i . ' ' . $s->plugin['setup']['class'] . '").' . $s->settings['plugin'] . '({' . rtrim( $script, ', ' ) . '}); }); });</script>' . "\n";

				// End gallery div
				$ret .= '</div>' . "\n";
			}
		}

		$i++;

		return $ret;
	}

	/**
	 * Helper function that builds a shortcode for TinyMCE
	 */
	public function buildShortcode( $post ) {
		$ret = '[good-old-gallery';

		foreach ( $post as $key => $item ) {
			if ( $key != 'gog-shortcode' && $key != 'submit' ) {
				if ( !empty( $item ) ) {
					if ( is_array( $item ) ) {
						$old_item = $item;
						reset( $item );
						$new_key = key( $item );
						$ret .= $old_item[$new_key] ? ' ' . $new_key . '="' . $old_item[$new_key] . '"' : '';
					}
					else {
						$ret .= $item ? ' ' . $key . '="' . $item . '"' : '';
					}
				}
			}
		}

		return $ret . ']';
	}
}
