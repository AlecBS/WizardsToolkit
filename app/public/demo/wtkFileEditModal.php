<?PHP
// Note this is almost exactly the same as /wtk/fileEdit.php except with comments for developers
$pgSecurityLevel = 0;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

// `ViewLink` is required for wtkFormFile so can pass to /wtk/viewFile.php
$pgSQL =<<<SQLVAR
SELECT `UID`, CRC32(`UID`) AS `ViewLink`, `Description`, `ParentUID`,
    `TableRelation`, `FilePath`, `NewFileName`,`TempDownload`
  FROM `wtkFiles`
WHERE `UID` = ?
SQLVAR;
if ($gloDriver1 == 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, 'CRC32(`UID`)','CRC32(CAST(`UID` AS VARCHAR))');
endif;

$pgMode = wtkGetParam('Mode');
if ($pgMode == 'EDIT'):
    $gloForceRO = wtkPageReadOnlyCheck('/demo/wtkFileEditModal.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgTableRel = wtkSqlValue('TableRelation');
else:
    $pgTableRel = $pgMode;
    $gloWTKmode = 'ADD';
	$gloId = 0;
endif;
if ($gloRNG == 0):
    $gloRNG = '';
endif;

$pgHtm =<<<htmVAR
<form id="FwtkFilesDIV" name="FwtkFilesDIV" method="POST">
    <span id="formMsg" class="red-text">$gloFormMsg</span>
    <div class="row">
        <div class="col s12">
            <h4>Document</h4>
            <br>
        </div>
htmVAR;

$pgHtm .= wtkFormText('wtkFiles', 'Description');
//$pgHtm .= wtkFormFile('wtkFiles', 'FilePath','../docs/' . $pgTableRel . '/','NewFileName','Pick File','m6 s12','','N','','N');
$pgHtm .= wtkFormFile('wtkFiles', 'FilePath','/demo/imgs/','NewFileName','Pick File','m6 s12','','N','accept="image/*"','N');
// change second-to-last parameter above to accept=".pdf" for PDF-only option
$pgHtm .= wtkFormPrimeField('wtkFiles', 'UserUID', $gloUserUID);
$pgHtm .= wtkFormPrimeField('wtkFiles', 'TempDownload', 'Y'); // will copy file to exports for viewing
$pgHtm .= wtkFormPrimeField('wtkFiles', 'ParentUID', $gloRNG);
if ($gloWTKmode == 'ADD'):
    $pgHtm .= wtkFormPrimeField('wtkFiles', 'TableRelation', $pgTableRel);
else:
	$pgHtm .= wtkFormHidden('wtkwtkFilesTableRelation', $pgTableRel);
endif;
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/wtkFileListModal.php');
// Note that ajxFileList.php is a little different from wtkFileListModal.php and calls /wtk/fileEdit.php instead of this file

// below are for wtkFileUpload.js and fileUpload.php but only need this if using $fncShowOneClickUpload feature of wtkFormFile
// $pgHtm .= wtkFormHidden('UserUID', $gloUserUID);
// $pgHtm .= wtkFormHidden('tabRel', $pgTableRel);
// $pgHtm .= wtkFormHidden('ParentUID', $gloRNG);
// $pgHtm .= wtkFormHidden('imgMode', $gloWTKmode);
// $pgHtm .= wtkFormHidden('imgTable', wtkEncode('wtkFiles'));

$pgHtm .= wtkFormWriteUpdField();
$pgBtns = wtkModalUpdateBtns('/wtk/lib/Save','wtkFilesDIV');
$pgHtm .=<<<htmVAR
    </div>
</form>
<div id="modFooter" class="modal-footer right">
    $pgBtns
</div>
htmVAR;
echo $pgHtm;
exit;
?>
