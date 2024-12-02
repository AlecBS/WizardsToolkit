<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSqlFilter = array('UID' => $gloId);
$pgMode = wtkGetPost('Mode');

$pgSQL =<<<SQLVAR
UPDATE `wtkNotifications`
  SET `@Mode@Date` = NOW(), `@Mode@ByUserUID` = $gloUserUID
WHERE `UID` = :UID AND `@Mode@Date` IS NULL
SQLVAR;
$pgSQL = wtkReplace($pgSQL, '@Mode@',$pgMode);

wtkSqlExec($pgSQL, $pgSqlFilter);
exit;
?>
