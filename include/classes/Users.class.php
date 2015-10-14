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

       \DB::insertUpdate('users', $userInfo);
       $user = \DB::queryFirstRow("SELECT * FROM users WHERE external_id = %s AND provider=%s", $userInfo['external_id'], $userInfo['provider']);

       if(empty($user)) throw new XCException("DB user not found");
       $_SESSION['user'] = $user;
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
    }

    public static function checkIsLoggedIn(){
        return !empty($_SESSION['user']);
    }

    public function getLoginURLs() {
        return [
            'facebook' => FacebookIntegration::getLoginURL()
        ];
    }

} 
