<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgDate = wtkSqlDateFormat('h.`AddDate`','VisitDate',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT h.`UID`, CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `User`,
    $pgDate,
    h.`PageURL`, h.`OtherUID` AS `PassedId`, h.`SecondsTaken`
FROM `wtkUserHistory` h
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = h.`UserUID`
SQLVAR;
$pgHideReset = ' class="hide"';
$pgWHERE = '';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgWHERE = " h.`UserUID` = $pgFilterValue ";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgDateFilter = '';
$pgStartDate = wtkFilterRequest('StartDate'); // to store filter value temporarily use wtkFilterRequest instead of wtkGetParam
$pgEndDate = wtkFilterRequest('EndDate');
if (($pgStartDate != '') || ($pgEndDate != '')):
    if ($pgWHERE != ''):
        $pgWHERE .= ' AND';
    endif;
    $pgStart = date('Y-m-d', strtotime($pgStartDate));
    $pgEnd = date('Y-m-d', strtotime($pgEndDate));
    if ($gloDriver1 == 'pgsql'):
        $pgSelDates = 'to_char(h."AddDate", \'YYYY-MM-DD\')';
    else:
        $pgSelDates = "DATE_FORMAT(h.`AddDate`,'%Y-%m-%d')";
    endif;
    if (($pgStartDate != '') && ($pgEndDate != '')):
        $pgWHERE .= " $pgSelDates BETWEEN '$pgStart' AND '$pgEnd'";
    elseif ($pgStartDate != ''): // EndDate must be blank
        $pgWHERE .= " $pgSelDates >= '$pgStart'";
    else:
        $pgWHERE .= " $pgSelDates <= '$pgEnd'";
    endif;
endif;
if ($pgWHERE != ''):
    $pgSQL .= ' WHERE ' . $pgWHERE;
endif;
$pgSQL .= ' ORDER BY h.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$pgSelSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Name`
 FROM `wtkUsers`
 WHERE `DelDate` IS NULL
ORDER BY `FirstName` ASC
SQLVAR;
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, [], 'Name', 'UID', $pgFilterValue);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>User Page History
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('userHistory','wtkUserHistory')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
        <input type="hidden" id="HasDatePicker" name="HasDatePicker" value="Y">
        <div class="input-field">
            <div class="filter-width-33 input-field">
                <select id="wtkFilter" name="wtkFilter">
                    <option value="">Show All</option>
                    $pgSelOptions
                </select>
                <label for="wtkFilter" class="active">Choose User</label>
            </div>
            <div class="filter-width-33 input-field">
                <input type="text" class="datepicker" id="StartDate" name="StartDate" value="$pgStartDate">
                <label for="StartDate">From Start Date</label>
            </div>
            <div class="filter-width-33 input-field">
                <input type="text" class="datepicker" id="EndDate" name="EndDate" value="$pgEndDate">
                <label for="EndDate">To End Date</label>
            </div>
            <button onclick="Javascript:wtkBrowseFilter('userHistory','wtkUserHistory')"
              id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$gloColumnAlignArray = array (
    'PassedId' => 'center',
	'SecondsTaken' => 'center'
);
wtkSetHeaderSort('SecondsTaken');

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkUserHistory', '/admin/userHistory.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
