<?php
mb_internal_encoding("UTF-8");

require_once('config.php');

class Autoloader {
    public static $aPaths = array();
    public static function autoload($sClassName) {
        if(strpos($sClassName,'shumenxc\\') === 0) {
            $sClassName = substr($sClassName,9);
            $aPaths = self::$aPaths['shumenxc\\'];
        } else {
            $aPaths = self::$aPaths[''];
        }
        if(!preg_match("/^[a-z0-9_]+$/i",$sClassName)) return false;
        foreach ($aPaths as $sPath) {
            if(file_exists($sPath.'/'.$sClassName.'.class.php')) {
                require_once($sPath.'/'.$sClassName.'.class.php');
                return true;
            }
        }
        return false;
    }
}
Autoloader::$aPaths = array(
    '' => array(
        '',
        'include',
        get_include_path(),
    ),
    'shumenxc\\' => array(
        'include/classes'
    )
);
spl_autoload_register(array('Autoloader','autoload'));

require_once __DIR__ . '/vendor/autoload.php';
