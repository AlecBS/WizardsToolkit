<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgWhy = wtkGetParam('why');
if ($pgWhy == 'thanks'):
    $pgHtm =<<<htmVAR
    <h2>Thanks!</h2><br>
    <p>We really appreciate your business.</p><br>
    <div class="center"><a href="$gloWebBaseURL" class="btn b-shadow waves-effect waves-light">Return to Home</a></div>
htmVAR;
else:
    $pgHtm =<<<htmVAR
    <h2>Canceled</h2><br>
    <p>Purchase has been canceled - let us know if there are any questions we can answer.</p><br>
    <div class="center"><a href="$gloWebBaseURL" class="btn b-shadow waves-effect waves-light">Return to Home</a></div>
htmVAR;
endif;

$pgSqlFilter = array (
    'DevNote' => 'payPal: ' . $pgWhy
);
// wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)',$pgSqlFilter);

wtkMergePage($pgHtm, 'Thanks!', 'htm/minibox.htm');
?>
