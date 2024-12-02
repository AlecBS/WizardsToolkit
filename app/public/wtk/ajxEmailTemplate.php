<?php
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgOtherUID = wtkGetPost('oid',0);

$pgSQL =<<<SQLVAR
SELECT `Subject`, `EmailBody`
  FROM `wtkEmailTemplate`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgSubject = wtkSqlValue('Subject');
$pgEmailBody = wtkSqlValue('EmailBody');

$pgEmailBody = wtkReplace($pgEmailBody, '"','~!~');
$pgEmailBody = wtkReplace($pgEmailBody, "\n",'^n^');
$pgEmailBody = wtkReplace($pgEmailBody, "\r",'^n^');

if ($pgOtherUID > 0):
    $pgSubject = wtkReplace($pgSubject,'@OtherUID@',$pgOtherUID);
    $pgEmailBody = wtkReplace($pgEmailBody,'@OtherUID@',$pgOtherUID);
endif;

$pgSubject   = wtkTokenToValue($pgSubject);
$pgEmailBody = wtkTokenToValue($pgEmailBody);

$pgJSON  = '{"result":"ok","Subject":"' . $pgSubject . '","Body":"' . $pgEmailBody . '"}';
echo $pgJSON;
?>
