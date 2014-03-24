<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/8/13
 * Time: 20:58
 */

namespace shumenxc;

class Meteo2 {

    public static function getPhotosForPeriod($from, $nPeriod = 60, $bAsc = true) {
        $from = $from == date("Y-m-d H:i:s",strtotime($from)) ? $from : date("Y-m-d H:i:s",strtotime($from,time()));

        return \DB::query(sprintf("
            SELECT
              UNIX_TIMESTAMP(f.created_time) as timestamp,
              file2url(f.id) AS url
            FROM files f
            JOIN (
              SELECT
                @timeFrom:='%s',
                @period:=%s
            ) t
            WHERE 1
            AND f.created_time BETWEEN BINARY @timeFrom AND BINARY DATE_ADD(@timeFrom,INTERVAL @period MINUTE)
            ORDER BY f.created_time %s
        ", $from, $nPeriod, $bAsc ? 'ASC' : 'DESC'));
    }

    //6.5 oborota na propelera za sek = 1 m/s
    //from mysql date or php strtotime argument
    public static function getWeatherDataForPeriod($from, $nPeriod = 60,$nSegments = 10, $bAsc = false) {
        $from = $from == date("Y-m-d H:i:s", strtotime($from)) ? $from : date("Y-m-d H:i:s", strtotime($from, time()));

        $sQuery = "
           SELECT
               ROUND(AVG(d.temperature),1)		                    AS temperature,
               CEIL(AVG(d.humidity))		 	                    AS humidity,
               ROUND(AVG(d.pressure))   		                    AS pressure,
               CEIL(
                    ABS(
                       DEGREES(
                           ATAN2(
                               AVG(SIN(RADIANS(d.wind_dir))),
                               AVG(COS(RADIANS(d.wind_dir)))
                           )
                       )
                   )
               )			 					                    AS wind_dir,
               dd.dir                                               AS wind_dir_sym,
               ROUND((SUM(d.wind_count) / SUM(d.samples)/6.5),1)    AS wind_count,
               SUM(d.samples)					                    AS samples,
               UNIX_TIMESTAMP(d.created_time)	                    AS timestamp,
               UNIX_TIMESTAMP(MIN(d.created_time))	                AS start_timestamp,
               UNIX_TIMESTAMP(MAX(d.created_time))	                AS end_timestamp,
               UNIX_TIMESTAMP(AVG(d.created_time))	                AS avg_timestamp
           FROM data_avg d
           LEFT JOIN directions dd ON dd.id = ROUND(d.wind_dir / 22.5,0)
           JOIN (
               SELECT
                   @period:=%d,
                   @segments:=%d,
                   @timeFrom:='%s'
           ) t
           WHERE 1
               AND d.created_time BETWEEN BINARY @timeFrom AND BINARY DATE_ADD(@timeFrom,INTERVAL @period MINUTE)
           GROUP BY UNIX_TIMESTAMP(d.created_time) DIV ((@period DIV @segments)*60)
           ORDER BY d.created_time %s
           LIMIT ".(int)$nSegments;

        return \DB::query(sprintf($sQuery, (int)$nPeriod, (int)$nSegments, $from, $bAsc ? 'ASC' : 'DESC'));
    }



    public function weatherPhoto() {
        if(!empty($_FILES) && !$_FILES['file']['error'] ) {

            $oFileUpload = new FileUpload();
            $oFileUpload->uploadFile(
                md5(file_get_contents($_FILES["file"]["tmp_name"])).'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),
                $_FILES["file"]["tmp_name"]
            );

            unlink($_FILES["file"]["tmp_name"]);

        } else throw new XCException("Bad photo");

        return $this->getStationSettings();
    }


    public function weatherData($aParams) {
        $aMap = array('p'=>'pressure','h'=>'humidity','t'=>'temperature','w'=>'wind_dir','s'=>'wind_count');
        $aPieces = explode(',',$aParams['data']);
        if(count($aPieces) < count($aMap)) throw new XCException("Invalid Number of dataparams");

        $aData = array('id'=>0);
        foreach($aPieces as $piece) {
            $aPiece = explode(':',$piece);
            if(count($aPiece) != 2) throw new XCException("Broken data");
            $aData[$aMap[$aPiece[0]]] = $aPiece[1];
        }

        $aData['created_time'] = date("Y-m-d H:i:s");

        \DB::insert('data',array($aData));
        self::notifyBroadcaster($aData);

        return array($aData);
    }


    public function getStationSettings() {
        return \DB::query('SELECT s.key, s.value as value FROM settings s where disabled != 1');
    }


    public static function notifyBroadcaster($aData) {
        if(empty($aData)) return;

        try{
            $ch = curl_init();
            if(!$ch) throw new XCException("Curl init fail");

            $sJSON = json_encode($aData, JSON_NUMERIC_CHECK);

            curl_setopt($ch, CURLOPT_URL, '127.0.0.1');
            curl_setopt($ch, CURLOPT_PORT, 8001);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sJSON);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($sJSON))
            );

            if(!curl_exec($ch)) throw new XCException("Curl exec fail");
            curl_close($ch);

        } catch( XCException $e) {}
    }

} 