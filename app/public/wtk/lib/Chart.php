<?PHP
/**
* Chart functions for Wizard's Toolkit
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2024, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/
// 2FIX need to change ADO to PDO
$gloXLabels = '';
$gloLineOneArray = '';
$gloLineTwoArray = '';
$gloLineThreeArray = '';
$gloLineFourArray  = '';
$gloSuppressChartArray = array();

/**
* Graph Values for Chart.js and MaterializeCSS
*
* This can be used to retrieve proper syntax for Chart.js Charts.
*
* Calling method:  $pgGraph = wtkGraphValues($pgSQL, $pgSqlFilter);
*
* @param string $fncSQL  SQL SELECT
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string $fncDateFormat If first column is a date you can pass in the format you want the date displayed
* @return array ($fncList, $fncResult)
*/
function wtkGraphValues($fncSQL, $fncSqlFilter, $fncDateFormat = '') {
    global $gloWTKobjConn, $gloSuppressChartArray;
    // // 2ENHANCE: Stored Procedure instead of SELECT
    $fncSQL = wtkSqlPrep($fncSQL);
    $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    $fncPDO->execute($fncSqlFilter);
    $fncColCount = $fncPDO->columnCount();
    $fncLabels = '';
    $fncResult = array();
    $fncCntr = 0;
    while ($fncRow = $fncPDO->fetch(PDO::FETCH_NUM)):
        $fncCntr ++;
        if ($fncCntr == 1):
            for ($i=1; $i < $fncColCount; $i++) {
                $fncTmpArray = $fncPDO->getColumnMeta($i);
                if (!wtkColSuppressed($fncTmpArray['name'])):
                    if (!in_array($fncTmpArray['name'], $gloSuppressChartArray)):
                        $fncResult[$fncTmpArray['name']] = '';
                    endif;
                endif;
            }
        else:
            $fncLabels .= ',';
            for ($i=1; $i < $fncColCount; $i++) {
                $fncTmpArray = $fncPDO->getColumnMeta($i);
                if (!wtkColSuppressed($fncTmpArray['name'])):
                    if (!in_array($fncTmpArray['name'], $gloSuppressChartArray)):
                        $fncResult[$fncTmpArray['name']] .= ',';
                    endif;
                endif;
            }
        endif;
        $fncTmp = wtkReplace($fncRow[0], "'",'`');
        $fncTmp = wtkReplace($fncTmp, '>delete<','><');
        $fncTmp = wtkReplace($fncTmp, '&nbsp;',' ');
        $fncTmp = wtkRemoveStyle($fncTmp);
        if ($fncDateFormat != ''):
            $fncTmp = date($fncDateFormat, strtotime($fncTmp));
        endif;
        $fncLabels .= "'" . $fncTmp . "'";
        for ($i=1; $i < $fncColCount; $i++) {
            $fncTmpArray = $fncPDO->getColumnMeta($i);
            if (!wtkColSuppressed($fncTmpArray['name'])):
                if (!in_array($fncTmpArray['name'], $gloSuppressChartArray)):
                    $fncVal = wtkParseCurrencyToNumber($fncRow[$i]);
                    $fncResult[$fncTmpArray['name']] .= $fncVal;
                endif;
            endif;
        }
    endwhile;
    unset($fncPDO);
    // if ($fncResult == ''):
    //     $fncResult = "['No Data', 'no data']" . "\n";
    // else:
    //     $fncResult .= "\n";
    // endif;
    return array ($fncLabels, $fncResult);
}// wtkGraphValues

