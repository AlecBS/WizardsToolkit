<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT l.`UID`, l.`PrimaryText` AS `English`, o.`LookupDisplay` AS `ConvertTo`,
    l.`NewText` AS `OtherLanguage`
FROM `wtkLanguage` l
  LEFT OUTER JOIN `wtkLookups` o ON o.`LookupType` = 'LangPref' AND o.`LookupValue` = l.`Language`
SQLVAR;

$pgHideReset = ' class="hide"';
$pgWhere = '';

$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgWhere = " lower(l.`PrimaryText`) LIKE lower('%" . $pgFilterValue . "%')";
endif;  // $pgFilterValue != ''
$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    if ($pgWhere != ''):
        $pgWhere .= ' AND';
    endif;
    $pgWhere .= " l.`Language` = '$pgFilter2Value'";
endif;
$pgFilter3Value = wtkFilterRequest('wtkFilter3');
if ($pgFilter3Value == 'Y'):
    if ($pgWhere != ''):
        $pgWhere .= ' AND';
    endif;
    $pgWhere .= ' l.`NewText` IS NULL';
endif;
if ($pgWhere != ''):
    $pgSQL .= ' WHERE ' . $pgWhere;
    $pgHideReset = '';
endif;
$pgSQL .= ' ORDER BY l.`Language` ASC, l.`PrimaryText` ASC';

$gloEditPage = 'languageEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkLanguage'; // have DelDate at end if should DelDate instead of DELETE

$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'LangPref'
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, [], 'LookupDisplay', 'LookupValue', $pgFilter2Value);
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$gloWTKmode = 'ADD';
$pgTmp = wtkFormSwitch('', 'Filter3', 'Only Empty', $pgValues, 'm2 s12');
$pgSwitch = wtkReplace($pgTmp, 'input-field col m2 s12','');
$pgHtm =<<<htmVAR
<div class="container">
    <h4>Language Management
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('languageList','wtkLanguage')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">

        <div class="input-field">
            <div class="filter-width-33">
                <input type="search" name="wtkFilter" id="wtkFilter" value="$pgFilterValue" placeholder="enter partial Original Text">
            </div>
            <div class="filter-width-33">
                <select id="wtkFilter2" name="wtkFilter2">
                    <option value="">Show All</option>
                    $pgSelOptions
                </select>
                <label for="wtkFilter2" class="active">Choose Language</label>
            </div>
            <div class="filter-width-33">
                $pgSwitch
            </div>
            <button onclick="Javascript:wtkBrowseFilter('languageList','wtkLanguage')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkLanguage', '/admin/languageList.php');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
