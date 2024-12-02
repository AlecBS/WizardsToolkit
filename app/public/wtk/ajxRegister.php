<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');
$gloShowPrint = false;
$pgEmail      = trim(strtolower(wtkGetParam('wtkwtkUsersEmail')));
if ($gloConnected == false):   // implies this page was called from a different saving page
    wtkConnectToDB();
endif;  // $gloConnected == false

if ($pgEmail == ''):
    wtkInsFailedAttempt('Reg');
    $fncIPaddress = wtkGetIPaddress();
    $fncMsg  = 'Possible hacker attempt.  Someone accessed ajxRegister without an email address.';
    $fncMsg .= ' time: ' . date('m/d/Y h:i:s') . '<br><br>' . $gloCurrentPage;
    $fncMsg .= '<br><br>Website: ' . $gloWebBaseURL ;
    $fncMsg .= '<br><br>From IP address: ' . $fncIPaddress ;
    $pgSaveArray = array (
        'FromUID' => 0
    );
    wtkNotifyViaEmailPlain('Registration issue', $fncMsg, '', $pgSaveArray);

    $fncHtm  = '<div class="row">' . "\n";
    $fncHtm .= '	<div class="col m10 offset-m1 s12">' . "\n";
    $fncHtm .= '<h2>Page called incorrectly.</h2>' . "\n";
    $fncHtm .= '<br>Your IP address is: ' . $fncIPaddress . ' and our technical staff has been notified so they can look into this immediately.'. "\n";
    $fncHtm .= '	<br><br>' . "\n";
    $fncHtm .= '	</div>' . "\n";
    $fncHtm .= '</div>' . "\n";
    $gloShowPrint = false;
    wtkMergePage($fncHtm, 'Nefarious Action Detected', _RootPATH . 'wtk/htm/minibox.htm');
endif;

$pgLoginCode   = wtkGetParam('wtkwtkUsersLoginCode');
$pgFirstName   = wtkGetParam('wtkwtkUsersFirstName');
$pgLastName    = wtkGetParam('wtkwtkUsersLastName');
$pgPhone       = wtkGetParam('wtkwtkUsersPhone');

wtkBuildInsertSQL('wtkUsers', 'LoginCode', $pgLoginCode);
wtkBuildInsertSQL('wtkUsers', 'FirstName', $pgFirstName);
wtkBuildInsertSQL('wtkUsers', 'LastName', $pgLastName);
wtkBuildInsertSQL('wtkUsers', 'Email', $pgEmail);
if ($pgPhone != ''):
    wtkBuildInsertSQL('wtkUsers', 'Phone', $pgPhone);
endif;
wtkBuildInsertSQL('wtkUsers', 'SecurityLevel', 1);
$pgIPaddress = wtkGetIPaddress();
wtkBuildInsertSQL('wtkUsers', 'IPaddress', $pgIPaddress);

// BEGIN AppSumo registration keys
$pgPromoKey     = wtkGetParam('promoReg');
if ($pgPromoKey != ''):
    wtkBuildInsertSQL('wtkUsers', 'PromoCode', $pgPromoKey);
    $pgSQL =<<<SQLVAR
UPDATE `wtkPromoCodes`
  SET `RedeemDate` = NOW(), `RedeemIPaddress` = :RedeemIPaddress
WHERE `PromoPlanUID` = :PlanUID AND `PromoCode` = :PromoCode
SQLVAR;
    $pgPromoFilter = array (
        'PlanUID' => 1,
        'PromoCode' => $pgPromoCode,
        'RedeemIPaddress' => $pgIpAddress
    );
    wtkSqlExec($pgSQL, $pgPromoFilter);
endif;
//  END  AppSumo registration keys

$pgUserOrigPW = wtkGetParam('wtkwtkUsersWebPassword');
$pgTmpValue = hash_hmac("sha256", $pgUserOrigPW, $gloAuthStatus);
$pgTmpValue = password_hash($pgTmpValue, PASSWORD_DEFAULT);
wtkBuildInsertSQL('wtkUsers', 'WebPassword', $pgTmpValue);

$pgNewPassHash = hash('sha256', uniqid() . mt_rand(0,25000));
wtkBuildInsertSQL('wtkUsers', 'NewPassHash', $pgNewPassHash);
wtkExecInsertSQL('wtkUsers');

