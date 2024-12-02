<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

if (!empty($_POST)):
    $pgIPaddress = wtkGetIPaddress();
    $pgAffiliateUID = wtkGetParam('AffiliateUID');
    if ($pgAffiliateUID == 0):
        $pgAffiliateUID = 'NULL';
        $pgAffiliateRate = 'NULL';
    else:
        $pgAffiliateRate = wtkSqlGetOneResult('SELECT `AffiliateRate` FROM `wtkAffiliates` WHERE `UID` = ?', [$pgAffiliateUID]);
    endif;
    $pgOrderUID = wtkGetPost('OrderUID');
    if ($pgOrderUID == ''):
        $pgOrderUID = 'NULL';
    endif;
    $pgPayerEmail = wtkGetPost('PayeeEmail');
    $pgPayerId = wtkGetPost('PayerId');
    $pgFirstName = wtkGetPost('FirstName');
    $pgLastName = wtkGetPost('LastName');
    $pgStatus = wtkGetPost('Status');
    $pgAmount = wtkGetPost('Amount');
    $pgCurrency = wtkGetPost('CurrencyCode');
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkRevenue` (`EcomUID`, `UserUID`,`IPaddress`,`PayerEmail`,`PayerId`,
    `AffiliateUID`,`AffiliateRate`,
    `FirstName`,`LastName`,`PaymentStatus`,`GrossAmount`,`CurrencyCode`)
  VALUES (:EcomUID, :UserUID, :IPaddress, :PayerEmail, :PayerId,
    :AffiliateUID, :AffiliateRate,
    :FirstName, :LastName, :PaymentStatus, :GrossAmount, :CurrencyCode)
SQLVAR;
//  'OrderUID' => $pgOrderUID,
    $pgSqlFilter = array (
        'EcomUID' => 1,
        'UserUID' => $gloUserUID,
        'IPaddress' => $pgIPaddress,
        'PayerEmail' => $pgPayerEmail,
        'PayerId' => $pgPayerId,
        'FirstName' => $pgFirstName,
        'LastName' => $pgLastName,
        'PaymentStatus' => $pgStatus,
        'GrossAmount' => $pgAmount,
        'CurrencyCode' => $pgCurrency,
        'AffiliateUID' => $pgAffiliateUID,
        'AffiliateRate' => $pgAffiliateRate
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
endif;

echo '{"result":"ok"}';
exit;
?>
