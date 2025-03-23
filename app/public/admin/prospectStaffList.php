<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT s.`UID`,
    CONCAT(p.`CompanyName`,'<br>',
        CASE
            WHEN COALESCE(p.`Website`,'') <> '' THEN
                CONCAT('<a target="_blank" href="https://', p.`Website`, '">', p.`Website`, '</a>')
            ELSE ''
        END
    ) AS `Company`,
    COALESCE(p.`CompanySize`,p.`NumberOfEmployees`) AS `CompanySize`, p.`AnnualSales`,
    CONCAT(COALESCE(p.`City`,''), ', ', COALESCE(p.`State`,'')) AS `City`,
    s.`FirstName`, s.`LastName`,
    `fncContactIcons`(s.`Email`,COALESCE(s.`DirectPhone`,p.`MainPhone`,''),0,0,'Y',s.`UID`,'N','N','') AS `Contact`,
    s.`LinksClicked`,
    CONCAT('<a class="btn btn-floating " onclick="JavaScript:ajaxGo(\'/admin/prospectEdit\',',
        s.`ProspectUID`, ',0);"><i class="material-icons">edit</i></a>') AS `Edit`
  FROM `wtkProspectStaff` s
   LEFT OUTER JOIN `wtkProspects` p ON p.`UID` = s.`ProspectUID`
WHERE s.`DelDate` IS NULL AND s.`DoNotContact` = 'N'
  AND p.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(s.`FirstName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(s.`LastName`) LIKE lower('%" . $pgFilter2Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''

$pgFilterEmail = wtkFilterRequest('wtkFilterE');
if ($pgFilterEmail != ''):
    $pgSQL .= " AND lower(s.`Email`) LIKE lower('%" . $pgFilterEmail . "%')";
    $pgHideReset = '';
endif;  // $pgFilterEmail != ''
$pgFilter3Value = wtkFilterRequest('showClicked');
if ($pgFilter3Value == 'Y'):
    $pgShowClicked = 'checked';
    $pgSQL .= ' AND s.`LinksClicked` > 0' . "\n";
else:
    $pgShowClicked = '';
endif;
$pgFilter3Value = wtkFilterRequest('showReplies');
if ($pgFilter3Value == 'Y'):
    $pgShowReplied = 'checked';
    $pgSQL .= " AND p.`ProspectStatus` = 'reply'" . "\n";
else:
    $pgShowReplied = '';
endif;
$pgSQL .= ' ORDER BY s.`LastName` ASC, s.`FirstName` ASC';

wtkSetHeaderSort('LastName', 'Last Name');
wtkSetHeaderSort('FirstName', 'First Name');
wtkSetHeaderSort('LinksClicked');
$gloColumnAlignArray = array (
    'CompanySize' => 'center',
	'LinksClicked' => 'center'
);
$gloAddPage = '/admin/prospectEdit';
// $gloDelPage  = 'wtkProspectStaffDelDate'; // have DelDate at end if should DelDate instead of DELETE

/*
this shows fine but breaks CSS on back office site
'Display' => array(
        'act' => '/admin/emailProspects',
        'img' => 'remove_red_eye',
        'mode' => 'View'
    ),

    changed SendOne to PickOne
*/
$gloMoreButtons = array(
        'View Past Emails' => array(
                'act' => '/admin/emailProHistory',
                'img' => 'remove_red_eye',
                'mode' => 'ViewEmail'
            ),
        'Choose Email to Send' => array(
                'act' => '/admin/pickEmailTemplate',
                'img' => 'send',
                'mode' => 'P'
            )
        );

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Prospect Staff List
        <small>
            <a class="btn orange black-text right"
                onclick="JavaScript:wtkModal('pickEmailTemplate','P',0,'SendAll')">Bulk Email</a>
        </small>
    </h4>
    <p>Bulk Emailing will send to the next 50 prospects which have not been notified before.</p>
    <p class="hide">Send follow-up email to those that have clicked link so far:
        <a class="btn" onclick="JavaScript:ajaxGo('emailProspects','sales3fup','FupSales3')">Follow-up Email</a>
    </p>
    <h5>Quick Filter <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/admin/prospectStaffList','wtkProspectStaff')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="row">
            <div class="col s12">
                <div class="input-field">
                   <div class="filter-width-50">
                       <span>Show only those that clicked Link</span>
                       <div class="switch">
                         <label for="showClicked">No
                           <input type="checkbox" value="Y" id="showClicked" name="showClicked" $pgShowClicked>
                           <span class="lever"></span>
                           Yes</label>
                       </div>
                   </div>
                   <div class="filter-width-50">
                       <div class="input-field">
                           <span>Show only those that filled out landing page</span>
                           <div class="switch">
                             <label for="showReplies">No
                               <input type="checkbox" value="Y" id="showReplies" name="showReplies" $pgShowReplied>
                               <span class="lever"></span>
                               Yes</label>
                           </div>
                       </div>
                   </div>
                   <button onclick="Javascript:wtkBrowseFilter('/admin/prospectStaffList','wtkProspectStaff')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
                </div>
            </div>
            <div class="col s12">
                <div class="input-field">
                   <div class="filter-width-33">
                      <input type="search" name="wtkFilterE" id="wtkFilterE" value="$pgFilterEmail" placeholder="enter partial Email to search for">
                   </div>
                   <div class="filter-width-33">
                      <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial First Name to search for">
                   </div>
                   <div class="filter-width-33">
                      <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Last Name to search for">
                   </div>
                </div>
            </div>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
wtkSearchReplace('https://http','http'); // to fix data where website already included http: or https:
wtkSearchReplace('Edit</th>','&nbsp;</th>');
wtkSearchReplace("wtkModal('/admin/emailPro", "ajaxGo('/admin/emailPro");
$pgList = wtkBuildDataBrowse($pgSQL, [], 'wtkProspectStaff', '/admin/prospectStaffList', 'P');
$pgList = wtkReplace($pgList,'Edit</th>','&nbsp;</th>');
$pgList = wtkReplace($pgList,'https://http','http'); // to fix data where website already included http: or https:
//$pgList = wtkReplace($pgList,"wtkModal('/admin/emailPro", "ajaxGo('/admin/emailPro");
$pgHtm .= $pgList . '</div>' . "\n";

echo $pgHtm;
exit;
?>
