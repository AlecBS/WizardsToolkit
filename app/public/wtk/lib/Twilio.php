<?php
/**
* This contains Wizard's Toolkit functions for sending SMS via Twilio
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
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

/**
* This uses Twilio to send SMS and stores message in.
*
* This will strip out non-numeric characters in phone number. If stripped phone number
* passed is not 10 or 11 digits an SMS will not be sent and an email alert will be sent to $gloTechSupport .
* If Twilio returns "pair violates a blacklist rule" this will also email $gloTechSupport .
* This is usually because phone owner blocked SMS from this number.
* Any other type of error will be logged into wtkErrorLog.
* After sending text, this stores SMS sent into `wtkSMSsent` data table.
*
* @param string $fncToPhone phone number to send text to
* @param string $fncMessage message sent via SMS
* @param string $fncSubject only used for saving in wtkSMSsent.SMSSubject for analytics, defaults to 'SMS'
* @param string $fncFromUserUID defaults to 0 for Server, can pass in wtkUsers.UID
* @param string $fncToUserUID defaults to NULL; can send wtkUsers.UID for tracking
* @global string $gloTwilioPhone your Twilio From Phone number defined in wtk/wtkServerInfo
* @global string $gloTwilioSID our Account SID from www.twilio.com/user/account ; defined in wtk/wtkServerInfo
* @global string $gloTwilioToken our Auth Token from www.twilio.com/user/account ; defined in wtk/wtkServerInfo
* @return null
*/
use Twilio\Rest\Client;

function wtkSendSMS($fncToPhone, $fncMessage, $fncSubject = 'SMS', $fncFromUserUID = 0, $fncToUserUID = 'NULL') {
    global $gloTwilioPhone, $gloTwilioSID, $gloTwilioToken, $gloTechPhone;
    if ($fncToUserUID != 'NULL'):
        $fncSaveArray = array (
            'FromUID' => $fncFromUserUID,
            'ToUID' => $fncToUserUID
        );
    else:
        $fncSaveArray = array (
            'FromUID' => $fncFromUserUID
        );
    endif;
    $fncPhone = preg_replace("/[^0-9]/", '', $fncToPhone);
    $fncLength = strlen($fncPhone);
    if ($fncLength == 10):
        $fncPhone = '+1' . $fncPhone;
    elseif ($fncLength == 11):
        $fncPhone = '+' . $fncPhone;
    endif;  // $fncLength == 10
    if (($fncLength == 10) || ($fncLength == 11)):
        require_once('../' . _RootPATH . '/twilio/src/Twilio/autoload.php');
        $fncTwilio = new Client($gloTwilioSID, $gloTwilioToken);
        try {
            // BEGIN for testing, use special phone #
            global $gloDbConnection;
            if ($gloDbConnection != 'Live'):
                switch ($fncPhone):
                    case '+1' . $gloTechPhone:
                        // do nothing, this is testing for developers
                        break;
                    default:
                        $fncPhone = '+1' . $gloTechPhone;
                        $fncMessage = 'TST: ' . $fncMessage;
                        break;
                endswitch;
            endif;
            //  END  for testing, use special phone #
            $fncTwilio->messages->create(
                $fncPhone,[
                    'from' => '+1' . $gloTwilioPhone,
                    'body' => $fncMessage
                ]
            );
            $fncSQL  = 'INSERT INTO `wtkSMSsent` (`SendByUserUID`,`SendToUserUID`,`SMSPhone`,`SMSSubject`,`SMSText`)';
            $fncSQL .= ' VALUES (:SendByUserUID, :SendToUserUID, :SMSPhone, :SMSSubject, :SMSText)';
            $fncFilter = array (
                'SendByUserUID' => $fncFromUserUID,
                'SendToUserUID' => $fncToUserUID,
                'SMSPhone' => $fncPhone,
                'SMSSubject' => $fncSubject,
                'SMSText' => $fncMessage
            );
            wtkSqlExec($fncSQL, $fncFilter);
        // BEGIN Trap for errors so CRONbackground does not crash
        } catch (\Exception $e) {
            if (stripos($e, 'The message From/To pair violates a blacklist rule') !== false):
                $fncMsg  = 'SMS Error: The message From/To pair violates a blacklist rule.';
                $fncMsg .= '<br><br>UserUID of ' . $fncToUserUID . ' with phone # of :' . $fncPhone;
                $fncMsg .= '<br><br>Message:<br>' . wtkEscapeStringForDB($fncMessage);
                wtkNotifyViaEmailPlain('SMS Failure', $fncMsg, '', $fncSaveArray);
            else:
                wtkLogError('SMS', $e);
            endif;
        }
        //  END
    else:   // Not ($fncLength == 10) || ($fncLength == 11)
        wtkNotifyViaEmailPlain('Bad Phone', 'SMS failed because of phone number: ' . $fncToPhone . ' for message:<br>' . $fncMessage, '', $fncSaveArray);
    endif;  // ($fncLength == 10) || ($fncLength == 11)
}  // end of wtkSendSMS

function wtkSmsViaEmail($fncPhoneNumber, $fncCarrier, $fncMessage){
    // below code uses PostmarkApp specifically but you can change to your preferred email methodology
    global $gloPostmarkToken, $gloEmailFromAddress;
    $fncCarrierGateways = [
        'att' => 'txt.att.net',
        'tmobile' => 'tmomail.net',
        'verizon' => 'vtext.com',
        'spectrum' => 'mypixmessages.com',
        'sprint' => 'messaging.sprintpcs.com'
    ];
    $fncReturn = 'SMS Failed';
    if ($fncCarrier == 'att'):
        $fncReturn = 'AT&T no longer offers this service';
        wtkLogError('SMS', 'AT&T no longer offers email-to-text service');
    else:
        $fncPhone = preg_replace("/[^0-9]/", '', $fncPhoneNumber);
        $fncLength = strlen($fncPhone);
        if ((isset($fncCarrierGateways[$fncCarrier])) && ($fncLength == 10)):
            $fncToPhone = $fncPhone . '@' . $fncCarrierGateways[$fncCarrier];
            $fncPostArray = array('From'    => $gloEmailFromAddress,
                                  'To'      => $fncToPhone,
                                  'Subject' => '',
                                  'TextBody' => $fncMessage,
                                  'TrackOpens' => false,
                                  'MessageStream' => 'outbound'
                                );

            $fncJSON = json_encode($fncPostArray);

            $fncCurlHeaders = [
                'Accept: application/json',
                'Content-Type: application/json',
                'X-Postmark-Server-Token: ' . $gloPostmarkToken
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.postmarkapp.com/email');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $fncCurlHeaders);
            curl_setopt($ch, CURLOPT_POST, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fncJSON); // http_build_query did not work
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $fncResult = curl_exec($ch);
            if (!($fncResult)):
                $fncCurlErrNum = curl_errno($ch);
                $fncCurlErrStr = curl_error($ch);
                wtkLogError('PostmarkApp cURL', "cURL error: [$fncCurlErrNum] $fncCurlErrStr \n Email to $fncToPhone");
            else:
                $fncReturn = 'SMS sent';
    //          $fncResultArray = json_decode($fncResult, true);
    //          $fncCurlInfo = curl_getinfo($ch);
    //          print_r($fncCurlInfo);
            endif;
            curl_close($ch);
        else:
            if ($fncLength != 10):
                $fncReturn = 'phone number needs 10 digits';
            endif;
        endif;
    endif;
    return $fncReturn;
} // wtkSmsViaEmail
?>
