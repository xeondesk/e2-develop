<?php
/*
 * e2template.functions.php - template tags & helpers for e2 templates.
 *
 * These functions are called at runtime from the templates, after the engine
 * files (e2vars.php, e2functions.php) have all been loaded.
 */

/* ==================================================================== */
/* Minimal filter system (add_filter / apply_filters)                    */
/* ==================================================================== */

$e2_filters = array();

function add_filter($tag, $function_to_add) {
	global $e2_filters;
	if (isset($e2_filters[$tag]) && in_array($function_to_add, $e2_filters[$tag])) {
		return;
	}
	$e2_filters[$tag][] = $function_to_add;
}

function apply_filters($tag, $value) {
	global $e2_filters;
	if (isset($e2_filters[$tag])) {
		foreach ($e2_filters[$tag] as $f) {
			if (function_exists($f)) {
				$value = call_user_func($f, $value);
			}
		}
	}
	return $value;
}

/* ==================================================================== */
/* Blog info                                                            */
/* ==================================================================== */

function bloginfo($show = '') {
	global $siteurl, $blogfilename, $blogname, $blogdescription, $admin_email, $rss_language, $e2_version;
	switch ($show) {
		case 'url':
			echo $siteurl;
			break;
		case 'description':
			echo $blogdescription;
			break;
		case 'admin_email':
			echo $admin_email;
			break;
		case 'name':
		default:
			echo $blogname;
			break;
		case 'version':
			echo $e2_version;
			break;
		case 'rdf_url':
			echo $siteurl . '/e2rdf.php';
			break;
		case 'rss_url':
			echo $siteurl . '/e2rss.php';
			break;
		case 'rss2_url':
			echo $siteurl . '/e2rss2.php';
			break;
		case 'pingback_url':
			echo $siteurl . '/xmlrpc.php';
			break;
		case 'comments_popup_script':
			comments_popup_script();
			break;
	}
}

function bloginfo_rss($show = '') {
	$info = get_bloginfo($show);
	echo convert_chars($info);
}

function get_bloginfo($show = '') {
	global $siteurl, $blogfilename, $blogname, $blogdescription, $admin_email, $rss_language, $e2_version;
	switch ($show) {
		case 'url':
			return $siteurl;
		case 'description':
			return $blogdescription;
		case 'admin_email':
			return $admin_email;
		case 'version':
			return $e2_version;
		case 'name':
		default:
			return $blogname;
	}
}

/* ==================================================================== */
/* Title                                                                */
/* ==================================================================== */

function the_title($before = '', $after = '', $echo = true) {
	global $post;
	$title = stripslashes($post->post_title);
	$title = apply_filters('the_title', $title);
	if ($echo) {
		echo $before . $title . $after;
	} else {
		return $before . $title . $after;
	}
}

function the_title_rss() {
	global $post;
	$title = stripslashes($post->post_title);
	$title = htmlspecialchars($title);
	echo $title;
}

function single_post_title($prefix = '', $display = true) {
	global $p, $id, $postdata;
	if ($p != '' && $p != 'all') {
		if (empty($postdata) || !isset($postdata["Title"])) {
			$postdata = get_postdata($p);
		}
		$title = $postdata["Title"];
		if ($display) {
			echo $prefix . strip_tags($title);
		} else {
			return strip_tags($title);
		}
	}
}

function single_cat_title($prefix = '', $display = true) {
	global $cat;
	if ($cat != '' && $cat != 'all') {
		$cat = urldecode($cat);
		$cat_name = get_catname(intval($cat));
		if ($display) {
			echo $prefix . $cat_name;
		} else {
			return $cat_name;
		}
	}
}

function single_month_title($prefix = '', $display = true) {
	global $m, $month;
	if ($m != '') {
		$m = intval($m);
		$year = substr($m, 0, 4);
		$monthnum = substr($m, 4, 2);
		if ($display) {
			echo $prefix . $month[zeroise($monthnum, 2)] . ' ' . $year;
		} else {
			return $month[zeroise($monthnum, 2)] . ' ' . $year;
		}
	}
}

