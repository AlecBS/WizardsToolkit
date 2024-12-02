<?PHP
$pgSecurityLevel = 5;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgTitle = wtkGetPost('title');
$pgVideo = wtkGetPost('vid');
$pgHText = wtkGetPost('text');

$pgSQL =<<<SQLVAR
UPDATE `wtkHelp`
 SET `HelpTitle` = :HelpTitle, `HelpText` = :HelpText, `VideoLink` = :VideoLink,
    `LastModByUserUID` = :LastModByUserUID
WHERE `UID` = :UID
SQLVAR;

$pgSqlFilter = array (
    'UID' => $gloId,
    'LastModByUserUID' => $gloUserUID,
    'HelpTitle' => $pgTitle,
    'VideoLink' => $pgVideo,
    'HelpText' => $pgHText
);

wtkSqlExec($pgSQL, $pgSqlFilter);

$pgJSON = '{"result":"success"}';
echo $pgJSON;
exit; // no display needed, handled via JS and spa.htm
?>
