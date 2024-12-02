<?PHP
// called from wtkCurrency.js - function:  changeCurrency
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgCurrency = wtkGetParam('Currency');
wtkSetCookie('wtkCurrency', $pgCurrency);

// Change pricing and currencies below as needed.  Or change so becomes a data-lookup.
switch ($pgCurrency):
    case 'MXN':
        $pgBasicCost = '$399 pesos';
        $pgBasicAmt = 399;
        $pgPremiumCost = '$789 pesos';
        $pgPremiumAmt = 789;
        break;
    case 'EUR':
        $pgBasicCost = '&euro;16';
        $pgBasicAmt = 16;
        $pgPremiumCost = '&euro;31';
        $pgPremiumAmt = 31;
        break;
    case 'NZD':
        $pgBasicCost = '$24 NZD';
        $pgBasicAmt = 24;
        $pgPremiumCost = '$48 NZD';
        $pgPremiumAmt = 48;
        break;
    case 'GBP':
        $pgBasicCost = '&pound;13.50';
        $pgBasicAmt = 13.50;
        $pgPremiumCost = '&pound;27';
        $pgPremiumAmt = 27;
        break;
    default: // USD
        $pgBasicCost = '$18 USD';
        $pgBasicAmt = 18;
        $pgPremiumCost = '$36 USD';
        $pgPremiumAmt = 36;
        break;
endswitch;

$pgJSON  = '{"result":"ok","basicCost":"' . $pgBasicCost . '","premCost":"' . $pgPremiumCost . '",';
$pgJSON .= '"basicAmount":"' . $pgBasicAmt . '","premAmount":"' . $pgPremiumAmt . '"}';

echo $pgJSON;
exit; // no display needed, handled via changeCurrency JS function in wtkCurrency.js
?>
