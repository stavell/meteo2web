<?php

namespace shumenxc;

class Notifications {

    private static function generateMessageID($payload) {
        return sha1(json_encode($payload) . time() . microtime(1));
    }

    public static function sendMessage($deviceID, $payload) {
        \DB::startTransaction();
        $token = \DB::queryFirstField("SELECT token FROM devices WHERE id = $deviceID");
        if(empty($token)) throw new XCInvalidParam("No device found");

        $payload['message_id'] = self::generateMessageID($payload);

        \DB::insert('messages', array(
            'message_id' => $payload['message_id'],
            'destination_device_id' => $deviceID,
            'message' => json_encode($payload, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ));

        GCM::notifyDeviceByToken($token, $payload);
        \DB::commit();
        return $payload['message_id'];
    }

    public static function ackMessage($messageID) {
        $message = \DB::queryFirstRow("select message_id from messages where message_id = %s", $messageID);
        if(empty($message)) throw new XCInvalidParam;

        $message['receive_time'] = date('Y-m-d H:i:s');
        \DB::update('messages', $message, "message_id=%s", $messageID);

        return true;
    }

    public static function respondToMessage($messageID, $response) {
        $message = \DB::queryFirstRow("select message_id from messages where message_id = %s", $messageID);
        if(empty($message)) throw new XCInvalidParam;

        $message['response_time'] = date('Y-m-d H:i:s');
        $message['response'] = $response;

        \DB::update('messages', $message, "message_id=%s", $messageID);
        return true;
    }


    public static function getMessage($messageID) {
        if(empty($messageID)) throw new XCInvalidParam();
        $json = \DB::queryOneField("SELECT message FROM messages WHERE message_id = %s", $messageID);
        if(empty($json)) throw new XCInvalidParam();
        return json_decode($json, JSON_UNESCAPED_UNICODE);
    }


}