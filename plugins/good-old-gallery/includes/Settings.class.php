<?php
/**
 * @file Settings.class.php
 */

/**
 * Sets up the settings for Good Old Gallery.
 */
class GOG_Settings {
	private $default = array();

	public $settings = array();
	public $themes = array();

	public $plugin = array();

	public function __construct(){
		$settings = $this->getOption( 'settings' );
		$themes = $this->getOption( 'themes' );

		$this->default = array(
			'settings' => array(
				'size'        => 'full',
				'set_width'   => '',
				'set_height'  => '',
				'title'       => 'false',
				'description' => 'false',
				'plugin'      => 'cycle',
				'order_title' => 1,
				'order_desc'  => 2,
				'order_image' => 3,
			),
			'themes' => array(
				'default'     => '',
				'default_css' => 'true',
				'themes'      => '',
			),
		);

		$is_admin = is_admin();

		$this->settings = !$settings || !is_array( $settings ) ? $this->default['settings'] : $settings;
		$this->themes = !$themes || !is_array( $themes ) ? $this->default['themes'] : $themes;

		// Check for faulty settings in options
		$u = FALSE;
		if ( $is_admin && ( empty( $this->settings['plugin'] ) || empty( $settings ) || empty( $themes ) ) ) {
			update_option( GOG_PLUGIN_SHORT . '_themes', $this->default['themes'] );
			$u = TRUE;
		}

		// Load slider plugin settings from db, if not found load from settings file and save.
		$this->plugin = $this->getOption( 'plugin' );
		if ( $u || ( empty( $this->plugin['plugin'] ) || ( !empty( $this->plugin['plugin'] ) && $this->plugin['plugin'] != $this->settings['plugin'] ) ) ) {
			$this->plugin = $this->loadPlugin( $u ? $this->default['settings'] : $this->settings );
			update_option( GOG_PLUGIN_SHORT . '_plugin', $this->plugin );
		}

		// Extend default settings with slider plugin settings
		if ( isset( $this->plugin['settings'] ) && is_array( $this->plugin['settings'] ) ) {
			$this->default['settings'] += $this->plugin['settings'];
			$this->settings = !$settings || !is_array( $settings ) ? $this->default['settings'] : $settings;
		}

		// Add update settings if faulty settings in options
		if ( $u ) {
			update_option( GOG_PLUGIN_SHORT . '_settings', $this->default['settings'] );
		}
	}

	/**
	 * Get a Good Old Gallery option from the database.
	 *
	 * @param string $title
	 *
	 * @return array
	 */
	public function getOption( $title ) {
		return get_option( GOG_PLUGIN_SHORT . '_' . $title );
	}

	/**
	 * Finds available plugins.
	 *
	 * @param bool $full
	 *	- Return full plugin info or just the titles.
	 *
	 * @return array
	 */
	public function getPlugins( $full = FALSE ) {
		$plugins = array();

		$paths = array(
			GOG_PLUGIN_DIR . '/plugins' => GOG_PLUGIN_URL . 'plugins',
			WP_CONTENT_DIR . '/gog-plugins' => WP_CONTENT_URL . 'gog-plugins',
		);

		foreach ( $paths as $path => $url ) {
			if ( is_dir( $path ) ) {
				$dir = @ opendir( $path );

				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( substr( $file, 0, 1 ) != '.' ) {
						include_once( $path . '/' . $file . '/' . $file . '.php' );
						if ( function_exists( 'goodold_gallery_' . $file . '_setup' ) ) {
							$settings = call_user_func( 'goodold_gallery_' . $file . '_setup' );
							if ( $full ) {
								$plugins[$file] = $settings;
							}
							else {
								$plugins[$file] = $settings['title'];
							}
						}
					}
				}
				@ closedir( $dir );
			}
		}

		return $plugins;
	}

	/**
	 * Loads a plugin or plugin info from the plugins dir.
	 *
	 * @param array $args
	 *	 - plugin
	 *	 - function
	 *	 - settings
	 *
	 * @return array
	 */
	public function loadPlugin( $args = array() ) {
		// Add default keys
		$ret = array(
			'setup' => array(),
			'settings' => array(),
			'settings_numeric' => array(),
			'settings_form' => array(),
			'widget' => array(),
		);

		$ret['plugin'] = empty( $args['plugin'] ) ? $this->settings['plugin'] : $args['plugin'];
		if ( !empty( $ret['plugin'] ) && $ret['plugin'] != 'none') {
			require_once( GOG_PLUGIN_DIR . '/plugins/' . $ret['plugin'] . '/' . $ret['plugin'] . '.php' );

			if ( isset( $args['function'] ) ) {
				if ( function_exists( 'goodold_gallery_' . $ret['plugin'] . '_' . $args['function'] ) ) {
					$settings = isset( $args['settings'] ) ? $args['settings'] : array();
					$ret = call_user_func( 'goodold_gallery_' . $ret['plugin'] . '_' . $args['function'], $settings );
				}
			}
			else {
				foreach ( $ret as $callback => $item ) {
					if ( function_exists( 'goodold_gallery_' . $ret['plugin'] . '_' . $callback ) ) {
						$ret[$callback] = call_user_func( 'goodold_gallery_' . $ret['plugin'] . '_' . $callback );
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Finds available themes.
	 *
	 * @param bool $select
	 *	- True returns only title for dropdowns.
	 *
	 * @return array
	 */
	public function getThemes( $select = FALSE ) {
		$themes = array();
		$theme_path = get_stylesheet_directory();
		$theme_url = get_bloginfo( 'template_url' );

		$paths = array(
			GOG_PLUGIN_DIR . 'themes' => GOG_PLUGIN_URL . 'themes',
			WP_CONTENT_DIR . '/gog-themes' => WP_CONTENT_URL . '/gog-themes',
			$theme_path . '/gog-themes' => $theme_url . '/gog-themes',
		);

		foreach ( $paths as $path => $url ) {
			if ( is_dir( $path ) ) {
				$dir = @ opendir( $path );

				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( substr( $file, 0, 1 ) == '.' ) {
						continue;
					}
					if ( $file && is_dir( $path . '/' . $file ) ) {
						$subdir = @ opendir( $path . '/' . $file );
						while ( ( $subfile = readdir( $subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) == '.' ) {
								continue;
							}
							if ( substr( strtolower( $subfile ), -3) == 'css' ) {
								$info = $this->loadTheme( $path . '/' . $file . '/' . $subfile );
								if ( $select ) {
									$themes[$subfile] = $info['Name'];
								}
								else {
									$info['path'] = array( 'path' => $path . '/' . $file, 'url' => $url . '/' . $file );
									$themes[$subfile] = $info;
								}
							}
						}
						@closedir( $subdir );
					}
				}
				@ closedir( $dir );
			}
		}

		return $themes;
	}

	/**
	 * Loads information about a theme.
	 *
	 * @param string $file
	 *	- A path to a specific stylesheet file.
	 *
	 * @return array
	 */
	public function loadTheme( $file = NULL ) {
		$default_headers = array(
			'Name' => 'Style Name',
			'Class' => 'Class',
			'Description' => 'Description',
			'Version' => 'Version',
			'Author' => 'Author',
			'AuthorURI' => 'Author URI',
		);

		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		foreach ( $default_headers as $field => $regex ) {
			preg_match( '/^[ \t\/*#]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
			if ( !empty( ${$field} ) )
				${$field} = _cleanup_header_comment( ${$field}[1] );
			else
				${$field} = '';
		}

		$file_data = compact( array_keys( $default_headers ) );

		return $file_data;
	}
}
