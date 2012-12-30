<?php
$wdaymap = array('sun','mon','tue','wed','thu','fri','sat');
$now = localtime((time()), true);
$wday  = $wdaymap[$now['tm_wday']];
$hour  = $now['tm_hour'];

if ($hour < 10) {
	$hour = '0' . $hour;
}

if (isset($_REQUEST['on']) && $_REQUEST['on'] == '1') {
	$httpcode = array('on'=>404, 'off'=>200);
} else {
	$httpcode = array('on'=>200, 'off'=>404);
}

$path = $_SERVER['WMS_PATH'] . '/gametime/' . $wday . '/' . $hour;

if (file_exists($path)) {
	$msg = 'Gametime is ON';
	header('HTTP/1.0 ' . $httpcode['on'] . ' ' . $msg);
} else {
	$msg = 'Gametime is OFF';
	header('HTTP/1.0 ' . $httpcode['off'] . ' ' . $msg);
}
header('Content-Type: text/plain');
header('Content-Length: ' . strlen($msg));
echo $msg;
