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
gloImportObject = {}; // Clear the object
gloCsvArray = [];     // Clear the array
</script>
htmVAR;
$pgHtm .= wtkFormHidden('csvFileLocation', '');
$pgHtm .= wtkFormHidden('TableName', $pgTableName);

echo $pgHtm;
exit;
?>
