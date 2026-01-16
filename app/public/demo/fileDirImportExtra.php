<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL);
$pgHtm =<<<htmVAR
<h4>File Import Demo</h4><br>
<p>Pass in the Directory to read, and all images in that directory will be inserted into `wtkFiles` data table.</p>
<p>A client sent me more images to import for him but many of them I had previously imported and uploaded to 
  the production server, so there were many duplicates. This file gives a demo of checking a past import file and both 
  excluding those files from this INSERT scripts and deletes them from your local server environment so you can have
  a clean upload with no duplicates.</p>
<br>
htmVAR;
$pgHtm .= wtkFormHidden('HasImage', 'Y');

$pgIgnoreFiles = wtkLoadInclude('pastFiles.txt');

$pgSubDirectory = 'Extra'; // put the files you want to import in this subfolder here
$pgFiles = wtkReadDir($pgSubDirectory,'image'); // subfolder with images only

// Change below for your needs
$pgSQL =<<<SQLVAR
INSERT INTO `wtkFiles` (`UserUID`,`TableRelation`,`FilePath`,`FileExtension`,`OrigFileName`,`NewFileName`,`FileSize`)
 VALUES (2, 'seedImage', '/imgs/seed/', 'JPG', :OrigFileName,:NewFileName,:FileSize);
SQLVAR;

$pgSqlScripts = '';
foreach ($pgFiles as $pgImage):
    if (substr($pgImage, -6, 6) == '-1.JPG'):
        unlink($pgImage); // remove file because duplicate
    else:
        $pgPos = stripos($pgIgnoreFiles, wtkReplace($pgImage, $pgSubDirectory . '/', ''));
        if ($pgPos === false):
            $pgFileSize = filesize(__DIR__ . '/' . $pgImage);
            $pgImage = wtkReplace($pgImage, $pgSubDirectory . '/', '');
            $pgTmp = wtkReplace($pgSQL, ':OrigFileName', "'$pgImage'");
            $pgTmp = wtkReplace($pgTmp, ':NewFileName', "'$pgImage'");
            $pgTmp = wtkReplace($pgTmp, ':FileSize', $pgFileSize);
            $pgSqlScripts .= $pgTmp . "\n";
        else:
            unlink($pgImage); // remove file because previously uploaded to server
        endif;
    endif;
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
