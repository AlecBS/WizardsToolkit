<?PHP
$pgSecurityLevel = 99;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `WidgetName`, `SecurityLevel`, `WidgetType`, `ChartType`, `WidgetColor`,
    `WidgetDescription`, `WidgetSQL`, `WidgetURL`, `PassRNG`,
    `WindowModal`, `SkipFooter`
  FROM `wtkWidget`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/widgetEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Widget</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkWidget', 'WidgetName','text','','m12 s12');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkWidget', 'SecurityLevel', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'WidgetType' ORDER BY `LookupValue` ASC";
$pgTmp  = wtkFormSelect('wtkWidget', 'WidgetType', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');
$pgHtm .= wtkReplace($pgTmp, '<select ','<select onchange="JavaScript:widgetTypeChanged(this.value)" ');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'ChartType' AND `LookupValue` <> 'All' ORDER BY `LookupValue` ASC";
$pgTmp  = wtkFormSelect('wtkWidget', 'ChartType', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="chartTypeDIV" class="input-field');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'WidgetColor' ORDER BY `LookupValue` ASC";
$pgTmp  = wtkFormSelect('wtkWidget', 'WidgetColor', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="colorDIV" class="input-field');

$pgTmp  = wtkFormCheckbox('wtkWidget', 'SkipFooter', '',$pgValues,'m4 s12');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="skipFooterDIV" class="input-field');

$pgHtm .= '</div><div class="row">' . "\n";
$pgTmp  = wtkFormText('wtkWidget', 'WidgetURL', 'text','Link to Page','m6 s12');
$pgHtm .= wtkReplace($pgTmp, 'type="text"','type="text" onchange="JavaScript:showHideLinkOptions()"');

$pgTmp  = wtkFormText('wtkWidget', 'PassRNG', 'text', 'Pass RNG', 'm3 s12');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="PassRNGDIV" class="input-field');

$pgTmp  = wtkFormCheckbox('wtkWidget', 'WindowModal', 'Use Modal Window',$pgValues,'m3 s12');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="WindowModalDIV" class="input-field');

$pgHtm .= wtkFormTextArea('wtkWidget', 'WidgetDescription', 'Description', 's12');
$pgTmp  = wtkFormTextArea('wtkWidget', 'WidgetSQL', 'Widget SQL', 's12');
$pgTmp  = wtkReplace($pgTmp, '"materialize-textarea"','"materialize-textarea code-text"');
$pgHtm .= wtkReplace($pgTmp, '<div class="input-field','<div id="skipSQLDIV" class="input-field');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/widgetList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns('wtkForm','/wtk/lib/Save','Y') . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgWidgetType = wtkSqlValue('WidgetType');
$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
<script type="text/javascript">
showHideLinkOptions();
widgetTypeChanged('$pgWidgetType');
</script>
htmVAR;
echo $pgHtm;
exit;
?>
