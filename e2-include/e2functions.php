<?php
/*
 * e2functions.php - e2's core engine functions.
 *
 * Faithful reconstruction of the missing e2-include/e2functions.php for the
 * e2 weblog engine (cafelog.com, precursor to WordPress).  All database access
 * goes through the mysql_*() shim defined in compat.php (mysqli underneath).
 */

$e2_version = '0.9';

$querycount = 0;
$time_start = 0;
$time_stop  = 0;

/* ==================================================================== */
/* Timing                                                               */
/* ==================================================================== */

function timer_start() {
	global $time_start;
	$time_start = microtime(true);
}

function timer_stop($display = 0, $precision = 3) {
	global $time_start, $time_stop;
	$time_stop = microtime(true);
	$time_total = $time_stop - $time_start;
	if ($display) {
		$results = number_format($time_total, $precision);
		echo "{$results}s";
	}
	return $time_total;
}

/* ==================================================================== */
/* Database                                                             */
/* ==================================================================== */

function dbconnect() {
	global $server, $loginsql, $passsql, $base, $dbh;
	$dbh = mysql_connect($server, $loginsql, $passsql);
	if (!$dbh) {
		die('Can\'t connect to the database<br>' . mysql_error());
	}
	if (!mysql_select_db($base)) {
		die('Can\'t select the database "<b>' . $base . '</b>".<br>' . mysql_error());
	}
	return $dbh;
}

function mysql_oops($query = '') {
	global $admin_email;
	$error = mysql_error();
	echo "<p>Oops, e2 hit an error in the database.<br />\n";
	echo "If this is your blog, you can try to contact your webmaster, or the <a href=\"mailto:$admin_email\">blog admin</a>.<br />\n";
	if ($query != '') {
		echo "The query was:<br />\n<pre>" . htmlspecialchars($query) . "</pre><br />\n";
	}
	echo "The error was:<br />\n<pre>" . htmlspecialchars($error) . "</pre></p>\n";
	die();
}

/* ==================================================================== */
/* Settings / helpers                                                   */
/* ==================================================================== */

function get_settings($setting) {
	global $cache_settings, $tablesettings;
	if (empty($cache_settings)) {
		$settings = mysql_query("SELECT * FROM $tablesettings LIMIT 1");
		while ($row = mysql_fetch_object($settings)) {
			foreach ($row as $key => $value) {
				$cache_settings[$key] = $value;
			}
		}
	}
	return (isset($cache_settings[$setting])) ? $cache_settings[$setting] : '';
}

function get_lastpostdate() {
	global $tableposts, $time_difference;
	$lastpostdate = mysql_query("SELECT post_date FROM $tableposts WHERE post_date < '" . date('Y-m-d H:i:s', (time() + ($time_difference * 3600))) . "' ORDER BY post_date DESC LIMIT 1");
	$lastpostdate = mysql_fetch_row($lastpostdate);
	return $lastpostdate[0];
}

function get_weekstartend($mysqlstring, $start_of_week) {
	$my = (int)substr($mysqlstring, 0, 4);
	$mm = (int)substr($mysqlstring, 4, 2);
	$md = (int)substr($mysqlstring, 6, 2);
	$day = mktime(0, 0, 0, $mm, $md, $my);
	$weekday = date('w', $day);
	$i = 86400;
	while ($weekday != $start_of_week) {
		$day = $day - 86400;
		$weekday = date('w', $day);
	}
	$week['start'] = $day;
	$week['end'] = $day + 604799;
	return $week;
}

function mysql2date($dateformatstring, $mysqlstring) {
	$m = $mysqlstring;
	if (empty($m)) {
		return false;
	}
	$m = str_replace('\\\\', '', $m);
	return date($dateformatstring, strtotime($m));
}

function date_i18n($dateformatstring, $unixtimestamp) {
	global $month, $weekday;
	$i = $unixtimestamp;
	$j = date($dateformatstring, $i);
	if (is_array($month)) {
		foreach ($month as $key => $value) {
			$j = str_replace(date('F', mktime(0, 0, 0, $key, 1, 2001)), $value, $j);
		}
	}
	if (is_array($weekday)) {
		foreach ($weekday as $key => $value) {
			$j = str_replace(date('l', mktime(0, 0, 0, 1, $key + 1, 2001)), $value, $j);
		}
	}
	return $j;
}

function zeroise($number, $threshold) {
	$number = (int)$number;
	$number = '' . $number;
	$length = (int)$threshold;
	if (strlen($number) < $length) {
		$number = str_repeat('0', $length - strlen($number)) . $number;
	}
	return $number;
}

function addslashes_gpc($string) {
	return addslashes($string);
}

