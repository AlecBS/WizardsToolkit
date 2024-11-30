<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
    $gloForceRO = wtkPageReadOnlyCheck('/admin/companyEdit.php', 1);
else:
    $gloFormMsg = 'Your data has been saved.';
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `CoName`, `PayPalEmail`, `DomainName`, `AppVersion`, `EnableLockout`
  FROM `wtkCompanySettings`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [1]);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Company Setting</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
if ($gloFormMsg == 'Your data has been saved.'):
    $pgHtm = wtkReplace($pgHtm, 'class="red-text"','class="green-text"');
endif;

$pgHtm .= wtkFormText('wtkCompanySettings', 'CoName', 'text', 'Company Name');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgTmp  = wtkFormText('wtkCompanySettings', 'PayPalEmail', 'email');
$pgHtm .= wtkReplace($pgTmp, 'Pay Pal','PayPal');
$pgHtm .= wtkFormText('wtkCompanySettings', 'DomainName');
$pgHtm .= wtkFormText('wtkCompanySettings', 'AppVersion');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('wtkCompanySettings', 'EnableLockout', '', $pgValues);
$pgHtm .= wtkFormHidden('ID1', 1);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/companyEdit.php');
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
