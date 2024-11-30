<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgDate = wtkSqlDateFormat('f.`AddDate`','AddDate',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT f.`UID`, $pgDate,
 f.`IPaddress`, L.`LookupDisplay` AS `Reason`, f.`FailNote`, f.`OpSystem`, f.`Browser`
 FROM wtkFailedAttempts f
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'FailCode' AND L.`LookupValue` = f.`FailCode`
WHERE f.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(f.`IPaddress`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY f.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloDelPage  = 'wtkFailedAttemptsDelDate'; // have DelDate at end if should DelDate instead of DELETE

wtkSetHeaderSort('IPaddress', 'IP Address');

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Failed Access Attempts</h4><br>
    <h5>IP Address Quick Filters <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('failedAttemptList','wtkFailedAttempts')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial value to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('failedAttemptList','wtkFailedAttempts')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkFailedAttempts', '/admin/failedAttemptList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
