<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA';

$pgSQL =<<<SQLVAR
SELECT u.`UID`,
    CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `WhoToChatWith`
  FROM `wtkUsers` u
   LEFT OUTER JOIN `wtkChat` c
    ON c.`SendToUserUID` = u.`UID` OR c.`SendByUserUID` = u.`UID`
WHERE u.`DelDate` IS NULL AND u.`UID` <> ?
GROUP BY u.`UID`
ORDER BY COUNT(c.`UID`) DESC, u.`FirstName` ASC, u.`LastName` ASC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/wtk/chatEdit';

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h4>Chats</h4>' . "\n";
$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloUserUID], 'wtkChat', '/wtk/chatList.php');
$pgHtm .= '</div></div>' . "\n";

$pgHtm = wtkReplace($pgHtm, $gloIconEdit,'<i class="material-icons">chat</i>');

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
