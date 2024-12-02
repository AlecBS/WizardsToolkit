<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

/*
Note: calling the View should work but in some versions of MySQL can give an error
SELECT `Widget1`, `Widget2`, `Widget3`, `Widget4`
  FROM `wtkDashboardView`
*/
$pgSQL =<<<SQLVAR
SELECT
  (SELECT COUNT(*) FROM `wtkUsers` WHERE `DelDate` IS NULL) AS `Widget1`,
  (SELECT COUNT(*) FROM `wtkLoginLog`) AS `Widget2`,
  (SELECT COUNT(*) FROM `wtkUserHistory`) AS `Widget3`,
  (SELECT COUNT(*) FROM `wtkReportCntr`) AS `Widget4`
SQLVAR;
wtkSqlGetRow($pgSQL, []);
$pgWidget1 = wtkSqlValue('Widget1');
$pgWidget2 = wtkSqlValue('Widget2');
$pgWidget3 = wtkSqlValue('Widget3');
$pgWidget4 = wtkSqlValue('Widget4');

$pgJSON  = '{"result":"ok","widget1":"' . $pgWidget1 . ' ",';
$pgJSON .= '"widget2":"' . $pgWidget2 . ' ",';
$pgJSON .= '"widget3":"' . $pgWidget3 . ' ",';
$pgJSON .= '"widget4":"' . $pgWidget4 . ' "}';
echo $pgJSON;
exit; // no display needed, handled via JS and spa.htm
?>
