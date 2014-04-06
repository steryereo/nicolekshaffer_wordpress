<?php

require_once( dirname( __FILE__ ) . '/Helpers.class.php' );
$g = new GOG_Helpers();
$wp_root = $g->getWPRoot( dirname( dirname( __FILE__ ) ) );

/** WordPress Administration Bootstrap */
$wp_load = $wp_root . "/wp-load.php";
if ( !file_exists( $wp_load ) ) {
	$wp_config = $wp_root . "/wp-config.php";
	if ( !file_exists( $wp_config ) ) {
			exit( "Can't find wp-config.php or wp-load.php" );
	}
	else {
			require_once( $wp_config );
	}
}
else {
	require_once( $wp_load );
}

require_once( $wp_root . '/wp-admin/admin.php' );

wp_reset_vars( array( 'action' ) );

if ( isset( $_GET['post'] ) ) {
	$post_id = $post_ID = ( int ) $_GET['post'];
}

if ( $post_id ) {
	$post = get_post( $post_id );
}

if ( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

switch ( $action ) {
	case 'delete':
		$sendback = wp_get_referer();

		check_admin_referer( 'delete-' . $post_type . '_' . $post_id );

		if ( !current_user_can( $post_type_object->cap->delete_post, $post_id ) )
			wp_die( __( 'You are not allowed to delete this item.', 'goodoldgallery' ) );

		$force = !EMPTY_TRASH_DAYS;
		if ( $post->post_type == 'attachment' ) {
			$force = ( $force || !MEDIA_TRASH );
			if ( ! wp_delete_attachment( $post_id, $force ) )
				wp_die( __( 'Error when deleting.', 'goodoldgallery' ) );
		}
		else {
			if ( !wp_delete_post( $post_id, $force ) ) {
				wp_die( __( 'Error when deleting.', 'goodoldgallery' ) );
			}
		}

		wp_redirect( add_query_arg( 'deleted', 1, $sendback ) );
		exit();
		break;
}
