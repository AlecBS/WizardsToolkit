<?PHP
/*
https://developer.paypal.com/docs/subscriptions/integrate/

This page can be called using AJAX to replace their:
    alert('You have successfully subscribed to ' + data.subscriptionID);
For example:

onApprove: function(data, actions) {
//  alert('You have successfully subscribed to ' + data.subscriptionID);
    let fncSubId = data.subscriptionID;
    let fncStatus = 'APPROVAL_PENDING';
    $.ajax({
      type: 'POST',
      url: '/wtk/ajxSavePayPal.php',
      data: { apiKey: pgApiKey, id: fncSubId,
          Status: fncStatus, ItemName: 'Silver Tier', Amount: 9},
      success: function(data2) {
          let fncJSON = $.parseJSON(data2);
          let fncResult = fncJSON.result;
          M.toast({html: fncResult, classes: 'green rounded'});
          ajaxGo('$pgFrom',0,0);
      }
    })
}

In PayPal set it up for your Webhook to point to:
{yourdomain}/wtk/payPalWebhook.php
    and that file will update the wtkRevenue upon activation of subscription
*/
define('_RootPATH', '../');
require('wtkLogin.php');

$pgIPaddress = wtkGetIPaddress();

$pgStatus = wtkGetPost('Status');
$pgItemName = wtkGetPost('ItemName');
$pgGrossAmount = wtkGetPost('Amount');

$pgSqlFilter = array(
    'UserUID' => $gloUserUID,
    'EcomUID' => 1,
    'EcomPayId' => $gloId,
    'IPaddress' => $pgIPaddress,
    'ItemName' => $pgItemName,
    'PaymentStatus' => $pgStatus,
    'GrossAmount' => $pgGrossAmount,
    'CurrencyCode' => 'USD'
);

$pgSQL =<<<SQLVAR
INSERT INTO `wtkRevenue` (`UserUID`, `EcomUID`, `EcomPayId`, `IPaddress`,
  `ItemName`, `PaymentStatus`, `GrossAmount`, `CurrencyCode`)
VALUES (:UserUID, :EcomUID, :EcomPayId, :IPaddress,
    :ItemName, :PaymentStatus, :GrossAmount, :CurrencyCode)
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter);

if ($pgItemName == 'Gold Tier'):  // Change based on your subscription tier names
    $pgTierLevel = 2;
else: // Silver
    $pgTierLevel = 1;
endif;
$pgSQL =<<<SQLVAR
UPDATE `wtkUsers`
  SET `TierLevel` = :TierLevel, `SubscriptionExpirationDate` = :SubDate
WHERE `UID` = :UserUID
SQLVAR;
$pgSubDate = date('Y-m-d', strtotime('+1 month'));
$pgSqlFilter = array(
    'UserUID' => $gloUserUID,
    'TierLevel' => $pgTierLevel,
    'SubDate' => $pgSubDate
);
wtkSqlExec($pgSQL, $pgSqlFilter);
$pgResult = 'Payment is being processed.  You are now on the ' . $pgItemName;

echo '{"result":"' . $pgResult . '"}';

// BEGIN Notify staff
$pgHtmBody =<<<htmVAR
<h3>$pgItemName Subscription!</h3>
User #: $gloUserUID
<br>Amount: $pgGrossAmount
htmVAR;

$pgMailArray = array(
    'ToAddress'     => $gloTechSupport,
    'Subject'       => $gloCoName . ' Subscription!',
    'Body'          => $pgHtmBody
);
// Comment out next line if you do not want notifications about new subscriptions
$pgTmp = wtkSendMail($pgMailArray);
//  END  Notify staff

exit; // no display needed, handled via JS and spa.htm
?>
