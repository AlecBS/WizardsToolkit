<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
endif;

$pgFilter = wtkGetPost('Filter');
$pgReset = wtkGetPost('Reset');
$pgShowAll = wtkFilterRequest('wtkFilter');
$pgShowFuture = wtkFilterRequest('wtkFilter2');
$pgShowCompleted = wtkFilterRequest('wtkFilter3');

$pgStaffRole = wtkFilterRequest('wtkFilterStaffRole');
if ($pgStaffRole == 'ALL'):
    $pgDept = 'or for any';
    $pgDeptSQL = 'L.`LookupDisplay`';
else:
	if ($pgStaffRole != ''):
        $pgDept = wtkSqlGetOneResult("SELECT `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'StaffRole' AND `LookupValue` = ?", [$pgStaffRole]);
    else: // not previosly set
        $pgSQL =<<<SQLVAR
SELECT CONCAT(u.`StaffRole`,'~',L.`LookupDisplay`) AS `DeptArray`
 FROM `wtkUsers` u
   LEFT OUTER JOIN `wtkLookups` L
    ON L.`LookupType` = 'StaffRole' AND L.`LookupValue` = u.`StaffRole`
WHERE u.`UID` = ?
SQLVAR;
        $pgDeptInfo  = wtkSqlGetOneResult($pgSQL, [$gloUserUID]);
        $pgDeptArray = explode('~',$pgDeptInfo);
        $pgStaffRole = $pgDeptArray[0];
        $pgDept = $pgDeptArray[1];
    endif;
    $pgDeptSQL = "'" . wtkReplace($pgDept,"'", "''") . "'"; // for PostgreSQL
    $pgDept = 'and for ' . $pgDept;
endif;

$pgSQL =<<<SQLVAR
SELECT n.`UID`, DATE_FORMAT(n.`AddDate`, '$gloSqlDateTime') AS `Date`,
    DATE_FORMAT(n.`SeenDate`, '$gloSqlDateTime') AS `SeenDate`,
    n.`Icon`, n.`IconColor`, n.`NoteTitle`, n.`NoteMessage`,
    n.`GoToUrl`, n.`GoToId`, n.`GoToRng`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `CreatedBy`,
    CASE
      WHEN n.`Audience` = 'S' THEN CONCAT(COALESCE(u2.`FirstName`,''), ' ', COALESCE(u2.`LastName`,''))
      WHEN n.`Audience` = 'D' THEN $pgDeptSQL
    END AS `CreatedFor`
 FROM `wtkNotifications` n
  INNER JOIN `wtkUsers` u ON u.`UID` = n.`AddedByUserUID`
  LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = n.`ToUID`
  LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'StaffRole' AND L.`LookupValue` = n.`ToStaffRole`
WHERE @CurrentOrFuture@
 AND
    CASE
      WHEN n.`Audience` = 'S' THEN n.`ToUID` = :UserUID
      WHEN n.`Audience` = 'D' THEN n.`ToStaffRole` = :StaffRole
    END
SQLVAR;
if ($pgStaffRole == 'ALL'):
    $pgSQL = wtkReplace($pgSQL, "WHEN n.`Audience` = 'D' THEN n.`ToStaffRole` = :StaffRole", "ELSE n.`Audience` <> 'S'");
    $pgSqlFilter = array('UserUID' => $gloUserUID);
else:
    $pgSqlFilter = array (
        'UserUID'  => $gloUserUID,
        'StaffRole' => $pgStaffRole
    );
endif;
if ($pgShowFuture == 'Y'):
    $pgShowFuture = 'checked';
    $pgReplace = '(n.`StartDate` IS NOT NULL AND NOW() < n.`StartDate`)';
else:
    $pgShowFuture = '';
    $pgReplace = '(n.`StartDate` IS NULL OR NOW() >= n.`StartDate`)';
endif;
$pgSQL = wtkReplace($pgSQL, '@CurrentOrFuture@', $pgReplace);

if ($pgShowAll == 'Y'):
    $pgShowAll = 'checked';
else:
    $pgSQL .= ' AND n.`SeenDate` IS NULL';
	$pgShowAll = '';
endif;
if ($pgShowCompleted == 'Y'):
    $pgShowCompleted = 'checked';
    $pgSQL .= ' AND n.`CloseDate` IS NOT NULL';
else:
    $pgShowCompleted = '';
    $pgSQL .= ' AND n.`CloseDate` IS NULL';
endif;
$pgSQL .= ' ORDER BY n.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);
// echo '<br>$pgSQL: value = ' . $pgSQL . "\n";

