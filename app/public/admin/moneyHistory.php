<?php
if (!isset($gloConnected)):
    $pgSecurityLevel = 80;
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

switch ($gloCurrencyCode):
    case 'GBP':
        $pgCurrency = '&pound;';
        break;
    case 'USD':
    case 'MXN':
    case 'CAN':
        $pgCurrency = '$';
        break;
    default:
        $pgCurrency = '$';
        break;
endswitch;

// BEGIN Filter logic
$pgDateFilter = '';
$pgStartDate = wtkGetParam('StartDate'); // to store filter value temporarily use wtkFilterRequest instead of wtkGetParam
$pgEndDate = wtkGetParam('EndDate');
$pgFilterMsg = '';
$pgFirstVisit = false;
if (($pgStartDate == '') || ($pgEndDate == '')):
    $pgStartDate = date('M j, Y', strtotime('last month'));
    $pgEndDate = date('M j, Y', strtotime('today'));
    $pgFirstVisit = true;
    $pgFilterMsg = '<br>Choose date range to filter by.';
endif;
if (($pgStartDate != '') || ($pgEndDate != '')):
    if ($pgFilterMsg == ''):
        $pgFilterMsg = '<br>Report only showing data created ' . "\n";
    endif;
    $pgStart = date('Y-m-d', strtotime($pgStartDate));
    $pgEnd = date('Y-m-d', strtotime($pgEndDate));
    if (($pgStartDate != '') && ($pgEndDate != '')):
        $pgFilterMsg .= ' from ' . $pgStartDate . ' to ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(`AddDate`,'%Y-%m-%d') BETWEEN '$pgStart' AND '$pgEnd'";
    elseif ($pgStartDate != ''): // EndDate must be blank
        $pgFilterMsg .= ' on or after ' . $pgStartDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(`AddDate`,'%Y-%m-%d') >= '$pgStart'";
    else:
        $pgFilterMsg .= ' before or on ' . $pgEndDate . '.';
        $pgDateFilter = " WHERE DATE_FORMAT(`AddDate`,'%Y-%m-%d') <= '$pgEnd'";
    endif;
endif;
//  END  Filter logic

switch ($gloRNG):
    case 'y':
        $pgTitle = 'Yearly';
        $pgMsg = 'Last five years.';
        $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%Y')";
        $pgSelDates = "DATE_FORMAT(`AddDate`,'%Y') AS `Year`";
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 6;
        break;
    case 'm':
        $pgTitle = 'Monthly';
        $pgMsg = 'Last six months.';
        $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%m')";
        $pgSelDates = "DATE_FORMAT(`AddDate`,'%M') AS `Month`";
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 6;
        break;
    case 'd':
        $pgTitle = 'Daily';
        $pgMsg = 'Last fourteen days.';
        $pgSelDates = "DATE_FORMAT(`AddDate`,'%b %D (%a)') AS `Day`";
        $pgGroupBy = "DATE_FORMAT(`AddDate`,'%j')";
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 14;
        break;
    default: // w
        $gloRNG = 'w';
        $pgTitle = 'Weekly';
        $pgMsg = 'Last eight weeks where Monday is the first day of the week.';
        $pgGroupBy = "DATE_FORMAT(`AddDate`,'%Y-%u')";
        $pgSelDates  = "DATE_FORMAT(DATE_ADD(DATE_ADD(DATE_FORMAT(`AddDate`,'%Y-01-01')," . "\n";
        $pgSelDates .= " INTERVAL DATE_FORMAT(`AddDate`,'%u') WEEK), INTERVAL 1 DAY),'%b %D') AS `WeekEnding`";
        $pgOrderBy = $pgGroupBy;
        $pgLimit = 8;
        break;
endswitch;

$pgSqlFilter = array (
    'Limit' => $pgLimit
);
$gloColumnAlignArray = array (
    'Count'  => 'center',
    'RefundsCurrentYear'  => 'right',
    'RefundsLastYear' => 'right',
    'IncomeCurrentYear'  => 'right',
    'IncomeLastYear' => 'right',
    'GrossAmount'  => 'center',
    'Amount'  => 'center',
    'Year'  => 'center',
    'Month' => 'center',
    'WeekEnding' => 'center',
    'Day'   => 'center',
    'Income' => 'center',
    'IncomeComparison' => 'center',
	'RefundComparison' => 'center'
);

$gloSkipFooter = true;
$pgChart = '2DO';
$pgChartOps = array('regRpt', 'bar','area');
$pgTableID = wtkGetPost('TableID');

