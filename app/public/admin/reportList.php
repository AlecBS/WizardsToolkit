<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT r.`UID`, r.`RptName`, L2.`LookupDisplay` AS `SecurityLevel`,
  L.`LookupDisplay` AS `ReportType`, r.`ViewOrder`,
  CONCAT('<a target="_blank" href="/wtk/reports.php?NP=Y&rng=', r.`UID`,
    '&apiKey=$pgApiKey" class="btn-floating"><i class="material-icons',
    CASE
      WHEN r.`GraphRpt` = 'Y' THEN '">insert_chart'
      ELSE '">format_list_numbered'
    END,
    '</i></a>') AS `View`
FROM `wtkReports` r
  LEFT OUTER JOIN `wtkLookups` L
    ON L.`LookupType` = 'RptType' AND L.`LookupValue` = r.`RptType`
  LEFT OUTER JOIN `wtkLookups` L2
    ON L2.`LookupType` = 'SecurityLevel' AND L2.`LookupValue` = r.`SecurityLevel`
 WHERE r.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterCol   = 'RptName';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(r.`RptName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY r.`ViewOrder` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'reportEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkReportsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloColumnAlignArray = array (
   'ViewOrder' => 'center',
   'View' => 'center'
);
wtkSetHeaderSort('RptName', 'Report Name');
wtkSetHeaderSort('ReportType', 'Report Type');
wtkSetHeaderSort('ViewOrder','View Order');
wtkSetHeaderSort('SecurityLevel', 'Security Level');

$pgHtm =<<<htmVAR
<div class="container">
    <h4>SQL Report Wizard
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('reportList','wtkReports')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter report name to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('reportList','wtkReports')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkReports', '/admin/reportList.php');

$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
