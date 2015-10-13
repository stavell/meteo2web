<?php
namespace shumenxc;

class Users {

    public static function handleOAuthLogin($params){
        if(empty($params['provider'])) return;

        switch($params['provider']) {
            case 'facebook':
                self::logUser(FacebookIntegration::handleLogin());
                break;
            default:
                return;
        }
    }

    protected static function logUser($userInfo){
        if(empty($userInfo['id']) || empty($userInfo['provider'])) throw new XCException("Empty user data");

        $userInfo = array(
            'provider' => $userInfo['provider'],
            'user_id'  => $userInfo['id'],
            'name'     => $userInfo['name'],
            'email'    => $userInfo['email'],
            'token'    => $userInfo['token']
        );

       \DB::insertUpdate('users', $userInfo);

       $_SESSION['user'] = $userInfo;
    }

    public static function getCurrentUser() {
        if(empty($_SESSION['user'])) throw new XCException("Not logged in");
        return $_SESSION['user'];
    }

    public function logout() {
        $_SESSION['user'] = null;
    }

    public function getLoginURLs() {
        return [
            'facebook' => FacebookIntegration::getLoginURL()
        ];
    }

} 
