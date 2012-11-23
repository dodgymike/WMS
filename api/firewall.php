<?php
/*
 * firewall.php
 * this forms part of the CTWUG NOC
 *
 * Statically return the firewall script
 *
 * Mike Davis
 * 2012/11/22

//sample api call
http://noc.ctwug.za.net/web/api/firewall/firewall.rsc
*/
include_once('config.php');
$db = new PDO("mysql:host=$DBHOST;dbname=$DBNAME", $DBUSER,$DBPASS);

$IPaddr = $_SERVER['REMOTE_ADDR'];


$stmt = $db->prepare("INSERT into firewall (ipaddr,lastseen) values (INET_ATON(?),CURRENT_TIMESTAMP()) ON DUPLICATE KEY UPDATE lastseen = CURRENT_TIMESTAMP()");
$stmt->execute(array($IPaddr));

?>
/ip firewall mangle
:local fw
:foreach fw in [find] do={
 :if ( [:pick [get $fw comment] 0 4] = "AUTO" ) do=[remove $fw]
}
add action=mark-packet chain=prerouting comment="AUTO bulk" disabled=yes connection-mark=pre-bulk new-packet-mark=BULK passthrough=no
add action=mark-packet chain=prerouting comment="AUTO service" disabled=yes connection-mark=pre-service new-packet-mark=SERVICE passthrough=no
add action=mark-packet chain=prerouting comment="AUTO game" disabled=yes connection-mark=pre-game new-packet-mark=GAME passthrough=no
add action=mark-packet chain=prerouting comment="AUTO real" disabled=yes connection-mark=pre-real new-packet-mark=REAL passthrough=no
add action=mark-connection chain=pre-bulk comment="AUTO bulk" disabled=yes connection-mark=!pre-bulk new-connection-mark=pre-bulk
add action=mark-packet chain=pre-bulk comment="AUTO bulk" disabled=yes connection-mark=pre-bulk new-packet-mark=BULK passthrough=no
add action=mark-connection chain=pre-service comment="AUTO service" disabled=yes connection-mark=!pre-service new-connection-mark=pre-service
add action=mark-packet chain=pre-service comment="AUTO service" disabled=yes connection-mark=pre-service new-packet-mark=SERVICE passthrough=no
add action=mark-connection chain=pre-game comment="AUTO game" disabled=yes connection-mark=!pre-game new-connection-mark=pre-game
add action=mark-packet chain=pre-game comment="AUTO game" disabled=yes connection-mark=pre-game new-packet-mark=GAME passthrough=no
add action=mark-connection chain=pre-real comment="AUTO real" disabled=yes connection-mark=!pre-real new-connection-mark=pre-real
add action=mark-packet chain=pre-real comment="AUTO real" disabled=yes connection-mark=pre-real new-packet-mark=REAL passthrough=no 

