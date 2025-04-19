<?php
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `FirstName`, `LastName`, `Email`, `City`, `Phone`, `CellPhone`, `WebPassword`,
    `SecurityLevel`, `StaffRole`, `FilePath`, `NewFileName`,
    `CanPrint`, `CanExport`, `CanEditHelp`, `UseSkype`, `SMSEnabled`,`OptInEmails`
  FROM `wtkUsers`
WHERE `UID` = ?
SQLVAR;
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/userAdminEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>User Administration</h4>
    <br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$pgHtm .= wtkFormText('wtkUsers', 'FirstName');
$pgHtm .= wtkFormText('wtkUsers', 'LastName');
$pgHtm .= wtkFormText('wtkUsers', 'Email', 'email');
$pgHtm .= wtkFormText('wtkUsers', 'City');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' AND `DelDate` IS NULL ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkUsers', 'SecurityLevel', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m6 s6');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'StaffRole' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkUsers', 'StaffRole', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m6 s6');

/*
$pgTmpMode = $gloWTKmode;
$gloWTKmode = 'ADD';
$pgTmp = wtkFormText('wtkUsers', 'WebPassword', 'password');
$pgTmp = wtkReplace($pgTmp, '<input type','<input onchange="JavaScript:checkPassStrength(this.value)" type');
$pgHtm .= $pgTmp;
$gloWTKmode = $pgTmpMode;
*/
$pgHtm .= wtkFormText('wtkUsers', 'Phone', 'tel');
$pgHtm .= wtkFormText('wtkUsers', 'CellPhone', 'tel');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('wtkUsers', 'CanPrint', 'Allowed to Print', $pgValues, 'm4 s12');
$pgHtm .= wtkFormCheckbox('wtkUsers', 'CanExport', 'Allowed to Export', $pgValues, 'm4 s12');
$pgHtm .= wtkFormCheckbox('wtkUsers', 'CanEditHelp', 'Allowed to Edit Help', $pgValues, 'm4 s12');
$pgHtm .= wtkFormCheckbox('wtkUsers', 'UseSkype', 'Use Skype for calls', $pgValues, 'm4 s12');
$pgHtm .= wtkFormCheckbox('wtkUsers', 'SMSEnabled', 'Allow SMS to Cell Phone', $pgValues, 'm4 s12');
$pgHtm .= wtkFormCheckbox('wtkUsers', 'OptInEmails', 'Email Opt-In', $pgValues, 'm4 s12');

$pgHtm .= wtkFormFile('wtkUsers','FilePath','/imgs/user/','NewFileName','User Photo','m6 s12');

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/userList.php');
// $pgHtm .= wtkFormHidden('Debug', 'Y');
$pgHtm .= wtkFormHidden('ID1', $gloId);
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

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
