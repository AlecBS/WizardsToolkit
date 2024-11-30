<?PHP
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSqlFilter = array('UID' => $gloId);

$pgSQL =<<<SQLVAR
DELETE FROM `wtkVisitors` WHERE `AffiliateUID` = :UID AND DATE_FORMAT(`AddDate`,'%Y-%m-%d') = CURRENT_DATE
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
UPDATE `wtkAffiliates`
 SET `SignedDate` = NULL
WHERE `UID` = :UID
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

echo '{"result":"ok"}';
exit;
?>
