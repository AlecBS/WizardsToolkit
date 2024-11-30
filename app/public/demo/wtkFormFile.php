<?php
$gloLoginRequired = false;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php

$pgSQL =<<<SQLVAR
SELECT `FirstName`,`FilePath`,`NewFileName`
 FROM `wtkUsers`
WHERE `UID` = ?
SQLVAR;
$gloId = 1;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgFirstName = wtkSqlValue('FirstName');

$pgHtm  = '<div class="container"><br>' . "\n";
$pgHtm .= '  <div class="card">' . "\n";
$pgHtm .= '    <div class="card-content">' . "\n";
$pgHtm .= '<h3>File Upload Demo</h3><br>' . "\n";
if (wtkGetPost('save') == 'ok'):
    $pgHtm .= '<p class="green-text">File uploaded - if not visible it is because this page reloaded too fast.' . "\n";
    $pgHtm .= ' Normally after saving return to listing page. Try <a onclick="JavaScript:ajaxGo(\'wtkFormFile\')">reloading</a>.</p>' . "\n";
endif;
$pgHtm .= "<p>Updating `NewFileName` for $pgFirstName with UID of $gloId.</p>" . "\n";
$pgHtm .= '<h4>wtkFormFile example</h4>' . "\n";
$pgHtm .= '<form id="wtkForm" name="wtkForm" method="post">' . "\n";

$pgHtm .= wtkFormFile('wtkUsers','FilePath','/imgs/user/','NewFileName','User Photo','m6 s12');

$pgHtm .= wtkUpdateBtns() . "\n";

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/wtkFormFile.php');
//$pgHtm .= wtkFormHidden('Debug', 'Y'); // uncomment to see debug from Save.php
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('save', 'ok');
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= '</form></div></div></div>' . "\n";

echo $pgHtm;
exit;
?>
