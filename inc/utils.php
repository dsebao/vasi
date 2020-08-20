<?php


/**
 * Hide WordPress update nag to all but admins
 */

function hide_update_notice_to_all_but_admin()
{
	if (!current_user_can('update_core')) {
		remove_action('admin_notices', 'update_nag', 3);
	}
}
add_action('admin_head', 'hide_update_notice_to_all_but_admin', 1);


/**
 * Disable Emoji mess
 */

function disable_wp_emojicons()
{
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	add_filter('tiny_mce_plugins', 'disable_emojicons_tinymce');
	add_filter('emoji_svg_url', '__return_false');
}
add_action('init', 'disable_wp_emojicons');

function disable_emojicons_tinymce($plugins)
{
	return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
}

/**
 * Disable xmlrpc.php
 */

add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');


/* ----------------------------------------------------------------------------
 * Remove WordPress version from header
 * ------------------------------------------------------------------------- */

function simple_remove_version_info()
{
	return '';
}
add_filter('the_generator', 'simple_remove_version_info');


/* ----------------------------------------------------------------------------
 * Remove welcome panel in dashboard
 * ------------------------------------------------------------------------- */

remove_action('welcome_panel', 'wp_welcome_panel');


/** 
 * Disable JSON REST API  
 */

add_filter('json_enabled', '__return_false');
add_filter('json_jsonp_enabled', '__return_false');

/**
 * PHP Logger
 */

function php_logger($data)
{
	$output = $data;
	if (is_array($output))
		$output = implode(',', $output);

	// print the result into the JavaScript console
	echo "<script>console.log( 'PHP LOG: " . $output . "' );</script>";
}


//Agregar imagenes a los posteos
function insert_attachment($file_handler, $post_id, $setthumb = 'false')
{
	if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$attach_id = media_handle_upload($file_handler, $post_id);

	if ($setthumb) update_post_meta($post_id, '_thumbnail_id', $attach_id);
	return $attach_id;
}


