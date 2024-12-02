<?PHP
/**
* This contains all Wizard's Toolkit functions that involve PayPal ecommerce.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2024, All rights reserved.
* @link        Official website: https://wizardstoolkit.com
* @version     2.0
*/

/**
* wtkPayPalOneItem
*
* This uses 2021 JS code that PayPal recommends using paypal-js.  Must include in spa.htm like:
* <script type="text/javascript" src="https://unpkg.com/@paypal/paypal-js@4.1.0/dist/iife/paypal-js.min.js" defer></script>
*
* gloPayPalClientId must be defined in /wtk/js/wtkClientVars.js with your PayPal ClientID in order for this to work.
*
* @param string $fncItemName Name of the item being sold
* @param decimal $fncPrice
* @param string $fncPage this is passed to JS function wtkPayPal which passes it to cliPayPal
*    cliPayPal is defined in /wtk/js/wtkClientVars.js for your custom coding
* @param string $fncDescription optional, defaults to blank
* @param string $fncThanks optional: custom thanks message to show after successful purchase
* @param string $fncSKU optional
* @return html with JavaScript for PayPal to show purchase buttons
*/
function wtkPayPalOneItem($fncItemName, $fncPrice, $fncPage, $fncDescription = '',
        $fncThanks = '', $fncSKU = '', $fncCallCount = '', $fncSkipShipping = 'N'){

    $fncPrice = wtkReplace($fncPrice, '$','');
    $fncPrice = wtkReplace($fncPrice, ',','');
    if ($fncThanks != ''):
        $fncThanks = '<div id="paypal-thanks' . $fncCallCount . '" class="hide">' . $fncThanks . '</div>';
    endif;
    if ($fncSKU != ''):
        $fncSKU = '"sku": "' . $fncSKU . '",';
    endif;
    $fncCurrency = wtkGetCookie('wtkCurrency');
    if ($fncCurrency == ''):
        $fncCurrency = 'USD';
    endif;
    if ($fncSkipShipping == 'N'):
        $fncShipping = '';
    else:
        $fncShipping  = '"application_context": {' . "\n";
        $fncShipping .= '    "shipping_preference": "NO_SHIPPING"' . "\n";
        $fncShipping .= '},' . "\n";
    endif;
    $fncJSVarName = 'pgPayPalItem' . $fncCallCount;
    $fncHtm =<<<htmVAR
<div id="paypal-buttons$fncCallCount" class="center"></div>
$fncThanks
<script type="text/javascript">
var $fncJSVarName = {
  $fncShipping "purchase_units": [{
        "description": "$fncDescription",
        "amount": {
            "currency_code": "$fncCurrency",
            "value": "$fncPrice",
            "breakdown": {
               "item_total": {
                   "currency_code": "$fncCurrency",
                   "value": "$fncPrice"
               }
            }
        },
        "items": [ {
            "name": "$fncItemName",
            "description": "$fncDescription",
            $fncSKU
            "unit_amount": {
                 "currency_code": "$fncCurrency",
                 "value": "$fncPrice"
            },
            "quantity": "1"
        }]
    }]
}

wtkPayPal($fncJSVarName, $fncPrice, '$fncPage', '$fncCurrency');
</script>
htmVAR;
    return $fncHtm;
} // wtkPayPalOneItem

