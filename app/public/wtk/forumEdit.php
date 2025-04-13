<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA';

$pgSQL =<<<SQLVAR
SELECT `ForumName`, `ForumNote`
  FROM `wtkForum`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);
$pgForumName = wtkSqlValue('ForumName');
$pgForumNote = nl2br(wtkSqlValue('ForumNote'));

$pgSQL =<<<SQLVAR
SELECT f.`UID`, f.`ForumMsg`, u.`FilePath`, u.`NewFileName`,
 CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `UserName`,
 DATE_FORMAT(f.`AddDate`, '$gloSqlDateTime') AS `AddDate`
  FROM `wtkForumMsgs` f
   INNER JOIN `wtkUsers` u ON u.`UID` = f.`UserUID`
WHERE f.`ForumUID` = ?
ORDER BY f.`UID` ASC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgForum = wtkForumList($pgSQL, [$gloId]);
$gloWTKmode = 'ADD';

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Forum Topic: $pgForumName</h4><br>
    <p>$pgForumNote</p>
    <form id="wtkForm" name="wtkForm" method="POST">
        <span id="formMsg" class="green-text">$gloFormMsg</span>
    $pgForum
htmVAR;
$pgHtm .= wtkSendNoteForum($gloId);
$pgHtm .=<<<htmVAR
    </form>
</div>
htmVAR;

wtkProtoType($pgHtm);
wtkAddUserHistory('Forum Edit');

echo $pgHtm;
exit;
?>
