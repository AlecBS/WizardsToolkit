<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `AddDate`, `IPaddress`, `FailCode`, `LockUntil`, `BlockedCount`
  FROM `wtkLockoutUntil`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/lockoutEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>IP Address Lockout</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkLockoutUntil', 'IPaddress');
$pgTmp  = wtkFormText('wtkLockoutUntil', 'LockUntil', 'date');
$pgHtm .= wtkReplace($pgTmp, 'value=""', 'value="2030/01/01"');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'FailCode' ORDER BY `LookupDisplay` ASC";
$pgTmp  = wtkFormSelect('wtkLockoutUntil', 'FailCode', $pgSQL, [], 'LookupDisplay', 'LookupValue','Reason', 'm4 s12');
$pgHtm .= wtkReplace($pgTmp, 'value="Hack"', 'value="Hack" selected');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'AbuseIPCode' ORDER BY `LookupDisplay` ASC";
$pgAbuseCodes = wtkGetSelectOptions($pgSQL, [], 'LookupDisplay', 'LookupValue', 15);
$pgHtm .=<<<htmVAR
<div class="input-field col m4 s12">
    <select id="AbuseIPDB" name="AbuseIPDB">
        <option value=""></option>
        $pgAbuseCodes
    </select>
    <label for="AbuseIPDB" class="active">Abuse IP Code</label>
    <span class="helper-text">if set then will automatically post to AbuseIPDB</span>
</div>
htmVAR;

$pgTmp  = wtkFormText('wtkLockoutUntil', 'BlockedCount','number', 'Blocked Count', 'm4 s12');
$pgTmp  = wtkReplace($pgTmp, '<label ', '<label class="active" ');
$pgHtm .= wtkReplace($pgTmp, 'value=""', 'value="1"');

if ($gloWTKmode == 'MODAL'):
    $pgHtm .=<<<htmVAR
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="center">
           <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Return</button>
        </div>
    </div>
</div>
htmVAR;
else:
    $pgHtm .= wtkFormHidden('ID1', $gloId);
    $pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
    $pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
    $pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/lockoutList.php');
    $pgHtm .= '            </div>' . "\n";
    $pgHtm .= wtkUpdateBtns('wtkForm', '/wtk/lib/Save','Y') . "\n";
    $pgHtm .= wtkFormWriteUpdField();

    $pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
endif;

echo $pgHtm;
exit;
?>
