<?php
/*
 * e2verifauth.php - checks that the current visitor is a logged-in user.
 * Self-contained: e2header.php loads this file before e2functions.php, and the
 * DB connection may not be established yet, so connect on demand.
 */

global $server, $loginsql, $passsql, $base;
global $tableusers, $HTTP_COOKIE_VARS;
global $user_login, $user_ID, $user_level, $user_email, $user_url, $user_nickname, $user_firstname, $user_lastname, $user_pass, $user_idmode;

if (empty($GLOBALS['__e2dbh'])) {
	mysql_connect($server, $loginsql, $passsql);
	mysql_select_db($base);
}

$user_login = '';
$user_ID = 0;
$user_level = 0;

if (isset($HTTP_COOKIE_VARS["cafeloguser"])) {
	$user_login = $HTTP_COOKIE_VARS["cafeloguser"];
	$r = mysql_query("SELECT * FROM $tableusers WHERE user_login = '" . addslashes($user_login) . "'");
	if ($r) {
		$row = mysql_fetch_array($r);
		if ($row) {
			$user_ID = $row[0];
			$user_pass = $row[2];
			$user_level = $row[13];
			$user_firstname = $row[3];
			$user_lastname = $row[4];
			$user_nickname = $row[5];
			$user_email = $row[7];
			$user_url = $row[8];
			$user_idmode = $row[17];
			if (!isset($HTTP_COOKIE_VARS["cafelogpass"]) || $HTTP_COOKIE_VARS["cafelogpass"] != md5($user_pass)) {
				$user_ID = 0;
				$user_level = 0;
			}
		}
	}
}

if ($user_level == 0) {
	header('Location: e2login.php');
	exit();
}