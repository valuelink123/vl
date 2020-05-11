<?php
	
	class Tor {

		private $tor_ip, $control_port, $auth_code;

		function __construct($ip, $port, $auth) {
			$this->tor_ip = $ip;
			$this->control_port = $port;
			$this->auth_code = $auth;
		}

		/**
		 * Switch TOR to a new identity.
		 **/
		function tor_new_identity($t_ip = '', $c_port = '', $a_code = '') {

			if ($t_ip == "") $t_ip = $this->tor_ip;
			if ($c_port == "") $c_port = $this->control_port;
			if ($a_code == "") $a_code = $this->auth_code;

		    $fp = fsockopen($t_ip, $c_port, $errno, $errstr, 30);
		    if (!$fp) return false; //can't connect to the control port
		     
		    $b = fputs($fp, "AUTHENTICATE \"$a_code\"\r\n");
		    $response = fread($fp, 1024);

		    echo "r:".$response.",";

		    list($code, $text) = explode(' ', $response, 2);

		    if ($code != '250') return false; //authentication failed
		     
		    //send the request to for new identity
		    fputs($fp, "signal NEWNYM\r\n");
		    $response = fread($fp, 1024);
		    list($code, $text) = explode(' ', $response, 2);
		    if ($code != '250') return false; //signal failed
		     
		    fclose($fp);
		    return true;
		}
	}
?>
