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

    protected $bLog = true;

    public function __construct($message = "", $query = "") {


        parent::__construct($message,$this->code);
    }

    protected function setAdditionalInfo($info) {
        $this->additionalInfo = $info;
    }

    public function getJSObject()
    {
        $aJSObj = array(
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        );
        return $aJSObj;
    }

} 
