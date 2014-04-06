<?php

class GOG_Admin {
	protected $settings;

	/**
	 * Constructor loads admin functionality.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;

		$h = new GOG_Helpers;
		$wp_root = $h->getWPRoot( dirname( dirname( __FILE__ ) ) );
		require_once( $wp_root . "/wp-load.php" );
		require_once( $wp_root . "/wp-admin/includes/admin.php" );

		// Register new Good Old Gallery post type.
		$this->registerPostType();

		// Add admin
		add_filter( 'plugin_action_links', array( $this, 'pluginActionLinks' ), 10, 2 );

		// Add media
		add_filter( 'media_upload_tabs', array( $this, 'mediaTab' ) );
		add_filter( "attachment_fields_to_save", array( $this, 'imageAttachmentFieldsToSave' ), null , 2 );
		add_filter( "attachment_fields_to_edit", array( $this, 'imageAttachmentFieldsToEdit' ), null, 2 );
		add_filter( 'media_buttons_context', array( $this, 'mediaButton' ) );
		add_filter( 'wp_ajax_delete_attachment', array( $this, 'deleteAttachment' ) );

		// Register styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'loadStylesAndScripts' ) );

		// Add action to save the gallery metadata
		add_action( 'save_post', array( $this, 'saveMetaContent' ) );

		// Remove unwanted tabs for Good Old Gallery content uploader
		if ( isset( $_GET['post_id'] ) && ( get_post_type( $_GET['post_id'] ) == 'goodoldgallery') ) {
			add_filter( 'media_upload_tabs', array( $this, 'removeMediaTabs' ) );
		}

		// Insert shortcode into post othwerwise load page
		if ( isset( $_POST['gog-shortcode'] ) ) {
			media_send_to_editor( GOG_Shortcode::buildShortcode( $_POST ) );
		}
		else {
			add_action( 'media_upload_gogallery', array( $this, 'mediaTabHandle' ) );
		}
	}

	/**
	 * Register post type.
	 */
	public function registerPostType() {
		return register_post_type( 'goodoldgallery',
			array(
				'labels' => array(
					'name'               => _x( 'Galleries', 'gallery type general name', 'goodoldgallery' ),
					'singular_name'      => _x( 'Gallery', 'gallery type singular name', 'goodoldgallery' ),
					'add_new'            => __( 'Add New', 'goodoldgallery' ),
					'add_new_item'       => __( 'Add New Gallery', 'goodoldgallery' ),
					'edit_item'          => __( 'Edit Gallery', 'goodoldgallery' ),
					'new_item'           => __( 'New Gallery', 'goodoldgallery' ),
					'view_item'          => __( 'View Gallery', 'goodoldgallery' ),
					'search_items'       => __( 'Search Galleries', 'goodoldgallery' ),
					'not_found'          => __( 'No Galleries found', 'goodoldgallery' ),
					'not_found_in_trash' => __( 'No Galleries found in Trash', 'goodoldgallery' ),
					'parent_item_colon'  => '',
				),
			'public'               => false,
			'description'          => __( 'A Gallery that is used to display sliders.', 'goodoldgallery' ),
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_admin_bar'    => true,
			'query_var'            => 'goodoldgallery',
			'capability_type'      => 'post',
			'hierarchical'         => false,
			'menu_position'        => 10,
			'exclude_from_search'  => true,
			'supports'             => array( 'title' ),
			'rewrite'              => array( 'slug' => 'good-old-gallery', 'with_front' => false ),
			'menu_icon'            => GOG_PLUGIN_URL . 'assets/img/good-old-gallery.png',
			'register_meta_box_cb' => array( $this, 'addMeta' ),
			)
		);
	}

	/**
	 * Add link to settings page on plugins page.
	 */
	public function pluginActionLinks( $links, $file ) {
		if ( $file == GOG_PLUGIN_BASENAME . '/good-old-gallery.php' ) {
			$links[] = '<a href="edit.php?post_type=goodoldgallery&page=gog_settings">'.__( 'Settings', 'goodoldgallery' ).'</a>';
		}

		return $links;
	}

