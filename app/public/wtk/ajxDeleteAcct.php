<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

// Email user that their account is deleted and how to re-register if they wish later
list($pgEmailUID, $pgSubject, $pgEmailBody, $pgToEmail) = wtkPrepEmail('DelAcct', $gloUserUID);
$pgSaveArray = array (
    'EmailUID' => $pgEmailUID,
    'FromUID' => 0,
    'ToUID' => $gloUserUID
);
wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, $pgSaveArray, '', 'default', '', 'Y');

// Email tech department to remove all references in other databases regarding this user
$pgBody =<<<htmVAR
<h3>User Account Deleted</h3>
<p>User Account # $gloUserUID has been deleted and their goodbye email has been
 sent to $pgToEmail.</p>
htmVAR;
wtkNotifyViaEmail('User Deleted Account', $pgBody);

$pgSQL =<<<SQLVAR
UPDATE `wtkUsers`
  SET `FirstName` = 'deleted', `LastName` = 'deleted', `Email` = 'deleted',
    `Phone` = IF(`Phone` IS NOT NULL, 'deleted', NULL),
    `CellPhone` = IF(`CellPhone` IS NOT NULL, 'deleted', NULL),
    `Address` = IF(`Address` IS NOT NULL, 'deleted', NULL),
    `Address2` = IF(`Address2` IS NOT NULL, 'deleted', NULL),
    `City` = IF(`City` IS NOT NULL, 'deleted', NULL),
    `State` = NULL,
    `Zipcode` = NULL,
    `CountryCode` = NULL,
    `LangPref` = NULL,
    `LoginCode` = NULL,
    `WebPassword` = 'deleted',
    `IPAddress` = NULL,
    `NewPassHash` = NULL,
    `OptInEmails` = 'N',
    `DelDate` = NOW()
 WHERE `UID` = :UserUID
SQLVAR;
$pgSqlFilter = array(
    'UserUID' => $gloUserUID
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
DELETE FROM `wtkEmailsSent`
 WHERE `SendToUserUID` = :UserUID
   AND `Subject` = 'Forgotten Password Reset'
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
UPDATE `wtkEmailsSent`
   SET `EmailAddress` = 'deleted'
 WHERE `SendToUserUID` = :UserUID
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

echo '{"result":"ok"}';
exit; // no display needed, handled via JS and spa.htm
?>
