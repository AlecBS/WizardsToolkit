<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `CompanyName`, `ContactName`, `Email`, `MainPhone`, `Website`,
  `WebPasscode`, `CountryCode`, `SignedDate`,`LinkToURL`,
  `AffiliateHash`, `DiscountPercentage`, `AffiliateRate`,
  `PaymentInstructions`, `InternalNote`, fncWTKhash(`UID`) AS `Hash`
FROM `wtkAffiliates`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/affiliateEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgHash = wtkSqlValue('Hash');
    $pgHashLink  = '<small class="right"><a target="_blank" href="/affiliate.php?rng=';
    $pgHashLink .= $pgHash . '">/affiliate.php?rng=' . $pgHash . '</a></small>';
    $pgResetLink = '<p><a onclick="JavaScript:resetAffiliate(' . $gloId . ')">Reset Signed Date</a></p>' . "\n";
else:
    $pgHashLink = '';
    $pgResetLink = '';
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Affiliate
        $pgHashLink
    </h4>$pgResetLink<br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkAffiliates', 'CompanyName');
$pgHtm .= wtkFormText('wtkAffiliates', 'ContactName');

$pgHtm .= wtkFormText('wtkAffiliates', 'LinkToURL','text','Link to URL', 'm6 s12','Y');
$pgHtm .= wtkFormText('wtkAffiliates', 'AffiliateHash','text','Link ID', 'm2 s12','Y');
$pgHtm .= wtkFormText('wtkAffiliates', 'DiscountPercentage','number','Discount %','m2 s12','Y');
$pgHtm .= wtkFormText('wtkAffiliates', 'AffiliateRate','number','Commission','m2 s12','Y');

$pgHtm .= wtkFormText('wtkAffiliates', 'Email', 'email','Email','m4 s12','Y');
$pgHtm .= wtkFormText('wtkAffiliates', 'MainPhone', 'tel','MainPhone','m4 s12');
$pgHtm .= wtkFormText('wtkAffiliates', 'Website','text','Website','m4 s12');
$pgHtm .= wtkFormText('wtkAffiliates', 'WebPasscode','text','Web Passcode','m4 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'Country' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkAffiliates', 'CountryCode', $pgSQL, [], 'LookupDisplay', 'LookupValue','Country','m4 s12','Y');
$pgHtm .= wtkFormText('wtkAffiliates', 'SignedDate', 'date','','m4 s12');
$pgHtm .= wtkFormTextArea('wtkAffiliates', 'PaymentInstructions');
$pgHtm .= wtkFormTextArea('wtkAffiliates', 'InternalNote');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/affiliateList.php');
//$pgHtm .= wtkFormPrimeField('wtkAffiliates', 'ParentUID', $gloRNG);
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
