<?php
namespace shumenxc;

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



}