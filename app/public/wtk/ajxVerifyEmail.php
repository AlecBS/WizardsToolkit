<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgEmail = wtkGetPost('wtkwtkUsersEmail');
$pgSQL = "SELECT COUNT(*) FROM `wtkUsers` WHERE `Email` = :Email AND `DelDate` IS NULL";
$pgSqlFilter = array (
    'Email' => $pgEmail
);
$pgCount = wtkSqlGetOneResult(wtkSqlPrep($pgSQL), $pgSqlFilter);
if ($pgCount == 0):
    $pgResult = 'ok';
else:
    $pgResult = 'That email account already exists.<br>Did you forget your password?';
endif;
$pgJSON = '{"result":"' . $pgResult . '", "count":"' . $pgCount . '"}';
echo $pgJSON;
?>
