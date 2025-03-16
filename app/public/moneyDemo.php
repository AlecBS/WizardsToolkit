<?PHP
$pgSecurityLevel = 1;
require('wtk/wtkLogin.php');

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
        endif;
        $pgLimit = 8;
        break;
endswitch;

$pgTabBar =<<<htmVAR
<p>View: &nbsp;
<a onclick="JavaScript:ajaxFillDiv('moneyDemo','dwmyChart','dwmyChart','d')">Daily</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('moneyDemo','dwmyChart','dwmyChart','w')">Weekly</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('moneyDemo','dwmyChart','dwmyChart','m')">Monthly</a> &nbsp;
<a onclick="JavaScript:ajaxFillDiv('moneyDemo','dwmyChart','dwmyChart','y')">Yearly</a>
</p>
htmVAR;
$pgTabBar = wtkReplace($pgTabBar,"onclick=\"JavaScript:ajaxFillDiv('moneyDemo','dwmyChart','dwmyChart','$gloRNG')\"",'disabled class="black-text"');

if ($gloDriver1 == 'pgsql'):
    $pgSQL =<<<SQLVAR
SELECT $pgSelDates ,
    COUNT(`UID`) AS `Count`,
    SUM(`GrossAmount`) AS `Income`
  FROM `wtkRevenue`
GROUP BY $pgGroupBy
ORDER BY $pgOrderBy DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT $pgSelDates ,
    COUNT(`UID`) AS `Count`,
    FORMAT(SUM(`GrossAmount`),2) AS `Income`
  FROM `wtkRevenue`
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
  FROM `wtkRevenue` r
  $pgDateFilter
GROUP BY r.`PaymentStatus`
ORDER BY r.`PaymentStatus` DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT r.`PaymentStatus`, COUNT(r.`UID`) AS `Count`,
    FORMAT(SUM(r.`GrossAmount`),2) AS `Amount`
  FROM `wtkRevenue` r
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
      FROM `wtkRevenue` r
        INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
      $pgDateFilter
    GROUP BY e.`PaymentProvider`
    ORDER BY e.`PaymentProvider` DESC LIMIT :Limit
SQLVAR;
else:
    $pgSQL =<<<SQLVAR
SELECT e.`PaymentProvider`, FORMAT(COUNT(r.`UID`),0) AS `Count`,
    FORMAT(SUM(r.`GrossAmount`),2) AS `Amount`
  FROM `wtkRevenue` r
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
                            <button onclick="Javascript:revenueRptFilter();" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
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
function revenueRptFilter(){
    let fncFormData = $('#dateRngForm').serialize();
    fncFormData = fncFormData + '&apiKey=' + pgApiKey;
    $('#payChartSPAN').text('');
    $('#provChartSPAN').text('');

    $.ajax({
        type: "POST",
        url: 'moneyDemo.php?TableID=wtkRpt2',
        data: (fncFormData),
        success: function(data) {
            $('#payChartSPAN').html(data);
        }
    })
    $.ajax({
        type: "POST",
        url: 'moneyDemo.php?TableID=wtkRpt3',
        data: (fncFormData),
        success: function(data) {
            $('#provChartSPAN').html(data);
        }
    })
    $('#payFilterMsg').html('&nbsp;');
}
</script>
htmVAR;

echo $pgHtm;
?>
