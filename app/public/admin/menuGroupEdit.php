<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `MenuUID`, `GroupName`, `GroupURL`, `Priority`
  FROM `wtkMenuGroups`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/menuGroupEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgMenuUID = wtkSqlValue('MenuUID');
else:
    $pgMenuUID = $gloRNG;
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Menu Group</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkMenuGroups', 'GroupName');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;

$pgHtm .= wtkFormText('wtkMenuGroups', 'GroupURL','url','Group URL');
$pgHtm .= wtkFormText('wtkMenuGroups', 'Priority', 'number');

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/menuGroupList.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $pgMenuUID);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormPrimeField('wtkMenuGroups', 'MenuUID', $gloRNG);
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
