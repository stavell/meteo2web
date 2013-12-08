<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/7/13
 * Time: 15:12
 */

namespace shumenxc;

class XCException extends \Exception {


    public function __construct($message = "", $code = "") {

        \DB::insert('logs',array(array(
            'message' => $message,
            'exception' => var_export(array(
                'request' => $_REQUEST,
                '_server' => $_SERVER,
                'trace' => debug_backtrace()
            ),1)
        )));

        parent::__construct($this->message,$this->code);
    }

} 