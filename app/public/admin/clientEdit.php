<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('/clientEdit.php', $gloId);

$pgSQL =<<<SQLVAR
SELECT `UID`, `ClientName`, `ClientPhone`,
    `Address`, `Address2`, `City`, `State`, `Zipcode`,
    `CountryCode`, `ClientEmail`, `AccountEmail`,
    `StartDate`, `ClientStatus`, `InternalNote`
  FROM `wtkClients`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Client</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkClients', 'ClientName','text','','s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'ClientStatus' ORDER BY `UID` ASC";
$pgHtm .= wtkFormSelect('wtkClients', 'ClientStatus', $pgSQL, [], 'LookupDisplay', 'LookupValue', '', 'm4 s12');
$pgHtm .= wtkFormText('wtkClients', 'StartDate', 'date', '', 'm4 s12');
$pgHtm .= wtkFormText('wtkClients', 'ClientPhone', 'text', 'Main Phone', 'm4 s12');

$pgHtm .= wtkFormText('wtkClients', 'Address');
$pgHtm .= wtkFormText('wtkClients', 'Address2');

$pgHtm .= wtkFormText('wtkClients', 'City', 'text', 'City', 'm5 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAstate' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkClients', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue','State','m4 s12','Y');
$pgHtm .= wtkFormText('wtkClients', 'Zipcode', 'number', 'Zipcode', 'm3 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'Country' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkClients', 'CountryCode', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s12','Y');

$pgHtm .= wtkFormText('wtkClients', 'ClientEmail', 'email', 'Main Email', 'm4 s12');
$pgHtm .= wtkFormText('wtkClients', 'AccountEmail', 'email', 'Accounting Email', 'm4 s12');

$pgHtm .= wtkFormTextArea('wtkClients', 'InternalNote', '', 's12');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/clientList.php');
//$pgHtm .= wtkFormPrimeField('wtkClients', 'ParentUID', $gloRNG);
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
