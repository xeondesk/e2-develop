<?php
/*
 * xmlrpc.inc - minimal XML-RPC client library (xmlrpcval, xmlrpcmsg, xmlrpc_client).
 * A compact reconstruction of the classic usefulinc library used by e2.
 */

class xmlrpcval {
	var $me = array();
	var $type = 'string';

	function __construct($val = -1, $type = '') {
		$this->me = array('value' => $val, 'type' => $type);
		$this->type = $type;
	}

	function serializeval($o) {
		$value = $o->me['value'];
		$type = $o->me['type'];
		if ($type == 'struct') {
			$out = '<struct>';
			if (is_array($value)) {
				foreach ($value as $key => $val) {
					$out .= '<member><name>' . htmlspecialchars($key) . '</name><value>' . $this->serializeval($val) . '</value></member>';
				}
			}
			$out .= '</struct>';
			return $out;
		} elseif ($type == 'array') {
			$out = '<array><data>';
			if (is_array($value)) {
				foreach ($value as $val) {
					$out .= '<value>' . $this->serializeval($val) . '</value>';
				}
			}
			$out .= '</data></array>';
			return $out;
		} elseif ($type == 'boolean') {
			return '<boolean>' . (($value) ? '1' : '0') . '</boolean>';
		} elseif ($type == 'int' || $type == 'i4') {
			return '<int>' . (int)$value . '</int>';
		} elseif ($type == 'double') {
			return '<double>' . $value . '</double>';
		} elseif ($type == 'base64') {
			return '<base64>' . base64_encode($value) . '</base64>';
		} else {
			return '<string>' . htmlspecialchars($value) . '</string>';
		}
	}

	function serialize() {
		return '<value>' . $this->serializeval($this) . '</value>';
	}
}

class xmlrpcmsg {
	var $method;
	var $params = array();

	function __construct($meth = '', $pars = '') {
		$this->method = $meth;
		$this->params = $pars;
	}

	function serialize() {
		$out = '<?xml version="1.0"?>' . "\n";
		$out .= "<methodCall>\n";
		$out .= '<methodName>' . $this->method . '</methodName>' . "\n";
		$out .= "<params>\n";
		foreach ($this->params as $p) {
			$out .= "<param>\n";
			if ($p instanceof xmlrpcval) {
				$out .= $p->serialize();
			} else {
				$out .= '<value><string>' . htmlspecialchars($p) . '</string></value>';
			}
			$out .= "</param>\n";
		}
		$out .= "</params>\n";
		$out .= '</methodCall>';
		return $out;
	}
}

class xmlrpcresp {
	var $value;
	var $errno = 0;
	var $errstr = '';

	function __construct($val = '', $fcode = 0, $fstr = '') {
		if ($fcode != 0) {
			$this->errno = $fcode;
			$this->errstr = $fstr;
		} else {
			$this->value = $val;
		}
	}

	function faultCode() {
		return $this->errno;
	}

	function faultString() {
		return $this->errstr;
	}
}

class xmlrpc_client {
	var $url;
	var $path;
	var $server;
	var $port = 80;

	function __construct($url) {
		$this->url = $url;
		$parts = parse_url($url);
		$this->server = (isset($parts['host'])) ? $parts['host'] : $url;
		$this->path = (isset($parts['path'])) ? $parts['path'] : '/';
		if (isset($parts['port'])) {
			$this->port = $parts['port'];
		}
	}

	function send($msg, $timeout = 5) {
		$payload = $msg->serialize();
		$fp = @fsockopen($this->server, $this->port, $errno, $errstr, $timeout);
		if (!$fp) {
			return new xmlrpcresp('', 1, 'Could not connect to ' . $this->server);
		}
		$request = "POST " . $this->path . " HTTP/1.0\r\n";
		$request .= "Host: " . $this->server . "\r\n";
		$request .= "Content-Type: text/xml\r\n";
		$request .= "Content-Length: " . strlen($payload) . "\r\n\r\n";
		$request .= $payload;
		fwrite($fp, $request);
		$response = '';
		while (!feof($fp)) {
			$response .= fgets($fp, 4096);
		}
		fclose($fp);
		if (strpos($response, "\r\n\r\n") !== false) {
			$response = substr($response, strpos($response, "\r\n\r\n") + 4);
		}
		return new xmlrpcresp($response);
	}
}

/* ------------------------------------------------------------------ */
/* Minimal XML-RPC response parser                                     */
/* ------------------------------------------------------------------ */

function xmlrpc_parse_response($xml) {
	if (preg_match('|<fault>|i', $xml)) {
		return false;
	}
	$value = '';
	if (preg_match('|<value>(.*?)</value>|is', $xml, $m)) {
		$value = trim(strip_tags($m[1]));
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
	}
	return $value;
}