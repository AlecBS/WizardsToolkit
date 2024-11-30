<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkPageProtect('wtk4LowCode');

$pgFileCount = wtkSqlGetOneResult("SELECT COUNT(*) FROM `wtkFiles` WHERE `CurrentLocation` = 'A'", []);

$pgHtm =<<<htmVAR
<h3>Copy files from AWS S3 to Cloudflare R2</h3>
<br>
<p>There are $pgFileCount files that need to be migrated from <strong>AWS S3</strong> to <strong>Cloudflare R2</strong>.</p>
<p>This will copy each file to <strong>Cloudflare R2</strong> but will not remove them from <strong>AWS S3</strong>.</p>
<p>This page does require the AWS S3 SDK to be installed.  It should be in a folder labeled
 /s3-sdk below the root folder and not accessible to the public website.  For example at the
 same level in the folder structure as the /public folder.</p>
<div align="center">
<br><button class="btn" type="button" onclick="JavaScript:startCopy()" id="startBtn">Start Test</button>
</div>
<hr>
<div id="resultsDIV" class="hide">
    <h3 class="center">File Processing Status
       <small><a onclick="JavaScript:quitCopy()" class="btn hide" id="quitBtn">Quit</a></small>
    </h3>
    <div class="row">
         <div class="col m6 s12">
            <ul id="resultDetail" class="browser-default"></ul>
         </div>
         <div class="col m6 s12">
            <div id="resultSummary"></div>
         </div>
    </div>
</div>
<script type="text/javascript">

function startCopy(){
    $('#startBtn').attr('disabled', true);
    $('#resultSummary').text('');
    document.getElementById('resultDetail').innerHTML = '';
    $('#resultsDIV').removeClass('hide');
    pgQuitNow = 'N';
    pgFileCount = 0;
    pgTotalSec = 0;
    copyFiles();
    var fncUrl = location.href;
    location.href = '#resultDetail';  //Go to the target element.
    history.replaceState(null,null,fncUrl);
    let fncUL = document.getElementById('resultDetail');
    let fncLI = document.createElement('li');
    fncLI.appendChild(document.createTextNode('starting...'));
    fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
    $('#quitBtn').removeClass('hide');
}

var pgQuitNow = 'N';
var pgFileCount = 0;
var pgTotalSec = 0;
function copyFiles(){
    let fncfileCount = 0;
    let fncTime = 0;
    $.ajax({
        type: 'POST',
        url:  'ajxCopyS3toR2.php',
        data: { Type: 'start' },
        success: function(data) {
            let fncJSON = $.parseJSON(data);
            fncfileCount = fncJSON.fileCount;
            fncTime = fncJSON.time;
            pgTotalSec += Number(fncTime);
            pgFileCount += roundToPrecision(fncfileCount);
            let fncUL = document.getElementById('resultDetail');
            let fncLI = document.createElement('li');
            fncLI.appendChild(document.createTextNode(fncfileCount + ' files copied to R2 in ' + fncTime + ' seconds'));
            fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
            if (fncJSON.result == 'OK'){ // when finished will be 'done'
                if (pgQuitNow == 'N'){
                    copyFiles();
                } else {
                    showSummary();
                }
            } else {
                showSummary();
            }
            wtkDebugLog('copyFiles ' + fncfileCount);
        }
    })
}

function quitCopy(){
    pgQuitNow = 'Y';
    $('#quitBtn').addClass('hide');
    let fncUL = document.getElementById('resultDetail');
    let fncLI = document.createElement('li');
    fncLI.appendChild(document.createTextNode('Quit requested... finishing last call'));
    fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
    $('#startBtn').attr('disabled', false);
}

function showSummary(){
    let fncHtm = '<br><h4>Summary</h4><p>Finished copying ' + pgFileCount + ' files from S3 to R2!</p>';
    pgTotalSec = roundToPrecision(pgTotalSec * 100);
    pgTotalSec = (pgTotalSec / 100).toFixed(2);
    let fncMinutes = (pgTotalSec / 60).toFixed(2);
    fncHtm += '<p>Took a total of ' + fncMinutes + ' minutes (' + pgTotalSec + ' seconds).</p>';
    $('#resultSummary').html(fncHtm);
    $('#quitBtn').addClass('hide');
}

</script>
htmVAR;
require('wtkinfo.php');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'S3 to R2', '../wtk/htm/minibox.htm');
?>
