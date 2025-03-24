<?php
// This page is optimized to be your entry point into your app (iPhone or Android)
$gloLoginRequired = false;
require('wtk/wtkLogin.php');

$pgMobileLink = '';
$pgMobile = wtkGetParam('mobile');
if ($pgMobile != ''): // this makes website work for iOS and Android apps; have Xcode point to this page ?mobile=ios
    $pgMobileLink = '?mobile=' . $pgMobile; // will be ios if from iPhone mobile app
    wtkSetCookie('MobileApp','Y');
    wtkTrackVisitor('WTK App');
else:
    wtkTrackVisitor('WTK Demo');
endif;
$pgNavBar =<<<htmVAR
<div class="navbar-fixed">
    <div class="navbar navbar-home">
        <div class="row">
            <div class="col s3" style="margin-top:12px">
                <a id="backBtn" onclick="JavaScript:wtkGoBack()" class="hide"><i class="material-icons small white-text">navigate_before</i></a>
            </div>
            <div class="col s6 center">
                <h4 style="padding-top:12px">WTK Demo</h4>
            </div>
            <div class="col s3">
                <a id="hamburger" data-target="phoneSideBar" class="sidenav-trigger show-on-large hide right"><i class="material-icons small white-text">menu</i></a>
            </div>
        </div>
    </div>
</div>
    <!-- sidebar -->
    <div class="sidebar-panel">
        <ul id="phoneSideBar" class="collapsible sidenav side-nav">
            <li>
                <div class="user-view">
                    <div class="background">
                        <img src="/imgs/sunset.jpg">
                    </div>
                    <img class="circle responsive-img" id="myPhoto" src="/wtk/imgs/noPhotoAvail.png">
                    <span class="name" id="myName">@FullName@</span>
                </div>
            </li>
            <li><a class="sidenav-close" onclick="Javascript:goHome();"><i class="material-icons">dashboard</i>Dashboard</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('user');"><i class="material-icons">account_box</i>My Profile</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('reportViewer');"><i class="material-icons">insert_chart</i>Reports</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('chatList');"><i class="material-icons">chat</i>Chat</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('forumList');"><i class="material-icons">forum</i>Forum</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('messageList');"><i class="material-icons">message</i>Message</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('videoList');"><i class="material-icons">ondemand_video</i>Videos</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('demo/moneyStatsDemo');"><i class="material-icons">attach_money</i>Money Stats</a></li>
            <li><a class="sidenav-close" onclick="Javascript:showBugReport();"><i class="material-icons">bug_report</i>Report Bug</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('buyWTK');"><i class="material-icons">access_alarm</i>WTK Saves $</a></li>
            <li><a class="sidenav-close" onclick="Javascript:wtkLogout();"><i class="material-icons">close</i>Log Out</a></li>
        </ul>
    </div>
    <!-- end sidebar -->
htmVAR;
if (($gloDeviceType == 'phone') || ($pgMobile != '')):
    $pgNavBar = wtkReplace($pgNavBar, '<h4 style="padding-top:12px">WTK Demo</h4>','<h5 style="padding-top:12px">WTK Demo</h5>');
endif;
wtkSearchReplace('<!-- @wtkMenu@ -->', $pgNavBar);
$pgTmp  = 'All this source code and SQL database could be yours.' . "\n";
$pgTmp .= ' Save thousands on your development with<br>' . "\n";
$pgTmp .= '<a target="_blank" href="https://wizardstoolkit.com/pricing.php' . $pgMobileLink . '">Wizard&rsquo;s Toolkit</a>' . "\n";
$pgTmp .= ' low-code library.';
wtkSearchReplace('Put your logo and tag-line here.', $pgTmp);
$pgPromo =<<<htmVAR
    <h5>Jump Start Your Company</h5>
    <p>This demo shows a fraction of what comes with
      <a target="_blank" href="https://wizardstoolkit.com/pricing.php$pgMobileLink">Wizard&rsquo;s Toolkit</a>
      low-code library.  All source code is available for you to modify and
      host on your own servers. View on computer, phone or tablet.</p>
    <p>Registration required to reduce chance of inappropriate image uploads.
      The other option would be to have the app not allow inserts, edits or image uploads
      but that takes away proof of functionality.</p>
