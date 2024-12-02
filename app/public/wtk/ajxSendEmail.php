<?PHP
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
foreach($_POST as $key => $val ):
	if ($key == 'formName'):
		$pgFormName = wtkInsertSpaces(ucfirst($val));
	else:
		$pgNote .= ucfirst(wtkInsertSpaces($key)) . ": $val \n";
	endif;
endforeach;

// BEGIN Notify your staff
$pgBody =<<<htmVAR
Message sent from $gloCoName website!
IP Address: $pgIPaddress
<hr>
<strong>Form Contents</strong>
<p>Unchecked checkboxes will not be shown.</p>

$pgNote

htmVAR;

$pgResult = wtkNotifyViaEmail($pgFormName . ' Submission', $pgBody, $pgToEmail, [],'','default',$pgFromEmail,'Y');

if ($pgResult == true):
	echo '{"result":"OK"}';
else:
	$pgMsg = 'Email Failure: From ' . $pgFromEmail . ', To ' . $pgToEmail . '; IP Address: ' . $pgIPaddress ;
	wtkLogError('Email Failure', $pgMsg);
	echo '{"result":"emailFailure"}';
endif;
exit;
?>
