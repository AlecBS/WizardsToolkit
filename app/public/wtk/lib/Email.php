<?PHP
/**
* This contains all Wizard's Toolkit functions that involve emailing.
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
* @link        Official website: https://wizardstoolkit.com
* @version     2.0
*/

// do not need PHPMailer if you are using 'PostmarkApp'
use PHPMailer\PHPMailer\PHPMailer;  // must be in this file, not in Core.php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require(_WTK_RootPATH . 'PHPMailer/src/SMTP.php');

/**
* Primary function to call to send emails.
*
* If second parameter is passed then it will also save email information into wtkEmailsSent data table.
*
* Example call includes defining Email Array and optional Save Array like:
* <code><br>
* $pgMailArray = array(<br>
*                         'ToAddress'   => $pgToEmail,<br>
*                         'ToName'      => $pgUserName,<br>
*                         'Subject'     => $pgSubject,<br>
*                         'Body'        => $pgBody<br>
*                     );<br>
* <br>
* $pgSaveArray = array (<br>
*     'FromUID' => 0,<br>
*     'EmailUID' => $pgEmailUID,<br>
*     'ToUID' => $gloUserUID<br>
* );<br>
* <br>
* if (wtkSendMail($pgMailArray, $pgSaveArray)):<br>
*     // show success message<br>
* endif;
* </code>
*
* @param array $fncEmailArray
* @param array $fncSaveArray
* @param array $fncAttachments defaults to blank
* @param string $fncDebugLevel defaults to 0
* @global string $gloEmailMethod
* @global string $gloTechSupport
* @global string $gloDbConnection If not 'Live' then
*    changes To address to $gloTechSupport and prepends 'TST: ' to subject
* @uses function wtkSendPHPMail or wtkPostmarkApp depending on $gloEmailMethod value
* @return boolean true if succeeds, false if email fails
*/
function wtkSendMail($fncEmailArray, $fncSaveArray = [], $fncAttachments = '', $fncDebugLevel = 0) {
    global $gloEmailMethod, $gloDbConnection, $gloTechSupport;
    if (is_array($fncEmailArray)
        && array_key_exists('ToAddress', $fncEmailArray)
        && array_key_exists('Subject', $fncEmailArray)
        && array_key_exists('Body', $fncEmailArray) ):

        $fncBody = wtkTokenToValue($fncEmailArray['Body']);
        if (array_key_exists('PlainTextBody', $fncEmailArray)):
            $fncAltBody = wtkTokenToValue($fncEmailArray['PlainTextBody']);
            $fncAltBody = wtkReplace($fncAltBody, '"','\"');
            $fncEmailArray['PlainTextBody'] = $fncAltBody;
        endif;
        $fncSubject = wtkTokenToValue($fncEmailArray['Subject']);
        if ($gloDbConnection != 'Live'):
            $fncSubject = 'TST: ' . $fncSubject;
            $fncTmp  = '<br><br><span style="background:#41cee2">(Testing: originally was to be sent to ' . $fncEmailArray['ToAddress'] . ')</span>';
            $fncBody = wtkReplace($fncBody,'</body>', $fncTmp . '</body>');
            $fncEmailArray['ToAddress'] = $gloTechSupport;
            $fncEmailArray['CC'] = '';
            $fncEmailArray['BCC'] = '';
        endif;
        $fncEmailArray['Subject'] = $fncSubject;
        $fncEmailArray['Body'] = $fncBody;

        if ($gloEmailMethod == 'PostmarkApp'):
            $fncResult = wtkPostmarkApp($fncEmailArray, $fncSaveArray, $fncAttachments);
        else:
            $fncResult = wtkSendPHPMail($fncEmailArray, $fncSaveArray, $fncAttachments, $fncDebugLevel);
        endif;
    else:
        wtkLogError('Email Not Sent', 'missing required data');
        $fncResult = false;
    endif;
    return $fncResult;
} // wtkSendMail

