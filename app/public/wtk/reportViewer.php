<?php
// loops through and builds list of reports for each RptType
// that user has access to based on their security level
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSkipFooter = true;

$pgCanPrint = wtkSqlGetOneResult('SELECT `CanPrint` FROM `wtkUsers` WHERE `UID` = ?',[$gloUserUID]);
if ($pgCanPrint == 'N'):
    $pgHtm =<<<htmVAR
    <div class="container">
        <h5>Reports</h5><br>
        <p>Your account does not have report priviledges.
        If you need access to reports please contact your manager.</p>
        <a onclick="Javascript:wtkGoBack()">Return</a>
    </div>
htmVAR;
    echo $pgHtm;
    exit;
endif;
$pgHtm =<<<htmVAR
<div class="container">
    <h5>Reports</h5><br>
    <ul class="collapsible">
htmVAR;

$pgRptSQL =<<<SQLVAR
SELECT `UID`,
    CONCAT('<a onClick="JavaScript:rpt(', `UID`, ')">',`RptName`,
    CASE
      WHEN `GraphRpt` = 'Y' THEN ' &nbsp;&nbsp; <button type="button" class="btn-floating"><i class="material-icons">insert_chart</i></button></a>'
      ELSE '</a>'
    END) AS `ReportName`
FROM `wtkReports`
WHERE `RptType` = :RptType AND `SecurityLevel` <= :SecurityLevel
     AND `TestMode` = :TestMode AND `DelDate` IS NULL
ORDER BY `ViewOrder` ASC
SQLVAR;

$pgRptFilter = array (
    'RptType' => 'fill',
    'SecurityLevel' => $gloUserSecLevel,
    'TestMode' => 'N'
);

$pgSQL =<<<SQLVAR
SELECT r.`RptType`, L.`LookupDisplay` AS `Category`, COUNT(r.`UID`) AS `Count`
 FROM `wtkLookups` L
   INNER JOIN `wtkReports` r ON r.`RptType` = L.`LookupValue`
    AND r.`SecurityLevel` <= $gloUserSecLevel
WHERE L.`LookupType` = :LookupType AND L.`DelDate` IS NULL
GROUP BY r.`RptType`, L.`LookupValue`, L.`LookupDisplay`
ORDER BY L.`LookupDisplay` ASC
SQLVAR;
$pgSqlFilter = array (
    'LookupType' => 'RptType'
);
$pgSQL = wtkSqlPrep($pgSQL);
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);

while ($pgRow = $pgPDO->fetch()):
    $pgCount = $pgRow['Count'];
    $pgHtm .= '     <li>' . "\n";
    $pgHtm .= '       <div class="collapsible-header b-shadow">' . $pgRow['Category'] . '</div>' . "\n";
    $pgHtm .= '       <div class="collapsible-body card">' . "\n";
    $pgRptFilter['RptType'] = $pgRow['RptType'];
    $pgHtm .= wtkBuildDataBrowse($pgRptSQL, $pgRptFilter, 'rptViewer');
    $pgHtm .= '       </div>' . "\n";
    $pgHtm .= '     </li>' . "\n";
endwhile;

$pgHtm .= '    </ul>' . "\n";
$pgHtm .= '</div><br>' . "\n";

if ($gloDeviceType == 'phone'):
    $pgHtm .=<<<htmVAR
    <div id="rptSpan" class="center">
        <br><br><p>reports will show here</p><br><br>
    </div>
htmVAR;
else:
    $pgHtm .=<<<htmVAR
    <div id="rptSpan">
        <div class="container">
            <div class="card b-shadow">
                <div class="card-content">
                    <p>reports will show here</p>
                </div>
            </div>
        </div>
    </div>
htmVAR;
endif;
$pgHtm .= '<br>' . "\n";
$pgHtm .= wtkFormHidden('HasCollapse', 'Y');

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
