<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$gloCoName = 'WTK Demos';
wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar('WTK Demos'));

$pgHtm =<<<htmVAR
<div class="container">
    <h2 class="center">Wizard&rsquo;s Toolkit Demos</h2><br>
    <p>All pages will work in both SPA and MPA environments.  To change a page to work
        with one style or the other requires only a couple lines of code at the bottom;
        the SPA pages echo directly and the MPA pages use wtkMergePage to insert
        page into an HTML template.</p>
    <div class="row">
        <div class="col m6 s12">
            <div class="card">
                <div class="card-content center">
                    <h3>SPA<small><br>Single Page Application</small></h3>
                    <br>
                    <a onclick="JavaScript:ajaxGo('/demo/lookupList');">Listing with Sorting and Edit</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('contactList');">Listing with More Buttons</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('wtkFileListModal');">List Files with Modal Upload Edit Page</a>
                    <br><hr>
                    <a onclick="JavaScript:ajaxGo('petList');">Pet List, Edit and Modal Notes Demo</a>
                    <br>requires wtkServerInfo.php to set<br><code>&dollar;gloSiteDesign = 'SPA';</code><br>
                        and wtkLibrary.js to set<br><code>var pgMPAvsSPA = 'SPA';</code>
                    <p>Must set up test data by running SQL scripts in demo/petList.php before accessing.</p>
                    <hr>
                    <a onclick="JavaScript:ajaxGo('charts');">Charts from single SQL SELECT</a>
                    <br>
                    <a target="_blank" href="charts.htm">Demo 1 HTM and JS</a>
                        &nbsp;&nbsp;&nbsp;
                    <a target="_blank" href="charts2.htm">Demo 2 HTM and JS</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('rptDateRange');">Date Range Chart Analytics</a>
                    <br><hr>
                    <a onclick="JavaScript:ajaxGo('moneyStatsDemo');">Revenue Analytics</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('moneyHistoryDemo');">Revenue History</a>
                    <br><hr>
                    <a onclick="JavaScript:ajaxGo('wtkFormFile');">File Upload</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('wtkFileUpload');">Alternative File Upload</a>
                    <br><hr>
                    <a onclick="JavaScript:ajaxGo('widgetDashboards');">Widget Dashboards</a>
                    <br>
                    <a onclick="JavaScript:ajaxGo('broadcast');">Broadcast Demo</a>
                    <br><hr>
                    <a onclick="JavaScript:ajaxGo('/demo/companyEdit');">Save Advanced Options</a>
                </div>
            </div>
        </div>

        <div class="col m6 s12">
            <div class="card">
                <div class="card-content center">
                    <h3>MPA<small><br>Multi Page Application</small></h3><br>
                    <a href="listDataMin.php" target="_blank">Bare Minimum to List Data</a>
                    <br>
                    <a href="listCustomHTML.php" target="_blank">Minimum List with Custom HTML and Exports</a>
                    <br>
                    <a href="listCustomRowHTM.php" target="_blank">Listing with Custom Row Template</a>
                    <br>
                    <a href="listRowFunction.php" target="_blank">Listing with Row Function</a>
                    <br>
                    <a href="listSortAligns.php" target="_blank">List with Sorts and Alignments</a>
                    <br>
                    <a href="listWithImage.php" target="_blank">List with Images</a>
                    <br>
                    <a href="storedProcList.php" target="_blank">List via Stored Procedure</a>
                    <br>
                    <a href="list3demo.php" target="_blank">3 Lists on Same Page</a>
                    <br><hr>
                    <a href="petListMPA.php" target="_blank">Pet List, Edit and Modal Notes Demo</a>
                    <br>requires wtkServerInfo.php to set<br><code>&dollar;gloSiteDesign = 'MPA';</code><br>
                        and wtkLibrary.js to set<br><code>var pgMPAvsSPA = 'MPA';</code>
                    <p>Also must set up test data by running SQL scripts in demo/petListMPA.php before accessing.</p>
                    <hr>
                    <a href="PayPalSubscribe.php" target="_blank">PayPal Subscribe Button</a>
                    <br><hr>
                    <a href="imageUploadDisplay.php" target="_blank">Image Upload Display</a>
                    <br>
                    <a href="wtkFormFileSecure.php" target="_blank">Secure File Upload</a>
                    <br>
                    <a href="fileDisplayDir.php" target="_blank">Display Files in Directories</a>
                    <br>
                    <a href="fileDisplayData.php" target="_blank">Display Files via Data</a>
                </div>
            </div>
        </div>
        <div class="col s12">
            <p>For more about SPA versus MPA, see this video:</p>
            <div class="video-container">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/qBOG5HZ8cs0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col m6 s12">
            <a onclick="JavaScript:ajaxGo('rptDateRange');">
                <div class="card b-shadow">
                    <div class="card-content center">
                        <i class="material-icons icon-gradient">view_quilt</i>
                        <h4>User Activity Report</h4>
                        <p><span id="widget1">42</span> Report Views</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col m6 s12">
            <a onclick="JavaScript:ajaxGo('petList');">
                <div class="card b-shadow">
                    <div class="card-content center">
                        <i class="material-icons icon-gradient">pets</i>
                        <h4>Pet List</h4>
                        <p><span id="widget2">12</span> Pets</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col m6 s12">
            <div class="card b-shadow">
                <div class="card-content center">
                    <i class="material-icons icon-gradient">fingerprint</i>
                    <h4>Login Log</h4>
                    <p><span id="widget3">119</span> Logins</p>
                </div>
            </div>
        </div>
        <div class="col m6 s12">
            <div class="card b-shadow">
                <div class="card-content center">
                    <i class="material-icons icon-gradient">brightness_high</i>
                    <h4>Features List</h4>
                    <p><span id="widget4">72</span> Page Views</p>
                </div>
            </div>
        </div>
    </div>
</div>
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

$pgVersion = 2; // makes preventing cache when update JS very easy
wtkSearchReplace('wtkUtils.js','wtkUtils.js?v=' . $pgVersion);
wtkSearchReplace('wtkLibrary.js','wtkLibrary.js?v=' . $pgVersion);
wtkSearchReplace('wtkFileUpload.js','wtkFileUpload.js?v=' . $pgVersion);
wtkSearchReplace('wtkClientVars.js','wtkClientVars.js?v=' . $pgVersion);

wtkMergePage('', $gloCoName, '../wtk/htm/spa.htm'); // minibox
?>
