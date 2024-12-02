var chartColor = Chart.helpers.color;
var gloChartExists = new Array();
var gloLastTab = new Array();

function wtkRemovePie(fncChartNum) {
    switch (fncChartNum) {
        case 0:
            wtk0PieConfig.data.datasets.splice(0, 1);
            wtk0Chart.update();
            break;
        case 1:
            wtk1PieConfig.data.datasets.splice(0, 1);
            wtk1Chart.update();
            break;
        case 2:
            wtk2PieConfig.data.datasets.splice(0, 1);
            wtk2Chart.update();
            break;
        case 3:
            wtk3PieConfig.data.datasets.splice(0, 1);
            wtk3Chart.update();
            break;
        case 4:
            wtk4PieConfig.data.datasets.splice(0, 1);
            wtk4Chart.update();
            break;
        case 5:
            wtk5PieConfig.data.datasets.splice(0, 1);
            wtk5Chart.update();
            break;
    }
}

function togglePieDoughnut(fncChartNum) {
    switch (fncChartNum) {
        case 0:
            if (wtk0Chart.options.cutout) {
                wtk0Chart.options.cutout = 0;
            } else {
                wtk0Chart.options.cutout = '40%';
            }
            wtk0Chart.update();
            break;
        case 1:
            if (wtk1Chart.options.cutout) {
                wtk1Chart.options.cutout = 0;
            } else {
                wtk1Chart.options.cutout = '40%';
            }
            wtk1Chart.update();
            break;
        case 2:
            if (wtk2Chart.options.cutout) {
                wtk2Chart.options.cutout = 0;
            } else {
                wtk2Chart.options.cutout = '40%';
            }
            wtk2Chart.update();
            break;
        case 3:
            if (wtk3Chart.options.cutout) {
                wtk3Chart.options.cutout = 0;
            } else {
                wtk3Chart.options.cutout = '40%';
            }
            wtk3Chart.update();
            break;
        case 4:
            if (wtk4Chart.options.cutout) {
                wtk4Chart.options.cutout = 0;
            } else {
                wtk4Chart.options.cutout = '40%';
            }
            wtk4Chart.update();
            break;
        case 5:
            if (wtk5Chart.options.cutout) {
                wtk5Chart.options.cutout = 0;
            } else {
                wtk5Chart.options.cutout = '40%';
            }
            wtk5Chart.update();
            break;
    }
}

