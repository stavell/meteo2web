<?php
use shumenxc as xc;
require_once(dirname(__FILE__).'/config.inc.php');

$nLastPhotoTime = strtotime(DB::queryFirstField('SELECT created_time FROM files ORDER BY created_time desc limit 1'));
$nLastDataTime = strtotime(DB::queryFirstField('SELECT created_time FROM data ORDER BY created_time desc limit 1'));

$matches=array();
preg_match('/\d{0,}(?=%\s)/', shell_exec('df -h | grep '.ROOT_DISK), $matches);
$diskUsagePercentage = intval(reset($matches));


if($nLastDataTime < strtotime("-5 min") || $nLastPhotoTime < strtotime("-15 min") || $diskUsagePercentage >= 90) {
    mail("svelchev@gmail.com, stavel@icloud.com, zlati.dimitrov@gmail.com", "Shumen-XC Meteo Alert", "Disk usage: ".$diskUsagePercentage."%"."\n"."Last photo: ".date("Y-m-d H:i:s",$nLastPhotoTime)."\n"."Last data: ".date("Y-m-d H:i:s",$nLastDataTime)."\n"."Send SMS to +359876277058 to reboot. \n https://www.vivaonline.bg \n svelchev@gmail.com / Stanislav64");
}

echo date("Y-m-d H:i:s");