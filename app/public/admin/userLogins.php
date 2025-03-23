<?php
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
if ($gloRNG == 0):
    $gloRNG = $gloId;
endif;

$pgSQL  = "SELECT CONCAT(COALESCE(`FirstName`,''), ' ', COALESCE(`LastName`,'')) AS `UserName`";
$pgSQL .= ' FROM `wtkUsers` WHERE `UID` = ?';
$pgUser = wtkSqlGetOneResult($pgSQL, [$gloRNG]);

$pgSQL  = 'SELECT ' . wtkSqlDateFormat('FirstLogin') . ',' . wtkSqlDateFormat('LastLogin') . ', `CurrentPage`';
$pgSQL .= ' FROM `wtkLoginLog` WHERE `UserUID` = ? ORDER BY `UID` DESC';

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '  <h4><a onclick="JavaScript:wtkGoBack()">Users</a> > ' . $pgUser . '</h4>' . "\n";
$pgHtm .= '<div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloRNG]);
$pgHtm .= '</div><br></div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