	/**
	 * Register styles and scripts.
	 */
	public function loadStylesAndScripts( $hook ) {
		// Load up media upload when administering gallery content
		if ( ( ( $hook == 'edit.php' || $hook == 'post.php' ) && ( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'goodoldgallery' ) ) || ( $hook == 'post-new.php' && $_GET['post_type'] == 'goodoldgallery' ) ) {
			add_thickbox();

			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'good-old-gallery-admin', GOG_PLUGIN_URL . 'assets/js/good-old-gallery-admin.js', 'jquery', false, false );

			wp_register_style( 'good-old-gallery-admin', GOG_PLUGIN_URL . 'assets/css/good-old-gallery-admin.css' );
			wp_enqueue_style( 'good-old-gallery-admin' );
		}

		// Add CSS and JS for admin section
		if ( $hook == 'goodoldgallery_page_gog_settings' || $hook == 'goodoldgallery_page_gog_themes' ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'good-old-gallery-admin', GOG_PLUGIN_URL . 'assets/js/good-old-gallery-admin.js', 'jquery', false, false );

			wp_register_style( 'good-old-gallery-admin', GOG_PLUGIN_URL . 'assets/css/good-old-gallery-admin.css' );
			wp_enqueue_style( 'good-old-gallery-admin' );

			wp_register_style( 'font-awesome', GOG_PLUGIN_URL . 'assets/css/font-awesome.css' );
			wp_enqueue_style( 'font-awesome' );
		}

		// Add CSS to thickbox media popup
		if ( $hook == 'media-upload-popup' && ( isset( $_GET['tab'] ) && $_GET['tab'] == 'gallery' ) && get_post_type( $_GET['post_id'] ) == 'goodoldgallery' ) {
			wp_register_style( 'good-old-gallery-admin', GOG_PLUGIN_URL . 'assets/css/good-old-gallery-admin.css' );
			wp_enqueue_style( 'good-old-gallery-admin' );
		}
	}


	// META DATA ------------------------------------------
	// ----------------------------------------------------


	/**
	 * Registered function that adds a metadata box to the post edit screen for
	 * the fortunecookie type.
	 */
	public function addMeta() {
		add_meta_box( 'good-old-gallery',
			__( 'Gallery', 'goodoldgallery' ),
			array( $this, 'addMetaContent' ),
			'goodoldgallery',
			'advanced'
		);
	}

	/**
	 * Registered function that provides the content for the metadata box.
	 */
	public function addMetaContent() {
		$args = func_get_args();

		$edit = FALSE;
		$button = __( 'Upload images', 'goodoldgallery' );
		if ( !empty( $_GET['post'] ) ) {
			$button = __( 'Manage gallery', 'goodoldgallery' );
			$edit = TRUE;
		}

		echo '<div class="go-gallery-admin">';

		$out = $this->uploadButton( $button, 'image' );
		$context = apply_filters( 'media_buttons_context', __( '%s' ) );
		printf( $context, $out );

		if ( !$edit ) {
			echo ' <span class="description">' . __( 'Click to upload your images.', 'goodoldgallery' ) . '</span>';
		}
		else {
			echo ' <span class="description">' . __( 'Click to manage your images.', 'goodoldgallery' ) . '</span>';
		}

		echo '</div>';

		if ( $edit ) {
			echo '<p>' . __( 'Shortcode for this gallery', 'goodoldgallery' ) . ': <code>[good-old-gallery id="' . $_GET['post'] . '"]</code></p>';
			echo '<span class="description">';
			echo '<p>' . __( 'Shortcodes can be used to paste a gallery into a post or a page, just copy the full code and paste it into the page/post in HTML mode.', 'goodoldgallery' ) . '</p>';
			echo '<p>' . __( "To generate a shortcode with custom variables, click on the 'Insert Good Old Gallery' icon next to 'Upload/Insert' on a page or a post.", 'goodoldgallery' ) . '</p>';
			echo '</span>';

			$attachments = get_children( array(
				'post_parent' => $_GET['post']
			) );

			if ( $attachments ) {
				echo '<h4>' . __( 'Gallery overview', 'goodoldgallery' ) . '</h4>' . "\n";
				echo '<ul class="attachments">' . "\n";
				foreach ( $attachments as $gallery_id => $attachment ) {
					echo '<li>' . "\n";
					echo '<div class="image">' . wp_get_attachment_image( $gallery_id, 'thumbnail', false ) . '</div>' . "\n";
					echo '<div class="id">' . sprintf( __( 'Attachment ID: %s ', 'goodoldgallery' ), $attachment->ID ) . '</div>' . "\n";

					$id = $attachment->ID;
					$post = get_post( $id );
					$filename = esc_html( basename( $post->guid ) );

					// Delete code borrowed from wordpress.
					echo "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$id').style.display='block';return false;\">" . __( 'Delete', 'goodoldgallery' ) . "</a>
						<div id='del_attachment_$id' class='del-attachment' style='display:none;'>" . sprintf( __( 'You are about to delete <strong>%s</strong>.', 'goodoldgallery' ), $filename ) . "
						<a href='" . wp_nonce_url( GOG_PLUGIN_URL . "/includes/delete.php?action=delete&amp;post=$attachment->ID", 'delete-attachment_' . $attachment->ID ) . "' id='del[$attachment->ID]' data-id='$attachment->ID' data-nonce='" . wp_create_nonce( 'goodold_gallery_delete_attachment' ) . "' class='submitdelete button'>" . __( 'Continue', 'goodoldgallery' ) . '</a>' . "
						<a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel', 'goodoldgallery' ) . "</a>
						</div>" . "\n";
					echo '</li>' . "\n";
				}
				echo '</ul>' . "\n";
			}
		}
	}

	/**
	 * Registered function that saves the metadata content to the database,
	 *
	 * @param int $post_id The current post ID.
	 */
	public function saveMetaContent( $post_id ) {
		// Nothing saved at the moment.
	}

	/**
	 * Adds custom image link field.
	 */
	public function imageAttachmentFieldsToEdit( $form_fields, $post ) {
		$form_fields["goodold_gallery_image_link"] = array(
			"label" => __( "Link", 'goodoldgallery' ),
			"input" => "text",
			"value" => get_post_meta( $post->ID, "_goodold_gallery_image_link", true ), "helps" => __( 'Used by ' . GOG_PLUGIN_NAME . '.', 'goodoldgallery' ),
		);

		return $form_fields;
	}

	/**
	 * Saves custom image link field.
	 */
	public function imageAttachmentFieldsToSave( $post, $attachment ) {
		if ( isset( $attachment['goodold_gallery_image_link'] ) ){
			update_post_meta( $post['ID'], '_goodold_gallery_image_link', $attachment['goodold_gallery_image_link'] );
		}
		return $post;
	}


	// MEDIA TAB ------------------------------------------
	// ----------------------------------------------------


	/**
	 * Adds Good Old Gallery media tab.
	 */
	public function mediaTab( $tabs ) {
		$posts = GOG_Helpers::getGalleries( 'publish' );

		if ( $posts ) {
			$newtab = array( 'gogallery' => __( GOG_PLUGIN_NAME, 'goodoldgallery' ) );
			$tabs = array_merge($tabs, $newtab);
		}

		return $tabs;
	}

	/**
	 * Good Old Gallery tab page.
	 */
	public function mediaProcess() {
		media_upload_header();

		// Build dropdown with galleries
		$posts = GOG_Helpers::getGalleries( 'publish' );

		$options = !$posts ? "<option value=\"\">No galleries found</option>" : "";

		$gallery_options = '';
		foreach ( $posts as $p ) {
			$selected = '';
			$gallery_options .= "<option value=\"$p->ID\">$p->post_title</option>";
		}

		// Build dropdown with themes
		$theme_options = '';
		if ( $this->settings->themes['themes'] !== 'false' || $this->settings->themes['themes'] === '' ) {
			$themes = $this->settings->GetThemes();
			foreach ( $themes as $file => $theme ) {
				$theme_options .= '<option value="' . $theme['Class'] . '">' . $theme['Name'] . '</option>';
			}
		}
?>
	<div id="go-gallery-generator">
		<h3 class="media-title"><?php echo GOG_PLUGIN_NAME; ?> shortcode generator</h3>

		<div class="postbox submitdiv">
			<h3 style="margin: 0; padding: 10px;">Generator</h3>
			<div class="inside" style="margin: 10px;">
				<span class="description">
					<p>
						<?php echo __( 'Copy and paste the full generated shortcode into your page/post in HTML mode.', 'goodoldgallery' ); ?>
					</p>
				</span>

				<div id="good-old-gallery-shortcode">
					<p>
						<code>[good-old-gallery]</code>
					</p>
				</div>

			<form action="media.php" method="post" id="shortcode-form">

				<input id="gog-shortcode" name="gog-shortcode" type="hidden" value="true" />
				<input class="button submit" type="submit" name="submit" value="<?php echo __( 'Insert into post', 'goodoldgallery' ); ?>" />

<?php if ( $gallery_options ): ?>
				<p>
					<label for="id" title="<?php echo __( 'Select gallery', 'goodoldgallery' ); ?>" style="line-height:25px;"><?php echo __( 'Gallery', 'goodoldgallery' ); ?>:</label>
					<select id="id" name="id">
						<option value=""><?php echo __( 'Select gallery' ); ?></option>
						<?php echo $gallery_options; ?>
					</select>
				</p>
<?php endif; ?>

<?php if ( $theme_options ): ?>
				<p>
					<label for="theme" title="<?php echo __( 'Select theme', 'goodoldgallery' ); ?>" style="line-height:25px;"><?php echo __( 'Theme', 'goodoldgallery' ); ?>:</label>
					<select id="theme" name="theme">
						<option value="">- Select theme -</option>
						<?php echo $theme_options; ?>
					</select>
				</p>
<?php endif; ?>

				<p>
					<label for="size" title="Select gallery size" style="line-height:25px;">Image size:</label>
					<select id="size" name="size">
						<option value="">- Select size -</option>
						<option value="thumbnail">Thumbnail</option>
						<option value="medium">Medium</option>
						<option value="large">Large</option>
						<option value="full">Full</option>
					</select>
				</p>

<?php GOG_FormBuilder::parseFormCustom( $this->settings->plugin['settings_form'] ); ?>

			</form>
			</div>
		</div>
	</div>
<?php
	}

	/**
	 * Loads Good Old Gallery tab page.
	 */
	public function mediaTabHandle() {
		wp_enqueue_style( 'media' );
		wp_enqueue_script( 'gallery-insert', GOG_PLUGIN_URL . 'assets/js/good-old-gallery-admin.js', 'jquery', false, true );
		wp_enqueue_style( 'good-old-gallery', GOG_PLUGIN_URL . 'assets/css/good-old-gallery-admin.css' );
		return wp_iframe( array( $this, 'mediaProcess' ) );
	}

	/**
	 * Hide unwanted tabs in Good Old Gallery uploader.
	 */
	public function removeMediaTabs( $tabs ) {
		unset( $tabs['type_url'] );
		unset( $tabs['library'] );
		unset( $tabs['gogallery'] );
		return $tabs;
	}

	/**
	 * Custom upload media button.
	 */
	public function uploadButton( $title, $type ) {
		return '<a href="' . esc_url( get_upload_iframe_src() ) . '" id="content-add_media" class="thickbox button" title="' . $title . '" onclick="return false;">' . $title . '</a>';
	}


	/**
	 * Adds Good Old Gallery media button to posts.
	 */
	public function mediaButton( $context ) {
		$post = wp_get_single_post();

		$button = ' %s';
		if ( get_post_type() != 'goodoldgallery' && isset( $post->ID ) ) {
			$image = GOG_PLUGIN_URL . 'assets/img/good-old-gallery-small.png';
			$button .= '<a href="media-upload.php?post_id=' . $post->ID . '&tab=gogallery&TB_iframe=1" id="add_gogallery" class="thickbox add_media" title="Insert ' . GOG_PLUGIN_NAME . '"><img src="' . $image . '" /></a>';
		}

		 return sprintf( $context, $button );
	}

	/**
	 * Delete attachments from gallery posts with ajax.
	 */
	public function deleteAttachment( $post ) {
		if ( wp_delete_attachment( $_POST['att_ID'], true ) ) {
			echo sprintf( __( 'Attachment ID: %s has been deleted.', 'goodoldgallery' ), $_POST['att_ID'] );
		}
		exit();
	}
}
