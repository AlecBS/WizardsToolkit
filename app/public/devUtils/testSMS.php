<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
wtkPageProtect('wtk4LowCode');

/*
The wtkSendSMS function is located in /wtk/lib/Twilio.php

function wtkSendSMS($fncToPhone, $fncMessage, $fncSubject = 'SMS', $fncFromUserUID = 0, $fncToUserUID = 'NULL') {

Global variables are defined in pho.env and /wtk/wtkServerInfo.php
but you can override them here for testing.

Note: this function has been fully tested and verified for USA numbers
      but never for other countries

Check wtkErrorLog data table after tests to see if any errors logged.
Note: Twilio does not send all errors back - you'll have to log in to Twilio and check there also.
    https://console.twilio.com/us1/monitor/logs/debugger/errors

Note: normally it sends to the first parameter in wtkSendSMS
    but as a safety precaution, if not 'Live' server
        then sends instead to the $gloTechPhone
    by testing within wtkSendSMS like this:
    if ($gloDbConnection != 'Live'):
*/
/*
-- Override global variables here; once working,
-- copy into php.env or wtk/wtkServerInfo.php

$gloTwilioPhone         = 'yourFromPhone'; // Your Twilio From Phone number
$gloTwilioSID           = 'yourSID';   // Your Account SID from www.twilio.com/user/account
$gloTwilioToken         = 'yourToken'; // Your Auth Token from www.twilio.com/user/account

$gloTechPhone   = 'yourPhone'; // your cell phone # to send SMS to
$gloTechSupport = 'you@email.com';
*/
$pgHtm =<<<htmVAR
<h2>Twilio Related Variables</h2>
<p>&dollar;gloDbConnection: $gloDbConnection <em>(just informational)</em></p>
<p>&dollar;gloTwilioSID: $gloTwilioSID</p>
<p>&dollar;gloTwilioToken: $gloTwilioToken</p>
<p>&dollar;gloTwilioPhone: $gloTwilioPhone <em>(your Twilio "From" Phone number)</em></p>
<p>&dollar;gloTechPhone: $gloTechPhone</p>
<p>&dollar;gloTechSupport: $gloTechSupport
    <em>(blacklist warnings and some other errors from Twilio will
        be sent here)</em></p>

<a href="?Step=Refresh" class="btn waves-effect waves-light">Refresh</a> &nbsp;&nbsp;
htmVAR;

if (($gloTwilioSID == 'Your-Account-SID') || ($gloTwilioToken == 'Your-Auth-Token')
    || ($gloTwilioPhone == '2095551234') || ($gloTechPhone == '2095551234')
    || ($gloTechSupport == 'you@email.com')):
    $pgHtm .= '<br><br><h4 class="red-text">Your variables need to be set before you can SMS!</h4>';
else:
    $pgHtm .= '<a href="?Step=SMS" class="btn waves-effect waves-light">Test SMS</a>' . "\n";
endif;
$pgHtm .= '<br><br>' . "\n";

$gloUserUID = 0; // this would be logged-in user's ID#; 0 is "from server"
$pgSmsMsg = 'WTK Twilio testing message';
if (wtkGetParam('Step') == 'SMS'):
    // To send SMS use next function (only the next line is necessary)
    wtkSendSMS($gloTechPhone, $pgSmsMsg,'SMS', $gloUserUID);
    $pgHtm .= '<br><h2>SMS Testing</h2>' . "\n";
    $pgHtm .= "<p>Message sent to: $gloTechPhone</p><hr><br>" . "\n";
endif;

// BEGIN Error Reporting
// Errors are automatically logged to the wtkErrorLog table
$pgSQL =<<<SQLVAR
SELECT `AddDate`, `ErrType`, `ErrMsg`
 FROM `wtkErrorLog`
WHERE `AddDate` > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
ORDER BY `UID` DESC
SQLVAR;
$pgHtm .= '<h3>Errors in last minute</h3>' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, []);
//  END  Error Reporting

// BEGIN SMS logging - successful messages are stored here
$pgSQL =<<<SQLVAR
SELECT DATE_FORMAT(s.`AddDate`, '$gloSqlDateTime') AS `AddDate`,
    CONCAT(f.`FirstName`, ' ', COALESCE(f.`LastName`,'')) AS `From`,
    CONCAT(t.`FirstName`, ' ', COALESCE(t.`LastName`,'')) AS `SentTo`,
    s.`SMSPhone` AS `PhoneNumber`, s.`SMSText` AS `TextSent`
 FROM `wtkSMSsent` s
   INNER JOIN `wtkUsers` f ON f.`UID` = s.`SendByUserUID`
   LEFT OUTER JOIN `wtkUsers` t ON t.`UID` = s.`SendToUserUID`
ORDER BY s.`UID` DESC LIMIT 10
SQLVAR;
$pgHtm .= '<br><h3>Last 10 SMS Sent</h3>' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, []);
//  END  SMS logging - successful messages are stored here
require('wtkinfo.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkSearchReplace('<div class="row"><div class="col m4 offset-m4 s12">','<div class="container"><br><br>'); // for minibox adjustment
wtkSearchReplace('</div></div>','</div>');
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
