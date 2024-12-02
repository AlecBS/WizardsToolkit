<?php
/**
* Wizard's Toolkit functions for Mobile Push Notifications.
*
* These have not been tested in years and need to be reviewed and verified.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2024, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

/**
* Pass in values and this function will trigger Android or Apple Push notification
*
* wtkMobilePush table needs to be found/created.
* Everything needs to be retested because this code is so old.
*
* @param string $fncUserId
* @param string $fncDeviceID
* @param string $fncMsg
* @param string $fncPlatform defaults to 'ios'
* @param string $fncTitle
* @param string $fncSubTitle
* @param string $fncType
* @param string $fncUID
* @param string $fncTickerText
* @param string $fncVibrate
* @param string $fncSound
* @param string $fncLargeIcon defaults to blank
* @param string $fncSmallIcon defaults to blank
* @uses function wtkAndroidPush
* @uses function wtkApplePush
* @return null
*/
function wtkMobilePNpush($fncUserId, $fncDeviceID, $fncMsg = '', $fncPlatform = 'ios', $fncTitle = '', $fncSubTitle = '',
    $fncType = 'NULL', $fncUID = 'NULL', $fncTickerText = '',
    $fncVibrate = 0, $fncSound = 0, $fncLargeIcon = '', $fncSmallIcon = '') {
/* ----------------- ABS 08/21/15 -------------------
Android Information
API Key: yourKeyHere
Use this API key when making request to the GCM

iOS Information
Password for the certificate : yourCertPW
 --------------------------------------------------*/
    $fncSQL  = 'INSERT INTO `wtkMobilePush` (`UserUID`, `MobilePlatform`, `DeviceID`, `Title`, `SubTitle`, `Message`, `MsgType`, `OtherUID`, `TickerText`, `Vibrate`, `Sound`, `LargeIcon`, `SmallIcon`, `SentDate`)';
    $fncSQL .= " VALUES (" . $fncUserId . ",'" . $fncPlatform . "','" . $fncDeviceID . "','" . $fncTitle . "','" . $fncSubTitle . "','";
    $fncSQL .= wtkEscapeStringForDB($fncMsg) . "','" . $fncType . "'," . $fncUID . ", '" . $fncTickerText . "','" . $fncVibrate . "','" . $fncSound . "','" . $fncLargeIcon . "','" . $fncSmallIcon . "', NOW())";
    $fncSQL  = wtkReplace($fncSQL, "'NULL'", 'NULL');
    // ABS 10/10/19  changed mysql_real_escape_string to wtkEscapeStringForDB
    wtkSqlExec($fncSQL);

    // ABS 08/22/15  need to capture errors better 2FIX
    if ($fncPlatform == 'ios'):
        $fncBadgeCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkMobilePush` WHERE `ReadDate` IS NULL AND `UserUID` = ' . $fncUserId);
        $fncResult = wtkApplePush($fncDeviceID, $fncMsg, $fncSound, $fncLargeIcon, $fncBadgeCount );
    else:   // Not $fncPlatform == 'ios'
        // prep the bundle
        $fncAndroidMsg = array (
            'message'    => $fncMsg,
            'title'      => $fncTitle,
            'subtitle'   => $fncSubTitle,
            'tickerText' => $fncTickerText,
            'vibrate'    => $fncVibrate,
            'sound'      => $fncSound,
            'largeIcon'  => $fncLargeIcon,
            'smallIcon'  => $fncSmallIcon
        );
//        $fncDeviceID = json_encode($fncDeviceID);
        $fncAndroidArrays = array (
            'registration_ids'  => array($fncDeviceID),
            'data'              => $fncAndroidMsg
        );
        $fncResult = wtkAndroidPush($fncAndroidArrays) ;
    endif;  // $fncPlatform == 'ios'
    // currently not using $fncResult but may later
}  // end of wtkMobilePNpush

/**
* With Google API_ACCESS_KEY this uses cURL to Push to Android
*
* @param array $fncAndroidArrays which is defined in wtkMobilePNpush
* @link https://android.googleapis.com/gcm/send
* @return result from curl
*/
function wtkAndroidPush($fncAndroidArrays) {
    global $gloGoogleApiKey;
    define('API_ACCESS_KEY', $gloGoogleApiKey);   // API access key from Google API's Console

    $fncHeaders = array (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $fncHeaders );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fncAndroidArrays ) );
    $fncResult = curl_exec($ch );
    curl_close( $ch );
    return $fncResult;
}  // end of wtkAndroidPush

/**
* This handles Apple Push functionality
*
* Must set PEM and setProviderCertificatePassphrase
*
* @param string $fncDeviceID
* @param string $fncMessage
* @param string $fncSound
* @param string $fncLargeIcon defaults to blank
* @param string $fncBadge to 0
* @param string $fncCustom to blank
* @global sting $gloApnLog
* @return error or success from Apple
*/
function wtkApplePush($fncDeviceID, $fncMessage, $fncSound, $fncLargeIcon = '', $fncBadge = 0, $fncCustom = '') {
    global $gloApnLog;
    $gloApnLog = '';
    $fncBadge = intval($fncBadge);
    require_once('ApnsPHP/Autoload.php'); // Using Autoload all classes are loaded on-demand
    // Instantiate a new ApnsPHP_Push object
    $pgPEM = wtkGetParam('PEM');
    if ($pgPEM == ''):
        $pgPEM = 'putYourPEMhere';
    endif;  // $pgPEM == ''
    $pgAppleEnv = wtkGetParam('Env');
    //$pgAppleEnv = 'sandbox';  // quick way to put Mobile in sandbox mode.
    if ($pgAppleEnv == 'sandbox'):
        $push = new ApnsPHP_Push(
            ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
            _WTK_RootPATH . 'ApnsPHP/' . $pgPEM . '.pem'
        );
    else:   // Not $pgAppleEnv == 'sandbox'
        $push = new ApnsPHP_Push(
            ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
            _WTK_RootPATH . 'ApnsPHP/' . $pgPEM . '.pem'
        );
    endif;  // $pgAppleEnv == 'sandbox'
    // Set the Provider Certificate passphrase
    $push->setProviderCertificatePassphrase('YourPhraseHere');
    // Set the Root Certificate Autority to verify the Apple remote peer
    $push->setRootCertificationAuthority(_WTK_RootPATH . 'certs/entrust_root_certification_authority.pem');
    $push->connect();   // Connect to the Apple Push Notification Service
    // Instantiate a new Message with a single recipient
    $message = new ApnsPHP_Message($fncDeviceID); // ERC 09/03/15
    $message->setBadge($fncBadge);  // ERC 02/17/17 Sets the number of notifications on the badge on the app icon for ios.
    // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
    $fncResult = '';
    if ($fncCustom != ''):
        $message->setCustomIdentifier($fncCustom);   // over a ApnsPHP_Message object retrieved with the getErrors() message.
        $fncResult = 'Custom Identifer sent as: ' . $fncCustom ;
        // ABS 02/17/18  moved next two lines here instead of just before setExpiry
        $message->setCustomProperty('your_info', 'someValue'); // Set a custom property
    endif;  // $fncCustom != ''
    if ($fncLargeIcon > 0):
        $message->setBadge($fncLargeIcon);    // Set badge icon to "3"
    endif;  // $fncLargeIcon > 0
    $message->setText($fncMessage);     // Set a simple welcome text
    if ($fncSound > 0):
        $message->setSound();               // Play the default sound
    endif;  // $fncSound > 0
    // Set another custom property  $message->setCustomProperty('acme3', array('bing', 'bong'));
    $message->setExpiry(30);        // Set the expiry value to 30 seconds
    $push->add($message);           // Add the message to the message queue
    $push->send();                  // Send all messages in the message queue
    $push->disconnect();            // Disconnect from the Apple Push Notification Service

    $aErrorQueue = $push->getErrors();  // Examine the error message container
    if (!empty($aErrorQueue)):
//        var_dump($aErrorQueue);
        return $aErrorQueue;
    else:   // Not !empty($aErrorQueue)
        $gloApnLog .= "\n" . $fncResult;
        return $gloApnLog; // ABS 09/01/15  'Successful push to Apple';
    endif;  // !empty($aErrorQueue)
}  // end of wtkApplePush

wtkTimeTrack('End of Mobile.php');
?>
