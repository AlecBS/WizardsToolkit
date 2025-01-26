<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

// using wtkSqlDateFormat makes this work for both MySQL and PostgreSQL
$pgSQL  = 'SELECT l.`UID`, ' . wtkSqlDateFormat('l.`AddDate`', 'OBAddDate') . ', l.`TableName`,' . "\n";
$pgSQL .= ' l.`AddDate` AS `LogAddDate`,' . "\n";  // to allow date sorting without ambiguous column name error
$pgSQL .= "CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `WhoChanged`," . "\n";
$pgSQL .= ' l.`ChangeInfo`' . "\n";
$pgSQL .= ' FROM `wtkUpdateLog` l' . "\n";
$pgSQL .= ' LEFT OUTER JOIN `wtkUsers` u' . "\n";
$pgSQL .= ' ON u.`UID` = l.`UserUID`' . "\n";
//$pgSQL .= ' WHERE l.`DelDate` IS NULL' . "\n";
$pgSQL .= "  WHERE (l.`TableName` <> 'wtkUpdateLog' AND l.`ChangeInfo` <> 'Deleted this row ')" . "\n";

$pgHideReset = ' class="hide"';
$pgFilter3Value = wtkFilterRequest('wtkFilter3');
if ($pgFilter3Value != ''):
    $pgSQL .= " AND lower(l.`ChangeInfo`) LIKE lower('%" . $pgFilter3Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter3Value != ''
$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND l.`TableName` = '$pgFilter2Value' ";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND l.`UserUID` = $pgFilterValue ";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY l.`UID` DESC';

$gloEditPage = 'updateLogView';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkUpdateLog';

wtkFillSuppressArray('LogAddDate');
wtkSetHeaderSort('OBAddDate', 'Date Added', 'LogAddDate');
wtkSetHeaderSort('WhoChanged', 'Who Changed');
wtkSetHeaderSort('TableName', 'Table Name');

$pgSelSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Name`
 FROM `wtkUsers`
 WHERE `DelDate` IS NULL
ORDER BY `FirstName` ASC
SQLVAR;
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, [], 'Name', 'UID', $pgFilterValue);

if ($gloDriver1 == 'pgsql'):
    // 2ENHANCE add this functionality for PostgreSQL
    $pgHideTableFilter = ' hide';
    $pgFilterWidth = '50';
    $pgSel2Options = '';
else:
    $pgHideTableFilter = '';
    $pgFilterWidth = '33';

    $pgSelSQL =<<<SQLVAR
SELECT t.TABLE_NAME AS `Table`
 FROM information_schema.TABLES t
  INNER JOIN $gloDb1.`wtkUpdateLog` l ON l.`TableName` = t.TABLE_NAME
 WHERE t.TABLE_SCHEMA = '$gloDb1'
 GROUP BY t.TABLE_NAME
ORDER BY t.TABLE_NAME ASC
SQLVAR;
    $pgSel2Options = wtkGetSelectOptions($pgSelSQL, [], 'Table', 'Table', $pgFilter2Value);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Log</h4><br>
    <h5>Quick Filters <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('updateLogList','wtkUpdateLog')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-33 input-field">
               <select id="wtkFilter" name="wtkFilter">
                   <option value="">Show All</option>
                   $pgSelOptions
               </select>
               <label for="wtkFilter" class="active">Choose User</label>
           </div>
           <div class="filter-width-33 input-field$pgHideTableFilter">
               <select id="wtkFilter2" name="wtkFilter2">
                   <option value="">Show All</option>
                   $pgSel2Options
               </select>
               <label for="wtkFilter2" class="active">Choose SQL Table</label>
           </div>
           <div class="filter-width-$pgFilterWidth input-field">
              <input type="search" name="wtkFilter3" id="wtkFilter3" value="$pgFilter3Value" placeholder="enter partial change text to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('updateLogList','wtkUpdateLog')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkUpdateLog', '/admin/updateLogList.php');
$pgHtm .= '</div></div>' . "\n";
$pgHtm .= wtkFormHidden('HasSelect', 'Y');

echo $pgHtm;
exit;
?>
