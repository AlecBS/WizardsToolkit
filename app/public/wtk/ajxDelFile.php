<?PHP
// Used for deleting files based on wtkFormFile; called by wtkfDelFile in wtkFileUpload.js
define('_RootPATH', '../');
require('wtkLogin.php');

$pgTable = wtkDecode(wtkGetPost('t'));
$pgColPath = wtkGetPost('colPath');
$pgColFile = wtkGetPost('colFile');
$pgUID = wtkDecode(wtkGetPost('uid'));
$pgId = wtkGetPost('id');
$pgPath = wtkGetPost('path');
$pgDelFile = wtkGetPost('del');

$pgPriorFileName = '..' . $pgPath . $pgDelFile;
$pgPriorFileName = wtkReplace($pgPriorFileName, '....', '../..');
if (is_file($pgPriorFileName)):
    unlink($pgPriorFileName);
endif;

$pgSQL =<<<SQLVAR
UPDATE `$pgTable`
 SET `$pgColPath` = NULL, `$pgColFile` = NULL
WHERE `$pgUID` = :UID
SQLVAR;
wtkSqlPrep($pgSQL);
$pgSqlFilter = array (
    'UID' => $pgId
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgJSON = '{"result":"ok"}';
//$pgJSON = '{"result":"ok","sql":"' . $pgSQL . '","del":"' . $pgPriorFileName . '"}';
echo $pgJSON;
exit; // no display needed, handled via JS and spa.htm
?>
