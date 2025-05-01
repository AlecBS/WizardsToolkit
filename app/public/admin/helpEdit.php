<?PHP
$pgSecurityLevel = 75;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `HelpIndex`, `HelpTitle`, `HelpText`, `VideoLink`
  FROM `wtkHelp`
WHERE `UID` = ?
SQLVAR;
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/helpEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Help</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkHelp', 'HelpIndex', 'text', 'Help Index', 'm3 s12');
$pgHtm .= wtkFormText('wtkHelp', 'HelpTitle', 'text', 'Help Title', 'm9 s12');
$pgHtm .= wtkFormText('wtkHelp', 'VideoLink','text','Video Link (YouTube or Vimeo)','m6 s12','N','for YouTube this should be in the format of src="https://www.youtube.com/embed/{yourLink}"');
$pgHtm .= wtkFormTextArea('wtkHelp', 'HelpText', '', 'm12 s12');

$pgHtm .= wtkFormPrimeField('wtkHelp', 'LastModByUserUID', $gloUserUID);

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/helpList.php');
//$pgHtm .= wtkFormPrimeField('wtkHelp', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();
// BEGIN check to see if company prefers WYSIWYG
$pgWYSIWYG = wtkSqlGetOneResult('SELECT `PreferWYSIWYG` FROM `wtkCompanySettings` WHERE `UID` = 1', []);
if ($pgWYSIWYG == 'Y'):
    $pgHtm .= '<input type="hidden" id="HasTinyMCE" name="HasTinyMCE" value="textarea#wtkwtkHelpHelpText">';
endif;
//  END  check to see if company prefers WYSIWYG

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
