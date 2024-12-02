<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');


$pgEmail = wtkGetParam('wtkwtkUsersEmail');
$pgSQL = "SELECT COUNT(*) FROM `wtkUsers` WHERE `Email` = :Email AND `DelDate` IS NULL";
$pgSqlFilter = array (
    'Email' => $pgEmail
);
$pgCount = wtkSqlGetOneResult(wtkSqlPrep($pgSQL), $pgSqlFilter);

$pgJSON = '{"result":"';
if ($pgCount > 0):
    $pgJSON .= 'accountExist", "count":"' . $pgCount . '"';
else:
    $pgFirstName    = wtkGetParam('wtkwtkUsersFirstName');
    $pgLastName     = wtkGetParam('wtkwtkUsersLastName');
    $pgPhone        = wtkGetParam('wtkwtkUsersPhone');
    if ($pgPhone != ''):
        wtkBuildInsertSQL('wtkUsers', 'Phone', $pgPhone);
    endif;
    $pgLoginCode    = wtkGetParam('wtkwtkUsersLoginCode');
    if ($pgLoginCode != ''):
        wtkBuildInsertSQL('wtkUsers', 'LoginCode', $pgLoginCode);
    endif;
    wtkBuildInsertSQL('wtkUsers', 'FirstName', $pgFirstName);
    wtkBuildInsertSQL('wtkUsers', 'LastName', $pgLastName);
    wtkBuildInsertSQL('wtkUsers', 'Email', $pgEmail);
    wtkBuildInsertSQL('wtkUsers', 'SecurityLevel', 5);
    $pgIPaddress = wtkGetIPaddress();
    wtkBuildInsertSQL('wtkUsers', 'IPaddress', $pgIPaddress);

    $pgUserOrigPW = wtkGetParam('wtkwtkUsersWebPassword');
    $pgTmpValue = hash_hmac("sha256", $pgUserOrigPW, $gloAuthStatus);
    $pgTmpValue = password_hash($pgTmpValue, PASSWORD_DEFAULT);
    wtkBuildInsertSQL('wtkUsers', 'WebPassword', $pgTmpValue);

    $pgPIN = wtkGeneratePassword(6,'N');
    wtkBuildInsertSQL('wtkUsers', 'NewPassHash', $pgPIN);
    $pgJSON .= 'PIN", "pin":"' . $pgPIN . '"';

    wtkExecInsertSQL('wtkUsers');

    $pgSQL  = "SELECT `UID` FROM `wtkUsers` WHERE `Email` = :Email AND `DelDate` IS NULL ORDER BY `UID` DESC LIMIT 1";
    $pgSqlFilter = array (
        'Email' => $pgEmail
    );
    $pgSQL = wtkSqlPrep($pgSQL);
    $gloUserUID = wtkSqlGetOneResult($pgSQL, $pgSqlFilter, 0);
    $pgJSON .= ', "userId":"' . $gloUserUID . '"';

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

    $pgHowVerify = wtkGetPost('howVerify');
    if ($pgHowVerify == 'S'):
        wtkSendSMS($pgPhone, $gloCoName . ' PIN: ' . $pgPIN,'SMS',0, $gloUserUID);
    else:
        $pgSQL = 'SELECT `Subject`, `EmailBody` FROM `wtkEmailTemplate` WHERE `EmailCode` = :EmailCode ORDER BY UID DESC LIMIT 1';
        $pgSqlFilter = array (
            'EmailCode' => 'WelcomePIN'
        );
        wtkSqlGetRow($pgSQL, $pgSqlFilter);
        $pgSubject  = wtkSqlValue('Subject');
        $pgEmailMsg = wtkSqlValue('EmailBody');

        $pgEmailMsg = wtkReplace($pgEmailMsg, '@FirstName@', $pgFirstName);
        $pgEmailMsg = wtkReplace($pgEmailMsg, '@LastName@', $pgLastName );
        $pgEmailMsg = wtkReplace($pgEmailMsg, '@PIN@', $pgPIN );

        $pgTemplate = wtkLoadInclude('../wtk/htm/emailLight.htm');
        $pgTemplate = wtkReplace($pgTemplate, '@website@', $gloWebBaseURL); // because URL is in HTML template once for unsubcribe link
        $pgHtmBody  = wtkReplace($pgTemplate, '@CurrentYear@', date('Y'));
        $pgHtmBody  = wtkReplace($pgHtmBody, '@Header@', $pgSubject );
        $pgHtmBody  = wtkReplace($pgHtmBody, '@Date@', date('F jS, Y'));
        $pgHtmBody  = wtkReplace($pgHtmBody, '@email@', urlencode(trim($pgEmail)));
        $pgHtmBody  = wtkReplace($pgHtmBody, '@wtkContent@', nl2br($pgEmailMsg));
        $pgHtmBody  = wtkReplace($pgHtmBody, 'Sincerely,', '');
        // ABS 10/07/16   END   new GUI template
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
            'EmailUID' => 2,
            'FromUID' => 0,
            'ToUID' => $gloUserUID
        );

        $pgTmp = wtkSendMail($pgMailArray,$pgSaveArray);
        $pgSaveRegMsg1 = 'A welcome email was sent to you at your email address of <strong>' . $pgEmail . '</strong>.';
    //  $pgSaveRegMsg1 .= ' &nbsp;&nbsp;&nbsp;&nbsp;The link within that email will activate your account.';
        $pgSaveRegMsg2 = 'Please also check your spam folder for this email, and add ' . $gloEmailFromAddress . ' to your email\'s white list or contact book.';
        if ($pgTmp == false):
            $pgSaveRegMsg2 = 'Email failed to send for some reason.  Please email ' . $gloEmailFromAddress . ' with your account email address so they can manually fix the problem.';
        endif;
    endif;
    wtkSetCookie('UserEmail', wtkEncode($pgEmail), '1year');
    wtkSetCookie('UserPW', wtkEncode($pgUserOrigPW), '1year');
    wtkSetCookie('rememberMe', 'Y', '1year');
endif;
$pgJSON .= '}';
echo $pgJSON;
?>
