<?PHP
$pgSecurityLevel = 30;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('/admin/prospectEdit.php', $gloId);

if ($gloRNG > 0):
    $gloId = $gloRNG;
    $gloWTKmode = 'EDIT';
endif;
$pgSQL =<<<SQLVAR
SELECT `UID`, `CompanyName`, `Website`,
    `MainPhone`, `Website`, `Address1`,`Address2`, `City`, `State`, `Zipcode`,
    `LinkedIn`,`OtherSocial`,`MainEmail`,`TimeZone`,
    `FoundingYear`,`SICCode`,`B2BorB2C`, `CompanySize`,`NumberOfEmployees`,
    `AnnualSales`,`MonthlyWebsiteVisits`,`MonthlyWebsiteVisitsGrowth`,
    `CEOFirstName`,`CEOLastName`,`CEOEmail`,`CEOTwitter`,`CEOLinkedIn`,
    `FundingDate`,`FundingAmount`,`FundingType`,`FundingLink`,
    `ProspectStatus`, `InternalNote`, `Description`,`Technologies`
  FROM `wtkProspects` p
WHERE `UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    if ($gloId == 0):
        $gloId = wtkGetParam('ID1');
    endif;
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Prospect Company</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkProspects', 'CompanyName','text','', 'm8 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'ProspectStatus' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkProspects', 'ProspectStatus', $pgSQL, [], 'LookupDisplay', 'LookupValue','Prospect Status','m4 s12');

$pgHtm .= wtkFormText('wtkProspects', 'Address1', 'text', 'Address', 'm8 s12');
$pgHtm .= wtkFormText('wtkProspects', 'Address2', 'text', 'Suite or Unit', 'm4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'City', 'text', 'City', 'm5 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAstate' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkProspects', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue','State','m4 s12','Y');
$pgHtm .= wtkFormText('wtkProspects', 'Zipcode', 'text', 'Zipcode', 'm3 s12');

$pgHtm .= wtkFormText('wtkProspects', 'Website', 'text', '', 'm6 s12');
$pgHtm .= wtkFormText('wtkProspects', 'MainPhone', 'tel', 'Main Phone', 'm4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'TimeZone','text','','m2 s6');

$pgHtm .= wtkFormText('wtkProspects', 'LinkedIn', 'text', 'LinkedIn', 'm8 s12');
$pgHtm .= wtkFormText('wtkProspects', 'OtherSocial', 'text', '', 'm4 s12');

$pgHtm .= wtkFormText('wtkProspects', 'FoundingYear','number','','m3 s6');
$pgHtm .= wtkFormText('wtkProspects', 'B2BorB2C','text','B2B or B2C','m3 s6');
$pgHtm .= wtkFormText('wtkProspects', 'SICCode','text','SIC Code','m3 s6');

$pgHtm .= wtkFormText('wtkProspects', 'CompanySize','text','','m3 s6');
$pgHtm .= wtkFormText('wtkProspects', 'NumberOfEmployees','text','','m3 s6');
$pgHtm .= wtkFormText('wtkProspects', 'AnnualSales','text','','m3 s6');
$pgHtm .= wtkFormText('wtkProspects', 'MonthlyWebsiteVisits','text','Website Visits','m3 s6','N','Monthly');
$pgHtm .= wtkFormText('wtkProspects', 'MonthlyWebsiteVisitsGrowth','text','Website Visits Growth','m3 s6','N','Monthly');

$pgHtm .=<<<htmVAR
</div>
<div class="card">
    <div class="card-content">
        <h5>CEO Details</h5><br>
        <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkProspects', 'CEOFirstName','text','First Name','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'CEOLastName', 'text','Last Name','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'CEOEmail', 'email','CEO Email','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'CEOLinkedIn','text','CEO LinkedIn');
$pgHtm .= wtkFormText('wtkProspects', 'CEOTwitter','text','CEO Twitter');

$pgHtm .=<<<htmVAR
        </div>
    </div>
</div>
<br>
<div class="row">
htmVAR;
$pgHtm .=<<<htmVAR
</div>
<div class="card">
    <div class="card-content">
        <h5>Funding Details</h5><br>
        <div class="row">
htmVAR;
$pgHtm .= wtkFormText('wtkProspects', 'FundingType','text','','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'FundingDate', 'date','','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'FundingAmount','text','','m4 s12');
$pgFundingLink = wtkSqlValue('FundingLink');
if ($pgFundingLink != ''):
    $pgHtm .= '<div class="col s12">' . "\n";
    $pgHtm .= '  <a target="_blank" href="' . $pgFundingLink . '">' . $pgFundingLink . '</a>' . "\n";
    $pgHtm .= '</div>' . "\n";
endif;

$pgHtm .=<<<htmVAR
        </div>
    </div>
</div>
<br>
<div class="row">
htmVAR;

$pgHtm .= wtkFormTextArea('wtkProspects', 'InternalNote');
$pgHtm .= wtkFormTextArea('wtkProspects', 'Description');
$pgHtm .= wtkFormTextArea('wtkProspects', 'Technologies');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/prospectList.php');
//$pgHtm .= wtkFormPrimeField('wtkProspects', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= '</form>' . "\n";

if ($gloWTKmode != 'ADD'):
    $gloRNG = wtkSqlValue('UID');
    $pgSQL =<<<SQLVAR
SELECT s.`UID`, s.`FirstName`, s.`LastName`, s.`StaffRole`,
    CASE s.`AllowContact`
      WHEN 'Y' THEN 'Yes'
      ELSE '<i class="material-icons red-text small">cancel</i>'
    END AS `ContactAllowed`,
    `fncContactIcons`(s.`Email`,COALESCE(s.`DirectPhone`,''),0,0,'Y',s.`UID`,'N','N','') AS `Contact`,
    s.`Email`
  FROM `wtkProspectStaff` s
WHERE s.`ProspectUID` = :PropUID AND s.`DelDate` IS NULL
ORDER BY s.`FirstName` ASC, s.`LastName` ASC
SQLVAR;
    $pgSqlFilter = array ('PropUID' => $gloId);
    $gloColumnAlignArray = array (
      'ContactAllowed' => 'center',
    	'Contact' => 'center'
    );
    $gloEditPage = '/admin/prospectStaffEdit';
    $gloAddPage  = $gloEditPage;

    $pgList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkProspectStaff', '/admin/prospectEdit', 'P');
    $pgHtm .=<<<htmVAR
    <div class="row">
    <div class="col s12">
<div class="card">
  <div class="card-content">
    <h5>Staff</h5>
    $pgList
    </div>
  </div>
  </div>
</div>
htmVAR;
endif;
$pgHtm .=<<<htmVAR
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
