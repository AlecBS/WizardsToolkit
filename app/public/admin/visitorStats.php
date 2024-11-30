<?PHP
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

if ($gloDriver1 == 'pgsql'):
    $pgDate = 'to_char(v."AddDate", \'YYYY-MM-DD\')';
else:
    $pgDate = "DATE_FORMAT(v.`AddDate`,'%Y-%m-%d')";
endif;
// BEGIN Filter logic
$pgDateFilter = '';
$pgFilterMsg = '<br>Currently no date range filtering.';
$pgStartDate = wtkFilterRequest('StartDate'); // to store filter value temporarily use wtkFilterRequest instead of wtkGetParam
$pgEndDate = wtkFilterRequest('EndDate');
if (($pgStartDate != '') || ($pgEndDate != '')):
    $pgFilterMsg = '<br>Report only showing data created ' . "\n";
    $pgStart = date('Y-m-d', strtotime($pgStartDate));
    $pgEnd = date('Y-m-d', strtotime($pgEndDate));
    if (($pgStartDate != '') && ($pgEndDate != '')):
        $pgFilterMsg .= ' from ' . $pgStartDate . ' to ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE $pgDate BETWEEN '$pgStart' AND '$pgEnd'";
    elseif ($pgStartDate != ''): // EndDate must be blank
        $pgFilterMsg .= ' on or after ' . $pgStartDate . '.';
        $pgDateFilter = " WHERE $pgDate >= '$pgStart'";
    else:
        $pgFilterMsg .= ' before or on ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE $pgDate <= '$pgEnd'";
    endif;
endif;
//  END  Filter logic

// BEGIN SpecialFilter logic
$pgSpecialFilter = wtkFilterRequest('SpecialFilter');
$pgOnly1Checked = '';
$pgManyChecked = '';
$pgFromChecked = '';
$pgAllChecked = '';
switch ($pgSpecialFilter):
    case 'only1':
        $pgOnly1Checked = 'checked';
        if ($pgDateFilter == ''):
            $pgDateFilter = ' WHERE';
        else:
            $pgDateFilter .= ' AND';
        endif;
        $pgDateFilter .= ' (v.`PagesB4Buy` + v.`PagesAfterBuy`) = 1';
        $pgPageVisitsSQL =<<<SQLVAR
SELECT COUNT(h.`UID`) AS `Count`
  FROM `wtkVisitorHistory` h
    INNER JOIN `wtkVisitors` v ON v.`UID` = h.`VisitorUID`
SQLVAR;
        break;
    case 'moreThan1':
        $pgManyChecked = 'checked';
        if ($pgDateFilter == ''):
            $pgDateFilter = ' WHERE';
        else:
            $pgDateFilter .= ' AND';
        endif;
        $pgDateFilter .= ' (v.`PagesB4Buy` + v.`PagesAfterBuy`) > 1';
        $pgPageVisitsSQL =<<<SQLVAR
SELECT COUNT(h.`UID`) AS `Count`
  FROM `wtkVisitorHistory` h
    INNER JOIN `wtkVisitors` v ON v.`UID` = h.`VisitorUID`
SQLVAR;
        break;
    case 'mustFrom':
        $pgFromChecked = 'checked';
        if ($pgDateFilter == ''):
            $pgDateFilter = ' WHERE';
        else:
            $pgDateFilter .= ' AND';
        endif;
        $pgDateFilter .= ' v.`ReferDomain` IS NOT NULL';
        $pgPageVisitsSQL =<<<SQLVAR
SELECT COUNT(h.`UID`) AS `Count`
  FROM `wtkVisitorHistory` h
    INNER JOIN `wtkVisitors` v ON v.`UID` = h.`VisitorUID`
SQLVAR;
        break;
    default:
        $pgAllChecked = 'checked';
        $pgPageVisitsSQL =<<<SQLVAR
SELECT COUNT(h.`UID`) AS `Count`
  FROM `wtkVisitorHistory` h
    INNER JOIN `wtkVisitors` v ON v.`UID` = h.`VisitorUID`
SQLVAR;
endswitch;
/* Use this if you want to exclude IP addresses of your staff
if ($pgDateFilter == ''):
    $pgDateFilter = ' WHERE';
else:
    $pgDateFilter .= ' AND';
endif;
$pgDateFilter .= " v.`IPaddress` NOT IN ('127.0.0.1','123.45.67.89')";
*/
$pgPageVisitsSQL .= $pgDateFilter;
//  END  SpecialFilter logic

$pgSQL =<<<SQLVAR
SELECT COUNT(v.`UID`) AS `Count`
  FROM `wtkVisitors` v
$pgDateFilter
SQLVAR;
$pgUniqueVisits = wtkSqlGetOneResult($pgSQL, []);

$pgPageVisits = wtkSqlGetOneResult($pgPageVisitsSQL, []);

$pgSQL =<<<SQLVAR
SELECT CASE WHEN v.`IPaddress` = 'no-IP' THEN 'no-IP'
         ELSE
           CONCAT('<a target="_blank" href="https://dnschecker.org/ip-location.php?ip=',
            v.`IPaddress`,'">',v.`IPaddress`,'</a>')
       END AS `IPAddress`,
    v.`ReferDomain` AS `FromDomain`,
    IF (v.`UserUID` IS NULL, 'No Signup', 'Registered') AS `UserStatus`,
    v.`FirstPage`, v.`LastPage`,
    (v.`PagesB4Buy` + v.`PagesAfterBuy`) AS `TotalPages`, v.`SecondsOnSite`
