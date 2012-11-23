<?php
/*
 * update.php
 * this forms part of the CTWUG NOC
 *
 * rb's will call as below and info will be persisted into a database
 *
 * Zayin Krige
 * 2012/11/22

//sample api call
http://noc.ctwug.za.net/web/api/update?id=".$fid."&serial=".$fserial."&update=2&version=".$fver."&cpu=".$fcpu."&freq=".$fcpufreq."&arch=".$farch."&board=".$fboard."&fw=".$ffw."&ip=".$fip."&ospf=".$fospf."&policy=".$fpolicy

 */
function get($name)
{
    if (isset($_GET[$name]))
    {
        return $_GET[$name];
    }
}

//setup pdo mysql connection
include_once('config.php');
$db = new PDO("mysql:host=$DBHOST;dbname=$DBNAME", $DBUSER,$DBPASS);

//get parameters
$id = get("id");
$serial = get("serial");
$update = get("update");
$version = get("version");
$cpu = get("cpu");
$freq = get("freq");
$arch = get("arch");
$board = get("board");
$fw = get("fw");
$ip = get("ip");
$ospf = get("ospf");
$policy = get("policy");

//check if there is an entry for the existing routerboard serialnumber
$stmt = $db->prepare("SELECT * FROM routerboard WHERE ros_serial = ?");
$stmt->execute(array($serial));
$routerboard_id = 0;
if ($stmt->rowCount() >0)
{
    //this rb exists in the db
    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
    $routerboard_id = $rows[0]->id;
    //so update
    $sql = "update routerboard set name = ? where ros_serial = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($id, $serial));
} else
{
    //this rb doesnt existin the db
    $sql = "insert into routerboard (name, ros_serial) values (?,?)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($id, $serial));
    $routerboard_id == $db->lastInsertId();
}
//update rb stats

//is there a db entry for stats for this rb
$sql = "select * from routerboard_stats where routerboard_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute(array($routerboard_id));
$stats_id = 0;
if ($stmt->rowCount() >0)
{
    //there is so update it
    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
    $sql = "update routerboard_stats set version = ?, cpu=?, freq=?, arch=?, board=?, fw=?, ip=?, ospf=?, policy=? where routerboard_id = ?";
} else
{
    //there isnt an stats entry yet for this rb, so insert it
    $sql = "insert into routerboard_stats (version, cpu, freq, arch, board, fw, ip, ospf, policy, routerboard_id) values (?,?,?,?,?,?,?,?,?,?)";
}
//perform the query
$stmt = $db->prepare($sql);
$stmt->execute(array($version, $cpu, $freq, $arch, $board, $fw, $ip, $ospf, $policy, $routerboard_id));

//echo out the version number to the caller
echo $version;
