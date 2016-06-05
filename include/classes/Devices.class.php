<?php
namespace shumenxc;

class Devices {

    public static function statusUpdate($idDevice, $status, $info = ''){
        $message['device_id'] = $idDevice;
        $message['status'] = $status;
        $message['info'] = empty($info) ? '' : $info;
        $message['created_time'] = date('Y-m-d H:i:s');
        \DB::insert('device_status_events', $message);
    }

}