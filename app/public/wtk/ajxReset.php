<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgUID  = wtkGetPost('id');
$pgPW   = wtkGetPost('pw');
$pgHash = wtkGetPost('u');

$pgNewPW = hash_hmac("sha256", $pgPW, $gloAuthStatus);
$pgNewPW = password_hash($pgNewPW, PASSWORD_DEFAULT);

if ($pgHash == ''):
    $pgResult = 'Page called incorrectly';
    wtkInsFailedAttempt('Hash');
else:
    $pgSQL = 'SELECT COUNT(*) FROM `wtkUsers` WHERE `NewPassHash` = ?';
    $pgCount = wtkSqlGetOneResult($pgSQL, [$pgHash]);
    if ($pgCount == 0):
        $pgHtm .= '<p>' . wtkLang('This reset password request is not valid. Perhaps it has already been processed.') . '</p><br>';
        $pgResult = 'Reset link no longer valid.';
    else:
        $pgResult = 'ok';
        $pgSQL = 'UPDATE `wtkUsers` SET `WebPassword` = :PW, `NewPassHash` = NULL WHERE `UID` = :UID';
        $pgSqlFilter = array (
            'UID' => $pgUID,
            'PW' => $pgNewPW
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
    endif;
endif;

$pgJSON  = '{"result":"' . $pgResult . '"}';
echo $pgJSON;
?>
