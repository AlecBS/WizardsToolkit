<?php
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgParam = wtkGetPost('p');

$pgDate = wtkSqlDateFormat('e.`AddDate`','SentDate',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT e.`UID`, $pgDate,
  CASE
    WHEN e.`SendByUserUID` IS NULL THEN 'Server'
    ELSE CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,''),
        CASE WHEN e.`SendByUserUID` = $gloRNG THEN ''
          ELSE CONCAT(' (',L.`LookupDisplay`,')')
        END
        )
  END AS `SentFrom`,
  CONCAT(u2.`FirstName`, ' ', COALESCE(u2.`LastName`,''),
    IF (e.`SendToUserUID` = $gloRNG,'',CONCAT(' (',L2.`LookupDisplay`,')'))
  ) AS `SentTo`,
  CASE
    WHEN e.`OtherUID` IS NULL THEN ''
    ELSE CONCAT('<a onclick="JavaScript:ajaxGo(\'orderEdit\',',e.`OtherUID`,')">Order #',e.`OtherUID`, '</a>')
  END AS `RelatedOrder`,
  e.`Subject`
FROM `wtkEmailsSent` e
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = e.`SendByUserUID`
  LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = e.`SendToUserUID`
  INNER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel' AND CAST(L.`LookupValue` AS smallint) = u.`SecurityLevel`
  INNER JOIN `wtkLookups` L2 ON L2.`LookupType` = 'SecurityLevel' AND CAST(L2.`LookupValue` AS smallint) = u2.`SecurityLevel`
WHERE
SQLVAR;
// change orderEdit above to whatever file would have the related order information
if ($gloDriver1 != 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, 'AS smallint','AS decimal');
endif;

if ($pgParam == 'UserUID'):
    $pgSQL .= ' e.`SendByUserUID` = :UIDa OR e.`SendToUserUID` = :UIDb' . "\n";
    $pgSqlFilter = array (
        'UIDa' => $gloRNG,
        'UIDb' => $gloRNG
    );
else:
    $pgSQL .= ' e.`OtherUID` = :UID' . "\n";
    $pgSqlFilter = array (
        'UID' => $gloRNG
    );
endif;
$pgSQL .= 'ORDER BY e.`UID` DESC';

$gloRowsPerPage = 20;
$gloEditPage = '/admin/emailView';
$gloAddPage  = '';
$gloDelPage  = '';
$pgMsgsList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkEmailsSentDIV', '','Y');
$pgMsgsList = wtkReplace($pgMsgsList, 'No data.','no messages yet');

echo $pgMsgsList;
?>
