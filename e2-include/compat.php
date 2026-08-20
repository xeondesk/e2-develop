<?php
/*
 * e2 compat.php - PHP 8.x compatibility layer.
 *
 * This file is loaded through auto_prepend_file (see docker/php.ini) so that the
 * whole historic codebase sees the same environment it expected from PHP 4:
 *
 *   - $HTTP_*_VARS superglobal aliases
 *   - mysql_*() functions backed by mysqli
 *   - ereg()/eregi()/ereg_replace()/eregi_replace() shims
 *   - get_magic_quotes_gpc()
 *   - browser-detection globals ($is_winIE, $is_gecko, ...)
 *   - $pagenow, $querycount etc.
 *
 * Nothing in this file is a "real" MySQL implementation; it is a transparent
 * adapter to the mysqli extension.
 */

if (!defined('E2_COMPAT_LOADED')) {
	define('E2_COMPAT_LOADED', 1);

	/* ------------------------------------------------------------------ */
	/* Superglobal aliases                                                 */
	/* ------------------------------------------------------------------ */
	if (!isset($HTTP_GET_VARS))    $HTTP_GET_VARS    = $_GET;
	if (!isset($HTTP_POST_VARS))   $HTTP_POST_VARS   = $_POST;
	if (!isset($HTTP_COOKIE_VARS)) $HTTP_COOKIE_VARS = $_COOKIE;
	if (!isset($HTTP_SERVER_VARS)) $HTTP_SERVER_VARS = $_SERVER;
	if (!isset($HTTP_POST_FILES))  $HTTP_POST_FILES  = $_FILES;
	if (!isset($HTTP_HOST))        $HTTP_HOST        = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'localhost';
	if (!isset($HTTP_USER_AGENT))  $HTTP_USER_AGENT  = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
	if (!isset($HTTP_REFERER))     $HTTP_REFERER     = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
	if (!isset($PHP_SELF))         $PHP_SELF         = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '/index.php';

	$pagenow = (isset($_SERVER['PHP_SELF'])) ? basename($_SERVER['PHP_SELF']) : 'index.php';

	/* ------------------------------------------------------------------ */
	/* Browser detection globals                                           */
	/* ------------------------------------------------------------------ */
	$is_gecko = (preg_match('/Gecko/', $HTTP_USER_AGENT)) ? 1 : 0;
	$is_winIE = ((preg_match('/MSIE/', $HTTP_USER_AGENT)) && (preg_match('/Win/', $HTTP_USER_AGENT))) ? 1 : 0;
	$is_macIE = ((preg_match('/MSIE/', $HTTP_USER_AGENT)) && (preg_match('/Mac/', $HTTP_USER_AGENT))) ? 1 : 0;
	$is_IE    = (($is_macIE) || ($is_winIE)) ? 1 : 0;
	$is_mac   = (preg_match('/Mac/', $HTTP_USER_AGENT)) ? 1 : 0;
	$is_lynx  = (preg_match('/Lynx/', $HTTP_USER_AGENT)) ? 1 : 0;
	$is_NS    = (preg_match('/Netscape/', $HTTP_USER_AGENT)) ? 1 : 0;
	$is_NS4   = ((preg_match('/Netscape/', $HTTP_USER_AGENT)) && (preg_match('/4\..*/', $HTTP_USER_AGENT))) ? 1 : 0;
	$is_opera = (preg_match('/Opera/', $HTTP_USER_AGENT)) ? 1 : 0;
	$is_IIS   = (isset($HTTP_SERVER_VARS['SERVER_SOFTWARE']) && strpos($HTTP_SERVER_VARS['SERVER_SOFTWARE'], 'IIS') !== false) ? 1 : 0;

	/* ------------------------------------------------------------------ */
	/* mysql_*() -> mysqli shim                                            */
	/* ------------------------------------------------------------------ */
	if (!function_exists('mysql_connect')) {
		$GLOBALS['__e2dbh'] = null;

		function mysql_connect($server = null, $username = null, $password = null) {
			$link = @mysqli_connect($server, $username, $password);
			$GLOBALS['__e2dbh'] = $link;
			return $link;
		}

		function mysql_select_db($dbname, $link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return false; }
			return mysqli_select_db($link, $dbname);
		}

		function mysql_query($query, $link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return false; }
			return mysqli_query($link, $query);
		}

		function mysql_fetch_array($result, $type = null) {
			if ($result instanceof mysqli_result) {
				if ($type === null) { $type = MYSQLI_BOTH; }
				return mysqli_fetch_array($result, $type);
			}
			return false;
		}

		function mysql_fetch_object($result) {
			if ($result instanceof mysqli_result) {
				return mysqli_fetch_object($result);
			}
			return false;
		}

		function mysql_fetch_row($result) {
			if ($result instanceof mysqli_result) {
				return mysqli_fetch_row($result);
			}
			return false;
		}

		function mysql_num_rows($result) {
			if ($result instanceof mysqli_result) {
				return mysqli_num_rows($result);
			}
			return 0;
		}

		function mysql_insert_id($link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return 0; }
			return mysqli_insert_id($link);
		}

		function mysql_error($link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return mysqli_connect_error(); }
			return mysqli_error($link);
		}

		function mysql_errno($link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return mysqli_connect_errno(); }
			return mysqli_errno($link);
		}

		function mysql_free_result($result) {
			if ($result instanceof mysqli_result) {
				mysqli_free_result($result);
			}
			return true;
		}

		function mysql_ping($link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return false; }
			return mysqli_ping($link);
		}

		function mysql_real_escape_string($string, $link = null) {
			if (!$link) { $link = $GLOBALS['__e2dbh']; }
			if (!$link) { return addslashes($string); }
			return mysqli_real_escape_string($link, $string);
		}
	}

	/* ------------------------------------------------------------------ */
	/* ereg() family shims                                                 */
	/* ------------------------------------------------------------------ */
	if (!function_exists('ereg')) {
		function ereg($pattern, $string) {
			return preg_match('/' . $pattern . '/', $string);
		}
		function eregi($pattern, $string) {
			return preg_match('/' . $pattern . '/i', $string);
		}
		function ereg_replace($pattern, $replacement, $string) {
			return preg_replace('/' . $pattern . '/', $replacement, $string);
		}
		function eregi_replace($pattern, $replacement, $string) {
			return preg_replace('/' . $pattern . '/i', $replacement, $string);
		}
	}

	/* ------------------------------------------------------------------ */
	/* get_magic_quotes_gpc() was removed in PHP 8.0                       */
	/* ------------------------------------------------------------------ */
	if (!function_exists('get_magic_quotes_gpc')) {
		function get_magic_quotes_gpc() {
			return false;
		}
	}

	/* ------------------------------------------------------------------ */
	/* misc                                                                 */
	/* ------------------------------------------------------------------ */
	if (!function_exists('is_writeable')) {
		function is_writeable($file) {
			return is_writable($file);
		}
	}
}