<?php
use shumenxc as xc;

require_once(dirname(__FILE__) . '/config.inc.php');

$nLastPhotoTime = strtotime(DB::queryFirstField('SELECT created_time FROM files ORDER BY created_time desc limit 1'));
$nLastDataTime = strtotime(DB::queryFirstField('SELECT created_time FROM data ORDER BY created_time desc limit 1'));

$matches = array();
preg_match('/\d{0,}(?=%\s)/', shell_exec('df -h | grep ' . ROOT_DISK), $matches);
$diskUsagePercentage = intval(reset($matches));

$message = [];

if($nLastDataTime < strtotime("-10 min")) $message[] = 'No data';
if($nLastPhotoTime < strtotime("-20 min")) $message[] = 'No photos';
if($diskUsagePercentage >= 99) $message[] = 'Low disk space';

if(!empty($message)) {

    $mcache = new Memcache();
    $mcache->connect('127.0.0.1', 11211);

    $lastSMSTime = $mcache->get('lastSMSTime');

    if($lastSMSTime === false || (time() - $lastSMSTime) > (70 * 60)) {
        require_once 'smsReboot.php';
        var_dump(reboot());
        $mcache->set('lastSMSTime', time());
    }

//    $notification = [
//        'title' => 'Alert ' . date('H:i:s d.m.Y'),
//        'body' => implode(', ', $message),
//    ];
//
//    $token = reset(DB::queryFirstRow("SELECT token FROM devices WHERE id = 7"));
//    \shumenxc\GCM::notifyDeviceByToken($token, null, $notification, ['collapse_key' => 'server_alert']);
}