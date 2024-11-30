<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL = 'SELECT L.`UID`,';
// This shows how you can easily code your SQL to work with both PostgreSQL and mySQL DBs
if (strpos(strtolower($gloDriver1), 'ostgres') > 0):  // checking for any PostgreSQL version
    $pgSQL .= ' (u."FirstName" || \' \' || u."LastName") AS "User", . "\n"';
else:   // Not strpos(strtolower($gloDriver1), 'ostgres') > 0
    $pgSQL .= " CONCAT(u.`FirstName`, ' ', u.`LastName`) AS `User`," . "\n";
endif;  // strpos(strtolower($gloDriver1), 'ostgres') > 0
$pgSQL .= ' L.`PagesVisited`,';
$pgSQL .= wtkSqlDateFormat('L.`FirstLogin`','LoggedIn') . ',';
// wtkSqlDateFormat uses $gloSqlDateTime as defined in wtkServerInfo so one change
// there can update entire website; plus it works for both PostgreSQL and MySQL date formatting
$pgSQL .= wtkSqlDateFormat('L.`LastLogin`','LastPageVisit') . ',';
$pgSQL .=<<<SQLVAR
 TIMEDIFF(L.`LastLogin`, L.`FirstLogin`) AS `TimeDiff`
  FROM `wtkLoginLog` L
LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = L.`UserUID`
 ORDER BY L.`UID` DESC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);

$gloColumnAlignArray = array (
    'PagesVisited' => 'center',
    'TimeDiff'    => 'center'
);
// add name of column(s) that you want totalled
// Use 'SUM' to sum it and 'DSUM' to sum it and display as US currency
$gloTotalArray = array (
    'PagesVisited' => 'SUM'
);
// Note that Sort Order requires SELECT to have ORDER BY
wtkSetHeaderSort('PagesVisited'); // Defaults column name but can change with second parameter
wtkSetHeaderSort('TimeDiff', 'Session Duration');
wtkSetHeaderSort('LoggedIn', 'My Date', 'FirstLogin');
// when third parameter exists it is used for sorting of first parameter's column

$pgHtm  = '<h4>Login Logs</h4>' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkLoginLog');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
// Use above 2 lines for MPA; for SPA use below instead
//echo $pgHtm;
//exit;
?>
