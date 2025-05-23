<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');
$pgUserEmail = wtkGetPost('em');
$pgRemember = wtkGetPost('rem');
$pgForgot = wtkGetPost('fpw');
$pgMenu = wtkGetPost('menu');
$pgMpaOrSpa = wtkGetPost('MpaOrSpa');
$pgWhichApp = wtkGetPost('app');
$pgAccessMethod = wtkGetPost('AccessMethod','website'); // default to website
if ($pgAccessMethod == 'N'):
    $pgAccessMethod = 'website';
endif;
$pgMoreResult = '';
$pgSQL =<<<SQLVAR
SELECT COALESCE(`WebPassword`,'NoPW')
  FROM `wtkUsers`
WHERE `DelDate` IS NULL AND `Email` = :Email
SQLVAR;
$pgFilter = array (
    'Email' => $pgUserEmail
);

if ($gloCSSLib == 'TailwindCSS'):
    $pgCSSerr = 'inline-flex items-center px-3 py-1 rounded-full bg-red-600 text-white text-sm font-medium';
else:
    $pgCSSerr = 'chip red white-text';
endif;

$pgDbPassword = wtkSqlGetOneResult($pgSQL, $pgFilter, 'NotExists');
if ($pgDbPassword == 'NotExists'): // email does not exist in DB
    if ($pgForgot != ''): // Forgot PW request
        $pgResult = "<span class='$pgCSSerr'>" . wtkLang('That email account does not exist on our site') . '.</span>';
    else: // Login request
        $pgResult = "<span class='$pgCSSerr'>" . wtkLang('Email address does not exist in our system') . '.</span>';
    endif;
else:
    if ($pgForgot == ''): // login attempt, did not forget password
        $pgUserOrigPW = wtkGetPost('pw');
        $pgUserPW = hash_hmac('sha256', $pgUserOrigPW, $gloAuthStatus);
        if (!password_verify($pgUserPW, $pgDbPassword)):
            $pgResult = "<span class='$pgCSSerr'>" . wtkLang('Email address or password is invalid') . '.</span>';
//          $pgMoreResult  = ',"debug":"' . $pgUserPW .'"';
        else:
            $pgResult = 'success';
            if ($pgRemember == 'Y'):
                wtkSetCookie('rememberMe', 'Y', '1year');
            else:
                wtkSetCookie('rememberMe', 'N', '1year');
            endif;
            if (($pgRemember == 'Y') && ($pgUserEmail != '')):
                wtkSetCookie('UserEmail', wtkEncode($pgUserEmail), '1year');
                wtkSetCookie('UserPW', wtkEncode($pgUserOrigPW), '1year');
            else:
                if (($pgRemember == 'N') && wtkGetCookie('UserEmail') != ''):
                    wtkDeleteCookie('UserEmail');
                    wtkDeleteCookie('UserPW');
                endif;    // delete cookie if it exists and the checkbox is unchecked
            endif;  // isset(wtkGetPost('wtkwtkUsersEmail'))

            $pgSQL =<<<SQLVAR
SELECT u.`UID`, CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `FullName`,
    u.`FirstName`, u.`SecurityLevel`, c.`AppVersion`, COALESCE(u.`MenuSet`, u.`StaffRole`) AS `MenuSet`,
    u.`FilePath`, COALESCE(u.`NewFileName`,'noPhoto') AS `UserPhoto`
 FROM `wtkUsers` u, `wtkCompanySettings` c
 WHERE u.`DelDate` IS NULL AND u.`Email` = :Email AND u.`WebPassword` = :WebPassword
   AND c.`UID` = :cUID
 ORDER BY u.`UID` DESC LIMIT 1
