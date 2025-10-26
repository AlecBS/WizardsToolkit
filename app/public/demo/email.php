<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$gloTechSupport = 'alec.sherman@me.com';
// see below for list of possible parameters
wtkConnectToDB();

$pgTmp = wtkNotifyViaEmail('Test Email', 'Your email message to be sent.'); // sends to $gloTechSupport by default
if ($pgTmp == true):
    $pgResults  = '<h4>Email sent successfully to ' . $gloTechSupport . '</h4>' . "\n";
    $pgResults .= '<p>If you do not receive the email; retry using /testWTK.php</p>';
else:
    $pgResults  = '<h4>Email failed to send to ' . $gloTechSupport . '</h4>' . "\n";
    $pgResults .= '<p>Check for error in `wtkErrorLog` data table.</p>' . "\n";
endif;
$pgResults .= '<p>Email method: ' . $gloEmailMethod . '</p>';
echo $pgResults;
exit;

// Below is demo sending to your@email.com instead
// $pgTmp = wtkNotifyViaEmail('Test Email', 'Email message to be sent.','your@email.com')

/**
* function wtkNotifyViaEmail($fncSubject, $fncMessage, $fncToEmail = '', $fncSaveArray = [], $fncCC = '', $fncTemplate = 'default', $fncReplyTo = '', $fncAddNL2BR = 'N')
*
* One-line method of sending an email using an email HTML template.
*
* This defaults to sending email to $gloTechSupport defined in wtkServerInfo.php but you can pass both a To and a CC email
*
* @param string $fncSubject
* @param string $fncMessage - body of email
* @param array  $fncSaveArray optionally pass info to save into wtkEmailsSent data table
* @param string $fncToEmail if blank then $gloTechSupport will be used
* @param array  $fncSaveArray
* @param string $fncCC email address to CC if desired
* @param string $fncTemplate what HTML email template you want to use; 'default' will use wtk/htm/emailLight.htm or emailDark.htm
* @param string $fncReplyTo the email addresss to have as the ReplyTo
* @param string $fncAddNL2BR defaults to 'Y'; if set to 'Y' then does nl2br() on body of message
* @global string $gloDarkLight if $fncTemplate is not set will default to 'email' . $gloDarkLight . '.htm'
* @return void
*/
?>
