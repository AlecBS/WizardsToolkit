<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgToken     = wtkGetPost('token');
$pgFirstName = wtkGetPost('payFirstName');
$pgLastName  = wtkGetPost('payLastName');
$pgAddress1  = wtkGetPost('payAddress1');
$pgAddress2  = wtkGetPost('payAddress2');
$pgCity      = wtkGetPost('payCity');
$pgCountry   = wtkGetPost('payCountry');
$pgPayerEmail = wtkGetPost('payerEmail');
$pgOrderUID   = wtkGetPost('orderUID');
$pgAmount     = wtkGetPost('amount');
$pgCurrencyCode = wtkGetPost('currencyCode', 'GBP');
$pgIpAddress  = wtkGetIPaddress();

$pgEcomUID = wtkSqlGetOneResult("SELECT `UID` FROM `wtkEcommerce` WHERE `PaymentProvider` = 'Checkout'",[],'NULL');

$pgSQL =<<<SQLVAR
INSERT INTO `wtkRevenue` (`EcomUID`,`UserUID`,`OrderUID`,`EcomTxnType`,`PayerEmail`,
    `FirstName`,`LastName`,`PaymentStatus`,`GrossAmount`,`CurrencyCode`)
  VALUES (:EcomUID,:UserUID,:OrderUID,:EcomTxnType,:PayerEmail,
          :FirstName,:LastName,:PaymentStatus,:GrossAmount,:CurrencyCode)
SQLVAR;

$pgSqlFilter = array (
    'EcomUID' => $pgEcomUID,
    'UserUID' => $gloUserUID,
    'OrderUID' => $pgOrderUID,
    'EcomTxnType' => $pgToken,
    'PayerEmail' => $pgPayerEmail,
    'FirstName' => $pgFirstName,
    'LastName' => $pgLastName,
    'PaymentStatus' => 'Requested',
    'GrossAmount' => $pgAmount,
    'CurrencyCode' => $pgCurrencyCode
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSqlFilter = array (
    'UserUID' => $gloUserUID,
    'OrderUID' => $pgOrderUID
);
$pgRevenueUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkRevenue` WHERE `UserUID` = :UserUID AND `OrderUID` = :OrderUID ORDER BY `UID` DESC LIMIT 1', $pgSqlFilter);

// BEGIN cURL access to checkout.com
$pgAmount = ($pgAmount * 100);
//checkout.com requires amount to be provided in the "minor currency unit"; aka pennies, etc.
$pgPaymentJSON =<<<htmVAR
{
  "source": {
    "type": "token",
    "token": "$pgToken"
  },
  "processing_channel_id": "$gloEcomChannel",
  "amount": $pgAmount,
  "currency": "$pgCurrencyCode",
  "payment_type": "Regular",
  "authorization_type": "Final",
  "description": "research and writing project",
  "reference": "$pgRevenueUID",
  "metadata": {
    "udf1": "$pgOrderUID"
  }
}
htmVAR;

$pgCurlHeaders = [
    'Authorization: Bearer ' . $gloEcomKey,
    'Content-Type: application/json',
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $gloEcomServer . 'payments');
curl_setopt($ch, CURLOPT_HTTPHEADER, $pgCurlHeaders);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pgPaymentJSON); // http_build_query did not work
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$pgResult = curl_exec($ch);
$pgCurlInfo = curl_getinfo($ch);

if (!($pgResult)):
    $pgCurlErrNum = curl_errno($ch);
    $pgCurlErrStr = curl_error($ch);
    wtkLogError('Checkout cURL', "cURL error: [$pgCurlErrNum] $pgCurlErrStr \n Called from $pgIpAddress");
else:
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkInboundLog` (`IPaddress`,`EcomUID`,`RevenueUID`,`InboundText`)
   VALUES (:IPaddress,:EcomUID,:RevenueUID,:InboundText)
SQLVAR;
    $pgSqlFilter = array (
        'IPaddress' => $pgIpAddress,
        'EcomUID' => $pgEcomUID,
        'RevenueUID' => $pgRevenueUID,
        'InboundText' => $pgResult
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
    $pgResultArray = json_decode($pgResult, true);
    // BEGIN process returned cURL
    if (!is_array($pgResultArray)):
        wtkLogError('Checkout bad JSON', $pgResult);
    else:
        if (array_key_exists('status',$pgResultArray)):
            $pgEcomPayId = $pgResultArray['id'];
            $pgActionId = $pgResultArray['action_id'];
            $pgStatus = $pgResultArray['status'];
            $pgSQL =<<<SQLVAR
UPDATE `wtkRevenue`
  SET `EcomPayId` = :EcomPayId, `PayerId` = :PayerId, `PaymentStatus` = :PaymentStatus
 WHERE `UID` = :UID
SQLVAR;
            $pgSqlFilter = array (
                'UID' => $pgRevenueUID,
                'EcomPayId' => $pgEcomPayId,
                'PayerId' => $pgActionId,
                'PaymentStatus' => $pgStatus
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
        endif;
    endif;
    //  END  process returned cURL
endif;
//  END  cURL access to checkout.com

/*
var_dump($pgCurlInfo); // For debugging

https://api-reference.checkout.com/#operation/requestAPaymentOrPayout

https://www.checkout.com/docs/payments/accept-payments/request-a-payment#Using_a_token
*/

echo '{"result":"ok"}';
exit;
?>
