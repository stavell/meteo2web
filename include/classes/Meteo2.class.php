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


} 