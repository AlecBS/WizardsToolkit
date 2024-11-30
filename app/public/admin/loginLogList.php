<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL = 'SELECT L.`UID`,';
$pgSQL .= " CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `User`," . "\n";
$pgSQL .= ' L.`PagesVisited`,';
$pgSQL .= wtkSqlDateFormat('L.`FirstLogin`','LoggedIn') . ',';
$pgSQL .= wtkSqlDateFormat('L.`LastLogin`','LastPageVisit') . ',';
$pgSQL .=<<<SQLVAR
 L.`CurrentPage`,
 TIMEDIFF(L.`LastLogin`, L.`FirstLogin`) AS `SessionDuration`
  FROM `wtkLoginLog` L
LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = L.`UserUID`
SQLVAR;
$pgSQL .= ' ORDER BY L.`UID` DESC';
if ($gloDriver1 == 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, 'TIMEDIFF(','AGE(');
endif;
$pgSQL = wtkSqlPrep($pgSQL);

$gloColumnAlignArray = array (
    'PagesVisited'   => 'center',
    'SessionDuration' => 'center'
);

$pgHtm  = '<div class="row">' . "\n";
$pgHtm .= '    <div class="col m12">' . "\n";
$pgHtm .= '        <h4>Login Logs</h4><br>' . "\n";
$pgHtm .= '        <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkLoginLog');
$pgHtm .= '        </div>' . "\n";
$pgHtm .= '    </div>' . "\n";
$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