/**
* Called from wtkSendMail, this uses PostmarkApp to send emails.
*
* If second parameter is passed then it will also save email information into wtkEmailsSent data table.
* This uses curl method of calling PostmarkApp.
*
* @param array $fncEmailArray
* @param array $fncSaveArray
* @param string $fncAttachments defaults to blank
* @global string $gloPostmarkToken defined in wtk/wtkServerInfo.php
* @global string $gloEmailFromAddress will be used as the From email address; must be assigned in PostmarkApp
* @uses function wtkSaveEmailWrap to both put email body into HTML template and save to wtkEmailsSent table
* @link https://postmarkapp.com/developer/user-guide/send-email-with-api/send-a-single-email
* @return boolean true if succeeds, false if email fails
*/
$gloBulkEmailing = false;
function wtkPostmarkApp($fncEmailArray, $fncSaveArray, $fncAttachments = '') {
    global $gloPostmarkToken, $gloEmailFromAddress, $gloBulkEmailing;
    $fncToAddress = $fncEmailArray['ToAddress'];
    $fncParamCount = 8;
    $fncExtra = '';
    $fncAttachFiles = '';
    if (array_key_exists('CC', $fncEmailArray)):
        $fncCC = $fncEmailArray['CC'];
        if ($fncCC != ''):
            $fncExtra = "\n" . '"Cc": "' . $fncCC . '",';
            $fncParamCount ++;
        endif;  // $fncCC != ''
    endif;  // array_key_exists('CC, $fncEmailArray)
    if (array_key_exists('ReplyTo', $fncEmailArray)):
        $fncReplyTo = $fncEmailArray['ReplyTo'];
        if ($fncReplyTo != ''): // ABS  01/27/20
            $fncExtra = "\n" . '"ReplyTo": "' . $fncReplyTo . '",';
            $fncParamCount ++;
        endif;
    endif;  // array_key_exists('ReplyTo', $fncEmailArray)
    if (array_key_exists('PlainTextBody', $fncEmailArray)):
        $fncAltBody = $fncEmailArray['PlainTextBody'];
    else:
        $fncAltBody = '';
    endif;
    $fncSubject = $fncEmailArray['Subject'];
    $fncBody = $fncEmailArray['Body'];
    // BEGIN Add attachments if there are any
    if ($fncAttachments != ''):
        $fncAttachFiles = ',' . "\n" . '"Attachments": [' . "\n";
        for ($i = 0; $i < count($fncAttachments); $i++):
            if ($i > 0):
                $fncAttachFiles .= ',' . "\n";
            endif;
            $fncFile = $fncAttachments[$i];
            $fncHandle = fopen($fncFile, 'r');
            $fncFileContent = fread($fncHandle, filesize($fncFile));
            $fncFileContent = base64_encode($fncFileContent);
            $fncArray = array();
            $fncArray = explode('/', $fncFile);
            $fncFileName = $fncArray[(count($fncArray) - 1)];

            $fncAttachFiles .= '{"Name": "' . $fncFileName . '",' . "\n";
            $fncAttachFiles .= '"ContentType": "application/octet-stream",' . "\n";
            $fncAttachFiles .= '"Content": "' . $fncFileContent . '"' . "\n";
            $fncAttachFiles .= '}';
        endfor;
        $fncAttachFiles .= ']';
    endif;  // $fncAttachments != ""
    //  END  Add attachments if there are any
    // BEGIN cURL method
    list($fncEmailUID, $fncBody) = wtkSaveEmailWrap($fncEmailArray, $fncSaveArray);

    if ($gloBulkEmailing == true):
        $fncMsgStream = 'broadcast';
    else:
        $fncMsgStream = 'outbound';
    endif;
    $fncPostArray = array('From'    => $gloEmailFromAddress,
                          'To'      => $fncToAddress,
                          'Subject' => $fncSubject,
                          'HtmlBody' => $fncBody,
                          'TextBody' => $fncAltBody,
                          'TrackOpens' => true,
                          'TrackLinks' => 'HtmlOnly',
                          'MessageStream' => $fncMsgStream
                        );
    if (array_key_exists('CC', $fncEmailArray)):
        $fncCC = $fncEmailArray['CC'];
        if ($fncCC != ''):
            $fncPostArray['CC'] = $fncCC;
            $fncParamCount ++;
        endif;  // $fncCC != ''
    endif;  // array_key_exists('CC, $fncEmailArray)
    if (array_key_exists('ReplyTo', $fncEmailArray)):
        $fncReplyTo = $fncEmailArray['ReplyTo'];
        if ($fncReplyTo != ''):
            $fncPostArray['ReplyTo'] = $fncReplyTo;
            $fncParamCount ++;
        endif;
    endif;  // array_key_exists('ReplyTo', $fncEmailArray)

    $fncJSON = json_encode($fncPostArray);

    // BEGIN Add attachments if there are any
    if ($fncAttachments != ''):
        $fncJSON = substr($fncJSON, 0, -1); // remove last }
        $fncParamCount ++;
        $fncAttachFiles = ',' . "\n" . '"Attachments": [' . "\n";
        for ($i = 0; $i < count($fncAttachments); $i++):
            if ($i > 0):
                $fncAttachFiles .= ',' . "\n";
            endif;
            $fncFile = $fncAttachments[$i];
            $fncHandle = fopen($fncFile, 'r');
            $fncFileContent = fread($fncHandle, filesize($fncFile));
            $fncFileContent = base64_encode($fncFileContent);
            $fncArray = array();
            $fncArray = explode('/', $fncFile);
            $fncFileName = $fncArray[(count($fncArray) - 1)];

            $fncAttachFiles .= '{"Name": "' . $fncFileName . '",' . "\n";
            $fncAttachFiles .= '"ContentType": "application/octet-stream",' . "\n";
            $fncAttachFiles .= '"Content": "' . $fncFileContent . '"' . "\n";
            $fncAttachFiles .= '}';
        endfor;
        $fncAttachFiles .= ']';
        $fncJSON .= $fncAttachFiles . '}';
    endif;  // $fncAttachments != ""
    //  END  Add attachments if there are any

    $fncCurlHeaders = [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Postmark-Server-Token: ' . $gloPostmarkToken
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.postmarkapp.com/email');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $fncCurlHeaders);
    curl_setopt($ch, CURLOPT_POST, $fncParamCount);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fncJSON); // http_build_query did not work
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $fncResult = curl_exec($ch);
    $fncReturn = true;
    if (!($fncResult)):
        $fncIpAddress = wtkGetIPaddress();
        $fncCurlErrNum = curl_errno($ch);
        $fncCurlErrStr = curl_error($ch);
        wtkLogError('PostmarkApp cURL', "cURL error: [$fncCurlErrNum] $fncCurlErrStr \n Called from $fncIpAddress");
        $fncReturn = false;
    else:
        $fncResultArray = json_decode($fncResult, true);
        if (array_key_exists('MessageID',$fncResultArray)):
            $fncMessageID = $fncResultArray['MessageID'];
            global $gloSkipConnect;
            if ($gloSkipConnect != 'Y'):
                $fncSQL = 'UPDATE `wtkEmailsSent` SET `EmailMsgId` = :MessageID WHERE `UID` = :UID';
                $fncSqlFilter = array (
                    'UID' => $fncEmailUID,
                    'MessageID' => $fncMessageID
                );
                wtkSqlExec($fncSQL, $fncSqlFilter);
            endif;
        endif;
        if (array_key_exists('Message',$fncResultArray)):
            $fncResponse = $fncResultArray['Message'];
            if ($fncResponse != 'OK'):
                $fncResult = wtkReplace($fncResult,"'",'~');
                wtkLogError('PostmarkApp cURL', "$fncResult");
                $fncReturn = false;
            endif;
        else:
            $fncResult = wtkReplace($fncResult,"'",'~');
            wtkLogError('PostmarkApp cURL', "$fncResult");
            $fncReturn = false;
        endif;
        $fncCurlInfo = curl_getinfo($ch);
        if ($fncReturn == true):
            $fncCurlHttp = $fncCurlInfo['http_code'];
            if ($fncCurlHttp != 200):
                wtkLogError('PostmarkApp cURL', "HTTP Error : $fncCurlHttp ; Result: $fncResult");
            endif;
        else:
            if (strpos($fncResult, 'Found inactive addresses') !== false):
                $fncSqlFilter = array('Email' => $fncToAddress);
                wtkSqlExec("UPDATE `wtkUsers` SET `OptInEmails` = 'N' WHERE `Email` = :Email", $fncSqlFilter, false);
                wtkSqlExec("UPDATE `wtkAffiliates` SET `DelDate` = NOW() WHERE `Email` = :Email", $fncSqlFilter, false);
                wtkSqlExec("UPDATE `wtkProspectStaff` SET `AllowContact` = 'N', `InternalNote` = 'email bounced' WHERE `Email` = :Email", $fncSqlFilter, false);
            endif;
        endif;
    endif;
    curl_close($ch);
    //  END  cURL method
    return $fncReturn;
} // end of wtkPostmarkApp