function is_email($email) {
	return preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+$/', $email);
}

function antispambot($email) {
	$email = str_replace('@', '&#64;', $email);
	$email = str_replace('.', '&#46;', $email);
	return $email;
}

function redirect($location) {
	header('Location: ' . $location);
	exit();
}

function gzip_compression() {
	if (extension_loaded('zlib') && !ob_get_status()) {
		ob_start('ob_gzhandler');
	}
}

/* ==================================================================== */
/* Post data                                                            */
/* ==================================================================== */

function get_postdata($postid) {
	global $postdata, $tableposts;
	$postid = intval($postid);
	$result = mysql_query("SELECT * FROM $tableposts WHERE ID = '$postid'");
	if ($result) {
		$row = mysql_fetch_array($result);
		$postdata["ID"] = $row[0];
		$postdata["Author_ID"] = $row[1];
		$postdata["Date"] = $row[2];
		$postdata["Content"] = $row[3];
		$postdata["Title"] = $row[4];
		$postdata["Category"] = $row[5];
		return $postdata;
	} else {
		return false;
	}
}

function get_commentdata($comment_ID, $no_links = 0) {
	global $commentdata, $tablecomments;
	$comment_ID = intval($comment_ID);
	$result = mysql_query("SELECT * FROM $tablecomments WHERE comment_ID = '$comment_ID'");
	if ($result) {
		$row = mysql_fetch_array($result);
		$commentdata["comment_ID"] = $row[0];
		$commentdata["comment_post_ID"] = $row[1];
		$commentdata["comment_author"] = $row[2];
		$commentdata["comment_author_email"] = $row[3];
		$commentdata["comment_author_url"] = $row[4];
		$commentdata["comment_author_IP"] = $row[5];
		$commentdata["comment_date"] = $row[6];
		$commentdata["comment_content"] = $row[7];
		$commentdata["comment_karma"] = $row[8];
		return $commentdata;
	} else {
		return false;
	}
}

/* ==================================================================== */
/* Users                                                                */
/* ==================================================================== */

function get_userdata($userid) {
	global $userdata, $tableusers;
	$userid = intval($userid);
	$result = mysql_query("SELECT * FROM $tableusers WHERE ID = '$userid'");
	if ($result) {
		$userdata = mysql_fetch_array($result);
		return $userdata;
	} else {
		return false;
	}
}

function get_userdata2($userid) {
	return get_userdata($userid);
}

function get_userdatabylogin($user_login) {
	global $userdata, $tableusers;
	$user_login = addslashes_gpc($user_login);
	$result = mysql_query("SELECT * FROM $tableusers WHERE user_login = '$user_login'");
	if ($result) {
		$userdata = mysql_fetch_array($result);
		return $userdata;
	} else {
		return false;
	}
}

function get_usernumposts($userid) {
	global $tableposts;
	$userid = intval($userid);
	$result = mysql_query("SELECT COUNT(*) FROM $tableposts WHERE post_author = '$userid'");
	$count = mysql_fetch_row($result);
	return $count[0];
}

function get_currentuserinfo() {
	global $user_login, $userdata, $user_level, $user_ID, $user_email, $user_url, $user_pass, $user_nickname, $user_firstname, $user_lastname, $user_idmode, $HTTP_COOKIE_VARS;
	if (isset($HTTP_COOKIE_VARS["cafeloguser"])) {
		$user_login = $HTTP_COOKIE_VARS["cafeloguser"];
		$userdata = get_userdatabylogin($user_login);
		if ($userdata) {
			$user_level = $userdata[13];
			$user_ID = $userdata[0];
			$user_firstname = $userdata[3];
			$user_lastname = $userdata[4];
			$user_nickname = $userdata[5];
			$user_email = $userdata[7];
			$user_url = $userdata[8];
			$user_pass = $userdata[2];
			$user_idmode = $userdata[17];
		} else {
			$user_level = 0;
			$user_ID = 0;
		}
	} else {
		$user_level = 0;
		$user_ID = 0;
		$user_login = '';
		$user_email = '';
		$user_url = '';
		$user_nickname = '';
		$user_firstname = '';
		$user_lastname = '';
		$user_idmode = '';
	}
}

/* ==================================================================== */
/* Categories                                                           */
/* ==================================================================== */

function get_catname($cat_ID) {
	global $tablecategories;
	$cat_ID = intval($cat_ID);
	$cat_name = mysql_query("SELECT cat_name FROM $tablecategories WHERE cat_ID = '$cat_ID'");
	$cat_name = mysql_fetch_row($cat_name);
	return $cat_name[0];
}

