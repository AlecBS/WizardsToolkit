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
$pgUpload  = wtkReplace($pgUpload, 'JavaScript:wtkfFileUpload(','JavaScript:csvFileUpload(');
$pgUpload .= wtkFormHidden('wtkfRefreshDIV', 'csvImporter'); // this tells JS to refresh uploadFileDIV DIV by calling this page

// $pgTableName = wtkGetParam('TableName');

$pgSQL =<<<SQLVAR
SELECT c.`COLUMN_NAME` AS `ColumnName`, c.`COLUMN_TYPE` AS `ColumnType`,
    IF (c.`IS_NULLABLE` = 'NO' AND c.`COLUMN_DEFAULT` IS NULL AND c.`EXTRA` <> 'auto_increment',
        '<span class="red-text">Yes</span>', 'No') AS `Required`,
    c.`COLLATION_NAME` AS `Collation`,
    CONCAT('<a draggable="true" ondragover="wtkDragOver(event)"',
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
$pgTableDef = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'columnList');

$pgHtm =<<<htmVAR
<div class="row">
    <div id="verifyImport" class="col s12 hide"></div>
    <div id="tableDef" class="col m6 s12">
        <h4>$pgTableName Data Table
            <small id="importBtn" class="right hide">
                <a class="btn" onclick="JavaScript:wtkImport('verify')">Review Import</a>
            </small>
        </h4>
        <div class="wtk-list card b-shadow">
            $pgTableDef
        </div>
    </div>
    <div id="csvDIV" class="col m6 s12">
        <h4>CSV Columns to Import</h4>
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
function csvFileUpload(){
    $('#tableDef').addClass('hide');
    $('#csvDIV').removeClass('m6');
    $('#wtkForm').addClass('hide');
    wtkfFileUpload('','wtkAffiliates');
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

    $('#columnList').removeClass('hide');
    $('#displayFileDIV').addClass('hide');
    $('#csvColCard').html($('#csvColumns').html());
    showLinks();
}

var pgImportArray = {};
function importDropName(fncColName){
    pgImportArray[fncColName] = pgFromDragId;
    $('#importBtn').removeClass('hide');
}

function wtkImport(fncStep) {
    if (pgImportArray) {
        let fncColMap = JSON.stringify(pgImportArray)
        let fncCSVfile = $('#csvFileLocation').val();
        let fncTableName = $('#TableName').val();
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
</script>
htmVAR;
$pgHtm .= wtkFormHidden('csvFileLocation', '');
$pgHtm .= wtkFormHidden('TableName', $pgTableName);

echo $pgHtm;
exit;
/*
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
