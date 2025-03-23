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
SELECT `UID`, `Priority`, `GroupName`
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

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '  <h4><a onclick="JavaScript:wtkGoBack()">Menu Sets</a> > ' . $pgSetName . '</h4>'. "\n";
$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloRNG], 'wtkMenuGroups', '/admin/menuGroupList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";
$pgHtm .= $pgRefresh . "\n";

echo $pgHtm;
exit;
?>
