<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `LookupType`, `LookupValue`, `LookupDisplay`, `espLookupDisplay`
  FROM `wtkLookups`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/lookupEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Lookup</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <input type="hidden" id="CharCntr" name="CharCntr" value="Y">
            <div class="row">
htmVAR;

$pgTmp  = wtkFormText('wtkLookups', 'LookupType');
$pgHtm .= wtkReplace($pgTmp, 'type="text"','type="text" class="char-cntr" data-length="15" maxlength="15"');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgTmp  = wtkFormText('wtkLookups', 'LookupValue');
$pgHtm .= wtkReplace($pgTmp, 'type="text"','type="text" class="char-cntr" data-length="40" maxlength="40"');
$pgHtm .= '</div>' . "\n";
$pgHtm .= '<div class="row">' . "\n";
$pgHtm .= wtkFormText('wtkLookups', 'LookupDisplay');
$pgHtm .= wtkFormText('wtkLookups', 'espLookupDisplay');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/lookupList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns('wtkForm','/wtk/lib/Save','Y') . "\n";
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= wtkFormHidden('CharCntr', 'wtkwtkLookupsLookupType,input#wtkwtkLookupsLookupValue');
$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;

echo $pgHtm;
exit;
?>
