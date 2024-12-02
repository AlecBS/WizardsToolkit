<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSqlFilter = array('UserUID' => $gloUserUID);
$pgSQL =<<<SQLVAR
SELECT n.`UID`, DATE_FORMAT(COALESCE(n.`StartDate`,n.`AddDate`), '$gloSqlDateTime') AS `Date`,
    n.`Icon`,n.`IconColor`,n.`NoteTitle`,n.`NoteMessage`, n.`GoToUrl`,
    COALESCE(n.`GoToId`,0) AS `GoToId`, n.`GoToRng`
 FROM `wtkNotifications` n, `wtkUsers` u
WHERE u.`UID` = :UserUID and n.`SeenDate` IS null
 AND COALESCE(n.`StartDate`,n.`AddDate`) < NOW()
    AND CASE WHEN n.`ToStaffRole` IS NULL THEN n.`ToUID` = u.`UID`
          ELSE n.`ToStaffRole` = u.`StaffRole`
    END
ORDER BY n.`UID` ASC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);

$pgTemplate =<<<htmVAR
<a id="alertId@UID@" onclick="JavaScript:wtkGoToNotification(@UID@,'@GoToUrl@',@GoToId@,@GoToRng@)">
    <span class="btn-floating btn-large @IconColor@"><i class="material-icons">@Icon@</i></span>
    <span class="mail-contnet">
        <h5>@NoteTitle@</h5>
        <span class="mail-desc">@NoteMessage@</span>
        <span class="time">@Date@</span>
    </span>
</a>
htmVAR;

$pgAlertList = '';
$pgCntr = 0;
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgTmp = $pgTemplate;
    $pgTmp = wtkReplace($pgTmp, '@UID@', $gloPDOrow['UID']);
    $pgTmp = wtkReplace($pgTmp, '@IconColor@', $gloPDOrow['IconColor']);
    $pgTmp = wtkReplace($pgTmp, '@Icon@', $gloPDOrow['Icon']);
    $pgTmp = wtkReplace($pgTmp, '@NoteTitle@', $gloPDOrow['NoteTitle']);
    $pgNoteMessage = nl2br($gloPDOrow['NoteMessage']);
    $pgTmp = wtkReplace($pgTmp, '@NoteMessage@', $pgNoteMessage);
    $pgTmp = wtkReplace($pgTmp, '@Date@', $gloPDOrow['Date']);
    $pgTmp = wtkReplace($pgTmp, '@GoToUrl@', $gloPDOrow['GoToUrl']);
    $pgTmp = wtkReplace($pgTmp, '@GoToId@', $gloPDOrow['GoToId']);
    $pgTmp = wtkReplace($pgTmp, '@GoToRng@', $gloPDOrow['GoToRng']);
    $pgAlertList .= $pgTmp;
    $pgCntr ++;
endwhile;
unset($pgPDO);

if ($pgCntr > 0):
    $pgAlertList .=<<<htmVAR
<script type="text/javascript">
$('#alertCounter').text($pgCntr);
$('#alertCounter').removeClass('hide');
</script>
htmVAR;
else:
    $pgAlertList .=<<<htmVAR
<br><br><p class="center">No notifications currently</p>
<script type="text/javascript">
$('#alertCounter').addClass('hide');
</script>
htmVAR;
endif;
echo $pgAlertList;
exit;
?>
