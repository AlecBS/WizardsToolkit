<?PHP
// BEGIN Cloudflare Turnstile (captcha) verification
function wtkCFValidateTurnstile($fncToken, $fncSecret, $fncRemoteIP = null) {
	$fncURL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	$fncData = [
		'secret' => $fncSecret,
		'response' => $fncToken
	];
	if ($fncRemoteIP):
		$fncData['remoteip'] = $fncRemoteIP;
	endif;
	$fncOptions = [
		'http' => [
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($fncData)
		]
	];

	$fncContext = stream_context_create($fncOptions);
	$fncResponse = file_get_contents($fncURL, false, $fncContext);
	if ($fncResponse === FALSE):
		return ['success' => false, 'error-codes' => ['internal-error']];
	endif;
	return json_decode($fncResponse, true);
} // wtkCFValidateTurnstile

// Usage
$gloCFTurnstileSecret = '0x4AAAAAAB4vA-LPeem7pO5dZ8MRVSLlK0Y';
$pgCFToken = $_POST['cf-turnstile-response'] ?? '';
$pgRemoteIP = $_SERVER['HTTP_CF_CONNECTING_IP'] ??
	$_SERVER['HTTP_X_FORWARDED_FOR'] ??
	$_SERVER['REMOTE_ADDR'];

$pgTurnstileResult = wtkCFValidateTurnstile($pgCFToken, $gloCFTurnstileSecret, $pgRemoteIP);
if ($pgTurnstileResult['success']):
	// passed Captcha - continue
else:
	echo '{"response":"error"}';
endif;
//  END  Cloudflare Turnstile (captcha) verification

$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');
/*
email to Tech Support unless specified in hidden field
and insert into wtkEmailsSent
*/
$pgToEmail = wtkGetPost('toEmail');
if ($pgToEmail == ''):
	$pgToEmail = $gloTechSupport;
endif;
$pgFromEmail = wtkGetPost('email');
$pgIPaddress = wtkGetIPaddress();

$pgFormName = 'Form';
$pgNote = '';
$pgMessage = '';
foreach($_POST as $key => $val ):
	switch($key):
		case 'cf-turnstile-response':
			break; // skip
		case 'formName':
			$pgFormName = wtkInsertSpaces(ucfirst($val));
			break;
		case 'message':
			$pgMessage = $val;
			break;
		default:
			$pgNote .= '<tr><td>' . ucfirst(wtkInsertSpaces($key)) . ":</td><td>$val</td></tr> \n";
	endswitch;
endforeach;
if ($pgNote != ''):
    $pgNote = '<table class="table-border">' . $pgNote . '</table>';
endif;
if ($pgMessage != ''):
    $pgMessage = '<br/><strong>Message</strong><br>' . nl2br($pgMessage);
endif;

// BEGIN Notify your staff
$pgBody =<<<htmVAR
Message sent from $gloCoName website!
From IP Address: $pgIPaddress
<hr>
<strong>Form Contents</strong>
<p>Unchecked checkboxes will not be shown.</p>
$pgNote
$pgMessage
<br/><br/>
htmVAR;

$pgResult = wtkNotifyViaEmail($pgFormName . ' Submission', $pgBody, $pgToEmail, [],'','default',$pgFromEmail);

if ($pgResult == true):
	echo '{"result":"OK"}';
else:
	$pgMsg = 'Email Failure: ' . $gloWebBaseURL . ', From Email ' . $pgFromEmail . ', To ' . $pgToEmail . '; IP Address: ' . $pgIPaddress ;
	wtkLogError('Email Failure', $pgMsg);
	echo '{"result":"emailFailure"}';
endif;
exit;
?>