/**
* Single line call to generate multiple charts for a single set of data.
*
* This calls wtkBuildDataBrowse to generate a list of data and wtkGraphValues to
* make charts in conjunction with HTML and JS as defined in wtk/htm/chartJS.htm.
*
* Calling example:<br>
* $pgChartOps = array('regRpt','pie','bar','line');<br>
* $pgHtm .= wtkRptChart($pgSQL, $pgSqlFilter, $pgRpt . 'Rpt', '', $pgChartOps, $pgRptNum);
*
* @param string $fncSQL  SQL SELECT
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string $fncRptId only need to pass if have more than one report on page
* @param string $fncDateFormat If first column is a date you can pass in the format you want the date displayed
* @param array  $fncChartOps If not passed then all chart types will be shown; this allows choosing which charts to show.
* @param string $fncChartNum defaults to 0 and is used if more than one chart on page
* @uses function wtkBuildDataBrowse
* @uses function wtkGraphValues
* @uses html wtk/htm/chartJS.htm
* @return html and charts
*/
$pgHasTabs = false;
function wtkRptChart($fncSQL, $fncSqlFilter, $fncRptId = 'wtkRpt1', $fncDateFormat = '', $fncChartOps = [], $fncChartNum = 0, $fncCurrency = 'N'){
    // pass # to $fncChartNum if more than one chart on a page
    global $gloDeviceType, $gloSiteDesign, $pgHasTabs;
    $fncOpsCnt = count($fncChartOps);
    if (in_array('regRpt', $fncChartOps) || ($fncOpsCnt == 0)):
        $fncRegRpt = wtkBuildDataBrowse($fncSQL, $fncSqlFilter, $fncRptId, '', 'N', $fncDateFormat);
    else:
        $fncRegRpt = '';
    endif;
    $fncChart = wtkLoadInclude(_RootPATH . 'wtk/htm/chartJS.htm');
    if ($fncCurrency == 'Y'):
        $fncTmp =<<<htmVAR
options: {
    plugins: {
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';

                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },responsive: true
htmVAR;
        $fncChart = wtkReplace($fncChart,'options: {responsive: true', $fncTmp);
    endif;
    if ($gloSiteDesign == 'MPA'):
        $fncChart = wtkReplace($fncChart, '<script type="text/javascript">',
                '<script type="text/javascript">' . "\n" . 'function wtkMPAstart(){' . "\n");
        $fncChart = wtkReplace($fncChart, '</script>','}' . "\n" . '</script>');
    endif;
    $fncChart = wtkReplace($fncChart, 'wtkChartLabels','wtk' . $fncChartNum . 'ChartLabels');
    $fncChart = wtkReplace($fncChart, 'wtkBar','wtk' . $fncChartNum . 'Bar');
    $fncChart = wtkReplace($fncChart, 'wtkLine','wtk' . $fncChartNum . 'Line');
    $fncChart = wtkReplace($fncChart, 'wtkArea','wtk' . $fncChartNum . 'Area');
    $fncChart = wtkReplace($fncChart, 'wtkPie','wtk' . $fncChartNum . 'Pie');

    if ($fncOpsCnt == 0):
        $fncFirstTab = 'regRpt';
        $fncTabs =<<<htmVAR
<li class="tab col s3"><a class="active" onclick="Javascript:changeChart('regRpt', 'nada', $fncChartNum)">Report</a></li>
    <li class="tab col s2"><a onclick="Javascript:changeChart('bar', 'barCanvas$fncChartNum', $fncChartNum )">Bar Chart</a></li>
    <li class="tab col s2"><a onclick="Javascript:changeChart('line', 'lineCanvas$fncChartNum', $fncChartNum )">Line Chart</a></li>
    <li class="tab col s2"><a onclick="Javascript:changeChart('area', 'areaCanvas$fncChartNum', $fncChartNum )">Area Chart</a></li>
    <li class="tab col s2"><a onclick="Javascript:changeChart('pie', 'pieCanvas$fncChartNum', $fncChartNum )">Pie Chart</a></li>
htmVAR;
        $fncCanvas =<<<htmVAR
    <div class="col s12" id="regRpt0">
        $fncRegRpt
    </div>
    <div class="hide col s12" id="barChart$fncChartNum"><canvas id="barCanvas$fncChartNum"></canvas></div>
    <div class="hide col s12" id="lineChart$fncChartNum"><canvas id="lineCanvas$fncChartNum"></canvas></div>
    <div class="hide col s12" id="areaChart$fncChartNum"><canvas id="areaCanvas$fncChartNum"></canvas></div>
    <div class="hide col s12" id="pieChart$fncChartNum">
        <a onclick="JavaScript:wtkRemovePie($fncChartNum)" class="btn">Remove Dataset</a>
        <a onclick="JavaScript:togglePieDoughnut($fncChartNum)" class="btn">Toggle Doughnut View</a>
        <canvas id="pieCanvas$fncChartNum"></canvas>
    </div>
htmVAR;
    else:
        switch ($fncOpsCnt):
            case 5:
                $fncSmCol = 2;
                break;
            case 4:
                $fncSmCol = 3;
                break;
            case 3:
                $fncSmCol = 4;
                break;
            case 2:
                $fncSmCol = 6;
                break;
            default:
                $fncSmCol = 12;
                break;
        endswitch;
        $fncTabs = '';
        $fncCanvas = '';
        $fncCntr = 0;
        foreach ($fncChartOps as $fncTmp):
            $fncCntr ++;
            if (($gloDeviceType != 'phone') || ($fncTmp != 'area')): // skip Area Charts on Phones; 2ENHANCE later show if Line Chart is excluded
                $fncTabs .= '<li class="tab col s';
                if (($fncCntr == 1) && ($fncSmCol == 2)):
                    $fncTabs .= '3';
                else:
                    $fncTabs .= $fncSmCol;
                endif;
                $fncTabs .= '"><a';
                $fncCanvas .= '<div class="';
                if ($fncCntr == 1):
                    $fncTabs .= ' class="active"';
                    $fncFirstTab = $fncTmp;
                else:
                    $fncCanvas .= 'hide ';
                endif;
                $fncCanvas .= 'col s12" id="' . $fncTmp;
                if ($fncTmp == 'regRpt'):
                    $fncCanvas .= $fncChartNum . '">' . $fncRegRpt . "\n";
                else:
                    $fncCanvas .= 'Chart' . $fncChartNum . '">';
                    if ($fncTmp == 'pie'):
                        $fncCanvas .= '<a onclick="JavaScript:wtkRemovePie(' . $fncChartNum . ')" class="btn">Remove Dataset</a>' . "\n";
                        $fncCanvas .= '<a onclick="JavaScript:togglePieDoughnut(' . $fncChartNum . ')" class="btn">Toggle Doughnut View</a>' . "\n";
                    endif;
                    $fncCanvas .= '<canvas id="' . $fncTmp . 'Canvas' . $fncChartNum . '"></canvas>' . "\n";
                endif;
                $fncCanvas .= '</div>' . "\n";
                $fncTabs .= " onclick=\"Javascript:changeChart('$fncTmp', '$fncTmp" . "Canvas$fncChartNum', $fncChartNum)\">";
                if ($fncTmp == 'regRpt'):
                    $fncTabs .= 'Report';
                else:
                    $fncTabs .= ucwords($fncTmp) . ' Chart';
                endif;
                $fncTabs .= '</a></li>' . "\n";
            endif; // skip Area Charts on Phones
        endforeach;
    endif; // $fncOpsCnt != 0
    if ($fncOpsCnt == 1):
        $fncChartTabs = $fncCanvas;
    else:
        $fncChartTabs =<<<htmVAR
    <div class="wtk-box">
        <div id="RptTitle$fncChartNum">
            <ul class="tabs rpt-tabs" id="wtkRptTab$fncChartNum">
                $fncTabs
            </ul>
        </div>
        <div class="row">
        $fncCanvas
        </div>
    </div>
htmVAR;
    endif;
    if ($gloDeviceType == 'phone'):
        $fncChartTabs = wtkReplace($fncChartTabs,' Chart</', '</');
    endif;
    $fncChartTabs = wtkReplace($fncChartTabs, 'Chart" class="hide', $fncChartNum . 'Chart" class="hide');
    $fncHtm  = $fncChartTabs . "\n";
    if ($pgHasTabs == false):
        $pgHasTabs = true;
        $fncHtm .= wtkFormHidden('HasTabs', 'Y');
    endif;
    list ($fncLabels, $fncResult) = wtkGraphValues($fncSQL, $fncSqlFilter, $fncDateFormat);
    $fncChart = wtkReplace($fncChart,'@chartLabels@', $fncLabels);
    $fncChartData = '';
    $fncPieData = '';
    $fncCntr = 0;
    foreach($fncResult as $fncKey => $fncValues):
        if ($fncCntr > 0):
            $fncChartData .= ',';
            $fncPieData .= ',';
        endif;
        $fncCntr ++;
        $fncLabel = wtkInsertSpaces($fncKey);
        switch ($fncCntr):
            case 1:
                $fncColor = 'blue';
                break;
            case 2:
                $fncColor = 'red';
                break;
            case 3:
                $fncColor = 'green';
                break;
            case 4:
                $fncColor = 'purple';
                break;
            case 5:
                $fncColor = 'orange';
                break;
            default:
                $fncColor = 'cyan';
                break;
        endswitch;
        $fncPieData .=<<<htmVAR
{
label: '$fncLabel',
data: [$fncValues],
backgroundColor: wtkPieSliceColors
}
htmVAR;
        $fncChartData .=<<<htmVAR
{
label: '$fncLabel',
fill: true,
data: [$fncValues],
borderColor: wtkColor.$fncColor,
backgroundColor: chartColor(wtkColor.$fncColor).alpha(0.5).rgbString()
}
htmVAR;
    endforeach;
    $fncStartChart = '';
    if ($fncFirstTab != 'regRpt'):
        $fncStartChart = "\n" . "changeChart('$fncFirstTab', '$fncFirstTab" . "Canvas$fncChartNum', $fncChartNum);";
    endif;
    $fncChart = wtkReplace($fncChart,'@wtk' . $fncChartNum . 'PieData@', $fncPieData);
    $fncChart = wtkReplace($fncChart,'@wtk' . $fncChartNum . 'BarData@', $fncChartData);
    $fncLineData = wtkReplace($fncChartData,'alpha(0.5)', 'alpha(0.2)');
    $fncChart = wtkReplace($fncChart,'@wtk' . $fncChartNum . 'AreaData@', $fncLineData);
    $fncLineData = wtkReplace($fncLineData,'fill: true,', 'fill: false,');
    $fncChart = wtkReplace($fncChart,'@wtk' . $fncChartNum . 'LineData@', $fncLineData);
    $fncChart = wtkReplace($fncChart,"gloLastTab[0] = 'regRpt';", "gloLastTab[$fncChartNum] = 'regRpt$fncChartNum';");
    $fncChart = wtkReplace($fncChart,'gloChartExists[0] = false;', "gloChartExists[$fncChartNum] = false;" . $fncStartChart);
    $fncHtm .= $fncChart . "\n";
    return $fncHtm;
} // wtkRptChart

/*
// obsolete function
$gloChartMaxValue = 0;
$gloChartTotal = 0;
function wtkGraphChart($fncSQL, $fncLines = 2) {
    // 1 Line charts need to return values in this format:
    //   [["Cats","6"],["Dogs","14"],["Rabbits","4"]]
    global $gloWTKobjConn, $gloXLabels, $gloChartTotal, $gloChartMaxValue,
    $gloLineOneArray, $gloLineTwoArray, $gloLineThreeArray, $gloLineFourArray;
    $gloXLabels = '[No Data]';
    $gloLineOneArray = '[10]';
    $fncWTKobjRS = $gloWTKobjConn->Execute($fncSQL);
    if ($fncWTKobjRS->MoveFirst() == false):
        $fncWTKobjRS->Close();
    elseif($fncWTKobjRS):
        $gloChartTotal = 0;
        $fncChartCntr = 1;
        $gloXLabels = '[';
        $gloLineOneArray = '[';
        $gloLineTwoArray   = '[';
        $gloLineThreeArray = '[';
        $gloLineFourArray  = '[';
        $fncWTKobjRS->MoveFirst();
        while (!$fncWTKobjRS->EOF):
            if ($fncChartCntr > 1):
                $gloXLabels .= ',';
                $gloLineOneArray .= ',';
            endif;  // $fncChartCntr > 1
            $gloXLabels .= '[' . $fncChartCntr . ',"' . $fncWTKobjRS->fields(0) . '"]';
            // Label like State, Gender, etc.  (not used for 1 line charts)
            switch ($fncLines):
                case 1 :
                    $gloLineOneArray   .= '["' . $fncWTKobjRS->fields(0) . '",' . intval($fncWTKobjRS->fields(1)) . ']';
                    $gloChartTotal = ($gloChartTotal + intval($fncWTKobjRS->fields(1)));
                    break;
                case 2 :
                    $gloLineOneArray   .= intval($fncWTKobjRS->fields(1));
                    if ($fncChartCntr > 1):
                        $gloLineTwoArray .= ',';
                    endif;  // $fncChartCntr > 1
                    $gloLineTwoArray   .= intval($fncWTKobjRS->fields(2));
                    break;
                case 3 :
                    $gloLineOneArray   .= intval($fncWTKobjRS->fields(1));
                    if ($fncChartCntr > 1):
                        $gloLineTwoArray   .= ',';
                        $gloLineThreeArray .= ',';
                    endif;  // $fncChartCntr > 1
                    $gloLineTwoArray   .= intval($fncWTKobjRS->fields(2));
                    $gloLineThreeArray .= intval($fncWTKobjRS->fields(3));
                    break;
                case 4 :
                    $gloLineOneArray   .= intval($fncWTKobjRS->fields(1));
                    if ($fncChartCntr > 1):
                        $gloLineTwoArray   .= ',';
                        $gloLineThreeArray .= ',';
                        $gloLineFourArray  .= ',';
                    endif;  // $fncChartCntr > 1
                    $gloLineTwoArray   .= intval($fncWTKobjRS->fields(2));
                    $gloLineThreeArray .= intval($fncWTKobjRS->fields(3));
                    $gloLineFourArray  .= intval($fncWTKobjRS->fields(4));
                    break;
            endswitch; // fncLines
            $fncCount = intval($fncWTKobjRS->fields(1));
            if ($gloChartMaxValue < $fncCount):
                $gloChartMaxValue = $fncCount;
            endif;  // $gloChartMaxValue < $fncCount
            $fncWTKobjRS->MoveNext();
            $fncChartCntr += 1;
        endwhile;
        $gloXLabels .= ']';
        $gloLineOneArray .= ']';
        $gloLineTwoArray   .= ']';
        $gloLineThreeArray .= ']';
        $gloLineFourArray  .= ']';
    endif;
    $fncWTKobjRS->Close();
    unset($fncWTKobjRS);
    // BEGIN increment by 1 if max * 1.1 is same as max
    $fncChartMaxValue = number_format($gloChartMaxValue);
    $gloChartMaxValue = number_format($gloChartMaxValue * 1.1);
    if ($fncChartMaxValue == $gloChartMaxValue):
        $gloChartMaxValue += 1;
    endif;  // $fncChartMaxValue == $gloChartMaxValue
    //  END  increment by 1 if max * 1.1 is same as max
}// wtkGraphChart

// Next function needs to be changed to PDO
function wtkMapGraph($fncSQL, $fncSkipVendors = 'N'){;
    global $gloWTKobjConn;
    if ($fncSkipVendors == 'Y'):
        $fncResult = "['Region', 'Dog Count', 'StateCode'],";
    else:
        $fncResult = "['Region', 'Cat Count', 'Dog Count', 'StateCode'],";
    endif;
    // ABS 08/09/18  BEGIN if Stored Procedure then Connect instead of PConnect
    if (substr(strtolower($fncSQL),0,5) == 'call '):
        global $gloDriver1, $gloServer1,$gloUser1,$gloPassword1,$gloDb1;
        $fncConn = ADONewConnection($gloDriver1);
        $fncConn->clientFlags = 131074;      // critical for stored procedures (at least for MySQL)
        $fncConn->Connect($gloServer1,$gloUser1,$gloPassword1,$gloDb1);  // Note, not PConnect
        $fncConn->SetFetchMode(ADODB_FETCH_ASSOC); // probably critical for stored procedures (at least for MySQL)
        $fncWTKobjRS = $fncConn->Execute($fncSQL);
    else:
        $fncWTKobjRS = $gloWTKobjConn->Execute($fncSQL);
    endif;
    // ABS 08/09/18   END  if Stored Procedure then Connect instead of PConnect
    if ($fncWTKobjRS->MoveFirst() == false):
        $fncWTKobjRS->Close();
        wtkMergePage('No Data', 'Chart');
    endif;
    if ($fncWTKobjRS) :
        $fncWTKobjRS->MoveFirst();
        $fncCntr = 0;
        while (!$fncWTKobjRS->EOF):
            $fncCntr ++;
            if ($fncCntr > 1):
                $fncResult .= ',' . "\n";
            endif;
            $fncResult .= "['" . $fncWTKobjRS->fields('StateName') . "',";
            if ($fncSkipVendors == 'N'): // ABS 01/15/20
                $fncResult .= $fncWTKobjRS->fields('House') . ", ";
            endif;
            $fncResult .= $fncWTKobjRS->fields('Dogs') . ", ";
            $fncResult .= "'" . $fncWTKobjRS->fields('StateCode') . "']";
            $fncWTKobjRS->MoveNext();
        endwhile;
    endif;
    $fncWTKobjRS->Close();
    unset($fncWTKobjRS);
    return $fncResult;
}// end function wtkMapGraph
*/
?>
