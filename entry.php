<?php

header('Content-type: application/json');

require_once('classes/meekroDB.class.php');

try {

//    error_log("request!!!",0);
//	   error_log(var_export(array_merge($_POST,$_GET,$_FILES),true));
//	error_log(time());
//
	$aParams = $_REQUEST;

	$aAcceptedMethods = array('weatherData','weatherPhoto');

	if(empty($aParams['method'])) throw new Exception("EmptyMethod");

   	switch($aParams['method']) {
		case 'weatherPhoto':
 			
		
//			$data = base64_decode($aParams['file']);
//			$success = file_put_contents( $fileName = 'upload/photo_'.date('YmdHis').'.jpg', $data);


			 if(!empty($_FILES) && !$_FILES['file']['error'] ) {
       			 	$fileName = 'upload/photo_'.date('YmdHis').'.jpg';
        			 move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__).DIRECTORY_SEPARATOR.$fileName);
   			} else throw new Exception("Bad photo");

		
		
		break;
		case 'weatherData':
			$aMap = array('p'=>'pressure','h'=>'humidity','t'=>'temperature','w'=>'wind_dir','s'=>'wind_count');
			$aPieces = explode(',',$aParams['data']);
			if(count($aPieces) < count($aMap)) throw new Exception("Invalid Number of dataparams");
			
			$aData = array('id'=>0);
			foreach($aPieces as $piece) {
				$aPiece = explode(':',$piece);
				if(count($aPiece) != 2) throw new Exception("Broken data");
				$aData[$aMap[$aPiece[0]]] = $aPiece[1];				
			}

			$aData['created_time'] = date("Y-m-d H:i:s");
			DB::insert('data',array($aData));

			echo json_encode(array());
		break;
		default:
			throw new Exception('InavlidMethod');
	}



 } catch (Exception $e) {
	error_log($e->getMessage());
//	die(json_encode(array('error'=>$e->getMessage())));
}

  $aSettings = DB::query('SELECT s.key,s.value as value FROM shumenxc_meteo.settings s where disabled != 1');
  echo json_encode($aSettings,JSON_NUMERIC_CHECK);

