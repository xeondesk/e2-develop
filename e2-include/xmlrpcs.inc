<?php
/*
 * xmlrpcs.inc - minimal XML-RPC server library (xmlrpc_server class).
 */

class xmlrpc_server {
	var $dmap = array();

	function __construct($dispMap = '') {
		if (is_array($dispMap)) {
			$this->dmap = $dispMap;
		}
	}

	function service($data = '') {
		if ($data == '') {
			$data = file_get_contents('php://input');
		}
		header('Content-Type: text/xml');
		if (!preg_match('|<methodName>(.*?)</methodName>|is', $data, $m)) {
			$this->faultResponse(1, 'Invalid method request');
			return;
		}
		$method = trim($m[1]);
		if (!isset($this->dmap[$method])) {
			$this->faultResponse(1, 'Method not found: ' . $method);
			return;
		}
		$params = array();
		if (preg_match_all('|<param>.*?<value>(.*?)</value>.*?</param>|is', $data, $vals)) {
			foreach ($vals[1] as $v) {
				if (preg_match('|^<string>(.*)</string>$|is', $v, $str)) {
					$params[] = html_entity_decode($str[1], ENT_QUOTES, 'ISO-8859-1');
				} elseif (preg_match('|<int>(.*)</int>|is', $v, $iv)) {
					$params[] = (int)$iv[1];
				} elseif (preg_match('|<boolean>(.*)</boolean>|is', $v, $bv)) {
					$params[] = ($bv[1] == 1);
				} else {
					$params[] = trim(strip_tags($v));
				}
			}
		}
		$func = $this->dmap[$method];
		$result = call_user_func_array($func, $params);
		echo '<?xml version="1.0"?>' . "\n";
		echo "<methodResponse>\n<params><param>\n";
		echo '<value><string>' . htmlspecialchars($result) . '</string></value>';
		echo "\n</param></params>\n</methodResponse>";
	}

	function faultResponse($code, $str) {
		echo '<?xml version="1.0"?>' . "\n";
		echo "<methodResponse>\n<fault>\n<value><struct>\n";
		echo "<member><name>faultCode</name><value><int>$code</int></value></member>\n";
		echo "<member><name>faultString</name><value><string>" . htmlspecialchars($str) . "</string></value></member>\n";
		echo "</struct></value>\n</fault>\n</methodResponse>";
	}
}