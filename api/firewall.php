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
header('Content-Type: text/plain');
$db = new PDO("mysql:host=$DBHOST;dbname=$DBNAME", $DBUSER,$DBPASS);

//Get the ip and record it in the db
$IPaddr = $_SERVER['REMOTE_ADDR'];
$stmt = $db->prepare("INSERT into firewall (ipaddr,lastseen) values (INET_ATON(?),CURRENT_TIMESTAMP()) ON DUPLICATE KEY UPDATE lastseen = CURRENT_TIMESTAMP()");
$stmt->execute(array($IPaddr));


//heading
echo '/ip firewall mangle'. "\r\n";
echo ':local fw'. "\r\n";
echo ':foreach fw in [find] do={'. "\r\n";
echo ':if ( [:pick [get $fw comment] 0 4] = "AUTO" ) do=[remove $fw]}'."\r\n";


//get all the services and echo them
$stmt = $db->prepare("SELECT * from firewall_services ORDER BY id");
$stmt->execute();

while ($row = $stmt->fetch())
{
	echo 'add action='.$row[1] . 
	' chain='.$row[2] . 
	' comment='.'"AUTO '.$row[3].'"'. 
	' disabled='.$row[4] . 
	' connection-mark='.$row[5]. 
	' new-packet-mark='.$row[6];

//If passthrough is NULL don't echo it
	if (isset($row[7])) {
	echo ' passthrough='.$row[7] . "\r\n"; }
	else {echo "\r\n";};
}

//get all the rules and echo them
$stmt = $db->prepare("SELECT * from firewall_rules ORDER BY id");
$stmt->execute();

while ($row = $stmt->fetch())
{
	echo 'add action='. $row[1]. 
	' chain='.$row[2]. 
	' comment='.'"AUTO '.$row[3].'"'. 
	' disabled='.$row[4]. 
	' jump-target='.$row[6]. 
	' protocol='.$row[5]; 

//Don't echo port and passthrough if they are NULL
	if (isset($row[8])){
	echo " port=".$row[8];
	}
	if (isset($row[7])) {
	echo ' passthrough='.$row[7] . "\r\n"; }
	else {echo "\r\n";};
}

//Dump everything else into bulk
echo 'add action=jump chain=prerouting comment="AUTO ALL" disabled=yes jump-target=pre-bulk passthrough=no';
?>
