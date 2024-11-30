<?php
$gloLoginRequired = false;
$gloSiteDesign  = 'MPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
wtkPageProtect('wtk4LowCode');

$pgSQL =<<<SQLVAR
SELECT CRC32(`UID`) AS `UID`, `OrigFileName` AS `OriginalFilename`, `FileSize`,
    CONCAT(`FilePath`, `NewFileName`) AS `PathAndNewFilename`
 FROM `wtkFiles`
WHERE `TableRelation` = 'test'
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
$pgPath = wtkGetParam('Path', '../docs/wtkUsers/');

$pgUpload  = wtkFormFile('wtkFiles','FilePath',$pgPath,'NewFileName','Pick File','m6 s12','','Y','');
// BEGIN next line ONLY necessary because no other fields are in form
//$pgUpload .= wtkFormHidden('T', wtkEncode('wtkFiles'));
// END above line should not be included if there are other file form fields
$pgUpload .= wtkFormPrepUpdField('wtkFiles', 'NewFileName', 'file');
$pgUpload .= wtkFormWriteUpdField();
$pgUpload .= wtkFormHidden('ID1', 0);
$pgUpload .= wtkFormHidden('UID', wtkEncode('UID'));
$pgUpload .= wtkFormHidden('UserUID', $gloUserUID);
$pgUpload .= wtkFormHidden('wtkMode', 'ADD');
$pgUpload .= wtkFormHidden('tabRel', 'test');
$pgUpload .= wtkFormHidden('wtkfRefreshDIV', 'wtkFormFileSecure'); // this tells JS to refresh uploadFileDIV DIV by calling this page

$pgHtm =<<<htmVAR
<br><br>
<div class="card">
    <div class="card-content">
        <h2>Secure File Upload</h2>
        <p>This uses the wtkFormFile to upload file to a folder on the server that
            is not accessible to web pages. The link goes to /wtk/viewFile.php which pulls the file
            from the secure location and displays it directly to the browser.</p>
        <div class="card">
            <div class="card-content">
                <h4>Set Path to Upload File to</h4>
                <form action="wtkFormFileSecure.php?Debug=Y" id="pathForm" name="pathForm" method="post">
                    <div class="input-field">
                        <input placeholder="path related to this folder or wtk/lib folder" id="Path" name="Path" type="text" value="$pgPath">
                        <label for="Path">Path</label>
                    </div>
                    <input type="submit" class="btn" value="Change Path" />
                </form>
            </div>
        </div>
        <br>
        <h5>Files Uploaded
            <small id="uploadFileBtn" class="right">
                uploading to $pgPath &nbsp;&nbsp;
                <a onclick="JavaScript:wtkShowImageUpload()" class="btn btn-primary btn-floating waves-effect waves-light"><i class="material-icons">add</i></a>
            </small>
        </h5>
        <p>If after clicking the <span class="green-text">Upload</span> button the
            refresh does not show the file, check your WebDev tools to see what error
            was returned from fileUpload.php</p>
        <div id="uploadFileDIV" class="hide">
            <form id="wtkForm" name="wtkForm" method="post">
                $pgUpload
            </form>
        </div>
        <div id="displayFileDIV">
htmVAR;

$pgHtm .= $pgFileList . "\n";
$pgHtm .= '</div></div></div>' . "\n";

wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
?>