// BEGIN determine which completed quarter is most recent
$pgCurrMonth = date('n');
if ($pgCurrMonth < 4):
    $pgCurrToYear = date('Y', strtotime('last year'));
    $pgCurrFromYear = $pgCurrToYear; // will be Q1 to Q4 of same year
else:
    $pgCurrToYear = date('Y');
    $pgCurrFromYear = ($pgCurrToYear - 1);
endif;
switch ($pgCurrMonth):
    case 1:
    case 2:
    case 3:
        $pgStartQtr = 1;
        $pgEndQtr = 4;
        break;
    case 4:
    case 5:
    case 6:
        $pgStartQtr = 2;
        $pgEndQtr = 1;
        break;
    case 7:
    case 8:
    case 9:
        $pgStartQtr = 3;
        $pgEndQtr = 2;
        break;
    case 10:
    case 11:
    case 12:
        $pgStartQtr = 4;
        $pgEndQtr = 3;
        break;
endswitch;
$pgLastToYear = ($pgCurrToYear - 1);
$pgLastFromYear = ($pgCurrFromYear - 1);
// above is for: Net Comparison Quarterly Analytics; below is for Last 12 quarters
$pg3YrsAgo = ($pgCurrFromYear - 2);
//  END  determine which completed quarter is most recent

// BEGIN Last 12 quarters
$pgSQL =<<<SQLVAR
SELECT CONCAT('Qtr ', `Quarter`, ', ', `YearTracked`) AS `Year-Quarter`,
  FORMAT(SUM(`GrossIncome`),2) AS 'GrossIncome',
  FORMAT(SUM(`Refunds`),2) AS 'Refunds',
  FORMAT(SUM(`GrossIncome`) - SUM(`Refunds`),2) AS 'NetIncome',
  CONCAT(FORMAT((SUM(`Refunds`) / SUM(`GrossIncome`)) * 100,2),'%') AS 'RefundPercentage'
   FROM `wtkIncomeByMonth`
WHERE CONCAT(`YearTracked`,'-',`Quarter`) >= '$pg3YrsAgo-$pgStartQtr'
GROUP BY CONCAT(`YearTracked`,'-',`Quarter`)
ORDER BY CONCAT(`YearTracked`,'-',`Quarter`) ASC LIMIT 12;
SQLVAR;

if ($pgTableID == 'wtkRpt1'):
    $pgHasTabs = true; // so does not do it twice
    $gloSuppressChartArray[] = 'RefundPercentage';
    $gloColumnAlignArray = array (
        'Year-Quarter' => 'center',
        'GrossIncome' => 'right',
        'Refunds' => 'right',
        'NetIncome' => 'right',
        'RefundPercentage' => 'center'
    );
    $pgChart = wtkRptChart($pgSQL, [], $pgChartOps, 1);
    echo $pgChart;
    exit;
endif;
//  END  Last 12 quarters

// BEGIN Revenue Comparison for last 6 full months
$pgStartMon = date('Y-m', strtotime('6 months ago'));
$pgEndMon = date('Y-m', strtotime('last month'));
$pgPriorYrStart = date('Y-m', strtotime('18 months ago'));
$pgPriorYrEnd = date('Y-m', strtotime('-1 year', strtotime($pgEndMon)));
$pgSQL =<<<SQLVAR
SELECT
  CASE `MonthInYear`
    WHEN 1 THEN 'Jan'
    WHEN 2 THEN 'Feb'
    WHEN 3 THEN 'Mar'
    WHEN 4 THEN 'Apr'
    WHEN 5 THEN 'May'
    WHEN 6 THEN 'Jun'
    WHEN 7 THEN 'Jul'
    WHEN 8 THEN 'Aug'
    WHEN 9 THEN 'Sep'
    WHEN 10 THEN 'Oct'
    WHEN 11 THEN 'Nov'
    WHEN 12 THEN 'Dec'
  END AS `Month`,
  FORMAT(SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
          THEN `GrossIncome`
        ELSE 0 END),2) AS 'IncomeCurrentYear',
  FORMAT(SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
          THEN `GrossIncome`
        ELSE 0 END),2) AS 'IncomeLastYear',
  CONCAT(FORMAT(
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
              THEN `GrossIncome`
            ELSE 0 END
         )
      /
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
              THEN `GrossIncome`
            ELSE 0 END
         )
       * 100,2),'%')
  AS `IncomeComparison`,
    FORMAT(SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
            THEN `Refunds`
          ELSE 0 END),2) AS 'RefundsCurrentYear',
    FORMAT(SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
            THEN `Refunds`
          ELSE 0 END),2) AS 'RefundsLastYear',
    CONCAT(FORMAT(
        SUM(
            CASE
              WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
                THEN `Refunds`
              ELSE 0 END
           )
        /
        SUM(
            CASE
              WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
                THEN `Refunds`
              ELSE 0 END
           )
         * 100,2),'%')
    AS `RefundComparison`
 FROM `wtkIncomeByMonth`
