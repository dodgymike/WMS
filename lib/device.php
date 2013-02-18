<?php
class WMS_Device {
	private $_wms;
	private $_savedDevice;
	private $_idfield;
	private $_deviceColumns = array(
		'softid','name','model','routerid',
		'osver','bootver', 'cpu','cpufreq',
		'arch','contact','ct','lastip',
		'updatever');
	private $_interfaceColumns = array(
		'type','name','wi_mode','wi_ssid',
		'wi_radioname','wi_frequency','wi_protocol',
		'wi_macprotocol','wi_distance','wi_retries',
		'wi_rateselect','wi_ampdupriorities','wi_nv2qnum',
		'wi_nv2qselector','wi_tdmaperiod','queue',
		'ospftype','ospfcost','bridgename');
	private $_platform;
	public $deviceid;
	public $serial;
	public $softid;
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
	public $updatever;
	public $lastip;
	public $interfaces;

	public function __construct ($wms, $platform, $deviceid) {
		$this->_wms = $wms;
		switch ($platform) {
	 	case WMS::PF_ROS:
			if (isset($deviceid['softid']) && preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $deviceid['softid'])) {
				$this->deviceid = $this->softid = $deviceid['softid'];
			}
			if (isset($deviceid['serial']) && preg_match('/^[A-F0-9]{12}$/', $deviceid['serial'])) {
				$this->deviceid = $this->serial = $deviceid['serial'];
				$this->_idfield = 'serial';
			} else {
				if (!$this->softid) {
					throw new Exception('valid serial or softid required for ROS platform');
				}
				$this->_idfield = 'softid';
			}
			$this->_platform = 'ros';
			break;
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$this->lastip = $_SERVER['REMOTE_ADDR'];
		}
	}

	private function _load_interfaces () {
		$db = $this->_wms->getDb();
		$sql = 'SELECT id';
		foreach ($this->_interfaceColumns as $col) {
			$sql .= ', ' . $col;
		}
		$sql .= ' FROM interface WHERE device_id=:deviceid';
		$st = $db->prepare($sql);
		$sql = 'SELECT INET_NTOA(address) AS address, INET_NTOA(netmask) AS netmask';
		$sql .= ' FROM address WHERE interface_id=:interfaceid';
		$ast = $db->prepare($sql);
		if (!$st->execute(array(':deviceid' => $this->_savedDevice['id']))) {
			$idfield = $this->_idfield;
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
				. 'DB error accessing interfaces for: ' . $this->_platform . '/' . $this->$idfield);
			return false;
		}
		$ifaces = array();
		while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$iface = array();
			foreach ($this->_interfaceColumns as $col) {
				$iface[$col] = $row[$col];
			}
			$iface['id'] = $row['id'];
			if (!$ast->execute(array(':interfaceid' => $row['id']))) {
				$idfield = $this->_idfield;
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error accessing addresses for: ' . $this->_platform . '/' . $this->$idfield);
				return false;
			}
			$iface['addresses'] = array();
			while ($arow = $ast->fetch(PDO::FETCH_ASSOC)) {
				$iface['addresses'][] = $arow['address'] . '/' . $this->_netmask2pxlen($arow['netmask']);
			}
			$ast->closeCursor();
			$ifaces[] = $iface;
		}
		$st->closeCursor();
		$this->_savedDevice['interfaces'] = $ifaces;
		return true;
	}

	private function _load_device () {
		$db = $this->_wms->getDb();
		$idfield = $this->_idfield;
		$sql = 'SELECT id';
		foreach ($this->_deviceColumns as $col) {
			$sql .= ', ' . $col;
		}
		$sql .= ' FROM device WHERE ' . $idfield . '=:' . $idfield;
		$params = array(':'.$idfield => $this->$idfield);
		$st = $db->prepare($sql);
		if (!$st->execute($params)) {
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
				. 'DB error accessing device: ' . $this->_platform . '/' . $this->$idfield);
			return false;
		}
		if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->_savedDevice = $row;
		}
		$st->closeCursor();
	}

	private function _save_add_addresses ($ifaceid, $addresses) {
		$db = $this->_wms->getDb();
		$param = array(':deviceid' => $this->_savedDevice['id'], ':interfaceid' => $ifaceid);
		$sql = 'INSERT INTO address (device_id, interface_id, address, netmask)';
		$sql .= ' VALUES (:deviceid, :interfaceid, INET_ATON(:address), INET_ATON(:netmask))';
		$st = $db->prepare($sql);
		foreach ($addresses as $cidraddr) {
			list($param[':address'], $pxlen) = explode('/', $cidraddr);
			$param[':netmask'] = $this->_pxlen2netmask($pxlen);
			if (!$st->execute($param)) {
				$idfield = $this->_idfield;
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error adding addresses for: ' . $this->_platform . '/' . $this->$idfield);
				return false;
			}
			$st->closeCursor();
		}
		return true;
	}

	private function _save_add_interfaces ($ikeys = array()) {
		$db = $this->_wms->getDb();
		if (sizeof($ikeys) == 0) {
			$ic = sizeof($this->interfaces);
			for ($i = 0; $i < $ic; $i++) {
				$ikeys[] = $i;
			}
		}
		foreach ($ikeys as $ikey) {
			$iface = $this->interfaces[$ikey];
			$sqlcol = 'device_id';
			$sqlval = ':deviceid';
			$param = array(':deviceid' => $this->_savedDevice['id']);
			foreach ($this->_interfaceColumns as $key) {
				if (isset($iface[$key])) {
					$sqlcol .= ', ' . $key;
					$sqlval .= ', :' . $key;
					$param[':'.$key] = $iface[$key];
				}
			}
			$sql = 'INSERT INTO interface (' . $sqlcol . ') VALUES (' . $sqlval . ')';
			$st = $db->prepare($sql);
			if (!$st->execute($param)) {
				$idfield = $this->_idfield;
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error adding interface for: ' . $this->_platform . '/' . $this->$idfield);
				return false;
			}
			$st->closeCursor();
			$iface['id'] = $db->lastInsertId();
			if (!(is_numeric($iface['id']) && $iface['id'] >= 1)) {
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error getting interface insertion ID');
				return false;
			}
			if (!isset($iface['addresses'])) {
				$iface['addresses'] = array();
			}
			if (sizeof($iface['addresses']) > 0) {
				if (!$this->_save_add_addresses($iface['id'], $iface['addresses'])) {
					return false;
				}
			}
			if (!isset($this->_savedDevice['interfaces'])) {
				$this->_savedDevice['interfaces'] = array();
			}
			$this->_savedDevice['interfaces'][] = $iface;
		}
		return true;
	}

	private function _save_add_device () {
		$db = $this->_wms->getDb();
		$savedDevice = array();
		$sqlcol = 'platform';
		$sqlval = ':platform';
		$param = array(':platform' => $this->_platform);
		if ($this->serial) {
			$sqlcol .= ', serial';
			$sqlval .= ', :serial';
			$param[':serial'] = $this->serial;
		}
		foreach ($this->_deviceColumns as $col) {
			if ($this->$col) {
				$sqlcol .= ', ' . $col;
				$sqlval .= ', :' . $col;
				$param[':'.$col] = $this->$col;
			}
			$savedDevice[$col] = $this->$col;
		}
		$sql = 'INSERT INTO device (' . $sqlcol . ') VALUES (' . $sqlval . ')';
		$st = $db->prepare($sql);
		if (!$st->execute($param)) {
			$idfield = $this->_idfield;
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
				. 'DB error adding device: ' . $this->_platform . '/' . $this->$idfield);
			return false;
		}
		$savedDevice['id'] = $db->lastInsertId();
		if (!(is_numeric($savedDevice['id']) && $savedDevice['id'] >= 1)) {
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
				. 'DB error getting device insertion ID');
			return false;
		}
		$this->_savedDevice = $savedDevice;
		return $this->_save_add_interfaces();
	}

	public function save () {
		$db = $this->_wms->getDb();
		$idfield = $this->_idfield;
		if (is_null($this->_savedDevice)) {
			$this->_load_device();
		}
		if (!$this->_savedDevice) {
			$this->_save_add_device();
			return true;
		}
		$devsql = '';
		$devpar = array();
		$savedDevice = $this->_savedDevice;
		foreach ($this->_deviceColumns as $col) {
			if ($this->$col != $savedDevice[$col]) {
				$devsql .= ', ' . $col . '=:' . $col;
				$devpar[':'.$col] = $this->$col;
				$savedDevice[$col] = $this->$col;
			}
		}
		$sql = 'UPDATE device SET lastseen=NOW()' . $devsql;
		$sql .= ' WHERE id=:deviceid';
		$devpar[':deviceid'] = $savedDevice['id'];
		$st = $db->prepare($sql);
		if (!$st->execute($devpar)) {
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
				. 'DB error updating device for: ' . $this->_platform . '/' . $this->$idfield);
			return false;
		}
		$this->_savedDevice = $savedDevice;
		if (!$this->interfaces) {
			return true;
		}
		if (!isset($savedDevice['interfaces'])) {
			if (!$this->_load_interfaces()) {
				return false;
			}
			$savedDevice['interfaces'] = $this->_savedDevice['interfaces'];
		}
		$dbifaces = array();
		foreach ($savedDevice['interfaces'] as $iface) {
			$ifstr = '';
			$sep = '';
			foreach ($this->_interfaceColumns as $col) {
				$ifstr .= $sep . $iface[$col];
				$sep = ';';
			}
			foreach ($iface['addresses'] as $address) {
				$ifstr .= $sep . $address;
			}
			$dbifaces[$iface['id']] = crc32($ifstr);
		}
		$ifaces = array();
		foreach ($this->interfaces as $ifkey => $iface) {
			$ifstr = '';
			$sep = '';
			foreach ($this->_interfaceColumns as $col) {
				if (isset($iface[$col])) {
					$ifstr .= $sep . $iface[$col];
				} else {
					$ifstr .= $sep;
				}
				$sep = ';';
			}
			foreach ($iface['addresses'] as $address) {
				$ifstr .= $sep . $address;
			}
			$ifaces[$ifkey] = crc32($ifstr);
		}
		$changes = array_diff($dbifaces, $ifaces);
		$sql = 'DELETE FROM interface WHERE id=:interfaceid';
		$st = $db->prepare($sql);
		$sql = 'DELETE FROM address WHERE interface_id=:interfaceid';
		$ast = $db->prepare($sql);
		foreach ($changes as $ifaceid => $val) {
			$param = array(':interfaceid' => $ifaceid);
			if (!$ast->execute($param)) {
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error deleting addresses for: ' . $this->_platform . '/' . $this->$idfield);
				return false;
			}
			$ast->closeCursor();
			if (!$st->execute($param)) {
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '(): '
					. 'DB error deleting interface for: ' . $this->_platform . '/' . $this->$idfield);
				return false;
			}
			$st->closeCursor();
		}
		$changes = array_diff($ifaces, $dbifaces);
		if (sizeof($changes) > 0) {
			$keys = array();
			foreach ($changes as $key => $val) {
				$keys[] = $key;
			}
			if (!$this->_save_add_interfaces($keys)) {
				return false;
			}
		}
		return true;
	}

	public function load () {
		if (is_null($this->_savedDevice)) {
			$this->_load_device();
		}
		if (!$this->_savedDevice) {
			return false;
		}
		foreach ($this->_deviceColumns as $col) {
			$this->$col = $this->_savedDevice[$col];
		}
		return true;
	}

	private function _set_serial_ros ($val) {
		return true;
	}
	private function _set_softid_ros ($val) {
		return true;
	}
	private function _set_name_ros ($val) {
		if (isset($val{32})) {
			return false;
		}
		$this->name = $val;
		return true;
	}
	private function _set_model_ros ($val) {
		if (isset($val{16})) {
			return false;
		}
		$this->model = $val;
		return true;
	}
	private function _set_routerid_ros ($val) {
		if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $val)) {
			$this->routerid = $val;
			return true;
		}
		return false;
	}
	private function _set_osver_ros ($val) {
		if (preg_match('/^[4567]\.[0-9]{1,3}.{0,8}$/', $val)) {
			$this->osver = $val;
			return true;
		}
		return false;
	}
	private function _set_bootver_ros ($val) {
		if (preg_match('/^[1-5]\.[0-9]{1,3}.{0,8}$/', $val)) {
			$this->bootver = $val;
			return true;
		}
		return false;
	}
	private function _set_cpu_ros ($val) {
		if (isset($val{32})) {
			return false;
		}
		$this->cpu = $val;
		return true;
	}
	private function _set_cpufreq_ros ($val) {
		if (!is_numeric($val)) {
			return false;
		}
		$val = intval($val);
		if ($val > 100 && $val < 10000) {
			$this->cpufreq = $val;
			return true;
		}
		return false;
	}
	private function _set_arch_ros ($val) {
		if (isset($val{16})) {
			return false;
		}
		$this->arch = $val;
		return true;
	}
	private function _set_contact_ros ($val) {
		if (preg_match('/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i', $val)) {
			$this->contact = $val;
			return true;
		}
		return false;
	}
	private function _set_updatever_ros ($val) {
		if (!is_numeric($val)) {
			return false;
		}
		$val = intval($val);
		if ($val > 0 && $val < 4294967294) {
			$this->updatever = $val;
			return true;
		}
		return false;
	}
	private function _set_ct_ros ($val) {
		if (!is_numeric($val)) {
			return false;
		}
		$val = intval($val);
		if ($val >= 0 && $val <= 2) {
			$this->ct = $val;
			return true;
		}
		return false;
	}

	public function set ($prop, $val) {
		$jump = array($this, '_set_' . $prop . '_' . $this->_platform);
		if (!is_callable($jump)) {
			return false;
		}
		if (!call_user_func($jump, $val)) {
			return false;
		}
		return true;
	}

	public function addInterface ($interface) {
		if (!$this->interfaces) {
			$this->interfaces = array();
		}
		$this->interfaces[] = $interface;
	}

	private function _pxlen2netmask ($pxlen) {
		// Convert prefix length into a netmask
		$netmask = '';
		$sep = '';
		$octets = 0;
		while ($pxlen >= 8) {
			$netmask .= $sep . '255';
			$sep = '.';
			$pxlen -= 8;
			$octets++;
		}
		if ($pxlen >= 1) {
			$netmask .= $sep . (((255<<8)>>$pxlen)&255);
			$sep = '.';
			$octets++;
		}
		while ($octets < 4) {
			$netmask .= $sep . '0';
			$sep = '.';
			$octets++;
		}
		return $netmask;
	}

	private function _netmask2pxlen ($netmask) {
		// convert netmask into prefix length
		$octets = explode('.', $netmask);
		$pxlen = 0;
		foreach ($octets as $octet) {
			$octet = intval($octet);
			if ($octet < 255) {
				for ($i = 0; $i <= 7; $i++) {
					if ((($octet<<$i)&255) == 0) {
						$pxlen += $i;
						break;
					}
				}
				break;
			}
			$pxlen += 8;
		}
		return $pxlen;
	}

	private function _freqCanonicalise ($freq) {
		// Might be useful in future
		$freql = strlen($freq);
		for ($i = 0; $i < $freql; $i++) {
			if ($freq{$i} == '.') {
				continue;
			}
			if (!is_numeric($freq{$i})) {
				break;
			}
		}
		$unit = strtolower(trim(substr($freq, $i)));
		$freq2 = (float) substr($freq, 0, $i);
		if ($freq2 <= 0) {
			// Not sure what... return original
			return $freq;
		} else {
			switch ($unit) {
			case 'mhz':
				break;
			case 'ghz':
				$freq2 = intval($freq2 * 1000);
				break;
			case 'khz':
				$freq2 = intval($freq2 / 1000);
				break;
			}
			return $freq2;
		}
	}
}
