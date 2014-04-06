<?php

/**
 * Form builder
 */
class GOG_FormBuilder {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Loops through the form array and prints sections and fields accordingly using the Settings API.
	 */
	public function parseForm( $form, $name, $page, $saved ) {
		// Loop through form array
		foreach ( $form as $section_id => $section ) {
			// Print section
			add_settings_section(
				// $id
				GOG_PLUGIN_SHORT . '_' . $section_id,
				// $title
				__( $section['title'], 'goodoldgallery' ),
				// $callback
				$section['callback'],
				// $page
				$page
			);

			// Print fields in section
			if ( !empty( $section['fields'] ) && is_array( $section['fields'] ) ) {
				foreach ( $section['fields'] as $id => $field ) {
					$field['args'] += array(
						'id'      => $id,
						'default' => isset( $saved[$id] ) ? $saved[$id] : '',
						'name'    => GOG_PLUGIN_SHORT . '_' . $name . '[' . $id . ']',
					);

					$title = isset( $field['title'] ) ? '<label for="' . $id . '">' . __( $field['title'], 'goodoldgallery' ) .'</label>' : '';

					if ( method_exists( 'GOG_FormBuilder', $field['type'] ) ) {
						add_settings_field(
							// $id
							$id,
							// $title
							$title,
							// $callback (input type callback)
							array( 'GOG_FormBuilder', $field['type'] ),
							// $page
							$page,
							// $section
							GOG_PLUGIN_SHORT . '_' . $section_id,
							// $args
							$field['args']
						);
					}
				}
			}
		}
	}

	/**
	 * Loops through the form array and prints fields accordingly using html.
	 */
	public function parseFormCustom( $form, $saved = array() ) {
		$output = '';

		foreach ( $form as $section ) {
			foreach ( $section['fields'] as $key => $item ) {
				if ( !isset( $item['ignore'] ) ) {
					$lkey = strtolower( $key );
					$item['args'] += array(
						'id'      => isset( $item['widget_id'] ) ? $item['widget_id'] : $lkey,
						'default' => isset( $saved[$key] ) ? $saved[$key] : '',
						'name'    => isset( $item['widget_name'] ) ? $item['widget_name'] : GOG_PLUGIN_SHORT . '_' . $lkey . '[' . $lkey . ']',
					);

					echo '<p>';
					echo $item['type'] != 'checkbox' ? '<label for="' . $lkey . '">' . __( $item['title'], 'goodoldgallery' ) .':</label> ' : '';
					if ( method_exists( 'GOG_FormBuilder', $item['type'] ) ) {
						self::$item['type']( $item['args'] );
					}
					echo '</p>';
				}
			}
		}

		return $output;
	}

	/**
	 * Register errors and notices.
	 */
	public function notices() {
		settings_errors();
	}

	/**
	 * Outputs plain markup
	 */
	public function markup( $args ) {
		extract( $args );

		echo isset( $desc ) ? $desc : '';
	}

	/**
	 * Ouputs a <select>.
	 */
	public function select( $args ) {
		extract( $args );

		echo '<select id="' . $id . '" name="' . $name . '">';
		foreach ( $items as $key => $item ) {
			$selected = ( $default == $key ) ? ' selected="selected"' : '';
			echo "<option value=\"$key\"$selected>" . __( $item, 'goodoldgallery' ) . "</option>";
		}
		echo "</select>";
		echo isset( $desc ) ? '<span class="description"> ' . __( $desc, 'goodoldgallery' ) . '</span>' : '';
	}

	/**
	 * Outputs a <textarea>.
	 */
	public function textarea( $args ) {
		extract( $args );

		echo "<textarea id=\"$id\" name=\"$name\" rows=\"7\" cols=\"50\" type=\"textarea\">{$default}</textarea>";
	}

	/**
	 * Outputs an <input> textfield.
	 */
	public function text( $args ) {
		extract( $args );

		$size = isset( $size ) ? $size : 40;

		echo "<input id=\"$id\" name=\"$name\" size=\"$size\" type=\"text\" value=\"{$default}\" />";
		echo isset( $desc ) ? '<span class="description"> ' . __( $desc, 'goodoldgallery' ) . '</span>' : '';
	}

	/**
	 * Outputs an <input> password field.
	 */
	public function password( $args ) {
		extract( $args );

		echo "<input id=\"$id\" name=\"$name\" size=\"40\" type=\"password\" value=\"\" />";
	}

	/**
	 * Outputs an <input> checkbox.
	 */
	public function checkbox( $args ) {
		extract( $args );

		$checked = checked( 'true', $default, false );
		echo "<input value=\"false\" name=\"$name\" type=\"hidden\" />";
		echo "<input id=\"$id\" value=\"true\" name=\"$name\" type=\"checkbox\"$checked />";
		echo isset( $label ) ? '<label for="' . $id . '"> ' . __ ( $label ) . '</label>' : '';
	}

	/**
	 * Outputs an <input> radio button.
	 */
	public function radio( $args ) {
		extract( $args );

		foreach ( $items as $key => $item ) {
			$checked = ( $default == $key ) ? ' checked="checked"' : '';
			echo "<label><input value=\"$key\" name=\"$name\" type=\"radio\"$checked /> $item</label><br />";
		}
	}
}
