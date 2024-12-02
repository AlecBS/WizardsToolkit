<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSMSchoice = wtkGetPost('sms');

$pgSQL =<<<SQLVAR
UPDATE `wtkUsers` SET `SMSEnabled` = :SMSEnabled
 WHERE `UID` = :UID
SQLVAR;
$pgSqlFilter = array (
    'UID' => $gloUserUID,
    'SMSEnabled' => $pgSMSchoice
);
wtkSqlExec($pgSQL, $pgSqlFilter);

exit; // no display needed
?>
