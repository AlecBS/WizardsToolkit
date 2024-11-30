<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloIconSize = 'btn-small';

$pgSQL =<<<SQLVAR
SELECT `UID`, `FirstName`, `LastName`, `City`,
    `fncContactIcons`(`Email`,`CellPhone`,0,0,'Y',`UID`,`SMSEnabled`,'N','') AS `Contact`, `Email`
  FROM `wtkUsers`
WHERE `DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`FirstName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(`LastName`) LIKE lower('%" . $pgFilter2Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''

$pgSQL .= ' ORDER BY `LastName` ASC, `FirstName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloDeviceType == 'phone'):
    $pgSQL = wtkReplace($pgSQL, ', `City`, `Phone`, `Email`','');
endif;

wtkSetHeaderSort('LastName', 'Last Name');
wtkSetHeaderSort('FirstName', 'First Name');
wtkSetHeaderSort('City');

$gloEditPage = '/admin/userAdminEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkUsersDelDate'; // have DelDate at end if should DelDate instead of DELETE

$gloMoreButtons = array(
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
                    ),
                'Send Password Reset' => array(
                        'act' => '/admin/sendResetPW',
                        'img' => 'send'
                    )
                );

$pgHtm =<<<htmVAR
<div class="container">
    <h4>User List</h4>
    <br>
    <h5>Quick Filters <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/admin/userList','wtkUsers')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
                <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial First Name to search for">
           </div>
           <div class="filter-width-50">
                <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Last Name to search for">
		   </div>
           <button onclick="Javascript:wtkBrowseFilter('/admin/userList','wtkUsers')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkUsers', '/admin/userList');
$pgHtm .= '</div></div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
