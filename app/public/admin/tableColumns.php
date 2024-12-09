<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

if (wtkGetPost('p') == 'photos'):
    $pgTableName = $gloRNG;
else:
    $pgTableName = $gloId;
endif;

$gloWTKmode = 'ADD';
$pgUpload  = wtkFormFile('wtkFiles','FilePath','/exports/','NewFileName','Pick File to Upload','s12','','Y','');
// For example, instead of /demo/imgs you could put '../docs/imgs/'
    // and it would find files in a /app/docs/imgs/ folder next to /app/public
// BEGIN next line ONLY necessary because no other fields are in form
//$pgUpload .= wtkFormHidden('T', wtkEncode('wtkFiles'));
// END above line should not be included if there are other file form fields
$pgUpload .= wtkFormPrepUpdField('wtkFiles', 'NewFileName', 'file');
$pgUpload .= wtkFormWriteUpdField();
$pgUpload .= wtkFormHidden('ID1', $pgTableName);
$pgUpload .= wtkFormHidden('UID', wtkEncode('UID'));
$pgUpload .= wtkFormHidden('UserUID', $gloUserUID);
$pgUpload .= wtkFormHidden('wtkMode', 'ADD');
$pgUpload .= wtkFormHidden('tabRel', 'csv');
$pgUpload  = wtkReplace($pgUpload, 'width="144px"','width="245px"');
$pgUpload  = wtkReplace($pgUpload, 'JavaScript:wtkfFileUpload(', 'JavaScript:csvFileUpload(');
$pgUpload .= wtkFormHidden('wtkfRefreshDIV', 'csvImporter'); // this tells JS to refresh uploadFileDIV DIV by calling this page
// $pgUpload .= wtkFormHidden('wtkfColPath', '/exports');
// $pgTableName = wtkGetParam('TableName');

$pgSQL =<<<SQLVAR
SELECT c.`COLUMN_NAME` AS `ColumnName`, c.`COLUMN_TYPE` AS `ColumnType`,
    IF (c.`IS_NULLABLE` = 'NO' AND c.`COLUMN_DEFAULT` IS NULL AND c.`EXTRA` <> 'auto_increment',
        '<span class="red-text">Yes</span>', 'No') AS `Required`,
    c.`COLLATION_NAME` AS `Collation`,
    CONCAT('<a id="', c.`COLUMN_NAME`, 'Link" draggable="true" ondragover="wtkDragOver(event)"',
        ' ondrop="importDropName(\'',c.`COLUMN_NAME`,
        '\')" class="btn btn-floating hidden-link"><i class="material-icons">insert_link</i></a>') AS `Link`
 FROM `information_schema`.`COLUMNS` c
  INNER JOIN `information_schema`.`TABLES` t ON t.`TABLE_NAME` = c.`TABLE_NAME`
WHERE c.`TABLE_SCHEMA` = :TABLE_SCHEMA AND c.`TABLE_NAME` = :TABLE_NAME
ORDER BY c.`ORDINAL_POSITION` ASC
SQLVAR;

$pgSqlFilter = array(
    'TABLE_SCHEMA' => $gloDb1,
    'TABLE_NAME' => $pgTableName
);
$gloColumnAlignArray = array (
    'Required' => 'center',
	'Link' => 'center'
);
$gloRowsPerPage = 100;
$pgTableDef = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'columnList');

$pgHtm =<<<htmVAR
<div class="row">
    <div id="verifyImport" class="col s12 hide"></div>
    <div id="dataHeader" class="col m6 s12">
        <h4>$pgTableName Data Table
            <small id="importBtn" class="right hide">
                <a class="btn" onclick="JavaScript:wtkImport('verify')">Review Import</a>
            </small>
        </h4>
    </div>
    <div id="csvHeader" class="col m6 s12">
        <h4>CSV Columns to Import</h4>
    </div>
</div>
<div class="row">
    <div id="mappingDIV" class="col m6 offset-m3 s12 hide">
        <div class="card">
            <div class="card-content">
                <h5 class="center">Current Mapping for Import</h5>
                <ul id="mappingCSVul" class="collection with-header"></ul>
            </div>
        </div>
    </div>
    <div id="tableDef" class="col m6 s12">
        <div class="wtk-list card b-shadow">
            $pgTableDef
        </div>
    </div>
    <div id="csvDIV" class="col m6 s12">
        <div class="wtk-list card b-shadow" id="csvColCard">
            <br>
                <form id="wtkForm" name="wtkForm" method="post">
                    <div class="row">
                    $pgUpload
                    </div>
                </form>
            <div id="displayFileDIV"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
function csvFileUpload(fncFormId,fncTableName){
    $('#tableDef').addClass('hide');
    $('#csvDIV').removeClass('m6');
    $('#wtkForm').addClass('hide');
    wtkfFileUpload('wtkForm',fncTableName);
    $('#columnList').addClass('hide');
}

