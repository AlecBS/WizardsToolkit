<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('userNoteEdit.php', $gloId);
$pgBtns = wtkModalUpdateBtns('../wtk/lib/Save','wtkUserNote');

$pgDate = wtkSqlDateFormat('n.`AddDate`','DateAdded',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT $pgDate,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `WrittenBy`,
    n.`SecurityLevel`, n.`Notes`, n.`UserUID`, n.`FlagImportant`
  FROM `wtkUserNote` n
   INNER JOIN `wtkUsers` u ON u.`UID` = n.`AddedByUserUID`
WHERE n.`UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);

$pgHtm =<<<htmVAR
<div class="modal-content">
    <h3>User Note</h3>
    <form id="FwtkUserNote" method="POST">
        <span id="formMsg" class="red-text">$gloFormMsg</span>
        <div class="row">
htmVAR;

if ($gloWTKmode == 'ADD'):
    $pgHtm .= wtkFormPrimeField('wtkUserNote', 'UserUID', $gloRNG);
    $pgHtm .= wtkFormPrimeField('wtkUserNote', 'AddedByUserUID', $gloUserUID);
else:
    wtkSqlGetRow($pgSQL, [$gloId]);
    $gloRNG = wtkSqlValue('UserUID');
    $gloForceRO = true;
    $pgHtm .= wtkFormText('wtkUserNote', 'WrittenBy');
    $pgHtm .= wtkFormText('wtkUserNote', 'DateAdded');
    $pgHtm .= '</div><div class="row">' . "\n";
    $gloForceRO = false;
endif;

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkUserNote', 'FlagImportant', 'Important',$pgValues,'m4 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' ORDER BY `LookupValue` ASC";
$pgTmp  = wtkFormSelect('wtkUserNote', 'SecurityLevel', $pgSQL, [], 'LookupDisplay', 'LookupValue','Only visible to this security level and above','m8 s12');
if ($gloWTKmode == 'ADD'):
    $pgTmp = wtkReplace($pgTmp, 'value="30"','value="30" SELECTED'); // default to BB Staff level
endif;
$pgHtm .= $pgTmp . "\n";

$pgHtm .= wtkFormTextArea('wtkUserNote', 'Notes', '', 'm12 s12');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $gloRNG);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../ajxUserNotesList.php');
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </div>
    </form>
</div>
<div id="modFooter" class="modal-footer right">
$pgBtns
</div>
<script type="text/javascript">
M.textareaAutoResize($('#wtkwtkUserNoteNotes'));
</script>
htmVAR;
echo $pgHtm;
exit;
?>
