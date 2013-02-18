<?php
require_once($_SERVER['WMS_PATH'] . '/api.php');

class WMS_Firewall extends WMS_API {
	private $_protocols;

	public function __construct ($wms) {
		parent::__construct($wms);
		$pf = $this->getPlatform();
		if (!$pf) {
			$this->bail('Unsupported platform');
			return;
		}
		$this->_next = array($this, '_firewall_dscp_' . $pf);
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

	protected function _firewall_ctracking_ros () {
		$l4rules = array();
		$db = $this->_wms->getDb();
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

	protected function _firewall_dscp_ros () {
		$l4rules = array();
		$db = $this->_wms->getDb();
		$sql = 'SELECT protocol, port_min, port_max, class, comment FROM qos_classify ORDER BY sort ASC';
		if (false === ($st = $db->prepare($sql))) {
			$this->_log(LOG_ERR, 'DB error');
			return false;
		}
		if (!$st->execute()) {
			$this->_log(LOG_ERR, 'DB query error');
			return false;
		}
		while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
			if (!in_array($row['class'], array('bulk','service','game','real'))) {
				// invalid class name
				continue;
			}
			if (!isset($this->_protocols[$row['protocol']])) {
				// unknown protocol
				$this->_log(LOG_WARNING, 'Unknown protocol: ' . $row['protocol']);
				continue;
			}
			$protocol = $this->_protocols[$row['protocol']];
			$l4rule = 'chain=preclassify action=jump jump-target=predscp-' . $row['class'];
			$l4rule .= ' protocol=' . $protocol;
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
		$l4rules[] = 'chain=preclassify action=jump jump-target=predscp-bulk disabled=yes comment="AUTO catchall"';
?>
/ip firewall mangle
:foreach n in [find where comment~"^AUTO.*"] do={ remove $n }
add chain=premark-bulk action=set-priority new-priority=1 passthrough=yes disabled=yes comment="AUTO bulk mark"
add chain=premark-bulk action=mark-packet new-packet-mark=BULK passthrough=no disabled=yes comment="AUTO bulk mark"
add chain=premark-service action=set-priority new-priority=3 passthrough=yes disabled=yes comment="AUTO service mark"
add chain=premark-service action=mark-packet new-packet-mark=SERVICE passthrough=no disabled=yes comment="AUTO service mark"
add chain=premark-game action=set-priority new-priority=5 passthrough=yes disabled=yes comment="AUTO game mark"
add chain=premark-game action=mark-packet new-packet-mark=GAME passthrough=no disabled=yes comment="AUTO game mark"
add chain=premark-real action=set-priority new-priority=7 passthrough=yes disabled=yes comment="AUTO real mark"
add chain=premark-real action=mark-packet new-packet-mark=REAL passthrough=no disabled=yes comment="AUTO real mark"
add chain=predscp-bulk action=change-dscp new-dscp=2 passthrough=yes disabled=yes comment="AUTO bulk dscp"
add chain=predscp-bulk action=jump jump-target=premark-bulk disabled=yes comment="AUTO bulk dscp"
add chain=predscp-service action=change-dscp new-dscp=8 passthrough=yes disabled=yes comment="AUTO service dscp"
add chain=predscp-service action=jump jump-target=premark-service disabled=yes comment="AUTO service dscp"
add chain=predscp-game action=change-dscp new-dscp=32 passthrough=yes disabled=yes comment="AUTO game dscp"
add chain=predscp-game action=jump jump-target=premark-game disabled=yes comment="AUTO game dscp"
add chain=predscp-real action=change-dscp new-dscp=48 passthrough=yes disabled=yes comment="AUTO real dscp"
add chain=predscp-real action=jump jump-target=premark-real disabled=yes comment="AUTO real dscp" 
<?php
		foreach ($l4rules as $l4rule) {
			echo 'add ' . $l4rule . "\n";
		}
?>
add chain=prerouting action=jump jump-target=premark-bulk dscp=2 disabled=yes comment="AUTO bulk DSCP"
add chain=prerouting action=jump jump-target=premark-service dscp=8 disabled=yes comment="AUTO service DSCP"
add chain=prerouting action=jump jump-target=premark-game dscp=32 disabled=yes comment="AUTO game DSCP"
add chain=prerouting action=jump jump-target=premark-real dscp=48 disabled=yes comment="AUTO real DSCP"
add chain=prerouting action=jump jump-target=preclassify disabled=yes comment="AUTO prerouting"
add chain=output action=jump jump-target=premark-real protocol=ospf disabled=yes comment="AUTO OSPF"
add chain=output action=jump jump-target=predscp-service protocol=icmp disabled=yes comment="AUTO ICMP"
add chain=output action=jump jump-target=predscp-service protocol=tcp port=8291 disabled=yes comment="AUTO Winbox"
add chain=output action=jump jump-target=predscp-service protocol=tcp port=22-23 disabled=yes comment="AUTO SSH/Telnet"
add chain=output action=jump jump-target=predscp-service protocol=udp port=1812-1814 disabled=yes comment="AUTO Radius"
add chain=output action=jump jump-target=predscp-real protocol=udp port=514 disabled=yes comment="AUTO Syslog"
add chain=output action=jump jump-target=predscp-service protocol=udp port=161 disabled=yes comment="AUTO SNMP"
add chain=output action=jump jump-target=predscp-service protocol=udp port=123 disabled=yes comment="AUTO NTP"
add chain=output action=jump jump-target=predscp-real protocol=udp port=1234-1239 disabled=yes comment="AUTO Netflow"
add chain=output action=jump jump-target=predscp-service protocol=tcp port=1723 disabled=yes comment="AUTO PPTP Control"
add chain=output action=jump jump-target=predscp-service protocol=gre disabled=yes comment="AUTO PPTP"
add chain=output action=jump jump-target=predscp-bulk disabled=yes comment="AUTO output"
<?php
		return true;
	}
}

$wms = new WMS_Firewall(new WMS());
$wms->boot();
