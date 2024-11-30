<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgHtm  = '';
$pgEnd  = '';
$pgMode = wtkGetParam('Mode');
if ($pgMode == 'widget'): // if called from Widget into modal window, minimize
    $pgHtm  = '<div id="errorLogDIV">' . "\n"; // so return from Edit page refreshes properly
    $pgEnd  = '</div>';
    $gloRNG = 'widget'; // so passed to edit page and can adjust that for modal width also
elseif ($gloRNG == 'widget'): // $gloRNG = called via Cancel button on errorLogEdit
    $pgMode = 'widget';
endif;

$pgDate = wtkSqlDateFormat('AddDate','',$gloSqlDateTime);

$pgSQL =<<<SQLVAR
SELECT `UID`, $pgDate,
   `FromPage`, `ErrType` AS `ErrorType`, `ErrMsg` AS `ErrorMessage`
  FROM `wtkErrorLog`
WHERE `DelDate` IS NULL
SQLVAR;
if ($pgMode == 'widget'): // if called from Widget into modal window, minimize
    $pgSQL = wtkReplace($pgSQL, ', `ErrMsg` AS `ErrorMessage`','');
    wtkSearchReplace(':ajaxGo(',':wtkModalUpdate(');
endif;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`FromPage`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(`ErrMsg`) LIKE lower('%" . $pgFilter2Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''

$pgSQL .= ' ORDER BY `UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/admin/errorLogEdit';
$gloDelPage  = 'wtkErrorLogDelDate'; // have DelDate at end if should DelDate instead of DELETE

// put in columns you want sortable here:
//wtkSetHeaderSort('ColumnName', 'Column Header');
//wtkFillSuppressArray('ColumnName');

//$gloColumnAlignArray = array (
//    'Priority'   => 'center'
//);
$pgList = wtkBuildDataBrowse($pgSQL, [], 'wtkErrorLog', '/admin/errorLogList.php');
$pgList = wtkReplace($pgList, 'There is no data available.','no errors yet');
if ($pgMode == 'widget'): // if called from Widget into modal window, minimize
    $pgList = wtkReplace($pgList, ':ajaxGo(',':wtkModalUpdate(');
endif;

$pgHtm .=<<<htmVAR
<div class="row">
    <div class="col m12">
        <h4>Error Logs</h4><br>
        <h5>Quick Filters <small id="filterReset"$pgHideReset>
            <button onclick="JavaScript:wtkBrowseReset('errorLogList','wtkErrorLog')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
            </small>
        </h5>
        <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
            <input type="hidden" id="Filter" name="Filter" value="Y">
            <div class="input-field">
               <div class="filter-width-50">
                  <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial From Page to search for">
               </div>
               <div class="filter-width-50">
                  <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Error Message to search for">
               </div>
               <button onclick="Javascript:wtkBrowseFilter('errorLogList','wtkErrorLog')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
            </div>
        </form>
        <div class="wtk-list card b-shadow">
        $pgList
        </div>
    </div>
</div>
$pgEnd
htmVAR;

echo $pgHtm;
exit;
?>
