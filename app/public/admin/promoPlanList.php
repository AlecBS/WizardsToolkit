<?PHP
$pgSecurityLevel = 95;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT p.`UID`, p.`PlanName`,  L.`LookupDisplay` AS `Agency`,
 DATE_FORMAT(p.`ExpireDate`, '%c/%e/%Y at %l:%i %p') AS 'ExpireDate', p.`GrossSales`, p.`NetSales`
  FROM `wtkPromoPlans` p
 LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'PromoAgent' AND L.`UID` = p.`AgentUID`
WHERE p.`DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`PlanName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgSQL .= ' ORDER BY `PlanName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'promoPlanEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkPromoPlansDelDate'; // have DelDate at end if should DelDate instead of DELETE

// If you want phone version to show less columns...
// if ($gloDeviceType == 'phone'):
//     $pgSQL = wtkReplace($pgSQL, ', `ExtraColumns`','');
// endif;

// put in columns you want sortable here:
//wtkSetHeaderSort('ColumnName', 'Column Header');
//wtkFillSuppressArray('ColumnName');

//$gloColumnAlignArray = array (
//    'Priority'   => 'center'
//);

/*
$gloMoreButtons = array(
                'User Logins' => array(
                        'act' => 'pageName',
                        'img' => 'arrow-right'
                        )
                );
*/

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Promotion Plans
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/admin/promoPlanList','wtkPromoPlans')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <div class="filter-area">
        <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search">
            <input type="hidden" id="Filter" name="Filter" value="Y">
            <div class="input-field">
               <div class="filter-width">
                  <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial value to search for">
               </div>
               <button onclick="Javascript:wtkBrowseFilter('/admin/promoPlanList','wtkPromoPlans')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
            </div>
        </form>
    </div>
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkPromoPlans', '/admin/promoPlanList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
