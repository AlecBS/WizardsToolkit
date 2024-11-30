<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkPageProtect('wtk4LowCode');
$pgReady = false;

$pgSQL =<<<SQLVAR
SELECT COUNT(*) AS `Count` FROM `wtkFiles`
  WHERE `ExternalStorage` = :ExternalStorage AND `CurrentLocation` = :CurrentLocation
SQLVAR;
$pgSqlFilter = array (
    'ExternalStorage' => 'Y',
    'CurrentLocation' => 'L'
);
$pgFileCount = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);
if ($pgFileCount > 0):
    if (isset($gloExtBucket)):
        if (($gloExtBucket != '') && ($gloExtBucket != 'your-bucket')):
            $pgReady = true;
        endif;
    endif;
endif;

$pgHtm =<<<htmVAR
<h3>Upload files to External Storage</h3>
<br>
<p>There are $pgFileCount files that need to be uploaded to <strong>External Storage</strong>.</p>
<p>As a file is uploaded the `CurrentLocation` is changed from 'L'ocal to 'C'loudflare.</p>
<p>Note: Cloudflare uses the same SDK for R2 external storage as AWS S3.  So by setting your
 global variables in wtk/wtkServerInfo.php you can upload and store your files in either.</p>
<p>This page does require the AWS S3 SDK to be installed.  It should be in a folder labeled
 /s3-sdk below the root folder and not accessible to the public website.  For example at the
 same level in the folder structure as the /public folder.</p>
<p>Uploading to external bucket: &nbsp; <strong>$gloExtBucket</strong>.</p>
htmVAR;

if ($pgReady == false):
    $pgHtm .=<<<htmVAR
<hr><br>
<h4>External Storage Not Ready</h4>
<p>You need to set up your external storage global variables.  Those can be
 found in wtk/wtkServerInfo.php</p>
htmVAR;
else:
    $pgHtm .=<<<htmVAR
<div align="center">
<br><button class="btn" type="button" onclick="JavaScript:startUpload()" id="startBtn">Start Upload</button>
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

function startUpload(){
    $('#startBtn').attr('disabled', true);
    $('#resultSummary').text('');
    document.getElementById('resultDetail').innerHTML = '';
    $('#resultsDIV').removeClass('hide');
    pgQuitNow = 'N';
    pgFileCount = 0;
    pgTotalSec = 0;
    uploadFiles();
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
function uploadFiles(){
    let fncfileCount = 0;
    let fncTime = 0;
    $.ajax({
        type: 'POST',
        url:  'ajxS3upload.php',
        data: { Type: 'start' },
        success: function(data) {
            let fncJSON = $.parseJSON(data);
            fncfileCount = fncJSON.fileCount;
            fncTime = fncJSON.time;
            pgTotalSec += Number(fncTime);
            pgFileCount += roundToPrecision(fncfileCount);
            let fncUL = document.getElementById('resultDetail');
            let fncLI = document.createElement('li');
            fncLI.appendChild(document.createTextNode(fncfileCount + ' files uploaded to S3 in ' + fncTime + ' seconds'));
            fncUL.insertBefore(fncLI, fncUL.childNodes[0]);
            if (fncJSON.result == 'OK'){ // when finished will be 'done'
                if (pgQuitNow == 'N'){
                    uploadFiles();
                } else {
                    showSummary();
                }
            } else {
                showSummary();
            }
            wtkDebugLog('uploadFiles ' + fncfileCount);
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
    let fncHtm = '<br><h4>Summary</h4><p>Finished uploading ' + pgFileCount + ' files to external storage!</p>';
    pgTotalSec = roundToPrecision(pgTotalSec * 100);
    pgTotalSec = (pgTotalSec / 100).toFixed(2);
    let fncMinutes = (pgTotalSec / 60).toFixed(2);
    fncHtm += '<p>Took a total of ' + fncMinutes + ' minutes (' + pgTotalSec + ' seconds).</p>';
    $('#resultSummary').html(fncHtm);
    $('#quitBtn').addClass('hide');
}
</script>
htmVAR;
endif;
require('wtkinfo.php');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'External Uploads', '../wtk/htm/minibox.htm');
?>