/**
* Called from wtkSendMail, this uses PHPMailer to send emails.
*
* If second parameter is passed then it will also save email information into wtkEmailsSent data table.
* This uses curl method of calling PostmarkApp.
*
* @param array $fncEmailArray
* @param array $fncSaveArray
* @param string $fncAttachments defaults to blank
* @param string $fncDebugLevel defaults to 0
* @global string $gloEmailFromAddress will be used as the From email address; must be assigned in PostmarkApp
* @global string $gloWebBaseURL used for unsubscribe feature
* @global string $gloEmailHost all these global variables are defined in wtk/wtkServerInfo.php
* @global string $gloEmailMethod
* @global string $gloEmailPort
* @global string $gloEmailSMTPAuth
* @global string $gloEmailPassword
* @uses function wtkSaveEmailWrap to both put email body into HTML template and save to wtkEmailsSent table
* @link https://postmarkapp.com/developer/user-guide/send-email-with-api/send-a-single-email
* @return boolean true if succeeds, false if email fails
*/
function wtkSendPHPMail($fncEmailArray, $fncSaveArray = [], $fncAttachments = '', $fncDebugLevel = 0) {
    global $gloCoName, $gloTechSupport, $gloWebBaseURL, $gloEmailHost, $gloEmailMethod,
           $gloEmailPort, $gloEmailUserName, $gloEmailPassword, $gloEmailFromAddress;

    $fncResult = false;
    require_once(_WTK_RootPATH . 'PHPMailer/src/Exception.php');
    require_once(_WTK_RootPATH . 'PHPMailer/src/PHPMailer.php');
    $mail = new PHPMailer(true);

    // set PHPMailer CharSet to UTF-8
    $mail->CharSet = "UTF-8";

    $fncSubject = $fncEmailArray['Subject'];
    $fncBody = $fncEmailArray['Body'];
    if ($fncDebugLevel > 0): // 4 is most verbose
        $mail->SMTPDebug = $fncDebugLevel;
    endif;
    switch (strtolower($gloEmailMethod)):
        case 'smtp' :
        case 'aws' :
            $mail->isSMTP(); // Set mailer to use SMTP for Amazon SES
            break;
        case 'mail' :
            $mail->isMail();
            break;
        case 'qmail' :
            $mail->isQmail();
            break;
        default :
            $mail->isSendmail();
    endswitch;

    if (array_key_exists('FromAddress', $fncEmailArray)):
        $fncFromAddress = $fncEmailArray['FromAddress'];
        if (array_key_exists('FromName', $fncEmailArray)):
            $fncFromName = $fncEmailArray['FromName'];
            $mail->setFrom($fncFromAddress, $fncFromName);
        else:
            $mail->setFrom($fncFromAddress);
        endif;
    else:
        $mail->setFrom($gloEmailFromAddress, $gloCoName);
    endif;

    $mail->Username   = $gloEmailUserName;
    $mail->Password   = $gloEmailPassword;
    $mail->Host       = $gloEmailHost;
    $mail->Port       = $gloEmailPort;
    $mail->SMTPAuth   = true;
    if ($gloEmailPort == 465):
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    else:
        $mail->SMTPSecure = 'tls'; // FAILS?!? // was PHPMailer::ENCRYPTION_STARTTLS;
    endif;

    try {
        // BEGIN Set Recipients
        $fncToAddress = $fncEmailArray['ToAddress'];
        if (array_key_exists('ToName', $fncEmailArray)):
            $fncToName = $fncEmailArray['ToName'];
        else:
            $fncToName = '';
        endif;
        if ($fncToName != ''):
            $mail->addAddress($fncToAddress, $fncToName);
        else:
            $mail->addAddress($fncToAddress);
        endif;
        if (array_key_exists('CC', $fncEmailArray)):
            $fncCC = $fncEmailArray['CC'];
            if ($fncCC != ''):
                $mail->addCC($fncCC);
            endif;  // $fncCC != ''
        endif;  // array_key_exists('CC, $fncEmailArray)
        if (array_key_exists('BCC', $fncEmailArray)):
            $fncBCC = $fncEmailArray['BCC'];
            if ($fncBCC != ''):
                $mail->addBCC($fncBCC);
            endif;  // $fncCC != ''
        endif;  // array_key_exists('CC, $fncEmailArray)
        //  END  Set Recipients
        // Specify the content of the message.
        $mail->isHTML(true);
        $mail->Subject = $fncSubject;
        $mail->addCustomHeader("List-Unsubscribe: <mailto:$gloTechSupport?subject=Unsubscribe>, <$gloWebBaseURL/wtk/unsubscribe.php?Email=" . $fncEmailArray['ToAddress'] . ">");
        // BEGIN Add attachments if there are any
        $fncOK = true;
        if ($fncAttachments != ''):
            for ($i = 0; $i < count($fncAttachments); $i++):
                if (!$mail->addAttachment($fncAttachments[$i])):
                //    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
                    wtkLogError('Email Not Sent', 'problem with attachment: ' . $fncAttachments[$i]);
                    $fncOK = false;
                endif;
            endfor;
        endif;  // $fncAttachments != ""
        // END  Add attachments if there are any
        if ($fncOK == true):
            list($fncEmailUID, $fncBody) = wtkSaveEmailWrap($fncEmailArray, $fncSaveArray);

            $mail->Body = $fncBody;
            if (array_key_exists('PlainTextBody', $fncEmailArray)):
                $fncAltBody = $fncEmailArray['PlainTextBody'];
            else:
                $fncAltBody = wtkRemoveStyle($fncEmailArray['Body']);
            endif;
            $mail->AltBody = $fncAltBody;
            $mail->Send();

            $fncMessageID = $mail->getSMTPInstance()->getLastTransactionID();
    //      $fncResult = $mail->getLastMessageID();
    //      wtkLogError('Email getLastTransactionID', $fncMessageID);
            if ($fncMessageID != ''):
                global $gloSkipConnect;
                if ($gloSkipConnect != 'Y'):
                    $fncSQL = 'UPDATE `wtkEmailsSent` SET `EmailMsgId` = :MessageID WHERE `UID` = :UID';
                    $fncSqlFilter = array (
                        'UID' => $fncEmailUID,
                        'MessageID' => $fncMessageID
                    );
                    wtkSqlExec($fncSQL, $fncSqlFilter);
                endif;
            endif;
            $fncResult = true; // not necessarily correct
        endif;
    } catch (phpmailerException $e) {
        echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    } catch (PHPMailer\PHPMailer\Exception $e) {
        wtkLogError('Email Error', $mail->ErrorInfo);
    }
    return $fncResult;
} // end of wtkSendPHPMail

