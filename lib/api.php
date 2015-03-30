<?php 
require_once($_SERVER['WMS_PATH'] . '/wms.php');
require_once($_SERVER['WMS_PATH'] . '/device.php');

class WMS_API {
	private $_platform;
	protected $_wms;
	protected $_device;
	protected $_next;
	protected $_logparam;

	public function __construct ($wms) {
		$this->_wms = $wms;
		header('Content-Type: text/plain');
		if (isset($_REQUEST['pf'])) {
			switch ($_REQUEST['pf']) {
			case 'ros':
				$this->_platform = WMS::PF_ROS;
				break;
			}
		} elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
			if (substr($_SERVER['HTTP_USER_AGENT'], 0, 9) == 'Mikrotik/') {
				// "Mikrotik/3.x Fetch"
				$this->_platform = WMS::PF_ROS;
			}
		}
		switch ($this->_platform) {
		case WMS::PF_ROS:
			$deviceid = array();
			if (isset($_REQUEST['serial'])) {
				$deviceid['serial'] = $_REQUEST['serial'];
			}
			if (isset($_REQUEST['softid'])) {
				$deviceid['softid'] = $_REQUEST['softid'];
			}
			if ($deviceid) {
				$this->_device = new WMS_Device($wms, $this->_platform, $deviceid);
			}
			break;
		}
	}

	public function boot () {
		$response = WMS::R_GOOD;
		while (true) {
			if (!$this->_next) {
				break;
			}
			$jump = $this->_next;
			$this->_next = null;
			if (!is_callable($jump)) {
				if (is_array($jump)) {
					$this->_log(LOG_ERR, 'Uncallable jump: ' . get_class($jump[0]) . '::' . $jump[1] . '()');
				}
				$response = WMS::R_ERROR;
				break;
			}
			if (!call_user_func($jump)) {
				$response = WMS::R_NULL;
				break;
			}
		}
		call_user_func(array($this, '_boot_' . $response));
	}

	private function _boot_0 () {
		$this->_reqlog("GOOD");
	}

	private function _boot_1 () {
		$this->_reqlog("NULL");
		$this->bail('Nothing of interest');
	}

	private function _boot_2 () {
		$this->_reqlog("ERROR");
		$this->bail('We haz a problem', 500);
	}

	protected function _log ($priority, $msg = '') {
		if (!isset($msg{0}) && is_string($priority)) {
			$msg = $priority;
			$priority = LOG_INFO;
		}
		$p = $this->getPlatform();
		$msgpre = $this->_getLogIP();
		if ($p) {
			$msgpre .= '[' . $this->getPlatform();
			if ($this->_device) {
				$msgpre .= '/' . $this->_device->deviceid;
			}
			$msgpre .= ']';
		}
		$msgpre .= ': ';
		syslog($priority, get_class($this) . ': ' . $msgpre . $msg);
	}

	private function _reqlog ($status) {
		$msg = 'request[' . $status . ']';
		$params = $this->_logparam;
		if (sizeof($params) > 0) {
			$msg .= ': (';
			$delim = '';
			foreach ($params as $key => $val) {
				$msg .= $delim . $key . '=' . $val;
				$delim = ', ';
			}
			$msg .= ')';
		}
		$this->_log(LOG_INFO, $msg);
	}

	private function _getLogIP () {
		if (!isset($_SERVER['REMOTE_ADDR'])) {
			return 'local';
		}
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}
		$xff = '';
		foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $xffip) {
			$xff .= '/' . trim($xffip);
		}
		if (!isset($xff{0})) {
			return $_SERVER['REMOTE_ADDR'];
		}
		return $_SERVER['REMOTE_ADDR'] . $xff;
	}

	public function getPlatform () {
		if (!$this->_platform) {
			return false;
		}
		$pmethod = array($this, '_getPlatform_' . $this->_platform);
		return call_user_func($pmethod);
	}

	private function _getPlatform_1 () {
		return 'ros';
	}

	public function bail ($msg, $code = '404') {
		header('HTTP/1.0 ' . $code . ' ' . $msg);
		header('Content-Length: ' . (strlen($msg)+1));
		die($msg . "\n");
	}
}

