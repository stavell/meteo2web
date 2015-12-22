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


    public static function ackMessage($messageID){
        $message = \DB::queryOneRow("select * from messages where message_id = $messageID");
        if(empty($messageID)) throw new XCInvalidParam;

        $message['receive_time'] = date('Y-m-d H:i:s');
        \DB::update('messages', $message);

        return true;
    }

    public static function notifyDevice($id, $payload) {
        $client = new Client();

        $data['message_id'] = sha1(json_encode($payload).time().microtime(1));
        $data['payload'] = json_encode($payload, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $token = \DB::queryFirstField("SELECT token FROM devices WHERE id = $id");
        if(empty($token)) throw new XCInvalidParam("No device found");

        /** @noinspection PhpUndefinedConstantInspection */
        $params = [
            'headers' => [
                'Authorization' => 'key='.GCM_AUTH
            ],
            'json' => [
                'to' => $token,
                'priority' => 'high',
                'data' => $data
            ]
        ];

        $response = $client->request('POST', 'https://gcm-http.googleapis.com/gcm/send', $params);

        $result = json_decode($response->getBody()->getContents(), true);

        if(empty($result['success'])) throw new XCInvalidParam;

        $message['message_id'] = $data['message_id'];
        $message['device_to'] = $id;
        $message['message'] = json_encode($params,JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        \DB::insertUpdate('messages', $message);

        return $result;
    }

}