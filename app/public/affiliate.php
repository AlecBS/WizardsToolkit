<?PHP
// this is the page for Affiliates to be able to track their sales and visitors
$gloLoginRequired = false;
require('wtk/wtkLogin.php');

if ($gloRNG == ''):
    wtkSearchReplace('col m4 offset-m4 s12','col m6 offset-m3 s12');
    $pgHtm =<<<htmVAR
    <h3>Page accessed incorrectly</h3>
    <br><p>If you want to become an affiliate, <a href="/wtk/contactUs.php">contact us</a></p>
htmVAR;
    wtkMergePage($pgHtm, 'Error', _WTK_RootPATH . '/htm/minibox.htm');
endif;
$pgSqlFilter = array('Hash' => $gloRNG);

$pgSQL =<<<SQLVAR
SELECT COUNT(*)
 FROM `wtkAffiliates`
WHERE fncWTKhash(`UID`) = :Hash
SQLVAR;
$pgCount = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

if ($pgCount == 0):
    wtkSearchReplace('col m4 offset-m4 s12','col m6 offset-m3 s12');
    $pgHtm =<<<htmVAR
    <h3>Page accessed incorrectly</h3>
    <br><p>If you want to become an affiliate, <a href="/wtk/contactUs.php">contact us</a></p>
htmVAR;
    wtkMergePage($pgHtm, 'Error', _WTK_RootPATH . '/htm/minibox.htm');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `CompanyName`, `ContactName`, `Email`, `MainPhone`,
  `Website`, `WebPasscode`, `CountryCode`,
   DATE_FORMAT(`SignedDate`,'$gloSqlDate') AS `SignedDate`,
  `LinkToURL`, `AffiliateHash`, `DiscountPercentage`, `AffiliateRate`,
  `PaymentInstructions`
FROM `wtkAffiliates`
WHERE fncWTKhash(`UID`) = :Hash
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
wtkSqlGetRow($pgSQL, $pgSqlFilter);

wtkPageProtect(wtkSqlValue('WebPasscode'));
$pgAffiliateUID = wtkSqlValue('UID');
$pgSqlFilter = array('AffiliateUID' => $pgAffiliateUID);

// BEGIN Visitor Data
$pgSQL =<<<SQLVAR
SELECT (SUM(`PagesB4Signup`) + SUM(`PagesAfterBuy`)) AS `TotalVisits`,
  COUNT(DISTINCT(`IPaddress`)) AS `UniqueVisitors`,
  MIN(`AddDate`) AS `FirstDate`,
  MAX(`AddDate`) AS `LastDate`
FROM `wtkVisitors`
WHERE `AffiliateUID` = :AffiliateUID
SQLVAR;
$pgVisitPDO = $gloWTKobjConn->prepare($pgSQL);
$pgVisitPDO->execute($pgSqlFilter);
$pgPDOrow = $pgVisitPDO->fetch(PDO::FETCH_ASSOC);
if (!is_array($pgPDOrow)):
    $pgFirstVisit = 'not yet';
    $pgTotalVisits = 0;
    $pgUniqueVisits = 0;
    $pgLastVisit = 'not yet';
else:
    $pgUniqueVisits = $pgPDOrow['UniqueVisitors'];
    if ($pgUniqueVisits == 0):
        $pgTotalVisits = 0;
        $pgFirstVisit = 'not yet';
        $pgLastVisit = 'not yet';
    else:
        $pgTotalVisits = $pgPDOrow['TotalVisits'];
        $pgFirstVisit = date($gloPhpDateTime,strtotime($pgPDOrow['FirstDate']));
        $pgLastVisit = date($gloPhpDateTime,strtotime($pgPDOrow['LastDate']));
    endif;
endif;
//  END  Visitor Data

// BEGIN Visitor and Commission Statistics
$pgDiscountPercentage = wtkSqlValue('DiscountPercentage');
$pgDiscountPercentage = wtkReplace($pgDiscountPercentage, '.00','');
$pgAffiliateRate = wtkSqlValue('AffiliateRate');
$pgAffiliateRate = wtkReplace($pgAffiliateRate, '.00','');
$pgLinkToURL = wtkSqlValue('LinkToURL');
$pgAffiliateHash = wtkSqlValue('AffiliateHash');

$pgMyLink  = '<a target="_blank" href="' . $pgLinkToURL . $pgAffiliateHash . '">';
$pgMyLink .= $pgLinkToURL . $pgAffiliateHash . '</a>';

$pgSQL =<<<SQLVAR
SELECT `GrossAmount` AS `Amount`, `CurrencyCode` AS `Currency`,
    CONCAT(`AffiliateRate`,'%') AS `Commission`,
   FORMAT(((`GrossAmount` * `AffiliateRate`)/100),2) AS `Earned`
FROM `wtkRevenue`
WHERE `AffiliateUID` = :AffiliateUID
  AND `PaymentStatus` = 'Paid'
SQLVAR;
$gloColumnAlignArray = array (
    'Amount' => 'right',
    'Currency' => 'center',
    'Commission' => 'center',
	'Earned' => 'right'
);
$gloTotalArray = array (
	'Earned' => 'SUM'
);

$pgRevenueList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkRevenue');
$pgRevenueList = wtkReplace($pgRevenueList, 'No data.','none yet');
$pgRevenueList = wtkReplace($pgRevenueList, '.00%','%');
$pgRevenueList = wtkReplace($pgRevenueList, '.50%','.5%');
$pgRevenueList = wtkReplace($pgRevenueList, '<table','<table style="max-width:360px"');

