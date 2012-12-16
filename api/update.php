<?php
require_once($_SERVER['WMS_PATH'] . '/wms.php');

class WMS_Update extends WMS {
	private $_version;
	private $_add;
	private $_remove;

	public function __construct () {
		parent::__construct();
		$this->_logmodule = 'update';
		$this->_next = array($this, '_doLog_' . $this->getPlatform());
	}

	private function _sanitise_updatever ($val) {
		if (is_numeric($val) && $val >= 0 && $val < 4294967294) {
			return true;
		}
		return false;
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

	private function _addDevice ($extras) {
		// Basic input checks
		foreach ($extras as $key => $val) {
			$sanitise = array($this, '_sanitise_' . $key);
			if (!is_callable($sanitise)) {
				unset($extras[$key]);
				continue;
			}
			if (!call_user_func($sanitise, $val)) {
				unset($extras[$key]);
			}
		}
		// Check if serial number already exists
		$db = $this->_dbConnect();
		$st = $db->prepare('SELECT id FROM device WHERE serial=:serial');
		if (!$st->execute(array(':serial' => $this->_serial))) {
			return;
		}
		// If it does, we have an update.  If not, a new insertion.
		if ($row = $st->fetch()) {
			$update = $row['id'];
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
			$sql .= 'last_checkin=NOW() WHERE id=:id';
		} else {
			// Table INSERTs
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
		// Execute query
		$st = $db->prepare($sql);
		if (!$st->execute($params)) {
			return;
		}
		$st->closeCursor();
	}

	protected function _doLog_ros () {
		// Build some data structures for logging
		if (!isset($_REQUEST['ver'])) {
			$ver = 0;
		} else {
			$ver = intval($_REQUEST['ver']);
			if ($ver < 1) {
				$ver = 0;
			}
		}
		$this->_version = $ver;
		$syslogm = array('ver' => $ver);
		$dblogm = array('updatever' => $ver);
		// $ver > 0 means ctwug_update is executing, and we have more metrics to log
		// syslog logs raw data
		// db stores abstracted data
		if ($ver > 0) {
			if (!$this->_serial) {
				// missing this later would be ugly
				$this->bail('Missing serial number');
				return false;
			}
			foreach (array('cpu','cpufreq','arch','firmware') as $param) {
				if (!isset($_REQUEST[$param])) {
					continue;
				}
				$syslogm[$param] = $_REQUEST[$param];
			}
			if (isset($_REQUEST['rosver'])) {
				$dblogm['os'] = $syslogm['rosver'] = $_REQUEST['rosver'];
			}
			if (isset($_REQUEST['board'])) {
				$dblogm['model'] = $syslogm['board'] = $_REQUEST['board'];
			}
			if (isset($_REQUEST['name'])) {
				$dblogm['name'] = $syslogm['name'] = $_REQUEST['name'];
			}
			if (isset($_REQUEST['contact'])) {
				// E-mail address... or we hope so
				$syslogm['contact'] = $_REQUEST['contact'];
				$dbcontact = trim($_REQUEST['contact']);
				if (preg_match('/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i', $dbcontact)) {
					$dblogm['contact'] = $dbcontact;
				}
			}
			// update DB
			$this->_addDevice($dblogm);
		}
		if (isset($_REQUEST['curver'])) {
			$syslogm['curver'] = $_REQUEST['curver'];
		}
		// setup syslog params... will be logged by parent class
		$this->_logparam = $syslogm;
		// next stop, update calculation
		$this->_next = array($this, '_calcUpdate_' . $this->getPlatform());
		return true;
	}

	protected function _calcUpdate_ros () {
		$spath = $_SERVER['WMS_PATH'] . '/update/ros';
		if (isset($_REQUEST['curver'])) {
			$current = intval($_REQUEST['curver']);
		} else {
			if (!is_link($spath . '/current')) {
				// Unable to determine current version
				return false;
			}
			$current = intval(readlink($spath . '/current'));
		}
		if ($current < 1) {
			// Invalid current version
			$this->_log(LOG_ERR, 'invalid current version');
			return false;
		}
		$running = $this->_version;
		if ($running == $current) {
			// running version matches current version
			return false;
		}
		if (!is_dir($spath . '/' . $current)) {
			// broken symlink?
			$this->_log(LOG_ERR, 'invalid current dir');
			return false;
		}
		$dir = opendir($spath . '/' . $current);
		if (!$dir) {
			// file system problem
			$this->_log(LOG_ERR, 'current dir read error');
			return false;
		}
		// Reset $this->_version to the current script version, our target version
		$this->_version = $current;
		// Let the calculations begin
		$csfiles = array();
		while (false !== ($file = readdir($dir))) {
			if ($file{0} == '.') continue; // skip hidden entries
			if (is_file($spath . '/' . $current . '/' . $file)) {
				$csfiles[$file] = $spath . '/' . $current . '/' . $file;
			}
		}
		closedir($dir);
		if (sizeof($csfiles) < 1) {
			// FFS... empty loot
			$this->_log(LOG_ERR, 'empty current version');
			return false;
		}
		// Our next jump is most likely to generate output
		$this->_next = array($this, '_genOutput_' . $this->getPlatform());
		if ($running == 0) {
			// We haz a virgin!
			$this->_add = $csfiles;
			return true;
		}
		if (!is_dir($spath . '/' . $running)) {
			// Missing running version?  Treat our geriatric as a virgin.
			// (who would complain about that?)
			$this->_log(LOG_NOTICE, 'fountain of youth');
			$this->_add = $csfiles;
			return true;
		}
		$dir = opendir($spath . '/' . $running);
		if (!$dir) {
			// Unable to read running version
			$this->_log(LOG_ERR, 'running dir read error');
			return false;
		}
		// Gimme the 411
		$rsfiles = array();
		while (false !== ($file = readdir($dir))) {
			if ($file{0} == '.') continue;
			if (is_file($spath . '/' . $running . '/' . $file)) {
				$rsfiles[$file] = $spath . '/' . $running . '/' . $file;
			}
		}
		closedir($dir);
		if (sizeof($rsfiles) < 1) {
			// No files found for running version
			$this->_log(LOG_ERR, 'empty running version');
			return false;
		}
		// What's gone?
		$fremove = array_diff_key($rsfiles, $csfiles);
		// What's new?
		$fadd = array_diff_key($csfiles, $rsfiles);
		// What's changed?
		$dfiles = array_intersect_key($rsfiles, $csfiles);
		foreach ($dfiles as $dfile => $val) {
			$rdfile = file_get_contents($spath . '/' . $running . '/' . $dfile);
			$cdfile = file_get_contents($spath . '/' . $current . '/' . $dfile);
			if ($rdfile != $cdfile) {
				$fremove[$dfile] = true;
				$fadd[$dfile] = $spath . '/' . $current . '/' . $dfile;
			}
		}
		// ctwug_update contains a %ver% macro,
		// consequently it must _always_ be refreshed
		// when the version changes.
		if (!isset($fadd['ctwug_update'])) {
			$fremove['ctwug_update'] = true;
			$fadd['ctwug_update'] = $spath . '/' . $current . '/ctwug_update';
		}
		$this->_add = $fadd;
		$this->_remove = $fremove;
		// Ready for output generation!
		return true;
	}

	protected function _genOutput_ros () {
		$virgin = false;
		if (!isset($this->_remove)):
			$virgin = true;
?>
:put ""
:put "Backing up existing configuration to ctwug-prejoin.backup"
/system backup save name=ctwug-prejoin
:local fwait 1
:local cnt 0
:while ($fwait = 1) do={
  :set cnt ($cnt+1)
  :delay 1
  if ([/file find name=ctwug-prejoin.backup] != "") do={
    :set fwait 0
  }
  if ($cnt >= 20) do={
    :set fwait 0
    :error "Backup failed!  Aborting."
  }
}
:put "Seeking and destroying old scripts"
/system scheduler;
:foreach n in [find name~"^ctwug_.*"] do={remove $n};
/system script;
:foreach n in [find name~"^ctwug_.*"] do={remove $n};
:foreach n in [find name="radius_client"] do={remove $n};
<?php
		else:
?>
/system script;
<?php
			foreach ($this->_remove as $file => $path):
?>
:foreach n in [find name="<?php echo $file; ?>"] do={remove $n};
<?php
			endforeach;
		endif;
		if (isset($this->_add)):
			$add = $this->_add;
			foreach ($add as $file => $path):
?>
<?php if ($virgin): ?>
:put "Adding script <?php echo $file; ?>";
<?php endif; ?>
add name="<?php echo $file; ?>" policy=reboot,read,write,policy,test,password,sensitive source="<?php echo $this->_serialiseScript($path); ?>";
<?php
			endforeach;
			foreach (array('ctwug_global_settings','ctwug_qos') as $file):
				if (isset($add[$file])):
?>
<?php if ($virgin): ?>
:put "Running script <?php echo $file; ?>";
<?php endif; ?>
run <?php echo $file; ?>;
<?php
				endif;
			endforeach;
			if (isset($add['ctwug_update'])):
				// if ctwug_update changes, make it run 5 seconds later
				// this calls back to WMS letting it know the upgrade was successful
?>
/system scheduler add name="ctwug_update_temp" interval=5s on-event="/system script run ctwug_update";
<?php
			endif;
			if (isset($add['ctwug_backup']) && $virgin):
				// add ctwug_backup to scheduler only if this is a virgin
				// make it run every day some time between 1:00 and 2:00
				$randstart = (60 * rand(0, 60)) + 3600;
?>
:put "Adding ctwug_backup to scheduler";
/system scheduler add name="ctwug_backup" interval=1d start-time=[:totime <?php echo $randstart; ?>] on-event="/system script run ctwug_backup";
<?php
			endif;
		endif;
// always run ctwug_firewall and ctwug_gametime when we send an update
?>
<?php if ($virgin): ?>
:put "Running script ctwug_firewall";
<?php endif; ?>
run ctwug_firewall;
<?php if ($virgin): ?>
:put "Running script ctwug_gametime";
<?php endif; ?>
run ctwug_gametime;
<?php if ($virgin): ?>
:put "Welcome to CTWUG!";
<?php endif; ?>
<?php
		return true;
	}

	private function _serialiseScript ($file, $ver = 0) {
		if ($ver == 0) {
			$ver = $this->_version;
		}
		if (!is_file($file)) {
			return '';
		}
		$contents = file_get_contents($file);
		if ($contents === false) {
			return '';
		}
		$needles = array('\\', "\r\n", "\n\r", "\r", "\n", '"', '$', '%ver%');
		$pins = array('\\\\', '\n', '\n', '\n', '\n', '\"', '\$', $ver);
		return str_replace($needles, $pins, $contents);
	}

	private function _freqCanonicalise ($freq) {
		// Thought this would be cool, but turned out only maybe cool in future
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

$wms = new WMS_Update();
$wms->boot();
