<?php

namespace shumenxc;

use GuzzleHttp\Client;

class GCM {

    public static function registerDevice($key = null, $token, $deviceBrand = null, $deviceModel = null, $androidVersion = null, $cameraParams = null) {
        if(empty($token)) throw new XCInvalidParam("no token");
        if(empty($key)) throw new XCInvalidParam("no key");

        $device = Devices::getDeviceByKey($key);
        if(empty($device)) throw new XCInvalidParam("No device with key:" . $key);

        $device['token'] = $token;
        $device['deviceBrand'] = $deviceBrand;
        $device['deviceModel'] = $deviceModel;
        $device['androidVersion'] = $androidVersion;
        $device['cameraParams'] = $cameraParams;
        $device['updated_time'] = date('Y-m-d H:i:s');

        \DB::insertUpdate('devices', $device);

        return Devices::getDeviceByKey($key);
    }

    public static function notifyDeviceByToken($token, $payload = null, $notification = null, $additionalParams = null) {
        $client = new Client(['verify' => false]);

        /** @noinspection PhpUndefinedConstantInspection */
        $params = [
            'headers' => [
                'Authorization' => 'key=' . GCM_AUTH
            ],
            'json' => [
                'to' => $token,
                'priority' => 'high'
            ]
        ];

        if(!empty($payload)) $params['json']['data'] = $payload;
        if(!empty($notification)) $params['json']['notification'] = $notification;
        if(!empty($additionalParams)) $params['json'] = array_merge($params['json'], $additionalParams);

        $response = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', $params);
        $result = json_decode($response->getBody()->getContents(), true);

        if(empty($result['success'])) throw new XCInvalidParam(print_r($result,true));

        return $result;
    }

}