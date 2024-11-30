<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `FileName`, `FileDescription`
  FROM `wtkDownloads`
WHERE `DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`FileName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgSQL .= ' ORDER BY `FileName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = 'downloadEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkDownloadsDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h2>Downloads</h2>' . "\n";
$pgHtm .= '<br><div class="search-result">' . "\n";
$pgHtm .= '  <h4>File Name Quick Filters <small id="filterReset"' . $pgHideReset . '>' . "\n";
$pgHtm .= '<button type="button" class="btn btn-small btn-save waves-effect waves-light right" onclick="JavaScript:wtkBrowseReset(\'downloadList.php\',\'wtkDownloads\',' . $gloRNG . ')">Reset List</button>' . "\n";
$pgHtm .= '</small></h4>' . "\n";
$pgHtm .= '  <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search">' . "\n";
$pgHtm .= wtkFormHidden('Filter', 'Y') . "\n";
$pgHtm .= '    <div class="input-field">' . "\n";
$pgHtm .= '        <input type="search" class="input-search" name="wtkFilter" id="wtkFilter" value="' . $pgFilterValue . '" placeholder="enter partial value to search for">' . "\n";
$pgHtm .= '        <button onclick="Javascript:wtkBrowseFilter(\'downloadList\',\'wtkDownloads\')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>' . "\n";
$pgHtm .= '    </div>' . "\n";
$pgHtm .= '  </form>' . "\n";
$pgHtm .= '</div>' . "\n";

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkDownloads', '/admin/downloadList.php');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no downloads defined yet');
$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
