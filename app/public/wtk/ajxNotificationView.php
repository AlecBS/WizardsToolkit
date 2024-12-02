<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSqlFilter = array('UID' => $gloId);
$pgSQL =<<<SQLVAR
UPDATE `wtkNotifications`
  SET `SeenDate` = NOW(), `SeenByUserUID` = $gloUserUID
WHERE `UID` = :UID AND `SeenDate` IS NULL
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
SELECT `UID`, DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `Date`,
    `Icon`,`IconColor`,`NoteTitle`,`NoteMessage`, `GoToUrl`,
    COALESCE(`GoToId`,0) AS `GoToId`, `GoToRng`
 FROM `wtkNotifications`
WHERE `UID` = :UID
SQLVAR;
wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgIconColor = wtkSqlValue('IconColor');
$pgIcon = wtkSqlValue('Icon');
$pgNoteTitle = wtkSqlValue('NoteTitle');
$pgNoteMessage = nl2br(wtkSqlValue('NoteMessage'));
$pgDate = wtkSqlValue('Date');

$pgHtm =<<<htmVAR
<div class="modal-content">
    <div class="row">
        <div class="col m1 s3">
            <span class="btn-floating btn-large $pgIconColor"><i class="material-icons">$pgIcon</i></span>
        </div>
        <div class="col m10 offset-m1 s9">
            <h5>$pgNoteTitle</h5>
            <span class="mail-desc">$pgNoteMessage</span>
            <span class="time">$pgDate</span>
        </div>
    </div>
</div>
<div id="modFooter" class="modal-footer right">
    <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</button>
    &nbsp;&nbsp;
    <button type="button" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="Javascript:modalSave('ajxNotificationSave','yourDiv')">Save</button>
</div>
htmVAR;

echo $pgHtm;
exit;
?>
