<?php

require_once( dirname( __FILE__ ) . '/Helpers.class.php' );
require_once( dirname( __FILE__ ) . '/Settings.class.php' );
$h = new GOG_Helpers();
$wp_root = $h->getWPRoot( dirname( dirname( __FILE__ ) ) );

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

$g = new GOG_Settings();
$themes = $g->GetThemes();

function compress( $buffer ) {
	global $themes;

	// Remove comments
	$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );
	// Remove tabs, spaces, newlines, etc.
	$buffer = str_replace( array( "\r\n", "\r", "\n", "\t", '	 ', '		 ', '		 ' ), '', $buffer );
	// Add absolute paths
	foreach ( $themes as $file => $theme ) {
		$path = substr( $file, 0, -4 );
		$buffer = str_replace( 'url(' . $path, 'url(' . $theme['path']['url'] . '/' . $path, $buffer );
	}

	return $buffer;
}

header( 'Content-type: text/css' );

ob_start( "compress" );
	foreach ( $themes as $file => $theme ) {
		if ( file_exists( include( $theme['path']['path'] . '/' . $file ) ) ) {
			include( $theme['path']['path'] . '/' . $file );
		}
	}
$content = compress( ob_get_contents() );
ob_end_clean();

$upload_url = wp_upload_dir();
if ( is_writeable( $upload_url['basedir'] ) ) {
	file_put_contents( $upload_url['basedir'] . '/good-old-gallery-themes.css', $content );
}

// Redirect back to settings
if ( isset( $_GET['redirect'] ) ) {
	header( 'Location: /wp-admin/edit.php?post_type=goodoldgallery&page=gog_themes' );
}