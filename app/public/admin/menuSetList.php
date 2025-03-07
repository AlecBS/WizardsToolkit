<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `MenuName`, `Description`
  FROM `wtkMenuSets`
 WHERE `DelDate` IS NULL
ORDER BY `MenuName` ASC
SQLVAR;

$gloEditPage = 'menuSetEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkMenuSetsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloMoreButtons = array(
    'MenuGroups' => array(
            'act' => 'menuGroupList',
            'img' => 'list'
            )
    );

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h4>Menu Sets</h4><br>' . "\n";
$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkMenuSets', '/admin/menuSetList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
