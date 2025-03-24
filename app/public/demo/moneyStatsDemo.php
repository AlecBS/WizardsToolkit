<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT COUNT(*) AS `Count`
  FROM information_schema.`tables`
   WHERE `TABLE_SCHEMA` = 'wiztools' AND `TABLE_NAME` = :Table
SQLVAR;
$pgTableCount = wtkSqlGetOneResult($pgSQL, ['wtkRevenueDemo']);

if ($pgTableCount == 0):
    $pgHtm =<<<htmVAR
<div class="row">
    <div class="col m6 offset-m3 s12">
        <div class="card">
            <div class="card-content">
                <h3>Missing Revenue Demo Data</h3>
                <p>This demo page require `wtkRevenueDemo` data table and data.</p>
                <p>To generate the SQL table and data run the following scripts:
                    <ul class="browser-default">
                        <li>\SQL\mySQL\Utils\NameGeneration.sql</li>
                        <li>\SQL\mySQL\Utils\GenerateRevenueDemo.sql</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>
</div>
htmVAR;
    echo $pgHtm;
    exit;
endif;

if ($gloDriver1 == 'pgsql'):
    $pgDate = 'to_char(r."AddDate", \'YYYY-MM-DD\')';
else:
    $pgDate = "DATE_FORMAT(r.`AddDate`,'%Y-%m-%d')";
endif;
// BEGIN Filter logic
$pgDateFilter = '';
$pgFilterMsg = '<br>Currently no date range filtering.';
$pgStartDate = wtkGetParam('StartDate'); // to store filter value temporarily use wtkFilterRequest instead of wtkGetParam
$pgEndDate = wtkGetParam('EndDate');
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

switch ($gloRNG):
    case 'y':
        $pgTitle = 'Yearly';
        $pgMsg = 'Last five years.';
        if ($gloDriver1 == 'pgsql'):
            $pgGroupBy = 'to_char("AddDate", \'YYYY\')';
            $pgSelDates = $pgGroupBy . ' AS "Year"';
        else:
            $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%Y')";
            $pgSelDates = "DATE_FORMAT(`AddDate`,'%Y') AS `Year`";
        endif;
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 6;
        break;
    case 'm':
        $pgTitle = 'Monthly';
        $pgMsg = 'Last six months.';
        if ($gloDriver1 == 'pgsql'):
            $pgGroupBy = 'to_char("AddDate", \'MM\')';
            $pgSelDates = $pgGroupBy . ' AS "Month"';
        else:
            $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%m')";
            $pgSelDates = "DATE_FORMAT(`AddDate`,'%M') AS `Month`";
        endif;
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 6;
        break;
    case 'd':
        $pgTitle = 'Daily';
        $pgMsg = 'Last fourteen days.';
        if ($gloDriver1 == 'pgsql'):
            $pgSelDates = 'to_char("AddDate", \'Mon FMDDth (Dy)\') AS "Day"';
            $pgGroupBy = 'to_char("AddDate", \'YYYYJ\'),to_char("AddDate", \'Mon FMDDth (Dy)\')';
            $pgOrderBy = 'to_char("AddDate", \'YYYYJ\')';
        else:
            $pgSelDates = "DATE_FORMAT(`AddDate`,'%b %D (%a)') AS `Day`";
            $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y%j')";
            $pgOrderBy = $pgGroupBy;
        endif;
        $pgLimit = 14;
        break;
    default: // w
        $gloRNG = 'w';
        $pgTitle = 'Weekly';
        $pgMsg = 'Last eight weeks where Monday is the first day of the week.';
        if ($gloDriver1 == 'pgsql'):
            $pgSelDates = 'to_char("AddDate", \'WW\') AS "Week"';
            $pgGroupBy = 'to_char("AddDate", \'WW\')';
            $pgOrderBy = $pgGroupBy;
        else:
            $pgGroupBy = "YEAR(`AddDate`), WEEK(`AddDate`, 1)";
            $pgSelDates = "DATE_FORMAT(DATE_ADD(`AddDate`, INTERVAL (1 - DAYOFWEEK(`AddDate`)) + 7 DAY), '%b %D') AS `WeekEnding`" . "\n";
            $pgOrderBy = 'YEAR(`AddDate`) DESC, WEEK(`AddDate`, 1)';

            // $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%u')";
            // $pgSelDates  = "DATE_FORMAT(DATE_ADD(DATE_ADD(DATE_FORMAT(`AddDate`,'%Y-01-01')," . "\n";
            // $pgSelDates .= " INTERVAL DATE_FORMAT(`AddDate`,'%u') WEEK), INTERVAL 1 DAY),'%b %D') AS `WeekEnding`";
            // $pgOrderBy = $pgGroupBy;
        endif;
        $pgLimit = 8;
        break;
