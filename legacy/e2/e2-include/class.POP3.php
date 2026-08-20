<?php
/*
 * class.POP3.php - minimal POP3 client for e2mail.php (mail-to-blog).
 */

class POP3 {
	var $ERROR = '';
	var $connection = 0;
	var $messages = 0;
	var $MAILBOX = 0;

	function connect($host, $port = 110) {
		$this->connection = @fsockopen($host, $port, $errno, $errstr, 30);
		if (!$this->connection) {
			$this->ERROR = "Couldn't connect to $host:$port ($errno: $errstr)";
			return false;
		}
		$response = $this->getline();
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "Server did not respond correctly: $response";
			return false;
		}
		return true;
	}

	function login($username, $password) {
		$response = $this->sendcmd("USER $username");
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "USER command failed: $response";
			return false;
		}
		$response = $this->sendcmd("PASS $password");
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "PASS command failed: $response";
			return -1;
		}
		$response = $this->sendcmd('STAT');
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "STAT command failed: $response";
			return false;
		}
		$parts = explode(' ', $response);
		$this->messages = (isset($parts[1])) ? (int)$parts[1] : 0;
		return $this->messages;
	}

	function get($msgno) {
		$response = $this->sendcmd("RETR $msgno");
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "RETR failed for message $msgno: $response";
			return false;
		}
		$lines = array();
		while (($line = fgets($this->connection, 1024)) !== false) {
			$line = str_replace("\r\n", "\n", $line);
			if (($line == '.') || ($line == ".\n")) {
				break;
			}
			if (substr($line, 0, 1) == '.') {
				$line = substr($line, 1);
			}
			$lines[] = $line;
		}
		return $lines;
	}

	function delete($msgno) {
		$response = $this->sendcmd("DELE $msgno");
		if (substr($response, 0, 3) != '+OK') {
			$this->ERROR = "DELE failed for message $msgno: $response";
			return false;
		}
		return true;
	}

	function reset() {
		$response = $this->sendcmd('RSET');
		return (substr($response, 0, 3) == '+OK');
	}

	function quit() {
		if ($this->connection) {
			$this->sendcmd('QUIT');
			fclose($this->connection);
			$this->connection = 0;
		}
	}

	function sendcmd($cmd) {
		fputs($this->connection, $cmd . "\r\n");
		return $this->getline();
	}

	function getline() {
		$line = fgets($this->connection, 1024);
		if ($line === false) {
			return '';
		}
		return rtrim($line, "\r\n");
	}
}