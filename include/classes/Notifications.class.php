<?php
namespace shumenxc;

class Notifications {

    public static function sendMessage($deviceID, $payload) {
        $token = \DB::queryFirstField("SELECT token FROM devices WHERE id = $deviceID");
        if(empty($token)) throw new XCInvalidParam("No device found");

        $data['message_id'] = sha1(json_encode($payload).time().microtime(1));
        $data['payload'] = json_encode($payload, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        \DB::insertUpdate('messages', array(
            'message_id' => $data['message_id'],
            'device_to' => $deviceID,
            'message' => $data['payload']
        ));

        return GCM::notifyDeviceByToken($token, $data);
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