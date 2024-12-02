<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSQL = "UPDATE `wtkLoginLog` SET `LogoutTime` = NOW() WHERE `UID` = :UID";
$pgSavePg = substr($gloCurrentPage, 0, 150);
$pgSQLFilter = array (
    'UID' => $pgLoginUID
);
wtkSqlExec($pgSQL, $pgSQLFilter);

unset($_SESSION[$gloAuthStatus . 'UserLevel']);
unset($_SESSION['UserUID']);
unset($_SESSION['Prototype']);
unset($_SESSION['apiKey']);
session_unset();
session_write_close();
setcookie(session_name(),'',0,'/');

// ABS 04/11/20  changed to JS and JSON method
$pgJSON  = '{"result":"ok"}';
echo $pgJSON;
exit;
?>
