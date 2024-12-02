<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');
$pgHtm =<<<htmVAR
    <h4>Password Reset</h4>
</div>
<div class="card-content">
htmVAR;

$pgNewPassHash = wtkGetParam('u');
if ($pgNewPassHash != ''):
    $pgSQL = 'SELECT COUNT(*) FROM `wtkUsers` WHERE `NewPassHash` = ?';
    $pgCount = wtkSqlGetOneResult($pgSQL, [$pgNewPassHash]);
    if ($pgCount == 0):
        $pgHtm .= '<p>' . wtkLang('This reset password request is not valid.<br>Perhaps it has already been processed.') . '</p><br>';
    else:
        wtkSearchReplace('col m4 offset-m4 s12','col s12');
        $pgUserUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkUsers` WHERE `NewPassHash` = ?', [$pgNewPassHash]);
        if ($gloDarkLight == 'Dark'):
            $pgTextColor = ' white-text';
        else:
            $pgTextColor = '';
        endif;
        $pgHtm .=<<<htmVAR
        <form>
            <input type="hidden" id="id" name="id" value="$pgUserUID">
            <input type="hidden" id="u" name="u" value="$pgNewPassHash">
            <input type="hidden" id="CharCntr" name="CharCntr" value="Y">
            <div class="row">
                <div id="resultMsg" class="col s12"></div>
                <div class="input-field col m6 s12">
                    <input type="password" name="wtkwtkUsersWebPassword" id="wtkwtkUsersWebPassword" class="char-cntr" data-length="20">
                    <label for="wtkwtkUsersWebPassword">Password</label>
                </div>
                <div class="input-field col m6 s12">
                    <input type="password" name="rePW" id="rePW" class="char-cntr" data-length="20">
                    <label for="rePW">Confirm Password</label>
                </div>
                <div class="col s12 center">
                    <button id="btnResetPW" type="button" onclick="Javascript:resetPW()" class="btn b-shadow waves-effect">Save New Password</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="finishedDIV" class="card middle-box b-shadow hide">
    <div class="gradient-header center">
        <h4>Success!</h4>
    </div>
    <div class="card-content">
        <p>Your password has been changed.</p>
        <p>Return <a href="$gloWebBaseURL" class="btn b-shadow waves-effect waves-light">home</a> and login.</p>
    </div>
</div>
htmVAR;
    endif;
else:
    $pgHtm .= '<p>' . wtkLang('Page called incorrectly') . '.</p><br>';
endif;

wtkSearchReplace('"card b-shadow"','"card middle-box b-shadow" id="resetForm"');
wtkSearchReplace('<div class="card-content"><br>','<div class="gradient-header center">');
wtkSearchReplace('"col m4 offset-m4 s12"','"col s12"');
wtkSearchReplace('<body ','<body class="bg-second" ');
wtkSearchReplace('<div id="mainPage">','<div class="full-page valign-wrapper">');
$pgHtm .= wtkFormHidden('pgSiteVar', 'browser');
$pgHtm .= wtkFormHidden('pgDebugVar', 'Y');

wtkMergePage($pgHtm, 'New password request | ' . $gloCoName, 'htm/minibox.htm');
?>
