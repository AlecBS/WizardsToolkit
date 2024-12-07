<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgStep = wtkGetPost('step');
$pgTableName = wtkGetPost('tableName');
$pgCsvFile = wtkGetPost('csvFile');
$pgJColMap = wtkGetPost('colMap');

$pgColMap  = json_decode($pgJColMap,true);
$pgCntr = 0;

if ($pgStep == 'verify'):
    $pgHtm =<<<htmVAR
<h4>Example of Import into $pgTableName</h4>
  <div class="wtk-list card b-shadow">
    <br>
    <p>Verify all looks correct, then
      <a class="btn" onclick="JavaScript:wtkImport('import')">Import</a>
      or
      <a class="btn" onclick="JavaScript:wtkImport('makeSQL')">Generate INSERT Script</a>
    </p>
    <table id="demoImport" class="striped"><thead>
htmVAR;
    foreach ($pgColMap as $key => $value):
        $pgHtm .= "<th>$key</th>" . "\n";
    endforeach;
    $pgHtm .= '</thead><tbody>' . "\n";
else:
    $pgSQL = 'INSERT INTO `' . $pgTableName . '` (';
    foreach ($pgColMap as $key => $value):
        $pgCntr ++;
        if ($pgCntr > 1):
            $pgSQL .= ',';
        endif;
        $pgSQL .= "`$key`";
    endforeach;
    $pgSQL .= ')' . "\n";
    $pgSQL .= ' VALUES ';
endif;

// import CSV and loop through to fill VALUES to insert
// Open the CSV file
if (($pgHandle = fopen('../' . $pgCsvFile, 'r')) !== false):
    $pgRowCount = 0;
    while (($pgData = fgetcsv($pgHandle, 1000, ',')) !== false):
        if ($pgRowCount > 0): // Skip header row
            if ($pgStep == 'verify'):
                $pgHtm .= '<tr>' . "\n";
            else:
                if ($pgRowCount > 1):
                    $pgSQL .= ',' . "\n";
                endif;
                $pgSQL .= '(';
            endif;
            $pgColCount = 0;
            foreach ($pgColMap as $pgKey => $pgValue):
                $pgColCount++;
                if ($pgStep == 'verify'):
                	$pgHtm .= '  <td>' . $pgData[$pgValue] . '</td>' . "\n";
                else:
                    if ($pgColCount > 1):
                        $pgSQL .= ',';
                    endif;
                    $pgSQL .= "'" . addslashes($pgData[$pgValue]) . "'";
                endif;
            endforeach;
            if ($pgStep == 'verify'):
                $pgHtm .= '</tr>' . "\n";
            else:
                $pgSQL .= ')';
            endif;
        endif;
        $pgRowCount++;
        if (($pgStep == 'verify') && ($pgRowCount > 10)):
            break;
        endif;
    endwhile;
    fclose($pgHandle);
endif;

switch ($pgStep):
    case 'verify':
        $pgHtm .= '</tbody></table></div>' . "\n";
        break;
    case 'import':
        $pgHtm =<<<htmVAR
    <div class="card">
        <div class="card-content">
            <br><h2>$pgRowCount rows Imported!</h2><br>
        </div>
    </div>
htmVAR;
        wtkSqlExec($pgSQL, []);
        break;
    default: // makeSQL
        $pgHtm =<<<htmVAR
    <div class="card">
        <div class="card-content">
            <br><h2>SQL Insert</h2><br>
            <br><pre><code>$pgSQL</code></pre>
        </div>
    </div>
htmVAR;
        break;
endswitch;
echo $pgHtm;
// echo '<br>$pgCsvFile: value = ' . $pgCsvFile . "\n";
// print_r($pgColMap);
exit;
?>
