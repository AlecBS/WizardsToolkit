<?PHP
$pgSecurityLevel = 95;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloId = wtkGetParam('id');

$pgSQL =<<<SQLVAR
SELECT `PromoCode`
 FROM `wtkPromoCodes`
WHERE `PromoPlanUID` = :PlanUID
ORDER BY `UID` ASC
SQLVAR;
$pgSqlFilter = array (
    'PlanUID' => $gloId
);

$pgCodes = '';
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgCodes .= $gloPDOrow['PromoCode'] . "\n";
endwhile;
unset($pgPDO);

echo $pgCodes;
exit;
?>
