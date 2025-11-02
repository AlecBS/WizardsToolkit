<?PHP
/**
* Ecommerce functions for Wizard's Toolkit
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
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

function wtkAddItemToCart($fncType, $fncUID, $fncQty, $fncAddOnQty = 0) {
    global $gloUserUID;
    if ($fncPricePer == 0):
        $fncPricePer = NULL;
    endif;
    if ($fncUserUIDs == ''):
        $fncUserUIDs = 'NULL';
    endif;
    // Check to see if cart exists, if not then add and set a cookie
    list($fncShopUID, $fncTotalItems) = wtkCheckCart();
    if ($fncShopUID == 0): // none exists yet
        $fncIPAddress = wtkGetIPaddress();
        $fncSQL = 'INSERT INTO `wtkShoppingCart` (`IPAddress`,`UserUID`) VALUES (:IPAddress,:UserUID)';
        $fncSqlFilter = array(
            'UserUID' => $gloUserUID,
            'IPAddress' => $fncIPAddress
        );
        wtkSqlExec($fncSQL, $fncSqlFilter);
        $fncShopUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkShoppingCart` WHERE `UserUID` = ? ORDER BY `UID` DESC LIMIT 1', [$gloUserUID]);
        wtkSetCookie('wtkShopUID', $fncShopUID, '1year');
    else:
        $fncIPAddress = 'CartExists';
    endif;

    switch ($fncType):
        case 'Widgets':
            $fncTable = 'YourWidgets'; // your custom SQL tables
            break;
        case 'Item':
            $fncTable = 'wtkInventory';
            break;
        default:
            $fncTable = 'wtkInventory';
            break;
    endswitch;

    // If cart already exists, then check to see if cart item exists
    //   if so then increase quantity; if not then add
    if ($fncIPAddress == 'CartExists'):
        $fncSQL =<<<SQLVAR
SELECT `UID`
 FROM `wtkCartItems`
WHERE `ShopUID` = :ShopUID AND `TableName` = :TableName AND `TableUID` = :TableUID
SQLVAR;
        $fncSqlFilter = array(
            'ShopUID' => $fncShopUID,
            'TableName' => $fncTable,
            'TableUID' => $fncUID
        );
        $fncItemUID = wtkSqlGetOneResult($fncSQL, $fncSqlFilter, 0);
    else:
        $fncItemUID = 0;
    endif;
    if ($fncItemUID == 0):
        if ($fncQty > 0):
            $fncSQL =<<<SQLVAR
INSERT INTO `wtkCartItems` (`ShopUID`,`TableName`,`TableUID`,`BuyQty`,`PricePer`)
  VALUES (:ShopUID, :TableName, :TableUID, :BuyQty, :PricePer);
SQLVAR;
            $fncSqlFilter = array(
                'ShopUID' => $fncShopUID,
                'TableName' => $fncTable,
                'TableUID' => $fncUID,
                'BuyQty'  => $fncQty,
                'PricePer' => $fncPricePer
            );
        else:
            $fncSQL = '';
        endif;
    else: // currently only updating main item; not add-on items
        $fncSQL =<<<SQLVAR
UPDATE `wtkCartItems`
  SET `BuyQty` = :BuyQty, `PricePer` = :PricePer
WHERE `UID` = :UID
SQLVAR;
        $fncSqlFilter = array(
            'UID' => $fncItemUID,
            'BuyQty'  => $fncQty,
            'PricePer' => $fncPricePer
        );
    endif;
    if ($fncSQL != ''):
        wtkSqlExec($fncSQL, $fncSqlFilter);
    endif;
    // BEGIN Handle Add-On orders for Events
    if ($fncIPAddress == 'CartExists'): // check to see if already exists
        $fncSQL =<<<SQLVAR
SELECT `UID`
 FROM `wtkCartItems`
WHERE `ShopUID` = :ShopUID AND `TableName` = :TableName AND `TableUID` = :TableUID
    AND `SpecialAddOn` = 'Y'
SQLVAR;
        $fncSqlFilter = array(
            'ShopUID' => $fncShopUID,
            'TableName' => $fncTable,
            'TableUID' => $fncUID
        );
        $fncItemUID = wtkSqlGetOneResult($fncSQL, $fncSqlFilter, 0);
    else:
        $fncItemUID = 0;
    endif;
    if ($fncAddOnQty > 0):
        if ($fncItemUID == 0):
            $fncSQL =<<<SQLVAR
INSERT INTO `wtkCartItems` (`ShopUID`,`TableName`,`TableUID`,`SpecialAddOn`,`BuyQty`)
  VALUES (:ShopUID, :TableName, :TableUID, 'Y', :BuyQty)
SQLVAR;
            $fncSqlFilter = array(
                'ShopUID' => $fncShopUID,
                'TableName' => $fncTable,
                'TableUID' => $fncUID,
                'BuyQty'  => $fncAddOnQty
            );
        else:
            $fncSQL =<<<SQLVAR
UPDATE `wtkCartItems`
  SET `BuyQty` = :BuyQty
WHERE `UID` = :UID
SQLVAR;
            $fncSqlFilter = array(
                'UID' => $fncItemUID,
                'BuyQty'  => $fncAddOnQty
            );
        endif;
        wtkSqlExec($fncSQL, $fncSqlFilter);
    endif;
    //  END  Handle Add-On orders for Events
} // wtkAddItemToCart

function wtkCheckCart() {
    $fncShopUID = wtkGetCookie('wtkShopUID');
    // sometimes above does not work when returning from cancelled Stripe session
    if ($fncShopUID == ''):
        $fncShopUID = wtkGetParam('ShopUID');
    endif;
    if ($fncShopUID == ''):
        $fncShopUID = 0;
        $fncCount = 0;
    else:
        // if shopping cart already paid, remove from cookie
        $fncSqlFilter = array('ShopUID' => $fncShopUID);
        $fncSQL  = 'SELECT COUNT(*) FROM `wtkShoppingCart` WHERE `UID` = :ShopUID';
        $fncSQL .= " AND `ShoppingStatus` IN ('N','B')";
        $fncCount = wtkSqlGetOneResult($fncSQL, $fncSqlFilter);
        if ($fncCount == 0): // cart already paid for
            $fncShopUID = 0;
            $fncCount = 0;
            $fncShopUID = wtkDeleteCookie('wtkShopUID');
        else:
            $fncCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkCartItems` WHERE `ShopUID` = :ShopUID', $fncSqlFilter);
        endif;
    endif;
    return array($fncShopUID, $fncCount);
} // wtkCheckCart
?>
