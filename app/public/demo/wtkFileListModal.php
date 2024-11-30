<?php
$gloLoginRequired = false;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `Description`, CONCAT(`FilePath`, `NewFileName`) AS `PathAndFile`,
    CONCAT('<a class="btn btn-floating" onclick="JavaScript:wtkGoToURL(\'/wtk/viewFile\',', CRC32(`UID`),',0,\'targetBlank\')">',
    '<i class="material-icons">visibility</i>') AS `View`
 FROM `wtkFiles`
WHERE `TableRelation` = 'demo' AND `DelDate` IS NULL
ORDER BY `UID` DESC
SQLVAR;
if ($gloDriver1 == 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, 'CRC32(`UID`)','CRC32(CAST(`UID` AS VARCHAR))');
endif;
$gloColumnAlignArray = array (
	'View' => 'center'
);
$gloEditPage = 'wtkFileEditModal';
//$gloEditPage = '/wtk/fileEdit';  // uncomment to see how works with normal wtk/fileEdit.php
$gloAddPage  = $gloEditPage;
// maybe not needed in original wtkFileList.php ???
// $gloRNG = 13; // set to the value you want used for ParentUID by Add page

$pgList = wtkBuildDataBrowse($pgSQL, [], 'wtkFilesDIV', '','Y');
$pgList = wtkReplace($pgList, 'No data.','no documents yet');
$pgList = wtkReplace($pgList, "wtkFileEditModal','ADD'","wtkFileEditModal','demo'"); // pass TableRelation in Mode parameter
if (wtkGetParam('T') != ''): // refresh from edit page
    echo $pgList;
    exit;
endif;
$pgHtm =<<<htmVAR
<div class="container">
    <div class="card">
        <div class="card-content">
            <h4>wtkFiles List</h4>
            <p>$pgList</p>
        </div>
    </div>
</div>
htmVAR;

if ($gloSiteDesign == 'SPA'):
    echo $pgHtm;  // SPA Method
else: // MPA Method
    wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
endif;
?>
