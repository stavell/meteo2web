<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/7/13
 * Time: 15:12
 */

namespace shumenxc;

class XCInvalidParam extends XCException {

    protected $message = "Invalid param";

    public function __construct($message = "", $code = "") {
        parent::__construct($this->getMessage(),$this->code);
    }

} 