/**
* Shopping Cart
*
* Called from BrowsePDO function if gloMoreButtons includes:
* <code>$gloMoreButtons = array(<br>
*                'Ecommerce' => array(<br>
*                        'act' => 'ShoppingCart',<br>
*                        'img' => 'arrow-right'<br>
*                        )<br>
*                );<br>
* </code>
*
* This code was written in 2011 and should be retested to verify PayPal has not changed.
*
* @param string $fncItemName
* @param string $fncItemUID
* @param string $fncSkU
* @param string $fncPrice
* @param string $fncShipping
* @param string $fncCost optional: defaults to 0
* @return html with form for PayPal to add an item to a Shopping Cart
*/
function wtkShoppingCart($fncItemName, $fncItemUID, $fncSKU, $fncPrice, $fncShipping, $fncCost = '0') {
    global $gloPayPalEmail, $gloEcomServer, $gloTaxRate, $gloUserUID,
        $gloUPickUsed, $gloWebBaseURL;

    $fncReturn = $gloWebBaseURL . '/wtk/payPalReturn.php?why=thanks';
    $fncCancel = $gloWebBaseURL . '/wtk/payPalReturn.php?why=cancel';

    $fncItemName = wtkReplace($fncItemName, '"', "'");  // so double quotes in an item name do not mess up HTML

    $fncBtn  = '<form target="paypal" action="' . $gloEcomServer . '/cgi-bin/webscr" method="post">' . "\n";
//    $fncBtn .= '<input type="hidden" name="test_ipn" value="1">' . "\n";  // 2FIX remove when going live - this is for testing only
    $fncBtn .= '<input type="hidden" name="custom" value="U' . $gloUserUID . '">' . "\n";  // pass User's ID so we can get it back and not rely on session var
    $fncBtn .= '<input type="hidden" name="cmd" value="_cart">' . "\n";
    $fncBtn .= '<input type="hidden" name="return" value="' . $fncReturn . '">' . "\n";
//  $fncBtn .= '<input type="hidden" name="rm" value="2">' . "\n";  // to have PayPal return transaction info via Post
    $fncBtn .= '<input type="hidden" name="cancel_return" value="' . $fncCancel . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="business" value="' . $gloPayPalEmail . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="item_name" value="' . $fncItemName . '">' . "\n";
    if (!is_null($fncSKU) && $fncSKU != ''):
        $fncBtn .= '<input type="hidden" name="item_number" value="' . $fncSKU . '">' . "\n";
    endif;  // !is_null($fncSKU) && $fncSKU != '')
    $fncBtn .= '<input type="hidden" name="amount" value="' . $fncPrice . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="lc" value="US">' . "\n";
    $fncBtn .= '<input type="hidden" name="currency_code" value="USD">' . "\n";
// $fncBtn .= '<input type="hidden" name="button_subtype" value="products">' . "\n";
    $fncBtn .= '<input type="hidden" name="no_note" value="0">' . "\n";
    if ($gloTaxRate != '' && $gloTaxRate != '0.00'):
        $fncBtn .= '<input type="hidden" name="tax_rate" value="' . $gloTaxRate . '">' . "\n";
    endif;  // $gloTaxRate != '' && $gloTaxRate != '0.00'

    if (!is_null($fncShipping) && $fncShipping != '0.00'):
        $fncBtn .= '<input type="hidden" name="shipping" value="' . $fncShipping . '">' . "\n";
    endif;  // !is_null($fncShipping) && $fncShipping != '')

    $fncBtn .= '<input type="hidden" name="add" value="1">' . "\n";
/*
 // BEGIN  UPick droplist if applicable
    $fncSQL  = 'SELECT  u."Description" FROM  "bxItem_UPicks" x';
    $fncSQL .= ' LEFT OUTER JOIN "wtkInventory" u ON u."UID" = x."UPickUID"';
    $fncSQL .= ' WHERE  x."ItemUID" = ' . $fncItemUID;
    $fncSQL .= ' ORDER BY  x."Priority" ASC';
    $fncDropList = wtkGetSelectOptions($fncSQL, 'Description', 'Description','');
    if ($fncDropList != 'No records were found'):
        $fncBtn .= '<input type="hidden" name="on0" value="My Choice">';
        $fncDropStart  = '<table><tr><td><strong>Choose which:</strong></td></tr>' . "\n";
        $fncDropStart .= '<tr><td><select name="os0">' . "\n";
        $fncDropList   = $fncDropStart . $fncDropList;
        $fncDropList  .= '</select></td></tr></table>' . "\n";
        $fncBtn .= $fncDropList;
    endif;  // $fncDropList != ''
 //  END   UPick droplist if applicable
*/
// ABS 03/11/11 $fncBtn .= '<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHostedGuest">' . "\n";
    $fncBtn .= '<input type="image" src="/wtk/imgs/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' . "\n";
    $fncBtn .= '<img alt="" border="0" src="/wtk/imgs/pixel.gif" width="1" height="1">' . "\n";
    $fncBtn .= '</form>' . "\n";

    return $fncBtn;
}  // end of wtkShoppingCart

