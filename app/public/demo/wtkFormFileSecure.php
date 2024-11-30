<?php
$pgSecurityLevel = 1;
$gloSiteDesign  = 'MPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT CRC32(`UID`) AS `UID`, `OrigFileName` AS `OriginalFilename`, `FileSize`,
    CONCAT(`FilePath`, `NewFileName`) AS `PathAndNewFilename`
 FROM `wtkFiles`
WHERE `TableRelation` = 'demo'
ORDER BY `UID` DESC
SQLVAR;
$gloColumnAlignArray = array (
	'FileSize' => 'right'
);
$gloEditPage = '/wtk/viewFile';
$pgFileList = wtkBuildDataBrowse($pgSQL);
$pgFileList = wtkReplace($pgFileList, '>edit<','>visibility<');
$pgFileList = wtkReplace($pgFileList, '<a class=','<a target="_blank" class=');
if (wtkGetParam('p') != ''): // called from ajaxFillDiv
    echo $pgFileList;
    exit;
endif;

$gloWTKmode = 'ADD';
$pgUpload  = wtkFormFile('wtkFiles','FilePath','/demo/imgs/','NewFileName','Pick File','m6 s12','','Y','');
// For example, instead of /demo/imgs you could put '../docs/imgs/'
    // and it would find files in a /app/docs/imgs/ folder next to /app/public
// BEGIN next line ONLY necessary because no other fields are in form
//$pgUpload .= wtkFormHidden('T', wtkEncode('wtkFiles'));
// END above line should not be included if there are other file form fields
$pgUpload .= wtkFormPrepUpdField('wtkFiles', 'NewFileName', 'file');
$pgUpload .= wtkFormWriteUpdField();
$pgUpload .= wtkFormHidden('ID1', 0);
$pgUpload .= wtkFormHidden('UID', wtkEncode('UID'));
$pgUpload .= wtkFormHidden('UserUID', $gloUserUID);
$pgUpload .= wtkFormHidden('wtkMode', 'ADD');
$pgUpload .= wtkFormHidden('tabRel', 'demo');
$pgUpload .= wtkFormHidden('wtkfRefreshDIV', 'wtkFormFileSecure'); // this tells JS to refresh uploadFileDIV DIV by calling this page

$pgHtm =<<<htmVAR
<br><br>
<div class="card">
    <div class="card-content">
        <h4>Secure File Upload</h4><br>
        <p>This uses the wtkFormFile to upload file to a folder on the server that
            is not accessible to web pages. The link goes to /wtk/viewFile.php which pulls the file
            from the secure location and displays it directly to the browser.</p>
        <br><h5>Photos
            <small id="uploadFileBtn" class="right">
                <a onclick="JavaScript:wtkShowImageUpload()" class="btn btn-primary btn-floating waves-effect waves-light"><i class="material-icons">add</i></a>
            </small>
        </h5>
        <div id="uploadFileDIV" class="hide">
            <form id="wtkForm" name="wtkForm" method="post">
                $pgUpload
            </form>
        </div>
        <div id="displayFileDIV">
htmVAR;

$pgHtm .= $pgFileList . "\n";
$pgHtm .= '</div></div></div>' . "\n";

wtkProtoType($pgHtm);
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
?>
