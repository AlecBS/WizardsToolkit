<?php
$pgSecurityLevel = 1;
$gloSiteDesign = 'MPA';
define('_RootPATH', '../');
require('wtkLogin.php');
if ($gloId == ''):
    wtkMergePage('<h2 align="center">Page called incorrectly</h2>','Error','htm/minibox.htm');
endif;

if ($gloDriver1 == 'pgsql'):
    $pgDateFormat = "to_char(`AddDate`, 'YY/Mon')"; // wtkSqlGetRow will convert ` to "
    $pgCRC32 = 'CRC32(CAST(`UID` AS VARCHAR))';
else:
	$pgDateFormat = "DATE_FORMAT(`AddDate`,'%y/%b')";
    $pgCRC32 = 'CRC32(`UID`)';
endif;
$pgSQL =<<<SQLVAR
SELECT `UID`, $pgDateFormat AS `ExtPath`,`CurrentLocation`,
    `NewFileName`,`FilePath`,`ExternalStorage`,`TempDownload`,`FileExtension`
  FROM `wtkFiles`
WHERE $pgCRC32 = :UID
ORDER BY `UID` ASC LIMIT 2
SQLVAR;
$pgSqlFilter = array ('UID' => $gloId);
wtkSqlGetRow($pgSQL, $pgSqlFilter);
if (!is_array($gloPDOrow)):
    wtkMergePage('<h2 class="center">Cannot locate file</h2><br>','Download File','htm/minibox.htm');
endif;

$pgExtPath = wtkSqlValue('ExtPath');
$pgFilePath = wtkSqlValue('FilePath');
$pgNewFileName = wtkSqlValue('NewFileName');
$pgExternalStorage = wtkSqlValue('ExternalStorage');
$pgTempDownload = wtkSqlValue('TempDownload');
$pgFileExt = wtkSqlValue('FileExtension');
$pgCurrentLocation = wtkSqlValue('CurrentLocation');

// BEGIN If file does not exist, give good error message
if ($pgNewFileName == ''):
    $pgHtm  = '<h3>File does not exist</h3>' . "\n";
    $pgHtm .= '<br><p>A problem must have happened with the uploading of the file.</p>' . "\n";
    $pgHtm .= '<p>Contact the person who uploaded this and ask them to try again.' . "\n";
    $pgHtm .= ' We apologize for the inconvenience.</p>' . "\n";
    wtkMergePage($pgHtm,'Error','htm/minibox.htm');
endif;
//  END  If file does not exist, give good error message

// BEGIN Pull down file from AWS S3
if (($pgExternalStorage == 'Y') && ($pgCurrentLocation != 'L')):
    if ($pgTempDownload == 'Y'): // this means Private Bucket
        require_once('lib/DocumentStorageService.php');
        $pgShowFile = DocumentStorageService::wtkR2task('genPresignedURL', $pgExtPath, $pgNewFileName);
    else:
// Next line method if using Public Cloudflare R2 to reduce Class A operations; above for fully private and secure
        $pgShowFile = $gloExtStorage . $pgExtPath . '/' . $pgNewFileName;
    endif;
else: // $pgExternalStorage != 'Y'
    $pgShowFile = $pgFilePath . $pgNewFileName;
    // BEGIN Verify file exists if on local server
    if (!file_exists($pgShowFile)):
        $pgFileLocation = _RootPATH . $pgShowFile;
        $pgFileLocation = str_replace('//', '/', $pgFileLocation);
        if (file_exists($pgFileLocation)):
        	$pgShowFile = $pgFileLocation;
        else:
            $pgFileLocation = str_replace('../', '/', $pgFileLocation);
            $pgFileLocation = str_replace('//', '/', $pgFileLocation);
            if (file_exists($pgFileLocation)):
                $pgShowFile = $pgFileLocation;
            else:
                $pgHtm  = '<h2>File Error</h2><p>File does not exist on server.' . "\n";
                $pgHtm .= " Contact tech support at <a href=\"mailto:$gloTechSupport\">$gloTechSupport</a></p>" . "\n";
                wtkMergePage($pgHtm,'Download File','htm/minibox.htm');
            endif;
        endif;
    endif;
    //  END  Verify file exists if on local server
endif;

if ($pgTempDownload == 'Y'): // test to see if in secure folder and needs to be copied
    /* Optional method pushing to browser
    $pgContent = file_get_contents($pgShowFile);
    if ($pgContent === false):
        echo 'Error reading file';
        exit;
    endif;
    // Create a temporary file and write content into it
    $pgTmpFilePath = tempnam(sys_get_temp_dir(), 'tempfile');
    file_put_contents($pgTmpFilePath, $pgContent);

    // Get MIME type of the temporary file
    $pgContentType = mime_content_type($pgTmpFilePath);
    if ($pgContentType === false):
        echo 'Error determining MIME type';
        unlink($pgTmpFilePath); // Clean up temp file
        exit;
    endif;

    header("Content-type: $pgContentType");
    header("Content-Disposition: inline; filename=$pgDLFileName");
    echo $pgContent;
    exit;
    */
    $pgNewName = wtkEncode(date('His') . $gloUserUID);
    $pgOrigFilePath = $pgShowFile;
    $pgShowFile = _RootPATH . 'exports/' . $pgNewName . '.' . $pgFileExt;
    if (!copy($pgOrigFilePath, $pgShowFile)):
        wtkMergePage("<h2>$pgNewFileName failed to copy!</h2>",'Download File','wtk/htm/minibox.htm');
    endif;
endif;

$pgHtm =<<<htmVAR
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Download</title>
</head>
<style >
html, body, iframe { height:100%;margin:0px;padding:0px;}
iframe { position: absolute; height: 100vh; border: none; }
</style>
<body>
    <iframe src="$pgShowFile" width="100%" height="100%" style="border:none;"></iframe>
</body>
</html>
htmVAR;

echo $pgHtm;
exit;
?>
