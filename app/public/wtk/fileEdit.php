<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

// `ViewLink` is required for wtkFormFile so can pass to /wtk/viewFile.php
if ($gloDriver1 == 'pgsql'):
    $pgSQL =<<<SQLVAR
SELECT `UID`, DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `AddDate`,
    CRC32(CAST(`UID` AS VARCHAR)) AS `ViewLink`, `Description`, `ParentUID`,
    `TableRelation`, `FilePath`, `NewFileName`,`TempDownload`,`OrigFileName`
  FROM `wtkFiles`
WHERE `UID` = ?
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT `UID`, DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `AddDate`,
    CRC32(`UID`) AS `ViewLink`, `Description`, `ParentUID`,
    `TableRelation`, `FilePath`, `NewFileName`,`TempDownload`,`OrigFileName`
  FROM `wtkFiles`
WHERE `UID` = ?
SQLVAR;
endif;

$pgMode = wtkGetParam('Mode');
if ($pgMode == 'EDIT'):
    $gloForceRO = wtkPageReadOnlyCheck('/wtk/fileEdit.php', $gloId);
    $pgForceRO = $gloForceRO;
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgTableRel = wtkSqlValue('TableRelation');
    $pgAddDate = '<small class="right">uploaded: ' . wtkSqlValue('AddDate') . '</small>';
    $pgTitle = 'Document';
else:
    $pgTitle = 'Upload Document';
    $pgTableRel = $pgMode;
    $gloWTKmode = 'ADD';
	$gloId = 0;
    $pgAddDate = '';
endif;

$pgBtns = wtkModalUpdateBtns('/wtk/lib/Save','wtkFilesDIV');

$pgHtm =<<<htmVAR
<div class="modal-content">
    <h4>$pgTitle $pgAddDate</h4>
        <form id="FwtkFilesDIV" name="FwtkFilesDIV" method="POST">
        <span id="formMsg" class="red-text">$gloFormMsg</span>
        <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkFiles', 'Description');
$pgTmp  = wtkFormFile('wtkFiles', 'FilePath','../docs/' . $pgTableRel . '/','NewFileName','Pick File','m6 s12','','N','','N','FwtkFilesDIV');
if ($gloWTKmode == 'ADD'):
    $pgTmp = wtkReplace($pgTmp, '<input type="file"','<input required="required" type="file"');
endif;
$pgHtm .= wtkReplace($pgTmp, '<table>','<table style="margin-top: -25px">');

if ($pgMode == 'EDIT'):
    $gloForceRO = true;
    $pgHtm .= '</div><div class="row">' . "\n";
    $pgHtm .= wtkFormText('wtkFiles', 'OrigFileName', 'text','Original File Name','s12');
    $gloForceRO = $pgForceRO;
endif;

$pgHtm .= wtkFormPrimeField('wtkFiles', 'UserUID', $gloUserUID);
$pgHtm .= wtkFormPrimeField('wtkFiles', 'TempDownload', 'Y'); // will copy file to exports for viewing
if ($gloWTKmode == 'ADD'):
    $pgHtm .= wtkFormPrimeField('wtkFiles', 'TableRelation', $pgTableRel);
    $pgHtm .= wtkFormPrimeField('wtkFiles', 'ParentUID', $gloRNG);
else:
    $pgHtm .= wtkFormHidden('wtkwtkFilesTableRelation', $pgTableRel);
	$pgHtm .= wtkFormHidden('wtkwtkFilesParentUID', wtkSqlValue('ParentUID'));
endif;
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../ajxFileList.php');
// below are for wtkFileUpload.js and fileUpload.php but only need this if using $fncShowOneClickUpload feature of wtkFormFile
// $pgHtm .= wtkFormHidden('UserUID', $gloUserUID);
// $pgHtm .= wtkFormHidden('tabRel', $pgTableRel);
// $pgHtm .= wtkFormHidden('ParentUID', $gloRNG);
// $pgHtm .= wtkFormHidden('imgMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('imgTable', wtkEncode('wtkFiles')); // without this edit/change file won't save

$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </div>
    </form>
</div>
<div id="modFooter" class="modal-footer right">
    $pgBtns
</div>
htmVAR;
echo $pgHtm;
exit;
?>
