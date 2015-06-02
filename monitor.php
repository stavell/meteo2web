<?php
use shumenxc as xc;
require_once(dirname(__FILE__).'/config.inc.php');

$nLastPhotoTime = strtotime(DB::queryFirstField('SELECT created_time FROM files ORDER BY created_time desc limit 1'));
$nLastDataTime = strtotime(DB::queryFirstField('SELECT created_time FROM data ORDER BY created_time desc limit 1'));

if($nLastDataTime < strtotime("-5 min") || $nLastPhotoTime < strtotime("-15 min") ) {
    mail("svelchev@gmail.com, stavel@icloud.com", "Shumen-XC Meteo Alert", "Last photo: ".date("Y-m-d H:i:s",$nLastPhotoTime)."\n"."Last data: ".date("Y-m-d H:i:s",$nLastDataTime));
}

echo date("Y-m-d H:i:s");