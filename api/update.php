<?php
require_once($_SERVER['WMS_PATH'] . '/wms.php');

class WMS_Update extends WMS {
	private $_version;

	public function __construct () {
		parent::__construct();
		$this->_logmodule = 'update';
		$this->_next = array($this, '_update_' . $this->getPlatform());
	}

	private function _addDevice ($extras) {
		$db = $this->_dbConnect();
		$st = $db->prepare('SELECT id FROM device WHERE serial=:serial');
		if (!$st->execute(array(':serial' => $this->_serial))) {
			return;
		}
		if ($row = $st->fetch()) {
			$update = $row['id'];
		} else {
			$update = 0;
		}
		$st->closeCursor();
		if ($update > 0) {
			$sql = 'UPDATE device SET ';
			$params = array(':id' => $update);
			foreach ($extras as $key => $val) {
				$sql .= $key . '=:' . $key . ', ';
				$params[':'.$key] = $val;
			}
			$sql .= 'last_checkin=NOW() WHERE id=:id';
		} else {
			$params = array(':serial' => $this->_serial, ':platform' => $this->getPlatform());
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
		syslog(LOG_DEBUG, $sql);
		$st = $db->prepare($sql);
		if (!$st->execute($params)) {
			return;
		}
		$st->closeCursor();
	}

	protected function _update_ros () {
		if (!isset($_REQUEST['ver'])) {
			$this->_version = 0;
		} else {
			$ver = intval($_REQUEST['ver']);
			if ($ver >= 1) {
				$this->_version = $_REQUEST['ver'];
			} else {
				$this->_version = 0;
			}
		}
		$this->_next = array($this, '_buildOutput_' . $this->getPlatform());
		return true;
	}

	protected function _buildOutput_ros () {
		$ppath = $_SERVER['WMS_PATH'] . '/update/ros';
		$this->_logparam = array(
			'ver' => $this->_version
		);
		if (isset($_REQUEST['curver'])) {
			$current = intval($_REQUEST['curver']);
		} else {
			if (!is_link($ppath . '/current')) {
				return false;
			}
			$current = intval(readlink($ppath . '/current'));
		}
		if ($current <= 0) {
			return false;
		}
		if ($this->_version == $current) {
			return false;
		}
		if (!is_dir($ppath . '/' . $current)) {
			return false;
		}
		$dir = opendir($ppath . '/' . $current);
		if (!$dir) {
			return false;
		}
		$csfiles = array();
		while (false !== ($file = readdir($dir))) {
			if ($file{0} == '.') continue;
			if (is_file($ppath . '/' . $current . '/' . $file)) {
				$csfiles[] = $file;
			}
		}
		closedir($dir);
		$extras = array();
		if ($this->_version == 0) {
			$fadd = $csfiles;
		} elseif ($this->_version > 0) {
			foreach (array('rosver','cpu','arch','board','firmware') as $param) {
				if (!isset($_REQUEST[$param])) {
					continue;
				}
				$this->_logparam[$param] = $_REQUEST[$param];
			}
			if (isset($_REQUEST['rosver'])) {
				$extras['os'] = $_REQUEST['rosver'];
			}
			if (isset($_REQUEST['board'])) {
				$extras['model'] = $_REQUEST['board'];
			}
			if (isset($_REQUEST['name'])) {
				$extras['name'] = $_REQUEST['name'];
			}
			$extras['updatever'] = $this->_version;
			if (isset($_REQUEST['cpufreq'])) {
				$cpufreq = $_REQUEST['cpufreq'];
				$cpufreql = strlen($_REQUEST['cpufreq']);
				for ($i = 0; $i < $cpufreql; $i++) {
					if ($cpufreq{$i} == '.') {
						continue;
					}
					if (!is_numeric($cpufreq{$i})) {
						break;
					}
				}
				$unit = strtolower(trim(substr($cpufreq, $i)));
				$cpufreq = (float) substr($cpufreq, 0, $i);
				if ($cpufreq <= 0) {
					$this->_logparam['cpufreq'] = $_REQUEST['cpufreq'];
				} else {
					switch ($unit) {
					case 'mhz':
						break;
					case 'ghz':
						$cpufreq = intval($cpufreq * 1000);
						break;
					case 'khz':
						$cpufreq = intval($cpufreq / 1000);
						break;
					}
					$this->_logparam['cpufreq'] = $cpufreq;
				}
			}
			$osfiles = array();
			if (is_dir($ppath . '/' . $this->_version)) {
				$dir = opendir($ppath . '/' . $this->_version);
				if ($dir) {
					while (false !== ($file = readdir($dir))) {
						if ($file{0} == '.') continue;
						if (is_file($ppath . '/' . $this->_version . '/' . $file)) {
							$osfiles[] = $file;
						}
					}
					closedir($dir);
				}
			}
			$fremove = array_diff($osfiles, $csfiles);
			$fadd = array_diff($csfiles, $osfiles);
			$dfiles = array_intersect($osfiles, $csfiles);
			foreach ($dfiles as $dfile) {
				$odfile = file_get_contents($ppath . '/' . $this->_version . '/' . $dfile);
				$cdfile = file_get_contents($ppath . '/' . $current . '/' . $dfile);
				if ($odfile != $cdfile) {
					$fremove[] = $dfile;
					$fadd[] = $dfile;
				}
			}
		}
		$this->_addDevice($extras);
?>
/system script
<?php
		if (!isset($fremove)):
?>
:foreach n in [find where name~"^ctwug_.*"] do={remove $n}
<?php
		else:
			foreach ($fremove as $file):
?>
remove [find where name="<?php echo $file; ?>"]
<?php
			endforeach;
		endif;
		if (isset($fadd)):
			foreach ($fadd as $file):
				echo 'add name="' . $file . '"';
				echo ' policy=policy=reboot,read,write,policy,test,password,sensitive';
				echo ' source="' . $this->_serialiseScript($ppath . '/' . $current . '/' . $file) . '"';
				echo "\n";
			endforeach;
			foreach (array('ctwug_update','ctwug_global_settings','ctwug_qos','ctwug_firewall') as $file):
				if (in_array($file, $fadd)):
					echo 'run ' . $file . "\n";
				endif;
			endforeach;
		endif;

		return true;
	}

	private function _serialiseScript ($file) {
		if (!is_file($file)) {
			return '';
		}
		$contents = file_get_contents($file);
		if ($contents === false) {
			return '';
		}
		return str_replace(array("\r\n", "\n\r", "\r", "\n"), '\n', $contents);
	}
}

$wms = new WMS_Update();
$wms->boot();