WHERE CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0'))
    BETWEEN '$pgPriorYrStart' AND '$pgEndMon'
GROUP BY `MonthInYear`
ORDER BY CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) ASC LIMIT 6
SQLVAR;
if ($pgTableID == 'wtkRpt2'):
    $pgHasTabs = true; // so does not do it twice
    $gloSuppressChartArray[] = 'RefundComparison';
    $gloSuppressChartArray[] = 'IncomeComparison';
    $gloTotalArray = array (
        'IncomeCurrentYear' => 'SUM',
        'IncomeLastYear' => 'SUM',
        'RefundsCurrentYear' => 'SUM',
    	'RefundsLastYear' => 'SUM'
    );
    $pgChart = wtkRptChart($pgSQL, [], $pgChartOps, 2);
    echo $pgChart;
    exit;
endif;
//  END  Revenue Refund Comparison for last 6 full months

// BEGIN Net Comparison Monthly Analytics
$pgSQL =<<<SQLVAR
SELECT
  CASE `MonthInYear`
    WHEN 1 THEN 'Jan'
    WHEN 2 THEN 'Feb'
    WHEN 3 THEN 'Mar'
    WHEN 4 THEN 'Apr'
    WHEN 5 THEN 'May'
    WHEN 6 THEN 'Jun'
    WHEN 7 THEN 'Jul'
    WHEN 8 THEN 'Aug'
    WHEN 9 THEN 'Sep'
    WHEN 10 THEN 'Oct'
    WHEN 11 THEN 'Nov'
    WHEN 12 THEN 'Dec'
  END AS `Month`,
  FORMAT(
    SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
          THEN `GrossIncome`
        ELSE 0 END)
        -
    SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
            THEN `Refunds`
          ELSE 0 END)
    ,2) AS 'NetCurrentYear',
  FORMAT(
    SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
          THEN `GrossIncome`
        ELSE 0 END)
    -
    SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
            THEN `Refunds`
          ELSE 0 END)
    ,2) AS 'NetLastYear',

  CONCAT(FORMAT(
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
            THEN `GrossIncome`
          ELSE 0 END)
          -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
              THEN `Refunds`
            ELSE 0 END)
      )
      -
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
            THEN `GrossIncome`
          ELSE 0 END)
      -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
              THEN `Refunds`
            ELSE 0 END)
      ),2))
  AS `NetDifference`,
  CONCAT(FORMAT(
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
            THEN `GrossIncome`
          ELSE 0 END)
          -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgStartMon' AND '$pgEndMon'
              THEN `Refunds`
            ELSE 0 END)
      )
      /
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
            THEN `GrossIncome`
          ELSE 0 END)
      -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) BETWEEN '$pgPriorYrStart' AND '$pgPriorYrEnd'
              THEN `Refunds`
            ELSE 0 END)
      ) * 100,2),'%')
  AS `NetComparison`
 FROM `wtkIncomeByMonth`
WHERE CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0'))
    BETWEEN '$pgPriorYrStart' AND '$pgEndMon'
GROUP BY `MonthInYear`
ORDER BY CONCAT(`YearTracked`,'-',LPAD(`MonthInYear`,2,'0')) ASC LIMIT 6
SQLVAR;
if ($pgTableID == 'wtkRpt3'):
    $pgHasTabs = true; // so does not do it twice
    $gloSuppressChartArray[] = 'NetComparison';
    $gloColumnAlignArray = array (
        'Month' => 'center',
        'NetCurrentYear' => 'right',
        'NetLastYear' => 'right',
        'NetDifference' => 'right',
    	'NetComparison' => 'center'
    );
    $gloTotalArray = array (
        'NetCurrentYear' => 'SUM',
        'NetDifference' => 'SUM',
        'NetLastYear' => 'SUM'
    );
    $pgChart = wtkRptChart($pgSQL, [], $pgChartOps, 3);
    echo $pgChart;
    exit;
