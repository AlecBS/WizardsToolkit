var pgTestCounter = 0;
var pgCounterId = 0;
var pgStressId = 0;

function calcPerMin(fncThis){
    let fncId = fncThis.id;
    let fncMinValue = (fncThis.value * 60);
    $('#' + fncId + 'PerMin').text(fncMinValue);
}

function setupSQLdata(fncMode, fncPrep){
    if (fncMode != 'start'){
        $('#resetComplete').addClass('hide');
    }
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  'ajxSQLTest.php',
        data: { Mode: fncMode, Prep: fncPrep },
        success: function(data) {
            waitLoad('off');
            let fncJSON;
            try {
                fncJSON = $.parseJSON(data);
                if (fncJSON.result == 'ok') {
                    if (fncMode == 'start'){
                        $('#setupSQLtext').text('SQL has been set up for testing including 11,000 rows of test data in wtkUsersTST!');
                        if (fncPrep == 'all') {
                            M.toast({html: "SQL functions, tables and data created", classes: "green rounded"});
                        } else {
                            M.toast({html: "SQL tables and data created", classes: "green rounded"});
                        }
                        $('#startBtn').attr('disabled', false);
                        $('#resetSQLtext').removeClass('hide');
                    } else {
                        $('#resetComplete').removeClass('hide');
                        M.toast({html: "SQL test data reset", classes: "green rounded"});
                    }
                }
            } catch (e) {
                // Handle non-JSON response here
//                M.toast({html: `Problem running setup SQL scripts:<br>${data}`, classes:"red rounded"});
                wtkAlert('Problem running setup SQL scripts:<br>' + `${data}`);
            }
        }
    })
}

function startTest(){
    // Each second JS will call PHP pages to stress tests as outlined above
    // Each PHP page called will work for one second
    $('#startBtn').attr("disabled", true);
    $('#stressDataCalls').addClass('hide');
    $('#stressDataSummary').text('');
    $('#stressDataDetails').text('');
    document.getElementById('resultDetail').innerHTML = '';
    $.ajax({
        type: 'POST',
        url:  'ajxStressTest.php',
        data: { Type: 'start' },
        success: function(data) {
            let fncJSON = $.parseJSON(data);
            pgStressId = fncJSON.id;
            let fncUL = document.getElementById("resultDetail");
            let fncLI = document.createElement('li');
            fncLI.appendChild(document.createTextNode('Stress Test starting at ID: ' + pgStressId));
            fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
            wtkDebugLog('startTest before setInterval');
            pgTestCounter = 1;
            callAjaxPage('sel40', pgTestCounter);
            callAjaxPage('sel250', pgTestCounter);
            callAjaxPage('ins',pgTestCounter);
            callAjaxPage('upd',pgTestCounter);
            callAjaxPage('del',pgTestCounter);
            callAjaxPage('wp', pgTestCounter);
            pgCounterId = window.setInterval(runTests, 1000);
            wtkDebugLog('startTest after setInterval');
        }
    })
    $('#resultsDIV').removeClass('hide');
    var fncUrl = location.href;
    location.href = "#resultDetail";  //Go to the target element.
    history.replaceState(null,null,fncUrl);
}

function runTests(){
    wtkDebugLog('runTests TOP');
    pgTestCounter ++;
    let fncDuraction = wtkVal0('duration');
    if (pgTestCounter > fncDuraction) {
        window.clearInterval(pgCounterId);
        wtkDebugLog('clearInterval: pgTestCounter = ' + pgTestCounter + '; fncDuraction = ' + fncDuraction);
    } else {
        callAjaxPage('sel40', pgTestCounter);
        callAjaxPage('sel250', pgTestCounter);
        callAjaxPage('ins',pgTestCounter);
        callAjaxPage('upd',pgTestCounter);
        callAjaxPage('del',pgTestCounter);
        callAjaxPage('wp', pgTestCounter);
    }
}

function callAjaxPage(fncType,fncCounter){
//    let fncCount = $('#' + fncType).val();
    let fncCount = wtkVal0(fncType);
    if (fncCount > 0){
        wtkDebugLog('callAjaxPage: ' + window.performance.now() + ' ; fncType = ' + fncType);
        $.ajax({
            type: 'POST',
            url:  'ajxStressTest.php',
            data: { Type: fncType, Count: fncCount },
            success: function(data) {
                let fncJSON = $.parseJSON(data);
                let fncUL = document.getElementById("resultDetail");
                let fncLI = document.createElement('li');
                fncLI.appendChild(document.createTextNode(fncCounter + ' second: ' + fncJSON.result));
                fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
                let fncDuraction = $('#duration').val();
                if (fncCounter == fncDuraction){
                    let fncWPCount = $('#wp').val();
                    if (fncWPCount == 0){
                        showStressResults();
                    } else {
                        if (fncType == 'wp'){
                            showStressResults();
                        }
                    }
                }
            }
        })
    }
}

function showStressResults(){
    if (pgTestCounter > 0){
        pgTestCounter = 0;
        M.toast({html: "Testing Complete", classes: "green rounded"});
        $('#startBtn').attr('disabled', false);
        ajaxFillDiv('ajxStressResults', 'summary', 'stressDataSummary', pgStressId);
        ajaxFillDiv('ajxStressResults', 'detail', 'stressDataDetails', pgStressId);
        let fncTests = 0;
        let fncTmp = wtkVal0('ins');
        if (fncTmp > 0) {
            fncTests ++;
        }
        fncTmp = wtkVal0('upd');
        if (fncTmp > 0) {
            fncTests ++;
        }
        fncTmp = wtkVal0('del');
        if (fncTmp > 0) {
            fncTests ++;
        }
        fncTmp = wtkVal0('sel40');
        if (fncTmp > 0) {
            fncTests ++;
        }
        fncTmp = wtkVal0('sel250');
        if (fncTmp > 0) {
            fncTests ++;
        }
        fncTmp = wtkVal0('wp');
        if (fncTmp > 0) {
            fncTests ++;
        }
        let fncDuraction = wtkVal0('duration');
        let fncPageCalls = (fncDuraction * fncTests);
        let fncSQLCalls = (fncDuraction * fncTests * 5) + 4;
        wtkDebugLog('fncDuraction = ' + fncDuraction + '; fncTests = ' + fncTests);

        $('#wtkPageCalls').text(fncPageCalls);
        $('#wtkSQLcalls').text(fncSQLCalls);
        $('#testDuration').text(fncDuraction);
        $('#stressDataCalls').removeClass('hide');
    }
}

function wtkVal0(fncIdName) {
    let fncTest = document.getElementById(fncIdName);
    if (fncTest) {
        var fncResult = document.getElementById(fncIdName).value;
    } else {
        var fncResult = 0;
    }
    return fncResult;
} // wtkGetValue
