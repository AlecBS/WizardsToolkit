<?php
/*
** Login requirements in this file; must pass apiKey for all pages
*/
function wtkTopGetGet($fncGetVariable) {
    return isset($_GET[$fncGetVariable]) ? stripslashes(urldecode($_GET[$fncGetVariable])) : '';
} // end of wtkTopGetGet
$pgDebug = wtkTopGetGet('Debug');
$gloForceRO = false;
if(!isset($gloLoginRequired)):
    $gloLoginRequired = true;
endif;
if(!isset($pgSecurityLevel)):
    $pgSecurityLevel = 0;
endif;
require('wtkServerInfo.php');
header('Access-Control-Allow-Origin: ' . $gloWebBaseURL);
$gloShowPrint = false;
$pgSavePg = '';
if ($gloLoginRequired == true):
    if (!isset($pgApiKey)):
        $pgApiKey = wtkGetParam('apiKey','login');
        if (($gloSiteDesign == 'MPA') && ($pgApiKey == 'login')):
            $pgApiKey = wtkGetCookie('apiKey');
            if ($pgApiKey == ''):
                $pgApiKey = 'login';
            endif;
        endif;
    endif;
    if ($pgApiKey == 'login'):
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
        wtkSearchReplace('@goToUrl@', $gloCurrentPage);
        wtkMergePage('', 'Login', _WTK_RootPATH . 'htm/login.htm');
    else:
        $pgLoginSQL =<<<SQLVAR
SELECT COALESCE(L.`UID`,0) AS `UID`, L.`UserUID`, c.`EnableLockout`,
    COALESCE(u.`LoginCode`,'') AS `LoginCode`, u.`UseSkype`,
    L.`WhichApp`, L.`AccessMethod`,
    u.`SecurityLevel`, L.`AppVersion`, c.`AppVersion` AS `mAppVersion`
  FROM `wtkLoginLog` L
    INNER JOIN `wtkUsers` u ON u.`UID` = L.`UserUID`
    INNER JOIN `wtkCompanySettings` c ON c.`UID` = :cUID
WHERE L.`apiKey` = :apiKey
ORDER BY L.`UID` DESC LIMIT 1
SQLVAR;
// 2ENHANCE so times out after 24 hours and/or checks to see if logged-out
        if (($gloSiteDesign == 'MPA') && ($pgApiKey == '')):
            $pgApiKey = wtkGetCookie('apiKey');
        endif;
        $pgSqlFilter = array (
            'apiKey' => $pgApiKey,
            'cUID' => 1
        );
        wtkSqlGetRow($pgLoginSQL, $pgSqlFilter);
        $pgLoginUID = wtkSqlValue('UID');
        if ($pgLoginUID == ''): // apiKey does not exist or is incorrect
            $pgHtm =<<<htmVAR
    <div class="container"><br><br>
        <div class="card b-shadow">
            <div class="card-content">
                <h3>Login Failed</h3>
                <p>Please go back and try again.</p>
            </div>
        </div>
    </div>
htmVAR;
            if ($gloSiteDesign == 'MPA'):
                wtkMergePage('<h3>Login Failed</h3><p>Please go back and try again.</p>', 'Login Error', _WTK_RootPATH . 'htm/minibox.htm');
            else:
                echo $pgHtm;
                exit;
            endif;
        endif;
        $gloUserUID = wtkSqlValue('UserUID');
        $gloLoginCode = wtkSqlValue('LoginCode');
        $gloUserSecLevel = wtkSqlValue('SecurityLevel');
        $gloUseSkype = wtkSqlValue('UseSkype');
        $pgLoginAppVer = wtkSqlValue('AppVersion');
        $pgCurrAppVer = wtkSqlValue('mAppVersion');
        $gloEnableLockout = wtkSqlValue('EnableLockout');
        $gloWhichApp = wtkSqlValue('WhichApp');
        $gloAccessMethod = wtkSqlValue('AccessMethod');
        if ($pgLoginAppVer != $pgCurrAppVer):
            $pgHtm =<<<htmVAR
    <div class="container"><br><br>
		<div class="card b-shadow">
            <div class="card-content">
                <h3>New App Version</h3>
                <p>The newest version of the app is now available.
                  Download is quick and easy.<br>Click
                  <a href="?App=new" class="btn">Upgrade</a>
                  then log back in.</p.
            </div>
        </div>
    </div>
