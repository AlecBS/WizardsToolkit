<?PHP
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

if (wtkGetPost('Mode') == 'modal'): // called via wtkModal JS call
    if ($gloRNG == 0):
        $gloRNG = $gloId;
    endif;
endif;
$pgSQL =<<<SQLVAR
SELECT u.`UID` AS `UserUID`, u.`FirstName`,u.`LoginCode`,u.`Email`,u.`WebPassword`,
    e.`UID`, e.`Subject`, e.`EmailBody`
  FROM `wtkUsers` u, `wtkEmailTemplate` e
WHERE u.`UID` = :UID AND e.`EmailCode` = :EmailCode
SQLVAR;
$pgSqlFilter = array (
    'UID' => $gloRNG,
    'EmailCode' => 'invite'
);
wtkSqlGetRow(wtkSqlPrep($pgSQL), $pgSqlFilter);

$pgUserName  = wtkSqlValue('FirstName');
$pgLoginCode = wtkSqlValue('LoginCode');
$pgToEmail   = wtkSqlValue('Email');
$pgSubject   = wtkSqlValue('Subject');
$pgEmailBody = wtkSqlValue('EmailBody');
$pgEmailUID  = wtkSqlValue('UID');
$pgUserUID   = wtkSqlValue('UserUID');

$pgSubject = wtkReplace($pgSubject,'@CompanyName@', $gloCoName);

$pgBody = wtkLoadInclude(_RootPATH . 'wtk/htm/email' . $gloDarkLight . '.htm');
$pgBody = wtkReplace($pgBody, '@wtkContent@', nl2br($pgEmailBody));
$pgBody = wtkReplace($pgBody, '@Date@', date('F jS, Y'));
$pgBody = wtkReplace($pgBody, '@CurrentYear@', date('Y'));
$pgBody = wtkReplace($pgBody,'@CompanyName@', $gloCoName);
$pgBody = wtkReplace($pgBody,'@Header@', $pgSubject);
$pgBody = wtkReplace($pgBody,'@UserName@', $pgUserName);
$pgBody = wtkReplace($pgBody,'@website@', $gloWebBaseURL);
$pgBody = wtkReplace($pgBody,'@email@', $pgToEmail);

// BEGIN Create Password
$pgWebPassword = wtkSqlValue('WebPassword');
if ($pgWebPassword == ''):
    $pgPW = wtkGeneratePassword(8,'N');
    $pgNewPW = hash_hmac("sha256", $pgPW, $gloAuthStatus);
    $pgNewPW = password_hash($pgNewPW, PASSWORD_DEFAULT);

    $pgSQL = 'UPDATE `wtkUsers` SET `WebPassword` = :PW, `NewPassHash` = NULL WHERE `UID` = :UID';
    $pgSqlFilter = array (
        'UID' => $pgUserUID,
        'PW' => $pgNewPW
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
    $pgNoPWBody = wtkReplace($pgBody,'@password@', '{not shown for security reasons}');
    $pgBody = wtkReplace($pgBody,'@password@', $pgPW);
else:
    $pgBody = wtkReplace($pgBody,'@password@', '{not sent for security reasons}');
    $pgNoPWBody = $pgBody;
endif;
//  END  Create Password
$pgPlainBody = wtkRemoveStyle($pgBody);

$pgMailArray = array(
                        'ToAddress'   => $pgToEmail,
                        'ToName'      => $pgUserName,
                        'Subject'     => $pgSubject,
                        'Body'        => $pgBody,
                        'PlainTextBody' => $pgPlainBody
                    );

$pgSaveArray = array (
    'FromUID' => 0,
    'EmailUID' => $pgEmailUID,
    'ToUID' => $gloRNG
);

if (wtkSendMail($pgMailArray, $pgSaveArray)):
    $pgHtm =<<<htmVAR
<div class="container">
    <div class="card">
        <div class="card-content">
            <h5>Email sent successfully to: $pgUserName <small>at $pgToEmail</small></h5>
            <br>
            <h6>Subject: $pgSubject</h6>
            <hr>
$pgNoPWBody
        </div>
    </div>
</div>
htmVAR;
else:
    $pgHtm  = '<h4>Email failed to send</h4>' . "\n";
    $pgHtm .= '<br><br>Email address: ' . $pgToEmail . "\n";
//    $pgHtm .= "<br><br>gloSMTPServer = " . $gloEmailHost . "\n";
endif;
echo $pgHtm;
wtkAddUserHistory();
exit;
?>
