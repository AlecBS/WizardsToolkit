<?php
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `FirstName`, `LastName`, `StaffRole`, `Email`,
    `AllowContact`, `DirectPhone`, `InternalNote`
  FROM `wtkProspectStaff`
WHERE `UID` = ?
SQLVAR;
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h5>User Administration</h5><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="post">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$pgHtm .= wtkFormText('wtkProspectStaff', 'FirstName');
$pgHtm .= wtkFormText('wtkProspectStaff', 'LastName');
$pgHtm .= wtkFormText('wtkProspectStaff', 'Email', 'email');
$pgHtm .= wtkFormText('wtkProspectStaff', 'StaffRole');

$pgHtm .= wtkFormText('wtkProspectStaff', 'DirectPhone', 'tel');
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkProspectStaff', 'AllowContact', 'Allow Contact',$pgValues,'m6 s12');

$pgHtm .= wtkFormTextArea('wtkProspectStaff', 'InternalNote');

$pgHtm .= wtkFormPrimeField('wtkProspectStaff', 'ProspectUID', $gloRNG);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/prospectEdit.php');
// $pgHtm .= wtkFormHidden('Debug', 'Y');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $gloRNG);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= '</div>' . "\n";
//$pgHtm .= '<div class="row">' . "\n";
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
