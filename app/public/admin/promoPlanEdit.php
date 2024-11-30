<?PHP
$pgSecurityLevel = 95;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PlanName`, `AgentUID`, `PlanNote`,
  CONCAT(`ExpireDate`, ' 00:00:00') AS `ExpireDate`, `GrossSales`, `NetSales`
  FROM `wtkPromoPlans`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/promoPlanEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h2>Update Promo Plan</h2><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkPromoPlans', 'PlanName');
$pgSQL  = "SELECT `UID`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'PromoAgent' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkPromoPlans', 'AgentUID', $pgSQL, [], 'LookupDisplay', 'UID');
$pgHtm .= wtkFormText('wtkPromoPlans', 'ExpireDate', 'date', 'Expire Date', 'm2 s12');
$pgHtm .= wtkFormText('wtkPromoPlans', 'GrossSales','number', 'Gross Sales', 'm4 s6');
$pgHtm .= wtkFormText('wtkPromoPlans', 'NetSales','number', 'Net Sales', 'm4 s6');
$pgHtm .= wtkFormTextArea('wtkPromoPlans', 'PlanNote');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/promoPlanList.php');
//$pgHtm .= wtkFormPrimeField('wtkPromoPlans', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

if ($gloWTKmode == 'ADD'):
    $pgHtm .= '</form></div></div>' . "\n";
else:

$pgHtm .=<<<htmVAR
        </form>
    </div><br>
    <div class="content card b-shadow">
        <div class="card-content">
            <form>
          <button id="showBtn" type="button" class="btn btn-save" onclick="JavaScript:showCodeMaker()">Generate Promo Codes!</button>
          &nbsp;&nbsp;&nbsp;
          <a href="exportPromoCodes.php?id=$gloId&apiKey=$pgApiKey" target="_blank" class="btn btn-save">Export Codes</a>
          <div id="codeMaker" class="row hide">
              <div class="col m5 s6">
                  <div class="input-field col m5 s12">
                    <input type="text" id="qty" value="1000">
                    <label for="qty" class="active">How Many Codes</label>
                  </div>
              </div>
              <div class="col m5 s6">
                  <div class="input-field col m5 s12">
                    <input type="text" id="codeLength" value="9">
                    <label for="codeLength" class="active">How Many Characters</label>
                  </div>
              </div>
              <div class="col m2 s6">
                 <button type="button" class="btn btn-save center" onclick="JavaScript:generatePromoCodes()">Generate!</button>
             </div>
          </div>
            </form>
        <div id="theCodes">
htmVAR;
$pgCodeCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkPromoCodes` WHERE `PromoPlanUID` = ?', [$gloId]);
$pgHtm .= '<h3>' . $pgCodeCount . ' Promo Codes</h3>' . "\n";
$pgSQL =<<<SQLVAR
SELECT `PromoCode`,  DATE_FORMAT(`RedeemDate`, '%c/%e/%Y at %l:%i %p') AS `RedeemDate`,
  CONCAT('$',`GrossValue`) AS `GrossValue`, CONCAT('$',`NetValue`) AS `NetValue`
 FROM `wtkPromoCodes` WHERE `PromoPlanUID` = :PlanUID
ORDER BY `RedeemDate` DESC LIMIT 30
SQLVAR;
$pgSqlFilter = array (
    'PlanUID' => $gloId
);
$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkPromoCodes');
$pgHtm .=<<<htmVAR
        </div>
    </div>
</div>
htmVAR;
endif; // Edit Mode

echo $pgHtm;
exit;
?>
