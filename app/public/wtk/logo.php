<?PHP
// This can be used as an image on emails to determine when wtkEmailsSent is opened in email client
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgUID = wtkGetParam('e');
if (wtkGetParam('Skip') != 'Y'):
    if ($pgUID != ''):
        // so Gmail doesn't auto-trigger view add 3 seconds for testing
        if ($gloDriver1 == 'pgsql'):
            $pgSQL =<<<SQLVAR
UPDATE `wtkEmailsSent`
  SET `EmailOpened` = NOW()
WHERE `UID` = :UID AND
    `EmailOpened` IS NULL AND `AddDate` < (`AddDate`::timestamp + '3 second'::interval)
SQLVAR;
        else:
            $pgSQL =<<<SQLVAR
UPDATE `wtkEmailsSent`
  SET `EmailOpened` = NOW()
WHERE `UID` = :UID AND
    `EmailOpened` IS NULL AND `AddDate` < DATE_SUB(NOW(), INTERVAL 3 SECOND)
SQLVAR;
        endif;
        $pgSqlFilter = array (
            'UID' => $pgUID
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
    endif;
endif;

$pgImageFileName = '../imgs/emailLogo.png';
list($pgWidth, $pgHeight, $pgType, $pgAttr) = getimagesize($pgImageFileName);

$pgImage = '';
if ($pgType == "2"):
    $pgImage = imagecreatefromjpeg($pgImageFileName);
    $pgImgType = 'jpeg';
elseif($pgType == "3"):
    if (function_exists('imagecreatefrompng')):
        $pgImage = imagecreatefrompng($pgImageFileName);
        imageAlphaBlending($pgImage, true);
        imageSaveAlpha($pgImage, true);
    else:
        echo 'imagecreatefrompng function does not exist';
    endif;
    $pgImgType = 'png';
elseif($pgType == "1"):
    $pgImage = imagecreatefromgif($pgImageFileName);
    $pgImgType = 'gif';
endif;

// below 2 lines did not work in PHP8 2FIX
// $pgImage2 = imagecreatetruecolor(60, 55.5);
// if (imagecopyresampled($pgImage2, $pgImage, 0, 0, 0, 0, 60, 55.5, $pgWidth, $pgHeight) )
if ($pgImage == ''):
    echo 'image function failed';
else:
    $pgImage2 = $pgImage;
    header("Content-type: image/" . $pgImgType);
    if ($pgImgType == 'png'):
        imagepng($pgImage2);
    else:
        imagejpeg($pgImage2);
    endif;
endif;
exit;
?>
