<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT c.`UID`, c.`ClientName`, L.`LookupDisplay` AS `ClientStatus`,
    L2.`LookupDisplay` AS `Country`,
    fncContactIcons(c.`ClientEmail`,c.`ClientPhone`,0,0,'Y',0,'N','N','') AS `Contact`,
    c.`AccountEmail` AS `AccountingEmail`
  FROM `wtkClients` c
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'ClientStatus' AND L.`LookupValue` = c.`ClientStatus`
   LEFT OUTER JOIN `wtkLookups` L2 ON L2.`LookupType` = 'Country' AND L2.`LookupValue` = c.`CountryCode`
WHERE c.`DelDate` IS NULL
SQLVAR;
// 2DO add filtering of clients based on CompanyUID of user that is logged in
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(c.`ClientName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY c.`ClientStatus` ASC, c.`ClientName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/admin/clientEdit';
    $gloAddPage  = $gloEditPage;
if ($gloUserSecLevel >= 95): // Owner level
    $gloDelPage  = 'wtkClientsDelDate'; // have DelDate at end if should DelDate instead of DELETE
endif;  // Mgr level

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Clients
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('clientList','wtkClients')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial client name to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('clientList','wtkClients')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
wtkSetHeaderSort('ClientName', 'Client');
wtkSetHeaderSort('Country', 'Country');
wtkSetHeaderSort('ClientStatus', 'Status');

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkClients', '/admin/clientList');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no clients yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
