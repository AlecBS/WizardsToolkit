<?PHP
$pgSecurityLevel = 95;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgPlanId = wtkGetPost('id');
$pgQty = wtkGetPost('qty');
$pgCodeLength = wtkGetPost('codeLength');

$pgSelSQL = 'SELECT COUNT(*) FROM `wtkPromoCodes` WHERE `PromoPlanUID` = :PlanUID AND `PromoCode` = :PromoCode';

$pgSQL = 'INSERT INTO `wtkPromoCodes` (`PromoPlanUID`, `PromoCode`) VALUES (:PlanUID, :PromoCode)';
$pgSqlFilter = array (
    'PlanUID' => $pgPlanId
);
$pgSkipCntr = 0;
$pgCntr = 1;
while ($pgCntr <= $pgQty):
    $pgCode = wtkGeneratePassword($pgCodeLength,'N');
    $pgSqlFilter['PromoCode'] = $pgCode;
    $pgExists = wtkSqlGetOneResult($pgSelSQL, $pgSqlFilter);
    if ($pgExists == 0):
        $pgCntr ++;
        wtkSqlExec($pgSQL, $pgSqlFilter);
    else:
        $pgSkipCntr ++;
    endif;
endwhile;

$pgCodeCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkPromoCodes` WHERE `PromoPlanUID` = ?', [$pgPlanId]);
$pgHtm  = '<h3>' . $pgCodeCount . ' Promo Codes</h3>' . "\n";
$pgHtm .= '<p>Created ' . ($pgCntr - 1) . ' promo codes.</p>' . "\n";
if ($pgSkipCntr > 0):
    $pgHtm .= '<p>Skipped to prevent duplicates:  ' . $pgSkipCntr . '.</p>' . "\n";
endif;
if ($pgCodeCount > 30):
    $pgHtm .= '<p>Below are the top 30</p>' . "\n";
endif;
$pgSQL =<<<SQLVAR
SELECT `PromoCode`, `RedeemDate`, CONCAT('$',`GrossValue`) AS `GrossValue`,
    CONCAT('$',`NetValue`) AS `NetValue`
 FROM `wtkPromoCodes` WHERE `PromoPlanUID` = ?
ORDER BY `RedeemDate` DESC LIMIT 30
SQLVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [$pgPlanId], 'wtkPromoCodes');

echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
