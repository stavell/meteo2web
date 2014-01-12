<?php
require_once('config.inc.php');
header('Content-type: application/json');

use shumenxc as xc;

$oResponse = array();

try {

    $aParams = $_REQUEST;

    $aAcceptedMethods = array('weatherData','weatherPhoto');

    if(empty($aParams['method'])) throw new xc\XCException("EmptyMethod");

    switch($aParams['method']) {

        case 'weatherPhoto':

            if(!empty($_FILES) && !$_FILES['file']['error'] ) {

                $oFileUpload = new xc\FileUpload();
                $oFileUpload->uploadFile(
                    MD5(file_get_contents($_FILES["file"]["tmp_name"])).'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),
                    $_FILES["file"]["tmp_name"]
                );

            } else throw new xc\XCException("Bad photo");

            break;
        case 'weatherData':
            $aMap = array('p'=>'pressure','h'=>'humidity','t'=>'temperature','w'=>'wind_dir','s'=>'wind_count');
            $aPieces = explode(',',$aParams['data']);
            if(count($aPieces) < count($aMap)) throw new xc\XCException("Invalid Number of dataparams");

            $aData = array('id'=>0);
            foreach($aPieces as $piece) {
                $aPiece = explode(':',$piece);
                if(count($aPiece) != 2) throw new xc\XCException("Broken data");
                $aData[$aMap[$aPiece[0]]] = $aPiece[1];
            }

            $aData['created_time'] = date("Y-m-d H:i:s");
            DB::insert('data',array($aData));

            echo json_encode(array());
            break;
        default:
            throw new xc\XCException('InavlidMethod');
    }


} catch (xc\XCException $e) {
	die(json_encode($e->getMessage()));
}

$aSettings = DB::query('SELECT s.key,s.value as value FROM shumenxc_meteo.settings s where disabled != 1');

echo json_encode($aSettings,JSON_NUMERIC_CHECK);

