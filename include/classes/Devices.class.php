<?php

namespace shumenxc;

class Devices {

    public static function statusUpdate($request) {
        $deviceID = \DB::queryOneField('id', "SELECT id FROM devices WHERE `key` = %s", $request['key']);
        if(empty($deviceID)) throw new XCInvalidParam();
        $message['device_id'] = $deviceID;
        $message['status'] = $request['status'];
        $message['info'] = json_encode($request['info'], JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $message['created_time'] = $request['timestamp'] ? date('Y-m-d H:i:s', $request['timestamp']) : date('Y-m-d H:i:s');
        \DB::insert('device_status_events', $message);
    }

    public static function takePhoto($idDevice, $cameraParams = []) {
        $payload['action'] = 'takePhoto';
        foreach (\DB::query("SELECT * FROM camera_settings WHERE disabled = 0 AND device_id = {$idDevice}") as $param) $payload['params'][$param['key']] = $param['value'];
        $payload['params'] = array_merge($payload['params'], $cameraParams);
        return Notifications::sendMessage($idDevice, $payload);
    }



}