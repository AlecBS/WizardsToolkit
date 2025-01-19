<?PHP
$pgSecurityLevel = 1;
//$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
/*
This page shows how easy it is to have Date Range filter on a page
with multiple Browse Boxes or Charts on single page.
It is critical that third parameter is different for each list so AJAX knows which to replace.
*/

if ($gloDriver1 == 'pgsql'):
    $gloSqlYYMMDD = 'YYYY-MM-DD';
else: // assume mySQL
    $gloSqlYYMMDD = '%Y-%m-%d';
endif;


// BEGIN Filter logic
$pgDateFilter = '';
$pgFilterMsg = '<br>These show all data - no date range filtering.';
$pgStartDate = wtkGetParam('StartDate'); // to store filter value temporarily use wtkFilterRequest instead of wtkGetParam
$pgEndDate = wtkGetParam('EndDate');
if (($pgStartDate != '') || ($pgEndDate != '')):
    $pgFilterMsg = '<br>Report only showing data created ' . "\n";
    $pgStart = date('Y-m-d', strtotime($pgStartDate));
    $pgEnd = date('Y-m-d', strtotime($pgEndDate));
    if (($pgStartDate != '') && ($pgEndDate != '')):
        $pgFilterMsg .= ' from ' . $pgStartDate . ' to ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(m.`FirstLogin`,'$gloSqlYYMMDD') BETWEEN '$pgStart' AND '$pgEnd'";
    elseif ($pgStartDate != ''): // EndDate must be blank
        $pgFilterMsg .= ' on or after ' . $pgStartDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(m.`FirstLogin`,'$gloSqlYYMMDD') >= '$pgStart'";
    else:
        $pgFilterMsg .= ' before or on ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(m.`FirstLogin`,'$gloSqlYYMMDD') <= '$pgEnd'";
    endif;
endif;
//  END  Filter logic

// BEGIN wtkLoginLog section
$pgSQL =<<<SQLVAR
SELECT CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `User`, COUNT(m.`UID`) AS `Count`
  FROM `wtkLoginLog` m
    LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = m.`UserUID`
$pgDateFilter
GROUP BY m.`UserUID`
ORDER BY u.`FirstName` ASC
SQLVAR;

$gloColumnAlignArray = array (
    'Count' => 'center'
);
$gloTotalArray = array (
    'Count' => 'SUM'
);
$gloSkipFooter = true;
$pgChartOps = array('regRpt', 'bar','pie');
$pgChart1 = wtkRptChart($pgSQL, [], $pgChartOps, 1);
//  END  wtkLoginLog section

$gloTotalArray = array ();
// BEGIN UpdateLog chart by User
// everything is the same except the data table and AddDate instead of FirstLogin
$pgSQL = wtkReplace($pgSQL, 'wtkLoginLog','wtkUpdateLog');
$pgSQL = wtkReplace($pgSQL, 'FirstLogin', 'AddDate');

// note 2 as last parameter allows multiple charts on single page
$pgChart2 = wtkRptChart($pgSQL, [], $pgChartOps, 2);
//  END  UpdateLog chart by User
// BEGIN UpdateLog chart by User
// everything is the same except the data table
$pgSQL = wtkReplace($pgSQL,'wtkUpdateLog', 'wtkUserHistory');
$pgChart3 = wtkRptChart($pgSQL, [], $pgChartOps, 3);
//  END  UpdateLog chart by User

$pgHtm =<<<htmVAR
<h3 class="center">Date Range Analytics Demo</h3>
<p class="center">$pgFilterMsg</p>
<div class="container">
    <form id="dateRngForm" name="dateRngForm" role="search" class="wtk-search card b-shadow" style="max-width:450px;margin: 0 auto;">
        <input type="hidden" name="HasDatePicker" id="HasDatePicker" value="Y">
        <div class="input-field">
            <div class="filter-width-50">
                <input type="text" class="datepicker" id="StartDate" name="StartDate" value="$pgStartDate" placeholder="from Start Date">
            </div>
            <div class="filter-width-50">
                <input type="text" class="datepicker" id="EndDate" name="EndDate" value="$pgEndDate" placeholder="to End Date">
            </div>
            <button onclick="Javascript:ajaxPost('rptDateRange','dateRngForm','N')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
</div>
<div class="row">
    <div class="col m8 offset-m2 s12">
        <div class="card">
            <div class="card-content">
                <h5 class="center">Login Logs</h5>
                $pgChart1
                <br>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col m6 s12 valign-helper">
        <div class="card">
            <div class="card-content">
                <h5 class="center">Update Logs</h5>
                $pgChart2
            </div>
        </div>
    </div>
    <div class="col m6 s12">
        <div class="card">
            <div class="card-content">
                <h5 class="center">User History</h5>
                $pgChart3
            </div>
        </div>
    </div>
</div>
htmVAR;

wtkProtoType($pgHtm);
echo $pgHtm;
?>
