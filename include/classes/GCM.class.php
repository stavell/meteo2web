<?php
namespace shumenxc;

use GuzzleHttp\Client;

class GCM {

    public static function registerDevice($id = null, $token, $identificator, $deviceBrand = null, $deviceModel = null, $androidVersion = null, $cameraParams = null) {
        if(empty($token)) throw new XCInvalidParam("no token");

        $device = array(
            'id' => $id,
            'deviceID' => $identificator,
            'token' => $token,
            'deviceBrand' => $deviceBrand,
            'deviceModel' => $deviceModel,
            'androidVersion' => $androidVersion,
            'cameraParams' => $cameraParams,
            'updated_time' => date('Y-m-d H:i:s')
        );

        \DB::insertUpdate('devices', $device);

        return \DB::queryOneRow("select * from devices where deviceID = %s", $identificator);
    }

    public static function notifyDeviceByToken($token, $payload = null, $notification = null, $additionalParams = null) {
        $client = new Client(['verify' => false]);

        /** @noinspection PhpUndefinedConstantInspection */
        $params = [
            'headers' => [
                'Authorization' => 'key='.GCM_AUTH
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

        if(empty($result['success'])) throw new XCInvalidParam;

        return $result;
    }

}