/* ==================================================================== */
/* Content                                                              */
/* ==================================================================== */

function the_content($more_link_text = 'Read the rest of this entry &raquo;', $stripteaser = 0, $more_file = '') {
	global $post, $single;
	$content = $post->post_content;
	$teaser = $content;
	$content = apply_filters('the_content', $content);
	if (strpos($content, '<!--more-->')) {
		$parts = explode('<!--more-->', $content);
		if (!$single) {
			$teaser = $parts[0] . '<p><a href="?p=' . $post->ID . '">' . $more_link_text . '</a></p>';
			$content = $teaser;
		} else {
			$content = implode('', $parts);
		}
	}
	echo $content;
}

function the_content_rss($feed = '', $max_characters = 0, $more_link_text = '', $more_link_class = '', $excerpt_length = 0) {
	global $post;
	$content = $post->post_content;
	if ($excerpt_length > 0) {
		$content = strip_tags($content);
		$words = preg_split('/\s+/', trim($content));
		if (count($words) > $excerpt_length) {
			$content = implode(' ', array_slice($words, 0, $excerpt_length)) . '...';
		}
	} else {
		$content = htmlspecialchars($content);
	}
	$content = apply_filters('the_content_rss', $content);
	echo $content;
}

function link_pages($before = '<br />', $after = '<br />', $next_or_number = 'number') {
	global $post, $single;
	$content = $post->post_content;
	$numpages = substr_count($content, '<!--nextpage-->') + 1;
	if ($numpages <= 1) {
		return;
	}
	$curpage = 1;
	$paged = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
	$paged = ($paged < 1) ? 1 : $paged;
	echo $before;
	if ($next_or_number == 'next') {
		if ($paged < $numpages) {
			echo '<a href="?p=' . $post->ID . '&page=' . ($paged + 1) . '">next &raquo;</a>';
		}
	} else {
		for ($i = 1; $i <= $numpages; $i++) {
			if ($i == $paged) {
				echo '<b>' . $i . '</b>';
			} else {
				echo '<a href="?p=' . $post->ID . '&page=' . $i . '">' . $i . '</a>';
			}
			if ($i < $numpages) {
				echo ' ';
			}
		}
	}
	echo $after;
}

/* ==================================================================== */
/* Date / time                                                          */
/* ==================================================================== */

$previousday = '';

function the_date($format = '', $before = '', $after = '', $echo = true) {
	global $post, $previousday, $dateformat;
	if ($format == '') {
		$format = $dateformat;
	}
	$the_date = mysql2date($format, $post->post_date);
	$return = '';
	if ($the_date != $previousday) {
		$return = $before . $the_date . $after;
		$previousday = $the_date;
	}
	if ($echo) {
		echo $return;
	} else {
		return $return;
	}
}

function the_time($d = '') {
	global $post, $timeformat;
	if ($d == '') {
		$d = $timeformat;
	}
	$time = mysql2date($d, $post->post_date);
	echo $time;
}

/* ==================================================================== */
/* Author                                                               */
/* ==================================================================== */

function the_author() {
	global $postdata;
	$authordata = get_userdata($postdata["Author_ID"]);
	$nickname = ($authordata["user_idmode"] == 'nickname') ? $authordata["user_nickname"] : $authordata["user_login"];
	echo $nickname;
}

function the_author_email() {
	global $postdata;
	$authordata = get_userdata($postdata["Author_ID"]);
	echo $authordata["user_email"];
}

function the_author_rss() {
	global $postdata;
	$authordata = get_userdata($postdata["Author_ID"]);
	echo htmlspecialchars($authordata["user_nickname"]);
}

function the_author_email_rss() {
	global $postdata;
	$authordata = get_userdata($postdata["Author_ID"]);
	echo htmlspecialchars($authordata["user_email"]);
}

/* ==================================================================== */
/* Category                                                             */
/* ==================================================================== */

function the_category_ID() {
	global $postdata;
	echo $postdata["Category"];
}