/**
* View Cart
*
* Will want to do something like this:
* <code>
* wtkSearchReplace('<!-- @MoreButtons@ -->', wtkViewCart());<br>
* </code>
*
* This code was written in 2011 and should be retested to verify PayPal has not changed.
*
* @return html with form for PayPal View Cart functionality
*/
function wtkViewCart() {
    global $gloPayPalEmail, $gloEcomServer;
    $fncResult  = '<form target="paypal" action="' . $gloEcomServer . '/cgi-bin/webscr" method="post">' . "\n";
    $fncResult .= '<input type="hidden" name="cmd" value="_cart">' . "\n";
    $fncResult .= '<input type="hidden" name="business" value="' . $gloPayPalEmail . '">' . "\n";
    $fncResult .= '<input type="hidden" name="display" value="1">' . "\n";
    $fncResult .= '<input type="image" src="/wtk/imgs/btn_viewcart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' . "\n";
    $fncResult .= '<img alt="" border="0" src="/wtk/imgs/pixel.gif" width="1" height="1">' . "\n";
    $fncResult .= '</form>';
    return $fncResult;
}  // end of wtkViewCart

/**
* Buy Now
*
* Pass in parameters to create a PayPal BuyNow button.
* This code was written in 2011 and should be retested to verify PayPal has not changed.
*
* @param string $fncItemName
* @param string $fncPrice
* @param string $fncItemUID Defaults blank, can be used for Inventory Droplist options (currently disabled)
* @param string $fncSkU Defaults blank, puts value in item_number PayPal field which wtkThanks.php can use for inventory handling
* @param string $fncShipping cost to be appended - defaults to 0
* @param string $fncShippingRequired defaults to 'N'; set to 'Y' if shipping is required
* @return html with form for PayPal Buy Now button and functionality
*/
function wtkBuyNow($fncItemName, $fncPrice, $fncItemUID = '', $fncSKU = '', $fncShipping = '0', $fncShippingRequired = 'N') {
    global $gloWebBaseURL, $gloPayPalEmail, $gloEcomServer, $gloTaxRate, $gloUserUID;

    $fncReturn = $gloWebBaseURL . '/wtk/payPalReturn.php?why=thanks';
    $fncCancel = $gloWebBaseURL . '/wtk/payPalReturn.php?why=cancel';

    $fncBtn  = '<div class="center">' . "\n";
    $fncBtn .= '<form action="' . $gloEcomServer . '/cgi-bin/webscr" method="post" id="ppForm" name="ppForm">' . "\n";
    $fncBtn .= '<input type="hidden" name="business" value="' . $gloPayPalEmail . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="return" value="' . $fncReturn . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="cancel_return" value="' . $fncCancel . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="item_name" value="' . $fncItemName . '">' . "\n";
    if ($fncSKU != ''):
        $fncBtn .= '<input type="hidden" name="item_number" value="' . $fncSKU . '">' . "\n";
    endif;  // $fncSKU != '')
    $fncBtn .= '<input type="hidden" name="amount" value="' . $fncPrice . '">' . "\n";
    if ($fncShipping > '0'):
        $fncBtn .= '<input type="hidden" name="shipping" value="' . $fncShipping . '">' . "\n";
    endif;  // $fncShipping > '')
    if ($gloTaxRate != '' && $gloTaxRate != '0.00'):
        $fncBtn .= '<input type="hidden" name="tax_rate" value="' . $gloTaxRate . '">' . "\n";
    endif;  // $gloTaxRate != '' && $gloTaxRate != '0.00'
    $fncBtn .= '<input type="hidden" name="custom" value="S' . $fncShippingRequired . 'U' . $gloUserUID . '">' . "\n";
        // pass Shipping requirement and User's ID so we can get it back and not rely on session var
    $fncBtn .= '<input type="hidden" name="cmd" value="_xclick">' . "\n";
// ABS 05/01/11      $fncBtn .= '<input type="hidden" name="notify_url" value="_xclick">' . "\n";       //2VERIFY
    $fncBtn .= '<input type="hidden" name="lc" value="US">' . "\n";
    $fncBtn .= '<input type="hidden" name="currency_code" value="USD">' . "\n";
    $fncBtn .= '<input type="hidden" name="cn" value="Add special instructions">' . "\n";
    $fncBtn .= '<input type="hidden" name="button_subtype" value="services">' . "\n";
    $fncBtn .= '<input type="hidden" name="no_note" value="0">' . "\n";
    $fncBtn .= '<input type="hidden" name="no_shipping" value="1">' . "\n";
    $fncBtn .= '<input type="hidden" name="rm" value="2">' . "\n"; // Return-Mode 2 makes it post back transaction data on completion
/* ----------------- ABS 08/28/13 -------------------
    // ABS 03/20/11  BEGIN  Inventory droplist if applicable
    $fncSQL  = 'SELECT  u."Description"';
    $fncSQL .= ' FROM "wtkInventory"';
    $fncSQL .= ' WHERE  u."ItemUID" = ' . $fncItemUID;
    $fncSQL .= ' ORDER BY  x."Priority" ASC LIMIT 1'; // ABS 08/29/13
    $fncDropList = wtkGetSelectOptions($fncSQL, 'Description', 'Description','');
    if ($fncDropList != 'No records were found'):
        $fncDropStart  = '<strong>Choose which:</strong><input type="hidden" name="on0" value="My Choice"></td>' . "\n";
        $fncDropStart .= '<td><select name="os0">' . "\n";
        $fncDropList   = $fncDropStart . $fncDropList;
        $fncDropList  .= '</select></td></tr>' . "\n";
        $fncDropList  .= '<tr><td colspan="2" align="right">' . "\n";
        $fncBtn .= $fncDropList;
    endif;  // $fncDropList != ''
    // ABS 03/20/11   END   UPick droplist if applicable
 --------------------------------------------------*/
    $fncBtn .= '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/cc-badges-ppmcvdam.png" alt="Buy now with PayPal" />';
    $fncBtn .= '<br><br><a href="JavaScript:document.ppForm.submit();">';
    $fncBtn .= '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-small.png" alt="Buy now with PayPal"/></a>' . "\n";
//    $fncBtn .= '<img alt="" border="0" src="/wtk/imgs/pixel.gif" width="1" height="1">' . "\n";
    $fncBtn .= '</form>' . "\n";
    $fncBtn .= '</div>' . "\n";

    return $fncBtn;
}  // end of wtkBuyNow

