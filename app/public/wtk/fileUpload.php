<?php
$pgPostData = file_get_contents("php://input");
$pgJSON = json_decode($pgPostData, true);
$pgApiKey = $pgJSON['apiKey'];
// if ($pgApiKey == ''): // comment this out for production server; should not allow file uploads without an account
//     $gloLoginRequired = false;
// else:
//     $pgSecurityLevel = 1;
// endif;
define('_RootPATH', '../');
require('wtkLogin.php');

// $pgDebug = 'Y';
if ($pgDebug == 'Y'):
    $pgDebugSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
    $pgTest = 'fileUpload top - AccessMode: ' . $gloAccessMethod . '; apiKey: ' . $pgApiKey ;
    $pgSqlFilter = array('DevNote' => $pgTest);
    wtkSqlExec($pgDebugSQL, $pgSqlFilter);
endif;

if (array_key_exists('table', $pgJSON)): // called from Xcode
    $pgFrom = 'Xcode';
    // the $pgSecret must match the value in wtk/js/wtkClientVars.js var gloWtkApiKey
    $pgSecret = '::YourCustomXcodeSecret::';
    $pgSecretKey = 'wtkApiKey';
    $pgFile = $pgJSON['fileData'];
    $pgMode = 'EDIT';
    $pgUID = 'UID';
else:
    $pgFrom = 'website';
    // the $pgSecret must match the value in wtk/js/wtkClientVars.js var gloWtkApiKey
    $pgSecret = "~369+success+BIG!winning+WTK+hope+Today~432~";
    $pgSecretKey = 's';
    $pgFile = $pgJSON['file'];
    if (array_key_exists('mode', $pgJSON)):
        $pgMode = $pgJSON['mode'];
    else:
        if (array_key_exists('id', $pgJSON)):
            if ($pgJSON['id'] == '0'):
                $pgMode = 'ADD';
            else:
                $pgMode = 'EDIT';
            endif;
        else:
            $pgMode = 'ADD';
        endif;
    endif;
endif;
if (array_key_exists($pgSecretKey, $pgJSON)):
    $pgSentOK = $pgJSON[$pgSecretKey]; // secret password sent
    if ($pgSentOK != $pgSecret):
        //failed password: this is an error:
        header('HTTP/1.1 500 Internal Server Error');
        $pgJSON = array("error" => "failed password");
    //    $pgJSON = array("error" => "Post Data: " . $pgPostData);
        echo json_encode($pgJSON);
        exit;
    endif;
else:
    $pgJSON = array("error" => "no password sent");
    echo json_encode($pgJSON);
    exit;
endif;
$pgPath  = '';
if ($pgFrom == 'Xcode'):
    $pgTable = $pgJSON['table']; // 2ENHANCE pass to Xcode wtkEncode value
else:
    $pgTable = wtkDecode($pgJSON['t']);
    $pgUID   = wtkDecode($pgJSON['uid']);
    $pgUserUID = $pgJSON['userUID'];
    if (array_key_exists('path', $pgJSON)):
        $pgPath = $pgJSON['path'];
    endif;
    if (array_key_exists('parentUID', $pgJSON)):
        $pgParentUID = $pgJSON['parentUID'];
    else:
        $pgParentUID = '';
    endif;
endif;

// BEGIN Debugging from Xcode
if ($pgDebug == 'Y'):
    $pgSqlFilter = array('DevNote' => 'fileUpload before pgJSON loop');
    wtkSqlExec($pgDebugSQL, $pgSqlFilter);
    $pgDebug = '';
    foreach ($pgJSON as $key => $value) {
        if ($key !== 'fileData') {
            // Append the key-value pair to pgDebug variable for debugging purposes
            $pgDebug .= "$key: $value" . "\n";
        }
    }
    $pgTest = 'fileUpload from: ' . $pgFrom . ';' . "\n" . $pgDebug;
    $pgTest = substr($pgTest,0,240);
    $pgSqlFilter = array('DevNote' => $pgTest);
    wtkSqlExec($pgDebugSQL, $pgSqlFilter);
endif;
//  END  Debugging

$pgFileName = $pgJSON['fileName'];
if (array_key_exists('colPath', $pgJSON)):
    $pgColPath = $pgJSON['colPath'];
else:
    $pgColPath = 'FilePath';
endif;
if (array_key_exists('colFile', $pgJSON)):
    $pgColFile = $pgJSON['colFile'];
else:
    $pgColFile = 'NewFileName';
endif;
switch ($pgTable):
    case 'wtkUsers':
        if ($pgPath == ''):
            $pgPath = '/imgs/user/';
        endif;
        $pgMode = 'EDIT'; // registration process would have already inserted data so must be an edit
        break;
    case 'YourTableName':
        $pgPath = '/imgs/somedir/';
        break;
    default:
        if ($pgFrom == 'Xcode'):
            $pgPath = '/imgs/';
        endif;
        break;
endswitch;

$pgFileName = wtkReplace($pgFileName, "'", '');
$pgFileName = wtkReplace($pgFileName, ' ', '');
$pgFileExt  = pathinfo($pgFileName, PATHINFO_EXTENSION);

$pgFileArray = explode(',', $pgFile);
$pgFixedFile = $pgFileArray[1]; // gets rid of data:image/jpeg;base64,

$pgNewFileName = wtkGenerateFileName($pgTable, $pgFileExt);
$pgUploadFile = '..' . $pgPath . $pgNewFileName;
$pgUploadFile = wtkReplace($pgUploadFile, '....', '../..');

$fp = fopen($pgUploadFile, 'w');
fwrite($fp, base64_decode($pgFixedFile) );
fclose($fp);
//This page is only called for Edit rows; Add is handled via Save.php
if ($pgMode == 'ADD'):
    $pgTableRelation = $pgJSON['tabRel'];
    if ($pgTableRelation != ''):
        $pgSQL =<<<SQLVAR
