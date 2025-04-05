<?PHP
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
    $pgHideHeader = false;
else:
    $pgHideHeader = true;
    $gloCurrentPage = '/wtk/reports.php';
endif;

$pgCanPrint = wtkSqlGetOneResult('SELECT `CanPrint` FROM `wtkUsers` WHERE `UID` = ?',[$gloUserUID]);
if ($pgCanPrint == 'N'):
    $pgHtm =<<<htmVAR
    <div class="container">
        <h3>Report</h3><br>
        <p>Your account does not have report priviledges.
        If you need access to reports please contact your manager.</p>
        <a onclick="Javascript:wtkGoBack()">Return</a>
    </div>
htmVAR;
    echo $pgHtm;
    exit;
endif;

$pgHtm = '';
$pgRptType = 'web';
$pgRptMode = wtkGetParam('Mode');

if ($gloRNG == ''):
    $pgHtm .= 'No report selected';
else:   // Not $pgRptUID == ''
    switch ($pgRptMode):
        case 'Export':
            $pgRptType = 'csv';
            $gloForceRO = true;
            break;
        case 'XML':
            $pgRptType = 'xml';
            $gloForceRO = true;
            break;
        default:
            $pgRptType = 'pdf';
    endswitch;

    $pgSQL =<<<SQLVAR
SELECT `RptName`, `RptSelect`, `RptNotes`,
 `AlignCenter`, `AlignRight`, `AddLink`, `EditLink`,
 `SelTableName`, `SelValueColumn`, `SelDisplayColumn`, `SelWhere`,
 `FieldSuppress`, `SortableCols`, `TotalCols`, `TotalMoneyCols`,
 `TestMode`, `HideFooter`, `URLredirect`,
 `StartDatePrompt`, `EndDatePrompt`, `DaysAgo`,
 `GraphRpt`,`ChartSuppress`,
 `RegRpt`,`BarChart`,`LineChart`,`AreaChart`,`PieChart`
 FROM `wtkReports` WHERE `UID` = ?
SQLVAR;

    wtkSqlGetRow(wtkSqlPrep($pgSQL),[$gloRNG]);
    $pgRedirect = wtkSqlValue('URLredirect');
    if ($pgRedirect != ''):
        $pgRedirect .= '?apiKey=' . $pgApiKey;
        wtkRedirect($pgRedirect);
    endif;  // $pgRedirect != ''

    $pgHideFooter = wtkSqlValue('HideFooter');
    if ($pgHideFooter == 'Y'):
        $gloSkipFooter = true;
    endif;  // $pgHideFooter == 'Y'

    $pgDaysAgo = wtkSqlValue('DaysAgo');
    if ($pgDaysAgo != ''):
        $pgDaysAgoVal = wtkFilterRequest('wtkDaysAgo','rpt' . $gloRNG);
        if ($pgDaysAgoVal == ''):
            $pgDaysAgoVal = $pgDaysAgo;
        endif;  // $pgDaysAgoVal == ''
    endif;  // $pgDaysAgo != ''

    $pgSelTableName = wtkSqlValue('SelTableName');
    $pgShowGraph = wtkSqlValue('GraphRpt');
    // BEGIN  show Date Range fields if applicable for this report
    $pgSQL    = wtkSqlValue('RptSelect');
    $pgStartDatePrompt = wtkSqlValue('StartDatePrompt');
    $pgEndDatePrompt   = wtkSqlValue('EndDatePrompt');
    $pgFilterValue = wtkFilterRequest('RptFilter','rpt' . $gloRNG);
    $pgPosId = stripos($pgSQL, '@ID@');
    if ($pgPosId !== false):
        $pgHtm .= wtkFormHidden('id', $gloId);
    endif;
