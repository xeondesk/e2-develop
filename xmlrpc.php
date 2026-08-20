<?php
/*
 * xmlrpc.php - e2's XML-RPC interface (Blogger API).
 */

require('e2config.php');
require($e2inc . '/e2template.functions.php');
require($e2inc . '/e2vars.php');
require($e2inc . '/e2functions.php');
require($e2inc . '/xmlrpc.inc');
require($e2inc . '/xmlrpcs.inc');

dbconnect();

$blogger_dmap = array(
	'blogger.getUsersBlogs' => 'xmlrpc_blogger_getusersblogs',
	'blogger.getUserInfo' => 'xmlrpc_blogger_getuserinfo',
	'blogger.getPost' => 'xmlrpc_blogger_getpost',
	'blogger.getRecentPosts' => 'xmlrpc_blogger_getrecentposts',
	'blogger.newPost' => 'xmlrpc_blogger_newpost',
	'blogger.editPost' => 'xmlrpc_blogger_editpost',
	'blogger.deletePost' => 'xmlrpc_blogger_deletepost',
	'metaWeblog.newPost' => 'xmlrpc_blogger_newpost',
);

function xmlrpc_verify_user($username, $password) {
	global $tableusers;
	$username = addslashes_gpc($username);
	$password = addslashes_gpc($password);
	$result = mysql_query("SELECT ID, user_login, user_pass, user_level FROM $tableusers WHERE user_login = '$username'");
	if (!$result) {
		return 0;
	}
	$row = mysql_fetch_object($result);
	if (!$row) {
		return 0;
	}
	if ($row->user_pass == $password) {
		return $row->ID;
	}
	return 0;
}

function xmlrpc_blogger_getusersblogs() {
	$args = func_get_args();
	global $blogname, $blogdescription, $siteurl, $blogfilename;
	$user = xmlrpc_verify_user($args[1], $args[2]);
	if (!$user) {
		return 'user not found';
	}
	return $blogname . ' | ' . $blogdescription . ' | ' . $siteurl . '/' . $blogfilename;
}

function xmlrpc_blogger_getuserinfo() {
	$args = func_get_args();
	global $tableusers;
	$user = xmlrpc_verify_user($args[1], $args[2]);
	if (!$user) {
		return 'user not found';
	}
	$result = mysql_query("SELECT * FROM $tableusers WHERE ID = $user");
	$row = mysql_fetch_object($result);
	return $row->user_firstname . ' ' . $row->user_lastname . ' | ' . $row->user_nickname . ' | ' . $row->user_email . ' | ' . $row->user_url;
}

function xmlrpc_getpost($postid) {
	global $tableposts;
	$postid = intval($postid);
	$result = mysql_query("SELECT * FROM $tableposts WHERE ID = $postid");
	$row = mysql_fetch_object($result);
	return $row;
}

function xmlrpc_blogger_getpost() {
	$args = func_get_args();
	$user = xmlrpc_verify_user($args[2], $args[3]);
	if (!$user) {
		return 'user not found';
	}
	$post = xmlrpc_getpost($args[1]);
	if (!$post) {
		return 'no such post';
	}
	return $post->ID . ' | ' . $post->post_date . ' | ' . $post->post_title . ' | ' . $post->post_content;
}

function xmlrpc_blogger_getrecentposts() {
	$args = func_get_args();
	global $tableposts;
	$user = xmlrpc_verify_user($args[2], $args[3]);
	if (!$user) {
		return 'user not found';
	}
	$num = (isset($args[4]) && intval($args[4]) > 0) ? intval($args[4]) : 10;
	$result = mysql_query("SELECT ID, post_date, post_title, post_content FROM $tableposts ORDER BY post_date DESC LIMIT $num");
	$out = array();
	while ($row = mysql_fetch_object($result)) {
		$out[] = $row->ID . ' | ' . $row->post_date . ' | ' . $row->post_title . ' | ' . $row->post_content;
	}
	return implode("\n", $out);
}

function xmlrpc_blogger_newpost() {
	$args = func_get_args();
	global $tableposts, $tablecategories;
	$user = xmlrpc_verify_user($args[2], $args[3]);
	if (!$user) {
		return 'user not found';
	}
	$content = $args[4];
	$post_title = xmlrpc_getposttitle($content);
	$post_category = xmlrpc_getpostcategory($content);
	if ($post_title == '') {
		$post_title = 'untitled';
	}
	$cat_id = 0;
	$result = mysql_query("SELECT cat_ID FROM $tablecategories ORDER BY cat_ID LIMIT 1");
	if ($result && ($row = mysql_fetch_object($result))) {
		$cat_id = $row->cat_ID;
	}
	if ($post_category != '') {
		$result = mysql_query("SELECT cat_ID FROM $tablecategories WHERE cat_name = '$post_category'");
		if ($result && ($row = mysql_fetch_object($result))) {
			$cat_id = $row->cat_ID;
		}
	}
	$post_title = addslashes($post_title);
	$content = addslashes($content);
	$now = date('Y-m-d H:i:s');
	mysql_query("INSERT INTO $tableposts (post_author, post_date, post_content, post_title, post_category) VALUES ($user, '$now', '$content', '$post_title', $cat_id)");
	return mysql_insert_id();
}

function xmlrpc_blogger_editpost() {
	$args = func_get_args();
	global $tableposts;
	$user = xmlrpc_verify_user($args[2], $args[3]);
	if (!$user) {
		return 'user not found';
	}
	$postid = intval($args[1]);
	$content = addslashes($args[4]);
	mysql_query("UPDATE $tableposts SET post_content = '$content' WHERE ID = $postid AND post_author = $user");
	if (mysql_affected_rows()) {
		return 1;
	}
	return 0;
}

function xmlrpc_blogger_deletepost() {
	$args = func_get_args();
	global $tableposts;
	$user = xmlrpc_verify_user($args[2], $args[3]);
	if (!$user) {
		return 'user not found';
	}
	$postid = intval($args[1]);
	mysql_query("DELETE FROM $tableposts WHERE ID = $postid AND post_author = $user");
	return 1;
}

$server = new xmlrpc_server($blogger_dmap);
$server->service();