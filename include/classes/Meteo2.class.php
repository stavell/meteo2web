<?php

namespace shumenxc;

use Facebook\Authentication\AccessTokenMetadata;
use Facebook\Authentication\OAuth2Client;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;

class Meteo2 {

    private static $nSpeedConstant = 6.5; //pulses per second for 1 m/s

    private static $fb = null;

    /**
     * @return Facebook
     */
    public static function getFB() {
        if(!self::$fb){
            self::$fb = new Facebook([
                'app_id' => '1085641581447273',
                'app_secret' => FB_APP_SECRET,
                'default_graph_version' => 'v2.4',
            ]);
        }
        return self::$fb;
    }

    public static function getPhotosForPeriod($from, $something = 60, $bAsc = true) {
        $aTimes = self::makeTimeFromTo($from, $something);

        return \DB::query(sprintf("
            SELECT
              UNIX_TIMESTAMP(f.created_time) as timestamp,
              file2url(f.id) AS url
            FROM files f

            WHERE 1
            AND f.created_time BETWEEN '{$aTimes['timeFrom']}' AND '{$aTimes['timeTo']}'
            ORDER BY f.created_time %s
        ", $bAsc ? 'ASC' : 'DESC'));
    }

    //6.5 oborota na propelera za sek = 1 m/s
    //from mysql date or php strtotime argument
    public static function getWeatherDataForPeriod($from, $something = 60, $nSegments = 10, $bAsc = false) {

        $aTimes = self::makeTimeFromTo($from, $something);

        $nSpeedConstant = floatval(self::$nSpeedConstant);

        $sQuery = "
           SELECT
               ROUND(AVG(d.temperature),1)		                    AS temperature,
               CEIL(AVG(d.humidity))		 	                    AS humidity,
               ROUND(AVG(d.pressure),1)   		                    AS pressure,
               CEIL(
                   DEGREES(
                           ATAN2(
                               AVG(SIN(RADIANS(d.wind_dir))),
                               AVG(COS(RADIANS(d.wind_dir)))
                           )
                   )
               )                                                    AS wind_dir,
               dd.dir                                               AS wind_dir_sym,
               ROUND((SUM(d.wind_count) / SUM(d.samples)/{$nSpeedConstant}),1)    AS wind_count,
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

        $aResult = \DB::query(sprintf($sQuery, (int)$aTimes['period'], (int)$nSegments, $aTimes['timeFrom'], $bAsc ? 'ASC' : 'DESC'));

        foreach($aResult as $k => $v) $aResult[$k]['wind_dir'] = $v['wind_dir'] <= 0 ? 360 + $v['wind_dir'] : $v['wind_dir'];

        return $aResult;
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
        if(count($aPieces) < count($aMap)) throw new XCInvalidRequest("Invalid Number of dataparams");

        $aData = array('id'=>0);
        foreach($aPieces as $piece) {
            $aPiece = explode(':',$piece);
            if(count($aPiece) != 2) throw new XCException("Broken data");
            $aData[$aMap[$aPiece[0]]] = $aPiece[1];
        }

        $aData['created_time'] = date("Y-m-d H:i:s");

        \DB::insert('data', $aData);

        $nSpeedConstant = floatval(self::$nSpeedConstant);

        self::notifyBroadcaster('weatherData', \DB::queryFirstRow("
               SELECT
               d.temperature,
               d.humidity,
               d.pressure,
               d.wind_dir,
               ROUND(SUM(d.wind_count) / {$nSpeedConstant}, 1)      AS wind_speed,
               UNIX_TIMESTAMP(d.created_time)	                    AS timestamp
               FROM data d
               WHERE d.id =
        ".\DB::insertId()));

        return array($aData);
    }


    public function getStationSettings() {
        return \DB::query('SELECT s.key, s.value as value FROM settings s where disabled != 1');
    }


    public static function notifyBroadcaster($sTarget = '', $aData) {
        if(empty($aData)) return;

        try{
            $ch = curl_init();
            if(!$ch) throw new XCException("Curl init fail");

            $sJSON = json_encode($aData, JSON_NUMERIC_CHECK);

            curl_setopt($ch, CURLOPT_URL, '127.0.0.1/'.$sTarget);
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

    private static function makeTimeFromTo($timeFrom, $something) {
        if($something == 'null' || empty($something)) $something = 60;
        $aResult['timeFrom'] = date('Y-m-d H:i:s', strtotime($timeFrom));
        $aResult['timeTo']   = date('Y-m-d H:i:s', strtotime($something) > 10000 ? strtotime($something) : (intval($something)*60) + strtotime($timeFrom));
        $aResult['period']   = ceil((strtotime($aResult['timeTo']) - strtotime($aResult['timeFrom'])) / 60);

        if( empty($aResult['timeFrom']) || empty($aResult['timeTo']) || $aResult['period'] <= 0 || $aResult['period'] > 30*24*60) throw new XCInvalidParam("Invalid time period");

        return $aResult;
    }

    public function getFBLoginURL() {
        $helper = self::getFB()->getRedirectLoginHelper();
        $permissions = ['email','public_profile'];
        return $helper->getLoginUrl('http://stavl.com/meteo2/fb-callback.php', $permissions);
    }


    public function getCurrentFBUserInfo() {
        if(empty($_SESSION['fb_access_token'])) return false;
        return $this->getFBUserInfo($_SESSION['fb_access_token']);
    }

    public function getFBUserInfo($authToken) {
        if(empty($authToken)) throw new \Exception("No auth token");

        /** @var FacebookResponse $response */
        $response = self::getFB()->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);

        /** @var GraphUser $user */
        $user = $response->getGraphUser();

        /** @var OAuth2Client $oAuth2Client */
        $oAuth2Client = self::getFB()->getOAuth2Client();

        /** @var AccessTokenMetadata $tokenMetadata */
        $tokenMetadata = $oAuth2Client->debugToken($authToken);

        return array(
            'user' => $user->asArray(),
            'tokenMetadata' => $tokenMetadata->getMetadata(),
        );
    }


} 
