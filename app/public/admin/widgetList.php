<?PHP
$pgSecurityLevel = 99;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT w.`UID`, w.`WidgetName`, L.`LookupDisplay`, w.`WidgetType`,
    CASE w.`WidgetType`
      WHEN 'Chart' THEN w.`ChartType`
      WHEN 'Count' THEN w.`WidgetColor`
      ELSE ''
    END AS `Extra`,
    CASE COALESCE(w.`WidgetURL`,'')
      WHEN '' THEN 'no link'
      ELSE CASE w.`WindowModal`
        WHEN 'N' THEN w.`WidgetURL`
        ELSE CONCAT('modal to ', w.`WidgetURL`)
        END
    END AS `LinkTo`
  FROM `wtkWidget` w
 INNER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel'
    AND CAST(L.`LookupValue` AS DECIMAL) = w.`SecurityLevel`
WHERE w.`DelDate` IS NULL
SQLVAR;
// Using DECIMAL in CAST makes it work for both MySQL and Postgres
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(w.`WidgetName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(w.`WidgetDescription`) LIKE lower('%" . $pgFilter2Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgSQL .= ' ORDER BY w.`WidgetName` ASC';

$gloEditPage = 'widgetEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkWidgetDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Widgets
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/admin/widgetList','wtkWidget','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
              <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="enter partial Widget Name to search for">
           </div>
           <div class="filter-width-50">
			  <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial Widget Description to search for">
		   </div>
           <button onclick="Javascript:wtkBrowseFilter('/admin/widgetList','wtkWidget')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

wtkSetHeaderSort('WidgetName');
wtkSetHeaderSort('LookupDisplay', 'Security Level');
wtkSetHeaderSort('WidgetType');
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkWidget', '/admin/widgetList.php');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