/**
* Buy Redirect
*
* Pass in parameters to create a PayPal BuyNow set of form fields then auto-submit those to go to PayPal page
* This can replace the BuyNow button function when additional processing needs to be done before posting to PayPal.
* The exact sampe parameters are used as the BuyNow button
*
* @param string $fncItemName
* @param string $fncPrice
* @param string $fncItemUID Defaults blank, can be used for Inventory Droplist options (currently disabled)
* @param string $fncSkU Defaults blank, puts value in item_number PayPal field which wtkThanks.php can use for inventory handling
* @param string $fncShipping cost to be appended - defaults to 0
* @return html with full page that includes wtkBuyNow and then submits it to PayPal
*/
function wtkBuyRedir($fncItemName, $fncPrice, $fncItemUID = '', $fncSKU = '', $fncShipping = '0') {
    wtkSearchReplace('<body', '<body onLoad="JavaScript:SendToPayPal();return false;"');
    $fncJS  = 'function SendToPayPal(){ document.ppForm.submit(); }' . "\n";
    wtkSearchReplace('/*wtk-JS code*/', $fncJS . '/*wtk-JS code*/');

    $fncHtm = wtkBuyNow($fncItemName, $fncPrice, $fncItemUID, $fncSKU, $fncShipping);
    $fncHtm = wtkReplace($fncHtm, '<img ','<img style="display: none;" ');
    $fncHtm .= 'Preparing purchase';
    wtkMergePage($fncHtm, $gloPageTitle);
}  // end of wtkBuyRedir

