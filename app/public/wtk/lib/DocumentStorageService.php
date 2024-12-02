<?php
/*
The only code change needed is to define $bucket, $accountId, $accessKeyId, and $accessKeySecret below.
Search for "BEGIN Only variables that need to be set to use this library"
*/
require_once(_RootPATH . '../s3-sdk/aws-autoloader.php');

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class DocumentStorageService
{
    const MAX_DOCS_PER_DIR = 1000;
    const MAX_UPLOAD_SIZE = 104857600; //bytes (100 mb)
    const VERSION = 'latest';

    private $bucket;
    private $s3Client;

    public function __construct($bucket)
    {
        // BEGIN Only variables that need to be set to use this library
        global $gloExtBucket, $gloExtAccountId, $gloExtAccessKeyId, $gloExtAccessKeySecret,
            $gloExtRegion, $gloExtEndPoint;
        $this->bucket = $bucket ?? $gloExtBucket;
//        $accountId = $gloExtAccountId;
        $accessKeyId = $gloExtAccessKeyId;
        $accessKeySecret = $gloExtAccessKeySecret;
        //  END  Only variables that need to be set to use this library
        $fncCredentials = new Aws\Credentials\Credentials($accessKeyId, $accessKeySecret);

        $fncOptions = [
            'region' => $gloExtRegion,
            'endpoint' => $gloExtEndPoint,
            'version' => 'latest',
            'credentials' => $fncCredentials
        ];

        $this->s3Client = new S3Client($fncOptions);
    }

    /**
     * @param $fncFileName
     * @param $fncFileLocation
     * @param $fncStoragePath
     * @return bool
     */
    public function create($fncStoragePath, $fncFileName, $fncFileLocation)
    {
        $fncFullPath = $fncStoragePath .'/'. $fncFileName;
        try {
            $fncTmp = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fncFullPath,
                'SourceFile' => $fncFileLocation
            ]);
//            echo "Uploaded $fncFullPath to $this->bucket.\n";
            $fncResult = true;
        } catch (Exception $exception) {
            $fncErr = $exception->getMessage();
            $fncErr = wtkReplace($fncErr, '"','`');
            $fncTmp = '{"result":"error", "err": "' . $fncErr . '","Upload":"' . $fncFullPath . '"}';
            exit($fncTmp);
            $fncResult = false;
        }
        return $fncResult;
    }
    /**
     * @param $fncFileName to be deleted
     * @return bool
     */
    public function deleteFile($fncStoragePath, $fncFileName)
    {
//        $fncFullPath = 's3://' . $this->bucket . '/' . $fncStoragePath . '/' . $fncFileName;
//        $fncDocStorageService = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);
        $fncFullPath = $fncStoragePath .'/'. $fncFileName;
        try {
//          $this->s3Client->registerStreamWrapper();
            $result = $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $fncFullPath,
            ]);
            // if not using version control the DeleteMarker will not be set (like for Staging)
            // print_r($result);
            // exit;
            // // still in progress
            // if ($result['DeleteMarker']) {
            //     echo $fncFullPath . ' was deleted or does not exist.' . PHP_EOL;
            // } else {
            //     exit('Error: ' . $fncFullPath . ' was not deleted.' . PHP_EOL);
            // }
        } catch (AwsException $e) {
            echo $e->getMessage() . PHP_EOL;
            throw new \Exception("Unable to read file ");
        }
    }
    /**
     * @param $fncStoragePath
     * @param $fncFileName
     * @return bool|false|string
     */
    public function readFromS3($fncStoragePath, $fncFileName)
    {
        $fncFullPath = $fncStoragePath .'/'. $fncFileName;
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $fncFullPath,
            ]);
            // echo 'readFromS3<hr>' . "\n";
            // print_r($result);
            return $result['Body'];
        } catch (AwsException $e) {
            echo $e->getMessage() . PHP_EOL;
            throw new \Exception("Unable to read file ");
        }
    }
    // BEGIN Alec - generate Presigned URL
    public static function wtkR2task($fncTask, $fncExtPath, $fncFileName)
    {
        try {
            $fncDocStorageService = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);
            // currently only task is genPresignedURL but may expand later
            switch ($fncTask):
                case 'genPresignedURL':
                    $fncResult = $fncDocStorageService->genPresignedURL($fncExtPath, $fncFileName);
                    break;
                default:
                    global $gloTechSupport;
                    $fncHtm  = '<h2>Unknown Task</h2><p>The ' . $fncTask . ' is not recognized.' . "\n";
                    $fncHtm .= " Contact tech support at <a href=\"mailto:$gloTechSupport\">$gloTechSupport</a></p>" . "\n";
                    wtkMergePage($fncHtm,'External File Error','wtk/htm/minibox.htm');
                    break;
            endswitch;
        } catch (AwsException $awsException) {
            self::displayFileNotFound();
        }
        return $fncResult;
    }

    /**
     * @param $fncStoragePath
     * @param $fncFileName
     * @return bool|false|string
     */
    public function genPresignedURL($fncExtPath, $fncFileName)
    {
        $fncDocStorageService = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);
        $fncFullPath = $fncExtPath .'/'. $fncFileName;
        try {
             $cmd = $this->s3Client->getCommand('GetObject', [
                 'Bucket' => $this->bucket,
                 'Key' => $fncFullPath,
             ]);
             // The second parameter allows you to determine how long the presigned link is valid.
             $fncRequest = $this->s3Client->createPresignedRequest($cmd, '+5 minute');
             $fncReturn = (string)$fncRequest->getUri();
             return $fncReturn;
         } catch (AwsException $e) {
             echo $e->getMessage() . PHP_EOL;
             throw new \Exception("Unable to generate presigned");
         }
    }
    //  END  Alec - generate Presigned URL

    public static function getMimeTypes()
    {
        return [
            'hqx'	=>	'application/mac-binhex40',
            'cpt'	=>	'application/mac-compactpro',
            'bin'	=>	'application/macbinary',
            /*'dms'	=>	'application/octet-stream',
            'lha'	=>	'application/octet-stream',
            'lzh'	=>	'application/octet-stream',
            'exe'	=>	'application/octet-stream',
            'class'	=>	'application/octet-stream',*/
            'psd'	=>	'application/x-photoshop',
            /*'so'	=>	'application/octet-stream',
            'sea'	=>	'application/octet-stream',
            'dll'	=>	'application/octet-stream',*/
            'oda'	=>	'application/oda',
            'pdf'	=>	['application/pdf', 'application/x-download'],
            'ai'	=>	'application/postscript',
            'eps'	=>	'application/postscript',
            'ps'	=>	'application/postscript',
            'smi'	=>	'application/smil',
            'smil'	=>	'application/smil',
            'mif'	=>	'application/vnd.mif',
            'xls'	=>	['application/excel', 'application/vnd.ms-excel', 'application/msexcel'],
            'ppt'	=>	['application/powerpoint', 'application/vnd.ms-powerpoint'],
            'wbxml'	=>	'application/wbxml',
            'wmlc'	=>	'application/wmlc',
            'dcr'	=>	'application/x-director',
            'dir'	=>	'application/x-director',
            'dxr'	=>	'application/x-director',
            'dvi'	=>	'application/x-dvi',
            'gtar'	=>	'application/x-gtar',
            'gz'	=>	'application/x-gzip',
            'php'	=>	'application/x-httpd-php',
            'php4'	=>	'application/x-httpd-php',
            'php3'	=>	'application/x-httpd-php',
            'phtml'	=>	'application/x-httpd-php',
            'phps'	=>	'application/x-httpd-php-source',
            'js'	=>	'application/x-javascript',
            'swf'	=>	'application/x-shockwave-flash',
            'sit'	=>	'application/x-stuffit',
            'tar'	=>	'application/x-tar',
            'tgz'	=>	'application/x-tar',
            'xhtml'	=>	'application/xhtml+xml',
            'xht'	=>	'application/xhtml+xml',
            'zip'	=>  ['application/x-zip', 'application/zip', 'application/x-zip-compressed'],
            'mid'	=>	'audio/midi',
            'midi'	=>	'audio/midi',
            'mpga'	=>	'audio/mpeg',
            'mp2'	=>	'audio/mpeg',
            'mp3'	=>	['audio/mpeg', 'audio/mpg'],
            'aif'	=>	'audio/x-aiff',
            'aiff'	=>	'audio/x-aiff',
            'aifc'	=>	'audio/x-aiff',
            'ram'	=>	'audio/x-pn-realaudio',
            'rm'	=>	'audio/x-pn-realaudio',
            'rpm'	=>	'audio/x-pn-realaudio-plugin',
            'ra'	=>	'audio/x-realaudio',
            'rv'	=>	'video/vnd.rn-realvideo',
            'wav'	=>	'audio/x-wav',
            'bmp'	=>	'image/bmp',
            'gif'	=>	'image/gif',
            'jpeg'	=>	['image/jpeg', 'image/pjpeg'],
            'jpg'	=>	['image/jpeg', 'image/pjpeg'],
            'jpe'	=>	['image/jpeg', 'image/pjpeg'],
            'png'	=>	['image/png',  'image/x-png'],
            'tiff'	=>	'image/tiff',
            'tif'	=>	'image/tiff',
            'css'	=>	'text/css',
            'html'	=>	'text/html',
            'htm'	=>	'text/html',
            'shtml'	=>	'text/html',
            'txt'	=>	'text/plain',
            'text'	=>	'text/plain',
            'log'	=>	['text/plain', 'text/x-log'],
            'rtx'	=>	'text/richtext',
            'rtf'	=>	['text/rtf', 'application/rtf'],
            'xml'	=>	'text/xml',
            'xsl'	=>	'text/xml',
            'mpeg'	=>	'video/mpeg',
            'mpg'	=>	'video/mpeg',
            'mpe'	=>	'video/mpeg',
            'qt'	=>	'video/quicktime',
            'mov'	=>	'video/quicktime',
            'avi'	=>	'video/x-msvideo',
            'movie'	=>	'video/x-sgi-movie',
            'doc'	=>	['application/msword', 'application/doc'],
            'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'word'	=>	'application/msword',
            'xl'	=>	'application/excel',
            'eml'	=>	'message/rfc822',
            'csv'	=>	['text/x-comma-separated-values', 'text/comma-separated-values', 'application/vnd.ms-excel', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'],
        ];
    }

    public static function getMimeTypeByFileExtension($fileExt)
    {
        $mimeTypes = self::getMimeTypes();

        if (!isset($mimeTypes[$fileExt])) {
            return 'application/octet-stream';
        }

        return ((is_array($mimeTypes[$fileExt])) ? $mimeTypes[$fileExt][0] : $mimeTypes[$fileExt]);
    }

    public static function getFileExtension($fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        if (stristr($ext, 'octet-stream')) {
            if (stristr($fileName, '.')) {
                $ext = array_pop( explode( '.', $fileName));
            }
        }

        return $ext;
    }

    public static function download($wtkFile)
    {
        try {
            $fncDocStorageService = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);
            $fncContents = $fncDocStorageService->readFromS3($wtkFile['ExtPath'], $wtkFile['NewFileName']);
        } catch (AwsException $awsException) {
            self::displayFileNotFound();
        }
        return $fncContents;
        /*
        // ABS changed so this is only called when ExternalStorage = 'Y'
        if ($wtkFile['ExternalStorage'] == 'Y') {

            try {
                $fncDocStorageService = new DocumentStorageService(getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : null);
                $fncContents = $fncDocStorageService->readFromS3($wtkFile['UID'], strtotime($wtkFile['ExtPath']));
            } catch (AwsException $awsException) {
                self::displayFileNotFound();
            }

        } else {

            $fncFileLoc = self::DOCUMENT_MANAGER . $wtkFile['UID'];

            if (file_exists($fncFileLoc)) {
                $fncContents = file_get_contents($fncFileLoc);
            } else {
                self::displayFileNotFound();
            }

        }

        $fileName = $wtkFile['NewFileName'];
        $contentType = self::getMimeTypeByFileExtension($wtkFile['FileExtension']);
        $fileSize = strlen($fncContents);

        header('Content-Type: "' . $contentType . '"');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header("Content-Length: " . $fileSize);

        if (strstr($_SERVER['HTTP_USER_AGENT' ], 'MSIE')) {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        echo $fncContents;
        exit();
        */
    }

    public static function displayFileNotFound()
    {
        global $gloTechSupport;
        $fncHtm  = '<h2>File Error</h2><p>File does not exist on server.' . "\n";
        $fncHtm .= " Contact tech support at <a href=\"mailto:$gloTechSupport\">$gloTechSupport</a></p>" . "\n";
        wtkMergePage($fncHtm,'Download File','wtk/htm/minibox.htm');
    }
}