$pgSQL  = "SELECT `UID` FROM `wtkUsers` WHERE `Email` = :Email AND `DelDate` IS NULL ORDER BY `UID` DESC LIMIT 1";
$pgSqlFilter = array (
    'Email' => $pgEmail
);
$pgSQL = wtkSqlPrep($pgSQL);
$gloUserUID = wtkSqlGetOneResult($pgSQL, $pgSqlFilter, 0);

// BEGIN Update the wtkVisitors data
$pgVisitUID = wtkGetCookie('VisitorUID');
if ($pgVisitUID != ''):
	$pgSQL =<<<SQLVAR
UPDATE `wtkVisitors`
  SET `UserUID` = :UserUID , `SignupDate` = NOW()
WHERE `UID` = :UID
SQLVAR;
	$pgSqlFilter = array (
		'UserUID' => $gloUserUID,
		'UID' => $pgVisitUID
	);
	wtkSqlExec($pgSQL, $pgSqlFilter);
endif;
//  END  Update the wtkVisitors data

// Spanish/English Messages
if ($gloLang == 'esp'):
    $pgEmailMsg    = 'Bienvenido al sitio de ' . $gloCoName . '!'; // '<br><br>Para validar su nueva cuenta con nosotros, ve a:';
    // 2FIX need to make more like English version below
    $pgEmailMsg   .= '<br><br>Si usted no inici&oacute; una cuenta nueva con nosotros utilizando esta direcci�n de correo electronico, favor de enviarnos';
    $pgEmailMsg   .= ' un <a href="mailto:' . $gloTechSupport . '">email</a> para que podemos investigar el asunto.';
    $pgEmailMsg   .= '<br><br>Que tenga excelente d&iacute;a! ';
    $pgSaveRegMsg1  = 'Un email de activaci&oacute;n le fue enviado a su direcci&oacute;n de correo electr&oacute;nico <strong>' . $pgEmail . '</strong>.' . "\n";
    $pgSaveRegMsg1 .= ' El enlace que se encuentra en el email activar&aacute; su cuenta<br>' . "\n";
    $pgSaveRegMsg1 .= 'Su n&uacute;mero &uacute;nico de identificaci&oacute;n es: <strong>' . $gloUserUID . '</strong><br>';

    $pgSaveRegMsg2 = 'Tambien debe checar su folder de correo noo deseado en busca de este mail y agregar ' . $gloTechSupport . ' a su lista de correos electr&oacute;nicos approbados.';
else:   // Not $gloLang == 'esp'
    $pgSQL = 'SELECT `Subject`,`EmailBody` FROM `wtkEmailTemplate` WHERE `EmailCode` = :EmailCode ORDER BY UID DESC LIMIT 1';
    $pgSqlFilter = array (
        'EmailCode' => 'Welcome'
    );
    wtkSqlGetRow($pgSQL, $pgSqlFilter);
    $pgSubject = wtkSqlValue('Subject');
    $pgEmailMsg = wtkSqlValue('EmailBody');

    $pgSaveRegMsg1 = 'A welcome email was sent to you at your email address of <strong>' . $pgEmail . '</strong>.';
//  $pgSaveRegMsg1 .= ' &nbsp;&nbsp;&nbsp;&nbsp;The link within that email will activate your account.';
    $pgSaveRegMsg2 = 'Please also check your spam folder for this email, and add <strong>' . $gloEmailFromAddress . '</strong> to your email&rsquo;s white list or contact book.';
endif;
// Email a copy to the customer
$pgEmailMsg = wtkReplace($pgEmailMsg, '@FirstName@', $pgFirstName);
$pgEmailMsg = wtkReplace($pgEmailMsg, '@LastName@', $pgLastName );

