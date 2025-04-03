<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$gloCoName = 'WTK Python Demos';
wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar('WTK Demos'));

$pgHtm =<<<htmVAR
<div class="container">
    <h2 class="center">Wizard&rsquo;s Toolkit Demos
        <small><br>this page is for testing Python calls</small>
    </h2><br>
    <div class="row">
        <div class="col m6 s12">
            <div class="card">
                <div class="card-content">
                    <h2 class="center">Python Calls</h2>
                    <hr><br>
                    <p>These will only work if you built the Python Docker for
                        Wizard&rsquo;s Toolkit.  You can do that by backing up
                        the docker-compose.yml then renaming docker-composePython.yml
                        to docker-compose.yml.  Then in Terminal run ./REBUILD_CONTAINERS.sh
                        and that will build a PHP, Nginx, MySQL, Python with phpMyAdmin.</p>
                    <p>The below links call the Python container.  Change the python code
                        in <strong>app.py</strong> for your own Python needs.</p>
                    <br>
                    <div class="center">
                        <a onclick="JavaScript:pythonTest('1');">Python Test 1</a>
                        <br><br>
                        <a onclick="JavaScript:pythonTest('Super-Duper');">Python Test 2</a>
                        <br><br>
                        <a onclick="JavaScript:pythonTest('Yoda');">Python Star Wars Test</a>
                        <br><br>
                        <hr><br>
                        <p>Return to regular <a href="index.php">WTK Demos</a>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col m6 s12">
            <div class="card">
                <div class="card-content">
                    <h2>Python Results</h2>
                    <hr><br>
                    <div id="pythonResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function pythonTest(fncTest){
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url: 'getPython.php',
        data: { apiKey: pgApiKey, step: fncTest },
        success: function(data) {
            waitLoad('off');
            $('#pythonResults').html(data);
//          let fncJSON = $.parseJSON(data);
        }
    })
}
</script>
htmVAR;
//wtkSearchReplace('<div class="container">','<div class="container">' . $pgHtm);
wtkSearchReplace('<div id="dashboard" class="hide">','<div id="dashboard" class="hide">' . $pgHtm);

$pgRememberMe = wtkGetCookie('rememberMe'); // 2ENHANCE save in phone storage
if ($pgRememberMe == 'Y'):
    $pgChecked = 'CHECKED';
    $pgEmail = wtkDecode(wtkGetCookie('UserEmail'));
    $pgPW = wtkDecode(wtkGetCookie('UserPW'));
else:
    $pgChecked = '';
    $pgEmail = '';
    $pgPW = '';
endif;

wtkSearchReplace('@myEmail@', $pgEmail);
wtkSearchReplace('@myPW@', $pgPW);
wtkSearchReplace('@rememberMe@', $pgChecked);
wtkSearchReplace('<div id="widgetDIV"></div>',''); // this demo page handles widget differently

//wtkSearchReplace('<div class="col m4 offset-m4 s12">','<div class="col m8 offset-m2 s12">');

$pgVersion = 1; // makes preventing cache when update JS very easy
wtkSearchReplace('wtkUtils.js','wtkUtils.js?v=' . $pgVersion);
wtkSearchReplace('wtkLibrary.js','wtkLibrary.js?v=' . $pgVersion);
wtkSearchReplace('wtkFileUpload.js','wtkFileUpload.js?v=' . $pgVersion);
wtkSearchReplace('wtkClientVars.js','wtkClientVars.js?v=' . $pgVersion);

wtkMergePage('', $gloCoName, '../wtk/htm/spa.htm'); // minibox
?>