SQLVAR;
            $pgFilter['cUID'] = 1;
            $pgFilter['WebPassword'] = $pgDbPassword;
            wtkSqlGetRow($pgSQL, $pgFilter);
            $gloUserUID = wtkSqlValue('UID');
            // ABS 06/09/20  BEGIN add logic from wtkLogin.php so can skip posting to index.php
            $gloUserSecLevel = wtkSqlValue('SecurityLevel');
            $gloUserName = wtkSqlValue('FirstName');
            $pgFullName  = trim(wtkSqlValue('FullName'));
            $pgFilePath = wtkSqlValue('FilePath');
            $pgUserPhoto = wtkSqlValue('UserPhoto');
            $pgAppVersion = wtkSqlValue('AppVersion');
            if ($pgMenu == 'byRole'):
                $pgUserMenu = wtkSqlValue('MenuSet');
            else:
                $pgUserMenu = $pgMenu;
            endif;
            $pgFullName = wtkReplace($pgFullName, '"', "'");
            $gloUserName = wtkReplace($gloUserName, '"', "'");
            $pgApiKey = wtkReplace(md5(uniqid(rand(), true)),"'", '');
            $pgMoreResult  = ',"apiKey":"' . $pgApiKey .'"';
            $pgMoreResult .= ',"uid":"' . $gloUserUID .'"';
            $pgMoreResult .= ',"secLevel":"' . $gloUserSecLevel .'"';
            $pgMoreResult .= ',"firstName":"' . $gloUserName .'"';
            $pgMoreResult .= ',"myName":"' . $pgFullName .'"';
            $pgMoreResult .= ',"myEmail":"' . $pgUserEmail .'"';
            if ($pgUserPhoto != 'noPhoto'):
                $pgPhoto = $pgFilePath . '/' . $pgUserPhoto;
                $pgPhoto = wtkReplace($pgPhoto, '//', '/');
            else:
                $pgPhoto = 'noPhoto';
            endif;
            $pgMoreResult .= ',"myPhoto":"' . $pgPhoto .'"';
            if ($pgMenu != ''):
                $pgMoreResult .= ',"menu":"' . $pgUserMenu . '"';
            endif;
            $pgSQL  = 'INSERT INTO `wtkLoginLog` (`FirstLogin`, `AccessMethod`, `CurrentPage`, `UserUID`, `apiKey`, `AppVersion`,`WhichApp`)';
            $pgSQL .= "  VALUES (NOW(), :AccessMethod, :CurrentPage, :UserUID, :apiKey, :AppVersion, :WhichApp)";
            $pgFilter = array (
                'AccessMethod' => $pgAccessMethod,
                'CurrentPage' => 'login',
                'UserUID' => $gloUserUID,
                'apiKey' => $pgApiKey,
                'AppVersion' => $pgAppVersion,
                'WhichApp' => $pgWhichApp
            );
            wtkSqlExec(wtkSqlPrep($pgSQL), $pgFilter);
            if (($gloSiteDesign == 'MPA') || ($pgMpaOrSpa == 'MPA')):
//              wtkSetSession('apiKey',$pgApiKey);
                wtkSetCookie('apiKey',$pgApiKey,'1month');
            endif;
            // END  add logic from wtkLogin.php so can skip posting to index.php
        endif; // password is valid
    else: // called from Forgot Password
        // ABS 04/12/20  BEGIN called from Forgot Password
        $pgSQL  = "SELECT CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `UserName`,";
        $pgSQL .= " `UID`,`LoginCode`";
        $pgSQL .= " FROM `wtkUsers` WHERE `Email` = :Email AND `DelDate` IS NULL";
        $pgSQL .= " ORDER BY `UID` DESC LIMIT 1";
        $pgFilter = array (
            'Email' => $pgUserEmail
        );
        wtkSqlGetRow(wtkSqlPrep($pgSQL),$pgFilter);

        $pgUserUID      = wtkSqlValue('UID');
        $pgUserName     = wtkSqlValue('UserName');
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

If you did not request a temporary password, please <a href="mailto:$gloTechSupport">email us</a> to look into the matter further.

Have a great day!<br><br>
EMAILBODY;
        endif;  // $gloLang == 'esp'
        $pgEmailBody = wtkReplace($pgEmailBody, "//wtk/","/wtk/");

        $pgTemplate = wtkLoadInclude('htm/email' . $gloDarkLight . '.htm');
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
                            'ToName'        => $pgUserEmail,
                            'Subject'       => 'Forgotten Password Reset',
                            'Body'          => $pgHtmBody,
                            'PlainTextBody' => $pgEmailBody
                        );

        $pgSaveArray = array (
            'FromUID' => 0,
            'ToUID' => $pgUserUID
        );
        if (wtkSendMail($pgMailArray,$pgSaveArray)):
            $pgResult = 'success';
        else:
            $pgResult  = "<span class='$pgCSSerr'><h3>". wtkLang('Email Failure') . '</h3></span><br><br>';
            $pgResult .= '<p>Email failed to send for some reason.  Please email ' . $gloTechSupport ;
            $pgResult .= ' with your account email address so they can review the problem.</p><br>';
        endif;
    endif;
    // ABS 04/12/20   END  called from Forgot Password
endif;

$pgJSON  = '{"result":"' . $pgResult . '","maxFileUploadSize":' . $gloMaxFileSize;
$pgJSON .= $pgMoreResult;
$pgJSON .= '}';

echo $pgJSON;
exit;
?>
