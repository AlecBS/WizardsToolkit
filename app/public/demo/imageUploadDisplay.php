<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSiteDesign  = 'MPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php

$pgSQL =<<<SQLVAR
SELECT CONCAT(`FilePath`, `NewFileName`) AS `AnyName`
 FROM `wtkFiles`
WHERE `TableRelation` = ?
SQLVAR;
$pgQuery = $gloWTKobjConn->prepare($pgSQL);
$pgQuery->execute(['demo']);
$pgAllFiles = $pgQuery->fetchALL(PDO::FETCH_COLUMN, 0);

$pgFileList = wtkFileDisplay($pgAllFiles, 'N');

if (wtkGetParam('p') != ''): // called from ajaxFillDiv
    echo $pgFileList;
    exit;
endif;

$gloWTKmode = 'ADD';
$pgUpload  = wtkFormFile('wtkFiles','FilePath','/demo/imgs/','NewFileName','Pick Photo','m6 s12','imageUploadDisplay','Y');
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
//$pgUpload .= wtkFormHidden('wtkfRefreshDIV', 'imageUploadDisplay'); // this tells JS to refresh uploadFileDIV DIV by calling this page

$pgHtm =<<<htmVAR
<br><br>
<div class="card">
    <div class="card-content">
        <h4>File Upload and Display</h4><br>
        <p>This uses the wtkFormFile and wtkFileDisplay functions.</p>
        <p>By default with WTK file uploads require the user is logged in.
            For this page to demo upload without requiring logging in,
            change /wtk/fileUpload.php by uncommenting this code near the top.</p>
            <pre><code>
// if (&dollar;pgApiKey == ''): // comment this out for production server; should not allow file uploads without an account
//     &dollar;gloLoginRequired = false;
// else:
//     &dollar;pgSecurityLevel = 1;
// endif;
            </code></pre>
        <h5>Photos
            <small id="uploadFileBtn1" class="right">
                <a onclick="JavaScript:wtkShowImageUpload(1)" class="btn btn-primary btn-floating waves-effect waves-light"><i class="material-icons">add</i></a>
            </small>
        </h5>
        <br>
        <div id="uploadFileDIV1" class="hide">
            <form id="wtkForm" name="wtkForm" method="post">
                $pgUpload
            </form>
        </div>
        <div id="displayFileDIV1">
htmVAR;

$pgHtm .= $pgFileList . "\n";
$pgHtm .= '</div></div></div>' . "\n";

wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
?>
