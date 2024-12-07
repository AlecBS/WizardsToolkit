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
        <p>Verify these look correct.</p>
htmVAR;

$pgCsvTable = '<table class="border"><thead>';
$pgCntr = 0;
foreach ($pgCSVarray as $row){
    $pgCntr ++;
    if ($pgCntr == 2):
        $pgCsvTable = wtkReplace($pgCsvTable, 'td>','th>');
        $pgCsvTable .= '</thead>' . "\n";
        $pgCsvTable .= '<tbody>' . "\n";
    endif;
    $pgCsvTable .= '<tr>' . "\n";
    foreach ($row as $cell) {
        $pgCsvTable .= '  <td>' . htmlspecialchars($cell) . '</td>' . "\n";
    }
    $pgCsvTable .= '</tr>' . "\n";
    if ($pgCntr == 10):
        break;
    endif;
}
$pgCsvTable .= '</tbody></table>' . "\n";

$pgHtm .=<<<htmVAR
        $pgCsvTable
    </div>
</div>
htmVAR;
//  File Name: $pgUploadedFile
echo $pgHtm;
exit;
?>
