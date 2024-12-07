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
    c.`COLLATION_NAME` AS `Collation`
 FROM `information_schema`.`COLUMNS` c
  INNER JOIN `information_schema`.`TABLES` t ON t.`TABLE_NAME` = c.`TABLE_NAME`
WHERE c.`TABLE_SCHEMA` = :TABLE_SCHEMA AND c.`TABLE_NAME` = :TABLE_NAME
ORDER BY c.`ORDINAL_POSITION` ASC
SQLVAR;

$pgSqlFilter = array(
    'TABLE_SCHEMA' => $gloDb1,
    'TABLE_NAME' => $pgTableName
);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>$pgTableName Data Table</h4>
    <div class="wtk-list card b-shadow" id="csvDIV">
    <br>
    <p class="center">
        <form id="wtkForm" name="wtkForm" method="post">
            <div class="row">
            $pgUpload
            </div>
        </form>
    </p>
    <div id="displayFileDIV">data will show here</div>
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'columnList');
$pgHtm .= '</div></div>' . "\n";

$pgHtm .=<<<htmVAR
<script type="text/javascript">

function csvFileUpload(){
    $('#wtkForm').addClass('hide');
    wtkfFileUpload('','wtkAffiliates');
}

</script>
htmVAR;

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
