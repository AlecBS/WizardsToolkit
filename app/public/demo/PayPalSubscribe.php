<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSiteDesign = 'MPA';

$pgHtm  = '<h4>PayPal</h4><h5>Subscribe Button</h5><br><br>' . "\n\n";

if ($gloPayPalEmail == 'buy@yourDomain.com'):
    $pgHtm .= 'Must set $gloPayPalEmail to an email address associated with a PayPal account.' . "\n";
    $pgHtm .= '<br><br>$gloPayPalEmail should be defined in your wtk/wtkServerInfo.php.' . "\n";
else:
    $pgHtm .= wtkSubscribePayPal("Wizard's Toolkit Subscription", 480, 'WTK1Year', 1, 'Y', 'CC_LG');
endif;

/*
All PayPal functions are located in /wtk/lib/PayPal.php

wtkSubscribePayPal($fncItemName, $fncPrice, $fncItemUID = '', $fncFrequency = 1, $fncTerm = 'M', $fncBtnType = 'CC_LG')
Requires you have following files deployed in your webserver:
    wtk/payPalIPN.php
    wtk/payPalReturn.php
*/

wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
