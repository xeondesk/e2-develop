<?php
/*
 * e2vars.php - post/comment loop variables and permalink helpers.
 */

/* ==================================================================== */
/* Loop                                                                 */
/* ==================================================================== */

function start_e2() {
	global $post, $single, $id, $postdata, $commentdata, $user_login, $user_ID, $user_level, $user_email, $user_url;
	global $posts_per_page, $what_to_show, $archive_mode, $autobr;
	global $querycount, $time_difference;
	global $row, $result;
	global $p, $paged;

	$post = $row;
	$single = (($p != '' && $p != 'all') || $paged == 1) ? true : false;
	$id = $post->ID;
	$postdata = get_postdata($id);
}

function end_e2() {
	return true;
}

/* ==================================================================== */
/* Permalinks                                                           */
/* ==================================================================== */

function permalink_link() {
	global $id, $single;
	if ($single) {
		$out = '?p=' . $id;
	} else {
		$out = '?p=' . $id;
	}
	echo $out;
}

function permalink_anchor() {
	global $id, $single;
	if ($single) {
		$the_permalink = '?p=' . $id;
	} else {
		$the_permalink = '?p=' . $id;
	}
	$the_title = attribute_escape(get_postdata_title());
	echo '<a name="' . $id . '"></a>';
}

function get_postdata_title() {
	global $postdata;
	return $postdata["Title"];
}

function permalink_single_rss() {
	global $siteurl, $blogfilename, $id;
	echo $siteurl . '/' . $blogfilename . '?p=' . $id;
}

function trackback_url() {
	global $siteurl, $id;
	echo $siteurl . '/e2trackback.php/' . $id;
}

function the_ID() {
	global $id;
	echo $id;
}

function comment_ID() {
	global $commentdata;
	echo $commentdata["comment_ID"];
}

function attribute_escape($text) {
	return htmlspecialchars($text, ENT_QUOTES, 'ISO-8859-1');
}