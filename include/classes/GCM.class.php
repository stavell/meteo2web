<?php
namespace shumenxc;

use GuzzleHttp\Client;

class GCM {

    public static function registerDevice($id = null, $token, $deviceBrand = null, $deviceModel = null, $androidVersion = null, $cameraParams = null) {
        if(empty($token)) throw new XCInvalidParam("no token");

        $device = array(
            'id' => $id,
            'token' => $token,
            'deviceBrand' => $deviceBrand,
            'deviceModel' => $deviceModel,
            'androidVersion' => $androidVersion,
            'cameraParams' => $cameraParams,
            'updated_time' => date('Y-m-d H:i:s')
        );

        \DB::insertUpdate('devices', $device);
        if(empty($id)) $id = \DB::insertId();

        return \DB::queryOneRow("select * from devices where id = $id");
    }

    public static function notifyDeviceByToken($token, $payload) {
        $client = new Client();

        /** @noinspection PhpUndefinedConstantInspection */
        $params = [
            'headers' => [
                'Authorization' => 'key='.GCM_AUTH
            ],
            'json' => [
                'to' => $token,
                'priority' => 'high',
                'data' => $payload
            ]
        ];

        $response = $client->request('POST', 'https://gcm-http.googleapis.com/gcm/send', $params);
        $result = json_decode($response->getBody()->getContents(), true);

        if(empty($result['success'])) throw new XCInvalidParam;

        return $result;
    }

}