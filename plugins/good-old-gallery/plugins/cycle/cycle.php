<?php

function goodold_gallery_cycle_setup() {
	return array(
		'title'   => 'jQuery Cycle',
		'version' => '2.9999.5',
		'files'   => array( 'jquery.cycle.all.min.js' ),
		'class'   => '.slides',
		'url'     => 'http://jquery.malsup.com/cycle/',
		'info'    => 'The jQuery Cycle Plugin is a slideshow plugin that supports many different types of transition effects. It supports pause-on-hover, auto-stop, auto-fit, before/after callbacks, click triggers and much more.',
	);
}

function goodold_gallery_cycle_settings() {
	return array(
		'fx'          => 'fade',
		'speed'       => 500,
		'timeout'     => 10000,
		'navigation'  => 'true',
		'pager'       => 'true',
		'prev'        => 'prev',
		'next'        => 'next',
	);
}

function goodold_gallery_cycle_settings_numeric() {
	return array(
		'speed' => __( 'Animation speed', 'goodoldgallery'),
		'timeout' => __( 'Transition speed', 'goodoldgallery'),
	);
}

function goodold_gallery_cycle_settings_form() {
	return array(
		// Section
		'cycle_settings' => array(
			'title'    => 'jQuery Cycle Settings',
			'callback' => array( 'GOG_PageSettings', 'settingsHeader' ),
			'fields'   => array(
				'fx' => array(
					'title' => 'Transition animation',
					'type'  => 'select',
					'args'  => array(
						'items' => array(
							'' => '- Select animation -',
							'fade' => 'Fade',
							'scrollHorz' => 'Horizontal scroll',
							'scrollVert' => 'Vertical scroll',
						),
						'desc' => 'Animation that should be used.',
					),
				),
				'timeout' => array(
					'title' => 'Transition speed',
					'type'  => 'text',
					'args'  => array(
						'size' => 4,
					),
				),
				'speed' => array(
					'title' => 'Animation speed',
					'type'  => 'text',
					'args'  => array(
						'size' => 4,
					),
				),
				'pager' => array(
					'title' => 'Show pager',
					'type'  => 'checkbox',
					'args'  => array(
						'label' => 'Select if the pager should be displayed.',
					),
				),
				'navigation' => array(
					'title' => 'Show navigation',
					'type'  => 'checkbox',
					'args'  => array(
						'label' => 'Select if you would like to add PREV and NEXT buttons.',
					),
				),
				'prev' => array(
					'title' => 'Prev',
					'type'  => 'text',
					'args'  => array(
						'desc' => 'Text used for PREV button.',
						'size' => 4,
					),
					'ignore' => TRUE,
				),
				'next' => array(
					'title' => 'Next',
					'type'  => 'text',
					'args'  => array(
						'desc' => 'Text used for NEXT button.',
						'size' => 4,
					),
					'ignore' => TRUE,
				),
			),
		),
	);
}

function goodold_gallery_cycle_shortcode_extras( $settings ) {
	$ret = array(
		'navigation' => '',
		'pager' => '',
		'script_extras' => '',
		'settings_extras' => array(),
	);

	extract( $settings );

	// NAVIGATION
	if ( $settings['navigation']['val'] == 'true' ) {
		$ret['navigation'] = '<div class="nav">' . "\n";
		$ret['navigation'] .= '<span class="prev">' . $settings['prev']['val'] . '</span><span class="next">' . $settings['next']['val'] . '</span>' . "\n";
		$ret['navigation'] .= '</div>' . "\n";
		$ret['script_extras']  .= 'prev: "#go-gallery-' . $id . '-' . $i . ' .prev",' .
														 'next: "#go-gallery-' . $id . '-' . $i . ' .next",';
	}

	// PAGER
	if ( $settings['pager']['val'] == 'true' ) {
		$ret['pager'] .= '<div class="pager"></div>' . "\n";
		$ret['script_extras'] .= 'pager: "#go-gallery-' . $id . '-' . $i . ' .pager",';
	}

	return $ret;
}
