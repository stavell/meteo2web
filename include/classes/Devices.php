<?php
namespace shumenxc;

class Devices {

    public function statusUpdate($idDevice, $statusInfo){
        $message['device_id'] = $idDevice;
        $message['status'] = $statusInfo;
        \DB::insertUpdate('device_status_events', $message);
    }

}