htmVAR;
if ($gloDeviceType == 'phone'):
    $pgTmp  =<<<htmVAR
    <br>
    <div class="container">
        <div id="loginCard" class="card bg-second">
            <div class="card-content">
                $pgPromo
            </div>
        </div>
    </div>
htmVAR;
else:
    $pgTmp  =<<<htmVAR
    <br>
    <div class="card">
        <div class="card-content">
            $pgPromo
        </div>
    </div>
htmVAR;
endif;
wtkSearchReplace('</div><!-- endloginMidBox -->', $pgTmp . '</div><!-- endloginMidBox -->');

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
if (wtkGetParam('App') == 'new'):
    $pgReplace = '<p id="upgMsg" class="green-text">' . wtkLang('Thank you for upgrading to newest version!') . '</p>';
    wtkSearchReplace('<div id="LoginErrMsg"></div>', '<div id="LoginErrMsg">' . $pgReplace . '</div>');
endif;

$pgHtm  = '';
// $pgHtm .= wtkFormHidden('pgDebugVar', 'Y');  // uncomment to turn on JavaScript debugging
$pgHtm .= wtkFormHidden('pgSiteVar', 'publicApp');

if ($pgMobile != ''): // this makes website work for iOS and Android apps; have Xcode point to this page ?mobile=ios
    $pgHtm .= wtkFormHidden('AccessMethod', $pgMobile);
    if ($pgMobile == 'ios'):
        wtkSearchReplace('id="myNavbar"','id="myNavbar" style="margin-top:20px"');
    endif;
    $pgPromoModal =<<<htmVAR
<div id="dockerGitInfo" class="hide">
    <div class="modal-content">
        <p>Check out Wizard&rsquo;s Toolkit on Docker at:
           https://hub.docker.com/r/proglabs/wizards-toolkit
        </p><br>
        <p>Prefer GIT?  Wizard&rsquo;s Toolkit is also available for download on
            GitHub at:
            https://github.com/AlecBS/WizardsToolkit
        </p>
    </div>
</div>
htmVAR;
    wtkSearchReplace('<!-- page bottom -->',$pgPromoModal);
endif;

if (($gloDeviceType == 'phone') || ($pgMobile == 'ios')):
    wtkSearchReplace('"width=device-width, initial-scale=1.0"','"width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no"');
    wtkSearchReplace('id="loginPage" class="full-page valign-wrapper"','id="loginPage" class="white" style="height:100%"');
    wtkSearchReplace('class="card b-shadow"','');
    wtkSearchReplace('"bg-second"','""');
    wtkSearchReplace('<form class="card-content">','<form class="container">');

    wtkSearchReplace('id="forgotPW" class="hide full-page valign-wrapper"','id="forgotPW" class="hide"');
    wtkSearchReplace('<div class="card-content"><p id="langForgotMsg">','<div class="container"><p id="langForgotMsg"><br>');

    wtkSearchReplace('id="resetPWdiv" class="hide full-page valign-wrapper"','id="resetPWdiv" class="hide"');
    wtkSearchReplace('<div class="card-content"><p id="langEmailMsg">','<div class="container"><p id="langEmailMsg"><br>');

    wtkSearchReplace('id="registerPage" class="hide full-page valign-wrapper"','id="registerPage" class="hide"');
    wtkSearchReplace('name="wtkRegisterForm" class="card-content">','name="wtkRegisterForm" class="container"><br>');
    if ($pgMobile == ''):
        $pgHtm .= wtkFormHidden('AccessMethod', 'pwa');
    endif;
else:
    wtkSearchReplace('id="loginPage" class="full-page valign-wrapper">','id="loginPage" class="full-page"><br>');
endif;

wtkSearchReplace('<div style="max-width: 144px"','<div class="hide"'); // language options
wtkSearchReplace('href="wtk/css/wtkLight.css">','href="wtk/css/wtkLight.css" id="CSStheme">');
wtkSearchReplace('href="wtk/css/wtkBlue.css">','href="wtk/css/wtkBlue.css" id="CSScolor">');

wtkMergePage($pgHtm, 'WTK Demo', 'wtk/htm/spa.htm');
?>
