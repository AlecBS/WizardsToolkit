<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgAbuseCode = wtkGetPost('AbuseIPDB');
if ($pgAbuseCode != ''): // just added one
    $pgIPaddress = wtkGetPost('wtkwtkLockoutUntilIPaddress');
    $pgResult = wtkReportAbuseByIP($pgIPaddress,$pgAbuseCode,'Apache Logs show hacking attempt');
    $pgDecoded = json_decode($pgResult, true);  // Decode JSON into associative array
    $pgConfidenceScore = $pgDecoded['data']['abuseConfidenceScore'];
    $pgMsg = '<div class="chip blue">Abuse IP DB Confidence Score: ' . $pgConfidenceScore . '</div>';
else:
    $pgMsg = '';
endif;

$pgDate = wtkSqlDateFormat('lu.`AddDate`','AddDate',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT lu.`UID`, $pgDate,
    lu.`IPaddress`, COALESCE(L.`LookupDisplay`,lu.`FailCode`) AS `Reason`,
    DATE_FORMAT(lu.`LockUntil`, '$gloSqlDate') AS `LockoutUntil`,
    FORMAT(lu.`BlockedCount`,0) AS `BlockedCount`
 FROM `wtkLockoutUntil` lu
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'FailCode' AND L.`LookupValue` = lu.`FailCode`
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " WHERE lower(lu.`IPaddress`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY lu.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloAddPage = 'lockoutAdd';
$gloDelPage = 'wtkLockoutUntil'; // have DelDate at end if should DelDate instead of DELETE
wtkSetHeaderSort('Reason');
wtkSetHeaderSort('IPaddress', 'IP Address');
wtkSetHeaderSort('BlockedCount', 'Blocked Count');
$gloColumnAlignArray = array (
    'LockoutUntil'   => 'center',
    'BlockedCount'   => 'center',
);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Locked-Out IP Addresses
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('lockoutList','wtkLockoutUntil')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    $pgMsg
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial IP Address to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('lockoutList','wtkLockoutUntil')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkLockoutUntil', '/admin/lockoutList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
