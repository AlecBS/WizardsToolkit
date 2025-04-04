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
                            <li>@CompanyName@ will become <strong>$gloCoName</strong>
                                <br>&nbsp;&nbsp;&nbsp; if sending to Affiliates, this will be their company name</li>
                            <li>@website@ will become <strong>$gloWebBaseURL</strong></li>
                            <li>@FirstName@ will use wtkUsers.`FirstName` if email code uses wtkPrepEmail function passing EmailCode and UserUID</li>
                            <li>@Date@ will become today&rsquo;s date like this: <strong>$pgDate</strong></li>
                            <li>@CurrentYear@ will become <strong>$pgCurrentYr</strong></li>
                            <li>@ProspectName@ if sending to Prospects will become the prospect&rsquo;s company name</li>
                            <li><br><strong>Affiliate Emailing</strong> includes these additional tokens:
                              <ul style="margin-left:18px">
                                <li>@CompanyName@ will use the affiliate company name, or contact if blank</li>
                                <li>@ContactName@ will use the affiliate contact name, or company if blank</li>
                                <li>@ToName@ will use the affiliate company name, or contact, or email (first non-blank)</li>
                                <li>@hash@ should be used to give them access to their custom page at: affiliate.php?rng=@hash@</li>
                                <li>@WebPasscode@ is their custom pass code to access their page</li>
                                <li>@DiscountPercentage@ is the Discount Percentage they offer to prospects</li>
                                <li>@AffiliateRate@ is the commission percentage they receive on sales</li>
                              </ul>
                            </li>
                        </ul>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
htmVAR;

// BEGIN check to see if company prefers WYSIWYG
$pgWYSIWYG = wtkSqlGetOneResult('SELECT `PreferWYSIWYG` FROM `wtkCompanySettings` WHERE `UID` = 1', []);
if ($pgWYSIWYG == 'Y'):
    $pgHtm .= '<input type="hidden" id="HasTinyMCE" name="HasTinyMCE" value="textarea#wtkwtkEmailTemplateEmailBody">';
endif;
//  END  check to see if company prefers WYSIWYG

echo $pgHtm;
exit;
?>
