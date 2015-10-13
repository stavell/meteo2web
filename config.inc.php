<?php
mb_internal_encoding("UTF-8");

define('BASE_PATH',dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.BASE_PATH.'/');
session_start();

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
        BASE_PATH.'/',
        BASE_PATH.'/include',
        BASE_PATH.get_include_path(),
        get_include_path(),
    ),
    'shumenxc\\' => array(
        BASE_PATH.'/include/classes'
    )
);

spl_autoload_register(array('Autoloader','autoload'));

require_once BASE_PATH . '/vendor/autoload.php';
