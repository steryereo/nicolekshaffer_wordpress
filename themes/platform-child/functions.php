<?php


	// Add action to enqueue scripts
	add_action('wp_enqueue_scripts','theme_javascript');
	 
	 
	// Function to load theme javascript
	function theme_javascript() {
	 
		// Enqueue theme script
		wp_enqueue_script('tweaks','/wp-content/themes/platform-child/js/tweaks.js',array('jquery'),'1.0', TRUE);
	 
	}

?>