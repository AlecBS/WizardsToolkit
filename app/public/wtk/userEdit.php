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
SELECT `UID`, `FirstName`, `LastName`, `Email`, `Phone`, `CellPhone`, `LangPref`,
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
$pgHtm .= wtkFormText('wtkUsers', 'Email', 'email', 'Email', 'm6 s12', 'Y');
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

$pgHtm .= wtkFormFile('wtkUsers','FilePath','/imgs/user/','NewFileName','User Photo','m6 s12','myPhoto');

$pgHtm .= wtkFormText('wtkUsers', 'PersonalURL');

$pgSQL = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'LangPref' ORDER BY `LookupValue` ASC";
$pgTmp = wtkFormSelect('wtkUsers','LangPref',$pgSQL,[],'LookupDisplay','LookupValue','Language Preference');
if ($gloId == $gloUserUID):
    $pgTmp = wtkReplace($pgTmp, '<select ','<select onchange="JavaScript:wtkLangUpdate(this.value)" ');
endif;
$pgHtm .= $pgTmp;

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
