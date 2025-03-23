<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PageName`, `Path`, `FileName`
  FROM `wtkPages`
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " WHERE lower(`PageName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgSQL .= ' ORDER BY `PageName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'pageEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkPages'; // have DelDate at end if should DelDate instead of DELETE
wtkSetHeaderSort('PageName');
wtkSetHeaderSort('Path');
wtkSetHeaderSort('FileName');

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Page List
        <small class="right"><a onclick="JavaScript:wtkMakePageList()">(update website path links)</a>
            <span id="filterReset"$pgHideReset>
                &nbsp;&nbsp;
                <button onclick="JavaScript:wtkBrowseReset('pageList','wtkPages')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
            </span>
        </small>
    </h4>
    <span id="pageMsg"></span>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial page name to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('pageList','wtkPages')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkPages', '/admin/pageList.php');
$pgHtm .= '</div><br></div>' . "\n";
wtkProtoType($pgHtm);

echo $pgHtm;
exit;
?>
