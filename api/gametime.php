<?php
require_once($_SERVER['WMS_PATH'] . '/api.php');

class WMS_Gametime extends WMS_API {
	public function __construct() {
		parent::__construct();
		if ($this->_serial || $this->_softid) {
			$this->_addDevice();
		}
	}

	public function doGametime () {
		$wdaymap = array('sun','mon','tue','wed','thu','fri','sat');
		$now = localtime((time()), true);
		$wday  = $wdaymap[$now['tm_wday']];
		$hour  = $now['tm_hour'];

		if ($hour < 10) {
			$hour = '0' . $hour;
		}

		$rbgton = false;
		if (isset($_REQUEST['on']) && $_REQUEST['on'] == '1') {
			$rbgton = true;
		}

		$path = $_SERVER['WMS_PATH'] . '/gametime/' . $wday . '/' . $hour;

		if (file_exists($path)) {
			$msg = 'Gametime is ON';
			if ($rbgton) {
				$this->bail($msg);
				return;
			}
		} else {
			$msg = 'Gametime is OFF';
			if (!$rbgton) {
				$this->bail($msg);
				return;
			}
		}
		header('Content-Length: ' . (strlen($msg)+1));
		echo $msg . "\n";
	}
}

$wms = new WMS_Gametime();
$wms->doGametime();