/**
* This is called by other Email PHP functions and generally shouldn't be called directly.
*
* This adds a row into the `wtkEmailsSent` for every email sent.
*
* @param string $fncSqlFilter
* @param string $fncSaveArray - "Save Parameters" passed manually by developer
* @param string $gloSkipConnect - if blank then $gloTechSupport will be used
* @param array  $gloSkipSaveEmail
* @return $fncEmailUID which is the UID of the `wtkEmailsSent` row just inserted
*/
function wtkSaveEmailSent($fncSqlFilter, $fncSaveArray){
    global $gloSkipConnect, $gloSkipSaveEmail;
    if (!isset($gloSkipSaveEmail)):
        $gloSkipSaveEmail = 'N';
    endif;
    if (($gloSkipConnect == 'Y') || ($gloSkipSaveEmail == 'Y')):
        $fncEmailUID = 0;
    else:
        $fncInsSQL = 'INSERT INTO `wtkEmailsSent` (`EmailAddress`, `Subject`, `EmailBody`)';
        $fncValues = ' VALUES (:EmailAddress, :Subject, :EmailBody)';
        // BEGIN if passed extra values...
        if (array_key_exists('FromUID', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`SendByUserUID`)');
            $fncValues = wtkReplace($fncValues, ')',', :FromUID)');
            $fncSqlFilter['FromUID'] = $fncSaveArray['FromUID'];
        endif;
        if (array_key_exists('ToUID', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`SendToUserUID`)');
            $fncValues = wtkReplace($fncValues, ')',', :ToUID)');
            $fncSqlFilter['ToUID'] = $fncSaveArray['ToUID'];
        endif;
        if (array_key_exists('EmailType', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`EmailType`)');
            $fncValues = wtkReplace($fncValues, ')',', :EmailType)');
            $fncSqlFilter['EmailType'] = $fncSaveArray['EmailType'];
        endif;
        if (array_key_exists('OtherUID', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`OtherUID`)');
            $fncValues = wtkReplace($fncValues, ')',', :OtherUID)');
            $fncSqlFilter['OtherUID'] = $fncSaveArray['OtherUID'];
        endif;
        if (array_key_exists('EmailUID', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`EmailUID`)');
            $fncValues = wtkReplace($fncValues, ')',', :EmailUID)');
            $fncSqlFilter['EmailUID'] = $fncSaveArray['EmailUID'];
        endif;
        if (array_key_exists('HtmlTemplate', $fncSaveArray)):
            $fncInsSQL = wtkReplace($fncInsSQL, ')',',`HtmlTemplate`)');
            $fncValues = wtkReplace($fncValues, ')',', :HtmlTemplate)');
            $fncSqlFilter['HtmlTemplate'] = $fncSaveArray['HtmlTemplate'];
        endif;
        //  END  if passed extra values...
        wtkSqlExec($fncInsSQL . $fncValues, $fncSqlFilter);
        $fncToAddress = $fncSqlFilter['EmailAddress'];
        $fncEmailUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkEmailsSent` WHERE `EmailAddress` = ? ORDER BY `UID` DESC LIMIT 1', [$fncToAddress], '', true);
    endif;
    return $fncEmailUID;
} // wtkSaveEmailSent

/**
* One-line method of sending an email <strong>without</strong> any email HTML template.
*
* This is the same as calling wtkNotifyViaEmail passing 'none' for the HTML template
*
* @param string $fncSubject
* @param string $fncMessage - body of email
* @param string $fncToEmail - if blank then $gloTechSupport will be used
* @param array  $fncSaveArray
* @param string $fncCC      - email address to CC if desired
* @return void
*/
function wtkNotifyViaEmailPlain($fncSubject, $fncMessage, $fncToEmail = '', $fncSaveArray = [], $fncCC = '') {
    wtkNotifyViaEmail($fncSubject, $fncMessage, $fncToEmail, $fncSaveArray, $fncCC, $fncTemplate = 'none');
}  // end of wtkNotifyViaEmailPlain

/**
* One-line method of sending an email using an email HTML template.
*
* This defaults to sending email to $gloTechSupport definied in wtkServerInfo.php but you can pass both a To and a CC email
*
* @param string $fncSubject
* @param string $fncMessage - body of email
* @param array  $fncSaveArray optionally pass info to save into wtkEmailsSent data table
* @param string $fncToEmail if blank then $gloTechSupport will be used
* @param array  $fncSaveArray
* @param string $fncCC email address to CC if desired
* @param string $fncTemplate what HTML email template you want to use
* @param string $fncReplyTo the email addresss to have as the ReplyTo
* @param string $fncAddNL2BR defaults to 'N'; if set to 'Y' then does nl2br() on body of message
* @global string $gloDarkLight if $fncTemplate is not set will default to 'email' . $gloDarkLight . '.htm'
* @return void
*/
function wtkNotifyViaEmail($fncSubject, $fncMessage, $fncToEmail = '', $fncSaveArray = [], $fncCC = '', $fncTemplate = 'default', $fncReplyTo = '', $fncAddNL2BR = 'N', $fncDebugLevel = 0) {
    // this version has HTML template added; cannot use to replace prior
    // version until all locations are found and any templates are removed from them
    global $gloTechSupport, $gloEmailFromAddress, $gloCoName, $gloWebBaseURL, $gloDarkLight;
    if ($fncTemplate != 'none'):
        if ($fncTemplate == 'default'):
            $fncTemplate = 'email' . $gloDarkLight . '.htm';
        endif;
        $fncSaveArray['HtmlTemplate'] = $fncTemplate;
    endif;
    if ($fncToEmail == ''):
        $fncToEmail = $gloTechSupport;
    endif;  // fncEmail == ''
    if ($fncAddNL2BR == 'Y'):
        $fncEmailBody = nl2br($fncMessage);
    else:
        $fncEmailBody = $fncMessage;
    endif;
    // one-line method to send emails with most-often-used settings
    $fncMailArray = array('FromAddress' => $gloEmailFromAddress,
                          'FromName'    => $gloCoName,
                          'ToAddress'   => $fncToEmail,
                          'ToName'      => $fncToEmail,
                          'CC'          => $fncCC,
                          'ReplyTo'     => $fncReplyTo,
                          'Subject'     => $fncSubject,
                          'Body'        => $fncEmailBody,
                          'PlainTextBody' => $fncMessage
                        );
    $fncResult = wtkSendMail($fncMailArray, $fncSaveArray, '', $fncDebugLevel);
    return $fncResult;
}  // end of wtkNotifyViaEmail

/**
* One-line method to retrieve EmailUID, Subject, Email Body, FirstName and ToEmail
*
* Pass in the EmailCode and the wtkUsers.UID to retrieve the basics for an email template
* Calling method:
* list($pgEmailUID, $pgSubject, $pgEmailBody, $pgToEmail) = wtkPrepEmail('Remind2Pay', $pgUserUID);
*
* @param string $fncEmailCode
* @param int $fncUserUID
* @return array
*/
function wtkPrepEmail($fncEmailCode,$fncUserUID){
    global $gloCoName;
    $fncSQL =<<<SQLVAR
SELECT u.`Email`, u.`FirstName`,
    et.`UID`, et.`Subject`, et.`EmailBody`
FROM `wtkUsers` u, `wtkEmailTemplate` et
WHERE u.`UID` = :UserUID AND et.`EmailCode` = :EmailCode
ORDER BY et.`UID` DESC
SQLVAR;
    $fncSqlFilter = array(
        'UserUID' => $fncUserUID,
        'EmailCode' => $fncEmailCode
    );
    $fncTmp = wtkSqlGetRow($fncSQL, $fncSqlFilter);
    if ($fncTmp == 'no data'):
        wtkNotifyViaEmail('wtkPrepEmail Error', "EmailCode: $fncEmailCode to $fncUserUID failed");
        echo 'wtkPrepEmail failed - quit processing';
        exit;
    endif;

    $fncEmailUID = wtkSqlValue('UID');
    $fncFirstName = wtkSqlValue('FirstName');
    $fncToEmail = wtkSqlValue('Email');
    $fncSubject = wtkSqlValue('Subject');
    $fncSubject = wtkReplace($fncSubject, '@CompanyName@', $gloCoName);

    $fncEmailBody = wtkSqlValue('EmailBody');
    $fncEmailBody = wtkTokenToValue($fncEmailBody);
    $fncEmailBody = wtkReplace($fncEmailBody, '@FirstName@', $fncFirstName);
    return array($fncEmailUID, $fncSubject, $fncEmailBody, $fncToEmail);
}

function wtkTokenToValue($fncText){
    global $gloCoName, $gloWebBaseURL;
    $fncText = wtkReplace($fncText,'@CurrentYear@', date('Y'));
    $fncText = wtkReplace($fncText,'@Date@', date('F jS, Y'));
    $fncText = wtkReplace($fncText,'@CompanyName@', $gloCoName);
    $fncText = wtkReplace($fncText,'@website@', $gloWebBaseURL);
    return $fncText;
} // end of wtkTokenToValue

function wtkUseEmailTemplate($fncBody, $fncToAddress, $fncTemplate){
    $fncPathFile = _RootPATH . 'wtk/htm/' . $fncTemplate;
    if (!file_exists($fncPathFile)):
        if (_RootPATH == '../'):  // probably called from /wtk/lib/Save.php
            $fncPathFile = '../htm/' . $fncTemplate;
            if (!file_exists($fncPathFile)):
                wtkLogError('Email Template', 'Missing email template wtk/htm/' . $fncTemplate);
                echo 'Missing email template: ' . $fncTemplate;
                exit;
            endif;
        endif;
    endif;
    $fncHtmBody = wtkLoadInclude($fncPathFile);
    $fncHtmBody = wtkTokenToValue($fncHtmBody);
    $fncHtmBody = wtkReplace($fncHtmBody, '@wtkContent@', $fncBody);
    $fncHtmBody = wtkReplace($fncHtmBody, '@email@', urlencode(trim($fncToAddress)));
    return $fncHtmBody;
}

/**
 * This wraps both Saving to wtkEmailsSent table and putting email body into HTML template
 *
 * If the wtkNotifyViaEmail function was called it would have filled
 * the HtmlTemplate.  This expects the HTML file to be in the /wtk/html folder.
 * It will use this as the email template for the email.
 * It will put the passed "body" into the @wtkContent@ space in the template.
 *
 * Calling method: list($fncEmailUID, $fncBody) = wtkSaveEmailWrap($fncEmailArray, $fncSaveArray);
 *
 * @param $fncEmailArray array containing Subject, ToEmail, and Body
 * @param $fncSaveArray array containing data to save to wtkEmailsSent
 * @uses function wtkSaveEmailSent to save email data
 * @return array containing both wtkEmailsSent.UID and updated Email Body
 */
function wtkSaveEmailWrap($fncEmailArray, $fncSaveArray){
    $fncToAddress = $fncEmailArray['ToAddress'];
    $fncSubject = $fncEmailArray['Subject'];
    $fncBody = $fncEmailArray['Body'];
    $fncSqlFilter = array (
        'EmailAddress' => $fncToAddress,
        'Subject' => $fncSubject,
        'EmailBody' => $fncBody
    );
    $fncEmailUID = wtkSaveEmailSent($fncSqlFilter, $fncSaveArray);
    if (array_key_exists('HtmlTemplate', $fncSaveArray)):
        $fncBody = wtkUseEmailTemplate($fncBody, $fncToAddress, $fncSaveArray['HtmlTemplate']);
    endif;
    $fncBody = wtkReplace($fncBody, '@EmailSentUID@', $fncEmailUID);
    if ($fncEmailUID != 0):
        $fncBody = wtkReplace($fncBody, 'logo.php?Skip=Y', 'logo.php?e=' . $fncEmailUID);
    endif;
    return array($fncEmailUID, $fncBody);
}
?>