function showLinks() {
    let fncLinks = document.querySelectorAll('.hidden-link');
    fncLinks.forEach(function(fncLink) {
        fncLink.style.display = 'block';
    });
}

function mapCSVcolumns(fncFileName){
    $('#csvDIV').addClass('m6');
    $('#tableDef').removeClass('hide');
    $('#mappingDIV').removeClass('hide');
    $('#columnList').removeClass('hide');
    $('#displayFileDIV').addClass('hide');
    $('#csvColCard').html($('#csvColumns').html());
    showLinks();
}

var gloImportObject = {};
var gloCsvArray = [];

function importDropName(fncColName){
    gloImportObject[fncColName] = pgFromDragId;
    $('#' + fncColName + 'Link').addClass('hide');
    $('#importBtn').removeClass('hide');
    showMappings();
}

function wtkImport(fncStep) {
    if (gloImportObject) {
        let fncColMap = JSON.stringify(gloImportObject)
        let fncCSVfile = $('#csvFileLocation').val();
        let fncTableName = $('#TableName').val();
        $('#mappingDIV').addClass('hide');
        $('#dataHeader').addClass('hide');
        $('#csvHeader').addClass('hide');
        waitLoad('on');
        $.ajax({
            type: 'POST',
            url: 'ajxImportData.php',
            data: { apiKey: pgApiKey, step: fncStep, tableName: fncTableName,
                    csvFile: fncCSVfile, colMap: fncColMap},
            success: function(data) {
                waitLoad('off');
                $('#tableDef').addClass('hide');
                $('#csvDIV').addClass('hide');
                $('#verifyImport').html(data);
                $('#verifyImport').removeClass('hide');
            }
        })
    }
}
function unLinkCSV2Table(fncColName){
    // remove from object then wipe out and rebuild mappingCSVul
    delete gloImportObject[fncColName];
    $('#' + fncColName + 'Link').removeClass('hide');
    showMappings();
}

htmVAR;
$pgTmp =<<<htmVAR
function showMappings(){
   let fncUL = $('#mappingCSVul');
   fncUL.empty(); // Clear existing list items
   let fncTmp = '';
   let fncLIstart = '<li class="collection-item"><table class="table-basic" width="100%"><tr><td width="35%" class="center">';
   let fncLImiddle = '</td><td width="20%" class="center"><i class="material-icons small btn light-blue white-text" style="padding-top:4px">compare_arrows</i></td>';
   fncLImiddle += '<td width="35%" class="center"">';
   let fncLImid2 = '</td><td width="10%"><a onclick="JavaScript:unLinkCSV2Table(\'';
   let fncLIend = '\')" class="secondary-content"><i class="material-icons red-text small">remove_circle</i></a></div></li></td></tr></li>';
   $.each(gloImportObject, function(key, value) {
       fncTmp = fncLIstart + key + fncLImiddle + gloCsvArray[value] + fncLImid2 + key + fncLIend;
       fncUL.append(fncTmp);
   });
}
htmVAR;
$pgTmp = wtkReplace($pgTmp, 'fncUL','$fncUL');
$pgHtm .= $pgTmp . "\n" . '</script>';
$pgHtm .= wtkFormHidden('csvFileLocation', '');
$pgHtm .= wtkFormHidden('TableName', $pgTableName);

echo $pgHtm;
exit;
/*
<li class="collection-item"><table class="default-table" width="100%"><tr><td width="35%" class="center">
City
</td><td width="20%"><i class="material-icons small btn light-blue white-text" style="padding-top:4px">compare_arrows</i></td>
<td width="35%">Town</td><td width="10%"><a onclick="JavaScript:unLinkCSV2Table('City
')" class="secondary-content"><i class="material-icons red-text small">remove_circle</i></a></div></li></td></tr>

let fncLIstart = '<li class="collection-item"><div class="center">';
let fncLImiddle = '<i class="material-icons small btn light-blue white-text" style="padding-top:4px">compare_arrows</i>';
let fncLIend = ' class="secondary-content"><i class="material-icons red-text small">remove_circle</i></a></div></li>';


function chooseCSVfile(){
    $('#modalWTK').html($('#pickFileModal').html());

    let fncModalId = document.getElementById('modalWTK');
    let fncModal = M.Modal.getInstance(fncModalId);
    fncModal.open();
    document.getElementById('modalWTK').scrollTop = 0;

} // chooseCSVfile


<p class="center"><a onclick="JavaScript:chooseCSVfile()" class="btn">Import CSV to Map</a> to this File</p>
<div id="pickFileModal" class="hide">
    <div class="modal-content">
        <p>Pick a file:
            <input type="file" class="btn">
            <a onclick="JavaScript:uploadCSV()" class="btn green">Upload CSV</a>
        </p>
    </div>
</div>
*/
?>
