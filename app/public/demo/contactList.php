<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT u.`UID`, u.`FirstName`, u.`LastName`, u.`City`,
    fncContactIcons(u.`Email`,u.`CellPhone`,0,0,'Y',u.`UID`,u.`SMSEnabled`,'Y','') AS `Contact`
  FROM `wtkUsers` u
WHERE u.`DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(u.`FirstName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(u.`LastName`) LIKE lower('%" . $pgFilter2Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''

$pgSQL .=<<<SQLVAR
 ORDER BY u.`LastName` ASC, u.`FirstName` ASC
SQLVAR;
//  HAVING COUNT(l.`UID`) > 50
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloDeviceType == 'phone'):
    $pgSQL = wtkReplace($pgSQL, ', `City`','');
endif;
wtkSetHeaderSort('LastName');
wtkSetHeaderSort('FirstName');
wtkSetHeaderSort('City');

$gloEditPage = '/admin/userAdminEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'contactsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloMoreButtons = array(
                'Contact Info' => array(
                        'act' => 'contactModal',
                        'img' => 'contact_phone'
                        ),
                'User Logins' => array(
                        'act' => '/admin/userLogins',
                        'img' => 'beenhere'
                        ),
                'User Updates' => array(
                        'act' => '/admin/userUpdates',
                        'img' => 'assignment'
                    ),
                'Send Invite' => array(
                        'act' => '/admin/sendInvite',
                        'img' => 'send'
                        )
                );

$pgHtm =<<<htmVAR
<div class="container">
    <h4>My Contacts</h4><br>
    <h5>Quick Filters <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('contactList')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
    </small></h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" name="Filter" id="Filter" value="Y">
        <div class="input-field">
            <div class="filter-width-50">
                <input type="search" class="input-search2" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial First Name to search for">
            </div>
            <div class="filter-width-50">
                <input type="search" class="input-search2" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Last Name to search for">
            </div>
            <button onclick="Javascript:wtkBrowseFilter('contactList')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
wtkSearchReplace("JavaScript:ajaxGo('contactModal',", "JavaScript:wtkModal('contactModal',");
$pgList = wtkBuildDataBrowse($pgSQL);
//$pgList = wtkBuildDataBrowse($pgSQL, [], '', '','P');
$pgHtm .= wtkReplace($pgList, "JavaScript:ajaxGo('contactModal',", "JavaScript:wtkModal('contactModal',");
$pgHtm .= '</div></div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
// if calling from within app use above 2 lines; if calling directly use below instead
wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
