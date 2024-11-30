<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `ViewOrder`, `SecurityLevel`,
    `RptType`, `RptName`, `RptNotes`, `RptSelect`,
    `TestMode`, `HideFooter`, `URLredirect`,
    `SelTableName`, `SelValueColumn`, `SelDisplayColumn`, `SelWhere`,
    `AddLink`, `EditLink`, `AlignCenter`, `AlignRight`,
    `FieldSuppress`, `SortableCols`, `TotalCols`, `TotalMoneyCols`,
    `StartDatePrompt`, `EndDatePrompt`, `DaysAgo`,
    `GraphRpt`,`ChartSuppress`,
    `RegRpt`,`BarChart`,`LineChart`,`AreaChart`,`PieChart`
FROM `wtkReports` WHERE `UID` = ?
 AND `DelDate` IS NULL
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/reportEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgGraphRpt = wtkSqlValue('GraphRpt');
else:
    $pgGraphRpt = 'N';
endif;

$pgHelp = wtkHelp();
$pgHtm =<<<htmVAR
<div class="container">
    <h4>SQL Report Wizard
        <small class="right">$pgHelp</small>
    </h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkReports', 'RptName', 'text', 'Report Name');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkReports', 'SecurityLevel', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m3 s6');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'RptType' AND `DelDate` IS NULL ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkReports', 'RptType', $pgSQL, [], 'LookupDisplay', 'LookupValue','Type', 'm3 s6');

$pgHtm .= wtkFormText('wtkReports', 'ViewOrder','number','', 'm4 s6');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('wtkReports', 'HideFooter', '', $pgValues, 'm4 s6');
$pgHtm .= wtkFormCheckbox('wtkReports', 'TestMode', '', $pgValues, 'm4 s6');

$pgHtm .= wtkFormTextArea('wtkReports', 'RptNotes', 'Description');
$pgTmp  = wtkFormText('wtkReports', 'URLredirect','text','URL Redirect (if entered, all below is ignored)');
$pgHtm .= wtkReplace($pgTmp, 'type="text"','onchange="JavaScript:hideRptFields(this.value)" type="text"');

$pgHtm .= '<div id="RptSelectDIV">' . "\n";
$pgTmp  = wtkFormTextArea('wtkReports', 'RptSelect', 'SQL for Report','s12','N','@UserUID@ will be replaced by logged-in users UID via &dollar;gloUserUID');
$pgHtm .= wtkReplace($pgTmp, '"materialize-textarea"','"materialize-textarea code-text"');
$pgHtm .=<<<htmVAR
</div></div>
<div id="dateFilterDIV" class="row">
    <h5>Date Filter Options</h5>
    <div class="col s12">
        <p>Fill any of the below and that filter option will appear.</p>
    </div>
htmVAR;

$pgHtm .= wtkFormText('wtkReports', 'StartDatePrompt','text','First Date Prompt','m6 s12','N', 'access value as @StartDate@ in above SQL');
$pgHtm .= wtkFormText('wtkReports', 'EndDatePrompt','text','Second Date Prompt','m6 s12','N','access value as @EndDate@ in above SQL');
$pgHtm .= '<div class="input-field col m7 s12"><p>Use above filters or "How many days" but not both.</p></div>' . "\n";
$pgHtm .= wtkFormText('wtkReports', 'DaysAgo','number','Default how many days before today','m5 s12','N','access value as @DaysPast@ in your SQL');

$pgHtm .=<<<htmVAR
</div>
<div id="filterOpsDIV" class="row">
    <h5>Drop-Table Filter Option</h5>
    <div class="col s12">
        <p>You can add a table to filter results by.  Add more tables in <a onclick="JavaScript:ajaxGo('lookupList',0,'SelTableName')">Lookups</a> with Lookup Type = 'SelTableName'.</p>
    </div>