function breadcrumbs()
{
	/* === OPTIONS === */
	$text['home']     = 'Inicio'; // text for the 'Home' link
	$text['category'] = 'Archivo por categoria "%s"'; // text for a category page
	$text['search']   = 'Resultado de la busqueda "%s"'; // text for a search results page
	$text['tag']      = 'Articulos con el tag "%s"'; // text for a tag page
	$text['author']   = 'Articulos pyblicados por %s'; // text for an author page
	$text['404']      = 'Error 404'; // text for the 404 page
	$show_current   = 1; // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
	$show_on_home   = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
	$show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
	$show_title     = 1; // 1 - show the title for the links, 0 - don't show
	$delimiter      = ' &raquo; '; // delimiter between crumbs
	$before         = '<span class="current">'; // tag before the current crumb
	$after          = '</span>'; // tag after the current crumb
	/* === END OF OPTIONS === */
	global $post;
	$home_link    = home_url('/');
	$link_before  = '<span typeof="v:Breadcrumb">';
	$link_after   = '</span>';
	$link_attr    = ' rel="v:url" property="v:title"';
	$link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
	$post == is_singular() ? get_queried_object() : false;
	if ($post) {
		$parent_id    = $parent_id_2 = $post->post_parent;
	} else {
		$parent_id    = $parent_id_2 = 0;
	}
	$frontpage_id = get_option('page_on_front');
	if (is_home() || is_front_page()) {
		if ($show_on_home == 1) echo '<div class="breadcrumbs"><a href="' . $home_link . '">' . $text['home'] . '</a></div>';
	} else {
		echo '<div class="breadcrumbs" xmlns:v="http://rdf.data-vocabulary.org/#">';
		if ($show_home_link == 1) {
			echo '<a href="' . $home_link . '" rel="v:url" property="v:title">' . $text['home'] . '</a>';
			if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo $delimiter;
		}
		if (is_category()) {
			$this_cat = get_category(get_query_var('cat'), false);
			if ($this_cat->parent != 0) {
				$cats = get_category_parents($this_cat->parent, TRUE, $delimiter);
				if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
				$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
				$cats = str_replace('</a>', '</a>' . $link_after, $cats);
				if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
				echo $cats;
			}
			if ($show_current == 1) echo $before . sprintf($text['category'], single_cat_title('', false)) . $after;
		} elseif (is_search()) {
			echo $before . sprintf($text['search'], get_search_query()) . $after;
		} elseif (is_day()) {
			echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
			echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F')) . $delimiter;
			echo $before . get_the_time('d') . $after;
		} elseif (is_month()) {
			echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
			echo $before . get_the_time('F') . $after;
		} elseif (is_year()) {
			echo $before . get_the_time('Y') . $after;
		} elseif (is_single() && !is_attachment()) {
			if (get_post_type() != 'post') {
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				printf($link, $home_link . $slug['slug'] . '/', $post_type->labels->singular_name);
				if ($show_current == 1) echo $delimiter . $before . get_the_title() . $after;
			} else {
				$cat = get_the_category();
				$cat = $cat[0];
				$cats = get_category_parents($cat, TRUE, $delimiter);
				if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
				$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
				$cats = str_replace('</a>', '</a>' . $link_after, $cats);
				if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
				echo $cats;
				if ($show_current == 1) echo $before . get_the_title() . $after;
			}
		} elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
			$post_type = get_post_type_object(get_post_type());
			echo $before . $post_type->labels->singular_name . $after;
		} elseif (is_attachment()) {
			$parent = get_post($parent_id);
			$cat = get_the_category($parent->ID);
			if (isset($cat[0])) {
				$cat = $cat[0];
			}
			if ($cat) {
				$cats = get_category_parents($cat, TRUE, $delimiter);
				$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
				$cats = str_replace('</a>', '</a>' . $link_after, $cats);
				if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
				echo $cats;
			}
			printf($link, get_permalink($parent), $parent->post_title);
			if ($show_current == 1) echo $delimiter . $before . get_the_title() . $after;
		} elseif (is_page() && !$parent_id) {
			if ($show_current == 1) echo $before . get_the_title() . $after;
		} elseif (is_page() && $parent_id) {
			if ($parent_id != $frontpage_id) {
				$breadcrumbs = array();
				while ($parent_id) {
					$page = get_page($parent_id);
					if ($parent_id != $frontpage_id) {
						$breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
					}
					$parent_id = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				for ($i = 0; $i < count($breadcrumbs); $i++) {
					echo $breadcrumbs[$i];
					if ($i != count($breadcrumbs) - 1) echo $delimiter;
				}
			}
			if ($show_current == 1) {
				if ($show_home_link == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo $delimiter;
				echo $before . get_the_title() . $after;
			}
		} elseif (is_tag()) {
			echo $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
		} elseif (is_author()) {
			global $author;
			$userdata = get_userdata($author);
			echo $before . sprintf($text['author'], $userdata->display_name) . $after;
		} elseif (is_404()) {
			echo $before . $text['404'] . $after;
		} elseif (has_post_format() && !is_singular()) {
			echo get_post_format_string(get_post_format());
		}
		if (get_query_var('paged')) {
			if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ' (';
			echo __('Page') . ' ' . get_query_var('paged');
			if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ')';
		}
		echo '</div><!-- .breadcrumbs -->';
	}
} // end dimox_breadcrumbs()


/*
    Get content via cUrl
*/

function url_get_contents($Url)
{
	if (!function_exists('curl_init')) {
		die();
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $Url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}



/* 
    remove wordpress logo and menu admin bar
*/
function remove_admin_bar_links()
{
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
	$wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
	$wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
	$wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
	$wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
	$wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
	$wp_admin_bar->remove_menu('updates');          // Remove the updates link
}
add_action('wp_before_admin_bar_render', 'remove_admin_bar_links');


/* 
    Funtion to call images in a post
*/
function my_image($postid = 0, $size = 'thumbnail')
{ //it can be thumbnail or full
	if ($postid < 1) {
		$postid = get_the_ID();
	}
	if (has_post_thumbnail($postid)) {
		$imgpost = wp_get_attachment_image_src(get_post_thumbnail_id($postid), $size);
		return $imgpost[0];
	} elseif ($images = get_children(array(
		'post_parent' => $postid,
		'post_type' => 'attachment',
		'numberposts' => '1',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'post_mime_type' => 'image',
	)))
		foreach ($images as $image) {
			$thumbnail = wp_get_attachment_image_src($image->ID, $size);
			return $thumbnail[0];
		}
	else {
		global $post, $posts;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		$first_img = $matches[1][0];
		return $first_img;
	}
}


/* 
    Force medium size image to crop
*/
if (false === get_option('medium_crop')) {
	add_option('medium_crop', '1');
} else {
	update_option('medium_crop', '1');
}

/* 
    Cleaner Dashboard
*/
function disable_default_dashboard_widgets()
{
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');
	remove_meta_box('dashboard_quick_press', 'dashboard', 'core');
	remove_meta_box('dashboard_primary', 'dashboard', 'core');
	remove_meta_box('dashboard_secondary', 'dashboard', 'core');
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');
	//remove_meta_box('dashboard_right_now', 'dashboard', 'core');
}
add_action('admin_menu', 'disable_default_dashboard_widgets');
