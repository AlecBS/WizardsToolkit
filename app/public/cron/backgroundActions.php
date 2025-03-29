<?PHP
require('cronTop.php');
// this should be called once every minute
// you can move this code into /cron/minute.php or leave here and call directly
$pgHtm .= '<h3>Background Actions</h3>' . "\n";
$pgEmailCount = 0;
//$gloDebug = 'Y'; //2FIX comment-out before deploying

// BEGIN process Background Actions
$pgSQL =<<<SQLVAR
SELECT `UID`, `ActionType`,`ForUserUID`,
    COALESCE(`Param1UID`,'') AS `Param1UID`,
    COALESCE(`Param2UID`,'') AS `Param2UID`,
    COALESCE(`Param1Str`,'') AS `Param1Str`,
    COALESCE(`Param2Str`,'') AS `Param2Str`
 FROM `wtkBackgroundActions`
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
    wtkSqlExec("UPDATE `wtkBackgroundActions` SET `StartTime` = NOW() WHERE `UID` = $pgUID", []);
    $pgActionType = $pgPDOrow['ActionType'];
    $pgForUserUID = $pgPDOrow['ForUserUID'];
    $pgParam1UID = $pgPDOrow['Param1UID'];
    $pgParam2UID = $pgPDOrow['Param2UID'];
    $pgParam1Str = $pgPDOrow['Param1Str'];
    $pgParam2Str = $pgPDOrow['Param2Str'];

    switch ($pgActionType):
        case 'SendEmail':
/*
  Normally it is best to send emails directly but if using AWS SES or slow email
  processing, may want to do as background action so does not slow down website.
  In which case, save email to wtkEmailsSent table then insert into wtkBackgroundActions as follows:

// $pgUserUID should be the `wtkUsers`.`UID` of the person sending to
// $pgEmailUID should be `wtkEmailsSent`.`UID`
INSERT INTO `wtkBackgroundActions` (`TriggerTime`, `ActionType`, `ForUserUID`, `Param1UID`)
  VALUES (NOW(), 'SendEmail', $pgUserUID, $pgEmailUID);
*/
            $pgSQL =<<<SQLVAR
SELECT `EmailAddress`, `Subject`, `EmailBody`,
    COALESCE(`HtmlTemplate`,'default') AS `HtmlTemplate`
 FROM `wtkEmailsSent`
WHERE `UID` = :UID
SQLVAR;
            $pgSqlFilter = array('UID' => $pgParam1UID);
            wtkSqlGetRow($pgSQL, $pgSqlFilter);
            $pgSubject = wtkSqlValue('Subject');
            $pgEmailBody = wtkSqlValue('EmailBody');
            $pgToEmail = wtkSqlValue('EmailAddress');
            $pgHtmlTemplate = wtkSqlValue('HtmlTemplate');
            if ($gloDebug == ''):
                $gloSkipSaveEmail = 'Y'; // since already saved, do not re-save
                $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, [], '', $pgHtmlTemplate);
                $pgEmailCount ++;
            else:
                if ($gloDebug == 'Y'):
                    $gloDebug = '';
                endif;
                $gloDebug .= "wtkNotifyViaEmail($pgSubject,$pgEmailBody,$pgToEmail,[],'',$pgHtmlTemplate);<br>" . "\n";
            endif;
            break;
        case 'Thank4Order': // must have wtkEmailsTempate with EmailCode of 'Thank4Order'
            list($pgEmailUID, $pgSubject, $pgEmailBody, $pgToEmail) = wtkPrepEmail($pgActionType, $pgForUserUID);
            $pgSubject   = wtkReplace($pgSubject, '@SkuNumber@', $pgParam1Str);
            $pgEmailBody = wtkReplace($pgEmailBody, '@SkuNumber@', $pgParam1Str);
            // modify below based on your company's needs
            // you could even pass the inventory.UID in Param1UID then do lookups for
            // very detailed and customized emails
            switch ($pgParam2Str):
                case 'support':
                    $pgExtra = '<p>Our Support hours are 9am to 4pm, Monday through Friday.</p>';
                    break;
                case 'warranty':
                    $pgExtra = '<p>There is a 30-day money back guarantee on most products.</p>';
                    break;
                default:
                    $pgExtra = '';
                    break;
            endswitch;
            $pgEmailBody = wtkReplace($pgEmailBody, '@Param2Str@', $pgExtra);
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
    wtkSqlExec("UPDATE `wtkBackgroundActions` SET `CompletedTime` = NOW() WHERE `UID` = $pgUID", []);

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
