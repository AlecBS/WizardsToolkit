<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `EmailCode`, `EmailType`, `Subject`, `EmailBody`, `InternalNote`
  FROM `wtkEmailTemplate`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/emailTemplateEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Email Template</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'EmailType' ORDER BY `UID` ASC";
$pgHtm .= wtkFormSelect('wtkEmailTemplate', 'EmailType', $pgSQL, [], 'LookupDisplay', 'LookupValue');

if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgHtm .= wtkFormText('wtkEmailTemplate', 'EmailCode');

$pgHtm .= wtkFormText('wtkEmailTemplate', 'Subject','text','','s12');
$pgHtm .= wtkFormTextArea('wtkEmailTemplate', 'EmailBody');
$pgHtm .= wtkFormTextArea('wtkEmailTemplate', 'InternalNote');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/emailTemplates.php');
//$pgHtm .= wtkFormPrimeField('wtkEmailTemplate', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgCurrentYr = date('Y');
$pgDate = date('F jS, Y');
$pgHtm .=<<<htmVAR
            <div class="row">
                <div class="col s12">
                    <p>The following tokens will automatically be converted to actual values:
                        <ul>
                            <li>@CompanyName@ will become <strong>$gloCoName</strong></li>
                            <li>@website@ will become <strong>$gloWebBaseURL</strong></li>
                            <li>@Date@ will become today&rsquo;s date like this: <strong>$pgDate</strong></li>
                            <li>@CurrentYear@ will become <strong>$pgCurrentYr</strong></li>
                        </ul>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
