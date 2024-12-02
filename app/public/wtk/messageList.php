<?php
define('_RootPATH', '../');
require('wtkLogin.php');
/*
CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `TechName`
INNER JOIN wtkUsers u ON u.`UID` = e.`SendByUserUID`
*/
$pgSQL =<<<SQLVAR
SELECT e.`UID`,
   DATE_FORMAT(e.`AddDate`, '$gloSqlDateTime') AS `TopRight`,
   e.`Subject` AS `Header`,
   REPLACE(e.`EmailBody`,' href="', ' href="#" title="') AS `Description`
 FROM `wtkEmailsSent` e
WHERE e.`SendToUserUID` = ?
ORDER BY e.`UID` DESC
SQLVAR;

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h4>Messages</h4>' . "\n";
$pgHtm .= '    <p>click for details</p>' . "\n";
$pgHtm .= wtkPageList($pgSQL, [$gloUserUID], '/wtk/messageDetail');
$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no messages yet');
$pgHtm .= '</div>' . "\n";

wtkAddUserHistory('Messages');
echo $pgHtm;
exit;
?>
