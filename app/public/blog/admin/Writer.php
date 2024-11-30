<?PHP
// Note: summernote is very sensitive about what jquery version is used
//       https://code.jquery.com/jquery-3.4.1.slim.min.js  works fine for Summernote
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../../');
    require('../../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PageTitle`,`Slug`,`BlogContent`,`MetaKeywords`,`MetaDescription`,
    `TwitterAcct`, `OGTitle`, `OGDescription`, `OGFilePath`, `OGImage`, `MakePublic`
FROM `wtkBlog`
WHERE `UID` = ?
SQLVAR;
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm = wtkLoadInclude('../files/blog.htm');
$pgHtm = wtkReplace($pgHtm, 'Javascript:wtkStart()', "Javascript:wtkStart('Writer')");
$pgHtm = wtkReplace($pgHtm, '<header>', '<form id="wtkForm" name="wtkForm" action="@wtkPath@lib/Save.php" method="POST">' . "\n" . '<header>');

$pgTmp =<<<htmVAR
<script type="text/javascript" src="summernote/summernote-lite.js" defer></script>
<script type="text/javascript" src="/wtk/js/wtkUtils.js" defer></script>
<script type="text/javascript" src="/wtk/js/wtkFileUpload.js" defer></script>
htmVAR;
$pgHtm = wtkReplace($pgHtm, '<!-- @OGtags@ -->', '<link href="summernote/summernote-lite.min.css" rel="stylesheet">');
$pgHtm = wtkReplace($pgHtm, '</head>', $pgTmp . '</head>');
$pgHtm = wtkReplace($pgHtm, '@ListOfBlogs@', 'Links to your blogs will be displayed here');
$pgHtm = wtkReplace($pgHtm, '="files/', '="../files/');
$pgHtm = wtkReplace($pgHtm, '@rootPath@', _RootPATH);
$pgHtm = wtkReplace($pgHtm, '@wtkPath@', _WTK_RootPATH);
$pgHtm = wtkReplace($pgHtm, '@PageTitle@', 'Blog Writer');
$pgHtm = wtkReplace($pgHtm, '@CompanyLogo@', $gloCoLogo);
$pgHtm = wtkReplace($pgHtm, '@CompanyName@', $gloCoName);
$pgHtm = wtkReplace($pgHtm, '@CurrentYear@', date('Y'));

// BEGIN editable fields
$pgTmp  = '<div class="row">' . "\n";
$pgTmp .= wtkFormTextArea('wtkBlog', 'BlogContent');
$pgTmp  = wtkReplace($pgTmp, '<textarea','<h3>Write your blog here</h3><textarea');
$pgTmp  = wtkReplace($pgTmp, '<label ','<label class="hide" ');
$pgTmp  = wtkReplace($pgTmp, 'materialize-textarea','materialize-textarea snote');
$pgTmp .= '</div>' . "\n";

$pgHtm = wtkReplace($pgHtm, '@BlogContent@', $pgTmp);

$pgTmp =<<<htmVAR
<hr>
<div class="card">
    <div class="card-content">
        <h3>Edit Blog Details <small><button class="btn" type="button" onclick="JavaScript:saveBlog();">Save</button>
         &nbsp; <a href="index.php" class="btn grey">Cancel</a>
        </small></h3>
        <div class="row">
htmVAR;

$pgTmp .= wtkFormHidden('ID1', $gloId);
$pgTmp .= wtkFormHidden('UID', wtkEncode('UID'));
$pgTmp .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgTmp .= wtkFormHidden('wtkDesign', 'MPA'); // this forces Save.php to use MPA mode
$pgTmp .= wtkFormHidden('wtkGoToURL', '/blog/admin/index.php');
$pgTmp .= wtkFormPrimeField('wtkBlog', 'UserUID', $gloUserUID);
$pgTmp .= wtkFormText('wtkBlog', 'PageTitle');
$pgSlug = wtkFormText('wtkBlog', 'Slug', 'text', 'Slug', 'm3 s12', 'Y', 'used as web URL for SEO');
$pgSlug = wtkReplace($pgSlug,'<input required ', '<input onblur="JavaScript:makeSlug(this.value)" required ');
$pgTmp .= $pgSlug;
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgSwitch = wtkFormSwitch('wtkBlog', 'MakePublic', 'Make Public', $pgValues, 'm2 s12');
$pgSwitch = wtkReplace($pgSwitch, '">Off','">No');
$pgSwitch = wtkReplace($pgSwitch, 'On</label>','Yes</label>');
$pgTmp .= $pgSwitch;

$pgTmp .= wtkFormText('wtkBlog', 'MetaKeywords');
$pgTmp .= wtkFormTextArea('wtkBlog', 'MetaDescription','','m6 s12');
$pgTmp .= wtkFormText('wtkBlog', 'TwitterAcct', 'text', 'Twitter Account for Sharing', 'm3 s12', 'N', 'should start with @');
$pgTmp .= wtkFormText('wtkBlog', 'OGTitle', 'text', 'Title for Social Media Sharing','m9 s12');
$pgTmp .= '</div><div class="row">' . "\n";

if ($gloWTKmode != 'ADD'):
    $pgFile = wtkFormFile('wtkBlog', 'OGFilePath','/blog/imgs/','OGImage','Social Media Photo','m3 s12');
    $pgFile = wtkReplace($pgFile,'material-icons','material-icons blue');
    $pgTmp .= $pgFile;
endif;
$pgTmp .= wtkFormTextArea('wtkBlog', 'OGDescription', 'Description for Social Media Sharing', 'm9 s12');
$pgTmp .= wtkFormWriteUpdField();
$pgTmp .= '</div></div></div>' . "\n" . '</header>' . "\n";
$pgHtm = wtkReplace($pgHtm, '</header>', $pgTmp);
$pgHtm = wtkReplace($pgHtm, '</main>', '</main></form>');
//  END  editable fields
$pgHtm = wtkReplace($pgHtm, 'navbar-fixed-top','navbar-fixed-top hide');

echo $pgHtm;
?>
