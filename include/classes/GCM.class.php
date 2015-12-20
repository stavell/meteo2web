<?php
namespace shumenxc;

class GCM {

    public static function registerDevice($token, $deviceBrand = null, $deviceModel = null, $androidVersion = null) {
        error_log(var_export(func_get_args(),1));
        return array(
            'token' => $token,
            'deviceBrand' => $deviceBrand,
            'deviceModel' => $deviceModel,
            'androidVersion' => $androidVersion
        );
    }



}