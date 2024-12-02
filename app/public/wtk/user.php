<?php
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
    $pgPath = '';
    $gloWTKmode = 'EDIT';
else: // from Save.php
    $pgPath = '../';
endif;

// do database lookup to retrieve values
$pgSQL =<<<SQLVAR
SELECT u.`FirstName`, u.`LastName`, u.`Email`, u.`Phone`, u.`CellPhone`,
    u.`FilePath`, u.`NewFileName`, u.`LoginCode`,
    u.`SecurityLevel`, u.`SMSEnabled`, u.`PersonalURL`
  FROM `wtkUsers` u
WHERE u.`UID` = :UID
SQLVAR;

$pgSqlFilter = array (
    'UID' => $gloUserUID
);
wtkSqlGetRow($pgSQL, $pgSqlFilter);

$pgHtm = wtkLoadInclude($pgPath . 'htm/user.htm');
// BEGIN Show link to see QR Code of personal URL
$pgPersonalURL = wtkSqlValue('PersonalURL');
if ($pgPersonalURL != ''):
    $pgQRlink = 'Personal Site: <a class="btn btn-floating" onclick="JavaScript:ajaxGo(\'showQRcode\')"><img src="imgs/qr_code.svg" style="margin-top:7px"></a>';
    $pgHtm = wtkReplace($pgHtm, '@CellPhone@</p>','@CellPhone@</p>' . "\n" . $pgQRlink);
endif;
//  END  Show link to see QR Code of personal URL

$pgHtm = wtkReplace($pgHtm, '@UID@', $gloUserUID);
$pgSMSEnabled = wtkSqlValue('SMSEnabled');
if ($pgSMSEnabled == 'Y'):
    $pgHtm = wtkReplace($pgHtm, '@SMSEnabled@','checked');
else:
    $pgHtm = wtkReplace($pgHtm, '@SMSEnabled@','');
endif;

$pgFilePath = wtkSqlValue('FilePath');
$pgNewFileName = wtkSqlValue('NewFileName');
if ($pgNewFileName != ''):
    $pgImgName = $pgFilePath . $pgNewFileName;
else:
    $pgImgName = '/wtk/imgs/noPhotoSmall.gif';
endif;
$pgHtm = wtkReplace($pgHtm, '@imgPreview@', $pgImgName);

$pgHtm = wtkDisplayData('FirstName', $pgHtm);
$pgHtm = wtkDisplayData('LastName', $pgHtm);
$pgHtm = wtkDisplayData('Email', $pgHtm);
$pgHtm = wtkDisplayData('LoginCode', $pgHtm);
$pgHtm = wtkDisplayData('Phone', $pgHtm, '', '<p>Phone: @Phone@</p>');
//$pgHtm = wtkDisplayData('CellPhone', $pgHtm, '', '<p>Cell: @CellPhone@</p>');
$pgPhone = wtkSqlValue('Phone');
$pgCellPhone = wtkSqlValue('CellPhone');
if (($pgPhone == '') && ($pgCellPhone == '')):
    $pgHtm = wtkReplace($pgHtm, 'content-info b-shadow card','hide');
endif;
if ($pgCellPhone == ''):
    $pgHtm = wtkReplace($pgHtm, '<p>Cell: @CellPhone@</p>','');
else:
    $pgHtm = wtkReplace($pgHtm, '@CellPhone@',$pgCellPhone);
endif;
$pgSecurityLevel = wtkSqlValue('SecurityLevel');
/*
if ($pgSecurityLevel <= 25):
    $pgNewFileName = wtkSqlValue('NewFileName');
    if ($pgNewFileName == ''):
        $pgTmp =<<<htmVAR
<br>
<div id="noPhotoDIV">
    <div class="chip red white-text">take photo to use app</div>
    <p class="red-text">Must be a face, cannot be the company logo.</p>
</div>
htmVAR;
        $pgHtm = wtkReplace($pgHtm, '<h4>', $pgTmp . '<h4>');
    endif;
endif;
*/

wtkProtoType($pgHtm);
echo $pgHtm;
wtkAddUserHistory('My Profile');
exit;
?>
