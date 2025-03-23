<?PHP
$pgSecurityLevel = 75;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `HelpIndex`, `HelpTitle`
  FROM `wtkHelp`
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " WHERE lower(`HelpIndex`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY `HelpIndex` ASC';

$gloEditPage = 'helpEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkHelp'; // have DelDate at end if should DelDate instead of DELETE

$pgHelp = wtkHelp('Help');
$pgHtm =<<<htmVAR
<div class="container">
    <h4>Help Management $pgHelp
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('helpList','wtkHelp')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial value to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('helpList','wtkHelp')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkHelp', '/admin/helpList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
