<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

/* Help with debugging
$pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
$pgSqlFilter = array(
    'DevNote' => 'payPalWebhook: at top'
);
wtkSqlExec($pgSQL, $pgSqlFilter, false); // false indicates do not stop if error
*/

// BEGIN save entire info to wtkInbound
$pgInput = 'phpInput: ' . file_get_contents('php://input');
$pgSQL = 'INSERT INTO `wtkInboundLog` (`EcomUID`,`IPaddress`,`InboundText`) VALUES (:EcomUID, :IPaddress, :InboundText)';

$pgIPaddress = wtkGetIPaddress();
$pgSqlFilter = array(
    'EcomUID' => 1,
    'IPaddress' => $pgIPaddress,
    'InboundText' => $pgInput
);
wtkSqlExec($pgSQL, $pgSqlFilter);
//  END  save entire info to wtkInbound

// BEGIN save details into wtkRevenue table
$pgInputArray = json_decode($pgInput, true);
if (isset($pgInputArray['id'])):
    try {
        $pgEcomTxnType = $pgInputArray['id'];
        // BEGIN Lookup which wtkRevenue this is from
        $pgEcomPayId = $pgInputArray['resource']['id'];
        $pgSQL =<<<SQLVAR
SELECT `UID`
 FROM `wtkRevenue`
WHERE `EcomPayId` = :EcomPayId
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
        $pgSqlFilter = array(
            'EcomPayId' => $pgEcomPayId
        );
        $pgRevenueUID = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);
        //  END  Lookup which wtkRevenue this is from

        $pgRevType = $pgInputArray['resource_type'];
        if (strlen($pgRevType) > 4):
            $pgRevType = substr($pgRevType, 0, 4);
        endif;
        $pgStatus = $pgInputArray['summary'];
        $pgFirstName = $pgInputArray['resource']['subscriber']['name']['given_name'];
        $pgLastName  = $pgInputArray['resource']['subscriber']['name']['surname'];
        $pgPayerEmail = $pgInputArray['resource']['subscriber']['email_address'];
        $pgPayerID = $pgInputArray['resource']['subscriber']['payer_id'];

        $pgCurrencyCode = $pgInputArray['resource']['billing_info']['last_payment']['amount']['currency_code'];
        $pgGrossAmount = $pgInputArray['resource']['billing_info']['last_payment']['amount']['value'];
        $pgSubExpires = $pgInputArray['resource']['billing_info']['next_billing_time'];
        $pgSubExpires = date('Y-m-d', (strtotime($pgSubExpires) + 86400)); // give extra day before expire

        $pgSQL =<<<SQLVAR
UPDATE `wtkRevenue`
  SET `EcomTxnType` = :EcomTxnType, `EcomPayId` = :EcomPayId,
      `PaymentStatus` = :PaymentStatus, `RevType` = :RevType,
      `GrossAmount` = :GrossAmount, `PayerEmail` = :PayerEmail, `PayerId` = :PayerId,
      `IPaddress` = :IPaddress, `FirstName` = :FirstName, `LastName` = :LastName
WHERE `UID` = :UID
SQLVAR;
        $pgSqlFilter = array(
            'UID' => $pgRevenueUID,
            'EcomTxnType' => $pgEcomTxnType,
            'EcomPayId' => $pgEcomPayId,
            'PaymentStatus' => $pgStatus,
            'RevType' => $pgRevType,
            'GrossAmount' => $pgGrossAmount,
            'PayerEmail' => $pgPayerEmail,
            'PayerId' => $pgPayerID,
            'IPaddress' => $pgIPaddress,
            'FirstName' => $pgFirstName,
            'LastName' => $pgLastName
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        /* Help with debugging
        $pgDebug = json_encode($pgSqlFilter);
        $pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
        $pgSqlFilter = array('DevNote' => $pgDebug);
        wtkSqlExec($pgSQL, $pgSqlFilter, false); // false indicates do not stop if error
        */
        $pgSQL =<<<SQLVAR
SELECT `UID`
 FROM `wtkInboundLog`
WHERE `EcomUID` = :EcomUID AND `IPaddress` = :IPaddress
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
        $pgSqlFilter = array(
            'EcomUID' => 1,
            'IPaddress' => $pgIPaddress
        );
        $pgInboundUID = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

        $pgSQL =<<<SQLVAR
UPDATE `wtkInboundLog`
  SET `RevenueUID` = :RevenueUID
WHERE `UID` = :UID
SQLVAR;
        $pgSqlFilter = array(
            'UID' => $pgInboundUID,
            'RevenueUID' => $pgRevenueUID
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
    } catch (Exception $e) {
        $pgErrMsg = 'PayPal Webhook Exception: ' . $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine();
        wtkLogError('PayPal Webhook Exception', $pgErrMsg, $e->getLine());
        $pgResult = $pgErrMsg;
    }
    //  END  save details into wtkRevenue table
endif;
echo '{"result":"ok"}';

/* Help with debugging
$pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
$pgSqlFilter = array('DevNote' => 'payPalWebhook: at bottom');
wtkSqlExec($pgSQL, $pgSqlFilter, false); // false indicates do not stop if error
*/
exit;
?>
