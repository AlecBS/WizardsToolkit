<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `Language`, `PrimaryText`, `NewText`, `MassUpdateId`
  FROM `wtkLanguage`
WHERE `UID` = ?
SQLVAR;
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/languageEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Language</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'LangPref'
ORDER BY `LookupDisplay` ASC
SQLVAR;

$pgHtm .= wtkFormText('wtkLanguage', 'PrimaryText', 'text', 'English Text', 'm7 s12');
$pgHtm .= wtkFormSelect('wtkLanguage', 'Language', $pgSelSQL, [], 'LookupDisplay', 'LookupValue', 'Convert to Language', 'm5 s12');
//$pgHtm .= wtkFormText('wtkLanguage', 'Language');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgHtm .= wtkFormText('wtkLanguage', 'NewText', 'text', 'New Text', 'm12 s12');
if ($gloUserSecLevel >= 80): // Mgr level
    $pgHtm .= wtkFormText('wtkLanguage', 'MassUpdateId', 'text', 'Template Text Code', 'm3 s12');
endif;  // Mgr level
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/languageList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
