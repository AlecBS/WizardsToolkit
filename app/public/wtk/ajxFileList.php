<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
endif;

//$pgTableRel = wtkGetPost('tabRel');
$pgTableRel = wtkGetPost('wtkwtkFilesTableRelation');
$pgParentUID = wtkGetPost('wtkwtkFilesParentUID');
$gloRNG = $pgParentUID;

$pgSqlFilter = array (
    'TableRelation' => $pgTableRel
);

$pgSQL =<<<SQLVAR
SELECT `UID`, DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `Uploaded`,
    COALESCE(`Description`,`OrigFileName`) AS `Description`,
    IF (`NewFileName` IS NULL, 'none',
      CONCAT('<a class="btn btn-floating" onclick="JavaScript:wtkGoToURL(\'/wtk/viewFile\',', CRC32(`UID`),',0,\'targetBlank\')">',
        '<i class="material-icons">visibility</i>')) AS `View`
 FROM `wtkFiles`
WHERE `TableRelation` = :TableRelation
  AND `DelDate` IS NULL
SQLVAR;
if ($gloDriver1 == 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, 'CRC32(`UID`)','CRC32(CAST(`UID` AS VARCHAR))');
endif;
if (($pgParentUID != 0) && ($pgParentUID != '')):
    $pgSQL .= ' AND `ParentUID` = :UID';
    $pgSqlFilter['UID'] = $pgParentUID;
endif;
$pgSQL .= ' ORDER BY `UID` DESC';

$gloEditPage = '/wtk/fileEdit';
$gloAddPage  = $gloEditPage;
if ($gloUserSecLevel >= 80): // Manager
    $gloDelPage = 'wtkFilesDelDate';
endif;

$pgDocsList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkFilesDIV', '','Y');
$pgDocsList = wtkReplace($pgDocsList, "'/wtk/fileEdit','ADD'","'/wtk/fileEdit','" . $pgTableRel . "'");
$pgDocsList = wtkReplace($pgDocsList, 'No data.','no documents yet');
$pgDocsList = wtkReplace($pgDocsList, '<tr id="DwtkFilesDIV','<tr id="DwtkFiles');
echo $pgDocsList;
//wtkShowTimeTracks();
exit;
?>
