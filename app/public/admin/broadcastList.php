<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgShowDate = wtkSqlDateFormat('b.`ShowOnDate`', 'ShowOnDate', $gloSqlDate);
$pgUntilDate = wtkSqlDateFormat('b.`ShowUntilDate`', 'ShowUntilDate', $gloSqlDate);
$pgSQL =<<<SQLVAR
SELECT b.`UID`, L.`LookupDisplay` AS `AudienceType`, b.`MessageHeader`,
    $pgShowDate, $pgUntilDate
  FROM `wtkBroadcast` b
   INNER JOIN `wtkLookups` L ON L.`LookupType` = 'AudienceType' AND L.`LookupValue` = b.`AudienceType`
WHERE b.`DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(b.`MessageHeader`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgFilterSelect = wtkFilterRequest('wtkFilterSel');
if ($pgFilterSelect != ''):
    $pgWhere = " WHERE b.`AudienceType` = '$pgFilterSelect'";
endif;
$pgSQL .= ' ORDER BY b.`UID` DESC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'broadcastEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkBroadcastDelDate'; // have DelDate at end if should DelDate instead of DELETE

// put in columns you want sortable here:
//wtkSetHeaderSort('ColumnName', 'Column Header');

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
// BEGIN droplist of AudienceType
$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
 WHERE `LookupType` = :LookupType
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSqlFilter = array (
    'LookupType' => 'AudienceType'
);
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, $pgSqlFilter, 'LookupDisplay', 'LookupValue', $pgFilterSelect);
//  END  droplist of AudienceType

$pgHtm  =<<<htmVAR
<div class="container">
    <h4>Broadcasts
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('broadcastList','wtkBroadcast','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <p>These will appear on websites to notify users as needed.</p>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
              <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="enter partial Message Header to search for">
           </div>
           <div class="filter-width-50">
               <select id="wtkFilterSel" name="wtkFilterSel">
                   $pgSelOptions
               </select>
		   </div>
           <button onclick="Javascript:wtkBrowseFilter('broadcastList','wtkBroadcast')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkBroadcast', '/admin/broadcastList.php');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