htmVAR;

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = ? ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkReports', 'SelTableName', $pgSQL, ['SelTableName'], 'LookupDisplay', 'LookupValue', 'Data Table Filter', 'm4 s12', 'Y');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = ? ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkReports', 'SelValueColumn', $pgSQL, ['SelValueColumn'], 'LookupDisplay', 'LookupValue', 'Data ID Column', 'm2 s12', 'Y');
$pgHtm .= wtkFormText('wtkReports', 'SelDisplayColumn', 'text', 'Data Display Column');
$pgHtm .=<<<htmVAR
    <div class="input-field col s12">
        <span class="helper-text" style="margin-top:-27px">
            Access value chosen by users from <b>Data Id</b> as @RptFilter@ in your SQL above.  Add more <a onclick="JavaScript:ajaxGo('lookupList',0,'SelValueColumn')">SelValueColumn options here</a>.
        </span>
    </div>
htmVAR;
$pgHtm .= wtkFormText('wtkReports', 'SelWhere', 'text', 'Where Filter', 'm12');
$pgHtm .= '</div>' . "\n";

$pgHtm .= '<div id="browseAffectsDIV" class="row">' . "\n";
$pgHtm .= '<h5>Special Browse Affects</h5><br>' . "\n";
$pgHtm .= wtkFormText('wtkReports', 'AddLink');
$pgHtm .= wtkFormText('wtkReports', 'EditLink');

$pgHtm .= wtkFormText('wtkReports', 'AlignCenter');
$pgHtm .= wtkFormText('wtkReports', 'AlignRight');
$pgHtm .= wtkFormText('wtkReports', 'FieldSuppress','text','Suppress Columns');

$pgHtm .= wtkFormTextArea('wtkReports', 'SortableCols', 'Sortable Columns','m6 s12','N','one line per<br>column to sort, header (optional), sort by column (optional)');
$pgHtm .= wtkFormText('wtkReports', 'TotalCols','text','Total Columns');
$pgHtm .= wtkFormText('wtkReports', 'TotalMoneyCols','text','Total Money Columns');

$pgHtm .=<<<htmVAR
</div>
<div id="chartOpsDIV" class="row">
    <h5>Charting Option</h5>
    <div class="col s12">
        <p>If your report has a description as the first column and other
         columns are graphable, then you can add graphs.</p>
    </div>
htmVAR;

$pgTmp  = wtkFormCheckbox('wtkReports', 'GraphRpt', 'Add Graphs to Report', $pgValues, 'm4 s12 center');
$pgHtm .= wtkReplace($pgTmp, '<input type="checkbox"','<input onclick="JavaScript:showHideChartTypes()" type="checkbox"');

$pgTmp  = wtkFormText('wtkReports', 'ChartSuppress','text','Suppress non-graphable columns on chart','m8 s12');

if ($pgGraphRpt == 'Y'):
    $pgHtm .= wtkReplace($pgTmp, '<div class="','<div id="chartSupressDIV" class="');
    $pgHtm .= '</div><div id="chartTypeDIV" class="row">' . "\n";
else:
    $pgHtm .= wtkReplace($pgTmp, '<div class="','<div id="chartSupressDIV" class="hide ');
    $pgHtm .= '</div><div id="chartTypeDIV" class="row hide">' . "\n";
endif;
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkReports', 'RegRpt', 'Textual',$pgValues,'m2 s6');
$pgHtm .= wtkFormCheckbox('wtkReports', 'BarChart', 'Bar',$pgValues,'m2 s6');
$pgHtm .= wtkFormCheckbox('wtkReports', 'LineChart', 'Line',$pgValues,'m2 s6');
$pgHtm .= wtkFormCheckbox('wtkReports', 'AreaChart', 'Area',$pgValues,'m2 s6');
$pgHtm .= wtkFormCheckbox('wtkReports', 'PieChart', 'Pie',$pgValues,'m2 s6');

$pgHtm .= '</div><br>' . "\n";
$pgHtm .= '<div class="row">' . "\n";

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/reportList.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
wtkFormPrepUpdField('wtkReports', 'LastModByUserUID', 'text');
$pgHtm .= wtkFormHidden('wtkwtkReportsLastModByUserUID', $gloUserUID);
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkUpdateBtns('wtkForm','/wtk/lib/Save','Y') . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
            </div>
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
