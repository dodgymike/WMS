<?php

class WMS_Notify {
	public $smtpserver = 'mail.geek.sh';
	public $smtpport = 25;
	public $smtptimeout = 10;
	public $smtphelo = 'wms.ctwug.za.net';
	public $smtpsender = 'wms@ctwug.za.net';
	private $type;
	private $params;

	public function __construct ($type) {
		$this->type = $type;
		$this->params = array();
	}

	public function set ($param, $value) {
		if (is_null($value)) {
			return;
		}
		$this->params[$param] = $value;
	}

	public function send () {
		$jump = array($this, 'send_' . $this->type);
		if (!is_callable($jump)) {
			return false;
		}
		return call_user_func($jump);
	}

	private function _smtp_write ($conn, $out) {
		$written = 0;
		$len = strlen($out);
		$fwrite = fwrite($conn, $out);
		if ($fwrite === false) {
			return false;
		}
		$written += $fwrite;
		while ($written < $len) {
			usleep(2000);
			$fwrite = fwrite($conn, substr($out, $written));
			if ($fwrite === false) {
				return false;
			}
			if ($fwrite === 0) {
				return false;
			}
			$written += $fwrite;
		}
		return $written;
        }

	public function smtp ($to, $subject, $message, $headers = array()) {
		$sent = false;
		$toheader = '';
		$conn = fsockopen($this->smtpserver, $this->smtpport, $errno, $errstr, $this->smtptimeout);
		if ($conn === false) {
			return false;
		}
		if (!stream_set_timeout($conn, $this->smtptimeout)) {
			return false;
		}
		$rcodes = array(
			'connect' => '220',
			'helo' => '250',
			'mailfrom' => '250',
			'rcptto' => '250',
			'data' => '354',
			'sent' => '250'
		);
		$state = 'connect';
		while (true) {
			$in = fgets($conn, 8192);
			$rcode = $rcodes[$state];
			if (substr($in, 0, 3) != $rcode) {
				break;
			}
			switch ($state) {
			case 'connect':
				$state = 'close';
				$ret = $this->_smtp_write($conn, 'HELO ' . $this->smtphelo . "\r\n");
				if ($ret !== false) {
					$state = 'helo';
				}
				break;
			case 'helo':
				$state = 'close';
				$ret = $this->_smtp_write($conn, 'MAIL FROM:<' . $this->smtpsender . ">\r\n");
				if ($ret !== false) {
					$state = 'mailfrom';
				}
				break;
			case 'mailfrom':
				$state = 'close';
				$rcpt = array_shift($to);
				$toheader .= '<' . $rcpt . '>';
				$ret = $this->_smtp_write($conn, 'RCPT TO:<' . $rcpt . ">\r\n");
				if ($ret !== false) {
					if (sizeof($to) > 0) {
						$state = 'mailfrom';
						$toheader .= ",\r\n  ";
					} else {
						$state = 'rcptto';
					}
				}
				break;
			case 'rcptto':
				$state = 'close';
				$ret = $this->_smtp_write($conn, "DATA\r\n");
				if ($ret !== false) {
					$state = 'data';
				}
				break;
			case 'data':
				$state = 'close';
				foreach ($headers as $header) {
					if (strtolower(substr($header, 0, 5)) == 'date:') {
						continue;
					}
					if (strtolower(substr($header, 0, 11)) == 'message-id:') {
						continue;
					}
					if (strtolower(substr($header, 0, 3)) == 'to:') {
						continue;
					}
					$ret = $this->_smtp_write($conn, $header . "\r\n");
					if ($ret === false) {
						break;
					}
				}
				$ret = $this->_smtp_write($conn, 'To: ' . $toheader . "\r\n");
				if ($ret === false) {
					break;
				}
				$date = date('D, d M Y H:i:s O');
				$ret = $this->_smtp_write($conn, 'Date: ' . $date . "\r\n");
				if ($ret === false) {
					break;
				}
				$msgid = '<' . dechex(crc32($toheader.$message)) . '-' . dechex(crc32($date));
				$msgid .= '-' . rand() . '@' . $this->smtphelo . '>';
				$ret = $this->_smtp_write($conn, 'Message-ID: ' . $msgid . "\r\n");
				if ($ret === false) {
					break;
				}
				$ret = $this->_smtp_write($conn, 'Subject: ' . $subject . "\r\n");
				if ($ret === false) {
					break;
				}
				$ret = $this->_smtp_write($conn, "\r\n" . $message . "\r\n.\r\n");
				if ($ret === false) {
					break;
				}
				$state = 'sent';
				break;
			case 'sent':
				$state = 'close';
				$sent = true;
				break;
			}
			if ($state == 'close') {
				break;
			}
		}
		fclose($conn);
		return $sent;
	}

	private function send_newcontact () {
		$params = $this->params;
		if (!(isset($params['oldaddress']) || isset($params['newaddress']))) {
			return false;
		}
		if (!isset($params['device'])) {
			return false;
		}
		$msg = 'Contact address';
		if (isset($params['oldaddress'])) {
			if (isset($params['newaddress'])) {
				$to = array($params['oldaddress'], $params['newaddress']);
				$msg .= ' changed from ' . $params['oldaddress'];
				$msg .= ' to ' . $params['newaddress'] . '.';
			} else {
				$to = array($params['oldaddress']);
				$msg .= ' removed!';
			}
		} else {
			$to = array($params['newaddress']);
			$msg .= ' set to ' . $params['newaddress'] . '.';
		}
		$subj = 'contact address change for ' . $params['device'];
		if (isset($params['devicename'])) {
			$subj .= ' (' . $params['devicename'] . ')';
		}
		$headers = array('From: WMS <' . $this->smtpsender . '>', 'Precedence: bulk');
		return $this->smtp($to, $subj, $msg, $headers);
	}

	private function send_newdevice () {
		$params = $this->params;
		if (!(isset($params['address']) && isset($params['device']))) {
			return false;
		}
		$to = array($params['address']);
		$subj = 'new device added to WMS: ' . $params['device'];
		if (isset($params['devicename'])) {
			$subj .= ' (' . $params['devicename'] . ')';
		}
		$headers = array('From: WMS <' . $this->smtpsender . '>', 'Precedence: bulk');
		return $this->smtp($to, $subj, null, $headers);
	}
}
