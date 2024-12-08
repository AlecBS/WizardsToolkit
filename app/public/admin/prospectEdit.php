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
SELECT `UID`, `CompanyName`, `Website`, `CompanySize`, `AnnualSales`,
    `MainPhone`, `Website`, `Address1`,`Address2`, `City`, `State`, `Zipcode`,
    `FundingDate`,`FundingAmount`,`FundingType`,`SICCode`,`B2BorB2C`,
    `LinkedIn`,`OtherSocial`,`MainEmail`,`TimeZone`,
    `ProspectStatus`, `InternalNote`, `Description`
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
    <h2>Prospect Company</h2><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkProspects', 'CompanyName','text','', 'm8 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'ProspectStatus' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkProspects', 'ProspectStatus', $pgSQL, [], 'LookupDisplay', 'LookupValue','Prospect Status','m4 s12');

$pgHtm .= wtkFormText('wtkProspects', 'Address1');
$pgHtm .= wtkFormText('wtkProspects', 'Address2');
$pgHtm .= wtkFormText('wtkProspects', 'City', 'text', 'City', 'm5 s12');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAstate' ORDER BY `LookupDisplay` ASC";
$pgHtm .= wtkFormSelect('wtkProspects', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue','State','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'Zipcode', 'text', 'Zipcode', 'm3 s12');
$pgHtm .= wtkFormText('wtkProspects', 'MainPhone', 'tel', 'Main Phone', 'm4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'Website', 'text', '', 'm8 s12');

$pgHtm .= wtkFormText('wtkProspects', 'LinkedIn');
$pgHtm .= wtkFormText('wtkProspects', 'OtherSocial');

$pgHtm .= wtkFormText('wtkProspects', 'FundingType','text','','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'FundingDate', 'date','','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'FundingAmount','text','','m4 s12');

$pgHtm .= wtkFormText('wtkProspects', 'B2BorB2C','text','B2B or B2C','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'SICCode','text','SIC Code','m4 s12');
$pgHtm .= wtkFormText('wtkProspects', 'TimeZone','text','','m4 s12');

$pgHtm .= wtkFormTextArea('wtkProspects', 'InternalNote');
$pgHtm .= wtkFormTextArea('wtkProspects', 'Description');

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
SELECT s.`UID`, s.`DoNotContact` AS `NoContact`, s.`FirstName`, s.`LastName`,
    `fncContactIcons`(s.`Email`,COALESCE(s.`DirectPhone`,''),0,0,'Y',s.`UID`,'N','N','') AS `Contact`,
    s.`Email`
  FROM `wtkProspectStaff` s
WHERE s.`ProspectUID` = :PropUID AND s.`DelDate` IS NULL
ORDER BY s.`FirstName` ASC, s.`LastName` ASC
SQLVAR;
    $pgSqlFilter = array ('PropUID' => $gloId);
    $gloColumnAlignArray = array (
      'NoContact' => 'center',
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
    <h2>Staff</h2>
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
