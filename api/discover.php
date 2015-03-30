<?php
require_once($_SERVER['WMS_PATH'] . '/api.php');
require_once($_SERVER['WMS_PATH'] . '/device.php');

class WMS_Discover extends WMS_API {
	public $deviceid;
	public $serial;
	public $softid;
	public $platform;
	public $name;
	public $model;
	public $routerid;
	public $osver;
	public $bootver;
	public $cpu;
	public $cpufreq;
	public $arch;
	public $contact;
	public $ct;
	public $interfaces;

	public function __construct ($wms) {
		parent::__construct($wms);
		$pf = $this->getPlatform();
		if (!$pf) {
			$this->bail('Unsupported platform');
			return;
		}
		$this->_next = array($this, '_discover_' . $pf);
	}

	private function _discover_ros_softid ($val) {
		$old = $this->_device->softid;
		$serial = $this->_device->serial;
		if ($val != $old) {
			$this->_log(LOG_WARNING, __FUNCTION__ . '(): '
				. 'softid changed from ' . $old . ' to ' . $val . ' for serial: ' . $serial);
		}
		return $this->_device->set('softid', $val);
	}

	private function _discover_ros_rosver ($val) {
		return $this->_device->set('osver', $val);
	}

	private function _discover_ros_board ($val) {
		return $this->_device->set('model', $val);
	}

	private function _discover_ros_firmware ($val) {
		return $this->_device->set('bootver', $val);
	}

	private function _discover_ros_wif ($val) {
		// format is: name;mode;radioname;ssid;hwretries;rateselect;protocol;frequency;macprotocol;distance;ampdupriorities;nv2qosselector;nv2queuecount;tdmaperiod;queue;ospftype;ospfcost;bridgename;ip1;ip2;ipX
		$props = explode(';', $val);
		$propc = sizeof($props);
		if ($propc < 18) {
			return false;
		}
		if (isset($props[0]{64})) {
			return false;
		}
		if (isset($props[2]{32})) {
			return false;
		}
		if (isset($props[3]{32})) {
			return false;
		}
		if (!(is_numeric($props[4]) && $props[4] >= 0 && $props[4] <= 17)) {
			$props[4] = 0;
		}
		$iface = array(
			'type' => 'W',
			'name' => $props[0],
			'wi_radioname' => $props[2],
			'wi_ssid' => $props[3],
			'wi_retries' => $props[4],
		);
		$translate = array(
			'bridge' => 'ap',
			'ap-bridge' => 'ap',
			'wds-slave' => 'ap',
			'station' => 'station',
			'station-wds' => 'station',
			'station-pseudobridge' => 'station',
			'station-pseudobridge-clone' => 'station',
			'station-bridge' => 'station'
		);
		if (isset($translate[$props[1]])) {
			$iface['wi_mode'] = $translate[$props[1]];
		} else {
			$iface['wi_mode'] = 'unknown';
		}
		$translate = array('legacy'=>true, 'advanced'=>true);
		if (isset($translate[$props[5]])) {
			$iface['wi_rateselect'] = $props[5];
		}
		$translate = array(
			'2ghz-b' => '802.11b',
			'2ghz-g' => '802.11g',
			'2ghz-n' => '802.11n',
			'5ghz-a' => '802.11a',
			'5ghz-n' => '802.11n'
		);
		if (isset($translate[$props[6]])) {
			$iface['wi_protocol'] = $translate[$props[6]];
		} else {
			$iface['wi_protocol'] = 'unknown';
		}
		if (is_numeric($props[7]) && $props[7] > 2000 && $props[7] < 6000) {
			$iface['wi_frequency'] = intval($props[7]);
		} else {
			$iface['wi_frequency'] = 0;
		}
		$translate = array('802.11'=>true, 'nstreme'=>true, 'nv2'=>true);
		if (isset($translate[$props[8]])) {
			$iface['wi_macprotocol'] = $props[8];
		} else {
			$iface['wi_macprotocol'] = 'unknown';
		}
		if (isset($props[9]{0}) && is_numeric($props[9])) {
			$props[9] = intval($props[9]);
			if ($props[9] >= 0 && $props[9] <= 255) {
				$iface['wi_distance'] = $props[9];
			}
		}
		if (isset($props[10]{0})) {
			if (!isset($props[10]{16})) {
				$iface['wi_ampdupriorities'] = $props[10];
			}
		}
		$translate = array('default'=>true, 'frame-priority'=>true);
		if (isset($translate[$props[11]])) {
			$iface['wi_nv2qselector'] = $props[11];
		}
		if (isset($props[12]{0}) && is_numeric($props[12])) {
			$props[12] = intval($props[12]);
			if ($props[12] >= 2 && $props[12] <= 8) {
				$iface['wi_nv2qnum'] = $props[12];
			}
		}
		if (isset($props[13]{0}) && is_numeric($props[13])) {
			$props[13] = intval($props[13]);
			if ($props[13] >= 1 && $props[13] <= 10) {
				$iface['wi_tdmaperiod'] = $props[13];
			}
		}
		if (isset($props[14]{0})) {
			if (!isset($props[14]{16})) {
				$iface['queue'] = $props[14];
			}
		}
		$translate = array(
			'broadcast' => 'B',
			'point-to-point' => 'P'
		);
		if (isset($translate[$props[15]])) {
			$iface['ospftype'] = $translate[$props[15]];
		}
		if (isset($props[16]{0}) && is_numeric($props[16])) {
			$props[16] = intval($props[16]);
			if ($props[16] >= 1 && $props[16] <= 120000) {
				$iface['ospfcost'] = $props[16];
			}
		}
		if (isset($props[17]{0})) {
			if (!isset($props[17]{64})) {
				$iface['bridgename'] = $props[17];
			}
		}
		$iface['addresses'] = array();
		if ($propc > 18) {
			if ($propc > 24) {
				$propc = 24;
			}
			for ($i = 18; $i < $propc; $i++) {
				if (!preg_match('|^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/[0-9]{1,2}$|', $props[$i])) {
					continue;
				}
				$iface['addresses'][] = $props[$i];
			}
		}
		$this->_device->addInterface($iface);
		return true;
	}

	protected function _discover_ros () {
		$device = $this->_device;
		if (!$device->load()) {
			$this->_log(LOG_NOTICE, __FUNCTION__ . '(): adding new device');
		}
		$device->addInterface(null);
		$device->set('name', null);
		$device->set('arch', null);
		$device->set('cpu', null);
		$device->set('cpufreq', null);
		$device->set('board', null);
		$device->set('model', null);
		$device->set('bootver', null);
		$device->set('osver', null);
		$device->set('contact', null);
		$device->set('ct', null);
		foreach ($_REQUEST as $key => $val) {
			if (substr($key, 0, 3) == 'wif') {
				$jump = array($this, '_discover_ros_wif');
			} else {
				$jump = array($this, '_discover_ros_' . $key);
			}
			if (is_callable($jump)) {
				if (!call_user_func($jump, $val)) {
					$this->_log(LOG_WARNING, __FUNCTION__ . '(): discovery failure for key: ' . $key);
				}
			} else {
				if (!$device->set($key, $val)) {
					$this->_log(LOG_WARNING, __FUNCTION__ . '(): set failure for key: ' . $key);
				}
			}
		}
		$device->save();
		return false;
	}
}

$wms = new WMS_Discover(new WMS());
$wms->boot();
