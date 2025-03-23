<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

// BEGIN pull in data table columns to compare to CSV column names
$pgSQL =<<<SQLVAR
SELECT c.`COLUMN_NAME` AS `ColumnName`
 FROM `information_schema`.`COLUMNS` c
  INNER JOIN `information_schema`.`TABLES` t ON t.`TABLE_NAME` = c.`TABLE_NAME`
WHERE c.`TABLE_SCHEMA` = :TABLE_SCHEMA AND c.`TABLE_NAME` = :TABLE_NAME
ORDER BY c.`ORDINAL_POSITION` ASC
SQLVAR;
$pgSqlFilter = array(
    'TABLE_SCHEMA' => $gloDb1,
    'TABLE_NAME' => $gloRNG
);

$pgDataColArray = [];
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgDataColArray[strtolower($gloPDOrow['ColumnName'])] = $gloPDOrow['ColumnName'];
endwhile;
unset($pgPDO);
//  END  pull in data table columns to compare to CSV column names

$pgSQL =<<<SQLVAR
SELECT CONCAT(`FilePath`, `NewFileName`) AS `UploadedFile`
 FROM `wtkFiles`
WHERE `TableRelation` = 'csv' AND `UserUID` = :UserUID
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
$pgSqlFilter = array('UserUID' => $gloUserUID);
$pgUploadedFile = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

$pgCSVarray = [];
// Open the CSV file for reading
if (($pgHandle = fopen('../' . $pgUploadedFile, 'r')) !== false):
    // Loop through each line in the file
    while (($pgData = fgetcsv($pgHandle, 1000, ',')) !== false):
        $pgCSVarray[] = $pgData;  // Add the line to the array
    endwhile;
    fclose($pgHandle);  // Close the file
endif;

$pgHtm =<<<htmVAR
<div class="card">
    <div class="card-content">
        <h4>Top 10 rows in CSV file</h4>
        <p>Once you have verified these look correct, click to
htmVAR;

$pgCsvTable = '<table class="border white"><thead>';
$pgCsvCols  = '<table id="csvHeaders" class="striped">' . "\n";
$pgCsvCols .= '<thead><th>&nbsp;</th><th>Column Name</th><th>First Row of Data</th></thead><tbody>';
$pgCntr = 0;
$pgExactMatches = '';
$pgDebug = '';
$pgCSVjsArray = '';
foreach ($pgCSVarray as $row):
    $pgCntr ++;
    if ($pgCntr == 2):
        $pgCsvTable = wtkReplace($pgCsvTable, 'td>','th>');
        $pgCsvTable .= '</thead>' . "\n";
        $pgCsvTable .= '<tbody>' . "\n";
    endif;
    $pgCsvTable .= '<tr>' . "\n";
    $pgColCntr = 0;
    foreach ($row as $cell):
        $pgColName = htmlspecialchars($cell);
        $pgCsvTable .= '  <td>' . $pgColName . '</td>' . "\n";
        if ($pgCntr == 1):
            $pgCSVjsArray .= "gloCsvArray.push('$pgColName');" . "\n";
            // BEGIN check for exact match with data table
            if (array_key_exists(strtolower(wtkReplace($pgColName,' ','')), $pgDataColArray)):
                $pgDataColName = $pgDataColArray[strtolower(wtkReplace($pgColName,' ',''))];
                $pgExactMatches .= "gloImportObject['$pgDataColName'] = $pgColCntr;" . "\n";
                $pgExactMatches .= "$('#" . $pgDataColName . "Link').addClass('hide');" . "\n";
            endif;
            //  END  check for exact match with data table
            $pgCsvCols .= '<tr><td>' . "\n";
            $pgCsvCols .= ' <a draggable="true" data-id="' . $pgColCntr . '" ondragstart="wtkDragStart(this);" ondragover="wtkDragOver(event)" class="btn btn-floating wtkdrag">' . "\n";
            $pgCsvCols .= '<i class="material-icons" alt="drag to link where to import" title="drag to link where to import">drag_handle</i></a></td>' . "\n";
            $pgCsvCols .= '<td>' . $pgColName . '</td>' . "\n"; // column name
            $pgCsvCols .= '<td>@First' . $pgColCntr . 'Data@</td>' . "\n"; // data from first row
            $pgCsvCols .= '</tr>' . "\n";
        elseif ($pgCntr == 2):
            $pgCsvCols = wtkReplace($pgCsvCols, '@First' . $pgColCntr . 'Data@', $pgColName);
            $pgDebug .= '@First' . $pgColCntr . 'Data@ replaced with: ' . $pgColName;
        endif;
        $pgColCntr ++;
    endforeach;
    $pgCsvTable .= '</tr>' . "\n";
    if ($pgCntr == 10):
        break;
    endif;
endforeach;
$pgCsvCols  .= '</tbody></table>' . "\n";
$pgCsvTable .= '</tbody></table>' . "\n";

if ($pgExactMatches != ''):
    $pgHasMatches = 'Y';
else:
    $pgHasMatches = 'N';
endif;

$pgHtm .=<<<htmVAR
        <a onclick="JavaScript:mapCSVcolumns('$pgUploadedFile','$pgHasMatches')" class="btn">Map Columns</a></p>

        $pgCsvTable
        <div id="csvColumns" class="hide">$pgCsvCols</div>
    </div>
</div>
<script type="text/javascript">
$('#csvFileLocation').val('$pgUploadedFile');

$pgExactMatches
$pgCSVjsArray

showMappings();
</script>
htmVAR;
echo $pgHtm;
exit;
?>
