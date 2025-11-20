<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

if ($gloCoName == 'Your Company Name'):
    $gloCoName = wtkSqlGetOneResult('SELECT `CoName` FROM `wtkCompanySettings` WHERE `UID` = ?', [1]);
endif;

if (wtkGetParam('Test') == 'wtkNavBar'):
    wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar('Wizards Toolkit'));
else:
    wtkSearchReplace('<!-- @wtkMenu@ -->', wtkMenu('WTK-Admin'));
endif;

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
wtkSearchReplace("wtkLoginForm(''","wtkLoginForm('','SPA','admin'");

if (wtkGetParam('App') == 'new'):
    $pgReplace = '<p id="upgMsg" class="green-text">' . wtkLang('Thank you for upgrading to newest version!') . '</p>';
    wtkSearchReplace('<div id="LoginErrMsg"></div>', '<div id="LoginErrMsg">' . $pgReplace . '</div>');
endif;

$pgHtm  = '';
// $pgHtm .= wtkFormHidden('pgDebugVar', 'Y');  // uncomment to turn on JavaScript debugging
$pgHtm .= wtkFormHidden('pgSiteVar', 'admin');

$pgVersion = wtkGetParam('v',1); // makes preventing cache when update JS very easy
wtkSearchReplace('wtkGlobal.css','wtkGlobal.css?v=' . $pgVersion);
wtkSearchReplace('wtkAdmin.js','wtkAdmin.js?v=' . $pgVersion);
wtkSearchReplace('wtkUtils.js','wtkUtils.js?v=' . $pgVersion);
wtkSearchReplace('wtkCore.js','wtkCore.js?v=' . $pgVersion);
wtkSearchReplace('wtkMaterialize.js','wtkMaterialize.js?v=' . $pgVersion);
wtkSearchReplace('wtkImporter.js','wtkImporter.js?v=' . $pgVersion);
wtkSearchReplace('wtkFileUpload.js','wtkFileUpload.js?v=' . $pgVersion);
// BEGIN Language Setup
$pgLangPref = wtkGetCookie('wtkLang');
if ($pgLangPref != ''):
    $pgHtm .= wtkFormHidden('changeLanguage', $pgLangPref);
    wtkSearchReplace('<option value="' . $pgLangPref . '">','<option selected value="' . $pgLangPref . '">');
endif;
//  END  Language Setup
wtkSPArestart($pgHtm); // only triggered when returning from outside APIs
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/spaAdmin.htm');
?>