add action=jump chain=prerouting comment="AUTO TCP" disabled=yes protocol=tcp jump-target=pre-tcp
add action=jump chain=prerouting comment="AUTO UDP" disabled=yes protocol=udp jump-target=pre-udp
add action=jump chain=output comment="AUTO TCP" disabled=yes protocol=tcp jump-target=pre-tcp
add action=jump chain=input comment="AUTO TCP" disabled=yes protocol=tcp jump-target=pre-tcp
add action=jump chain=output comment="AUTO UDP" disabled=yes protocol=udp jump-target=pre-udp
add action=jump chain=input comment="AUTO UDP" disabled=yes protocol=udp jump-target=pre-udp
add action=jump chain=pre-tcp comment="AUTO Torrents TCP" disabled=yes jump-target=pre-bulk passthrough=no protocol=tcp port=7000-7200
add action=jump chain=pre-udp comment="AUTO Torrents UDP" disabled=yes jump-target=pre-bulk passthrough=no protocol=udp port=7000-7200
add action=jump chain=pre-tcp comment="AUTO DC - TX TCP" disabled=yes jump-target=pre-bulk passthrough=no protocol=tcp port=2222-2223
add action=jump chain=pre-tcp comment="AUTO HTTP" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=80
add action=jump chain=prerouting comment="AUTO PPTP" disabled=yes jump-target=pre-game passthrough=no protocol=gre
add action=jump chain=pre-udp comment="AUTO TeamSpeak" disabled=yes jump-target=pre-real passthrough=no protocol=udp port=9987
add action=jump chain=pre-udp comment="AUTO DC - HUB (udp)" disabled=yes jump-target=pre-service passthrough=no protocol=udp port=411
add action=jump chain=pre-tcp comment="AUTO DC - HUB" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=411
add action=jump chain=pre-tcp comment="AUTO IRC" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=6667
add action=jump chain=pre-tcp comment="AUTO Winbox" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=8291
add action=jump chain=pre-udp comment="AUTO syslog" disabled=yes jump-target=pre-real passthrough=no protocol=udp port=514
add action=jump chain=pre-tcp comment="AUTO TMF TCP" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=2350-2351
add action=jump chain=pre-udp comment="AUTO Hon2" disabled=yes jump-target=pre-game passthrough=no protocol=udp port=11443
add action=jump chain=pre-udp comment="AUTO TMF UDP" disabled=yes jump-target=pre-game passthrough=no protocol=udp port=2350-2351
add action=jump chain=pre-udp comment="AUTO COD" disabled=yes jump-target=pre-game passthrough=no protocol=udp port=28960
add action=jump chain=pre-tcp comment="AUTO WOW1" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=3724,8085
add action=jump chain=pre-tcp comment="AUTO WOW2" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=6882-6999
add action=jump chain=pre-udp comment="AUTO DayZ Udp " disabled=yes jump-target=pre-game passthrough=no protocol=udp port=2302
add action=jump chain=pre-tcp comment="AUTO SMTP" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=25
add action=jump chain=pre-tcp comment="AUTO puppet" disabled=yes jump-target=pre-real passthrough=no protocol=tcp port=8140
add action=jump chain=pre-tcp comment="AUTO ntop - http port" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=3000
add action=jump chain=pre-udp comment="AUTO netflows" disabled=yes jump-target=pre-real passthrough=no protocol=udp port=1234-1239
add action=jump chain=pre-udp comment="AUTO LanBridger" disabled=yes jump-target=pre-game passthrough=no protocol=udp port=40001
add action=jump chain=pre-tcp comment="AUTO Warcraft3" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=6112
add action=jump chain=pre-tcp comment="AUTO HONTCP" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=11031
add action=jump chain=pre-tcp comment="AUTO PPTP TCP" disabled=yes jump-target=pre-game passthrough=no protocol=tcp port=1723
add action=jump chain=pre-tcp comment="AUTO DNS - transfers" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=53
add action=jump chain=pre-udp comment="AUTO VOIP IAX" disabled=yes jump-target=pre-real passthrough=no protocol=udp port=4569
add action=jump chain=pre-udp comment="AUTO Radius" disabled=yes jump-target=pre-service passthrough=no protocol=udp port=1812-1814
add action=jump chain=pre-udp comment="AUTO SNMP" disabled=yes jump-target=pre-service passthrough=no protocol=udp port=161
add action=jump chain=pre-tcp comment="AUTO IRC SSL" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=6697
add action=jump chain=pre-tcp comment="AUTO SSH" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=22-23
add action=jump chain=pre-udp comment="AUTO NTP" disabled=yes jump-target=pre-service passthrough=no protocol=udp port=123
add action=jump chain=pre-udp comment="AUTO DC - TX UDP" disabled=yes jump-target=pre-bulk passthrough=no protocol=udp port=2222-2223
add action=jump chain=prerouting comment="AUTO OSPF" disabled=yes jump-target=pre-service passthrough=no protocol=ospf
add action=jump chain=pre-udp comment="AUTO DNS" disabled=yes jump-target=pre-service passthrough=no protocol=udp port=53
add action=jump chain=pre-tcp comment="AUTO Webmin" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=10000-10001,11112,8082
add action=jump chain=pre-tcp comment="AUTO HTTPS" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=443
add action=jump chain=pre-tcp comment="AUTO Jabber" disabled=yes jump-target=pre-real passthrough=no protocol=tcp port=5222-5223
add action=jump chain=pre-tcp comment="AUTO MTik BTest" disabled=yes jump-target=pre-bulk passthrough=no protocol=tcp port=2000
add action=jump chain=pre-tcp comment="AUTO Windows Share" disabled=yes jump-target=pre-bulk passthrough=no protocol=tcp port=445
add action=jump chain=pre-udp comment="AUTO HON" disabled=yes jump-target=pre-game passthrough=no protocol=udp port=11235-11335
add action=jump chain=prerouting comment="AUTO ICMP" disabled=yes jump-target=pre-service passthrough=no protocol=icmp
add action=jump chain=output comment="AUTO ICMP" disabled=yes jump-target=pre-service passthrough=no protocol=icmp
add action=jump chain=pre-tcp comment="AUTO VNC RDP" disabled=yes jump-target=pre-service passthrough=no protocol=tcp port=3389,3390,5900
add action=jump chain=pre-tcp comment="AUTO FTP" disabled=yes jump-target=pre-bulk passthrough=no protocol=tcp port=21
add action=jump chain=prerouting comment="AUTO ALL" disabled=yes jump-target=pre-bulk passthrough=no

