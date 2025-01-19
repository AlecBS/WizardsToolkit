<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSiteDesign  = 'SPA'; // MPA or SPA for Multi-Page App or Single Page App

$pgSQL =<<<SQLVAR
SELECT CONCAT(wu.`FirstName`, ' ', COALESCE(wu.`LastName`,'')) AS `User`,
   (SELECT COUNT(L.`UID`) FROM `wtkLoginLog` L WHERE  L.`UserUID` = wu.`UID`) AS `Logins`,
   COUNT(h.`UID`) AS `PageViews`,
   (SELECT COUNT(r.`UID`) FROM `wtkReportCntr` r WHERE  r.`UserUID` = wu.`UID`) AS `ReportViews`,
   (SELECT COUNT(u.`UID`) FROM `wtkUpdateLog` u WHERE  u.`UserUID` = wu.`UID`) AS `Updates`
 FROM `wtkUsers` wu
   INNER JOIN `wtkUserHistory` h ON h.`UserUID` = wu.`UID`
 WHERE wu.`DelDate` IS NULL
 GROUP BY wu.`UID`
ORDER BY COUNT(h.`UID`) DESC, wu.`FirstName` ASC
SQLVAR;

$gloColumnAlignArray = array (
    'Logins' => 'right',
    'PageViews' => 'right',
    'ReportViews' => 'right',
	'Updates' => 'right'
);
$pgHtm = wtkRptChart($pgSQL);

wtkProtoType($pgHtm);
if ($gloSiteDesign == 'SPA'):
    echo $pgHtm;  // SPA Method
    exit;
else: // MPA Method
    wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
endif;
?>
