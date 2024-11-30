<?PHP
$pgSecurityLevel = 97;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT b.`UID`,  DATE_FORMAT(b.`AddDate`, '$gloSqlDateTime') AS 'AddDate',
  CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `User`,
  b.`Browser`, b.`BugMsg` AS `Message`
FROM `wtkBugReport` b
    LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = b.`CreatedByUserUID`
WHERE b.`DoneDate` IS NULL
ORDER BY b.`UID` DESC
SQLVAR;

$gloRNG = 1; // this lets bugView now to use SPA instead of MPA
$gloEditPage = 'bugView';
$gloDelPage  = 'wtkBugReport'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h4>Feedback</h4>' . "\n";
$pgHtm .= '    <p>These are from users via the "report bug/feedback" feature.</p>' . "\n";
$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
wtkSearchReplace('edit</i>','remove_red_eye</i>');
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkBugReport', '/admin/bugList.php');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no unresolved bugs');
$pgHtm  = wtkReplace($pgHtm, 'edit</i>','remove_red_eye</i>');
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
