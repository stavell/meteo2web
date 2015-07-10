<?php
/**
 * Created by PhpStorm.
 * User: stavel
 * Date: 7/10/15
 * Time: 12:16
 */

$sSignInURL = 'https://www.vivaonline.bg/login/cgi-bin/sso.cgi';

$aData['temp'] = 'loginv.html';
$aData['lang'] = 'bg';
$aData['site'] = 'loginv.html';
$aData['op'] = '4';
$aData['taddr'] = 'https://www.vivaonline.bg/c/portal/login?esc=1&lang=bg';
$aData['lusername'] = 'svelchev@gmail.com';
$aData['password'] = 'Stanislav64';
$aData['tab'] = '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sSignInURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt( $ch, CURLOPT_COOKIESESSION, true );

curl_setopt($ch,CURLOPT_POST, count($aData));
curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($aData));


$t = microtime(true);

$r = curl_exec($ch);


$matches = array();
preg_match_all("/(&sid=)(?<sid>.*)(&)/",$r,$matches);

$sSID = reset($matches['sid']);


//////////
$aLog['esc'] = '1';
$aLog['lang'] = 'bg';
$aLog['sid'] = $sSID;
$aLog['lang'] = 'bg';
curl_setopt($ch, CURLOPT_URL, "https://www.vivaonline.bg/c/portal/login?esc=1&lang=bg&sid=$sSID&lang=bg");
curl_setopt($ch,CURLOPT_POST, count($aLog));
curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($aLog));

$r = curl_exec($ch);


////////
curl_setopt($ch, CURLOPT_URL, "https://www.vivaonline.bg/sms-centre");
$r = curl_exec($ch);
preg_match('/authToken="(?<token>.*?)"/', $r, $matches);
$sToken = $matches['token'];


$aSMSGetData['p_auth'] = $sToken;
$aSMSGetData['p_p_id'] = 'smsportlet_WAR_smsportlet';
$aSMSGetData['p_p_lifecycle'] = '1';
$aSMSGetData['p_p_state'] = 'normal';
$aSMSGetData['p_p_mode'] = 'view';
$aSMSGetData['p_p_col_id'] = 'column-2';
$aSMSGetData['p_p_col_pos'] = '3';
$aSMSGetData['p_p_col_count'] = '4';
$aSMSGetData['_smsportlet_WAR_smsportlet_jspPage'] = '/html/smsportlet/view.jsp';
$aSMSGetData['_smsportlet_WAR_smsportlet_redirect'] = 'https://www.vivaonline.bg/sms-centre?p_p_id=smsportlet_WAR_smsportlet&p_p_lifecycle=0&p_p_state=normal&p_p_mode=view&p_p_col_id=column-2&p_p_col_pos=3&p_p_col_count=4&_smsportlet_WAR_smsportlet_jspPage=%2Fhtml%2Fsmsportlet%2Fview.jsp';
$aSMSGetData['_smsportlet_WAR_smsportlet_javax.portlet.action'] = 'sendSms';

$aSMSFormData['_smsportlet_WAR_smsportlet_sendFrom'] = '876277058';
$aSMSFormData['_smsportlet_WAR_smsportlet_sendTo'] = '876277058';

$aSMSFormData['_smsportlet_WAR_smsportlet_select-contact'] = '0';
$aSMSFormData['_smsportlet_WAR_smsportlet_message'] = 'reboot';


curl_setopt($ch,CURLOPT_URL, "https://www.vivaonline.bg/sms-centre?".http_build_query($aSMSGetData));
curl_setopt($ch,CURLOPT_POST, count($aSMSFormData));
curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($aSMSFormData));

$r = curl_exec($ch);

echo strpos($r,"Съобщението е изпратено успешно!") ? "OK" : "Fail";