function get_category_link($cat_ID) {
	global $siteurl, $blogfilename, $querystring_start, $querystring_equal;
	$cat_ID = intval($cat_ID);
	return $siteurl . '/' . $blogfilename . $querystring_start . 'cat' . $querystring_equal . $cat_ID;
}

/* ==================================================================== */
/* Text formatting                                                      */
/* ==================================================================== */

function balanceTags($text, $force = 0) {
	global $use_balanceTags;
	if (!$force && !isset($use_balanceTags)) {
		return $text;
	}
	if (!$force && !$use_balanceTags) {
		return $text;
	}
	$tagstack = array();
	$stacksize = 0;
	$tagqueue = '';
	$newtext = '';
	$single_tags = array('br', 'hr', 'img', 'input', 'link', 'meta', 'area', 'base', 'param');
	$nested_tags = array('blockquote', 'div', 'span', 'table', 'tr', 'td', 'b', 'strong', 'i', 'em', 'u', 'strike', 'font');

	$commentstarts = array('<!--', '<?', '<%');
	$commentends = array('-->', '?>', '%>');

	$comments = preg_split('#<!--|-->#U', $text);
	$num_comments = count($comments);
	$text = '';
	for ($i = 0; $i < $num_comments; $i++) {
		$text .= ($i % 2) ? '<!--' . $comments[$i] . '-->' : $comments[$i];
	}

	$split = preg_split('/(<[^>]*>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	for ($i = 0; $i < count($split); $i++) {
		if (substr($split[$i], 0, 1) == '<') {
			$offset = 1;
			if (substr($split[$i], 1, 1) == '/') {
				$offset = 2;
			}
			$tag = strtolower(substr($split[$i], $offset, strcspn($split[$i], '> ', 1)));
			if (in_array($tag, $single_tags)) {
				$tagqueue .= $split[$i];
			} elseif (in_array($tag, $nested_tags)) {
				if ($offset == 1) {
					$tagqueue .= $split[$i];
					array_push($tagstack, $tag);
					$stacksize++;
				} else {
					if ($stacksize > 0) {
						if (($tagstack[$stacksize - 1] == $tag) && ($stacksize > 1)) {
							$tagqueue .= '</' . array_pop($tagstack) . '>';
							$stacksize--;
							$newtext .= $tagqueue;
							$tagqueue = '';
						}
					}
				}
			} else {
				$tagqueue .= $split[$i];
			}
		} else {
			$newtext .= $tagqueue . $split[$i];
			$tagqueue = '';
		}
	}

	while ($tag = array_pop($tagstack)) {
		$tagqueue .= '</' . $tag . '>';
	}
	$newtext .= $tagqueue;
	return $newtext;
}

function make_clickable($text) {
	$text = eregi_replace('([a-zA-Z]+)://([^\s<]*[^\s<\.])', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $text);
	$text = eregi_replace('([a-zA-Z]+)://([^\s<]*[^\s<\.])', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $text);
	$text = eregi_replace('(^|[\n ])([a-z0-9\-]+\.)+([a-z]{2,4})([^ \n]*[^ \n\.])', '\\1<a href="http://\\2.\\3\\4" target="_blank">\\2.\\3\\4</a>', $text);
	return $text;
}

function convert_chars($content) {
	$content = str_replace('\'', '&#8217;', $content);
	$content = str_replace('"', '&#8220;', $content);
	$content = str_replace('--', '&#8211;', $content);
	$content = str_replace('...', '&#8230;', $content);
	$content = str_replace('(c)', '&#169;', $content);
	$content = str_replace('(r)', '&#174;', $content);
	$content = str_replace('(tm)', '&#8482;', $content);
	return $content;
}

function autobrize($text) {
	return preg_replace('|\r?\n|', '<br />', $text);
}

function gm2autobr($text) {
	$text = preg_replace('/\*\*(.+?)\*\*/', '<b>\\1</b>', $text);
	$text = preg_replace('/\*(.+?)\*/', '<i>\\1</i>', $text);
	$text = preg_replace('/\\\\(.+?)\\\\/', '<u>\\1</u>', $text);
	return $text;
}

