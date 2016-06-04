<?php
namespace shumenxc;

class Devices {

    public function statusUpdate($idDevice, $status, $info = ''){
        $message['device_id'] = $idDevice;
        $message['status'] = $status;
        $message['info'] = $info;
        \DB::insertUpdate('device_status_events', $message);
    }

}