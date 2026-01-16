<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSlug = wtkGetGet('slug');
if ($pgSlug == ''):
    $pgHtm = '<h2>New Blogs Coming Soon!</h2>';
    wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
endif;

$pgSlug = wtkReplace($pgSlug,'blog.php&slug=','');

$pgSQL =<<<SQLVAR
SELECT `UID`, `PageTitle`,`BlogContent`,`MetaKeywords`,`MetaDescription`,
  `TwitterAcct`, `OGTitle`, `OGDescription`, `OGImage`
 FROM `wtkBlog`
SQLVAR;
// WHERE `MakePublic` = 'Y'
if ($pgSlug == ''):
    $pgWhere = ' `Slug` <> :Slug ORDER BY `UID` DESC LIMIT 1';
else:
    $pgReferPage = wtkGetServer('HTTP_REFERER');
    $pgReferPage = wtkReplace($pgReferPage, $gloWebBaseURL,'');
    if (($pgReferPage != '/blog/admin/index.php') && ($pgReferPage != '/blog/admin/index.php')):
        $pgWhere = ' `Slug` = :Slug AND `DelDate` IS NULL';
    else:
        $pgWhere = ' `Slug` = :Slug';
    endif;
endif;
$pgSqlFilter = array (
    'Slug' => $pgSlug
);
wtkSqlGetRow($pgSQL . ' WHERE ' . $pgWhere, $pgSqlFilter);