//  $pgHtm .= wtkFormHidden('Debug', 'Y'); // uncomment to debug

    if (($pgStartDatePrompt != '') || ($pgEndDatePrompt != '') || ($pgSelTableName != '') || ($pgDaysAgo != '')):
        $pgHtm .= '<form method="get" role="form" name="rptForm" id="rptForm">';
        $pgHtm .= wtkFormHidden('rng', $gloRNG);
        $pgHtm .= wtkFormHidden('apiKey', $pgApiKey);
        $pgHtm .= wtkFormHidden('Filter', 'Y');
        if ($pgShowGraph != 'Y'):
            $pgHtm .= wtkFormHidden('AJAX', 'Y');
        endif;
        $pgHtm .= '<div class="row">';
        // BEGIN Client Table Filtering
        if ($pgSelTableName != ''):
            $pgSelValueColumn = wtkSqlValue('SelValueColumn');
            $pgSelDisplayColumn = wtkSqlValue('SelDisplayColumn');
            $pgSelWhere = wtkSqlValue('SelWhere');
            $pgSelWhere = wtkReplace($pgSelWhere, '@UserUID@',$gloUserUID);
            if ($pgSelWhere != ''):
                if (substr(strtolower($pgSelWhere),0,6) != 'where '):
                    $pgSelWhere = 'WHERE ' . $pgSelWhere;
                endif;
            endif;
            if ($gloForceRO == true):
                if (($pgSelWhere == '') && ($pgFilterValue != '')):
                    $pgSelWhere = "WHERE `$pgSelValueColumn` = '$pgFilterValue'";
                endif;
                $pgSelSQL = "SELECT `$pgSelDisplayColumn` FROM `$pgSelTableName` $pgSelWhere LIMIT 1";
                $pgHtm .= '  <div class="input-field col m12 s12">' . "\n";
                $pgHtm .= '<p>Filtered by: ' . wtkSqlGetOneResult($pgSelSQL,[]) . '</p>' . "\n";
            else:
                $pgSelDisplayColumn = wtkReplace($pgSelDisplayColumn, '`', '');
                $pgSelSQL = "SELECT `$pgSelValueColumn`, `$pgSelDisplayColumn` FROM `$pgSelTableName` $pgSelWhere ORDER BY `$pgSelDisplayColumn` ASC";
                $pgHtm .= '  <div class="input-field col m4 s12">' . "\n";
                $pgHtm .= '    <select id="RptFilter" name="RptFilter">' . "\n";
                $pgHtm .= '      <option value="">Show All</option>' . "\n";
                $pgHtm .= wtkGetSelectOptions($pgSelSQL, [], $pgSelDisplayColumn, $pgSelValueColumn, $pgFilterValue);
                $pgHtm .= '    </select>' . "\n";
                $pgHtm .= '    <label for="RptFilter" class="active">Filter</label>' . "\n";
            endif; // not exporting
            $pgHtm .= '  </div>' . "\n";
        endif;
        //  END  Client Table Filtering
        if ($pgDaysAgo != ''):
            $pgTmp = wtkFormText('', 'DaysAgo', 'number', 'Days before', 'm3 s6');
            $pgTmp = wtkReplace($pgTmp, '<label for="','<label class="active" for="');
            $pgHtm .= $pgTmp;
            $pgStartVal = date('Y/m/d', time() - (60*60*24* $pgDaysAgo)); // Y-m-d to Y/m/d for JS fix on Date Picker
        endif;  // $pgDaysAgo != ''
        if ($pgStartDatePrompt != ''):
            $pgSubmitBtn = 'Filter by Date';
            $pgStartVal = wtkFilterRequest('wtkStartDate','rpt' . $gloRNG);
            if ($pgStartVal == ''):
                $pgStartVal = date('Y/m/d', strtotime('-1 month')); // Y-m-d to Y/m/d for JS fix on Date Picker
            else:
                $pgStartVal = date('Y/m/d', strtotime($pgStartVal));
            endif;  // $pgStartVal == ''
            if ($pgStartDatePrompt == $pgEndDatePrompt):
                $pgPrePrompt = 'From ';
            else:   // Not $pgStartDatePrompt == $pgEndDatePrompt
                $pgPrePrompt = '';
            endif;  // $pgStartDatePrompt == $pgEndDatePrompt
            if ($gloForceRO == true):
                $pgShowDate = date('M jS, Y', strtotime($pgStartVal));
                $pgHtm .=<<<htmVAR
    <div class="input-field col m6 s12">
        <input type="text" disabled="disabled" id="wtkStartDate" name="wtkStartDate" value="$pgShowDate">
        <label class="active" for="wtkStartDate">$pgPrePrompt $pgStartDatePrompt</label>
    </div>
