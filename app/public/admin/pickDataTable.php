<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `TABLE_NAME` AS `GUID`, `TABLE_NAME` AS `TableName`, `ENGINE` AS `Engine`
 FROM `information_schema`.`TABLES`
WHERE `TABLE_SCHEMA` = :TABLE_SCHEMA AND `TABLE_TYPE` = 'BASE TABLE'
SQLVAR;
$pgSqlFilter = array('TABLE_SCHEMA' => $gloDb1);

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`TABLE_NAME`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY `TABLE_NAME` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Choose Data Table to Import Into
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('pickDataTable','pickDataTable','0')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="enter partial table name to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('pickDataTable','pickDataTable')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$gloEditPage = 'tableColumns';
$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'pickDataTable');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
