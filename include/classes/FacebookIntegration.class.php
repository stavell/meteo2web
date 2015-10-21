<?php
namespace shumenxc;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;

class FacebookIntegration {

    private static $fb = null;

    /**
     * @return Facebook
     */
    private static function getFB() {
        if(self::$fb) return self::$fb;
        return self::$fb = new Facebook([
            'app_id'                => FB_APP_ID,
            'app_secret'            => FB_APP_SECRET,
            'default_graph_version' => 'v2.4',
        ]);
    }

    /**
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \Exception
     * @return array
     */
    public static function handleLogin(){
        try {
            $helper = self::getFB()->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            return self::getUserInfoFromToken($accessToken->getValue());
        } catch(FacebookResponseException $e) {
            throw $e;
        } catch(FacebookSDKException $e) {
            throw $e;
        } catch(\Exception $e){
            throw $e;
        }
    }

    public static function getLoginURL() {
        return self::getFB()->getRedirectLoginHelper()->getLoginUrl('http://stavl.com/meteo2/?provider=facebook', ['email','public_profile', 'publish_actions']);
    }

    /**
     * @param $accessToken
     * @return array
     */
    private static function getUserInfoFromToken($accessToken) {
        /** @var FacebookResponse $response */
        $response = self::getFB()->get('/me?fields=id,name,email', $accessToken);
        /** @var GraphUser $user */
        $user = $response->getGraphUser();

        return [
            'provider'     => 'facebook',
            'external_id'  => $user->asArray()['id'],
            'name'         => $user->asArray()['name'],
            'email'        => $user->asArray()['email'],
            'token'        => $accessToken,
        ];
    }

    public static function postPhoto($params) {
        return self::getFB()->post('/'.FB_PAGE_ID.'/photos', $params, $_SESSION['user']['token']);
    }

} 
