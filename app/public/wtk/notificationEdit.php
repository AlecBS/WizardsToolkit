<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgTmp = wtkGetPost('Mode');
if ($pgTmp == 'ADD'):
    $gloWTKmode = 'ADD';
    $gloId = 0;
    $pgMode = 'Add';
else:
    $pgMode = 'Edit';
endif;

if (($gloWTKmode == 'ADD') && ($gloRNG > 0)): // create reminder for specific wtkUsers
    $pgSQL =<<<SQLVAR
SELECT CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Name`
  FROM `wtkUsers`
 WHERE `UID` = ?
SQLVAR;
    $pgUserName = wtkSqlGetOneResult($pgSQL, [$gloRNG]);
else:
    $pgUserName = '';
endif;

$pgSQL =<<<SQLVAR
SELECT `AddedByUserUID`,`NoteTitle`,`NoteMessage`,`StartDate`,
    `Audience`,`RepeatFrequency`,`ToUID`,`ToStaffRole`,`EmailAlso`,`SmsAlso`,
    DATE_FORMAT(`CloseDate`, '$gloSqlDateTime') AS `CloseDate`,
    `Icon`,`IconColor`,`GoToUrl`,`GoToId`,`GoToRng`
 FROM `wtkNotifications`
WHERE `UID` = :UID
SQLVAR;

$pgViewValue = '';
$pgFutureVisibility = ' hide';
$pgStartDateRequired = 'N';
$pgAltDelivery = ' hide';
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgCloseDate = wtkSqlValue('CloseDate');
    if ($pgCloseDate != ''):
        $gloForceRO = true;
        $pgMode = 'Delivered';
    endif;
    // BEGIN determine if repeater or future deliver
    $pgRepeatFrequency = wtkSqlValue('RepeatFrequency');
    $pgStartDate = wtkSqlValue('StartDate');
    if (($pgRepeatFrequency != 'N') || ($pgStartDate != '')):
        $pgViewValue = 'checked';
        $pgFutureVisibility = '';
        $pgStartDateRequired = 'Y';
        $pgAudience = wtkSqlValue('Audience');
        if ($pgAudience == 'S'):
            $pgAltDelivery = '';
        endif;
    endif;
    //  END  determine if repeater or future deliver
    $pgIconColor = wtkSqlValue('IconColor');
    $pgIcon = wtkSqlValue('Icon');
else:
    $pgCloseDate = '';
    $pgIconColor = 'green';
    $pgIcon = 'access_alarm';
    if ($pgUserName != ''): // adding for Researcher or Client
        $pgAltDelivery = '';
    endif;
endif;
$pgBtns = wtkModalUpdateBtns('wtk/lib/Save','notificationListDIV');

if ($gloDeviceType == 'phone'):
    $pgHtm  = '<form id="FnotificationListDIV" name="FnotificationListDIV" class="white">' . "\n";
    $pgHtm .= '    <div class="row">' . "\n";
else:
    $pgHtm =<<<htmVAR
<div class="modal-content">
    <h3>$pgMode Notification<span class="right">$pgBtns</span></h3>
  <form id="FnotificationListDIV" name="FnotificationListDIV">
    <br>
    <div class="card">
        <div class="card-content">
            <div class="row">
htmVAR;
endif;

// $pgHtm .= wtkReplace($pgTmp, '<input class','<input onchange="JavaScript:reminderToggle(this.value)" class');
if ($pgUserName == ''):
    $pgValues = array(
        'Staff' => 'S',
        'Department' => 'D'
    );
    $pgTmp  = wtkFormRadio('wtkNotifications', 'Audience', '', $pgValues);
    if ($gloWTKmode == 'ADD'):
        $pgTmp = wtkReplace($pgTmp, 'value="D"','value="D" CHECKED');
    endif;
    $pgHtm .= wtkReplace($pgTmp, '<input class','<input onchange="JavaScript:wtkNotificationAudience(this.value)" class');
endif;
// ToUID list will be shown here
$pgHtm .= '<div id="pickToUID">' . "\n";
if ($gloWTKmode == 'ADD'):
    if ($pgUserName != ''):
        $pgHtm .= wtkFormPrimeField('wtkNotifications', 'Audience', 'R');
        $pgHtm .= wtkFormPrimeField('wtkNotifications', 'ToUID', $gloRNG);
        $pgHtm .=<<<htmVAR
<div class="col m12 s12"><h4>Notification being created for $pgUserName</h4><br><br></div>
htmVAR;
    else: // not for specific user
        $pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay` AS `Display`
  FROM `wtkLookups`
 WHERE `LookupType` = :StaffRole
ORDER BY `UID` ASC
SQLVAR;
        $pgSqlFilter = array('StaffRole' => 'StaffRole');
        $pgHtm .= wtkFormSelect('wtkNotifications', 'ToStaffRole', $pgSQL, $pgSqlFilter, 'Display', 'LookupValue','Pick Department');
        wtkFormPrepUpdField('wtkNotifications', 'ToUID', 'text');
    endif;