function the_category($separator = ' ') {
	global $postdata;
	$cat_name = get_catname($postdata["Category"]);
	echo $cat_name;
}

function the_category_rss() {
	global $postdata;
	$cat_name = get_catname($postdata["Category"]);
	echo htmlspecialchars($cat_name);
}

function the_category_unicode() {
	global $postdata;
	$cat_name = get_catname($postdata["Category"]);
	echo convert_chars($cat_name);
}

function list_cats($sort_column = 'name', $optionall = 'All', $feed = 'name') {
	global $tablecategories;
	$q = "SELECT cat_ID, cat_name FROM $tablecategories ORDER BY cat_ID";
	$result = mysql_query($q);
	if ($optionall != '') {
		echo "<a href=\"?cat=all\">$optionall</a><br />\n";
	}
	while ($row = mysql_fetch_object($result)) {
		$cat_ID = $row->cat_ID;
		$cat_name = stripslashes($row->cat_name);
		if ($cat_ID != 0) {
			echo "<a href=\"?cat=$cat_ID\">$cat_name</a><br />\n";
		}
	}
}

function dropdown_categories() {
	global $tablecategories;
	$q = "SELECT cat_ID, cat_name FROM $tablecategories ORDER BY cat_ID";
	$result = mysql_query($q);
	echo "<select name=\"post_category\" id=\"post_category\">\n";
	while ($row = mysql_fetch_object($result)) {
		$cat_ID = $row->cat_ID;
		$cat_name = stripslashes($row->cat_name);
		echo "\t<option value=\"$cat_ID\">$cat_name</option>\n";
	}
	echo "</select><br />\n";
}

/* ==================================================================== */
/* Comments                                                             */
/* ==================================================================== */

function comment_author() {
	global $commentdata;
	echo stripslashes($commentdata["comment_author"]);
}

function comment_author_email_link($linktext = '', $before = '', $after = '') {
	global $commentdata;
	$email = $commentdata["comment_author_email"];
	if (($email != '') && ($email != ' ')) {
		$display = ($linktext != '') ? $linktext : $email;
		echo $before . '<a href="mailto:' . antispambot($email) . '">' . $display . '</a>' . $after;
	}
}

function comment_author_url_link($linktext = '', $before = '', $after = '') {
	global $commentdata;
	$url = $commentdata["comment_author_url"];
	if (($url != '') && ($url != ' ')) {
		$display = ($linktext != '') ? $linktext : $url;
		$url = ((!stristr($url, '://')) && ($url != '')) ? 'http://' . $url : $url;
		echo $before . '<a href="' . $url . '">' . $display . '</a>' . $after;
	}
}

function comment_author_url() {
	global $commentdata;
	echo $commentdata["comment_author_url"];
}

function comment_text() {
	global $commentdata;
	echo stripslashes($commentdata["comment_content"]);
}

function comment_date($d = '') {
	global $commentdata, $dateformat;
	if ($d == '') {
		$d = $dateformat;
	}
	echo mysql2date($d, $commentdata["comment_date"]);
}

function comment_time($d = '') {
	global $commentdata, $timeformat;
	if ($d == '') {
		$d = $timeformat;
	}
	echo mysql2date($d, $commentdata["comment_date"]);
}

function comments_popup_link($zero = 'No Comments', $one = '1 Comment', $more = '% Comments', $none = 'Comments Off', $page = '') {
	global $id, $comment_count, $tablecomments;
	$comments = mysql_query("SELECT comment_ID FROM $tablecomments WHERE comment_post_ID = $id");
	$comment_count = mysql_num_rows($comments);
	if (($comment_count == 0) && ($none != '')) {
		echo $none;
	} elseif ($comment_count == 1) {
		echo '<a href="?p=' . $id . '#comments">' . $one . '</a>';
	} else {
		$more = str_replace('%', $comment_count, $more);
		echo '<a href="?p=' . $id . '#comments">' . $more . '</a>';
	}
}

