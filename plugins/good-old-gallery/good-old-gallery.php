<?php

/**
 *
 * Plugin Name: Good Old Gallery
 * Plugin URI: http://wp.unwi.se/good-old-gallery
 * Description: Good Old Gallery is a WordPress plugin that helps you upload image galleries that can be used on more than one page/post, it utilizes the built in gallery functionality in WP. Other features include built in Flexslider and jQuery Cycle support and Widgets.
 * Author: Linus Lundahl
 * Version: 2.1.2
 * Author URI: http://unwi.se/
 * Text Domain: goodoldgallery
 *
 */

// Globals
define( 'GOG_PLUGIN_NAME', 'Good Old Gallery' );
define( 'GOG_PLUGIN_SHORT', 'gog' );
define( 'GOG_TEXTDOMAIN', 'goodoldgallery' );
define( 'GOG_PLUGIN_VERSION', '2.1.2' );
define( 'GOG_PLUGIN_BASENAME', plugin_basename( dirname( __FILE__ ) ) );
define( 'GOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
$upload_url = wp_upload_dir();

// Load classes
$classes = array( 'Admin', 'FormBuilder', 'Helpers', 'PageSettings', 'PageThemes', 'Settings', 'Shortcode', 'Widget' );
foreach ( $classes as $class ) {
	require_once dirname( __FILE__ ) . '/includes/' . $class . '.class.php';
}

/**
 * GoodOldGallery class.
 */
class GoodOldGallery {
	public $settings;
	public $is_admin;
	static $is_loaded;

	public function __construct() {
		$this->settings = new GOG_Settings();
		$this->is_admin = is_admin();
		$this->is_loaded = FALSE;

		// Init classes
		new GOG_FormBuilder( $this->settings );
		new GOG_Shortcode( $this->settings );
		new GOG_PageSettings( $this->settings );
		new GOG_PageThemes( $this->settings );

		// Init function
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'loaded' ) );

		// Register activation/deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register widget
		add_action( 'widgets_init', array( $this, 'registerWidget' ) );

		// Add html to WP Head
		add_action( 'wp_head', array( $this, 'WPHead' ) );

		// Register post check
		add_action( 'the_posts', array( $this, 'checkPost' ) );
	}

	/**
	 * Registers init actions.
	 */
	public function init() {
		// Init admin section
		if ( $this->is_admin ) {
			return new GOG_Admin( $this->settings );
		}

		// Check if the shortcode has been loaded
		if ( is_active_widget( FALSE, FALSE, 'goodoldgallerywidget', TRUE ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'loadStylesAndScripts' ) );
			$this->is_loaded = TRUE;
		}
	}

	/**
	 * Registers functions when plugin is loaded
	 */
	public function loaded() {
		load_plugin_textdomain( GOG_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) );
	}

	/**
	 * Activation function.
	 */
	public function activate() {
		$s = new GOG_Settings;

		if ( !get_option( GOG_PLUGIN_SHORT . '_settings' ) ) {
			add_option( GOG_PLUGIN_SHORT . '_settings', $s->settings );
		}

		if ( !get_option( GOG_PLUGIN_SHORT . '_themes' ) ) {
			add_option( GOG_PLUGIN_SHORT . '_themes', $s->themes );
		}

		if ( !get_option( GOG_PLUGIN_SHORT . '_plugin' ) ) {
			add_option( GOG_PLUGIN_SHORT . '_plugin', $s->plugin );
		}
	}

	/**
	 * Deactivation function.
	 */
	public function deactivate() {
		delete_option( GOG_PLUGIN_SHORT . '_settings' );
		delete_option( GOG_PLUGIN_SHORT . '_themes' );
		delete_option( GOG_PLUGIN_SHORT . '_plugin' );
	}

	/**
	 * Registers widget functions.
	 */
	public function registerWidget() {
		return register_widget( "GOG_Widget" );
	}

	/**
	 * Adds html to WP head.
	 */
	public function WPHead() {
		// Just tag the page for fun.
		echo "\n\t" . '<!-- This site uses Good Old Gallery, get it from http://wp.unwi.se/good-old-gallery -->' . "\n\n";
	}

	/**
	 * Check for shortcodes in posts.
	 */
	public function checkPost( $posts ) {
		if ( !$this->is_loaded ) {
			$found = FALSE;
			if ( !empty( $posts ) ) {
				foreach ( $posts as $gog_get['post'] ) {
					// Check post content for shortcode.
					if ( stripos( $gog_get['post']->post_content, '[good-old-gallery') !== FALSE ) {
						$found = TRUE;
						break;
					}
					// Not found in post content, let's check custom fields.
					else {
						$custom = get_post_custom( $gog_get['post']->ID );
						foreach ( $custom as $field ) {
							if ( stripos( $field[0], '[good-old-gallery' ) !== FALSE ) {
								$found = TRUE;
								break;
							}
						}
					}
				}

				if ( $found ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'loadStylesAndScripts' ) );
				}
			}
		}

		return $posts;
	}

	/**
	 * Register styles and js for selected slider plugin.
	 */
	public function loadStylesAndScripts() {
		global $upload_url;

		if ( !$this->is_admin ) {
			// Add minified css of all themes or the selected theme css
			if ( ( !empty( $this->settings->themes['themes'] ) && $this->settings->themes['themes'] == 'true' ) && file_exists( $upload_url['basedir'] . '/good-old-gallery-themes.css') ) {
				wp_enqueue_style( 'good-old-gallery-themes', $upload_url['baseurl'] . '/good-old-gallery-themes.css' );
			}
			// Add selected themes css
			else if ( !empty( $this->settings->themes['default'] ) && empty( $this->settings->themes['all'] ) ) {
				wp_enqueue_style( 'good-old-gallery-theme', $this->settings->themes['theme']['url'] . '/' . $this->settings->themes['default'] );
			}

			// Include selected slider plugin files
			if ( !empty( $this->settings->settings['plugin'] ) ) {
				foreach ( $this->settings->plugin['setup']['files'] as $file ) {
					wp_enqueue_script( 'slider', GOG_PLUGIN_URL . 'plugins/' . $this->settings->settings['plugin'] . '/' . $file, array( 'jquery' ), '', FALSE );
				}
			}

			// Includ default good-old-gallery.css
			if ( $this->settings->themes['default_css'] == 'true' ) {
				wp_enqueue_style( 'good-old-gallery', GOG_PLUGIN_URL . 'assets/css/good-old-gallery.css' );
			}
		}
	}
}

$gog = new GoodOldGallery();
