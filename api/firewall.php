<?php
require_once($_SERVER['WMS_PATH'] . '/wms.php');

class WMS_Firewall extends WMS {
	private $_protocols;

	public function __construct () {
		parent::__construct();
		$this->_logmodule = 'firewall';
		$this->_next = array($this, '_firewall_' . $this->getPlatform());
		$this->_protocols[1] = 'icmp';
		$this->_protocols[2] = 'igmp';
		$this->_protocols[6] = 'tcp';
		$this->_protocols[17] = 'udp';
		$this->_protocols[46] = 'rsvp';
		$this->_protocols[47] = 'gre';
		$this->_protocols[50] = 'esp';
		$this->_protocols[51] = 'ah';
		$this->_protocols[58] = 'icmpv6';
		$this->_protocols[89] = 'ospf';
		$this->_protocols[94] = 'ipip';
		$this->_protocols[115] = 'l2tp';
		$this->_protocols[132] = 'sctp';
	}

	protected function _firewall_ros () {
		$l4rules = array();
		$db = $this->_dbConnect();
		$sql = 'SELECT protocol, port_min, port_max, class, comment FROM qos_classify ORDER BY sort ASC';
		if (false === ($st = $db->prepare($sql))) {
			return false;
		}
		if (!$st->execute()) {
			return false;
		}
		while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
			if (!in_array($row['class'], array('bulk','service','game','real'))) {
				// invalid class name
				continue;
			}
			if (!isset($this->_protocols[$row['protocol']])) {
				// unknown protocol
				continue;
			}
			$protocol = $this->_protocols[$row['protocol']];
			$l4rule = 'chain=pre-l4 action=jump jump-target=pre-' . $row['class'];
			$l4rule .= ' passthrough=no protocol=' . $protocol;
			if ($row['protocol'] == 6 || $row['protocol'] == 17) {
				// TCP/UDP must specify a port
				if ($row['port_min'] < 1) {
					continue;
				}
				$l4rule .= ' port=' . $row['port_min'];
				if ($row['port_max'] > $row['port_min']) {
					$l4rule .= '-' . $row['port_max'];
				}
			}
			$l4rule .= ' disabled=yes comment="AUTO ' . $row['comment'] . '"';
			$l4rules[] = $l4rule;
		}
		if (sizeof($l4rules) < 1 ) {
			return false;
		}
		$l4rules[] = 'chain=pre-l4 action=jump jump-target=pre-bulk disabled=yes comment="AUTO catchall"';
?>
/ip firewall mangle
:foreach n in [find where comment~"^AUTO.*"] do={ remove $n }
add chain=prerouting action=mark-packet new-packet-mark=BULK passthrough=no connection-mark=pre-bulk disabled=yes comment="AUTO bulk"
add chain=prerouting action=mark-packet new-packet-mark=SERVICE passthrough=no connection-mark=pre-service disabled=yes comment="AUTO service"
add chain=prerouting action=mark-packet new-packet-mark=GAME passthrough=no connection-mark=pre-game disabled=yes comment="AUTO game"
add chain=prerouting action=mark-packet new-packet-mark=REAL passthrough=no connection-mark=pre-real disabled=yes comment="AUTO real"
add chain=pre-bulk action=mark-connection new-connection-mark=pre-bulk passthrough=yes connection-mark=!pre-bulk disabled=yes comment="AUTO bulk"
add chain=pre-bulk action=mark-packet new-packet-mark=BULK passthrough=no connection-mark=pre-bulk disabled=yes comment="AUTO bulk"
add chain=pre-service action=mark-connection new-connection-mark=pre-service passthrough=yes connection-mark=!pre-service disabled=yes comment="AUTO service"
add chain=pre-service action=mark-packet new-packet-mark=SERVICE passthrough=no connection-mark=pre-service disabled=yes comment="AUTO service"
add chain=pre-game action=mark-connection new-connection-mark=pre-game passthrough=yes connection-mark=!pre-game disabled=yes comment="AUTO game"
add chain=pre-game action=mark-packet new-packet-mark=GAME passthrough=no connection-mark=pre-game disabled=yes comment="AUTO game"
add chain=pre-real action=mark-connection new-connection-mark=pre-real passthrough=yes connection-mark=!pre-real disabled=yes comment="AUTO real"
add chain=pre-real action=mark-packet new-packet-mark=REAL passthrough=no connection-mark=pre-real disabled=yes comment="AUTO real"
add chain=prerouting action=jump jump-target=pre-l4 passthrough=no disabled=yes comment="AUTO prerouting"
add chain=output action=jump jump-target=pre-l4 passthrough=no disabled=yes comment="AUTO output"
add chain=input action=jump jump-target=pre-l4 passthrough=no disabled=yes comment="AUTO input"
<?php
		foreach ($l4rules as $l4rule) {
			echo 'add ' . $l4rule . "\n";
		}
		return true;
	}
}

$wms = new WMS_Firewall();
$wms->boot();
