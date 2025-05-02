<?php
$gloLoginRequired = false;
require('wtk/wtkLogin.php');

if ($gloCoName == 'Your Company Name'):
    $gloCoName = wtkSqlGetOneResult('SELECT `CoName` FROM `wtkCompanySettings` WHERE `UID` = ?', [1]);
endif;

wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar($gloCoName));
// Or instead use data-driven Menu Bar:  wtkSearchReplace('<!-- @wtkMenu@ -->', wtkMenu('WTK-Admin'));

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

$pgMobile = wtkGetParam('mobile');
if ($pgMobile != ''): // this makes website work for iOS and Android apps; have Xcode point to this page ?mobile=ios
    $pgHtm .= wtkFormHidden('AccessMethod', $pgMobile);
    if ($pgMobile == 'ios'):
        wtkSearchReplace('id="myNavbar"','id="myNavbar" style="margin-top:20px"');
    endif;
endif;
//wtkSearchReplace('wtkLight.css','wtkDark.css');
// BEGIN Language Setup
$pgLangPref = wtkGetCookie('wtkLang');
if ($pgLangPref != ''):
    $pgHtm .= wtkFormHidden('changeLanguage', $pgLangPref);
    wtkSearchReplace('<option value="' . $pgLangPref . '">','<option selected value="' . $pgLangPref . '">');
endif;
//  END  Language Setup

wtkProtoType($pgHtm);
wtkMergePage($pgHtm, $gloCoName, 'wtk/htm/spa.htm');
?>
