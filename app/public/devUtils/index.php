<?php
$gloLoginRequired = false;
$gloSkipConnect = 'Y';
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkPageProtect('wtk4LowCode');

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col m10 offset-m1 s12">
<h3>DevOps Utilities</h3>
<br>
<p>These utilities help configure connectionn to SQL, verify your PHP library
 environment, and stress test your servers.</p>
<br>
<p>Verify your <a href="testWTK.php">Wizard&rsquo;s Toolkit</a> PHP environment is setup.</p>
<p>Test and <a href="configDB.php">configure DB</a> connectivity.</p>
<p>Test Twilio <a href="testSMS.php">SMS functionality</a>.</p>
<p>Create unlimited <a href="generateUsers.php">test users</a> to verify your analytic reports work properly.</p>
<p><a href="stressTest.php">Stress Test</a> the servers for page opening and SQL access.</p>
<p>Migrated from <a href="S3toR2.php"><strong>AWS S3</strong> to <strong>Cloudflare R2</strong></a>.</p>
<p>Migrated from <a href="S3uploads.php"><strong>Upload Files</strong></a> to external storage (AWS S3 or Cloudflare R2).</p>
    </div>
</div>
htmVAR;
require('wtkinfo.php');
wtkSearchReplace('<div class="row"><div class="col m4 offset-m4 s12">','<div class="container"><br><br>'); // for minibox adjustment
wtkSearchReplace('</div></div>','</div>');
wtkMergePage($pgHtm, 'Dev Utils', '../wtk/htm/minibox.htm');
?>