$pgUID = wtkSqlValue('UID');
if ($pgUID == ''):
    $pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkBlog` WHERE `DelDate` IS NULL AND `MakePublic` = "Y"', []);
    if ($pgCount > 0):
        $pgWhere  = ' WHERE `DelDate` IS NULL AND `MakePublic` = "Y"' . "\n";
        $pgWhere .= ' ORDER BY `PublishDate` DESC LIMIT 1';
        wtkSqlGetRow($pgSQL . $pgWhere, []);
        $pgUID = wtkSqlValue('UID');
    endif;
endif;
if ($pgUID == ''):
    $pgOGtags = '';
    $pgPageTitle = $gloCoName;
    $pgBlogContent = '<h2>No blogs written yet</h2>';
    $pgMyURL = $gloWebBaseURL . '/blog/';
else:
    $pgPageTitle = wtkSqlValue('PageTitle');
    $pgBlogContent = wtkSqlValue('BlogContent');
    $pgBlogContent = wtkReplace($pgBlogContent, 'src="../imgs/', 'src="imgs/'); // so images added in /admin/Writer.php show properly
    $pgMetaKeywords = wtkSqlValue('MetaKeywords');
    $pgMetaDescription = wtkSqlValue('MetaDescription');

    $pgMyURL = $gloWebBaseURL . '/blog/' . $pgSlug;
    // BEGIN Social Media meta tags
    $pgTwitterAcct = wtkSqlValue('TwitterAcct');
    $pgOGTitle = wtkSqlValue('OGTitle');
    $pgOGDescription = wtkSqlValue('OGDescription');
    $pgOGImage = wtkSqlValue('OGImage');

    $pgOGtags  = '<meta property="og:url" content="' . $pgMyURL . '" />' . "\n";
    $pgOGtags .= '<meta property="og:type" content="website" />' . "\n";
    if ($pgTwitterAcct != ''):
        if (substr($pgTwitterAcct,0,1) != '@'):
            $pgTwitterAcct = '@' . $pgTwitterAcct;
        endif;
        $pgOGtags .= '<meta name="twitter:site" content="' . $pgTwitterAcct . '" />' . "\n";
        $pgOGtags .= '<meta name="twitter:creator" content="' . $pgTwitterAcct . '" />' . "\n";
        $pgOGtags .= '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        $pgOGtags .= '<meta name="twitter:description" content="' . $pgOGDescription . '" />' . "\n";
    endif;
    if ($pgOGTitle != ''):
        $pgOGtags .= '<meta property="og:title" content="' . $pgOGTitle . '" />' . "\n";
    else:
        $pgOGtags .= '<meta property="og:title" content="' . wtkRemoveStyle($pgPageTitle) . '" />' . "\n";
    endif;
    if ($pgOGDescription != ''):
        $pgOGDescription = wtkReplace($pgOGDescription, '"',"'");
        $pgOGtags .= '<meta name="description" property="og:description" content="' . $pgOGDescription . '" />' . "\n";
    endif;
    if ($pgOGImage != ''):
        $pgOGtags .= '<meta property="og:image" content="' . $gloWebBaseURL . '/blog/imgs/' . $pgOGImage . '" />' . "\n";
    endif;
    //  END  Social Media meta tags
    $pgSQL =<<<SQLVAR
UPDATE `wtkBlog`
 SET `LastViewDate` = NOW(), `Views` = (`Views` + 1)
WHERE `UID` = :UID
SQLVAR;

    $pgSqlFilter = array (
        'UID' => $pgUID
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
endif;

$pgHtm = wtkLoadInclude('files/blog.htm');
$pgHtm = wtkReplace($pgHtm, '@rootPath@', _RootPATH);
$pgHtm = wtkReplace($pgHtm, '@wtkPath@', _WTK_RootPATH);
$pgHtm = wtkReplace($pgHtm, '<title>@PageTitle@</title>', '<title>' . wtkRemoveStyle(wtkReplace($pgPageTitle,'<br>', ' ')) . '</title>');
$pgHtm = wtkReplace($pgHtm, '@PageTitle@', $pgPageTitle);
$pgHtm = wtkReplace($pgHtm, '@BlogContent@', $pgBlogContent);
$pgHtm = wtkReplace($pgHtm, '<!-- @OGtags@ -->', $pgOGtags);
$pgHtm = wtkReplace($pgHtm, '@CompanyLogo@', $gloCoLogo);
$pgHtm = wtkReplace($pgHtm, '@CompanyName@', $gloCoName);
$pgHtm = wtkReplace($pgHtm, '@CurrentYear@', date('Y'));

// BEGIN SideNav bar list of blogs
$pgSQL =<<<SQLVAR
SELECT CONCAT('<a href="',`Slug`,'" class="blog-link">',`PageTitle`,'</a>') AS `Title`,
    COALESCE(`OGFilePath`, '') AS `OGFilePath`,
    COALESCE(`OGImage`, '') AS `OGImage`,
    DATE_FORMAT(`PublishDate`, '%M %D, %Y') AS `Published`
FROM `wtkBlog`
 WHERE `DelDate` IS NULL AND `MakePublic` = :MakePublic
    AND `Slug` <> :Slug
ORDER BY `UID` DESC
SQLVAR;
$pgSqlFilter = array (
    'MakePublic' => 'Y',
    'Slug' => $pgSlug
);
$pgQuery = $gloWTKobjConn->prepare($pgSQL);
$pgQuery->execute($pgSqlFilter);

$pgList = '';
while ($pgRow = $pgQuery->fetch()):
    $pgTitle = $pgRow['Title'];
    $pgDate = $pgRow['Published'];
    $pgPath = $pgRow['OGFilePath'];
    $pgImage = $pgRow['OGImage'];
    $pgPhoto = '';
    if ($pgImage != ''):
        $pgPhoto = '<img src="' . $pgPath . $pgImage . '" class="responsive-img">';
    endif;

    $pgList .=<<<htmVAR
<div class="card">
    <div class="card-content">
        <h5>$pgTitle</h5>
        $pgPhoto
        <p class="nav-date">published $pgDate</p>
    </div>
</div>
htmVAR;
endwhile;
unset($pgQuery);

$pgHtm = wtkReplace($pgHtm, '@ListOfBlogs@', $pgList);
//  END  SideNav bar list of blogs

echo $pgHtm;
exit;
?>