htmVAR;
            else:
                $pgHtm .=<<<htmVAR
    <div class="input-field col m3 s6">
        <input type="text" class="datepicker" id="wtkStartDate" name="wtkStartDate" value="$pgStartVal">
        <label class="active" for="wtkStartDate">$pgPrePrompt $pgStartDatePrompt</label>
    </div>
htmVAR;
            endif;
        endif;  // $pgStartDatePrompt != ''
        if ($pgEndDatePrompt != ''):
            $pgSubmitBtn = 'Filter by Date';
            $pgEndVal = wtkFilterRequest('wtkEndDate','rpt' . $gloRNG);
            wtkTimeTrack('From wtkGetParam pgEndVal = ' . $pgEndVal);
            if ($pgEndVal == ''):
                $pgEndVal = date('Y/m/d', time()); // Y-m-d to Y/m/d for JS fix on Date Picker
            else:
                $pgEndVal = date('Y/m/d', strtotime($pgEndVal));
            endif;  // $pgEndVal == ''
            if ($pgStartDatePrompt == $pgEndDatePrompt):
                $pgPrePrompt = 'To ';
            else:   // Not $pgStartDatePrompt == $pgEndDatePrompt
                $pgPrePrompt = '';
            endif;  // $pgStartDatePrompt == $pgEndDatePrompt
            if ($gloForceRO == true):
                $pgShowDate = date('M jS, Y', strtotime($pgEndVal));
                $pgHtm .=<<<htmVAR
    <div class="input-field col m6 s12">
        <input type="text" disabled="disabled" id="wtkEndDate" name="wtkEndDate" value="$pgShowDate">
        <label class="active" for="wtkEndDate">$pgPrePrompt $pgEndDatePrompt</label>
    </div>
htmVAR;
            else:
                $pgHtm .=<<<htmVAR
    <div class="input-field col m3 s6">
        <input type="text" class="datepicker" id="wtkEndDate" name="wtkEndDate" value="$pgEndVal">
        <label class="active" for="wtkEndDate">$pgPrePrompt $pgEndDatePrompt</label>
    </div>
htmVAR;
            endif;
        endif;  // $pgEndDatePrompt != ''
        if ($gloForceRO != true):
            $pgHtm .= '  <div class="col m1 s4" style="padding-top: 9px">' . "\n";
            $pgHtm .= '<a onClick="Javascript:rptFilter()" class="btn btn-floating"><i class="material-icons">filter_list</i></a>';
            $pgHtm .= '  </div>' . "\n";
        endif;  // $gloForceRO != true
        $pgHtm .= '</div>';
        $pgHtm .= '</form>' . "\n";
        if ($pgSelTableName != ''):
            $pgHtm .= wtkFormHidden('HasSelect', 'Y');
        endif;
        if (($pgStartDatePrompt != '') || ($pgEndDatePrompt != '')):
            $pgHtm .= wtkFormHidden('HasDatePicker', 'Y');
        endif;
