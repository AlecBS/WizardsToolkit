<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
session_write_close();  // so rest of website still works
date_default_timezone_set('America/Los_Angeles'); // equivalent to MySQL 'US/Pacific'

$pgEmailTemplate = wtkGetPost('emailCode');;

$pgMode = wtkGetPost('Mode');
if ($pgMode != 'PickOne'):
    $pgHourMin   = date('H:i');
    if (($pgHourMin >= '06:00') && ($pgHourMin < '07:30')):
        $pgTimeZone = 'US/Eastern';
    elseif (($pgHourMin >= '07:30') && ($pgHourMin < '08:30')):
        $pgTimeZone = 'US/Central';
    elseif (($pgHourMin >= '08:30') && ($pgHourMin < '09:40')):
        $pgTimeZone = 'US/Mountain';
    elseif (($pgHourMin >= '09:40') && ($pgHourMin < '11:00')):
        $pgTimeZone = 'US/Pacific';
    else:
        $pgTimeZone = '';
    endif;
    // Good Email Template HTML at:
    // https://colorlib.com/etc/email-template/2/index.html

    if ($gloRNG == 'SendAll'): // 2FIX need to make data-driven and use something like PickOne feature
        $gloBulkEmailing = true;
    endif;

    $pgSQL =<<<SQLVAR
SELECT `UID`, `EmailType`, `Subject`, `EmailBody`
 FROM `wtkEmailTemplate`
WHERE `EmailCode` = :EmailCode
SQLVAR;
    $pgSqlFilter = array (
        'EmailCode' => $pgEmailTemplate
    );
    wtkSqlGetRow($pgSQL, $pgSqlFilter);

    $pgEmailUID = wtkSqlValue('UID');
    $pgEmailType = wtkSqlValue('EmailType');
    $pgSubject  = wtkSqlValue('Subject');
    $pgEmailBody = wtkSqlValue('EmailBody');
    $pgSubject  = wtkReplace($pgSubject, '@CompanyName@', $gloCoName);
    $pgSaveArray = array (
        'EmailUID' => $pgEmailUID,
        'FromUID' => 0,
        'EmailType' => $pgEmailType
    );
endif; // $pgMode != 'PickOne'
?>