FROM `wtkVisitors` v
$pgDateFilter
ORDER BY `TotalPages` DESC
SQLVAR;
// WHERE v.`UserUID`  AND v.`SignupDate`
$gloColumnAlignArray = array (
    'TotalPages' => 'center',
	'SecondsOnSite' => 'center'
);

$pgFreq1stPage = wtkBuildDataBrowse($pgSQL, [], 'mostFreq');

// BEGIN Analytics based on Averages
$pgSQL =<<<SQLVAR
SELECT v.`FirstPage`, COUNT(v.`UID`) AS `Count`,
    FORMAT(AVG(v.`PagesB4Buy` + v.`PagesAfterBuy`),2) AS `AveragePages`,
    FORMAT(AVG(v.`SecondsOnSite`),1) AS `AverageSeconds`,
    AVG(v.`SecondsOnSite`) AS `AvgSeconds`
FROM `wtkVisitors` v
$pgDateFilter
 GROUP BY v.`FirstPage`
ORDER BY AVG(v.`PagesB4Buy` + v.`PagesAfterBuy`) DESC
SQLVAR;

$gloColumnAlignArray = array (
    'Count' => 'center',
    'AveragePages' => 'center',
	'AverageSeconds' => 'center'
);
wtkSetHeaderSort('FirstPage');
wtkSetHeaderSort('Count');
wtkSetHeaderSort('AveragePages');
wtkSetHeaderSort('AverageSeconds','Average Seconds','AvgSeconds');
wtkFillSuppressArray('AvgSeconds');
$pgFirstAnalytics = wtkBuildDataBrowse($pgSQL, [], 'visitorFirstPage');

$pgSQL =<<<SQLVAR
SELECT v.`LastPage`, COUNT(v.`UID`) AS `Count`,
    FORMAT(AVG(v.`PagesB4Buy` + v.`PagesAfterBuy`),2) AS `AveragePages`,
    FORMAT(AVG(v.`SecondsOnSite`),1) AS `AverageSeconds`,
    AVG(v.`SecondsOnSite`) AS `AvgSeconds`
FROM `wtkVisitors` v
$pgDateFilter
 GROUP BY v.`LastPage`
ORDER BY `AveragePages` DESC
SQLVAR;
wtkSetHeaderSort('LastPage');
$pgLastAnalytics = wtkBuildDataBrowse($pgSQL, [], 'visitorLastPage');
//  END  Analytics based on Averages

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <h3 class="center">Choose First Access Date Range</h3>
                <p class="center" id="expFilterMsg">$pgFilterMsg</p>
                <form id="dateRngForm" name="dateRngForm">
                    <input type="hidden" name="Filter" id="Filter" value="Y">
                    <input type="hidden" name="HasDatePicker" id="HasDatePicker" value="Y">
                    <div class="row">
                        <div class="input-field col m2 offset-m4 s6">
                            <input type="text" class="datepicker" id="StartDate" name="StartDate" value="$pgStartDate">
                            <label class="active" for="StartDate">From Date</label>
                        </div>
                        <div class="input-field col m2 s6">
                            <input type="text" class="datepicker" id="EndDate" name="EndDate" value="$pgEndDate">
                            <label class="active" for="EndDate">To Date</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                        <table class="table-basic"><tr>
                          <td><h3>Special Filtering<h3></td>
                          <td><p>
                            <label>
                              <input class="with-gap" type="radio" name="SpecialFilter" id="SpecialFilter1" value="moreThan1" $pgManyChecked/>
                              <span>Exclude visitors that only viewed 1 page</span>
                            </label>
                          </p></td>
                          <td><p>
                            <label>
                              <input class="with-gap" type="radio" name="SpecialFilter" id="SpecialFilter2" value="only1" $pgOnly1Checked/>
                              <span>Only 1-page visitors</span>
                            </label>
                          </p></td>
                          <td><p>
                            <label>
                              <input class="with-gap" type="radio" name="SpecialFilter" id="SpecialFilter4" value="mustFrom" $pgFromChecked/>
                              <span>Has Referer Domain</span>
                            </label>
                          </p></td>
                          <td><p>
                            <label>
                              <input class="with-gap" type="radio" name="SpecialFilter" id="SpecialFilter3" value="All" $pgAllChecked/>
                              <span>All visitors</span>
                            </label>
                          </p></td>
                          <td>
                            <button onclick="Javascript:ajaxPost('visitorStats','dateRngForm','N');" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
                          </td>
                        </tr></table>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col m4 offset-m4 s12">
                        <div class="card b-shadow">
                            <div class="card-content">
                                <h2 class="center">Tallies</h2>
                                <p>Unique Visitors:  $pgUniqueVisits</p>
                                <p>Total Page Visits:  $pgPageVisits</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12"><br><br><hr><br><br></div>
                    <div class="col m6 s12">
                        <div class="card">
                            <div class="card-content">
                                <h3 class="center">First Page Analytics</h3>
                                <p>This shows visitor analytics based on what was the <b>first</b> page
                                  they visited on web site.</p>
                                $pgFirstAnalytics
                            </div>
                        </div>
                    </div>
                    <div class="col m6 s12">
                        <div class="card">
                            <div class="card-content">
                                <h3 class="center">Last Page Analytics</h3>
                                <p>This shows visitor analytics based on what was the <b>last</b> page
                                  they visited on web site.</p>
                                $pgLastAnalytics
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" style="min-height: 774px;">
            <div id="dwmyChart" class="card-content">
                <h3>Visitors that viewed most pages</h3>
                $pgFreq1stPage
            </div>
        </div>
    </div>
</div>
htmVAR;

echo $pgHtm;
?>
