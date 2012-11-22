<?php
/*
 * gametime.php
 * this forms part of the CTWUG NOC
 *
 * rb's will call as below and info will be persisted into a database
 *
 * Mike Davis
 * 2012/11/22

//sample api call
http://noc.ctwug.za.net/web/api/gametime

 */

// date 
$today = getdate();

$wday  = $today['wday'];
$hour  = $today['hours'];

// gametime
$gametime = 0;

//setup pdo mysql connection
$db = new PDO('mysql:host=localhost;dbname=wms', 'wms', 'wms');

//check if there is an entry for the existing routerboard serialnumber
$stmt = $db->prepare("select id, dow, hour, active from game_time_schedule where dow = ? and hour = ?");
$stmt->execute(array($wday, $hour));
if ($stmt->rowCount() >0) {
    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
    $gametime = $rows[0]->active;
}

// is it gametime?
echo $gametime;
