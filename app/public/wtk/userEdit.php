<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
endif;

if ($gloId == 0): // from My Profile (wtk/user.php) page
    $gloId = $gloUserUID;
endif;

$pgLang = wtkGetParam('wtkLang');
if ($pgLang != ''):
    if ($gloId == $gloUserUID):
        $gloLang = $pgLang;
        wtkSetCookie('wtkLang', $pgLang);
    endif;
    $pgHdrMsg = '<h5 class="green-text">' . wtkLang('You data has been saved') . '</h5>';
else:
    if ($gloId == $gloUserUID):
        $pgHdrMsg = '<h4>' . wtkLang('Edit Your Profile') . '</h4>';
    else:
        $pgHdrMsg = '<h4>' . wtkLang('Edit Profile') . '</h4>';
    endif;
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `FirstName`, `LastName`, `Email`, `Phone`, `CellPhone`, 'esp' AS `LangPref`,
    `PersonalURL`, `WebPassword`, `UseSkype`, `FilePath`, `NewFileName`
  FROM `wtkUsers`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgHtm =<<<htmVAR
<div class="container">
    $pgHdrMsg<br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkUsers', 'FirstName');
$pgHtm .= wtkFormText('wtkUsers', 'LastName');
$pgHtm .= wtkFormText('wtkUsers', 'Email', 'email');
$pgTmpMode = $gloWTKmode;
$gloWTKmode = 'ADD';
$pgTmp = wtkFormText('wtkUsers', 'WebPassword', 'password');
$pgTmp = wtkReplace($pgTmp, '<input type','<input onchange="JavaScript:checkPassStrength(this.value)" type');
$pgHtm .= $pgTmp;
$gloWTKmode = $pgTmpMode;
$pgHtm .= wtkFormText('wtkUsers', 'Phone', 'tel');
$pgHtm .= wtkFormText('wtkUsers', 'CellPhone', 'tel');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('wtkUsers', 'UseSkype', 'Use Skype for calls', $pgValues);

//$pgHtm .= wtkFormFile('Upload Photo', 'wtkUsers', 'NewFileName', '../imgscli/', '../../imgscli/');
// function wtkFormFile($fncLabel, $fncTable, $fncField, $fncPathShow, $fncPathSave = '../', $fncColSpan = '1') {
/*
    <input type="file" onchange="showFile()" accept="image/*"><br><br>
    <img id="imgPreview" src="" width="150">
    <div id="imgPreview2"></div>

Clean code:
https://www.digitalocean.com/community/tutorials/js-file-reader
*/

$pgHtm .= wtkFormFile('wtkUsers','FilePath','/imgs/user/','NewFileName','User Photo','m6 s12','myPhoto');

// $pgFilePath = wtkSqlValue('FilePath');
// $pgNewFileName = wtkSqlValue('NewFileName');
// if ($pgNewFileName != ''):
//     $pgPhoto = $pgFilePath . $pgNewFileName;
// else:
//     $pgPhoto = '';
// endif;
/*
$pgHtm .=<<<htmVAR
<div class="input-field col m6 s12">
    <table><tr><td width="150px">
        <img id="imgPreview" src="$pgPhoto" width="150">
      </td><td>
      <label for="wtkUpload" class="fileUpload">
          <input type="file" id="wtkUpload" style="display: none;">
      Choose File
      </label>
      </td></tr>
    </table>
    <input type="hidden" id="wtkfPath" value="/imgs/user/">
    <input type="hidden" id="wtkfColPath" value="FilePath">
    <input type="hidden" id="wtkfColFile" value="NewFileName">
    <input type="hidden" id="wtkfRefresh" value="myPhoto">
    <div id="photoProgressDIV" class="progress hide">
        <div id="photoProgress" class="determinate" style="width: 0%"></div>
    </div>
    <div id="uploadStatus"></div>
    <span id="uploadFileSize"></span>
    <span id="uploadProgress"></span>
</div>
htmVAR;
*/
// var textFile = /text.*/;
//
//  if (file.type.match(textFile)) {
//     fr.onload = function (event) {
//        preview.innerHTML = event.target.result;
//     }
//  } else {
//     preview.innerHTML = "<span class='error'>It doesn't seem to be a text file!</span>";
//  }
//  fr.readAsText(file);

$pgHtm .= wtkFormText('wtkUsers', 'PersonalURL');

if ($gloId == $gloUserUID):
    $pgLang = wtkGetCookie('wtkLang');
    $pgLangPref = wtkLang('Language Preference');
    $pgTmp =<<<htmVAR
<div class="input-field col m6 s12">
    <select id="wtkLang" name="wtkLang" onchange="JavaScript:wtkLangUpdate(this.value)">
        <option value="eng">English</option>
        <option value="esp">Espa&ntilde;ol</option>
        <option value="hin">हिन्दी</option>
    </select>
    <label for="wtkLang" class="active">$pgLangPref</label>
</div>
htmVAR;
    $pgTmp = wtkReplace($pgTmp, 'value="' . $gloLang . '"','value="' . $gloLang . '" selected');
else:
    $pgSQL = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'LangPref' ORDER BY `LookupValue` ASC";
    $pgTmp = wtkFormSelect('wtkUsers', 'LangPref', $pgSQL, [], 'LookupDisplay', 'LookupValue','Language Preference');
endif;
$pgHtm .= wtkFormHidden('wtkfImgWidth', 300);
$pgHtm .= wtkFormHidden('wtkfImgHeight', 300);
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('HasSelect', 'Y');
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
if (wtkGetPost('from') == 'reports'):
    $pgHtm .= wtkFormHidden('rng', $gloRNG);
    $pgHtm .= wtkFormHidden('wtkGoToURL', '../reports.php');
else:
    $pgHtm .= wtkFormHidden('wtkGoToURL', '../user.php');
endif;
$pgHtm .= $pgTmp . "\n";
$pgHtm .= '            </div><br>' . "\n";
$pgMode = wtkGetParam('Mode');
if ($pgMode == 'modal'):
    $pgHtm .= '<div class="center">';
    $pgHtm .= wtkModalUpdateBtns('/wtk/lib/Save',''); // if do not want to refresh page
    $pgHtm .= '</div>' . "\n";
    $pgHtm  = wtkReplace($pgHtm, '="wtkForm"','="F"');
else:
    $pgHtm .= wtkUpdateBtns() . "\n";
endif;

$pgHtm .= wtkFormWriteUpdField();
$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;

echo $pgHtm;

if ($gloUserSecLevel == 99): // Programmer level\
    // add special debugging code here
endif;
exit;
?>