endswitch;

$pgTabBar =<<<htmVAR
<p>View: &nbsp;
<a onclick="JavaScript:ajaxFillDiv('/demo/moneyStatsDemo','dwmyChart','dwmyChart','d')">Daily</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('/demo/moneyStatsDemo','dwmyChart','dwmyChart','w')">Weekly</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('/demo/moneyStatsDemo','dwmyChart','dwmyChart','m')">Monthly</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('/demo/moneyStatsDemo','dwmyChart','dwmyChart','y')">Yearly</a>
</p>
htmVAR;
$pgTabBar = wtkReplace($pgTabBar,"onclick=\"JavaScript:ajaxFillDiv('/admin/moneyStats','dwmyChart','dwmyChart','$gloRNG')\"",'disabled class="black-text"');

if ($gloDriver1 == 'pgsql'):
    $pgSQL =<<<SQLVAR
SELECT $pgSelDates ,
    COUNT(`UID`) AS `Count`,
    SUM(`GrossAmount`) AS `Income`
  FROM `wtkRevenueDemo`
GROUP BY $pgGroupBy
ORDER BY $pgOrderBy DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT $pgSelDates ,
    COUNT(`UID`) AS `Count`,
    FORMAT(SUM(`GrossAmount`),2) AS `Income`
  FROM `wtkRevenueDemo`
GROUP BY $pgGroupBy
ORDER BY $pgOrderBy DESC LIMIT :Limit
SQLVAR;
endif;
$pgSqlFilter = array (
    'Limit' => $pgLimit
);
$gloColumnAlignArray = array (
    'Count'   => 'center',
    'Year'  => 'center',
    'Month' => 'center',
    'WeekEnding' => 'center',
    'Day'   => 'center',
	'Income' => 'center'
);
$pgSQL = wtkSqlPrep($pgSQL);

$gloSkipFooter = true;
$pgChartOps = array('regRpt', 'bar','area');
$pgTableID = wtkGetGet('TableID');
if ($pgTableID == ''):
    $pgChart = wtkRptChart($pgSQL, $pgSqlFilter, $pgChartOps, 1);
    if (wtkGetParam('p') == 'dwmyChart'):
        $pgHtm  = "<h3>Income Earned $pgTitle</h3>" . "\n";
        $pgHtm .= "<p>$pgMsg</p>" . "\n" . $pgTabBar;
        $pgHtm .= $pgChart . "\n";
        echo $pgHtm;
        exit;
    endif;
endif;

// BEGIN Analytics based on Payment Status
if ($gloDriver1 == 'pgsql'):
    $pgSQL =<<<SQLVAR
SELECT r.`PaymentStatus`, COUNT(r.`UID`) AS `Count`,
    SUM(r.`GrossAmount`) AS `Amount`
  FROM `wtkRevenueDemo` r
  $pgDateFilter
GROUP BY r.`PaymentStatus`
ORDER BY r.`PaymentStatus` DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT r.`PaymentStatus`, COUNT(r.`UID`) AS `Count`,
    FORMAT(SUM(r.`GrossAmount`),2) AS `Amount`
  FROM `wtkRevenueDemo` r
  $pgDateFilter
