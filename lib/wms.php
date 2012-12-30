<?php 
class WMS {
	const PF_ROS = 1;
	const R_GOOD = 0;
	const R_NULL = 1;
	const R_ERROR = 2;
	private $_db;

	public function __construct () {
		require_once($_SERVER['WMS_PATH'] . '/config.php');
		$this->_db = array(
			'host' => $DBHOST,
			'name' => $DBNAME,
			'user' => $DBUSER,
			'pass' => $DBPASS
		);
		openlog('wms', LOG_ODELAY, LOG_LOCAL3);
	}

	protected function _log ($priority, $msg = '') {
		if (!isset($msg{0}) && is_string($priority)) {
			$msg = $priority;
			$priority = LOG_INFO;
		}
		syslog($priority, get_class($this) . ': ' . $msg);
	}

	protected function _getDb () {
		if (!is_array($this->_db)) {
			return $this->_db;
		}
		$db = $this->_db;
		$this->_db = new PDO('mysql:dbname=' . $db['name'] . ';host=' . $db['host'], $db['user'], $db['pass']);
		return $this->_db;
	}

	public function bail ($msg, $code = '404') {
		header('HTTP/1.0 ' . $code . ' ' . $msg);
		header('Content-Length: ' . (strlen($msg)+1));
		die($msg . "\n");
	}
}
