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
SELECT m.`UID`, p.`PageName` AS `MenuItem`,
  CONCAT('<a class="btn btn-floating wtkdrag" draggable="true"',
    ' data-id="', m.`UID`, '"',
    ' data-pos="', ROW_NUMBER() OVER(ORDER BY m.`Priority`), '"',
    ' ondragstart="wtkDragStart(this);" ondrop="wtkDropId(this)" ondragover="wtkDragOver(event)">',
    '<i class="material-icons" alt="drag to change priority" title="drag to change priority">drag_handle</i></a>')
    AS `Prioritize`
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
            <a onclick="JavaScript:ajaxGo('menuGroupList',0,$pgMenuUID)">$pgGroupName</a> > Menu Items
    </h4>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkMenuItems', '/admin/menuItemList.php');
$pgHtm  = wtkReplace($pgHtm, 'No data.','no menu items yet');
$pgHtm .=<<<htmVAR
    </div><br>
</div>
$pgRefresh
<input type="hidden" id="wtkDragTable" value="wtkMenuItems">
<input type="hidden" id="wtkDragColumn" value="Priority">
<input type="hidden" id="wtkDragFilter" value="$gloRNG">
<input type="hidden" id="wtkDragRefresh" value="/admin/menuItemList">
<input type="hidden" id="wtkDragLocation" value="table">
htmVAR;

echo $pgHtm;
exit;
?>