$pgSignedDate = wtkSqlValue('SignedDate');
if ($pgSignedDate == ''):
    wtkSqlExec('UPDATE `wtkAffiliates` SET `SignedDate` = NOW() WHERE `UID` = :AffiliateUID', $pgSqlFilter);
    $pgSignedDate = date($gloPhpDateTime);
endif;

$pgNewAffiliate = wtkGetSession('AffiliateCongrats');
if ($pgNewAffiliate == 'Y'):
    $pgCongratsMsg =<<<htmVAR
    <h3>Thank you for becoming an Affiliate!</h3>
    <p>Welcome to Wizard&rsquo;s Toolkit, the low-code development library.
      <br>PHP, SQL and JavaScript for data-driven websites.</p>
htmVAR;
    wtkDeleteSession('AffiliateCongrats');
else:
    $pgCongratsMsg = '';
endif;

$pgStatsBox =<<<htmVAR
    <div class="card">
        <div class="card-content">
            <h5>Commissions
                <small class="right" style="font-size:14px !important">started $pgSignedDate</small>
            </h5>
            <p>Using your link, customers receive a $pgDiscountPercentage%
             discount on services purchased from $gloCoName.
             Your commission rate is $pgAffiliateRate%.</p>
            <br>
            <div class="row">
                <div class="col m7 s12">
                    <table class="z-depth-2 table-basic"><tr>
                        <td style="padding:5px 15px">
                        <div class="center"><br><h6>Earnings</h6></div>
                        $pgRevenueList
                        <br>
                    </td></tr></table>
                </div>
                <div class="col m5 s12">
                    <table class="z-depth-2 table-basic"><tr>
                        <td style="padding:5px 15px">
                            <div class="center"><br><h6>Visitor Stats</h6></div>
                            <br><br>
                            <table class="striped">
                                <tr><td>Unique Visitors:</td><td class="center">$pgUniqueVisits</td></tr>
                                <tr><td>Total Visits:</td><td class="center">$pgTotalVisits</td></tr>
                                <tr><td>First visit date:</td><td class="center">$pgFirstVisit</td></tr>
                                <tr><td>Last visit date:</td><td class="center">$pgLastVisit</td></tr>
                            </table>
                    </td></tr></table>
                </div>
            </div>
        </div>
    </div>
htmVAR;
//  END  Visitor and Commission Statistics

$pgHtm =<<<htmVAR
    <br>
    <div class="card b-shadow">
        <div class="card-content">
            <h4>Affiliate of&nbsp; <a target="_blank" href="/">$gloCoName</a>
                <small class="right"><a href="wtk/contactUs.php">Contact Us</a></small>
            </h4>
            <p>Advertise this link to earn commissions: $pgMyLink</p>
            <div class="card green accent-2">
                <div class="card-content">
                    $pgCongratsMsg
                    <p>Whether you just want the $pgAffiliateRate% commissions for referrals,
                      or you plan on learning how to use WTK for your clients so you can
                      earn high hourly rates while using the most intuitive rapid
                      application development... we are here for you!
                      Note: <a target="_blank" href="/services.php">Startup Packages</a> are capped at 10% commission.</p>
                    <p>When you succeed, we succeed. Let us know how we can help you succeed!</p>
                </div>
            </div>
            <br>
            <form id="wtkForm" name="wtkForm" method="POST">
                <span id="formMsg" class="red-text">$gloFormMsg</span>
                <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkAffiliates', 'CompanyName','text','','m6 s12', 'N', 'will show on sales pages');
$pgHtm .= wtkFormText('wtkAffiliates', 'ContactName');
$pgHtm .= '</div><div class="row">' . "\n";
$pgHtm .= wtkFormText('wtkAffiliates', 'Email', 'email','Contact Email','m8 s12');
$pgHtm .= wtkFormText('wtkAffiliates', 'WebPasscode','text','Passcode','m4 s12', 'N','to access this page');
$pgHtm .= wtkFormTextArea('wtkAffiliates', 'PaymentInstructions','','s12','N','what is best way to pay you');

$pgHtm .= '</div><div class="row">' . "\n";
$pgHtm .= '<div class="col s12">' . $pgStatsBox . "\n";
$pgHtm .= '<br><br><h5>Optional Information</h5></div>' . "\n";
$pgHtm .= wtkFormText('wtkAffiliates', 'Website','text','Your Website','m4 s12');
$pgHtm .= wtkFormText('wtkAffiliates', 'MainPhone', 'tel','Main Phone','m4 s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'Country' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkAffiliates', 'CountryCode', $pgSQL, [], 'LookupDisplay', 'LookupValue','Country','m4 s12','Y');

$pgHtm .= wtkFormHidden('ID1', $pgAffiliateUID);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', 'EDIT');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
                <div class="center">
                    <a class="waves-effect waves-light btn-large green" onclick="JavaScript:saveAffiliate()">Save</a>
                </div>
            </form>
            <p>After a prospect uses your link, any purchase they make
                within the next 90 days will be credited to your account.</p>
        </div>
    </div>
    <br>
<script type="text/javascript">
function saveAffiliate(){
    waitLoad('on');
    let fncFormData = $('#wtkForm').serialize();
    $.ajax({
        type: 'POST',
        url: '/wtk/ajxSaveAffiliate.php',
        data: (fncFormData),
        success: function(data) {
            waitLoad('off');
            let fncJSON = $.parseJSON(data);
            if (fncJSON.result == 'ok'){
                M.toast({html: 'Information saved', classes: 'rounded green'});
            }
        }
    })
}
</script>
htmVAR;
wtkSearchReplace('wtkBlue.css','wtkGreen.css');
wtkMergePage($pgHtm, 'Wizards Toolkit', 'wtk/htm/mpa.htm');
?>
