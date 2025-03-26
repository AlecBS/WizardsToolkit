<?PHP
require('cronTop.php');

// BEGIN delete old files in /exports folder
$pgHtm .= '<h5>Delete Exports</h5>' . "\n";

$pgNewFileArray = array();
$pgExportFolder = array_diff(scandir('../exports'), array('.','..','index.php','.DS_Store'));
foreach ($pgExportFolder as $pgFile):
    $pgNewFile = wtkDecode(substr($pgFile,0,6));
    $pgNewFileArray[$pgNewFile] = $pgFile;
endforeach;
asort($pgNewFileArray);

$pgMinutes = 15; // this can be data-driven
$pgTooOldDate = 'w' . date('His', strtotime('-' . $pgMinutes . ' minutes'));
$pgYesterdayDate = 'w' . date('His', strtotime('+1 minutes'));
$pgCount = 0;
foreach ($pgNewFileArray as $pgDecoded => $pgFileToDel):
    $pgTmp = 'w' . $pgDecoded;
    if (($pgTmp < $pgTooOldDate) || ($pgTmp > $pgYesterdayDate)):
        unlink('../exports/' . $pgFileToDel);
        $pgCount ++;
    endif;
endforeach;
//  END  delete old files in /exports folder

// BEGIN Process Notifications for Email, SMS and creating Repeats
// Currently only Notifications will be shared with Departments; no emailing everyone in department
$pgSQL =<<<SQLVAR
SELECT n.`UID`, n.`Audience`, n.`AddedByUserUID`, n.`ToUID`,
    n.`RepeatFrequency`, u.`Email` AS `ToEmail`,
    u2.`Email` AS `ReplyToEmail`,
   `fncOnlyDigits`(u.`CellPhone`) AS `CellPhone`,
    n.`EmailAlso`, n.`SmsAlso`, n.`NoteTitle`, n.`NoteMessage`
  FROM `wtkNotifications` n
   LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = n.`ToUID`
   INNER JOIN `wtkUsers` u2 ON u2.`UID` = n.`AddedByUserUID`
 WHERE NOW() > COALESCE(n.`StartDate`,n.`AddDate`) AND n.`DelDate` IS NULL AND n.`SentDate` IS NULL
ORDER BY n.`UID` ASC
SQLVAR;
$pgSqlFilter = array();
$pgSQL = wtkSqlPrep($pgSQL);
$pgDebug = '';
$pgEmailCount = 0;
$pgSMSCount = 0;
$pgNewNotifications = 0;

$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgUID = wtkSqlValue('UID');
    $pgEmailAlso = wtkSqlValue('EmailAlso');
    $pgSmsAlso = wtkSqlValue('SmsAlso');
    $pgDebug .= 'UID: ' . $pgUID . ' EmailAlso = ' . $pgEmailAlso . '; SmsAlso = ' . $pgSmsAlso . '<br>' . "\n";
    wtkSqlExec("UPDATE `wtkNotifications` SET `SentDate` = NOW() WHERE `UID` = $pgUID", []);
    $pgAddedByUserUID = wtkSqlValue('AddedByUserUID');
    $pgToUID = wtkSqlValue('ToUID');
    $pgAudience = wtkSqlValue('Audience');
    $pgCellPhone = wtkSqlValue('CellPhone');
    $pgToEmail = wtkSqlValue('ToEmail');
    $pgReplyToEmail = wtkSqlValue('ReplyToEmail');
    $pgNoteTitle = wtkSqlValue('NoteTitle');
    $pgNoteMessage = wtkSqlValue('NoteMessage');
    $pgRepeatFrequency = wtkSqlValue('RepeatFrequency');
    $pgSaveArray = array (
        'FromUID' => $pgAddedByUserUID,
        'ToUID' => $pgToUID,
        'EmailType' => 'Remind',
        'OtherUID' => $pgUID
    );
    switch ($pgAudience):
        case 'S': // Staff
            if ($pgSmsAlso == 'Y'):
                if ($pgCellPhone != ''):
                    wtkSendSMS($pgCellPhone, $pgNoteMessage, 'SMS', $pgAddedByUserUID, $pgToUID);
                //  $pgDebug .= "wtkSendSMS($pgCellPhone, $pgNoteMessage, 'SMS', $pgAddedByUserUID, $pgToUID)<br>" . "\n";
                    $pgSMSCount ++;
                endif;
            endif;
            if ($pgEmailAlso == 'Y'):
                if ($pgToEmail != ''):
                    $pgNoteMessage = nl2br($pgNoteMessage);
                    wtkNotifyViaEmail($pgNoteTitle,$pgNoteMessage,$pgToEmail,$pgSaveArray,'','default',$pgReplyToEmail);
                //  $pgDebug .= "wtkNotifyViaEmail($pgNoteTitle,$pgNoteMessage,$pgToEmail,pgSaveArray,'','default',$pgReplyToEmail);<br>" . "\n";
                    $pgEmailCount ++;
                endif;
            endif;
            break;
        default: // Department based on StaffRole
            // add wtkNotifications
            break;
    endswitch;
    switch ($pgRepeatFrequency):
        case 'W':
        case 'M':
            if ($pgRepeatFrequency == 'W'):
                $pgRepeatFrequency = 'WEEK';
            else:
                $pgRepeatFrequency = 'MONTH';
            endif;

            $pgSQL =<<<SQLVAR
