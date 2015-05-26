<?php
use shumenxc as xc;
require_once('config.inc.php');

error_reporting(E_ALL);

header("Content-type: application/json; charset=UTF-8");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type,X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5,  Date, X-Api-Version, X-File-Name');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');

try {

    //legacy single request mode
    if(empty($_REQUEST['requests'])) {
        xc\Meteo2::notifyBroadcaster('requests', $_REQUEST);

        $sMethod = !empty($_REQUEST['method']) ? $_REQUEST['method'] : false;
        $aParams = !empty($_REQUEST['params']) ? json_decode($_REQUEST['params'],1) : array($_REQUEST);

        $oMeteo2 = new xc\Meteo2();
        if(!$sMethod || !method_exists($oMeteo2,$sMethod)) throw new xc\XCException('Invalid Method');

        echo json_encode(call_user_func_array(array($oMeteo2,$sMethod),$aParams),JSON_NUMERIC_CHECK);

    } else {
        //multple requests
        $aRequests = json_decode($_REQUEST['requests'], 1);
        $aResponse = array();

        foreach($aRequests as $k => $aRequest) {
            try{
                xc\Meteo2::notifyBroadcaster('requests', $aRequest);

                list($sClassName, $sMethodName) = explode('.',$aRequest['target']);

                $sClassName = 'shumenxc\\'.$sClassName;
                if(empty($sClassName) || !class_exists($sClassName, true)) {
                    throw new xc\XCException(sprintf("Клас \"%s\" не е намерен.", $sClassName));
                }

                $oClass = new $sClassName;

                if(!is_callable(array($oClass, $sMethodName))) throw new xc\XCException(sprintf("Метод \"%s\" не може да бъде извикан", $sMethodName));

                $aResponse[$k]['response'] = call_user_func_array(array($oClass, $sMethodName), $aRequest['params']);

            } catch( xc\XCException $e ) {
                $aResponse[$k]['error'] = $e->getJSObject();
            }
        }

        die(json_encode($aResponse, JSON_NUMERIC_CHECK));
    }

} catch (Exception $e) {
    echo json_encode($e->getMessage());
}
