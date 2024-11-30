<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('/admin/broadcastEdit.php', $gloId);

$pgSQL =<<<SQLVAR
SELECT `UID`, `AddedByUserUID`, `AudienceType`, `MessageType`, `BroadcastColor`,`TextColor`,
  `MessageHeader`,`MessageNote`,`ShowOnDate`,`ShowUntilDate`,`AllowClose`,`CloseMessage`
  FROM `wtkBroadcast`
WHERE `UID` = :UID
SQLVAR;
if ($gloWTKmode != 'ADD'):
    $pgSqlFilter = array (
        'UID' => $gloId
    );
    wtkSqlGetRow($pgSQL, $pgSqlFilter);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Broadcast</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$pgHtm .= wtkFormPrimeField('wtkBroadcast', 'AddedByUserUID', $gloUserUID);

if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'AudienceType' ORDER BY `UID` ASC";
$pgHtm .= wtkFormSelect('wtkBroadcast', 'AudienceType', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');

// MessageType may not be needed but is available if you need additional broadcast filtering
// $pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'MessageType' ORDER BY `UID` ASC";
// $pgHtm .= wtkFormSelect('wtkBroadcast', 'MessageType', $pgSQL, [], 'LookupDisplay', 'LookupValue');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'BroadcastColor' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkBroadcast', 'BroadcastColor', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'TextColor' ORDER BY `UID` ASC";
$pgHtm .= wtkFormSelect('wtkBroadcast', 'TextColor', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12');

$pgHtm .= wtkFormText('wtkBroadcast', 'MessageHeader','text','','m12 s12');
$pgHtm .= wtkFormTextArea('wtkBroadcast', 'MessageNote');
$pgHtm .= wtkFormText('wtkBroadcast', 'ShowOnDate', 'date','','m3 s12');
$pgHtm .= wtkFormText('wtkBroadcast', 'ShowUntilDate', 'date','','m3 s12');
$pgValues = array(
    'Yes' => 'Y',
    'No' => 'N'
);
$pgTmp = wtkFormRadio('wtkBroadcast', 'AllowClose', '', $pgValues,'m3 s12');
if ($gloWTKmode == 'ADD'):
    $pgTmp = wtkReplace($pgTmp, 'value="Y"','value="Y" checked');
endif;
$pgHtm .= $pgTmp;
$pgHtm .= wtkFormText('wtkBroadcast', 'CloseMessage','text','','m3 s12','Y','X, close, I agree, etc.');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/broadcastList.php');
//$pgHtm .= wtkFormPrimeField('wtkBroadcast', 'ParentUID', $gloRNG);
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
