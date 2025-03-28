<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `MenuGroupUID`, `PgUID`, `Priority`, `ShowDividerAbove`
  FROM `wtkMenuItems`
WHERE `UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/menuItemEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $gloRNG = wtkSqlValue('MenuGroupUID');
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Menu Item</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkMenuItems', 'ShowDividerAbove', '',$pgValues,'s12');
$pgSQL  = 'SELECT `UID`, `PageName` FROM `wtkPages` ORDER BY `PageName` ASC';
$pgHtm .= wtkFormSelect('wtkMenuItems', 'PgUID', $pgSQL, [], 'PageName', 'UID', 'Page Name', 's12');

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/menuItemList.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $gloRNG);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormPrimeField('wtkMenuItems', 'MenuGroupUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
