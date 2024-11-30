<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
wtkPageProtect('wtk4LowCode');

// below code copied from /wtk/cron.php and only slightly modified
require('../wtk/lib/DocumentStorageService.php');
$pgDocumentStorageService = new DocumentStorageService(null);
// $pgAwsS3 = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);

// BEGIN MOVE UPLOADED FILES FROM LOCAL STORAGE TO AWS S3 or Cloudflare R2
// Get all files that haven't been uploaded to S3. Limit to 3 to avoid processes taking too long.
if ($gloDriver1 == 'pgsql'):
    $pgDateFormat = "to_char(`AddDate`, 'YY/Mon')"; // wtkSqlGetRow will convert ` to "
else:
	$pgDateFormat = "DATE_FORMAT(`AddDate`,'%y/%b')";
endif;
$pgSQL =<<<SQLVAR
SELECT `UID`, $pgDateFormat AS `ExtPath`, `FilePath`, `NewFileName`
  FROM `wtkFiles`
WHERE `ExternalStorage` = :ExternalStorage AND `CurrentLocation` = :CurrentLocation
ORDER BY `UID` ASC LIMIT 3
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgSqlFilter = array (
    'ExternalStorage' => 'Y',
    'CurrentLocation' => 'L'
);

$pgTotalCount = 0;
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);

try {
    while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)) {
        $pgUID = $gloPDOrow['UID'];
        $pgExtPath = $gloPDOrow['ExtPath'];
        $pgFilePath = $gloPDOrow['FilePath'];
        $pgFileName = $gloPDOrow['NewFileName'];

        if (!file_exists($pgFilePath . $pgFileName)): //check file exists (possibly moved by other CRON job)
            // so CRON job does not process if large file and taking a long time to move
            wtkSqlExec("UPDATE `wtkFiles` SET `ExternalStorage` = 'P' WHERE `UID` = $pgUID", []);
            //move to Cloudflare R2 or AWS S3
            $pgDocumentStorageService->create($pgExtPath, $pgFileName, (_RootPATH . $pgFilePath . $pgFileName));

            // set `CurrentLocation` to 'A' if moving to AWS S3, or to 'C' if moving to Cloudflare R2
            wtkSqlExec("UPDATE `wtkFiles` SET `ExternalStorage` = 'Y', `CurrentLocation` = 'C' WHERE `UID` = $pgUID", []);
            unlink(_RootPATH . $pgFilePath . $pgFileName); //remove file from local storage
        else:
            // notify tech support of problem
            $pgError = 'wtkFiles.UID = ' . $pgUID . ' failed to copy to Cloudflare R2';
            exit('{"result":"error", "err", "' . $pgError . '"}');
        endif;

        $pgTotalCount++;
    }
} catch (Exception $e) {
    unset($pgPDO);
    echo '<br>Exception Error:' . "\n";
    exit($e->getMessage());
} catch (Throwable $e) {
    echo '<br>Throwable Error:' . "\n";
    unset($pgPDO);
    exit($e->getMessage());
}
unset($pgPDO);
//  END  MOVE UPLOADED FILES FROM LOCAL STORAGE TO AWS S3 or Cloudflare R

$pgPageTime = round(microtime(true) - $gloPageStart,4);
if ($pgTotalCount == 0):
    echo '{"result":"done", "fileCount": "' . $pgTotalCount . '","time":"' . $pgPageTime . '"}';
else:
    echo '{"result":"OK", "fileCount": "' . $pgTotalCount . '","time":"' . $pgPageTime . '"}';
endif;
exit; // no display because called by cURL to emulate page
?>