INSERT INTO `wtkNotifications`
  (`StartDate`,
  `AddDate`,`AddedByUserUID`,`Audience`,`ToUID`,`ToStaffRole`,`Icon`,`IconColor`,`NoteTitle`,`NoteMessage`,
  `GoToUrl`,`GoToId`,`GoToRng`,`EmailAlso`,`SmsAlso`,`RepeatFrequency`)
  SELECT @DateAdjust@,
  `AddDate`,`AddedByUserUID`,`Audience`,`ToUID`,`ToStaffRole`,`Icon`,`IconColor`,`NoteTitle`,`NoteMessage`,
  `GoToUrl`,`GoToId`,`GoToRng`,`EmailAlso`,`SmsAlso`,`RepeatFrequency`
   FROM `wtkNotifications`
  WHERE `UID` = $pgUID
SQLVAR;
            // removed AND `StopRepeatDate` > NOW()
            if ($gloDriver1 == 'pgsql'):
                $pgReplace = "(`StartDate` + INTERVAL '1 $pgRepeatFrequency') as `StartDate`" . "\n";
            else:
                $pgReplace = "DATE_ADD(`StartDate`, INTERVAL 1 $pgRepeatFrequency)" . "\n";
            endif;
            $pgSQL = wtkReplace($pgSQL, '@DateAdjust@',$pgReplace);
            wtkSqlExec($pgSQL, []);
            $pgNewNotifications ++;
            break;
    endswitch;
endwhile;
unset($pgPDO2);
//  END  Process Notifications for Email, SMS and creating Repeats

/* If you store files in AWS S3 or Cloudflare R2 you can use below code for automatically migrating them there

// BEGIN MOVE UPLOADED FILES FROM LOCAL STORAGE TO AWS S3 or Cloudflare R2
// Get all files that haven't been uploaded to S3. Limit to 50 to avoid processes taking too long.
$pgSQL =<<<SQLVAR
SELECT `UID`, to_char("AddDate", 'YY/Mon') AS "ExtPath", `FilePath`, `NewFileName`
  FROM `wtkFiles`
WHERE `ExternalStorage` = :ExternalStorage AND `CurrentLocation` = :CurrentLocation
ORDER BY `UID` ASC LIMIT 3
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgSqlFilter = array (
    'ExternalStorage' => 'N',
    'CurrentLocation' => 'L'
);

$pgTotalCount = 0;
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);

try {
    while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)) {
        if ($pgTotalCount == 0):
            require_once('../wtk/lib/DocumentStorageService.php');
            $pgDocumentStorageService = new DocumentStorageService(null);
        endif;
        $pgUID = $gloPDOrow['UID'];

        $pgExtPath = $gloPDOrow['ExtPath'];
        $pgFilePath = $gloPDOrow['FilePath'];
        $pgFileName = $gloPDOrow['NewFileName'];

        if (!file_exists($pgFilePath . $pgFileName)): //check file exists (possibly moved by other CRON job)
            // so next CRON job does not process if large file and taking a long time to move
            wtkSqlExec("UPDATE `wtkFiles` SET `ExternalStorage` = 'P' WHERE `UID` = $pgUID", []);
            //move to Cloudflare R2 or AWS S3
            $pgDocumentStorageService->create($pgFileName, file_get_contents(_RootPATH . $pgFilePath . $pgFileName), $pgExtPath);

            //mark file as uploaded to S3 in wtkFiles
            wtkSqlExec("UPDATE `wtkFiles` SET `ExternalStorage` = 'Y', `CurrentLocation` = 'C' WHERE `UID` = $pgUID", []);
            unlink(_RootPATH . $pgFilePath . $pgFileName); //remove file from local storage
        else:
            // notify tech support of problem
            $pgError = 'wtkFiles.UID = ' . $pgUID . ' failed to copy to Cloudflare R2';
            wtkNotifyViaEmail('wtkFiles Problem', $pgError, $gloTechSupport);
        endif;

        $pgTotalCount++;
    }
} catch (Exception $e) {
    unset($pgPDO);
    echo '<br>Exception Error:' . "\n";
    exit($e->getMessage());
} catch (Throwable $e) {
    echo '<br>Throwable Error:' . "\n";
    unset($pgPDO);
    exit($e->getMessage());
}
unset($pgPDO);
//  END  MOVE UPLOADED FILES FROM LOCAL STORAGE TO AWS S3 or Cloudflare R2
*/

$pgHtm .= '<br><h5>Summary</h5>' . "\n";
// $pgHtm .= '<p>' . $pgTotalCount . ' files uploaded to Cloudflare R2.</p>' . "\n";
$pgHtm .= '<p>' . $pgCount . ' old files deleted from /exports/ folder.</p>' . "\n";
$pgHtm .= '<p>Sent ' . $pgEmailCount . ' email reminders.</p>' . "\n";
$pgHtm .= '<p>Sent ' . $pgSMSCount . ' SMS reminders.</p>' . "\n";
$pgHtm .= '<p>Created ' . $pgNewNotifications . ' notification.</p>' . "\n";
$pgHtm .= '<hr>' . $pgDebug . "\n";

require('cronEnd.php');
?>