//        $gloForceRO = false;
        $gloFormMsg .= $pgHtm . "\n";
        if ($gloForceRO != true):
            $gloFormMsg .= '<hr>' . "\n";
        endif;
        $pgHtm = '';
    endif;  // ($pgStartDatePrompt != '') || ($pgEndDatePrompt != '')
    //  END   show Date Range fields if applicable for this report
    $gloPageTitle = wtkSqlValue('RptName');
    $pgRptNotes   = wtkSqlValue('RptNotes');
    if (($pgRptMode == 'Export') || ($pgRptMode == 'XML')):
        if ($gloFormMsg != ''):
            $gloFormMsg = '<p>' . $gloFormMsg . '</p>';
        endif;
    endif;  // $pgRptMode == 'Export'

    if (strtolower(substr($pgSQL, 0, 5)) == 'call '):  // if looks like a stored procedure call, then try this
        $gloRowsPerPage  = 400;
    endif;  // strtolower(substr($pgSQL, 0, 5)) == 'call '

    //   BEGIN  Client Custom Filtering based on Security Levels or Roles
    if (isset($gloUserSQLJoin)): // if User JOIN filtering
        $pgPosSqlInsert = stripos($pgSQL, ' WHERE ');
        if ($pgPosSqlInsert === false):
            $pgPosSqlInsert = stripos($pgSQL, "\n" . 'WHERE ');
            if ($pgPosSqlInsert === false):
                $pgPosSqlInsert = stripos($pgSQL, "\t" . 'WHERE ');
            endif;
        endif;
        if ($pgPosSqlInsert !== false):
            $pgTempSQL  = substr($pgSQL, 0, $pgPosSqlInsert);
            $pgTempSQL .= $gloUserSQLJoin . "\n";
            $pgSQLnewEnd = substr($pgSQL, ($pgPosSqlInsert + 1));
            $pgSQL = $pgTempSQL . ' ' . $pgSQLnewEnd;
        endif;
    endif;
    if (isset($gloUserSQLWhere)):
        $pgPosSqlInsert = stripos($pgSQL, ' WHERE ');
        if ($pgPosSqlInsert === false):
            $pgPosSqlInsert = stripos($pgSQL, ' WHERE ');
            if ($pgPosSqlInsert === false):
                $pgPosSqlInsert = stripos($pgSQL, "\n" . 'WHERE ');
                if ($pgPosSqlInsert === false):
                    $pgPosSqlInsert = stripos($pgSQL, "\t" . 'WHERE ');
                endif;
            endif;
        endif;
        if ($pgPosSqlInsert !== false):
            $pgPosSqlInsert = ($pgPosSqlInsert + 6);
            $pgTempSQL  = substr($pgSQL, 0, $pgPosSqlInsert);
            $pgTempSQL .= ' ' . $gloUserSQLWhere . "\n";
            $pgSQLnewEnd = substr($pgSQL, ($pgPosSqlInsert + 1));
            $pgSQL = $pgTempSQL . ' ' . $pgSQLnewEnd;
        endif;
    endif;  // ($gloUserSQLWhere exists)
    // BEGIN Client Filter logic
    if ($pgFilterValue != ''):
        $pgSQL = wtkReplace($pgSQL, '@RptFilter@', $pgFilterValue);
    else:
        $pgSQL = wtkReplace($pgSQL, '= @RptFilter@', '<> -1');
        $pgSQL = wtkReplace($pgSQL, "= '@RptFilter@'", "<> '~wtk~'");
    endif;
    //   END  Client Filter logic
    //  BEGIN  DaysAgo logic for Historical comparison to prior year
    if ($pgDaysAgo != ''):
        $pgSQL = wtkReplace($pgSQL, '@DaysPast@', $pgDaysAgoVal);
        $pgSQL = wtkReplace($pgSQL, '@StartDate@', wtkFormatDateTime('Y-m-d', $pgStartVal));  //
        $gloFormMsg = wtkReplace($gloFormMsg, '@DaysPast@', $pgDaysAgoVal);
        $pgRptNotes = wtkReplace($pgRptNotes, '@DaysPast@', $pgDaysAgoVal);
    else:   // Not $pgDaysAgo != ''
    //   END   DaysAgo logic for Historical comparison to prior year
        //  BEGIN  User-driven date range filtering
        if (($pgStartDatePrompt != '') && ($pgStartVal != '')):
            $pgSQL = wtkReplace($pgSQL, '@StartDate@', wtkFormatDateTime('Y-m-d', $pgStartVal));  //
        endif;  // $pgStartVal != ''
        if (($pgEndDatePrompt != '') && ($pgEndVal != '')):
            $pgSQL = wtkReplace($pgSQL, '@EndDate@', wtkFormatDateTime('Y-m-d', $pgEndVal));  //
        endif;  // ($pgEndDatePrompt != '') && ($pgEndVal != '')
    endif;  // $pgDaysAgo != ''
    //   END   User-driven date range filtering
    //   BEGIN  wtkSetHeaderSort
    $pgSortableCols = wtkSqlValue('SortableCols');
    if ($pgSortableCols != ''):
        $pgSortableArray = explode("\n", $pgSortableCols);
        foreach ($pgSortableArray as $pgHeaders):
            $pgHeaderArray = explode(",", $pgHeaders);
            if (isset($pgHeaderArray[2])):
                wtkSetHeaderSort(trim($pgHeaderArray[0]), trim($pgHeaderArray[1]), trim($pgHeaderArray[2]));
            elseif (isset($pgHeaderArray[1])):
                wtkSetHeaderSort(trim($pgHeaderArray[0]), trim($pgHeaderArray[1]));
            else:
                wtkSetHeaderSort(trim($pgHeaderArray[0]), trim($pgHeaderArray[0]));
            endif;  // isset($pgHeaderArray[2])
        endforeach;
    endif;  // $pgSortableCols != ''
    //    END   wtkSetHeaderSort
    //   BEGIN  Suppress Columns
    $pgFieldSuppress = wtkSqlValue('FieldSuppress');
    if ($pgFieldSuppress != ''):
        $pgSuppressArray = explode(",", $pgFieldSuppress);
        foreach ($pgSuppressArray as $pgFieldToSuppress):
            $gloSuppressColumnArray[] = trim($pgFieldToSuppress);
        endforeach;
    endif;  // $pgFieldSuppress != ''
    //    END   Suppress Columns
    //   BEGIN  Align Columns
    $pgAlignCenter = wtkSqlValue('AlignCenter');
    $pgAlignCenter = wtkReplace($pgAlignCenter, ' ,',',');
    $pgAlignCenter = wtkReplace($pgAlignCenter, ', ',',');
    $pgAlignRight = wtkSqlValue('AlignRight');
    $pgAlignRight = wtkReplace($pgAlignRight, ' ,',',');
    $pgAlignRight = wtkReplace($pgAlignRight, ', ',',');
    if ($pgAlignCenter != ''):
        $pgCenterArray = explode(',', $pgAlignCenter);
        if ($pgAlignRight == ''):
            $gloColumnAlignArray = array_fill_keys($pgCenterArray, 'center');
        else:   // Not $pgAlignRight == ''
            $pgCenterArray = array_fill_keys($pgCenterArray, 'center');
        endif;  // $pgAlignRight == ''
    endif;  // $pgAlignCenter != ''

    if ($pgAlignRight != ''):
        $pgRightArray = explode(',', $pgAlignRight);
        if ($pgAlignCenter == ''):
            $gloColumnAlignArray = array_fill_keys($pgRightArray, 'right');
        else:   // Not $pgAlignCenter == ''
            $pgRightArray = array_fill_keys($pgRightArray, 'right');
            $gloColumnAlignArray = array_merge($pgRightArray, $pgCenterArray);
        endif;  // $pgAlignCenter == ''
    endif;  // $pgAlignRight != ''
    //    END   Align Columns
    // BEGIN  Totaling Columns
    $pgTotalCols = wtkSqlValue('TotalCols');
    $pgTotalCols = wtkReplace($pgTotalCols, ' ,',',');
    $pgTotalCols = wtkReplace($pgTotalCols, ', ',',');

    $pgTotalMoneyCols = wtkSqlValue('TotalMoneyCols');  // ABS 11/08/15
    $pgTotalMoneyCols = wtkReplace($pgTotalMoneyCols, ' ,',',');
    $pgTotalMoneyCols = wtkReplace($pgTotalMoneyCols, ', ',',');
    if ($pgTotalCols != ''):
        $pgTotalArray = explode(',', $pgTotalCols);
        if ($pgTotalMoneyCols == ''):
            $gloTotalArray = array_fill_keys($pgTotalArray, 'SUM');  // 2ENHANCE later may pass AVE or other math functions
        else:   // Not $pgTotalMoneyCols == ''
            $pgTotalSumArray = array_fill_keys($pgTotalArray, 'SUM');  // 2ENHANCE later may pass AVE or other math functions
        endif;  // $pgTotalMoneyCols == ''
    endif;  // $pgAlignCenter != ''
    if ($pgTotalMoneyCols != ''):
        $pgTotalArray = explode(',', $pgTotalMoneyCols);
        if ($pgTotalCols == ''):
            $gloTotalArray = array_fill_keys($pgTotalArray, 'DSUM');
        else:   // Not $pgTotalCols == ''
            $pgTotalSum2Array = array_fill_keys($pgTotalArray, 'DSUM');
            $gloTotalArray = array_merge($pgTotalSumArray, $pgTotalSum2Array);
        endif;  // $pgTotalCols == ''
    endif;  // $pgAlignCenter != ''
    //  END   Totaling Columns
    //   BEGIN  Add and Edit buttons
    $pgAddLink  = wtkSqlValue('AddLink');
    if ($pgAddLink != ''):
        $gloAddPage  = $pgAddLink;
    endif;  // $pgAddLink != ''
    $pgEditLink = wtkSqlValue('EditLink');
    if ($pgEditLink != ''):
        $gloEditPage = $pgEditLink;
    endif;  // $pgEditLink != ''
