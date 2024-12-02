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
SELECT n.`NoteTitle`, n.`NoteMessage`,
    DATE_FORMAT(n.`AddDate`, '$gloSqlDateTime') AS `CreatedDate`,
    DATE_FORMAT(n.`SeenDate`, '$gloSqlDateTime') AS `SeenDate`,
    DATE_FORMAT(n.`CloseDate`, '$gloSqlDateTime') AS `CloseDate`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `CreatedBy`,
    CONCAT(COALESCE(u3.`FirstName`,''), ' ', COALESCE(u3.`LastName`,'')) AS `SeenBy`,
    CONCAT(COALESCE(u4.`FirstName`,''), ' ', COALESCE(u4.`LastName`,'')) AS `ClosedBy`,
    CASE
      WHEN n.`Audience` = 'S' THEN CONCAT(COALESCE(u2.`FirstName`,''), ' ', COALESCE(u2.`LastName`,''))
      WHEN n.`Audience` = 'D' THEN L.`LookupDisplay`
    END AS `CreatedFor`
  FROM `wtkNotifications` n
    INNER JOIN `wtkUsers` u ON u.`UID` = n.`AddedByUserUID`
    LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = n.`ToUID`
    LEFT OUTER JOIN `wtkUsers` u3 ON u3.`UID` = n.`SeenByUserUID`
    LEFT OUTER JOIN `wtkUsers` u4 ON u4.`UID` = n.`CloseByUserUID`
    LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'StaffRole' AND L.`LookupValue` = n.`ToStaffRole`
WHERE n.`UID` = :UID
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgCreatedBy = wtkSqlValue('CreatedBy');
$pgCreatedDate = wtkSqlValue('CreatedDate');
$pgCloseDate = wtkSqlValue('CloseDate');
$pgNoteTitle = wtkSqlValue('NoteTitle');
$pgNoteMessage = nl2br(wtkSqlValue('NoteMessage'));

if ($pgCloseDate == ''):
    $pgBtns = wtkModalUpdateBtns('wtk/lib/Save','notificationListDIV');
    $pgBtns = wtkReplace($pgBtns, '>Save<','>Mark Closed<');
    $pgBtns = wtkReplace($pgBtns, 'wtk/lib/Save','wtk/ajxNotificationClose');
    $pgCloseMsg = '';
else:
    $gloForceRO = true;
    $pgBtns = wtkModalUpdateBtns('wtk/lib/Save','notificationListDIV');
    $pgClosedBy = wtkSqlValue('ClosedBy');
    $pgCloseMsg = " <br>Closed by $pgClosedBy on $pgCloseDate";
endif;

$pgSeenDate = wtkSqlValue('SeenDate');
if ($pgSeenDate == ''):
    $pgSeenMsg = '';
else:
    $pgSeenBy = wtkSqlValue('SeenBy');
    $pgSeenMsg = "<br>Seen by $pgSeenBy on $pgSeenDate";
endif;

if ($gloDeviceType == 'phone'):
    // 2FIX need to make work on phones
    $pgHtm =<<<htmVAR
<form id="FnotificationListDIV" name="FnotificationListDIV" class="white">
    <div class="row">
        <div class="col m12 s12">Need to finish - currently not set up to work on phones.</div>
    </div>
</form>
<div id="modFooter" class="modal-footer right">
$pgBtns
</div>
htmVAR;
else:
    $pgHtm =<<<htmVAR
<div class="modal-content">
    <h3>Notification<span class="right">$pgBtns</span></h3>
  <form id="FnotificationListDIV" name="FnotificationListDIV">
    <br><input type="hidden" id="id" name="id" value="$gloId">
        <input type="hidden" id="rng" name="rng" value="$gloRNG">
    <div class="card">
        <div class="card-content">
            <div class="row">
                <div class="col m12 s12">
                    <h3>$pgNoteTitle</h3>
                    <br><p>$pgNoteMessage</p>
                    <hr>
                    <p>Created by $pgCreatedBy on $pgCreatedDate
                      $pgSeenMsg
                      $pgCloseMsg
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
htmVAR;
endif;
echo $pgHtm;
wtkAddUserHistory();
exit;
?>