endif;
//  END  Net Comparison Monthly Analytics

// BEGIN Net Comparison Quarterly Analytics
$pgSQL =<<<SQLVAR
SELECT `Quarter`,
  FORMAT(
    SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
          THEN `GrossIncome`
        ELSE 0 END)
        -
    SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
            THEN `Refunds`
          ELSE 0 END)
    ,2) AS 'NetCurrentYear',

  FORMAT(
    SUM(
      CASE
        WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
          THEN `GrossIncome`
        ELSE 0 END)
    -
    SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
            THEN `Refunds`
          ELSE 0 END)
    ,2) AS 'NetLastYear',

  CONCAT(FORMAT(
      (
      SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
            THEN `GrossIncome`
          ELSE 0 END)
          -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
              THEN `Refunds`
            ELSE 0 END)
      )
      -
      (
      SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
            THEN `GrossIncome`
          ELSE 0 END)
      -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
              THEN `Refunds`
            ELSE 0 END)
      ),2))
  AS `NetDifference`,
  CONCAT(FORMAT(
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
            THEN `GrossIncome`
          ELSE 0 END)
          -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgCurrFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
              THEN `Refunds`
            ELSE 0 END)
      )
      /
      (SUM(
        CASE
          WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
            THEN `GrossIncome`
          ELSE 0 END)
      -
      SUM(
          CASE
            WHEN CONCAT(`YearTracked`,'-',`Quarter`) BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgLastToYear-$pgEndQtr'
              THEN `Refunds`
            ELSE 0 END)
      ) * 100,2),'%')
  AS `NetComparison`
 FROM `wtkIncomeByMonth`
WHERE CONCAT(`YearTracked`,'-',`Quarter`)
    BETWEEN '$pgLastFromYear-$pgStartQtr' AND '$pgCurrToYear-$pgEndQtr'
GROUP BY `Quarter`
ORDER BY CONCAT(`YearTracked`,'-',`Quarter`) ASC LIMIT 4
SQLVAR;

if ($pgTableID == 'wtkRpt4'):
    $pgHasTabs = true; // so does not do it twice
    $gloSuppressChartArray[] = 'NetComparison';
    $gloColumnAlignArray = array (
        'Quarter' => 'center',
        'NetCurrentYear' => 'right',
        'NetLastYear' => 'right',
        'NetDifference' => 'right',
    	'NetComparison' => 'center'
    );
    $gloTotalArray = array (
        'NetCurrentYear' => 'SUM',
        'NetDifference' => 'SUM',
        'NetLastYear' => 'SUM'
    );
    $pgChart = wtkRptChart($pgSQL, [], $pgChartOps, 4);
    echo $pgChart;
    exit;
endif;
//  END  Net Comparison Quarterly Analytics

$pgHtm =<<<htmVAR
<div class="row" id="rptsRow">
    <div class="col m10 offset-m1 s12">
        <h3>Monthly and Quarterly Analytics</h3>
        <p>This is only updated on the first of each month for prior month&rsquo;s totals.</p>
        <div class="card" style="min-height: 540px;">
            <div class="card-content">
                <h3>&nbsp;Last 3 Years by Quarter</h3>
                <span id="qtrChartSPAN"></span>
            </div>
        </div>
    </div>
    <div class="col m12 s12"><br>
        <div class="card" style="min-height: 540px;">
            <div class="card-content">
                <h3>6-Month Revenue Comparison Analytics</h3>
                <p>This compares last 6 months to same months of prior year.</p>
                <p id="refundGoodNews" class="green-text hide">There was
                    <strong>$pgCurrency<span id="refundGoodDifAmt"></span></strong>
                    less in refunds during the last 6 months compared to prior year!</p>
                <p id="refundBadNews" class="red-text hide">The last six months had
                    <strong>$pgCurrency<span id="refundBadDifAmt"></span> more</strong>
                    in refunds compared to prior year same period!</p>
                <p id="incomeGoodNews" class="green-text hide">There was
                    <strong>$pgCurrency<span id="incomeGoodDifAmt"></span></strong>
                    more in income during the last 6 months compared to prior year!</p>
                <p id="incomeBadNews" class="red-text hide">The last six months had
                    <strong>$pgCurrency<span id="incomeBadDifAmt"></span> less</strong>
                    in income compared to prior year same period!</p>
                <span id="revenueChartSPAN"></span>
            </div>
        </div>
    </div>
    <div class="col m12 s12"><br>
        <div class="card" style="min-height: 540px;">
            <div class="card-content">
                <h3>Net Comparison Monthly Analytics</h3>
                <p>This compares last 6 months to same months of prior year.
                    The values are Income less Refunds.</p>
                <span id="netChartSPAN"></span>
            </div>
        </div>
    </div>
    <div class="col m12 s12"><br>
        <div class="card" style="min-height: 540px;">
            <div class="card-content">
                <h3>Net Comparison Quarterly Analytics</h3>
                <p>This compares last 4 quarters compared to same quarters of prior year.
                    The values are Income less Refunds.</p>
                <span id="netQtrChartSPAN"></span>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

