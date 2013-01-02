<?php 
require_once($_SERVER['WMS_PATH'] . '/wms.php');

class WMS_API extends WMS {
	private $_platform;
	protected $_serial;
	protected $_softid;
	protected $_next;
	protected $_logparam;

	public function __construct () {
		parent::__construct();
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
			if (isset($_REQUEST['serial']) && preg_match('/^[A-F0-9]{12}$/', $_REQUEST['serial'])) {
				$this->_serial = $_REQUEST['serial'];
			}
			if (isset($_REQUEST['softid']) && preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $_REQUEST['softid'])) {
				$this->_softid = $_REQUEST['softid'];
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

	private function _reqlog ($status) {
		$msg = 'request[' . $status . ']: ';
		$msg .= $this->_getLogIP() . '[' . $this->getPlatform();
		if ($this->_serial) {
			$msg .= '/' . $this->_serial;
		} else if ($this->_softid) {
			$msg .= '/' . $this->_softid;
		}
		$msg .= ']';
		$params = $this->_logparam;
		if (sizeof($params) > 0) {
			$msg .= ' (';
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

	private function _sanitise_updatever ($val) {
		if (is_numeric($val) && $val >= 0 && $val < 4294967294) {
			return true;
		}
		return false;
	}
	private function _sanitise_upgradever ($val) {
		return $this->_sanitise_updatever($val);
	}
	private function _sanitise_os ($val) {
		if (strlen($val) < 64) {
			return true;
		}
		return false;
	}
	private function _sanitise_model ($val) {
		if (strlen($val) < 32) {
			return true;
		}
		return false;
	}
	private function _sanitise_name ($val) {
		return $this->_sanitise_model($val);
	}
	private function _sanitise_contact ($val) {
		return $this->_sanitise_os($val);
	}
	private function _sanitise_lastip ($val) {
		if (strlen($val) < 40) {
			return true;
		}
		return false;
	}
	private function _sanitise_routerid ($val) {
		if (strlen($val) < 16) {
			return true;
		}
		return false;
	}
	private function _sanitise_ct ($val) {
		if (is_int($val) && $val >= 0 && $val <= 2) {
			return true;
		}
		return false;
	}

	protected function _addDevice ($extras = array()) {
		// Basic input checks
		foreach ($extras as $key => $val) {
			$sanitise = array($this, '_sanitise_' . $key);
			if (!is_callable($sanitise)) {
				unset($extras[$key]);
				$this->_log(LOG_ERR, __CLASS__ . '::' . __FUNCTION__ . '(): no sanitiser for key: ' . $key);
				continue;
			}
			if (!call_user_func($sanitise, $val)) {
				$this->_log(LOG_WARNING, __CLASS__ . '::' . __FUNCTION__ . '(): sanitise failure for key: ' . $key);
				unset($extras[$key]);
			}
		}
		$db = $this->_getDb();
		if ($this->_serial) {
			// Check if serial number already exists
			$st = $db->prepare('SELECT id, softid FROM device WHERE serial=:serial');
			if (!$st->execute(array(':serial' => $this->_serial))) {
				return;
			}
		} else {
			// Check if softid already exists
			$st = $db->prepare('SELECT id, softid FROM device WHERE softid=:softid');
			if (!$st->execute(array(':softid' => $this->_softid))) {
				return;
			}
		}
		// If it does, we have an update.  If not, a new insertion.
		if ($row = $st->fetch()) {
			$update = $row['id'];
			$updsoftid = false;
			if (is_null($row['softid'])) {
				if ($this->_softid) {
					$updsoftid = true;
				}
			} else {
				if ($this->_softid && ($row['softid'] != $this->_softid)) {
					$this->_log(LOG_NOTICE, 'softid change ' . $row['softid'] . '->' . $this->_softid);
				}
			}
		} else {
			$update = 0;
		}
		$st->closeCursor();
		// Build the query
		if ($update > 0) {
			// Table UPDATEs
			$sql = 'UPDATE device SET ';
			$params = array(':id' => $update);
			foreach ($extras as $key => $val) {
				$sql .= $key . '=:' . $key . ', ';
				$params[':'.$key] = $val;
			}
			if ($updsoftid) {
				$sql .= 'softid=:softid, ';
				$params[':softid'] = $this->_softid;
			}
			$sql .= 'lastseen=NOW() WHERE id=:id';
		} else {
			// Table INSERTs
			$params = array(':platform' => $this->getPlatform());
			if ($this->_serial) {
				$params[':serial'] = $this->_serial;
			}
			if ($this->_softid) {
				$params[':softid'] = $this->_softid;
			}
			foreach ($extras as $key => $val) {
				$params[':'.$key] = $val;
			}
			$sql = 'INSERT INTO device (';
			$prefix = '';
			foreach ($params as $key => $val) {
				$sql .= $prefix . substr($key, 1);
				$prefix = ', ';
			}
			$sql .= ') VALUES (';
			$prefix = '';
			foreach ($params as $key => $val) {
				$sql .= $prefix . $key;
				$prefix = ', ';
			}
			$sql .= ')';
		}
		// Execute query
		$st = $db->prepare($sql);
		if (!$st->execute($params)) {
			return;
		}
		$st->closeCursor();
	}
}
