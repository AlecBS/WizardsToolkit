<?PHP
$pgSecurityLevel = 50;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `CurrentPage`, `PassedId`
 FROM `wtkLoginLog`
WHERE `UID` = :UID
SQLVAR;
$pgSqlFilter = array('UID' => $gloId);
wtkSqlGetRow($pgSQL, $pgSqlFilter);

$pgCurrentPage = wtkSqlValue('CurrentPage');
$gloId = wtkSqlValue('PassedId');

// BEGIN Log this change to wtkUpdateLog table
$pgPDOvalues['UID'] = $gloId;  // $gloId will be saved to wtkUpdateLog.OtherUID
wtkBuildUpdateSQL('wtkLoginLog', 'CurrentPage', $pgCurrentPage, "unlocked all by UserUID $gloUserUID");
wtkExecUpdateSQL('wtkLoginLog', 'WHERE `UID` = :UID');
$pgPDOvalues= array(); // to prevent Save.php below from having errors
//  END  Log this change to wtkUpdateLog table

$pgSQL =<<<SQLVAR
UPDATE `wtkLoginLog`
 SET `CurrentPage` = 'unlocked by UserUID $gloUserUID'
WHERE `CurrentPage` = :CurrentPage AND `PassedId` = :PassedId
SQLVAR;
$pgSqlFilter = array(
    'CurrentPage' => $pgCurrentPage,
    'PassedId' => $gloId
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
UPDATE `wtkLoginLog`
 SET `CurrentPage` = :CurrentPage
WHERE `UID` = :UID
SQLVAR;
$pgSqlFilter = array(
    'CurrentPage' => $pgCurrentPage,
    'UID' => $pgLoginUID
);
wtkSqlExec($pgSQL, $pgSqlFilter); // $pgLoginUID is set in wtk/wtkLogin.php

require('../' . $pgCurrentPage);
exit;
?>