function revenueRptFilter(){
    waitLoad('on');
    let fncDone = 0;
    let fncToDo = 4;
    $('#qtrChartSPAN').text('');
    $('#revenueChartSPAN').text('');
    $.ajax({
        type: "POST",
        url: '/admin/moneyHistory.php',
        data: { apiKey: pgApiKey, TableID: 'wtkRpt2'},
        success: function(data) {
            fncDone ++;
            $('#revenueChartSPAN').html(data);
            let fncRefundsLastYear = parseFloat($('#wtkTotalRefundsLastYear').text().replace(',', ''));
            let fncRefundsCurrentYear = parseFloat($('#wtkTotalRefundsCurrentYear').text().replace(',', ''));
            let fncResult = (fncRefundsLastYear - fncRefundsCurrentYear);
            let fncFormattedResult = fncResult.toLocaleString('en-US', {maximumFractionDigits: 2});
            if (fncRefundsCurrentYear < fncRefundsLastYear){
                $('#refundGoodDifAmt').text(fncFormattedResult);
                $('#refundGoodNews').removeClass('hide');
            } else {
                let fncBadResult = (fncRefundsCurrentYear - fncRefundsLastYear);
                let fncBadFormattedResult = fncBadResult.toLocaleString('en-US', {maximumFractionDigits: 2});
                $('#refundBadDifAmt').text(fncBadFormattedResult);
                $('#refundBadNews').removeClass('hide');
            }
            let fncIncomeLastYear = parseFloat($('#wtkTotalIncomeLastYear').text().replace(/,/g, ''));
            let fncIncomeCurrentYear = parseFloat($('#wtkTotalIncomeCurrentYear').text().replace(/,/g, ''));
            let fncIncResult = (fncIncomeCurrentYear - fncIncomeLastYear);
            let fncIncFormattedResult = fncIncResult.toLocaleString('en-US', {maximumFractionDigits: 2});
            if (fncIncomeCurrentYear > fncIncomeLastYear){
                $('#incomeGoodDifAmt').text(fncIncFormattedResult);
                $('#incomeGoodNews').removeClass('hide');
            } else {
                fncIncResult = (fncIncomeLastYear - fncIncomeCurrentYear);
                fncIncFormattedResult = fncIncResult.toLocaleString('en-US', {maximumFractionDigits: 2});
                $('#incomeBadDifAmt').text(fncIncFormattedResult);
                $('#incomeBadNews').removeClass('hide');
            }

            if (fncToDo == fncDone){
                $('#rptsRow').removeClass('hide');
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
    $.ajax({
        type: "POST",
        url: '/admin/moneyHistory.php',
        data: { apiKey: pgApiKey, TableID: 'wtkRpt1'},
        success: function(data) {
            fncDone ++;
            $('#qtrChartSPAN').html(data);
            if (fncToDo == fncDone){
                $('#rptsRow').removeClass('hide');
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
    $.ajax({
        type: "POST",
        url: '/admin/moneyHistory.php',
        data: { apiKey: pgApiKey, TableID: 'wtkRpt3'},
        success: function(data) {
            fncDone ++;
            $('#netChartSPAN').html(data);
            if (fncToDo == fncDone){
                $('#rptsRow').removeClass('hide');
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
    $.ajax({
        type: "POST",
        url: '/admin/moneyHistory.php',
        data: { apiKey: pgApiKey, TableID: 'wtkRpt4'},
        success: function(data) {
            fncDone ++;
            $('#netQtrChartSPAN').html(data);
            if (fncToDo == fncDone){
                $('#rptsRow').removeClass('hide');
                let fncTabs = document.querySelectorAll('.tabs');
                let fncTmp = M.Tabs.init(fncTabs); // it is critical this is only done after all have loaded
                waitLoad('off');
            }
        }
    })
}

revenueRptFilter();
</script>
htmVAR;

echo $pgHtm;
exit;
?>
