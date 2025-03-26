<?PHP
require('cronTop.php');
// this should be called once every minute

$pgHtm .= '<h3>Background Actions</h3>' . "\n";
$pgEmailCount = 0;
// $gloDebug = 'Y:'; //2FIX comment-out before deploying

// BEGIN process Background Actions
$pgSQL =<<<SQLVAR
SELECT `UID`, `ActionType`,`ForUserUID`,
    COALESCE(`Param1UID`,'') AS `Param1UID`,
    COALESCE(`Param2UID`,'') AS `Param2UID`,
    COALESCE(`ParamStr1`,'') AS `ParamStr1`,
    COALESCE(`ParamStr2`,'') AS `ParamStr2`
 FROM `BackgroundActions`
WHERE `TriggerTime` < NOW() AND `StartTime` IS NULL AND `UID` > :UID
ORDER BY `TriggerTime` ASC
SQLVAR;
// for testing you can add to WHERE:  AND `UID` = 0
$pgSqlFilter = array('UID' => 0);

$pgCount = 0;
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($pgPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgCount ++;
    $pgUID = $pgPDOrow['UID'];
    wtkSqlExec("UPDATE `BackgroundActions` SET `StartTime` = NOW() WHERE `UID` = $pgUID", []);
    $pgActionType = $pgPDOrow['ActionType'];
    $pgForUserUID = $pgPDOrow['ForUserUID'];
    $pgParam1UID = $pgPDOrow['Param1UID'];
    $pgParam2UID = $pgPDOrow['Param2UID'];
    switch ($pgActionType):
        case 'SendEmail':
/* Normally it is best to send emails directly but if using AWS SES or slow email processing, may want
   to do as background action so does not slow down website.
   In which case, save email to wtkEmailsSent table then insert into wtkBackgroundActions as follows:

*/
            $pgSQL =<<<SQLVAR
SELECT `EmailAddress`, `Subject`, `EmailBody`
 FROM `wtkEmailsSent`
WHERE `UID` = :UID
SQLVAR;
            $pgSqlFilter = array('UID' => $pgParam1UID);
            wtkSqlGetRow($pgSQL, $pgSqlFilter);
            $pgSubject = wtkSqlValue('Subject');
            $pgEmailBody = wtkSqlValue('EmailBody');
            $pgToEmail = wtkSqlValue('EmailAddress');
            if ($gloDebug == ''):
                $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail);
                $pgEmailCount ++;
            else:
                $gloDebug .= "wtkNotifyViaEmail($pgSubject,$pgEmailBody,$pgToEmail);<br>" . "\n";
                print_r($pgSaveArray);
                echo '<br><br>' . "\n";
            endif;
            break;
        case 'Thank4Order': // must have wtkEmailsTempate with EmailCode of 'Thank4Order'
            list($pgEmailUID, $pgSubject, $pgEmailBody, $pgToEmail) = wtkPrepEmail($pgActionType, $pgForUserUID);
            $pgSubject   = wtkReplace($pgSubject, '@OrderUID@', $pgParam1UID);
            $pgEmailBody = wtkReplace($pgEmailBody, '@OrderUID@', $pgParam1UID);
            $pgSaveArray = array (
                'EmailUID' => $pgEmailUID,
                'FromUID' => 0,
                'ToUID' => $pgForUserUID
            );
            $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, $pgSaveArray, '', 'email' . $gloDarkLight . '.htm');
            $pgEmailCount ++;
            break;
        case 'YourCode':
            // add whatever custom background action you want by defining 'ActionType', passing parameters, and adding code here
            break;
    endswitch;
    wtkSqlExec("UPDATE `BackgroundActions` SET `CompletedTime` = NOW() WHERE `UID` = $pgUID", []);

    $pgTimePassed = (((hrtime(true) - $pgStartTime)/1e+6)/1000);
    if ($pgTimePassed > 42): // if over 42 seconds have passed then break so can do other tasks
        break;
    endif;
endwhile;
unset($pgPDO);

$pgTimePassed = round((((hrtime(true) - $pgStartTime)/1e+6)/1000),4);
$pgHtm .= "<p>$pgCount background actions processed in $pgTimePassed seconds.</p>" . "\n";
$pgHtm .= 'and sent ' . $pgEmailCount . ' emails<br>';
//  END  process Background Actions

require('cronEnd.php');
?>
