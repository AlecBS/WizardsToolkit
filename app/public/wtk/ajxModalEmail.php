<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgEmailUID = wtkGetPost('EmailUID');
if ($pgEmailUID == ''):
	$pgEmailUID = 'NULL';
endif;
$pgOtherUID = wtkGetPost('OtherUID');
if ($pgOtherUID == ''):
	$pgOtherUID = 'NULL';
endif;
$pgToEmail = wtkGetPost('ToEmail');
$pgFromEmail = wtkGetPost('FromEmail');
$pgFromName = wtkGetPost('FromName');
$pgToName = wtkGetPost('ToName');
$pgSubject = wtkGetPost('Subject');
$pgEmailMsg = nl2br(wtkGetPost('EmailMsg'));

$pgSaveArray = array (
	'EmailAddress' => $pgToEmail,
	'Subject' => $pgSubject,
	'EmailBody' => $pgEmailMsg,
	'FromUID' => $gloUserUID,
	'ToUID' => $gloId,
	'EmailUID' => $pgEmailUID,
	'OtherUID' => $pgOtherUID
);

$pgResult = wtkNotifyViaEmail($pgSubject, $pgEmailMsg, $pgToEmail, $pgSaveArray,'','default',$pgFromEmail,'N');
if ($pgResult == true):
	echo '{"result":"OK"}';
else:
	$pgIPaddress = wtkGetIPaddress();
	$pgMsg = 'Email Failure: From ' . $pgFromEmail . ', To ' . $pgToEmail . '; IP Address: ' . $pgIPaddress ;
	wtkLogError('Email Failure', $pgMsg);
	echo '{"result":"emailFailure"}';
endif;
exit;
?>
