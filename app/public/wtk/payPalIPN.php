<?php namespace Listener;
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

function wtkPrepInsert($fncColName, $fncValue){
    global $pgColumns, $pgValues, $pgSqlFilter;
    if ($fncValue != ''):
        $pgColumns .= ",`$fncColName`";
        $pgValues  .= ",:$fncColName";
        $pgSqlFilter[$fncColName] = $fncValue;
    endif;
    // code...
}
// This should be called by PayPal
if (empty($_POST)):
    $pgDebugFilter = array (
        'DevNote' => 'payPalIPN Empty POST'
    );
    wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)',$pgDebugFilter);
else:
    $pgDebugFilter = array (
        'DevNote' => 'payPalIPN with POST'
    );
    wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)',$pgDebugFilter);
    $pgColumns = '';
    $pgValues = '';
    $pgSqlFilter = array ();
    $pgInText = 'POSTed:' . "\n";
    foreach ($_POST as $key => $value):
        $pgInText .= '<br>' . $key . ' = ' . $value . "\n";
        switch ($key):
            case 'txn_type':
                wtkPrepInsert('EcomTxnType',$value);
                $pgTxnType = $value;
                break;
            case 'first_name':
                wtkPrepInsert('FirstName',$value);
                break;
            case 'last_name':
                wtkPrepInsert('LastName',$value);
                break;
            case 'custom':
                $pgCustom = $value;
                $pgValue = wtkReplace($value,'U',''); // can pass UserUID this way
                if (is_numeric($pgValue)):
                    wtkPrepInsert('UserUID',$pgValue);
                endif;
                break;
            case 'mc_currency':
                wtkPrepInsert('CurrencyCode',$value);
                break;
            case 'payer_id':
                wtkPrepInsert('PayerId',$value);
                break;
            case 'payment_status':
                wtkPrepInsert('PaymentStatus',$value);
                break;
            case 'payer_id':
                wtkPrepInsert('PayerId',$value);
                break;
            case 'mc_gross':
                wtkPrepInsert('GrossAmount',$value);
                break;
            case 'mc_fee':
                wtkPrepInsert('MerchantFee',$value);
                break;
            case 'payer_email':
                wtkPrepInsert('PayerEmail',$value);
                break;
            case 'item_name':
                wtkPrepInsert('ItemName',$value);
                break;
            case 'item_number':
                wtkPrepInsert('ItemNumber',$value);
                break;
        endswitch;
    endforeach;
    $pgIPaddress = wtkGetIPaddress();
    $pgInSQL  = "INSERT INTO `wtkInboundLog` (`IPaddress`,`InboundSource`,`InboundText`)";
    $pgInSQL .= " VALUES (:IPaddress,:InboundSource,:InboundText)";
    $pgInSqlFilter = array (
        'IPaddress' => $pgIPaddress,
        'InboundSource' => 'PayPal',
        'InboundText' => $pgInText
    );
    wtkSqlExec($pgInSQL, $pgInSqlFilter);
    switch ($pgTxnType):
        case 'subscr_signup':
            // do nothing, a separate transaction from PayPal will send payment info
            break;
        case 'subscr_payment':
        case 'web_accept':
        case 'send_money':
        case 'recurring_payment':
            if ($pgColumns != ''):
                $pgColumns = substr($pgColumns,1); // remove the first comma
                $pgValues = substr($pgValues,1);   // remove the first comma
                $pgDebugFilter = array (
                    'DevNote' => 'Cols: ' . $pgValues
                );
            //  wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)',$pgDebugFilter);
                $pgSQL  = "INSERT INTO `wtkRevenue` ($pgColumns)";
                $pgSQL .= " VALUES ($pgValues)";
                wtkSqlExec($pgSQL, $pgSqlFilter);
            endif;
            break;
    endswitch;
endif;

header("HTTP/1.1 200 OK");  // must be empty for PayPal
?>