function comments_popup_script($width = 400, $height = 400) {
	global $single;
	if ($single) {
		echo '<script type="text/javascript">function comments_popup(URL, H, W) { if (W == 0) { W = 400; } window.open(URL, \'commentspopup\', \'width=\'+W+\',height=\'+H+\',scrollbars=yes\'); }</script>' . "\n";
	}
}

/* ==================================================================== */
/* Trackback / Pingback popup links                                     */
/* ==================================================================== */

function trackback_popup_link($zero = 'No Trackbacks', $one = '1 Trackback', $more = '% Trackbacks') {
	global $id, $tb_count, $tablecomments;
	$tb = mysql_query("SELECT comment_ID FROM $tablecomments WHERE comment_post_ID = $id AND comment_content LIKE '%<trackback />%'");
	$tb_count = mysql_num_rows($tb);
	if ($tb_count == 0) {
		echo $zero;
	} elseif ($tb_count == 1) {
		echo '<a href="e2trackbackpopup.php?p=' . $id . '">' . $one . '</a>';
	} else {
		$more = str_replace('%', $tb_count, $more);
		echo '<a href="e2trackbackpopup.php?p=' . $id . '">' . $more . '</a>';
	}
}

function pingback_popup_link($zero = 'No Pingbacks', $one = '1 Pingback', $more = '% Pingbacks') {
	global $id, $pb_count, $tablecomments;
	$pb = mysql_query("SELECT comment_ID FROM $tablecomments WHERE comment_post_ID = $id AND comment_content LIKE '%<pingback />%'");
	$pb_count = mysql_num_rows($pb);
	if ($pb_count == 0) {
		echo $zero;
	} elseif ($pb_count == 1) {
		echo '<a href="e2pingbackspopup.php?p=' . $id . '">' . $one . '</a>';
	} else {
		$more = str_replace('%', $pb_count, $more);
		echo '<a href="e2pingbackspopup.php?p=' . $id . '">' . $more . '</a>';
	}
}

function trackback_rdf() {
	global $id;
	echo "\n<!--\n";
	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">' . "\n";
	echo '<rdf:Description rdf:about="?p=' . $id . '" dc:identifier="?p=' . $id . '" rdf:resource="?p=' . $id . '" />' . "\n";
	echo '</rdf:RDF>' . "\n";
	echo "-->\n";
}

function trackback_response($error = 0, $error_message = '') {
	header('Content-Type: text/xml');
	if ($error) {
		echo '<?xml version="1.0" encoding="utf-8"?' . '>' . "\n";
		echo "<response>\n<error>1</error>\n";
		echo "<message>$error_message</message>\n";
		echo '</response>';
	} else {
		echo '<?xml version="1.0" encoding="utf-8"?' . '>' . "\n";
		echo "<response>\n<error>0</error>\n";
		echo '</response>';
	}
}

function trackback($tb_url, $title, $excerpt, $ID) {
	global $use_trackback;
	if (!$use_trackback) {
		return;
	}
	$url = preg_replace('/#.*/', '', $tb_url);
	$url = preg_replace('/\?.*/', '', $url);
	$data = 'title=' . urlencode($title) . '&url=' . urlencode('?p=' . $ID) . '&excerpt=' . urlencode($excerpt) . '&blog_name=' . urlencode(''); 
	if (preg_match('|^https?://|', $url)) {
		$url_parts = parse_url($url);
		$fp = @fsockopen($url_parts['host'], 80, $errno, $errstr, 5);
		if ($fp) {
			$path = (isset($url_parts['path'])) ? $url_parts['path'] : '/';
			fputs($fp, "POST $path HTTP/1.0\r\n");
			fputs($fp, "Host: " . $url_parts['host'] . "\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n\r\n");
			fputs($fp, $data);
			fclose($fp);
		}
	}
}

/* ==================================================================== */
/* Admin chrome (used by e2header/e2footer based admin pages)            */
/* ==================================================================== */

$blankline = '<p>&nbsp;</p>';
$tabletop = '<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr><td>';
$tablebottom = '</td></tr></table>';