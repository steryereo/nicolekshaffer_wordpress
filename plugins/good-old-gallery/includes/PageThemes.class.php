<?php

class GOG_PageThemes {
	protected $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		// Add pages
		add_action( 'admin_init', array( $this, 'initThemesPage') );
		add_action( 'admin_menu', array( $this, 'addThemesPage' ) );
	}

	/**
	 * Register form fields.
	 */
	public function initThemesPage(){
		global $upload_url;

		// Build cache if needed.
		if ( $this->settings->themes['themes'] && !file_exists( $upload_url['basedir'] . '/good-old-gallery-themes.css' ) ) {
			wp_redirect( GOG_PLUGIN_URL . 'includes/cache.php?redirect' );
		}

		register_setting( GOG_PLUGIN_SHORT . '_themes', GOG_PLUGIN_SHORT . '_themes', array( $this, 'themesValidate' ) );

		$themes_active = FALSE;
		$themes_extra = "";
		if ( $this->settings->themes['themes'] == 'true' ) {
			$themes_active = TRUE;
			$themes_extra = " *";
		}

		// Get themes for select dropdown
		$theme_options = array( NULL => __( 'No theme', 'goodoldgallery' ) );
		$theme_options += $this->settings->GetThemes( TRUE );

		// Setup form
		$form = array(
			// Section
			'themes' => array(
				'title'    => 'Themes',
				'callback' => array( $this, 'themesHeader' ),
				'fields'   => array(
					'default' => array(
						'title' => 'Theme',
						'type'  => 'select',
						'args'  => array(
							'items' => $theme_options,
							'desc' => 'Select the default Flexslider gallery theme.',
						),
					),
					'default_css' => array(
						'title' => 'Load default CSS',
						'type'  => 'checkbox',
						'args'  => array(
							'label' => 'If selected, <strong>good-old-gallery.css</strong> with some basic slider css will be loaded.',
						),
					),
					'themes' => array(
						'title' => 'Activate all themes',
						'type'  => 'checkbox',
						'args'  => array(
							'label' => 'If selected, you will be able to choose theme in the shortcode.' . $themes_extra,
						),
					),
				),
			),
			'themes_available' => array(
				'title'    => 'Themes available',
				'callback' => array( $this, 'themesAvailable' ),
				'fields'   => array(),
			),
		);

		if ( $themes_active ) {
			$form['themes_cache'] = array(
				'title'    => 'Themes cache',
				'callback' => array( $this, 'themesCache' ),
				'fields'   => array(),
			);
		}

		GOG_FormBuilder::ParseForm( $form, 'themes', __FILE__, $this->settings->themes );
	}

	/**
	 * Add themes page.
	 */
	public function addThemesPage() {
		add_submenu_page( 'edit.php?post_type=goodoldgallery', GOG_PLUGIN_NAME . ' themes', 'Themes', 'administrator', GOG_PLUGIN_SHORT . '_themes', array( $this, 'themesPage' ) );
	}

	/**
	 * Main section header.
	 */
	public function themesHeader() {
	}

	/**
	 * Build themes function for settings page
	 */
	public function themesAvailable() {
		$themes = $this->settings->GetThemes();

		if ( $themes ) {
			echo '<ul class="themes-available">';
			foreach ( $themes as $file => $theme ) {
				$author = filter_var( $theme['AuthorURI'], FILTER_VALIDATE_URL ) ? '<a href="' . $theme['AuthorURI'] . '">' . $theme['Author'] . '</a>' : $theme['Author'];

				$scr_check = $theme['path']['path'] . '/' . substr( $file, 0, -4 ) . '.png';
				$screenshot = $theme['path']['url'] . '/' . substr( $file, 0, -4 ) . '.png';
				$screenshot = file_exists( $scr_check ) ? '<div class="screenshot"><img src="' . $screenshot . '" alt="' . $theme['Name'] . '" /></div>' : '';

				echo '<li class="theme">';
				echo $screenshot;
				echo '<div class="information">';
				echo '<span class="title">' . $theme['Name'] . '</span>';
				echo $theme['Version'] ? ' <span class="version">' . $theme['Version'] . '</span>' : '';
				echo $author ? ' by <span class="author">' . $author . '</span>' : '';
				echo $theme['Description'] ? '<div class="description">' . $theme['Description'] . '</div>' : '';
				echo '</div>';
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	/**
	 * Cache builder
	 */
	public function themesCache() {
		global $upload_url;

		if ( file_exists( $upload_url['basedir'] . '/good-old-gallery-themes.css' ) ) {
			$upload_dir = explode( '/', $upload_url['basedir'] );
			$count = count( $upload_dir );
			echo '<p>' . __( 'Cache location: ', 'goodoldgallery' ) . '<code>' . $upload_dir[$count-2] . '/' . $upload_dir[$count-1] . '/good-old-gallery-themes.css</code></p>';
			echo '<p>' . __( 'Cache last updated: ', 'goodoldgallery' ) . date( 'H:i, Y-m-d', filemtime( $upload_url['basedir'] . '/good-old-gallery-themes.css' ) ) . '</p>';
		}

		echo "<p>" . sprintf( __( "* To use all themes you need to %s, otherwise the themes won't be loaded, <strong>you also need to rebuild the cache if you install or delete themes</strong>.", 'goodoldgallery' ), '<a href="' . GOG_PLUGIN_URL . 'includes/cache.php?redirect">' . __( 'rebuild the css cache', 'goodoldgallery' ) . '</a>' ) . "</p>";
	}

	/**
	 * Print settings page
	 */
	public function themesPage() {
?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo GOG_PLUGIN_NAME; ?> settings</h2>
			<div class="go-flattr"><a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://wordpress.org/extend/plugins/good-old-gallery/"></a></div>
			<form action="options.php" method="post" id="go-themes-form">
			<?php settings_fields( GOG_PLUGIN_SHORT . '_themes' ); ?>
			<?php do_settings_sections( __FILE__ ); ?>
			<p class="submit">
				<?php submit_button( __( 'Save Settings', 'goodoldgallery' ), 'primary', GOG_PLUGIN_SHORT . '_themes[save]', false ); ?>
			</p>
			</form>
		</div>
<?php
	}

	public function themesValidate( $input ) {
		if ($input['default']) {
			$theme = $this->settings->GetThemes();
			$theme = $theme[$input['default']];
			$input['theme'] = array( 'url' => $theme['path']['url'], 'class' => $theme['Class'], 'id' => substr($input['default'], 0, -4) );
		}
		return $input;
	}
}