INSERT INTO `$pgTable` (`$pgColPath`,`$pgColFile`,`TableRelation`)
 VALUES (:FilePath, :FileName, :TabRel)
SQLVAR;
        $pgSqlFilter = array (
            'FilePath' => $pgPath,
            'TabRel'   => $pgTableRelation,
            'FileName' => $pgNewFileName
        );
    else:
        $pgSQL =<<<SQLVAR
INSERT INTO `$pgTable` (`$pgColPath`,`$pgColFile`)
 VALUES (:FilePath, :FileName )
SQLVAR;
        $pgSqlFilter = array (
            'FilePath' => $pgPath,
            'FileName' => $pgNewFileName
        );
    endif;
    // BEGIN Description saving on INSERT
    if (array_key_exists('imgDescription', $pgJSON)):
        $pgDescription = $pgJSON['imgDescription'];
        $pgSQL = wtkReplace($pgSQL, '` (`', '` (`Description`,`');
        $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:Description, ');
        $pgSqlFilter['Description'] = $pgDescription;
    endif;
    //  END  Description saving on INSERT
    if ($pgUserUID != ''):
        $pgSQL = wtkReplace($pgSQL, '` (`', '` (`UserUID`,`');
        $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:UserUID, ');
        $pgSqlFilter['UserUID'] = $pgUserUID;
    endif;
    if ($pgParentUID != ''):
        $pgSQL = wtkReplace($pgSQL, '` (`', '` (`ParentUID`,`');
        $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:ParentUID, ');
        $pgSqlFilter['ParentUID'] = $pgParentUID;
    endif;
    if ($pgTable == 'wtkFiles'):
        if ($pgFileName != ''):
            if (strlen($pgFileName) > 110):
                $pgFileName = substr($pgFileName, 0, 110);
            endif;
            $pgSQL = wtkReplace($pgSQL, '` (`', '` (`OrigFileName`,`');
            $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:OrigFileName, ');
            $pgSqlFilter['OrigFileName'] = $pgFileName;
        endif;
        $pgSQL = wtkReplace($pgSQL, '` (`', '` (`FileExtension`,`FileSize`,`');
        $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:FileExtension, :FileSize, ');
        $pgSqlFilter['FileExtension'] = $pgFileExt;
        $pgFileSize = filesize($pgUploadFile);
        $pgSqlFilter['FileSize'] = $pgFileSize;
        if (isset($gloExtBucket)):
            if ($gloExtBucket != ''):
                $pgSQL = wtkReplace($pgSQL, '` (`', '` (`ExternalStorage`,`');
                $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:ExternalStorage, ');
                $pgSqlFilter['ExternalStorage'] = 'Y';
            endif;
        endif;
    endif;
    if ($pgTable == 'YourTable'): // edit this as needed to customize your coding
        $pgSQL = wtkReplace($pgSQL, '` (`', '` (`YourColumn`,`');
        $pgSQL = wtkReplace($pgSQL, 'VALUES (', 'VALUES (:YourColumn, ');
        $pgSqlFilter['YourColumn'] = $pgYourColumn;
    endif;
    wtkSqlExec($pgSQL, $pgSqlFilter);
else:
    $pgId = $pgJSON['id'];
    $pgPriorFileName = '';
    if (array_key_exists('del', $pgJSON)):
        $pgDelFile = $pgJSON['del'];
        if ($pgDelFile != ''):
            $pgPriorFileName = '..' . $pgPath . $pgDelFile;
            $pgPriorFileName = wtkReplace($pgPriorFileName, '....', '../..');
            if (is_file($pgPriorFileName)):
                unlink($pgPriorFileName);
            else:
                $pgPriorFileName = 'NotFound/' . $pgPriorFileName;
            endif;
        endif;
    endif;
    $pgSQL =<<<SQLVAR
UPDATE `$pgTable`
 SET `$pgColPath` = :FilePath, `$pgColFile` = :FileName
WHERE `$pgUID` = :UID
SQLVAR;
    $pgSqlFilter = array (
        'UID' => $pgId,
        'FilePath' => $pgPath,
        'FileName' => $pgNewFileName
    );
    if ($pgTable == 'wtkFiles'):
        if ($pgFileName != ''):
            if (strlen($pgFileName) > 110):
                $pgFileName = substr($pgFileName, 0, 110);
            endif;
            $pgSQL = wtkReplace($pgSQL, '= :FileName', '= :FileName, `OrigFileName` = :OrigFileName ');
            $pgSqlFilter['OrigFileName'] = $pgFileName;
        endif;
        $pgSQL = wtkReplace($pgSQL, '= :FileName', '= :FileName, `FileSize` = :FileSize');
        $pgFileSize = filesize($pgUploadFile);
        $pgSqlFilter['FileSize'] = $pgFileSize;
    endif;
    wtkSqlExec($pgSQL, $pgSqlFilter);
endif;

$pgJSON = '{"result":"success","path":"' . $pgPath . '","fileName":"' . $pgNewFileName . '"}';
//$pgJSON = '{"result":"success","path":"' . $pgPath . '","fileName":"' . $pgNewFileName . '","DelFile":"' . $pgPriorFileName . '"}';
//$pgJSON = '{"result":"success","path":"' . $pgPath . '","fileName":"' . $pgNewFileName . '","sql":"' . $pgSQL . '"}';
echo $pgJSON;
if ($pgDebug == 'Y'):
    $pgSqlFilter = array('DevNote' => 'fileUpload bottom of page');
    wtkSqlExec($pgDebugSQL, $pgSqlFilter);
endif;
exit;
?>
