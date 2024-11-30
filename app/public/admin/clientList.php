<?PHP
$pgSecurityLevel = 95;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT c.`UID`, c.`ClientName`, L.`LookupDisplay` AS `Country`, c.`ClientEmail`
  FROM `wtkClients` c
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'Country' AND L.`LookupValue` = c.`CountryCode`
SQLVAR;
// 2DO add filtering of clients based on CompanyUID of user that is logged in
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " WHERE lower(c.`ClientName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY c.`ClientName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/admin/clientEdit';
if ($gloUserSecLevel > 95): // VP level
    $gloAddPage  = $gloEditPage;
//    $gloDelPage  = 'wtkClients'; // have DelDate at end if should DelDate instead of DELETE
endif;  // Mgr level
/*
$gloMoreButtons = array(
            'Projects' => array(
                    'act' => '/projectList',
                    'img' => 'chevron_right'
                    )
        );
*/
$pgHtm =<<<htmVAR
<div class="container">
    <h4>Client List</h4><br>
    <h5>Quick Filter <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('clientList','wtkClients')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
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
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkClients', '/admin/clientList');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no clients yet');
$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
