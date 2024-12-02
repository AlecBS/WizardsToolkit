<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `FileName`, `Path`
  FROM `wtkPages`
GROUP BY `FileName`,`Path`
ORDER BY `FileName` ASC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute([]);

$pgCntr = 0;
$pgTmp = 'var gloFilePath = [];' . "\n";
$pgJSON = '{';
while ($pgRow = $pgPDO->fetch()):
    $pgTmp .= "gloFilePath['" . $pgRow['FileName'] . "'] = '" . $pgRow['Path'] . "';" . "\n";

    $pgCntr ++;
    if ($pgCntr > 1):
        $pgJSON .= ',';
    endif;
    $pgJSON .= '"' . $pgRow['FileName'] . '":';
    $pgJSON .= '"' . $pgRow['Path'] . '"';
endwhile;
$pgJSON .= '}';

$pgFile = fopen(_RootPATH . 'wtk/js/wtkPaths.js', 'w');
fwrite($pgFile, $pgTmp);
fclose($pgFile);

echo $pgJSON;
exit;
?>
