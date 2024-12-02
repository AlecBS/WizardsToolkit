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
    $pgEditBtn  = '<a id="editHelpBtn" class="btn blue waves-effect right" onclick="JavaScript:wtkEditHelp()">Edit</a>';
    $pgEditBtn .= '<a id="saveHelpBtn" class="btn modal-close waves-effect right hide" onclick="JavaScript:wtkSaveHelp(' . $gloId . ')">Save</a>';

//  $pgHtm .= wtkFormText('wtkHelp', 'HelpIndex', 'text', 'Help Index', 'm3 s12');
    $pgHtm .=<<<htmVAR
    <div id="editHelp" class="card hide">
        <div class="card-content">
            <h4>Edit Help</h4>
            <form>
                <div class="row">
htmVAR;

    $pgHtm .= wtkFormText('wtkHelp', 'HelpTitle', 'text', 'Help Title', 'm12 s12');
    $pgHtm .= wtkFormText('wtkHelp', 'VideoLink','text','Video Link (YouTube or Vimeo)', 'm12 s12');
    $pgTmp  = wtkFormTextArea('wtkHelp', 'HelpText', '', 'm12 s12');
    $pgTmp  = wtkReplace($pgTmp, 'materialize-textarea','materialize-textarea snote');
    $pgHtm .= $pgTmp ;
    $pgHtm .=<<<htmVAR
                </div>
            </form>
        </div>
    </div>
htmVAR;
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
    <h2>$pgHelpTitle</h2><br>
    <input type="hidden" id="HasTextArea" name="HasTextArea" value="wtkwtkHelpHelpText">
    $pgHelpText
    $pgVideoURL
    $pgEditHelp
</div>
<div class="modal-footer bg-second">
    <a class="btn btn-save modal-close waves-effect left" onclick="JavaScript:wtkFixSideNav()">Close</a>
    $pgEditBtn
</div>
htmVAR;

echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
