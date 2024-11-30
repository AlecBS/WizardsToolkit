<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `LookupType`, `LookupValue`,
    `LookupDisplay`, `espLookupDisplay` AS `SpanishDisplay`
  FROM `wtkLookups`
WHERE `DelDate` IS NULL
SQLVAR;

$pgHideReset = ' class="hide"';
if (($gloRNG != '0') && ($gloRNG != '')):
    $pgFilterValue = $gloRNG;
else:
	$pgFilterValue = wtkFilterRequest('wtkFilter');
endif;
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`LookupType`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
$pgSQL .= ' ORDER BY `LookupType` ASC';

$gloEditPage = '/admin/lookupEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkLookupsDelDate'; // have DelDate at end if should DelDate instead of DELETE
wtkSetHeaderSort('LookupType');
wtkSetHeaderSort('LookupValue', 'Value');
wtkSetHeaderSort('LookupDisplay', 'Display');

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Lookup List</h4><br>
    <h5>Lookup Type Quick Filter <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('lookupList','wtkLookups')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial Lookup Type to search for">
           </div>
           <button onclick="Javascript:wtkBrowseFilter('lookupList','wtkLookups')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkLookups', '/admin/lookupList.php');
$pgHtm .= '</div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
