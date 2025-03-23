<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `FileName`, `FileDescription`
  FROM `wtkDownloads`
WHERE `DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`FileName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgSQL .= ' ORDER BY `FileName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'downloadEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkDownloadsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Downloads
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('downloadList','wtkDownloads','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial file name to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('downloadList','wtkDownloads')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkDownloads', '/admin/downloadList.php');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no downloads defined yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
