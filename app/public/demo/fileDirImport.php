<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgHtm =<<<htmVAR
<h4>File Import Demo</h4><br>
<p>Pass in the Directory to read, and all images in that directory will be inserted into `wtkFiles` data table.</p>
<br>
htmVAR;
$pgHtm .= wtkFormHidden('HasImage', 'Y');

$pgSubDirectory = 'YourDirectory';
$pgFiles = wtkReadDir($pgSubDirectory,'image'); // subfolder with images only

// Change below for your needs
$pgSQL =<<<SQLVAR
INSERT INTO `wtkFiles` (`UserUID`,`TableRelation`,`FilePath`,`FileExtension`,`OrigFileName`,`NewFileName`,`FileSize`)
 VALUES (2, 'seedImage', '/imgs/seed/', 'JPG', :OrigFileName,:NewFileName,:FileSize);
SQLVAR;

$pgSqlScripts = '';
foreach ($pgFiles as $pgImage):
    $pgFileSize = filesize(__DIR__ . '/' . $pgImage);
    $pgImage = wtkReplace($pgImage, $pgSubDirectory . '/', '');
    $pgTmp = wtkReplace($pgSQL, ':OrigFileName', "'$pgImage'");
    $pgTmp = wtkReplace($pgTmp, ':NewFileName', "'$pgImage'");
    $pgTmp = wtkReplace($pgTmp, ':FileSize', $pgFileSize);
    $pgSqlScripts .= $pgTmp . "\n";
    /*
     * Use this if inserting into local database
    $pgSqlFilter = array(
        'OrigFileName' => $pgImage,
        'NewFileName' => $pgImage,
        'FileSize' => $pgFileSize
    );
    wtkExecSql($pgSQL, $pgSqlFilter);
    */
endforeach;

$pgHtm .= '<textarea class="materialize-textarea code-text">' . $pgSqlScripts . '</textarea>';
$pgHtm .= wtkFormHidden('HasTextArea','Y');
$pgHtm .= wtkFileDisplay($pgFiles, 'Y'); // remove last parameter to let it pick optimal

wtkProtoType($pgHtm);
wtkSearchReplace('wtkDark.css','wtkLight.css'); // change from Dark to Light Mode
wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'File Display', '../wtk/htm/minibox.htm');
?>
