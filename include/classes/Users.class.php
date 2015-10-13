<?php
namespace shumenxc;

class Users {

    public static function handleOAuthLogin($params){
        if(empty($params['provider'])) throw new XCException('No OAuth provider specified');

        switch($params['provider']) {
            case 'facebook':
                self::logUser(FacebookIntegration::handleLogin());
                break;
            default:
                return;
        }
    }

    protected static function logUser($userInfo){
        if(empty($userInfo['user_id']) || empty($userInfo['provider'])) throw new XCException("Empty user data");

        $userInfo = array(
            'provider' => $userInfo['provider'],
            'user_id'  => $userInfo['user_id'],
            'name'     => $userInfo['name'],
            'email'    => $userInfo['email'],
            'token'    => $userInfo['token']
        );

       \DB::insertUpdate('users', $userInfo);

       $_SESSION['user'] = $userInfo;
    }

    public static function getCurrentUserBaseInfo() {
        if(empty($_SESSION['user'])) throw new XCException("Not logged in");
        return [
            'provider' => $_SESSION['user']['provider'],
            'name'     => $_SESSION['user']['name']
        ];
    }

    public function logout() {
        $_SESSION['user'] = null;
        return true;
    }

    public function getLoginURLs() {
        return [
            'facebook' => FacebookIntegration::getLoginURL()
        ];
    }

} 
