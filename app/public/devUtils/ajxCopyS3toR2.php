<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
wtkPageProtect('wtk4LowCode');
require('../wtk/lib/DocumentStorageService.php');
// main external bucket - probably Cloudflare

// can uncomment next two lines to force stop if testing locally
// echo '{"result":"done", "fileCount": "' . $pgCount . '","time":"' . $pgPageTime . '"}';
// exit; // no display because called by cURL to emulate page

$pgCloudflareR2 = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);

// Set values to secondary bucket - probably AWS
// Will pull from here and copy to main external bucket
$gloExtRegion = $gloExt2Region;
$gloExtBucket = $gloExt2Bucket;
$gloExtAccountId = $gloExt2AccountId;
$gloExtEndPoint = $gloExt2EndPoint;
$gloExtAccessKeyId = $gloExt2AccessKeyId;
$gloExtAccessKeySecret = $gloExt2AccessKeySecret;

/*
// SQL scripts to prepare data for testing
SELECT "UID", "CurrentLocation", "FileExtension", "FilePath","NewFileName"
 FROM "wtkFiles"
WHERE "UID" BETWEEN 31 AND 33
ORDER BY "UID" ASC;

UPDATE "wtkFiles"
 SET "CurrentLocation" = 'A', "NewFileName" = NULL, "FilePath" = NULL
WHERE "aa_id" IS NOT NULL;

UPDATE "wtkFiles"
 SET "OrigFileName" = 'Alec testing jpg', "FileExtension" = 'jpg'
WHERE "UID" = 24;
*/

if ($gloDriver1 == 'pgsql'):
    $pgDateFormat = "to_char(`AddDate`, 'YY/Mon')";
else:
	$pgDateFormat = "DATE_FORMAT(`AddDate`,'%y/%b')";
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `aa_id`, $pgDateFormat AS `CR2Path`,
    to_char(`AddDate`, 'YY/MM') AS `AWSPath`,
    `FileExtension`,`FilePath`,`OrigFileName`,`NewFileName`
  FROM `wtkFiles`
WHERE `CurrentLocation` = :CurrentLocation
ORDER BY `UID` ASC LIMIT 3
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgSqlFilter = array ('CurrentLocation' => 'A');
$pgCount = 0;
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgUID = $gloPDOrow['UID'];
    $pgAAid = $gloPDOrow['aa_id'];
    $pgNewFileName = $gloPDOrow['NewFileName'];
    $pgFileExtension = $gloPDOrow['FileExtension'];

    // BEGIN Pull down file from AWS S3
    $pgAWSPath  = $gloPDOrow['AWSPath'];
    $pgCalcPath = '/' . ceil($pgAAid / 1000);
    $pgAWSPath .= $pgCalcPath;

    $pgNewFileName = $pgAAid;
    $pgAwsFile = DocumentStorageService::wtkR2task('genPresignedURL', $pgAWSPath, $pgNewFileName);
    $pgNewFileName = wtkGenerateFileName('wtkFiles', $pgFileExtension);
    /*
    This code is only necessary if you want/need to copy files from AWS S3
      to your local server before moving to Cloudflare R2.
      Depending on AWS S3 settings this may or may not be necessary.
    */
    $pgAWSpath = $pgAwsFile;
    $pgAwsFile = 'aws/' . $pgNewFileName;
    // below crashes if file does not exist; need to verify file/URL exists
    if (!copy($pgAWSpath, $pgAwsFile)):
        wtkMergePage("<h2>$pgNewFileName failed to copy!</h2>",'Download File','wtk/htm/minibox.htm');
    endif;
    //  END  Pull down file from AWS S3

    // BEGIN Upload file to Cloudflare R
    $pgCR2Path = $gloPDOrow['CR2Path'];
//    $pgCloudflareR2->create($pgCR2Path, $pgNewFileName, file_get_contents($pgAwsFile)); // prior code
    $pgCloudflareR2->create($pgCR2Path, $pgNewFileName, $pgAwsFile);
    //  END  Upload file to Cloudflare R
    // BEGIN Update to show now in Cloudflare R2
    $pgSQL =<<<SQLVAR
UPDATE `wtkFiles`
  SET `CurrentLocation` = 'C', `FilePath` = $pgDateFormat, `NewFileName` = '$pgNewFileName'
WHERE `UID` = $pgUID
SQLVAR;
    wtkSqlExec($pgSQL, []);
    //  END  Update to show now in Cloudflare R2
    unlink($pgAwsFile); // delete AWS S3 file if temporarily stored on server
    $pgCount ++;
endwhile;
unset($pgPDO);

$pgPageTime = round(microtime(true) - $gloPageStart,4);
if ($pgCount == 0):
    echo '{"result":"done", "fileCount": "' . $pgCount . '","time":"' . $pgPageTime . '"}';
else:
    echo '{"result":"OK", "fileCount": "' . $pgCount . '","time":"' . $pgPageTime . '"}';
endif;
exit; // no display because called by cURL to emulate page
?>
