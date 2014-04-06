<?php

class GOG_PageSettings {
	protected $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		// Add pages
		add_action( 'admin_init', array( $this, 'initSettingsPage' ) );
		add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
	}

	/**
	 * Register form fields.
	 */
	public function initSettingsPage(){
		register_setting( GOG_PLUGIN_SHORT . '_settings', GOG_PLUGIN_SHORT . '_settings', array( $this, 'settingsValidate' ) );

		// Get themes for select dropdown
		$plugin_options = array( NULL => __( 'No plugin', 'goodoldgallery' ) );
		$plugin_settings = $this->settings->GetPlugins( TRUE );
		$plugin_info = '';
		foreach ( $plugin_settings as $plugin => $settings ) {
			$plugin_options[$plugin] = $settings['title'];
			$plugin_info .= '<div class="plugin-info ' . $plugin . '">';
			$plugin_info .= '<h4>' . $settings['title'] . ' <span class="version">' . sprintf( __( 'Version: %s', 'goodoldgallery' ), $settings['version'] ) . '</span></h4>';
			$plugin_info .= '<p><a href="' . $settings['url'] . '">' . $settings['url'] . '</a></p>';
			$plugin_info .= '<p>' . __( $settings['info'], 'goodoldgallery' ) . '</p>';
			$plugin_info .= '</div>';
		}

		// Setup form
		$form = array(
			// Section
			'basic_settings' => array(
				'title'    => 'Basic Settings',
				'callback' => array( $this, 'settingsHeader' ),
				'fields'   => array(
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
					'set_width' => array(
						'title' => 'Set gallery width',
						'type'  => 'select',
						'args'  => array(
							'items' => array(
								''         => 'Off',
								'smallest' => 'Smallest',
								'largest'  => 'Largest',
							),
							'label' => 'Automatically set the width of a gallery to the smallest or largest image within it.',
						),
					),
					'set_height' => array(
						'title' => 'Set gallery height',
						'type'  => 'select',
						'args'  => array(
							'items' => array(
								''         => 'Off',
								'smallest' => 'Smallest',
								'largest'  => 'Largest',
							),
							'label' => 'Automatically set the height of a gallery to the smallest or largest image within it.',
						),
					),
					'title' => array(
						'title' => 'Show title',
						'type'  => 'checkbox',
						'args'  => array(
							'label' => 'Show title for each image.',
						),
					),
					'description' => array(
						'title' => 'Show description',
						'type'  => 'checkbox',
						'args'  => array(
							'label' => 'Show description for each image.',
						),
					),
				),
			),
			// Section
			'order' => array(
				'title'    => 'Fields Order',
				'callback' => array( $this, 'orderHeader' ),
				'fields'   => array(
					'order_title' => array(
						'title' => 'Title',
						'type'  => 'select',
						'args'  => array(
							'items' => array(
								'1' => '1',
								'2' => '2',
								'3' => '3',
							),
						),
					),
					'order_desc' => array(
						'title' => 'Description',
						'type'  => 'select',
						'args'  => array(
							'items' => array(
								'1' => '1',
								'2' => '2',
								'3' => '3',
							),
						),
					),
					'order_image' => array(
						'title' => 'Image',
						'type'  => 'select',
						'args'  => array(
							'items' => array(
								'1' => '1',
								'2' => '2',
								'3' => '3',
							),
						),
					),
				),
			),
			// Section
			'plugins' => array(
				'title'    => 'Plugins',
				'callback' => array( $this, 'settingsHeader' ),
				'fields'   => array(
					'plugin' => array(
						'title' => 'Plugin',
						'type'  => 'select',
						'args'  => array(
							'items' => $plugin_options,
							'desc' => 'Select what slider plugin that should be used.',
						),
					),
					'info' => array(
						'type' => 'markup',
						'args' => array(
							'desc' => $plugin_info,
						),
					),
				),
			),
		);

		if ( !empty($this->settings->plugin) ) {
			$form += $this->settings->plugin['settings_form'];
		}

		GOG_FormBuilder::parseForm( $form, 'settings', __FILE__, $this->settings->settings );
	}

	/**
	 * Add settings page.
	 */
	public function addSettingsPage() {
		add_submenu_page( 'edit.php?post_type=goodoldgallery', GOG_PLUGIN_NAME . ' settings', 'Settings', 'administrator', GOG_PLUGIN_SHORT . '_settings', array( $this, 'settingsPage' ) );
	}

	/**
	 * Main section header.
	 */
	public function settingsHeader() {
	}

	/**
	 * Order section header.
	 */
	public function orderHeader() {
		$items = GOG_Helpers::orderFields( $this->settings->settings );

		$li = '';
		if ( !empty( $items ) && count( $items ) == 3 ) {
			foreach ( $items as $key ) {
				switch ( $key ) {
					case "title":
						$title = __( "Title", 'goodoldgallery' );
						break;
					case "desc":
						$title = __( "Description", 'goodoldgallery' );
						break;
					case "image":
						$title = __( "Image", 'goodoldgallery' );
						break;
				}
				$li .= "\t" . '<li id="' . $key . '"><i class="icon-move"></i> ' . $title . '</li>' . "\n";
			}
		}
		else {
			$li = <<<ITEMS
	<li id="title"><i class="icon-move"></i> Title</li>
	<li id="desc"><i class="icon-move"></i> Description</li>
	<li id="image"><i class="icon-move"></i> Image</li>
ITEMS;
		}

		echo '<ul id="order">' . $li . '</ul>';
	}

	/**
	 * Print settings page
	 */
	public function settingsPage() {
?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo GOG_PLUGIN_NAME; ?> settings</h2>
			<div class="go-flattr"><a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://wordpress.org/extend/plugins/good-old-gallery/"></a></div>
			<p><?php echo sprintf( __( 'These settings are the defaults that will be used by your selected plugin (if an animation is chosen), settings can be overridden by adding variables to the %s shortcode.', 'goodoldgallery' ), '<code>[good-old-gallery]</code>' ); ?><br />
			<?php echo sprintf( __( 'The simplest way to insert a shortcode is to use the built in %s button found next to <em>Upload/Insert</em> above the text area.', 'goodoldgallery' ), '<em>' . __( 'Insert Good Old Gallery', 'goodoldgallery' ) . '</em>' ); ?><br /><br />
			<?php echo sprintf( __( 'More help can be found at %s', 'goodoldgallery' ), '<a href="http://wordpress.org/extend/plugins/good-old-gallery/installation/">wordpress.org</a>' ); ?><br /><br />
			<?php echo sprintf( __( '%s created and maintained by %s.', 'goodoldgallery' ), GOG_PLUGIN_NAME, '<a href="http://unwi.se/">Linus Lundahl</a>' ); ?></p>
			<div class="tip"><strong><?php echo __( 'Tip', 'goodoldgallery' ); ?>!</strong> <?php echo sprintf( __( "You can use %s with regular galleries that you have uploaded on a page/post, don't enter any ID in the shortcode and it will look for a gallery attached to the current page/post.", 'goodoldgallery' ), GOG_PLUGIN_NAME ); ?></div>
			<form action="options.php" method="post" id="go-settings-form">
			<?php settings_fields( GOG_PLUGIN_SHORT . '_settings' ); ?>
			<?php do_settings_sections( __FILE__ ); ?>
			<p class="submit">
				<?php submit_button( __( 'Save Settings', 'goodoldgallery' ), 'primary', GOG_PLUGIN_SHORT . '_settings[save]', false ); ?>
			</p>
			</form>
		</div>
<?php
	}

	public function settingsValidate( $input ) {
		// Set default settings if plugin is changed
		if ( $input['plugin'] != $this->settings->settings['plugin'] ) {
			$plugin = $this->settings->LoadPlugin( array( 'plugin' => $input['plugin'] ) );
			$input += $plugin['settings'];
		}
		else {
			// Validate numeric fields
			foreach ( $this->settings->plugin['settings_numeric'] as $key => $name ) {
				if ( !empty( $input[$key] ) && !is_numeric( $input[$key] ) ) {
					$input[$key] = '';
					add_settings_error( $key, 'must_be_numeric', sprintf( __( 'The %s field must be numeric.', 'goodoldgallery' ), $name ) );
				}
			}
		}

		unset( $input['save'] );

		return $input;
	}
}
