<?php
    use shumenxc as xc;
    require_once('config.inc.php');

    header("Content-type: text/json; charset=UTF-8");

try {
    $oMeteo2 = new xc\Meteo2();
    $sMethod = !empty($_REQUEST['method']) ? $_REQUEST['method'] : false;
    $aParams = !empty($_REQUEST['params']) ? $_REQUEST['params'] : array();

    if(!$sMethod || !method_exists($oMeteo2,$sMethod)) throw new xc\XCException('Invalid Method');

    echo json_encode(call_user_func_array(array($oMeteo2,$sMethod),$aParams),JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode($e->getMessage());
}


