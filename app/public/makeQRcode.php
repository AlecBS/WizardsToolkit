<?php
function wtkGetGet($fncParameter, $fncDefault = '') {
    $fncResult = isset($_GET[$fncParameter]) ? stripslashes(urldecode($_GET[$fncParameter])) : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult;
} // end of wtkGetGet

$pgPW = wtkGetGet('pw');
if ($pgPW != 'LowCodeOrDie'):
    echo 'you are not allowed here';
    exit;
endif;
require "../vendor/autoload.php";
use Endroid\QrCode\QrCode;
$pgGoToUrl = wtkGetGet('url');

$qrCode = new QrCode($pgGoToUrl);

header('Content-Type: '.$qrCode->getContentType());
echo $qrCode->writeString();
?>