//    END   Add and Edit buttons

    $pgTestMode = wtkSqlValue('TestMode');
    if ($pgTestMode == 'Y'):
        $gloFormMsg .=  "\n" . '<code>' . "\n" . nl2br($pgSQL) . '</code><hr>' . "\n";
        $gloTrackTime = true;
    endif;  // wtkSqlValue('TestMode') == 'Y'
    $pgSqlFilter = array();
    $pgCanExport = wtkSqlGetOneResult('SELECT `CanExport` FROM `wtkUsers` WHERE `UID` = ?',[$gloUserUID]);
    $pgHtm  = '<h5 id="RptTitle0">' . $gloPageTitle ;
    if ($pgCanExport == 'Y'):
        $pgHtm .= ' <small class="right">' . "\n";
        $pgHtm .= ' <a target="_blank" href="/wtk/reports.php?Mode=Export';
        $pgHtm .= '&rng=' . $gloRNG . '&apiKey=' . $pgApiKey . '"><i class="material-icons">file_download</i></a>' . "\n";
        $pgHtm .= ' <a target="_blank" href="/wtk/reports.php?Mode=XML';
        $pgHtm .= '&rng=' . $gloRNG . '&apiKey=' . $pgApiKey . '">XML</a>' . "\n";
        $pgHtm .= '</small>' . "\n";
    endif;
    $pgHtm .= '</h5>' . "\n";
    if ($pgRptNotes != ''):
        $pgHtm .= '<br><p>' . $pgRptNotes . '</p>' . "\n";
    endif;
    $pgHtm .= '<hr>' . "\n";
    $pgHtm .= $gloFormMsg . "\n";
    if ($pgShowGraph == 'Y'):
        $pgHtm .= wtkFormHidden('HasTabs', 'Y');
    endif;