else:
    $pgAudience = wtkSqlValue('Audience');
    switch ($pgAudience):
        case 'S':
            $pgSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Display`
  FROM `wtkUsers`
 WHERE `DelDate` IS NULL AND `SecurityLevel` > 20
ORDER BY `FirstName` ASC, `LastName` ASC
SQLVAR;
            $pgHtm .= wtkFormSelect('wtkNotifications', 'ToUID', $pgSQL, [], 'Display', 'UID','Pick Person');
            break;
        default:
            $pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay` AS `Display`
  FROM `wtkLookups`
 WHERE `LookupType` = :StaffRole
ORDER BY `UID` ASC
SQLVAR;
            $pgSqlFilter = array('StaffRole' => 'StaffRole');
            $pgHtm .= wtkFormSelect('wtkNotifications', 'ToStaffRole', $pgSQL, $pgSqlFilter, 'Display', 'LookupValue','Pick Department');
            break;
    endswitch;
endif;
$pgHtm .= '</div>' . "\n";
$pgHtm .= '</div>' . "\n";
$pgHtm .= '<div class="row">' . "\n";

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'IconColor' ORDER BY `UID` ASC";
$pgTmp  = wtkFormSelect('wtkNotifications', 'IconColor', $pgSQL, [], 'LookupDisplay', 'LookupValue', '', 'm5 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'Icon' ORDER BY `UID` ASC";
$pgTmp .= wtkFormSelect('wtkNotifications', 'Icon', $pgSQL, [], 'LookupDisplay', 'LookupValue', '', 'm5 s9');
$pgHtm .= wtkReplace($pgTmp, '<select ','<select onchange="JavaScript:wtkProofNotification();" ');
$pgHtm .=<<<htmVAR
<div class="col m1 s3">
    <span class="btn-floating btn-large $pgIconColor" id="proofIconColor"><i class="material-icons" id="proofIcon">$pgIcon</i></span>
</div>
htmVAR;
$pgTmp  = wtkFormText('wtkNotifications', 'NoteTitle','text','Title','m12 s12','Y');
$pgTmp  = wtkReplace($pgTmp, '<input required','<input data-length="240" class="char-cntr" required');
$pgHtm .= wtkReplace($pgTmp, '<label ','<label id="labelHeader"');

$pgTmp  = wtkFormTextArea('wtkNotifications', 'NoteMessage', 'Notification Message', 'm12 s12','Y');
$pgHtm .= wtkReplace($pgTmp, '<div class=', '<div id="NoteMessageDIV" class=');
$pgHtm .= '</div>' . "\n";

if ($pgCloseDate == ''):
    $pgHtm .=<<<htmVAR
<div class="row">
    <div class="col s12">
        <div class="switch">
            <label>Immediate and only once
              <input type="checkbox" name="showMore" $pgViewValue onclick="JavaScript:wtkShowNotificationAdvanced()" value="Y">
              <span class="lever"></span>Future Delivery or Auto-Repeat
            </label>
        </div>
    </div>
</div>
htmVAR;
endif;

$pgHtm .= '<div id="futureDateDIV" class="row' . $pgFutureVisibility . '">' . "\n";

$pgValues = array(
    'No repeat' => 'N',
    'Weekly' => 'W',
    'Monthly' => 'M'
);
$pgTmp  = wtkFormRadio('wtkNotifications', 'RepeatFrequency', '', $pgValues);
$pgHtm .= wtkReplace($pgTmp, 'value="N"','value="N" CHECKED');

$pgTmp  = wtkFormText('wtkNotifications', 'StartDate','datetime-local','Future Delivery Date','m6 s6',$pgStartDateRequired);
$pgTmp  = wtkReplace($pgTmp, '</label>','</label>leave blank to deliver now');
$pgHtm .= wtkReplace($pgTmp, '<label ','<label class="active" ');

// 2ENHANCE make sure Future Date is after NOW() according to SQL server

// BEGIN Allow Emailing and SMS notification also
$pgHtm .= '</div>' . "\n";
$pgHtm .= '<div class="row' . $pgAltDelivery . '" id="AltDelivery">' . "\n";
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkNotifications', 'EmailAlso', 'Also send Email?',$pgValues,'m6 s12');
$pgHtm .= wtkFormCheckbox('wtkNotifications', 'SmsAlso', 'Also send SMS?',$pgValues,'m6 s12');
$pgHtm .= '</div>' . "\n";
//  END  Allow Emailing and SMS notification also

if ($pgCloseDate != ''):
    $pgHtm .= '<div class="row">' . "\n";
    $pgHtm .= wtkFormText('wtkNotifications', 'CloseDate', 'text', 'Date Closed', 'm12 s12');
endif;

$pgHtm .= wtkFormPrimeField('wtkNotifications', 'AddedByUserUID', $gloUserUID);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('ID1', $gloId);
//$pgHtm .= wtkFormHidden('Debug', 'Y');
$pgHtm .= wtkFormHidden('wtkGoToURL', '../notificationList.php');
$pgHtm .= wtkFormWriteUpdField();
//$pgHtm .= wtkFormHidden('Debug', 'Y');
if ($gloDeviceType == 'phone'):
    $pgHtm .=<<<htmVAR
    </div>
</form>
<div id="modFooter" class="modal-footer right">
$pgBtns
</div>
htmVAR;
else:
    $pgHtm .= '</div></div></div>' . "\n";
    $pgHtm .= '</form></div>' . "\n";
endif;
$pgHtm .= wtkFormHidden('CharCntr', 'Y');

echo $pgHtm;
exit;
?>
