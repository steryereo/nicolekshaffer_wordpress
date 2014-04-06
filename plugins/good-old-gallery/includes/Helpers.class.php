<?php

class GOG_Helpers {

	/**
	 * Helper function that finds the path of wp-load.php which is the root of wordpress.
	 */
	public function getWPRoot( $directory ) {
		global $wp_root;

		foreach ( glob( $directory . "/*" ) as $f ) {
			if ( 'wp-load.php' == basename( $f ) ) {
				return $wp_root = str_replace( "\\", "/", dirname( $f ) );
			}

			if ( is_dir( $f ) ) {
				$newdir = dirname( dirname( $f ) );
			}
		}

		if ( isset( $newdir ) && $newdir != $directory ) {
			if ( self::getWPRoot( $newdir ) ) {
				return $wp_root;
			}
		}

		return FALSE;
	}

	/**
	 * Returns an array of valid paths.
	 */
	public function paths( $paths ) {
		$paths = explode( "\n", $paths );

		foreach ( $paths as $key => $path ) {
			$paths[$key] = trim( $path );
		}

		return $paths;
	}

	/**
	 * Returns the saved order of title, desc and image
	 */
	public function orderFields( $settings ) {
		$ret = array();
		foreach ( $settings as $key => $val ) {
			if ( strpos( $key, 'order_' ) !== FALSE ) {
				$items[$val] = $key;
			}
		}

		if ( !empty( $items ) ) {
			ksort( $items );
			foreach ( $items as $val => $key ) {
				$id = str_replace( 'order_', '', $key );
				$ret[] = $id;
			}
		}

		return $ret;
	}

	/**
	 * Returns largest/smallest image width/height
	 */
	public function getImgSize( $type, $size, $height ) {
		$ret = 0;

		if ( $type == 'largest' ) {
			$ret = $height < $size ? $size : $height;
		}
		else if ( $type == 'smallest' ) {
			if ( $height === 0 ) {
				$ret = $size;
			}
			else {
				$ret = $height > $size ? $size : $height;
			}
		}

		return $ret;
	}

	/**
	 * Returns galleries
	 */
	public function getGalleries( $state ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare("
			SELECT ID, post_title FROM $wpdb->posts
				WHERE post_type = %s AND post_status = %s;",
				'goodoldgallery', $state
			) );
	}
}