//    $pgHtm .= wtkFormHidden('pgDebugVar', 'Y');
    if (wtkGetParam('AJAX') != 'Y'):
        $pgInsSQL = 'INSERT INTO `wtkReportCntr` (`RptUID`, `RptType`, `RptURL`, `UserUID`)';
        $pgInsSQL .= ' VALUES(:RptUID, :RptType, :RptURL, :UserUID)';
        $pgCurPage = substr($gloCurrentPage, 0, 40);
        $fncSqlFilter = array (
            'RptUID' => $gloRNG,
            'RptType' => $pgRptType,
            'RptURL' => $pgCurPage,
            'UserUID' => $gloUserUID
        );
        wtkSqlExec($pgInsSQL, $fncSqlFilter);
    endif;
    $pgSQL = wtkReplace($pgSQL, '@UserUID@', $gloUserUID);
    if ($gloId == 0):
        $pgSQL = wtkReplace($pgSQL, '= @ID@', '<> 0');
    else:
        $pgSQL = wtkReplace($pgSQL, '@ID@', $gloId);
    endif;
    switch ($pgRptMode):
        case 'Export':
            wtkBrowseExport($pgSQL, $pgSqlFilter, 'fRpt' . $gloRNG);
            break;
        case 'XML':
            wtkBrowseExportXML($pgSQL, $pgSqlFilter, 'fRpt' . $gloRNG);
            break;
        default:
            $pgStart  = '<div class="container">' . "\n";
            $pgStart .= '   <br><div class="wtk-box">' . "\n";
            if ($gloId == 99999): // put in Container
                $pgStart .= '<div class="row">' . "\n";
                $pgStart .= '   <div class="col s12">' . "\n";
            endif;
            $pgHtm  = $pgStart . $pgHtm;
            $pgHtm .= '    <div id="rptSpanFltr">' . "\n";
            if ($pgShowGraph == 'Y'):
                if (wtkGetParam('Filter') == 'Y'):
                    $pgHtm = '';
                endif;
                if (wtkGetParam('NP') == 'Y'):
                    $gloSiteDesign = 'MPA';
                endif;
                $gloSuppressChartArray[] = 'ID';
                $gloSuppressChartArray[] = 'UID';
                $gloSuppressChartArray[] = 'GUID';
                $pgChartSuppress = wtkSqlValue('ChartSuppress');
                if ($pgChartSuppress != ''):
                    $pgChartSuppressArray = explode(",", $pgChartSuppress);
                    foreach ($pgChartSuppressArray as $pgFieldToSuppress):
                        $gloSuppressChartArray[] = trim($pgFieldToSuppress);
                    endforeach;
                endif;  // $pgChartSuppress != ''
                $pgRegRpt   = wtkSqlValue('RegRpt');
                $pgBarChart = wtkSqlValue('BarChart');
                $pgLineChart = wtkSqlValue('LineChart');
                $pgAreaChart = wtkSqlValue('AreaChart');
                $pgPieChart = wtkSqlValue('PieChart');