$pgTemplate = wtkLoadInclude('../wtk/htm/email' . $gloDarkLight . '.htm');
$pgTemplate = wtkReplace($pgTemplate, '@website@', $gloWebBaseURL); // because URL is in HTML template once for unsubcribe link
$pgHtmBody  = wtkReplace($pgTemplate, '@CurrentYear@', date('Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '@Header@', $pgSubject );
$pgHtmBody  = wtkReplace($pgHtmBody, '@Date@', date('F jS, Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '@email@', urlencode(trim($pgEmail)));
$pgHtmBody  = wtkReplace($pgHtmBody, '@wtkContent@', $pgEmailMsg); // use nl2br($pgEmailMsg) if not HTML coding your Welcome email
$pgHtmBody  = wtkReplace($pgHtmBody, 'Sincerely,', '');
//  END   new GUI template
$pgMailArray = array(
    'FromAddress'   => $gloTechSupport,
    'FromName'      => $gloCoName,
    'ToAddress'     => $pgEmail,
    'ToName'        => $pgFirstName . ' ' . $pgLastName,
    'Subject'       => $pgSubject,
    'Body'          => $pgHtmBody,
    'PlainTextBody' => $pgEmailMsg
);

$pgSaveArray = array (
    'EmailUID' => 1,
    'FromUID' => 0,
    'ToUID' => $gloUserUID
);

$pgTmp = wtkSendMail($pgMailArray,$pgSaveArray);
if ($pgTmp == false):
    $pgSaveRegMsg2 = 'Email failed to send for some reason.  Please email ' . $gloEmailFromAddress . ' with your account email address so they can manually fix the problem.';
endif;

$pgEmailBody  = '<br>' . $pgFirstName . ' ' . $pgLastName . ' just signed up for ' . "\n";
$pgEmailBody .=  $gloCoName .'.<br>Their UserUID is ' . $gloUserUID . "\n";
$pgEmailBody .= '<br>Their email is ' . $pgEmail . '<br><br>';

$pgHtmBody  = wtkReplace($pgTemplate, '@CurrentYear@', date('Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '@Header@', 'New Sign-up');
$pgHtmBody  = wtkReplace($pgHtmBody, '@Date@', date('F jS, Y'));
$pgHtmBody  = wtkReplace($pgHtmBody, '@email@', urlencode($gloEmailFromAddress));
$pgHtmBody  = wtkReplace($pgHtmBody, '@wtkContent@', $pgEmailBody);

$pgMailArray = array(
    'ToAddress'     => $gloTechSupport,
    'Subject'       => 'New signup for ' . $gloCoName,
    'Body'          => $pgHtmBody,
    'PlainTextBody' => $pgEmailBody
);
// Comment out next line if you do not want notifications about new registrations
$pgTmp = wtkSendMail($pgMailArray);

$pgApiKey = md5(uniqid(rand(), true));
$pgRegKey = wtkFormHidden('regApiKey', $pgApiKey);
$pgHtm =<<<htmVAR
<div class="container">
    <div class="card">
      <div class="card-content">
        <h3>Registration Complete!</h3>
        <p>$pgSaveRegMsg1</p>
        <br>
        <p>$pgSaveRegMsg2</p>
      </div>
    </div>
    <div align="center"><br><br>
        <a class="waves-effect waves-light btn blue b-shadow" onclick="Javascript:goHome();">Go to Dashboard</a>
    </div>
</div>
$pgRegKey
htmVAR;

wtkSetCookie('rememberMe', 'Y', '1year');
wtkSetCookie('UserEmail', wtkEncode($pgEmail), '1year');
wtkSetCookie('UserPW', wtkEncode($pgUserOrigPW), '1year');
$pgAccessMethod = wtkGetPost('AccessMethod','website');

$pgAppVersion = wtkSqlGetOneResult('SELECT `AppVersion` FROM `wtkCompanySettings` WHERE `UID` = ?', [1]);
$pgSQL  = 'INSERT INTO `wtkLoginLog` (`FirstLogin`, `AccessMethod`, `CurrentPage`, `UserUID`, `apiKey`, `AppVersion`)';
$pgSQL .= "  VALUES (NOW(), :AccessMethod, :CurrentPage, :UserUID, :apiKey, :AppVersion)";
$pgFilter = array (
    'AccessMethod' => $pgAccessMethod,
    'CurrentPage' => 'registered',
    'UserUID' => $gloUserUID,
    'apiKey' => $pgApiKey,
    'AppVersion' => $pgAppVersion
);
wtkSqlExec(wtkSqlPrep($pgSQL), $pgFilter);
echo $pgHtm;
exit;
?>
