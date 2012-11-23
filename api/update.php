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
//setup pdo mysql connection
include_once('config.php');

//helper function
function get($name)
{
    if (isset($_GET[$name])) {
        return $_GET[$name];
    }
    return "";
}

class updateApi
{
    private $db;

    function __construct()
    {
        global $DBHOST, $DBNAME, $DBUSER, $DBPASS;
        $this->db = new PDO("mysql:host=$DBHOST;dbname=$DBNAME", $DBUSER, $DBPASS);
    }


    private function getScriptsForGroup($script_group_id)
    {
        $id = get("id");
        $update = get("update");
        $init = get("init");
        $sql = "SELECT * FROM script WHERE script_group_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($script_group_id));
        $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
        foreach ($rows as $row) {
            echo($row->script_body);
        }
    }

    private function getScriptsForRB($routerboard_id)
    {
        $sql = "SELECT script_group_id FROM routerboard_script_group WHERE routerboard_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($routerboard_id));
        $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
        foreach ($rows as $row) {
            $this->getScriptsForGroup($row->script_group_id);
        }
    }

    private function dumpScripts()
    {
        $serial = get("serial");

        $stmt = $this->db->prepare("SELECT id FROM routerboard WHERE ros_serial = ?");
        $stmt->execute(array($serial));
        $routerboard_id = 0;
        if ($stmt->rowCount() > 0) {
            //this rb exists in the db
            $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
            $routerboard_id = $rows[0]->id;
        }
        if ($routerboard_id != 0) {
            $this->getScriptsForRB($routerboard_id);
        } else {
            echo "No database entry for this routerboard. Please first update your info";
        }
    }

    private function saveRBInfo()
    {
        $id = get("id");
        $serial = get("serial");
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
        $stmt = $this->db->prepare("SELECT * FROM routerboard WHERE ros_serial = ?");
        $stmt->execute(array($serial));
        $routerboard_id = 0;
        if ($stmt->rowCount() > 0) {
            //this rb exists in the db
            $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
            $routerboard_id = $rows[0]->id;
            //so update
            $sql = "update routerboard set name = ? where ros_serial = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array($id, $serial));
        } else {
            //this rb doesnt existin the db
            $sql = "insert into routerboard (name, ros_serial) values (?,?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array($id, $serial));
            $routerboard_id == $this->db->lastInsertId();
        }
        //update rb stats

        //is there a db entry for stats for this rb
        $sql = "select * from routerboard_stats where routerboard_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($routerboard_id));
        $stats_id = 0;
        if ($stmt->rowCount() > 0) {
            //there is so update it
            $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
            $sql = "update routerboard_stats set version = ?, cpu=?, freq=?, arch=?, board=?, fw=?, ip=?, ospf=?, policy=? where routerboard_id = ?";
        } else {
            //there isnt an stats entry yet for this rb, so insert it
            $sql = "insert into routerboard_stats (version, cpu, freq, arch, board, fw, ip, ospf, policy, routerboard_id) values (?,?,?,?,?,?,?,?,?,?)";
        }
        //perform the query
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($version, $cpu, $freq, $arch, $board, $fw, $ip, $ospf, $policy, $routerboard_id));

        //echo out the version number to the caller
        echo $version;
    }

    function process()
    {
        $init = get("init");
        if ($init != "") {
            $this->dumpScripts();
        } else {
            $update = get("update");
            if ($update == 1) {
                $this->saveRBInfo();
            }
            if ($update == 2) {
                $this->showScriptsVersion();
            }
        }
    }
}

$update = new updateApi();
$update->process();