//                $pgChartOps = array('regRpt','pie','bar','line');
                $pgChartOps = array();
                if ($pgRegRpt == 'Y'):
                    $pgChartOps[] = 'regRpt';
                endif;
                if ($pgBarChart == 'Y'):
                    $pgChartOps[] = 'bar';
                endif;
                if ($pgLineChart == 'Y'):
                    $pgChartOps[] = 'line';
                endif;
                if ($pgAreaChart == 'Y'):
                    $pgChartOps[] = 'area';
                endif;
                if ($pgPieChart == 'Y'):
                    $pgChartOps[] = 'pie';
                endif;
                $pgHtm .= wtkRptChart($pgSQL, $pgSqlFilter, $pgChartOps);
            else:
                if ($pgHideHeader == true):
                    $pgHtm = '';
                endif;
                $pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'fRpt' . $gloRNG);
            endif;
            if (wtkGetParam('Filter') == 'Y'):
                $pgHtm .= '    </div>' . "\n";
                if ($gloId == 99999): // end of Container
                    $pgHtm .= '   </div>' . "\n";
                    $pgHtm .= '</div>' . "\n";
                endif;
                $pgHtm .= '</div>' . "\n";
            endif;
            $pgHtm .= '<br></div>' . "\n";
            if (wtkGetParam('NP') == 'Y'):
                wtkMergePage($pgHtm, 'Report', 'htm/report.htm');
            endif;
    endswitch;
endif;  // $pgRptUID == ''
//  END   if report selected then show report

echo $pgHtm;
?>
