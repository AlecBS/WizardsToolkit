<?php
/*
This file makes it easy to test webhooks from third-parties to see
exactly what they send in:
    * headers
    * POST
    * GET

Have website point to this /devUtils/webhookTest.php and you will see if they are passing values as expected.
After it calls the webhook check your results in the `wtkDebug` data table.

Do not leave this on server after you are finished with testing.
*/
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$gloLoginRequired = false;
require('wtk/wtkLogin.php');

$pgHeaders = getallheaders();
$pgKeyValues = '';
foreach ($pgHeaders as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgKeyValues .= "Array - $key2: $value2" . "\n";
        endforeach;
    else:
        $pgKeyValues .= "$key: $value" . "\n";
    endif;
endforeach;
$pgSqlFilter = array('DevNote' => "Webhook Header: \n$pgKeyValues");
wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $pgSqlFilter, false);

$pgKeyValues = '';
foreach ($_GET as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgKeyValues .= "Array - $key2: $value2" . "\n";
        endforeach;
    else:
        $pgKeyValues .= "$key: $value" . "\n";
    endif;
endforeach;
$pgSqlFilter = array('DevNote' => "Webhook GET: \n$pgKeyValues");
wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $pgSqlFilter, false);

$pgKeyValues = '';
foreach ($_POST as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgKeyValues .= "Array - $key2: $value2" . "\n";
        endforeach;
    else:
        $pgKeyValues .= "$key: $value" . "\n";
    endif;
endforeach;
$pgSqlFilter = array('DevNote' => "Webhook POST: \n$pgKeyValues");
wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $pgSqlFilter, false);

$pgInput = file_get_contents('php://input');
if (isset($pgInput)):
    if ($pgInput != ''):
        $pgSqlFilter = array('DevNote' => "Webhook input: \n$pgInput");
    else:
        $pgSqlFilter = array('DevNote' => "Webhook no input");
    endif;
    wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $pgSqlFilter, false);
endif;

// BEGIN Verify Header Password
$pgHeaders = getallheaders();
$pgUserAgent = isset($pgHeaders['User-Agent']) ? $pgHeaders['User-Agent'] : 'bad';
if ($pgUserAgent != 'WhatYouExpect'): // better to pass special header name and secret
    $pgDebugFilter = array('DevNote' => 'webhook: failed Header PW test');
    wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $pgDebugFilter, false); // false indicates do not stop if error
    echo '{"result":"fail","error":"failed header password verification"}';
    exit;
endif;
//  END  Verify Header Password

echo '{"result":"ok"}';
exit;
?>