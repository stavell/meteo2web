<?php
use shumenxc as xc;
require_once(dirname(__FILE__).'/config.inc.php');

$nLastPhotoTime = strtotime(DB::queryFirstField('SELECT created_time FROM files ORDER BY created_time desc limit 1'));
$nLastDataTime = strtotime(DB::queryFirstField('SELECT created_time FROM data ORDER BY created_time desc limit 1'));

$matches=array();
preg_match('/\d{0,}(?=%\s)/', shell_exec('df -h | grep '.ROOT_DISK), $matches);
$diskUsagePercentage = intval(reset($matches));


if($nLastDataTime < strtotime("-10 min")) {
    xc\Notifications::sendMessage(2, array('message'=>"No data coming"));
} elseif($nLastPhotoTime < strtotime("-20 min")){
    xc\Notifications::sendMessage(2, array('message'=>"No photos coming"));
} else if($diskUsagePercentage >= 99) {
    xc\Notifications::sendMessage(2, array('message'=>"Disk usage alert"));
}