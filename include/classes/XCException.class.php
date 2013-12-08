<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/7/13
 * Time: 15:12
 */

namespace shumenxc;

class XCException extends \Exception {

    protected $additionalInfo = array();

    public function __construct($message = "", $query = "") {

        \DB::insert('logs',array(array(
            'message' => $message,
            'exception' => var_export(array(
                'request' => $_REQUEST,
                '_server' => $_SERVER,
                'trace' => debug_backtrace(),
                'additionalInfo' => $this->additionalInfo
            ),1)
        )));

        parent::__construct($this->message,$this->code);
    }

    protected function setAdditionalInfo($info) {
        $this->additionalInfo = $info;
    }

} 