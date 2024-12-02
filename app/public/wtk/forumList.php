<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA';

$pgSQL =<<<SQLVAR
SELECT f.`UID`, f.`ForumName`,
    DATE_FORMAT(f.`AddDate`, '$gloSqlDate') AS `Created`,
    DATE_FORMAT(f.`LastEditDate`, '$gloSqlDate') AS `LastUpdate`
  FROM `wtkForum` f
WHERE f.`DelDate` IS NULL
ORDER BY COALESCE(f.`LastEditDate`,f.`AddDate`) DESC, f.`AddDate` DESC
SQLVAR;

$gloEditPage = '/wtk/forumEdit';
$gloAddPage  = '/wtk/forumAdd';

$pgHtm  = '<div class="container">' . "\n";
$pgHtm .= '    <h4>Forum Topics</h4>' . "\n";
$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkForum', '/wtk/forumList.php');
$pgHtm  = wtkReplace($pgHtm, 'No data.','no forum topics yet');
$pgHtm .= '</div></div>' . "\n";
$pgHtm = wtkReplace($pgHtm, "ajaxGo('forumAdd','ADD',0)", "ajaxGo('forumAdd','ADD',0,'N')");
$pgHtm = wtkReplace($pgHtm, $gloIconEdit,'<i class="material-icons">forum</i>');

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
