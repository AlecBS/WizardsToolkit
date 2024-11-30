<?PHP
$gloLoginRequired = false;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
//$gloSiteDesign = wtkGetSession('designType'); // MPA or SPA for Multi-Page App or Single Page App

$pgSQL =<<<SQLVAR
SELECT `LookupType`, `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

// The form action is only needed for MPA method:  action="../wtk/lib/Save.php"
$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Lookup</h4>
    <br>
    <div class="content b-shadow">
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

//$pgHtm .= wtkFormHidden('Debug', 'Y');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/lookupList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= wtkFormHidden('CharCntr', 'wtkwtkLookupsLookupType,input#wtkwtkLookupsLookupValue');
$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;

if ($gloSiteDesign == 'SPA'):
    echo $pgHtm;  // SPA Method
else: // MPA Method
    wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
endif;
?>
