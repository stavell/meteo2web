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

    public function __construct($nID = null) {
        $this->initDevice($nID);
    }

    private function initDevice($nID = null) {
        $this->aDevice = empty($nID) ? \DB::queryFirstRow("SELECT * FROM file_devices WHERE is_default = 1") : \DB::queryFirstRow("SELECT * FROM file_devices WHERE id = %d", $nID);
        if(empty($this->aDevice)) throw new XCException('Invalid file device');
    }

    public function uploadFile($sFileName, $sFilePath) {
        return $this->uploadFileContent($sFileName, file_get_contents($sFilePath));
    }


    public function uploadFileContent($sFileName, $content, $params = array()) {
        if(empty($sFileName) || empty($content)) throw new XCException('Bad upload data');

        $sUrl = sprintf("%s://%s:%s@%s/%s/%s",
            $this->aDevice['type'],
            urlencode($this->aDevice['username']),
            urlencode($this->aDevice['password']),
            $this->aDevice['address'],
            $this->aDevice['filepath'],
            urlencode($sFileName)
        );

        $handle = fopen($sUrl, "w");
        if(!$handle) throw new XCException('Can not open file device or file exists');

        fputs($handle, $content);
        fclose($handle);

        $aFile = array(
            'filename' => $sFileName,
            'id_device' => $this->aDevice['id']
        );
        $aFile = array_merge($aFile,$params);

        \DB::insert('files', array($aFile));
        $aFile['id'] = \DB::insertId();

        return $aFile;
    }

}