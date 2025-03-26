<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgBroadcast = wtkBroadcastAlerts();

$pgSQL =<<<SQLVAR
SELECT p.`UID`,
    CONCAT(p.`CompanyName`,'<br>',
        CASE
            WHEN COALESCE(p.`Website`,'') <> '' THEN
                CONCAT('<a target="_blank" href="https://', p.`Website`, '">', p.`Website`, '</a>')
            ELSE ''
        END
    ) AS `Company`,
    COALESCE(p.`CompanySize`,p.`NumberOfEmployees`) AS `CompanySize`, p.`AnnualSales`, p.`City`, p.`State`,
    `fncContactIcons`(NULL,p.`MainPhone`,0,0,'Y',p.`UID`,'N','N','') AS `Phone`,
    L.`LookupDisplay` AS `ProspectStatus`
  FROM `wtkProspects` p
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'ProspectStatus' AND L.`LookupValue` = p.`ProspectStatus`
WHERE p.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(p.`CompanyName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter3Value = wtkFilterRequest('showReplies');
if ($pgFilter3Value == 'Y'):
    $pgShowReplied = 'checked';
    $pgSQL .= " AND p.`ProspectStatus` = 'reply'" . "\n";
else:
    $pgShowReplied = '';
endif;
$pgSQL .= ' ORDER BY p.`CompanyName` ASC';
wtkSetHeaderSort('CompanyName', 'Company Name');
wtkSetHeaderSort('CompanySize', 'Company Size');
wtkSetHeaderSort('AnnualSales', 'Annual Sales');
wtkSetHeaderSort('City', 'City');
$gloColumnAlignArray = array (
    'CompanySize' => 'center'
);
$gloEditPage = '/admin/prospectEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkProspectsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm =<<<htmVAR
<div class="container">
    $pgBroadcast
    <h4>Prospects
        <small class="right">
            <a onclick="JavaScript:ajaxGo('prospectStaffList')">Emailing List</a>
            <span id="filterReset"$pgHideReset>
                &nbsp;&nbsp;
                <button onclick="JavaScript:wtkBrowseReset('/admin/prospectList','wtkProspects')"
                    type="button" class="btn btn-small btn-save waves-effect waves-light">Reset List</button>
            </span>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
              <input type="search" class="filter-width-50" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter Company Name">
           </div>
           <div class="filter-width-50">
               <span>Show only those that filled out landing page</span>
               <div class="switch">
                 <label for="showReplies">No
                   <input type="checkbox" value="Y" id="showReplies" name="showReplies" $pgShowReplied>
                   <span class="lever"></span>
                   Yes</label>
               </div>
           </div>
           <button onclick="Javascript:wtkBrowseFilter('/admin/prospectList','wtkProspects')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
wtkSearchReplace('https://http','http'); // to fix data where website already included http: or https:
$pgList = wtkBuildDataBrowse($pgSQL, [], 'wtkProspects', '/admin/prospectList', 'P');
$pgList = wtkReplace($pgList,'https://http','http'); // to fix data where website already included http: or https:
$pgHtm .= $pgList;
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
