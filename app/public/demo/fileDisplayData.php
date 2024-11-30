<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgHtm  = '<h4>File Display Demo</h4><br>' . "\n";
$pgHtm .= wtkFormHidden('HasImage', 'Y');

$pgSQL =<<<SQLVAR
SELECT CONCAT(`FilePath`, `NewFileName`) AS `Staff`
  FROM `wtkUsers`
 WHERE `NewFileName` IS NOT NULL
ORDER BY `NewFileName` ASC
SQLVAR;
$pgQuery = $gloWTKobjConn->prepare($pgSQL);
$pgQuery->execute();
$pgAllFiles = $pgQuery->fetchALL(PDO::FETCH_COLUMN, 0);

$pgHtm .= wtkFileDisplay($pgAllFiles, 'Y');
//$pgHtm .= wtkFileDisplay($pgAllFiles, 'Y', 6); // remove last parameter to let it pick optimal

//wtkSearchReplace('wtkDark.css','wtkLight.css'); // change from Dark to Light Mode
wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'File Display', '../wtk/htm/minibox.htm');
?>
