<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 12/7/13
 * Time: 14:29
 */

namespace shumenxc;

class FileUpload {

    private $aDevice = array();

    public function __construct() {
        $this->aDevice = \DB::queryFirstRow("SELECT * FROM file_devices WHERE is_default = 1");
        if(empty($this->aDevice)) throw new XCException('No default file device');
    }

    public function uploadFile($sFileName,$sFilePath) {
        if(empty($sFileName) || empty($sFilePath)) throw new XCException('Bad upload data');

        $sUrl = sprintf("%s://%s:%s@%s/%s/%s",
            $this->aDevice['type'],
            urlencode($this->aDevice['username']),
            urlencode($this->aDevice['password']),
            $this->aDevice['address'],
            $this->aDevice['filepath'],
            urlencode($sFileName)
        );

        $handle = fopen($sUrl, "w");
        if(!$handle) throw new XCException('Can not open file device');

        fputs($handle,file_get_contents($sFilePath));

        fclose($handle);

        $aFile = array(
            'filename' => $sFileName,
            'id_device' => $this->aDevice['id']
        );

        \DB::insert('files', array($aFile));
        $aFile['id'] = \DB::insertId();

        return $aFile;
    }

} 