<?php
require('wtk/lib/Utils.php');
$pgPW = wtkGetParam('pw');
if ($pgPW != 'LowCodeOrDie'):
    echo 'you are not allowed here';
    exit;
endif;

require "../vendor/autoload.php";
use Endroid\QrCode\QrCode;
$pgGoToUrl = wtkGetParam('url');

$qrCode = new QrCode($pgGoToUrl);

header('Content-Type: '.$qrCode->getContentType());
echo $qrCode->writeString();
?>
