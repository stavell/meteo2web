<?php
namespace shumenxc;
use Facebook\Authentication\AccessToken;
use Facebook\Authentication\AccessTokenMetadata;
use Facebook\Authentication\OAuth2Client;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;

class FbUsers {

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


    /**
     * @param AccessToken $accessToken
     * @throws XCAuthFailed
     * @throws \Exception
     */
    public static function logUser(AccessToken $accessToken) {
        $_SESSION['fb_access_token'] = $accessToken->getValue();

        if(!self::getFB()->getOAuth2Client()->debugToken($accessToken)->getIsValid()) throw new XCAuthFailed;

        if(!$accessToken->isLongLived()) {
            try {
                $accessToken = self::getFB()->getOAuth2Client()->getLongLivedAccessToken($accessToken);
            } catch (\Exception $e) {}
        }

        $userInfo = self::getUserInfo($accessToken);
        $_SESSION['user'] = $userInfo;

        $user = array(
            'user_id' => $userInfo['user']['id'],
            'name'    => $userInfo['user']['name'],
            'email'   => $userInfo['user']['email'],
            'token'   => $accessToken->getValue(),
            'token_issued_at'  => date('Y-m-d H:i:s',$userInfo['token_issued_at']),
            'token_expires_at' => date('Y-m-d H:i:s',$userInfo['token_expires_at']),
        );

        \DB::insertUpdate('fb_users', $user);
    }

    public static function isLoggedIn() {
        if(empty($_SESSION['fb_access_token'])) throw new XCAuthFailed();

        $user = \DB::queryFirstRow("SELECT user_id FROM fb_users WHERE token = '{$_SESSION['fb_access_token']}' AND token_expires_at > NOW()");
        if(empty($user)) throw new XCAuthFailed;
    }

    public function getLoginURL() {
        $helper = self::getFB()->getRedirectLoginHelper();
        $permissions = ['email','public_profile'];
        return $helper->getLoginUrl('http://stavl.com/meteo2/fb-callback.php?redirect_hash=fblogin', $permissions);
    }

    public function getCurrentUserInfo() {
        self::isLoggedIn();
        return $this->getUserInfo($_SESSION['fb_access_token']);
    }

    public static function getUserInfo($accessToken) {
        /** @var FacebookResponse $response */
        $response = self::getFB()->get('/me?fields=id,name,email', $accessToken);

        /** @var GraphUser $user */
        $user = $response->getGraphUser();

        /** @var OAuth2Client $oAuth2Client */
        $oAuth2Client = self::getFB()->getOAuth2Client();

        /** @var AccessTokenMetadata $tokenMetadata */
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        return array(
            'user' => $user->asArray(),
            'token_issued_at' => $tokenMetadata->getIssuedAt() ? $tokenMetadata->getIssuedAt()->getTimestamp() : 0,
            'token_expires_at' => $tokenMetadata->getExpiresAt() ? $tokenMetadata->getExpiresAt()->getTimestamp() : 0,
        );
    }

    public function logOut() {

    }

} 
