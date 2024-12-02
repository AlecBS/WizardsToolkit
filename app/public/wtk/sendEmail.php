<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkServerInfo.php');

$pgFromName = wtkGetPost('name');
$pgFromEmail = wtkGetPost('email');
$pgMsg = wtkGetPost('msg');
if (($pgFromEmail != '') || ($pgMsg != '')):
    $pgIPaddress = wtkGetIPaddress();
/*
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkEmailsSent` (`EmailAddress`, `Subject`, `InternalNote`, `EmailBody`)
  VALUES (:EmailAddress, :Subject, :InternalNote, :EmailBody)
SQLVAR;
    wtkSqlExec($pgSQL);

    $pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
    $pgSqlFilter = array (
        'DevNote' => $pgFromEmail
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
*/
    if ($pgFromName != ''):
        $pgFromName = '<p>From: ' . $pgFromName . '</p>';
    endif;
    $pgBody =<<<htmVAR
<p>Message sent from $gloCoName website!</p>
$pgFromName
<p>From email: $pgFromEmail</p>
<p>IP Address: $pgIPaddress</p>
<hr>
<strong>Message</strong><br>
$pgMsg

htmVAR;
    $pgSaveArray = array (
        'FromUID' => 0
    );
    wtkNotifyViaEmail($gloCoName . ' Support', $pgBody, $gloTechSupport, $pgSaveArray);
    echo '{"result":"ok","email":"' . $pgFromEmail . '"}';
else:
    echo '{"result":"fail","email":"' . $pgFromEmail . '"}';
endif;

exit;
?>
