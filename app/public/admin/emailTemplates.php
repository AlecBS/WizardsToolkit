<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT t.`UID`,  DATE_FORMAT(t.`AddDate`, '$gloSqlDateTime') AS `AddDate`,
    L.`LookupDisplay` AS `EmailType`, t.`EmailCode`, t.`Subject`
  FROM `wtkEmailTemplate` t
    LEFT OUTER JOIN `wtkLookups` L ON L.`LookupValue` = t.`EmailType` AND L.`LookupType` = 'EmailType'
WHERE t.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' hide';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(t.`EmailCode`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(t.`Subject`) LIKE lower('%" . $pgFilter2Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgSQL .= ' ORDER BY t.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/admin/emailTemplateEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkEmailTemplateDelDate'; // have DelDate at end if should DelDate instead of DELETE

// If you want phone version to show less columns...
// if ($gloDeviceType == 'phone'):
//     $pgSQL = wtkReplace($pgSQL, ', `ExtraColumns`','');
// endif;

// put in columns you want sortable here:
//wtkSetHeaderSort('ColumnName', 'Column Header');
//wtkFillSuppressArray('ColumnName');

$gloColumnAlignArray = array (
   'EmailCode' => 'center'
);

if ($gloUserSecLevel >= 80): // Mgr level
    if ($gloUserSecLevel == 99): // Tech level
        $gloMoreButtons = array(
            'View Example' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'View',
                    'img' => 'remove_red_eye'
                    ),
            'Test Send' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'Test',
                    'img' => 'send'
                ),
            'Send to one User' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'OneUser',
                    'img' => 'email'
                ),
            'Bulk Email Everyone' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'VerifyBulk',
                    'img' => 'recent_actors'
                )
            );
    else:
        $gloMoreButtons = array(
            'View Example' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'View',
                    'img' => 'remove_red_eye'
                    ),
            'Send to one User' => array(
                    'act' => '/admin/sendEmail',
                    'mode' => 'OneUser',
                    'img' => 'email'
                )
            );
    endif;  // Tech level
else:
    $gloMoreButtons = array(
        'View Example' => array(
                'act' => '/admin/sendEmail',
                'mode' => 'View',
                'img' => 'remove_red_eye'
                ),
        );
endif;  // Mgr level
// onclick="wtkModal('/wtk/emailModal','',214,'');"
$pgHtm =<<<htmVAR
<div class="container">
    <h4>Email Templates
        <small id="filterReset" class="right">
        <a onclick="wtkModal('/admin/emailWriter','Start');" class="btn btn-floating"><i class="material-icons" alt="Write email to send" title="Write email to send">email</i></a>
        <button onclick="JavaScript:wtkBrowseReset('emailTemplates','wtkEmailTemplate')" type="button" class="btn btn-small btn-save waves-effect waves-light$pgHideReset">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial Email Code to search for">
           </div>
           <div class="filter-width-50">
              <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Subject to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('emailTemplates','wtkEmailTemplate')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

wtkSetHeaderSort('EmailType');
wtkSetHeaderSort('EmailCode');
$pgTmp = wtkBuildDataBrowse($pgSQL, [], 'wtkEmailTemplate', '/admin/emailTemplates.php', 'P');
//$pgTmp = wtkReplace($pgTmp, "wtkModal('emailTemplateEdit','EDIT',", "ajaxGo('emailTemplateEdit',");
$pgReplace = 'target="_blank" href="/admin/sendEmail.php?apiKey=' . $pgApiKey . '&Mode=View&id=';
$pgTmp = wtkReplace($pgTmp, "onClick=\"JavaScript:wtkModal('/admin/sendEmail','View',", $pgReplace);
$pgSearch = ');" class="btn btn-floating "><i class="material-icons" alt="Click to View Example"';
$pgTmp = wtkReplace($pgTmp, $pgSearch, '" class="btn btn-floating"><i class="material-icons" alt="Click to View Example"');
$pgTmp = wtkReplace($pgTmp, '@CompanyName@', $gloCoName);
$pgHtm .= $pgTmp;
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
