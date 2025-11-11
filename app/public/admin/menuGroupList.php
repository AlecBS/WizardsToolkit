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
SELECT `UID`, `GroupName` AS `MenuGrouping`,
  CONCAT('<a class="btn btn-floating wtkdrag" draggable="true"',
    ' data-id="', `UID`, '"',
    ' data-pos="', ROW_NUMBER() OVER(ORDER BY `Priority`), '"',
    ' ondragstart="wtkDragStart(this);" ondrop="wtkDropId(this)" ondragover="wtkDragOver(event)">',
    '<i class="material-icons" alt="drag to change priority" title="drag to change priority">drag_handle</i></a>')
    AS `Prioritize`
  FROM `wtkMenuGroups`
 WHERE `DelDate` IS NULL AND `MenuUID` = ?
ORDER BY `Priority` ASC
SQLVAR;

$gloEditPage = 'menuGroupEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkMenuGroupsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloColumnAlignArray = array (
   'Priority'   => 'center'
);
$gloMoreButtons = array(
    'MenuItems' => array(
            'act' => 'menuItemList',
            'img' => 'list'
            )
    );

$pgSetName = wtkSqlGetOneResult('SELECT `MenuName` FROM `wtkMenuSets` WHERE `UID` = ?', [$gloRNG]);

$pgHtm =<<<htmVAR
<div class="container">
    <h4><a onclick="JavaScript:wtkGoBack()">Menu Sets</a> > $pgSetName </h4>
    <p>&ldquo;Menu Grouping&rdquo; will be shown across the top navbar.</p>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloRNG], 'wtkMenuGroups', '/admin/menuGroupList.php');
$pgHtm  = wtkReplace($pgHtm, 'No data.','no menu groups yet');
$pgHtm .=<<<htmVAR
    </div><br>
</div>
$pgRefresh
<input type="hidden" id="wtkDragTable" value="wtkMenuGroups">
<input type="hidden" id="wtkDragColumn" value="Priority">
<input type="hidden" id="wtkDragFilter" value="$gloRNG">
<input type="hidden" id="wtkDragRefresh" value="/admin/menuGroupList">
<input type="hidden" id="wtkDragLocation" value="table">
htmVAR;

echo $pgHtm;
exit;
?>
