<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgApiKey = wtkGetParam('apiKey','old');
if ($pgApiKey != 'old'):
    $pgLoginSQL =<<<SQLVAR
SELECT `UserUID`
 FROM `wtkLoginLog`
WHERE `apiKey` = :apiKey
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
    $pgSqlFilter = array (
        'apiKey' => $pgApiKey
    );
    $gloUserUID = wtkSqlGetOneResult($pgLoginSQL, $pgSqlFilter, 0);
    wtkSqlGetRow($pgLoginSQL, $pgSqlFilter);
endif;

$pgSQL =<<<SQLVAR
SELECT h.`HelpTitle`, h.`HelpText`, h.`VideoLink`, u.`CanEditHelp`
  FROM `wtkHelp` h, `wtkUsers` u
 WHERE u.`UID` = :UserUID AND h.`UID` = :HelpUID
SQLVAR;
$pgSqlFilter = array (
    'HelpUID' => $gloId,
    'UserUID' => $gloUserUID
);

wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgHelpTitle = wtkSqlValue('HelpTitle');
if ($pgHelpTitle == ''):
    $pgHelpTitle = 'Not Defined Yet';
endif;
$pgHelpText = wtkSqlValue('HelpText');
$pgVideoURL = wtkSqlValue('VideoLink');
$pgCanEditHelp = wtkSqlValue('CanEditHelp');

if ($pgVideoURL != ''):
    $pgPos = stripos($pgVideoURL, 'vimeo.com');
    if ($pgPos !== false): // Vimeo video
        $pgVid = wtkReplace($pgVideoURL, 'https://www.vimeo.com/','');
        $pgVid = wtkReplace($pgVid, 'https://vimeo.com/','');
        $pgTmp  = '<iframe webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" src="//player.vimeo.com/video/';
        $pgTmp .= $pgVid . '" class="note-video-clip" width="640" height="360" frameborder="0"></iframe>';
    else: // not Vimeo, assume must be YouTube
        $pgVid = wtkReplace($pgVideoURL, 'https://youtu.be/','');
        $pgVid = wtkReplace($pgVid, '&feature=emb_imp_woyt','');
        $pgVid = wtkReplace($pgVid, '&feature=youtu.be','');
//        https://www.youtube.com/watch?v=nMZA7Emr5z4
        $pgVid = wtkReplace($pgVid, 'https://www.youtube.com/watch?v=','');
        $pgVid = wtkReplace($pgVid, 'https://www.youtube.com/embed/','');
    	$pgTmp  = '<iframe width="560" height="315" src="https://www.youtube.com/embed/';
        $pgTmp .= $pgVid . '" title="YouTube video player" frameborder="0"';
        $pgTmp .= ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    endif;
    $pgVideoURL = '<br><br><div align="center">' . $pgTmp . '</div>';
endif;

$pgEditHelp = '';
$pgEditBtn = '';
$pgHtm = '';
if ($pgCanEditHelp == 'Y'):
    if ($gloCSSLib == 'TailwindCSS'):
        $pgHtm .=<<<htmVAR
<div id="editHelp" class="hidden">
    <h4 class="text-2xl font-semibold text-gray-800 mb-6">Edit Help</h4>
    <form method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 lg-grid-2 gap-6">
htmVAR;
        $pgEditBtn =<<<htmVAR
    <a id="editHelpBtn" class="btn btn-secondary" onclick="wtkEditHelp()">Edit</a>&nbsp;
    <a id="saveHelpBtn" class="btn btn-primary modal-close hidden" onclick="wtkSaveHelp($gloId);wtkCloseModal()">Save</a>
htmVAR;
    else:
        $pgEditBtn =<<<htmVAR
    <a id="editHelpBtn" class="btn blue waves-effect right" onclick="wtkEditHelp()">Edit</a>
    <a id="saveHelpBtn" class="btn modal-close waves-effect right hide" onclick="wtkSaveHelp($gloId)">Save</a>
htmVAR;

    //  $pgHtm .= wtkFormText('wtkHelp', 'HelpIndex', 'text', 'Help Index', 'm3 s12');
        $pgHtm .=<<<htmVAR
    <div id="editHelp" class="card hide">
        <div class="card-content">
            <h4>Edit Help</h4>
            <form>
                <div class="row">
htmVAR;
    endif;
    $pgHtm .= wtkFormText('wtkHelp', 'HelpTitle', 'text', 'Help Title', 'm12 s12');
    $pgHtm .= wtkFormText('wtkHelp', 'VideoLink','text','Video Link (YouTube or Vimeo)','m12 s12','N','for YouTube this should be in the format of src="https://www.youtube.com/embed/{yourLink}"');

    $pgTmp  = wtkFormTextArea('wtkHelp', 'HelpText', '', 'm12 s12');
//    $pgTmp  = wtkReplace($pgTmp, 'materialize-textarea','materialize-textarea snote');
    // BEGIN check to see if company prefers WYSIWYG
// ABS removed for Blast-Me
//    $pgWYSIWYG = wtkSqlGetOneResult('SELECT `PreferWYSIWYG` FROM `wtkCompanySettings` WHERE `UID` = 1', []);
//    if ($pgWYSIWYG == 'Y'):
//        $pgHtm .= '<input type="hidden" id="HasModalTinyMCE" name="HasModalTinyMCE" value="textarea#wtkwtkHelpHelpText">';
//    endif;
    //  END  check to see if company prefers WYSIWYG
    $pgHtm .= $pgTmp ;
    $pgHtm .=<<<htmVAR
            </div>
        </form>
    </div>
htmVAR;
    if ($gloCSSLib == 'MaterializeCSS'):
        $pgHtm .= '</div>';
    endif;
    $pgEditHelp = $pgHtm;
endif;

/*
<div class="input-field col s6">
    <input id="HelpTitle" type="text" class="validate" value="$pgHelpTitle">
    <label for="HelpTitle" class="active">Help Title</label>
</div>
*/
$pgHtm =<<<htmVAR
<div class="modal-content">
    <h2 class="text-2xl font-bold text-center">$pgHelpTitle</h2><br>
    <input type="hidden" id="HasTextArea" name="HasTextArea" value="wtkwtkHelpHelpText">
    $pgHelpText
    $pgVideoURL
    $pgEditHelp
</div>
htmVAR;
if ($gloCSSLib == 'MaterializeCSS'):
    $pgHtm .=<<<htmVAR
<div class="modal-footer bg-second">
    <a class="btn btn-save modal-close waves-effect left" onclick="wtkFixSideNav()">Close</a>
    $pgEditBtn
</div>    
htmVAR;
else:
    if ($pgCanEditHelp == 'Y'):
        $pgHtm .=<<<htmVAR
<div class="text-center mt-5">
    <a class="btn" onclick="wtkCloseModal()">Close</a>
    $pgEditBtn
</div>    
htmVAR;
    endif;
endif;

echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
