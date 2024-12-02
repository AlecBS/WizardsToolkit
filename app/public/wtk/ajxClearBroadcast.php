<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgIPaddress = wtkGetIPaddress();

$pgSQL =<<<SQLVAR
INSERT INTO `wtkBroadcast_wtkUsers` (`BroadcastUID`, `UserUID`, `IpAddress`)
  VALUES (:BroadcastUID, :UserUID, :IpAddress)
SQLVAR;
$pgSqlFilter = array (
    'BroadcastUID' => $gloId,
    'UserUID' => $gloUserUID,
    'IpAddress' => $pgIPaddress
);
wtkSqlExec($pgSQL, $pgSqlFilter);

echo '{"result":"ok"}';
exit; // no display needed, handled via JS and spa.htm
?>