function changeChart(fncChartType, fncCanvas, fncChartNum) {
    if (elementExist('wtkRptTab' + fncChartNum)) {
        let fncId = document.getElementById('wtkRptTab' + fncChartNum);
        var fncTab = M.Tabs.getInstance(fncId);
    }
    wtkDebugLog('changeChart: fncChartType = ' + fncChartType + '; fncCanvas = ' + fncCanvas + '; fncChartNum = ' + fncChartNum);

    if (fncChartType == 'regRpt') {
        $('#' + gloLastTab[fncChartNum]).addClass('hide');
        $('#regRpt' + fncChartNum).removeClass('hide');
        fncTab.select('regRpt' + fncChartNum);
        fncTab.updateTabIndicator();
        gloLastTab[fncChartNum] = 'regRpt' + fncChartNum;
    } else { // chart tab
        if (elementExist('RptTitle' + fncChartNum)) {
            document.getElementById('RptTitle' + fncChartNum).scrollIntoView();
        }
        $('#' + gloLastTab[fncChartNum]).addClass('hide');
//        if (elementExist(fncChartType + 'Chart' + fncChartNum)) {
//           fncTab.select(fncChartType + 'Chart' + fncChartNum);
//        }
        gloLastTab[fncChartNum] = fncChartType + 'Chart' + fncChartNum;
        $('#' + gloLastTab[fncChartNum]).removeClass('hide');
        let ctx = document.getElementById(fncCanvas).getContext("2d");
        if (gloChartExists[fncChartNum] == true) {
            switch (fncChartNum) {
                case 0:
                    wtk0Chart.destroy(); // Remove the old chart and all its event handles
                    break;
                case 1:
                    wtk1Chart.destroy();
                    break;
                case 2:
                    wtk2Chart.destroy();
                    break;
                case 3:
                    wtk3Chart.destroy();
                    break;
                case 4:
                    wtk4Chart.destroy();
                    break;
                case 5:
                    wtk5Chart.destroy();
                    break;
            }
        } else {
            gloChartExists[fncChartNum] = true;
        }
        switch (fncChartType) {
            case 'bar':
                switch (fncChartNum) {
                    case 0:
                        wtk0Chart = new Chart(ctx, wtk0BarConfig);
                        break;
                    case 1:
                        wtk1Chart = new Chart(ctx, wtk1BarConfig);
                        break;
                    case 2:
                        wtk2Chart = new Chart(ctx, wtk2BarConfig);
                        break;
                    case 3:
                        wtk3Chart = new Chart(ctx, wtk3BarConfig);
                        break;
                    case 4:
                        wtk4Chart = new Chart(ctx, wtk4BarConfig);
                        break;
                    case 5:
                        wtk5Chart = new Chart(ctx, wtk5BarConfig);
                        break;
                }
                break;
            case 'line':
                switch (fncChartNum) {
                    case 0:
                        wtk0Chart = new Chart(ctx, wtk0LineConfig);
                        break;
                    case 1:
                        wtk1Chart = new Chart(ctx, wtk1LineConfig);
                        break;
                    case 2:
                        wtk2Chart = new Chart(ctx, wtk2LineConfig);
                        break;
                    case 3:
                        wtk3Chart = new Chart(ctx, wtk3LineConfig);
                        break;
                    case 4:
                        wtk4Chart = new Chart(ctx, wtk4LineConfig);
                        break;
                    case 5:
                        wtk5Chart = new Chart(ctx, wtk5LineConfig);
                        break;
                }
                break;
            case 'area':
                switch (fncChartNum) {
                    case 0:
                        wtk0Chart = new Chart(ctx, wtk0AreaConfig);
                        break;
                    case 1:
                        wtk1Chart = new Chart(ctx, wtk1AreaConfig);
                        break;
                    case 2:
                        wtk2Chart = new Chart(ctx, wtk2AreaConfig);
                        break;
                    case 3:
                        wtk3Chart = new Chart(ctx, wtk3AreaConfig);
                        break;
                    case 4:
                        wtk4Chart = new Chart(ctx, wtk4AreaConfig);
                        break;
                    case 5:
                        wtk5Chart = new Chart(ctx, wtk5AreaConfig);
                        break;
                }
                break;
            case 'pie':
                switch (fncChartNum) {
                    case 0:
                        wtk0Chart = new Chart(ctx, wtk0PieConfig);
                        break;
                    case 1:
                        wtk1Chart = new Chart(ctx, wtk1PieConfig);
                        break;
                    case 2:
                        wtk2Chart = new Chart(ctx, wtk2PieConfig);
                        break;
                    case 3:
                        wtk3Chart = new Chart(ctx, wtk3PieConfig);
                        break;
                    case 4:
                        wtk4Chart = new Chart(ctx, wtk4PieConfig);
                        break;
                    case 5:
                        wtk5Chart = new Chart(ctx, wtk5PieConfig);
                        break;
                }
                break;
        }
    } // chart tab
};

// so charts work on MPA pages need to define vars in this file
var wtk0ChartLabels = new Array();
var wtk0BarData = '';
var wtk0BarConfig = '';
var wtk0LineData = '';
var wtk0LineConfig = '';
var wtk0AreaData = '';
var wtk0AreaConfig = '';
var wtk0PieData = '';
var wtk0PieConfig = '';

var wtk1BarData = '';
var wtk1BarConfig = '';
var wtk1LineData = '';
var wtk1LineConfig = '';
var wtk1AreaData = '';
var wtk1AreaConfig = '';
var wtk1PieData = '';
var wtk1PieConfig = '';

var wtk2BarData = '';
var wtk2BarConfig = '';
var wtk2LineData = '';
var wtk2LineConfig = '';
var wtk2AreaData = '';
var wtk2AreaConfig = '';
var wtk2PieData = '';
var wtk2PieConfig = '';

var wtk3BarData = '';
var wtk3BarConfig = '';
var wtk3LineData = '';
var wtk3LineConfig = '';
var wtk3AreaData = '';
var wtk3AreaConfig = '';
var wtk3PieData = '';
var wtk3PieConfig = '';

var wtk4BarData = '';
var wtk4BarConfig = '';
var wtk4LineData = '';
var wtk4LineConfig = '';
var wtk4AreaData = '';
var wtk4AreaConfig = '';
var wtk4PieData = '';
var wtk4PieConfig = '';

var wtk5BarData = '';
var wtk5BarConfig = '';
var wtk5LineData = '';
var wtk5LineConfig = '';
var wtk5AreaData = '';
var wtk5AreaConfig = '';
var wtk5PieData = '';
var wtk5PieConfig = '';
