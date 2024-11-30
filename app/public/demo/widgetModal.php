<?PHP
$pgSecurityLevel = 50;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

// widgetRefresh on next line is what makes widget Refresh on save
// Note: the form must be named "FwidgetRefresh" also for this to work
$pgBtns = wtkModalUpdateBtns('../wtk/lib/Save','widgetRefresh');

$pgSQL =<<<SQLVAR
SELECT `Address`,`City`,`State`,`Zipcode`
FROM `wtkCompanySettings`
WHERE `UID` = 1
SQLVAR;

$pgHtm =<<<htmVAR
<div class="modal-content">
    <h3>Modal Demo</h3>
    <p>If you change anything here and save it will refresh only the widget
        that was clicked when you called this page.</p>
    <form id="FwidgetRefresh" method="POST">
        <span id="formMsg" class="red-text">$gloFormMsg</span>
        <div class="row">
htmVAR;

$gloForceRO = wtkPageReadOnlyCheck('widgetModal.php', $gloId);
wtkSqlGetRow($pgSQL, []);

$pgHtm .= wtkFormText('wtkCompanySettings', 'Address');
$pgHtm .= wtkFormText('wtkCompanySettings', 'City');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAState' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkCompanySettings', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue');
$pgHtm .= wtkFormText('wtkCompanySettings', 'Zipcode');

$pgHtm .= wtkFormHidden('ID1', 1);
$pgHtm .= wtkFormHidden('WidgetUID', $gloId); // this determins which widget is refreshed
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', 'EDIT');
$pgHtm .= wtkFormHidden('wtkGoToURL', 'dashboard'); // so Save.php does not redirect somewhere
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
