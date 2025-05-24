<?PHP
$pgSecurityLevel = 50;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('/admin/revenueEdit.php', $gloId);
$pgBtns = wtkModalUpdateBtns('../wtk/lib/Save','revDIV');

$pgSQL =<<<SQLVAR
SELECT e.`PaymentProvider`, L.`LookupDisplay` AS `RevenueType`, r.`PaymentStatus`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `AssociatedUser`,
    r.`EcomPayId`, r.`PayerEmail`, r.`PayerId`, r.`FirstName`, r.`LastName`,
    r.`ItemName`, r.`ItemNumber`, r.`OrderUID`, r.`GrossAmount`, r.`MerchantFee`,
    r.`IPaddress`, r.`CurrencyCode`, r.`EcomTxnType`, r.`EcomPayId`, r.`DevNote`,
    e.`EcomPayLink`
  FROM `wtkRevenue` r
    INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
    LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = r.`UserUID`
    LEFT OUTER JOIN `wtkLookups` L ON L.`LookupValue` = r.`RevType` AND L.`LookupType` = 'RevType'
WHERE r.`UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
wtkSqlGetRow($pgSQL, [$gloId]);

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12">
        <h4>Revenue Detail <span class="right">$pgBtns</span></h4><br>
        <div class="card content b-shadow">
            <form id="FrevDIV" name="FrevDIV" method="POST">
                <span id="formMsg" class="red-text">$gloFormMsg</span>
                <div class="row">
htmVAR;

$pgPaymentProvider = wtkSqlValue('PaymentProvider');
$gloForceRO = true;
$pgHtm .= wtkFormText('wtkRevenue', 'PaymentProvider','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'PaymentStatus','text','','m4 s12');
$pgRevenueType = wtkSqlValue('RevenueType');
if ($pgRevenueType != ''):
    $pgHtm .= wtkFormText('wtkRevenue', 'RevenueType','text','','m4 s12');
else:
    $pgHtm .= '</div><div class="row">' . "\n";
endif;
$pgHtm .= wtkFormText('wtkRevenue', 'AssociatedUser','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'PayerEmail', 'email','','m4 s12');
if ($pgPaymentProvider == 'Checkout'):
    $pgHtm .= wtkFormText('wtkRevenue', 'PayerId','text','Action ID','m4 s12');
else:
    $pgHtm .= wtkFormText('wtkRevenue', 'PayerId','text','','m4 s12');
endif;

$pgHtm .= wtkFormText('wtkRevenue', 'FirstName','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'LastName','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'IPaddress','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'ItemName','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'ItemNumber','text','','m4 s12');
$pgOrderUID = wtkSqlValue('OrderUID');
if ($pgOrderUID != ''):
    $pgHtm .= wtkFormText('wtkRevenue', 'OrderUID','text','Order ID','m4 s12');
else:
    $pgHtm .= '</div><div class="row">' . "\n";
endif;
$pgHtm .= wtkFormText('wtkRevenue', 'GrossAmount','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'MerchantFee','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'CurrencyCode','text','','m4 s12');
$pgHtm .= wtkFormText('wtkRevenue', 'EcomTxnType','text','TxnType','m4 s12');
//if ($pgPaymentProvider == 'Checkout'):
    $pgEcomPayId = wtkSqlValue('EcomPayId');
    if ($pgEcomPayId != ''):
        $pgHtm .= wtkFormText('wtkRevenue', 'EcomPayId','text','Payment ID','m4 s11');
        $pgEcomPayLink = wtkSqlValue('EcomPayLink');
        if ($pgPaymentProvider == 'Checkout'):
            $pgPayLink = $pgEcomPayId;
        else:
            $pgPayerId = wtkSqlValue('EcomPayId'); // use this for Stripe
            $pgPayLink = $pgPayerId;
        endif;

        $pgHtm .=<<<htmVAR
<div class="col m1 s1">
    <a href="$pgEcomPayLink$pgPayLink" data-tooltip="use to refund or review" class="btn tooltipped" target="_blank">Stripe</a>
</div>
htmVAR;
    endif;
//endif;
$gloForceRO = false;
$pgHtm .= wtkFormTextArea('wtkRevenue', 'DevNote','Internal Note','s12');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', 'dashboard'); // since not updating anything visible in list, do not need ../../admin/revenueList.php

$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
            </form>
            <input type="hidden" id="HasTooltip" name="HasTooltip" value="Y">
        </div>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
