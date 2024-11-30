<?php
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

if ($gloRNG == 0):
    $gloRNG = $gloId;
endif;

$pgSQL = "SELECT CONCAT(COALESCE(`FirstName`,''), ' ', COALESCE(`LastName`,'')) AS `UserName`";
$pgSQL .= ' FROM `wtkUsers` WHERE `UID` = ?';
$pgUser = wtkSqlGetOneResult($pgSQL, [$gloRNG]);

$pgSQL = 'SELECT `UID`, ' . wtkSqlDateFormat('`AddDate`', 'OBAddDate') . ', `TableName`,';
$pgSQL .=<<<SQLVAR
  `AddDate` AS `LogAddDate`, `ChangeInfo`
 FROM `wtkUpdateLog`
 WHERE `UserUID` = ? AND `ChangeInfo` <> ''
ORDER BY `UID` DESC
SQLVAR;

wtkFillSuppressArray('LogAddDate');
wtkSetHeaderSort('OBAddDate', 'Date Added', 'LogAddDate');
wtkSetHeaderSort('TableName', 'Table Name');

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '  <h4>User: ' . $pgUser . '</h4><br>' . "\n";
$pgHtm .= '<div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloRNG]);
$pgHtm .= '</div></div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
