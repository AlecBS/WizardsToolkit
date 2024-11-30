<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
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
$pgHtm .= "<p>Updating `NewFileName` for $pgFirstName with UID of $gloId.</p>" . "\n";
$pgHtm .= '<h4>wtkFileUpload example</h4>' . "\n";
$pgHtm .= '<form id="wtkForm" name="wtkForm">' . "\n";

//$pgHtm .= wtkFormFile('wtkUsers','FilePath','/imgs/user/','NewFileName','User Photo','m6 s12');
$pgHtm .= wtkFileUpload('wtkUsers','FilePath','/imgs/user/','NewFileName','myPhoto');

$pgHtm .= wtkUpdateBtns() . "\n";

$pgHtm .= wtkFormHidden('wtkGoToURL', 'dashboard');
//$pgHtm .= wtkFormHidden('Debug', 'Y'); // uncomment to see debug from Save.php
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
// $pgHtm .= wtkFormHidden('wtkDesign', 'SPA'); // way to force MPA or SPA to Save.php if that is not standard for your website
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= '</form></div></div></div>' . "\n";

echo $pgHtm;
exit;
?>
