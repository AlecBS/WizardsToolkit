<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT wg.`UID`, wg.`WidgetGroupName`, L.`LookupDisplay` AS `SecurityLevel`
  FROM `wtkWidgetGroup` wg
  INNER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel'
    AND CAST(L.`LookupValue` AS DECIMAL) = wg.`SecurityLevel`
WHERE wg.`DelDate` IS NULL
ORDER BY wg.`UID` ASC
SQLVAR;

$gloEditPage = 'widgetGroupEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkWidgetGroupDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Widget Groups</h4><br>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkWidgetGroup', '/admin/widgetGroupList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
