<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `CompanyName`, `ContactName`, `Email`,
    `fncContactIcons`(`Email`,`MainPhone`,0,0,'Y',`UID`,'N','N','') AS `Contact`,
    DATE_FORMAT(`SignedDate`, '%c/%e/%Y at %l:%i %p') AS `SignedDate`,
    `SignedDate` AS `obSignedDate`
  FROM `wtkAffiliates`
WHERE `DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`CompanyName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(`ContactName`) LIKE lower('%" . $pgFilter2Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgFilter3Value = wtkFilterRequest('wtkFilter3');
if ($pgFilter3Value != ''):
    $pgSQL .= " AND lower(`Email`) LIKE lower('%" . $pgFilter3Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgSQL .= ' ORDER BY COALESCE(`CompanyName`,`ContactName`) ASC';

$gloEditPage = 'affiliateEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkAffiliatesDelDate'; // have DelDate at end if should DelDate instead of DELETE

// put in columns you want sortable here:
wtkSetHeaderSort('CompanyName');
wtkSetHeaderSort('ContactName');
wtkSetHeaderSort('SignedDate', 'Signed Date', 'obSignedDate');
wtkFillSuppressArray('obSignedDate');

$gloMoreButtons = array(
        'View Past Emails' => array(
                'act' => '/admin/emailAffiliateHistory',
                'img' => 'remove_red_eye',
                'mode' => 'ViewEmail'
            ),
        'Choose Email to Send' => array(
                'act' => '/admin/pickEmailTemplate',
                'img' => 'send',
                'mode' => 'Af'
            )
        );

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Affiliates
        <small>
            <a class="btn orange black-text right"
                onclick="JavaScript:wtkModal('pickEmailTemplate','Af',0,'SendAll')">Bulk Email</a>
        </small>
    </h4>
    <p>Bulk Emailing will send to the next 50 affiliates which have not been notified before.</p>
    <h5>Quick Filters <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('affiliateList','wtkAffiliates','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
            <div class="filter-width-33">
                <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="Company Name to search for">
            </div>
            <div class="filter-width-33">
                <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="Contact Name to search for">
            </div>
            <div class="filter-width-33">
                <input type="search" name="wtkFilter3" id="wtkFilter3" value="$pgFilter3Value" placeholder="Email to search for">
            </div>
            <button onclick="Javascript:wtkBrowseFilter('affiliateList','wtkAffiliates')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkAffiliates', '/admin/affiliateList.php', 'P');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
