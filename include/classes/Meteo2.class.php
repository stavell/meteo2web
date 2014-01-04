<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/8/13
 * Time: 20:58
 */

namespace shumenxc;


class Meteo2 {


    public static function getPhotosForInterval($nFrom, $nTo = 0,$bAsc = true) {
        if(empty($nFrom)) throw new XCInvalidParam;

        $nTo = !empty($nTo) ? $nTo : time();

        return \DB::query(sprintf("
            SELECT
              UNIX_TIMESTAMP(f.created_time) as timestamp,
              file2url(f.id) AS url
            FROM files f
            WHERE 1
            AND f.created_time BETWEEN '%s' AND '%s'
            ORDER BY f.created_time %s
        ",date("Y-m-d H:i:s",$nFrom),date("Y-m-d H:i:s",$nTo), $bAsc?'ASC':'DESC'));

    }



    //from mysql date or php strtotime argument
    public static function getWeatherDataForInterval($from,$nPeriod = 60,$nSegments = 10) {
        $from = $from == date("Y-m-d H:i:s",strtotime($from)) ? $from : date("Y-m-d H:i:s",strtotime($from,time()));

        $sQuery = "
           SELECT
               ROUND(AVG(d.temperature),1)		AS temperature,
               CEIL(AVG(d.humidity))		 	AS humidity,
               ROUND(AVG(d.pressure),1) 		AS pressure,
               CEIL(ABS(
                       DEGREES(
                           ATAN2(
                               AVG(SIN(RADIANS(d.wind_dir))),
                               AVG(COS(RADIANS(d.wind_dir)))
                           )
                       )
                   )
               )			 					AS wind_dir,
               SUM(d.wind_count)				AS wind_count,
               SUM(d.samples)					AS samples,
               UNIX_TIMESTAMP(d.created_time)	AS timestamp
           FROM data_avg d
           JOIN (
               SELECT
                   @period:=%d,
                   @segments:=%d,
                   @timeFrom:='%s'
           ) t
           WHERE 1
               AND d.created_time BETWEEN BINARY @timeFrom AND BINARY DATE_ADD(@timeFrom,INTERVAL @period MINUTE)
           GROUP BY UNIX_TIMESTAMP(d.created_time) DIV ((@period DIV @segments)*60)
           ORDER BY d.created_time DESC
           LIMIT ".(int)$nSegments;

        return \DB::query(sprintf($sQuery,(int)$nPeriod,(int)$nSegments,$from));
    }



} 