<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkPageProtect('wtk4LowCode');

$pgCount = wtkSqlGetOneResult("SELECT COUNT(*) FROM `wtkLookups` WHERE `LookupType` = 'TestType'", []);
if ($pgCount == 0):
    $pgResetHide = ' class="hide"';
    $pgSetupHide = '';
    $pgDisabled  = ' disabled="disabled"';
    $pgTestDataCount = '11,000';
else:
    $pgResetHide = '';
    $pgSetupHide = ' class="hide"';
    $pgDisabled  = '';
    $pgTestDataCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkUsersTST`', []);
    $pgTestDataCount = number_format($pgTestDataCount);
endif;
$pgHtm =<<<htmVAR
<style>
.max-width-72 {
    max-width: 60px !important;
}
#resultsDIV {
    min-height: 360px;
}
</style>
<h2>Server Stress Testing</h2>
<br>
<p>This page allows configuring different stress tests to run on your server.
  This is useful both for knowing what your current server can handle, and for
  testing any auto-scaling features that are implemented in your server configuration.</p>
<p>For example, if the stress load exceeds a certain point, does AWS, Google Cloud or
  your other provider spawn additional Kubernetes or VMs properly?  When the server
  load decreases, does it close them down properly?</p>
<p id="resetSQLtext"$pgResetHide>If your Stress Tests below have more DELETEs than INSERTs
    this could lower the row count so you may need to <strong>reset SQL data</strong> occasionally.
    To reset to starting 11,000 rows of test data, click
    <a onclick="JavaScript:setupSQLdata('reset','na')">reset SQL data</a> which will also TRUNCATE the
        `wtkStressTest` data table.  You are starting with $pgTestDataCount rows of test data.</p>
<h3 id="resetComplete" class="hide green-text">SQL data has been reset</h3>
<hr>
<form id="wtkForm">
    <div class="row">
        <div class="input-field col m3 offset-m5 s6 offset-s3">
            <input type="number" id="duration" name="duration" value="30" class="max-width-72">
            <label for="duration">Duration of Test (in seconds)</label>
        </div>
    </div>
<div align="center">
<table style="max-width: 450px !important">
    <thead>
        <tr><th>Process</th><th>Per Second</th><th class="right">Per Minute</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>SQL SELECT calls (40 rows)</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="sel40" name="sel40" value="20" class="max-width-72"></td>
            <td><span class="right" id="sel40PerMin">1200</span></td>
        </tr>
        <tr>
            <td>SQL SELECT calls (250 rows)</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="sel250" name="sel250" value="5" class="max-width-72"></td>
            <td><span class="right" id="sel250PerMin">300</span></td>
        </tr>
        <tr>
            <td>SQL INSERT calls (1 row)</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="ins" name="ins" value="30" class="max-width-72"></td>
            <td><span class="right" id="insPerMin">1800</span></td>
        </tr>
        <tr>
            <td>SQL UPDATE calls (1 row)</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="upd" name="upd" value="50" class="max-width-72"></td>
            <td><span class="right" id="updPerMin">3000</span></td>
        </tr>
        <tr>
            <td>SQL DELETE calls (1 row)</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="del" name="del" value="20" class="max-width-72"></td>
            <td><span class="right" id="delPerMin">1200</span></td>
        </tr>
        <tr>
            <td>Web Pages Opened</td>
            <td><input onchange="JavaScript:calcPerMin(this)" type="number" id="wp" name="wp" value="10" class="max-width-72"></td>
            <td><span class="right" id="wpPerMin">600</span></td>
        </tr>
    </tbody>
</table>
<br><button class="btn" type="button"$pgDisabled onclick="JavaScript:startTest()" id="startBtn">Start Test</button>
</div>
<p id="setupSQLtext"$pgSetupHide>Before running the Stress Test you must
  <a onclick="JavaScript:setupSQLdata('start','all')">Prepare SQL</a> for testing.
  This will create two SQL tables, add two functions and prepare some sample data to use.
  <br>Or if you already have generate_fname and generate_lname functions defined then
  <a onclick="JavaScript:setupSQLdata('start','skipf')">Skip Functions Prep</a> SQL.
  The `wtkUsersTST` data table and 11,000 inserted rows of sample data is used for
  all the SQL processes below.  Results are stored in the new `wtkStressTest` data table.
  The two functions added are `generate_fname` and `generate_lname`.</p>
</form>
<hr>
<div id="resultsDIV" class="hide">
    <h3 class="center">Stress Test Results</h3>
    <div class="row">
         <div class="col m6 s12">
             <div id="stressDataCalls">
                <p>Each page called uses Wizard&rsquo;s Toolkit library which does 5 SQL SELECT calls
                 in addition to your testing.  So including the results from your tests, the page calls
                 also did the following during your tests:</p>
                 <h4><span id="testDuration"></span> Second Duration</h4>
                 <ul class="browser-default">
                    <li>Additional Page calls: <span id="wtkPageCalls"></span></li>
                    <li>Additional SQL calls: <span id="wtkSQLcalls"></span></li>
                 </ul>
             </div>
         </div>
         <div class="col m6 s12">
            <div id="stressDataSummary"></div>
         </div>
    </div>
    <div class="row">
         <div class="col m6 s12">
            <div id="stressDataDetails"></div>
         </div>
         <div class="col m6 s12">
            <ul id="resultDetail" class="browser-default"></ul>
         </div>
    </div>
</div>
htmVAR;
require('wtkinfo.php');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkSearchReplace('</body>','<script type="text/javascript" src="/devUtils/wtkStressTest.js" defer></script>'. "\n" .'</body>');
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
