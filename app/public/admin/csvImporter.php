<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT CONCAT(`FilePath`, `NewFileName`) AS `UploadedFile`
 FROM `wtkFiles`
WHERE `TableRelation` = 'csv' AND `UserUID` = :UserUID
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
$pgSqlFilter = array('UserUID' => $gloUserUID);
$pgUploadedFile = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

wtkSetSession('csvUpload', $pgUploadedFile);

$pgCSVarray = [];

// Open the CSV file for reading
if (($handle = fopen('../' . $pgUploadedFile, 'r')) !== false):
    // Loop through each line in the file
    while (($data = fgetcsv($handle, 1000, ',')) !== false):
        // Add the line to the array
        $pgCSVarray[] = $data;
    endwhile;
    // Close the file
    fclose($handle);
endif;

$pgHtm =<<<htmVAR
<div class="card">
    <div class="card-content">
        <h4>Top 10 rows in CSV file</h4>
        <p>Once you have verified these look correct,
        click to <a onclick="JavaScript:mapCSVcolumns('$pgUploadedFile')" class="btn">Map Columns</a></p>
htmVAR;

$pgCsvTable = '<table class="border white"><thead>';
$pgCsvCols  = '<table id="csvHeaders" class="striped">' . "\n";
$pgCsvCols .= '<thead><th>&nbsp;</th><th>Column Name</th></thead><tbody>';
$pgCntr = 0;
$pgColCntr = 0;
foreach ($pgCSVarray as $row){
    $pgCntr ++;
    if ($pgCntr == 2):
        $pgCsvTable = wtkReplace($pgCsvTable, 'td>','th>');
        $pgCsvTable .= '</thead>' . "\n";
        $pgCsvTable .= '<tbody>' . "\n";
    endif;
    $pgCsvTable .= '<tr>' . "\n";
    foreach ($row as $cell) {
        $pgColName = htmlspecialchars($cell);
        $pgCsvTable .= '  <td>' . $pgColName . '</td>' . "\n";
        if ($pgCntr == 1):
            $pgCsvCols .= '<tr><td>' . "\n";
            $pgCsvCols .= ' <a draggable="true" ondragstart="wtkDragStart(' . $pgColCntr . ',0);" ondragover="wtkDragOver(event)" class="btn btn-floating">' . "\n";
            $pgCsvCols .= '<i class="material-icons" alt="drag to link where to import" title="drag to link where to import">drag_handle</i></a></td>' . "\n";
            $pgCsvCols .= '<td>' . $pgColName . '</td></tr>' . "\n";
            $pgColCntr ++;
        endif;
    }
    $pgCsvTable .= '</tr>' . "\n";
    if ($pgCntr == 10):
        break;
    endif;
}
$pgCsvCols  .= '</tbody></table>' . "\n";
$pgCsvTable .= '</tbody></table>' . "\n";

$pgHtm .=<<<htmVAR
        $pgCsvTable
        <div id="csvColumns" class="hide">$pgCsvCols</div>
    </div>
</div>
<script type="text/javascript">
$('#csvFileLocation').val('$pgUploadedFile');
</script>
htmVAR;
//  File Name: $pgUploadedFile
echo $pgHtm;
exit;
?>
