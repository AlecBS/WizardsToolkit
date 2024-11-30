<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL  = "SELECT CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `UserName`,";
$pgSQL .= " `UID`,`LoginCode`,`Email`";
$pgSQL .= " FROM `wtkUsers` WHERE `UID` = :UID";
$pgSqlFilter = array (
    'UID' => $gloRNG
);

wtkSqlGetRow(wtkSqlPrep($pgSQL),$pgSqlFilter);

$pgUserUID      = wtkSqlValue('UID');
$pgUserName     = wtkSqlValue('UserName');
$pgUserEmail    = wtkSqlValue('Email');
$pgLoginCode    = wtkSqlValue('LoginCode');
if ($pgLoginCode == ''):
    $pgUserAcct = $pgUserName;
else:
    $pgUserAcct = $pgLoginCode;
endif;
$pgNewPassHash = hash('sha256', uniqid() . mt_rand(0,25000));
$pgFilter = array (
    'UID' => $pgUserUID,
    'Hash' => $pgNewPassHash
);
wtkSqlExec('UPDATE `wtkUsers` SET `NewPassHash` = :Hash WHERE `UID` = :UID', $pgFilter);

if ($gloLang == 'esp'):
    $pgEmailBody =<<<EMAILBODY
$pgUserAcct,

Hemos recibido su petici&oacute;n para una contrase&ntilde;a temporal en el sitio de $gloCoName y estamos feliz servirle.

Para reestablecer su contrase&ntilde;a, haz clik en el siguiente enlace para: <a href="$gloWebBaseURL/wtk/passwordReset.php?u=$pgNewPassHash">ver la p&aacute;gina para
reestablecer la contrase&ntilde;a</a>.  Si el enlace no funciona, entonces se puede copiar y pegar la siguiente URL en su navegador:

$gloWebBaseURL/wtk/passwordReset.php?u=$pgNewPassHash

Si usted no pidi&oacute; una contrase&ntilde;a temporal, favor de <a href="mailto:$gloTechSupport">enviarnos un correo electronico</a> para que podemos investigar el asunto m&aacute;s a fondo.

Que tenga excelente d&iacute;a!
EMAILBODY;
else:   // Not $gloLang == 'esp'
    $pgEmailBody =<<<EMAILBODY
$pgUserAcct,

We received your request from $gloCoName and we&rsquo;re happy to help.

To reset your password, click the following link to: <a href="$gloWebBaseURL/wtk/passwordReset.php?u=$pgNewPassHash"> view our password reset page</a>.
If the link does not work, copy and paste the following URL into your browser:

$gloWebBaseURL/wtk/passwordReset.php?u=$pgNewPassHash

If you did not request a temporary password, please <a href="mailto:$gloTechSupport">email us</a> to look into the matter further.<br/>

Have a great day!
EMAILBODY;
endif;  // $gloLang == 'esp'
$pgEmailBody = wtkReplace($pgEmailBody, "//wtk/","/wtk/");

$pgTemplate = wtkLoadInclude('../wtk/htm/email' . $gloDarkLight . '.htm');
$pgHtmBody  = wtkReplace($pgTemplate, '@CurrentYear@', date('Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '<div>You are receiving this email because you signed up with @CompanyName@.</div>','');
$pgHtmBody  = wtkReplace($pgHtmBody, '@Date@', date('F jS, Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '@CompanyName@', $gloCoName);
$pgHtmBody  = wtkReplace($pgHtmBody, '@Header@', 'Password Reset');
$pgHtmBody  = wtkReplace($pgHtmBody, '@PrimaryButton@', 'Reset Password');
$pgHtmBody  = wtkReplace($pgHtmBody, '@PrimaryLink@', "$gloWebBaseURL/wtk/passwordReset.php?u=$pgNewPassHash");
$pgHtmBody  = wtkReplace($pgHtmBody, '@wtkContent@', nl2br($pgEmailBody));

$pgHtmBody  = wtkReplace($pgHtmBody, '<td valign="bottom"><a href="@website@/wtk/unsubscribe.php?e=@email@">Click to Unsubscribe</a></td>','');
$pgHtmBody  = wtkReplace($pgHtmBody, '<tr><td colspan="2">','<tr><td colspan="3">');
$pgHtmBody  = wtkReplace($pgHtmBody, '@email@', urlencode(trim($pgUserEmail)));
$pgHtmBody  = wtkReplace($pgHtmBody, '@website@', $gloWebBaseURL);

$pgMailArray = array(
                    'FromAddress'   => $gloTechSupport,
                    'FromName'      => $gloCoName . ' Password Reset',
                    'ToAddress'     => $pgUserEmail,
                    'ToName'        => $pgUserName,
                    'Subject'       => 'Forgotten Password Reset',
                    'Body'          => $pgHtmBody,
                    'PlainTextBody' => $pgEmailBody
                );

$pgSaveArray = array (
    'FromUID' => 0,
    'ToUID' => $gloRNG
);

if (wtkSendMail($pgMailArray, $pgSaveArray)):
    $pgHtm =<<<htmVAR
<div class="container">
    <div class="card">
        <div class="card-content">
            <h5>Email sent successfully to: $pgUserName <small>at $pgUserEmail</small></h5>
            <br>
            <h6>Subject: Forgotten Password Reset</h6>
            <hr>
$pgHtmBody
        </div>
    </div>
</div>
htmVAR;
else:
    $pgHtm  = '<h4>Email failed to send</h4>' . "\n";
    $pgHtm .= '<br><br>Email address: ' . $pgUserEmail . "\n";
//    $pgHtm .= "<br><br>gloSMTPServer = " . $gloEmailHost . "\n";
endif;
echo $pgHtm;
wtkAddUserHistory();
exit;
?>
