<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgDebugSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
$pgTmp = '';
$pgPassword = 'bad';
$pgHeaders  = apache_request_headers();
foreach ($pgHeaders as $header => $value):
    // Set this on PostmarkApp as your "Custom headers"
    // If all lower case, sometimes PostmarkApp makes first character capitalized
    // so to catch all do something like this:
    if (($header == 'mypw') || ($header == 'Mypw')):
        $pgPassword = $value;
        break;
    endif;
    $pgTmp .= "$header: $value <br />\n";
endforeach;

$pgResult = 'success';
$pgMoreResult = '';
if ($pgPassword != 'myRestPostmarkAppPW'):
    wtkLogError('PostmarkApp Headers', $pgTmp);
    $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp triggered but wtkDeadPage');
    wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
    wtkDeadPage('RPMA');
endif;

$pgInput = file_get_contents('php://input');
$pgJSONarray = json_decode($pgInput,true);

$pgRecordType = '';
$pgMessageId = '';
if (!is_array($pgJSONarray)):
    wtkLogError('PostmarkApp bad JSON', $pgInput);
    $pgResult = 'fail';
else:
    if (array_key_exists('MessageID',$pgJSONarray)):
        $pgMessageId = $pgJSONarray['MessageID'];
        if (array_key_exists('RecordType',$pgJSONarray)):
            $pgRecordType = $pgJSONarray['RecordType'];
        endif;
    endif;
endif;
if ($pgMessageId == ''):
    $pgRecordType = 'No MessageID';
endif;
$pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp triggered: MessageID = ' . $pgMessageId . '; Type = ' . $pgRecordType);
wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);

$pgTimeZoneAdjust = (60*60*2); // can set hour adjustment between PostmarkApp server and your server
switch ($pgRecordType):
    case 'Bounce':
        $pgSQL = 'UPDATE `wtkEmailsSent` SET `Bounced` = :Bounced WHERE `EmailMsgId` = :MessageID';
        $pgSqlFilter = array (
            'MessageID' => $pgMessageId,
            'Bounced' => 'Y'
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgMoreResult .= ',"RecType":"Bounce"';
        $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp Bounce for MessageID: ' . $pgMessageId);
//        wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
        break;
    case 'Delivery':
        $pgSQL = 'UPDATE `wtkEmailsSent` SET `EmailDelivered` = :DateTime WHERE `EmailMsgId` = :MessageID';
        $pgDeliveredAt = $pgJSONarray['DeliveredAt'];
        $pgDateTime = date('Y-m-d H:i:s', strtotime($pgDeliveredAt) + $pgTimeZoneAdjust);
        // DeliveredAt is at PostmarkApp server time; use that with $pgTimeZoneAdjust
        // or next line if you want based on your SQL server time
        // $pgDateTime = 'CURRENT_TIMESTAMP';
        $pgSqlFilter = array (
            'MessageID' => $pgMessageId,
            'DateTime' => $pgDateTime
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgMoreResult .= ',"RecType":"Delivery"';
        $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp Delivery for MessageID: ' . $pgMessageId);
//        wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
        break;
    case 'Open':
        $pgSQL = 'UPDATE `wtkEmailsSent` SET `EmailOpened` = :DateTime WHERE `EmailMsgId` = :MessageID';
        $pgReceivedAt = $pgJSONarray['ReceivedAt'];
        $pgDateTime = date('Y-m-d H:i:s', strtotime($pgReceivedAt) + $pgTimeZoneAdjust);
        // ReceivedAt is at PostmarkApp server time; use that with $pgTimeZoneAdjust
        // or next line if you want based on your SQL server time
        // $pgDateTime = 'CURRENT_TIMESTAMP';

        $pgSqlFilter = array (
            'MessageID' => $pgMessageId,
            'DateTime' => $pgDateTime
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgMoreResult .= ',"RecType":"Open"';
        $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp Open for MessageID: ' . $pgMessageId);
//        wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
        break;
    case 'Click':
        $pgSQL = 'UPDATE `wtkEmailsSent` SET `EmailLinkClicked` = :DateTime WHERE `EmailMsgId` = :MessageID';
        $pgReceivedAt = $pgJSONarray['ReceivedAt'];
        $pgDateTime = date('Y-m-d H:i:s', strtotime($pgReceivedAt) + $pgTimeZoneAdjust);
        // ReceivedAt is at PostmarkApp server time; use that with $pgTimeZoneAdjust
        // or next line if you want based on your SQL server time
        // $pgDateTime = 'CURRENT_TIMESTAMP';

        $pgSqlFilter = array (
            'MessageID' => $pgMessageId,
            'DateTime' => $pgDateTime
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgMoreResult .= ',"RecType":"Click"';
        $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp Click for MessageID: ' . $pgMessageId);
//        wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
        break;
    case 'SpamComplaint':
        $pgSQL = 'UPDATE `wtkEmailsSent` SET `SpamComplaint` = :DateTime WHERE `EmailMsgId` = :MessageID';
        $pgBouncedAt = $pgJSONarray['BouncedAt'];
        $pgDateTime = date('Y-m-d H:i:s', strtotime($pgBouncedAt) + $pgTimeZoneAdjust);
        // BouncedAt is at PostmarkApp server time; use that with $pgTimeZoneAdjust
        // or next line if you want based on your SQL server time
        // $pgDateTime = 'CURRENT_TIMESTAMP';

        $pgSqlFilter = array (
            'MessageID' => $pgMessageId,
            'DateTime' => $pgDateTime
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgMoreResult .= ',"RecType":"SpamComplaint"';
        $pgSqlDebugFilter = array ('DevNote' => 'restPostmarkApp SpamComplaint for MessageID: ' . $pgMessageId);
//        wtkSqlExec($pgDebugSQL, $pgSqlDebugFilter);
        break;
    default:
        wtkLogError('restPostmarkApp called incorrectly', $pgRecordType);
endswitch;

$pgJSON  = '{"result":"' . $pgResult . '"';
$pgJSON .= $pgMoreResult;
$pgJSON .= '}';
echo $pgJSON;
exit;
?>
