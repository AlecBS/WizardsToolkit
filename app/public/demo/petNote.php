<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
// See SQL to generate table below

if (wtkGetPost('Mode') == 'list'): // from petList as modal more-buttons call
    $gloRNG = $gloId;
    $gloWTKmode = 'ADD';
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PetNote`
  FROM `petNotes`
WHERE `UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;
$pgMode = ucwords(strtolower($gloWTKmode));

$pgHtm =<<<htmVAR
<form id="FpetNoteList" name="FpetNoteList" method="POST">
    <span id="formMsg" class="red-text">$gloFormMsg</span>
    <div class="row">
        <div class="col s12">
            <h4>$pgMode Note</h4>
            <br>
        </div>
htmVAR;
$pgHtm .= wtkFormPrimeField('petNotes', 'PetUID', $gloRNG);
$pgHtm .= wtkFormPrimeField('petNotes', 'UserUID', $gloUserUID);
$pgHtm .= wtkFormText('petNotes', 'PetNote', 'text', 'Note about Pet', 's12');
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $gloRNG);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/petEdit.php');
$pgHtm .= wtkFormWriteUpdField();

$pgBtns = wtkModalUpdateBtns('../wtk/lib/Save','petNoteList');

$pgHtm .=<<<htmVAR
    </div>
</form>
<div id="modFooter" class="modal-footer right">
    $pgBtns
</div>
htmVAR;

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