/**
* Pass in parameters to create a PayPal Subscribe button
*
* Pass in parameters to create a PayPal BuyNow button.
* This code was written in 2013 and verified still working as of 05/12/2022
*
* https://developer.paypal.com/docs/subscriptions/
*
* @param string $fncItemName
* @param string $fncPrice
* @param string $fncItemUID Defaults blank, can be used for Inventory Droplist options (currently disabled)
* @param string $fncFrequency defaults to 1
* @param string $fncTerm defaults to 'M' for monthly
* @param string $fncBtnType defaults to CC_LG
* @return html with form for PayPal Subscribe button and functionality
*/
function wtkSubscribePayPal($fncItemName, $fncPrice, $fncItemUID = '', $fncFrequency = 1, $fncTerm = 'M', $fncBtnType = 'CC_LG') {
    global $gloPayPalEmail, $gloUserUID, $gloWebBaseURL, $gloUserUID, $gloEcomServer;

    $fncReturn = $gloWebBaseURL . '/wtk/payPalReturn.php?why=thanks';
    $fncCancel = $gloWebBaseURL . '/wtk/payPalReturn.php?why=cancel';

    $fncBtn  = '<table style="border-collapse:initial"><tr><td class="center">' . "\n";
    $fncBtn .= '<form action="' . $gloEcomServer . '/cgi-bin/webscr" method="post" id="ppForm' . $fncItemUID . '" name="ppForm' . $fncItemUID . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="business" value="' . $gloPayPalEmail . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="return" value="' . $fncReturn . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="cancel_return" value="' . $fncCancel . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="item_name" value="' . $fncItemName . '">' . "\n";
    if (!is_null($fncItemUID) && $fncItemUID != ''):
        $fncBtn .= '<input type="hidden" name="item_number" value="' . $fncItemUID . '">' . "\n";
    endif;  // !is_null($fncSKU) && $fncSKU != '')
    $fncBtn .= '<input type="hidden" name="custom" value="U' . $gloUserUID . '">' . "\n";  // pass User's ID so we can get it back and not rely on session var

    $fncBtn .= '<input type="hidden" name="button_subtype" value="services">' . "\n";  // 2VERIFY
    $fncBtn .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">' . "\n";
    $fncBtn .= '<input type="hidden" name="lc" value="US">' . "\n";                    // 2VERIFY
    $fncBtn .= '<input type="hidden" name="currency_code" value="USD">' . "\n";
//  $fncBtn .= '<input type="hidden" name="cn" value="Add special instructions">' . "\n";
    $fncBtn .= '<input type="hidden" name="no_note" value="1">' . "\n";
    $fncBtn .= '<input type="hidden" name="no_shipping" value="1">' . "\n";
    $fncBtn .= '<input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHosted">' . "\n";
    /*  Subscription-specific fields - BEGIN  */
    $fncBtn .= '<input type="hidden" name="src" value="1">' . "\n";   // Set recurring payments until canceled.
    $fncBtn .= '<input type="hidden" name="a3" value="' . $fncPrice . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="p3" value="' . $fncFrequency . '">' . "\n";
    $fncBtn .= '<input type="hidden" name="t3" value="' . $fncTerm . '">' . "\n";
// ABS 07/06/13      $fncBtn .= '<input type="hidden" name="modify" value="2">' . "\n";  // Let current subscribers modify only.
    /*  Subscription-specific fields -  END   */
    $fncBtn .= '<input type="hidden" name="rm" value="2">' . "\n";   // Return-Mode 2 makes it post back transaction data on completion
    $fncBtn .= '<a href="JavaScript:document.ppForm' . $fncItemUID . '.submit();"><img alt="PayPal - The safer, easier way to pay online!" border="0"';
    $fncBtn .= ' src="/wtk/imgs/btn_subscribe' . $fncBtnType . '.gif"></a>' . "\n";
    $fncBtn .= '<img alt="" border="0" src="/wtk/imgs/pixel.gif" width="1" height="1">' . "\n";
    $fncBtn .= '</form>' . "\n";
    $fncBtn .= '</td></tr></table>' . "\n";

    return $fncBtn;
}  // end of wtkSubscribePayPal

/**
* Creates Unsubscribe button for PayPal
*
* Uses PayPal email address defined in wtkServerInfo.php.
* This code was written years ago and should be retested.
*
* @global string $gloPayPalEmail
* @return html for PayPal Unsubscribe button
*/
function wtkUnSubscribePayPal() {
    global $gloPayPalEmail, $gloEcomServer;
    $fncResult  = '<a href="' . $gloEcomServer . '/cgi-bin/webscr?cmd=_subscr-find&alias=' . wtkReplace(urlencode($gloPayPalEmail), '.','%2e') . '">' . "\n";
    $fncResult .= '<img src="https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif" border="0"></a>';
    return $fncResult;
}  // end of wtkUnSubscribePayPal
?>
