<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgPhone = wtkGetParam('SmsPhone');
$pgSmsMsg = wtkGetParam('SmsMsg');
wtkSendSMS($pgPhone, $pgSmsMsg,'SMS', $gloUserUID, $gloId);

echo '{"result":"ok"}';
exit;
?>