$pgTemplate =<<<htmVAR
<tr>
  <td>
    <div class="row">
        <div class="col m1 s12">
            <a onclick="JavaScript:wtkGoToNotification(@UID@,'@GoToUrl@',@GoToId@,@GoToRng@)">
            <span class="btn-floating btn-large @IconColor@"><i class="material-icons">@Icon@</i></span>
            </a>
        </div>
        <div class="col m3 s12">
            <h6>For: @CreatedFor@</h6>
            <p>Seen: @SeenDate@</p>
            <p>Created By: @CreatedBy@<br>@Date@</p>
        </div>
        <div class="col m7 s12">
            <h5>@NoteTitle@</h5>
            <p>@NoteMessage@</p>
        </div>
        <div class="col m1 s12">
            <a onclick="JavaScript:wtkModal('wtk/notificationEdit','EDIT',@UID@)">
            <span class="btn-floating"><i class="material-icons">edit</i></span>
            </a>
        </div>
    </div>
  </td>
</tr>
htmVAR;

$pgNotificationList = '';
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
    $pgTmp = wtkReplace($pgTmp, '@CreatedBy@', $gloPDOrow['CreatedBy']);
    $pgTmp = wtkReplace($pgTmp, '@CreatedFor@', $gloPDOrow['CreatedFor']);
    $pgSeenDate = $gloPDOrow['SeenDate'];
    if ($pgSeenDate == ''):
        $pgTmp = wtkReplace($pgTmp, '<p>Seen: @SeenDate@</p>', '');
    else:
        $pgTmp = wtkReplace($pgTmp, '@SeenDate@', $pgSeenDate);
    endif;
    $pgNotificationList .= $pgTmp;
    $pgCntr ++;
endwhile;
if ($pgNotificationList == ''):
    $pgNotificationList =<<<htmVAR
<table id="notificationList">
    <tr><td>
    <p>no notifications for you currently</p>
    </td></tr>
</table>
htmVAR;
else:
    $pgNotificationList = '<table id="notificationList">' . $pgNotificationList . '</table>';
endif;

unset($pgPDO);
if (isset($pgGoTo) || ($pgFilter == 'Y') || ($pgReset == 'Y')): // must have come from Save.php or Filtering
    echo $pgNotificationList;
    exit;
endif;

// BEGIN Department filtering
$pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay` AS `Display`
  FROM `wtkLookups`
 WHERE `DelDate` IS NULL AND `LookupType` = :Filter
ORDER BY `UID` ASC
SQLVAR;
$pgSqlFilter = array('Filter' => 'StaffRole');
$gloWTKmode = 'ADD';
$pgRoleList  = wtkFormSelect('Filter', 'StaffRole', $pgSQL, $pgSqlFilter, 'Display', 'LookupValue','Change Department', 'm4 s12', 'Y');
$pgRoleList  = wtkReplace($pgRoleList, 'value="' . $pgStaffRole . '"', 'value="' . $pgStaffRole . '" SELECTED');
$pgRoleList  = wtkReplace($pgRoleList, 'value=""><', 'value="ALL">All<');
//  END  Department filtering

$pgHtm =<<<htmVAR
    <div class="card">
        <div class="card-content">
            <h2>Notifications
                <small class="right"><a onclick="JavaScript:wtkModal('wtk/notificationEdit','ADD')" class="btn">Create Notification</a></small>
            </h2><br><br>
            <form id="wtkFilterForm">
                <input type="hidden" name="Filter" id="Filter" value="Y">
                <div class="row">
                    <div class="col m4 s12">
                        <div class="switch">
                            <label>Un-viewed Only
                              <input id="wtkFilter" name="wtkFilter" $pgShowAll value="Y" type="checkbox">
                              <span class="lever"></span>Show All
                            </label>
                        </div>
                    </div>
                    <div class="col m4 s12">
                        <div class="switch">
                            <label>Current
                              <input id="wtkFilter2" name="wtkFilter2" $pgShowFuture value="Y" type="checkbox">
                              <span class="lever"></span>Future
                            </label>
                        </div>
                    </div>
                    <div class="col m4 s12">
                        <div class="switch">
                            <label>Uncompleted
                              <input id="wtkFilter3" name="wtkFilter3" $pgShowCompleted value="Y" type="checkbox">
                              <span class="lever"></span>Completed
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col m7 s12">
                        <br><p>This is currently showing all notifications specifically for you $pgDept department.</p>
                    </div>
                    $pgRoleList
                    <div class="col m1 s12">
                        <button onclick="Javascript:wtkBrowseFilter('wtk/notificationList','notificationList')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light" style="margin:16px;position:initial"><i class="material-icons tiny">search</i></button>
                    </div>
                </div>
            </form>
            <br><br><br>
            <div id="notificationListDIV">
                $pgNotificationList
            </div>
        </div>
    </div>
htmVAR;
$pgHtm .= wtkFormHidden('HasSelect', 'Y');

echo $pgHtm;
wtkAddUserHistory();
exit;
?>
