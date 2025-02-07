<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../../');
    require('../../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`,`PageTitle`,`Slug`,
    CASE
      WHEN `MakePublic` = 'Y' THEN DATE_FORMAT(`PublishDate`, '$gloSqlDateTime')
      ELSE 'Not yet'
    END AS `Published`,
    `Views`,
    CASE
      WHEN `LastViewDate` IS NULL THEN 'Not yet'
      ELSE DATE_FORMAT(`LastViewDate`, '$gloSqlDateTime')
    END AS `LastViewDate`,
    CONCAT('<a target="_blank" href="../',`Slug`,
      '" class="btn btn-floating"><i class="material-icons" alt="Click to View" title="Click to View">visibility</i></a>')
      AS `View`
FROM `wtkBlog`
 WHERE `DelDate` IS NULL
ORDER BY `UID` DESC
SQLVAR;

$gloColumnAlignArray = array (
    'Views' => 'center',
    'LastViewDate' => 'center',
	'Published' => 'center'
);
wtkSetHeaderSort('PageTitle');

$gloEditPage = 'Writer';
$gloAddPage = $gloEditPage ; // . '?Mode=ADD';
$gloDelPage  = 'wtkBlogDelDate'; // have DelDate at end if should DelDate instead of DELETE

$pgHtm  = '<h2>Blog List' . "\n";
$pgHtm .= '<a class="btn blue tooltipped" href="Designer.php" data-tooltip="Change Blog colors and font">Blog Designer</a>' . "\n";
$pgHtm .= '</h2>' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkBlog');

$pgVersion = 1; // makes preventing cache when update JS very easy
wtkSearchReplace('wtkLibrary.js','wtkLibrary.js?v=' . $pgVersion);
wtkSearchReplace('wtkUtils.js','wtkUtils.js?v=' . $pgVersion);

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../../wtk/htm/minibox.htm');
?>
