<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/7/13
 * Time: 15:12
 */

namespace shumenxc;

class XCInvalidRequest extends XCException {

    protected $message = "Invalid param";

    public function __construct($message = "", $code = "") {
        $this->bLog = false;
        parent::__construct($this->getMessage(),$this->code);
    }

} 