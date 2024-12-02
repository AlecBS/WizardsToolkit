<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgHtm  = '<div class="card">' . "\n";
$pgHtm .= '  <div class="card-content">' . "\n";

$pgEmail = urldecode(wtkGetGet('Email'));

if ($pgEmail == ''):
    $pgEmail = wtkGetPost('myEmail');
endif;  // $pgEmail == ''

if ($pgEmail == ''):
    $pgHtm .= 'Email address not recognized';
else:   // Not $pgEmail == ''
    if (wtkGetPost('step') == 'send'):
        $pgMessage  = 'Unsubscribe request for email address: ' . $pgEmail . '<br><br>' . "\n";
        $pgMessage .= 'Message from unsubscriber:<br>' . nl2br(wtkGetParam('reason'));

        $pgSaveArray = array (
            'FromUID' => 0
        );
        wtkNotifyViaEmailPlain('Unsubscribe request', $pgMessage,'', $pgSaveArray);
        $pgHtm .= '<br><br>' . "\n";
        $pgHtm .= '<h4 class="center">Your message has been sent and we appreciate your past patronage of our site.</h3>' . "\n";
        $pgHtm .= '<br><br>' . "\n";
    else:   // Not wtkGetPost('step') == 'send'
        $pgSqlFilter = array (
            'Email' => $pgEmail
        );
        wtkSqlExec("UPDATE wtkUsers SET `OptInEmails` = 'N' WHERE `Email` = :Email", $pgSqlFilter);
        $pgHtm .= '   <form action="?" method="post">' . "\n";
        $pgHtm .= wtkFormHidden('myEmail', $pgEmail);
        $pgHtm .= wtkFormHidden('step', 'send');
        $pgHtm .=<<<htmVAR
<h4>Your email address is now unsubscribed from $gloCoName</h4>
<br>
<p>We will not send any future emails.</p>
<p>Please let us know why you are leaving or if there is anything we can do better.</p>
    <div class="row">
        <div class="input-field col s12">
             <textarea class="materialize-textarea" name="reason" id="reason"></textarea>
             <label for="reason">How can we improve?</label>
             <button type="submit" class="waves-effect waves-light btn right">Send</button>
        </div>
    </div>
  </form>
htmVAR;
    endif;  // wtkGetPost('step') == 'send'
endif;  // $pgEmail == ''

$pgHtm .= '</div></div>' . "\n";

wtkMergePage($pgHtm, 'Unsubscribe', 'htm/report.htm');
?>
