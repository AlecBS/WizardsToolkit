<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgEmailCode = wtkGetPost('EmailCode');

list($pgEmailUID, $pgSubject, $pgEmailBody, $pgToEmail) = wtkPrepEmail($pgEmailCode, $gloId);
$pgSaveArray = array (
	'EmailUID' => $pgEmailUID,
	'FromUID' => $gloUserUID,
	'ToUID' => $gloId
);

$pgTwoWeeks = date($gloPhpDateTime, strtotime('+ 14 days'));
$pgEmailBody = wtkReplace($pgEmailBody, '@TwoWeekNotice@', $pgTwoWeeks);
$pgResult = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, $pgSaveArray);

if ($pgResult == true):
	echo '{"result":"ok"}';
else:
	$pgIPaddress = wtkGetIPaddress();
	$pgMsg = 'Email Failure: From ' . $pgFromEmail . ', To ' . $pgToEmail . '; IP Address: ' . $pgIPaddress ;
	wtkLogError('Email Failure', $pgMsg);
	echo '{"result":"emailFailure"}';
endif;
exit;
?>