function format_to_post($content) {
	global $use_bbcode, $use_gmcode, $use_htmltrans;
	if ($use_bbcode) {
		$content = preg_replace('/\[b\](.*)\[\/b\]/s', '<b>\\1</b>', $content);
		$content = preg_replace('/\[i\](.*)\[\/i\]/s', '<i>\\1</i>', $content);
		$content = preg_replace('/\[u\](.*)\[\/u\]/s', '<u>\\1</u>', $content);
		$content = preg_replace('/\[url="(.*)"\](.*)\[\/url\]/s', '<a href="\\1">\\2</a>', $content);
	}
	if ($use_gmcode) {
		$content = gm2autobr($content);
	}
	if ($use_htmltrans) {
		$content = preg_replace('/<(b|i|u|em|strong|strike)>/i', '[\\1]', $content);
		$content = preg_replace('/<\/(b|i|u|em|strong|strike)>/i', '[/\\1]', $content);
		$content = htmlentities($content, ENT_COMPAT, 'ISO-8859-1');
		$content = str_replace(array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[em]', '[/em]', '[strong]', '[/strong]', '[strike]', '[/strike]'),
			array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<em>', '</em>', '<strong>', '</strong>', '<strike>', '</strike>'), $content);
	}
	return $content;
}

function format_to_edit($content) {
	global $use_htmltrans;
	if ($use_htmltrans) {
		$content = str_replace(array('&lt;', '&gt;', '&amp;'), array('<', '>', '&'), $content);
	}
	return $content;
}

function template_simplify($text) {
	return $text;
}

/* ==================================================================== */
/* XMLRPC helper (Blogger API bits used by e2mail.php)                  */
/* ==================================================================== */

function xmlrpc_getposttitle($content) {
	global $post_default_title;
	if (preg_match('/<title>(.*?)<\/title>/is', $content, $matchtitle)) {
		$post_title = $matchtitle[1];
		$post_title = str_replace('<![CDATA[', '', $post_title);
		$post_title = str_replace(']]>', '', $post_title);
		return $post_title;
	}
	return '';
}

function xmlrpc_getpostcategory($content) {
	if (preg_match('/<category>(.*?)<\/category>/is', $content, $matchcat)) {
		$post_category = $matchcat[1];
		$post_category = str_replace('<![CDATA[', '', $post_category);
		$post_category = str_replace(']]>', '', $post_category);
		return $post_category;
	}
	return '';
}

/* ==================================================================== */
/* RSS / pings                                                          */
/* ==================================================================== */

function rss_update($blog_ID) {
	global $tableposts;
	$q = "SELECT ID FROM $tableposts ORDER BY post_date DESC LIMIT 1";
	$r = mysql_query($q);
	$row = mysql_fetch_object($r);
	$lastpost = $row->ID;
	@file_put_contents('lastpost.id', $lastpost);
}

function pingWeblogs($blog_ID) {
	global $use_weblogsping, $siteurl, $blogfilename;
	if ($use_weblogsping) {
		$data = 'name=' . $siteurl . '&url=' . $siteurl . '/' . $blogfilename . '&changesURL=';
		$fp = @fsockopen('www.weblogs.com', 80, $errno, $errstr, 3);
		if ($fp) {
			fputs($fp, "POST /ping.exe HTTP/1.0\r\n");
			fputs($fp, "Host: www.weblogs.com\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n\r\n");
			fputs($fp, $data);
			fclose($fp);
		}
	}
}

function pingCafelog($cafelogID, $post_title, $post_ID) {
	global $use_cafelogping, $pathserver;
	if ($use_cafelogping && $cafelogID) {
		$data = 'id=' . $cafelogID . '&title=' . urlencode($post_title) . '&url=' . urlencode($pathserver . '/e2rss2.php') . '&postid=' . $post_ID;
		$fp = @fsockopen('www.cafelog.com', 80, $errno, $errstr, 3);
		if ($fp) {
			fputs($fp, "POST /ping.php HTTP/1.0\r\n");
			fputs($fp, "Host: www.cafelog.com\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n\r\n");
			fputs($fp, $data);
			fclose($fp);
		}
	}
}

function pingBlogs($blog_ID) {
	global $use_blodotgsping, $blodotgsping_url;
	if ($use_blodotgsping) {
		$data = 'url=' . urlencode($blodotgsping_url);
		$fp = @fsockopen('blo.gs', 80, $errno, $errstr, 3);
		if ($fp) {
			fputs($fp, "POST /ping HTTP/1.0\r\n");
			fputs($fp, "Host: blo.gs\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n\r\n");
			fputs($fp, $data);
			fclose($fp);
		}
	}
}

function pingback($content, $post_ID) {
	global $use_pingback, $siteurl, $blogfilename;
	if ($use_pingback && preg_match_all('|<a href="([^"]+)"|', $content, $match)) {
		require_once(dirname(__FILE__) . '/xmlrpc.inc');
		foreach ($match[1] as $url) {
			if (preg_match('|^https?://|', $url)) {
				$source = $siteurl . '/' . $blogfilename . '?p=' . $post_ID;
				$client = new xmlrpc_client($url);
				$msg = new xmlrpcmsg('pingback.ping', array(
					new xmlrpcval($source, 'string'),
					new xmlrpcval($url, 'string')
				));
				@$client->send($msg, 3);
			}
		}
	}
}