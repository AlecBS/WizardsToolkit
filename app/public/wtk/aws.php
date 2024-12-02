<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');
/*
trick is to switch AWS SNS "Subscriptions" to "Raw message delivery"
*/

$pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
$pgSqlFilter = array('DevNote' => 'AWS SNS triggered');
//wtkSqlExec($pgSQL, $pgSqlFilter);

$pgInput = file_get_contents('php://input');

$pgSQL = 'INSERT INTO `wtkInboundLog` (`InboundText`)  VALUES (:InboundText)';
$pgSqlFilter = array ('InboundText' => $pgInput);
// use for debugging only wtkSqlExec($pgSQL, $pgSqlFilter);

$pgJSONarray = json_decode($pgInput,true);
// print_r($pgJSONarray);
// exit;
$pgTimeZoneAdjust = 0; // (60*60*3); // 3 hour adjustment between PostmarkApp server and my server
$pgResult = 'success';
$pgMoreResult = '';
$pgRecordType = '';
$pgMessageId = '';
if (!is_array($pgJSONarray)):
    wtkLogError('AWS bad JSON', $pgInput);
    $pgResult = 'fail';
else:
    if (array_key_exists('notificationType',$pgJSONarray)):
        $pgRecordType = $pgJSONarray['notificationType'];
        $pgMessageId = $pgJSONarray['mail']['messageId'];
    else:
        wtkLogError('AWS invalid JSON', $pgInput);
        $pgResult = 'fail';
    endif;
endif;
if ($pgResult != 'fail'):
    switch ($pgRecordType):
        case 'Bounce':
            $pgSQL = 'UPDATE `wtkEmailsSent` SET `Bounced` = :Bounced WHERE `EmailMsgId` = :MessageID';
            $pgSqlFilter = array (
                'MessageID' => $pgMessageId,
                'Bounced' => 'Y'
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
            $pgMoreResult .= ',"RecType":"Bounce"';
            break;
        case 'Delivery':
            $pgSQL = 'UPDATE `wtkEmailsSent` SET `EmailDelivered` = :DateTime WHERE `EmailMsgId` = :MessageID';
// non-raw  $pgTimeStamp = $pgJSONarray['Message'][0]['mail']['timestamp'];
            $pgTimeStamp = $pgJSONarray['mail']['timestamp'];
            $pgSqlFilter = array (
                'MessageID' => $pgMessageId,
                'DateTime' => $pgTimeStamp
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
            $pgMoreResult .= ',"RecType":"Delivery"';
            break;
        case 'Complaint':
            $pgSQL = 'UPDATE `wtkEmailsSent` SET `SpamComplaint` = :DateTime WHERE `EmailMsgId` = :MessageID';
//          $pgTimeStamp = $pgJSONarray['Message'][0]['complaint']['timestamp'];
            $pgTimeStamp = $pgJSONarray['mail']['timestamp'];
            $pgSqlFilter = array (
                'MessageID' => $pgMessageId,
                'DateTime' => $pgTimeStamp
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
            $pgMoreResult .= ',"RecType":"SpamComplaint"';
            break;
        default :
            wtkLogError('AWS unrecognized Event', $pgRecordType);
    endswitch;
endif;

$pgJSON  = '{"result":"' . $pgResult . '"';
$pgJSON .= $pgMoreResult;
$pgJSON .= '}';
echo $pgJSON;
exit;
?>