htmVAR;
            if ($gloSiteDesign == 'MPA'): // this should never occur
                wtkMergePage($pgHtm, 'Login', _WTK_RootPATH . 'htm/minibox.htm');
            else:
                echo $pgHtm;
                exit;
            endif;
        endif;
        if ($pgSecurityLevel > $gloUserSecLevel):
            $pgTitle = wtkLang('No Access Allowed');
            $pgMsg  = '<p>' . wtkLang('The security level for this page exceeds your login security level') . '.</p>' . "\n";
            $pgMsg .= '<p>' . wtkLang('Please check with your Administrator regarding increasing your security access') . '.</p>';
            if ($gloSiteDesign == 'SPA'):
                $pgMsg .= '<p><a href="Javascript:wtkGoBack();">' . wtkLang('Return to prior page') . '</a><br>or' . "\n";
                $pgMsg .= ' <a onclick="Javascript:wtkLogout();">' . wtkLang('Logout') . '</a> ' . wtkLang('then log in with a different login code') . '.</p>';
            endif;
            if ($gloSiteDesign == 'MPA'):
                $pgHtm = "<h3>$pgTitle</h3><br>$pgMsg" . "\n";
                wtkSearchReplace('col m4 offset-m4 s12','col m8 offset-m2 s12');
                wtkMergePage($pgHtm, 'Security Issue', _WTK_RootPATH . 'htm/minibox.htm');
            else:
                $pgHtm =<<<htmVAR
    <div class="container"><br><br>
        <div class="card b-shadow">
            <div class="card-content">
                <h3>$pgTitle</h3>
<br> $pgMsg
            </div>
        </div>
    </div>
htmVAR;
                echo $pgHtm;
                exit;
            endif;
        endif;
    endif;
    if ($gloUserUID == 0):
        echo '<br>Page called incorrectly - notify developers' . "\n";
        // echo '<br>gloUserUID: ' . $gloUserUID . "\n";
        // echo '<br>SQL: ' . $pgSQL . "\n";
        // print_r($pgSqlFilter);
        // exit;
    else:
        $pgSQL = "UPDATE `wtkLoginLog` SET `CurrentPage` = :CurrentPage, `PassedId` = :PassedId, `LastLogin` = NOW(), `PagesVisited` = (`PagesVisited` + 1) WHERE `UID` = :UID ";
        $pgSavePg = substr(wtkReplace($gloCurrentPage , "'",'`'), 0, 150);
        $pgPassedId = wtkGetParam('id','NULL');
        if (!is_numeric($pgPassedId)):
            $pgPassedId = 'NULL';
        endif;
        $pgSQLFilter = array (
            'UID' => $pgLoginUID,
            'CurrentPage' => $pgSavePg,
            'PassedId' => $pgPassedId
        );
        wtkSqlExec(wtkSqlPrep($pgSQL), $pgSQLFilter);
        if ($gloSiteDesign == 'MPA'):
            wtkSetCookie('apiKey', $pgApiKey, '1month');
        endif;
    endif;
else:
    $gloAccessMethod = 'website';
endif;
if (($gloSiteDesign == 'MPA') && (!isset($pgApiKey))):
    $pgApiKey = wtkGetCookie('apiKey');
endif;
if ((wtkGetParam('Mode') == 'ADD') || ($gloId == 'ADD')):
    $gloWTKmode = 'ADD';  // must be below above code so wtkSqlGetRow retrieves values
else:   // Not wtkGetParam('Mode') == 'ADD'
    $gloWTKmode = 'EDIT';
endif;  // wtkGetParam('Mode') == 'ADD'
if ($gloLoginRequired == true):
    if ($pgSavePg != '/wtk/ajxNotificationList.php'):
        wtkAddUserHistory();
    endif;
endif;
?>
