<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
    $pgRefresh = '';
else: // returning from save of menuGroupEdit
	$gloRNG = wtkGetParam('rng');
    $pgRefresh = wtkFormHidden('refreshMenu', 'Y');
endif;

$pgSqlFilter = array('MenuGroupUID' => $gloRNG);

$pgSQL =<<<SQLVAR
SELECT s.`MenuName`, g.`MenuUID`, g.`GroupName`
  FROM `wtkMenuGroups` g
    INNER JOIN `wtkMenuSets` s ON s.`UID` = g.`MenuUID`
WHERE g.`UID` = :MenuGroupUID
SQLVAR;
wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgMenuUID  = wtkSqlValue('MenuUID');
$pgMenuName = wtkSqlValue('MenuName');
$pgGroupName = wtkSqlValue('GroupName');

$pgSQL =<<<SQLVAR
SELECT m.`UID`, m.`Priority`, p.`PageName`
 FROM `wtkMenuItems` m
   LEFT OUTER JOIN `wtkPages` p
    ON p.`UID` = m.`PgUID`
WHERE m.`DelDate` IS NULL
 AND m.`MenuGroupUID` = :MenuGroupUID
 ORDER BY m.`Priority` ASC
SQLVAR;

$gloEditPage = 'menuItemEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkMenuItemsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloColumnAlignArray = array (
   'Priority'   => 'center'
);

$pgHtm =<<<htmVAR
<div class="container">
    <h4><a onclick="Javascript:ajaxGo('menuSetList');">$pgMenuName</a> >
            <a onclick="JavaScript:wtkGoBack()">$pgGroupName</a> > Menu Items
    </h4>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkMenuItems', '/admin/menuItemList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";
$pgHtm .= $pgRefresh . "\n";

echo $pgHtm;
exit;
?>
