<?php 
class WMS {
	const PF_ROS = 1;
	const R_GOOD = 0;
	const R_NULL = 1;
	const R_ERROR = 2;
	private $_db;

	public function __construct () {
		openlog('wms', LOG_ODELAY, LOG_LOCAL3);
	}

	public function getDb () {
		if (!$this->_db) {
			require_once($_SERVER['WMS_PATH'] . '/config.php');
			$this->_db = new PDO('mysql:dbname=' . $DBNAME . ';host=' . $DBHOST, $DBUSER, $DBPASS);
		}
		return $this->_db;
	}
}
