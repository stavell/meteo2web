<?php
namespace shumenxc;

class Devices {

    public static function statusUpdate($idDevice, $status, $info = '') {
        $message['device_id'] = $idDevice;
        $message['status'] = $status;
        $message['info'] = json_encode($info, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $message['created_time'] = date('Y-m-d H:i:s');
        \DB::insert('device_status_events', $message);
    }

    public static function takePhoto($idDevice, $cameraParams = []) {
        $payload['action'] = 'takePhoto';
        foreach (\DB::query("SELECT * FROM camera_settings WHERE disabled = 0 AND device_id = {$idDevice}") as $param) $payload['params'][$param['key']] = $param['value'];
        $payload['params'] = array_merge($payload['params'], $cameraParams);
        return Notifications::sendMessage($idDevice, $payload);
    }


}