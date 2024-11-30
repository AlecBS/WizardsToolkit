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

$pgSQL =<<<SQLVAR
SELECT m.`UID`, m.`Priority`, p.`PageName`
 FROM `wtkMenuItems` m
   LEFT OUTER JOIN `wtkPages` p
    ON p.`UID` = m.`PgUID`
WHERE m.`DelDate` IS NULL
 AND m.`MenuGroupUID` = ?
 ORDER BY m.`Priority` ASC
SQLVAR;

$gloEditPage = 'menuItemEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkMenuItemsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloColumnAlignArray = array (
   'Priority'   => 'center'
);

$pgGroupName = wtkSqlGetOneResult('SELECT `GroupName` FROM `wtkMenuGroups` WHERE `UID` = ?', [$gloRNG]);
$pgMenuUID = wtkSqlGetOneResult('SELECT `MenuUID` FROM `wtkMenuGroups` WHERE `UID` = ?', [$gloRNG]);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>$pgGroupName : Menu Items
        <small><a onclick="JavaScript:ajaxGo('menuGroupList',0,$pgMenuUID)" class="btn btn-small btn-save waves-effect waves-light">return</a></h4>
    <br>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloRNG], 'wtkMenuItems', '/admin/menuItemList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div></div>' . "\n";
$pgHtm .= $pgRefresh . "\n";

echo $pgHtm;
exit;
?>
