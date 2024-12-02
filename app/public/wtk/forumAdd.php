<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `UID`, `ForumName`, `ForumNote`
  FROM `wtkForum`
WHERE `UID` = ?
SQLVAR;

$gloSiteDesign  = 'SPA';
$gloWTKmode = 'ADD';

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Create Forum Topic</h4><br>
    <div class="content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkForum', 'ForumName');
$pgHtm .= wtkFormTextArea('wtkForum', 'ForumNote');

$pgHtm .= wtkFormHidden('ID1', 0);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormPrimeField('wtkForum', 'CreatedByUserUID', $gloUserUID);
$pgHtm .= wtkFormHidden('wtkMode', 'ADD');
$pgHtm .= wtkFormHidden('wtkGoToURL', '../forumList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