GROUP BY r.`PaymentStatus`
ORDER BY r.`PaymentStatus` DESC LIMIT :Limit
SQLVAR;
endif;
$gloColumnAlignArray = array (
    'Count' => 'center',
	'Amount' => 'center'
);
$pgPayChart = wtkRptChart($pgSQL, $pgSqlFilter, $pgChartOps, 2);
if ($pgTableID == 'wtkRpt2'):
    echo $pgPayChart;
    exit;
endif;
//  END  Analytics based on Payment Status

// BEGIN Analytics based on PaymentProvider
if ($gloDriver1 == 'pgsql'):
    $pgSQL =<<<SQLVAR
    SELECT e.`PaymentProvider`, COUNT(r.`UID`) AS `Count`,
        SUM(r.`GrossAmount`) AS `Amount`
      FROM `wtkRevenueDemo` r
        INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
      $pgDateFilter
    GROUP BY e.`PaymentProvider`
    ORDER BY e.`PaymentProvider` DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT e.`PaymentProvider`, FORMAT(COUNT(r.`UID`),0) AS `Count`,
    FORMAT(SUM(r.`GrossAmount`),2) AS `Amount`
  FROM `wtkRevenueDemo` r
    INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
  $pgDateFilter
GROUP BY e.`PaymentProvider`
ORDER BY e.`PaymentProvider` DESC LIMIT :Limit
SQLVAR;
endif;
$gloTotalArray = array (
    'Count'  => 'SUM',
	'Amount' => 'DSUM'
);
$pgProviderChart = wtkRptChart($pgSQL, $pgSqlFilter, $pgChartOps, 3);
if ($pgTableID == 'wtkRpt3'):
    echo $pgProviderChart;
    exit;
endif;
//  END  Analytics based on PaymentProvider

$pgPhoneBR = '';
if ($gloDeviceType == 'phone'):
    $pgPhoneBR = '<br>';
endif;

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <h3 class="center">Choose Date Range</h3>
                <p class="center" id="payFilterMsg">$pgFilterMsg</p>
                <form id="dateRngForm" name="dateRngForm">
                    <input type="hidden" name="HasDatePicker" id="HasDatePicker" value="Y">
                    <div class="row">
                        <div class="input-field col m2 offset-m4 s5">
                            <input type="text" class="datepicker" id="StartDate" name="StartDate" value="$pgStartDate">
                            <label class="active" for="StartDate">From Start Date</label>
                        </div>
                        <div class="input-field col m2 s5">
                            <input type="text" class="datepicker" id="EndDate" name="EndDate" value="$pgEndDate">
                            <label class="active" for="EndDate">To End Date</label>
                        </div>
                        <div class="input-field col m1 s2">
                            <button onclick="Javascript:revenueDemoRptFilter();" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col m6 s12">
                        $pgPhoneBR<h3>&nbsp;Payment Status</h3>
                        <span id="payChartSPAN">$pgPayChart</span>
                    </div>
                    <div class="col m6 s12">
                        $pgPhoneBR<h3>&nbsp;Payment Provider</h3>
                        <span id="provChartSPAN">$pgProviderChart</span>
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
                <h3>Income Earned $pgTitle</h3>
                <p>$pgMsg</p>
                $pgTabBar
                $pgChart
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function revenueDemoRptFilter(){
    waitLoad('on');
    let fncDone = 0;
    let fncToDo = 2;
    let fncFormData = $('#dateRngForm').serialize();
    fncFormData = fncFormData + '&apiKey=' + pgApiKey;
    $('#payChartSPAN').text('');
    $('#provChartSPAN').text('');
    $.ajax({
        type: "POST",
        url: '/demo/moneyStatsDemo.php?TableID=wtkRpt2',
        data: (fncFormData),
        success: function(data) {
            fncDone ++;
            $('#payChartSPAN').html(data);
            if (fncToDo == fncDone){
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
    $.ajax({
        type: "POST",
        url: '/demo/moneyStatsDemo.php?TableID=wtkRpt3',
        data: (fncFormData),
        success: function(data) {
            fncDone ++;
            $('#provChartSPAN').html(data);
            if (fncToDo == fncDone){
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
    $('#payFilterMsg').html('&nbsp;');
}
</script>
htmVAR;

echo $pgHtm;
?>
