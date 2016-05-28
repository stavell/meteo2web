<?php
namespace shumenxc;

class Notifications {

    public static function sendMessage($deviceID, $payload) {
        $token = \DB::queryFirstField("SELECT token FROM devices WHERE id = $deviceID");
        if(empty($token)) throw new XCInvalidParam("No device found");

        $payload['message_id'] = sha1(json_encode($payload).time().microtime(1));

        \DB::insertUpdate('messages', array(
            'message_id' =>  $payload['message_id'],
            'device_to' => $deviceID,
            'message' => json_encode($payload, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ));

        return GCM::notifyDeviceByToken($token, $payload);
    }


    public static function ackMessage($messageID) {
        $message = \DB::queryFirstRow("select message_id from messages where message_id = %s", $messageID);
        if(empty($message)) throw new XCInvalidParam;

        $message['receive_time'] = date('Y-m-d H:i:s');
        \DB::update('messages', $message, "message_id=%s", $messageID);

        return true;
    }

    public static function setResponse($messageID, $response) {
        $message = \DB::queryFirstRow("select message_id from messages where message_id = %s", $messageID);
        if(empty($message)) throw new XCInvalidParam;

        $message['response_time'] = date('Y-m-d H:i:s');
        $message['response'] = $response;

        \DB::update('messages', $message, "message_id=%s", $messageID);
        return true;
    }


}