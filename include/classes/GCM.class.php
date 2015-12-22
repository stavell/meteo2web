<?php
namespace shumenxc;

use GuzzleHttp\Client;

class GCM {

    public static function registerDevice($id = null, $token, $deviceBrand = null, $deviceModel = null, $androidVersion = null) {
        if(empty($token)) throw new XCInvalidParam("no token");

        $device = array(
            'id' => $id,
            'token' => $token,
            'deviceBrand' => $deviceBrand,
            'deviceModel' => $deviceModel,
            'androidVersion' => $androidVersion,
            'updated_time' => date('Y-m-d H:i:s')
        );

        \DB::insertUpdate('devices', $device);
        if(empty($id)) $id = \DB::insertId();

        return \DB::queryOneRow("select * from devices where id = $id");
    }


    public static function notifyDevice($id, $data) {
        $client = new Client();

        $token = \DB::queryFirstField("SELECT token FROM devices WHERE id = $id");
        if(empty($token)) throw new XCInvalidParam("No device found");

        /** @noinspection PhpUndefinedConstantInspection */
        $response = $client->request('POST', 'https://gcm-http.googleapis.com/gcm/send', [
            'headers' => [
                'Authorization' => 'key='.GCM_AUTH
            ],
            'json' => [
                'to' => $token,
                'priority' => 'high',
                'collapse_key' => sha1(time()),
                'data' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }


}