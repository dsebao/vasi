<?php

// Useful Shortcodes
add_shortcode('home_url', 'return_home_url');

add_filter('widget_text', 'do_shortcode'); // enable shortcodes on widgets

function return_home_url()
{
	return home_url();
}

add_shortcode('theme_url', 'return_theme_url');
function return_theme_url()
{
	return get_bloginfo('template_url');
}

add_shortcode('uploads_url', 'return_uploads_url');
function return_uploads_url()
{
	$uploads_dir = wp_upload_dir();
	return $uploads_dir['baseurl'];
}
