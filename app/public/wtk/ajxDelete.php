<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgTable = wtkGetPost('tbl');
$pgDelDate = wtkGetPost('date');
$pgFilter = array ('UID' => $gloId);
$pgJSON = '{"result":"ok"}';

if ($pgTable == 'wtkFiles'): // also delete the associated file
    $pgSQL = 'SELECT CONCAT(`TempDownload`,"~",`FilePath`,`NewFileName`) AS `result` FROM `wtkFiles` WHERE `UID` = :UID';
    $pgFileInfo = wtkSqlGetOneResult($pgSQL,$pgFilter,'noFile');
    $pgFileArray = array();
    $pgFileArray = explode('~', $pgFileInfo);
    $pgFileCopy = $pgFileArray[0]; // will be used later for AWS S3 or Cloudflare file storage
    $pgFileLoc = _RootPATH . $pgFileArray[1];
    if (file_exists($pgFileLoc)):
        unlink($pgFileLoc);
        $pgJSON = '{"result":"ok","msg":"deleted file"}';
    else:
        $pgJSON = '{"result":"ok","skipped":"unlink"}';
    endif;
endif;

if ($pgDelDate == 'Y'):
    wtkSqlExec("UPDATE `$pgTable` SET `DelDate` = NOW() WHERE `UID` = :UID", $pgFilter);
    $pgDelMsg = "DelDate'd this row";
else:
    wtkSqlExec("DELETE FROM `$pgTable` WHERE `UID` = :UID", $pgFilter);
    $pgDelMsg = "Deleted this row";
endif;

//$pgJSON = '{"result":"ok","table":"' . $pgTable . '","id":"' . $gloId . '"}';
echo $pgJSON;

$pgSQL =<<<SQLVAR
INSERT INTO `wtkUpdateLog` (`UserUID`,`TableName`,`FullSQL`,`ChangeInfo`,`OtherUID`)
 VALUES (:UserUID, :TableName, :FullSQL, :ChangeInfo, :OtherUID)
SQLVAR;
$pgSqlFilter = array(
    'UserUID' => $gloUserUID,
    'TableName' => $pgTable,
    'OtherUID' => $gloId,
    'FullSQL' => 'n/a',
    'ChangeInfo' => $pgDelMsg
);
wtkSqlExec($pgSQL, $pgSqlFilter);

exit; // no display needed, handled via JS and spa.htm
?>
