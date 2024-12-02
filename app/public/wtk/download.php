<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$gloId = wtkGetGet('id');

$pgSQL =<<<SQLVAR
INSERT INTO `wtkDownloadTracking` (`DownloadUID`, `IPaddress`)
 VALUES (:DownloadUID, :IPaddress)
SQLVAR;
$pgIPaddress = wtkGetIPaddress();
$pgSqlFilter = array (
    'DownloadUID' => $gloId,
    'IPaddress' => $pgIPaddress
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
SELECT `FileName`, `FileDescription`, `FileLocation`
  FROM `wtkDownloads`
WHERE `UID` = :UID
SQLVAR;
$pgSqlFilter = array (
    'UID' => $gloId
);
wtkSqlGetRow($pgSQL, $pgSqlFilter);

$pgFileName = wtkSqlValue('FileName');
$pgFileDescription = wtkSqlValue('FileDescription');
$pgFileDescription = nl2br($pgFileDescription);
$pgFileLocation = wtkSqlValue('FileLocation');

$pgHtm =<<<htmVAR
<h2 class="center">$pgFileName</h2><br>
<div class="center">
    <p>$pgFileDescription</p>
    <br>
    <a href="$pgFileLocation" target="_blank" class="btn btn-large btn-primary">Download</a>
</div><br>
htmVAR;
wtkSearchReplace('col m4 offset-m4 s12','col m6 offset-m3 s12');
wtkMergePage($pgHtm, $gloCoName, 'htm/minibox.htm');
?>
