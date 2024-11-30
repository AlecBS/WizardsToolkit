<?php
/******************************************************
IMAGE RESIZE CUSTOM FUNCTIONS BASED ON GD LIBRARY

REQUIRES GD IMAGE SUPPORT AND PHP INSTALLED ON SERVER
ZIP LIBRARY SHOULD BE INSTALLED ON SERVER FOR BETTER PERFORMANCE

ERIC IGLESIAS
https://ProgrammingLabs.com
March 9, 2006

sample syntax

img.php?header=jpeg&ImageFileName=as11-40-5903.jpg&NewWidth=400&NewHeight=400&keepratio=1

can be inserted in html in this manner:
<img src="img.php?header=jpeg&ImageFileName=as11-40-5903.jpg&NewWidth=400&NewHeight=400&keepratio=1" border="0" />

another sample
http://localhost/~eric/ImageSize/img.php?header=jpeg&ImageFileName=http://localhost/~eric/ImageSize/as11-40-5903.jpg&NewWidth=100&NewHeight=400&keepratio=1

ARGUMENTS

header is either jpeg or png, and is the output format
ImageFileName is the filename
NewWidth is the new file width
NewHeight is the new file Height
keepratio is either 0 or 1 (1 keeps ratio)

FOR A FUTURE VERSION:

IMPROVE ERROR HANDLING (CURRENTLY ONLY FAILS WITH BAD FILENAME, BUT OTHER ARGUMENTS CAN ALSO BE BAD)

IF USED IN CONJUNCTION WITH THE WRAPPER FILE, THEN WRAPPER WILL MAKE THE VALUES ALWAYS CORRECT FOR THE FILE, AVOIDING ERRORS

******************************************************/

/*VAR TEST VALUES
$header = "jpeg"; //determines output format, either png of jpeg
$ImageFileName = "as11-40-5903.jpg";
$NewWidth = "400";
$NewHeight = "400";
$keepratio = "1"; //variable to keep ratio 0 = no keep, 1 = force ratio.  retains width
*/

//IF ZLIB IS INSTALLED, SEND COMPRESSED INFO (ZLIB OR DEFLATE)
if (extension_loaded('zlib')):
    ob_start('ob_gzhandler');
endif;

if (isset($_GET["NewHeight"])):
    $NewHeight = $_GET["NewHeight"];
    $keepratio = 0;
    if ($_GET["NewHeight"] == 0):
        $keepratio = 1;
    endif;
endif;

if (isset($_GET["header"]) && isset($_GET["ImageFileName"]) && isset($_GET["NewWidth"])):
    $header = $_GET["header"];
    $ImageFileName = utf8_decode($_GET["ImageFileName"]);
    $NewWidth = $_GET["NewWidth"];
    if ((!(isset($_GET["NewHeight"]))) || ($_GET["NewHeight"] == 0)):
        $keepratio = 1;
    endif;
else:
    exit('Missing arguments');
endif;
//GET ORIGINAL FILE ATTRIBUTES, TO DO THINGS LIKE SCALING WITHOUT DISTORTION
//ALSO DETERMINES TYPE
/*KNOWN FILE TYPES 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM */
list($width, $height, $type, $attr) = getimagesize($ImageFileName);

$ratio = $width / $height;

if ( ($keepratio == "1") || ($ratio < 1)):
    //CHANGE WIDTH TO MATCH RATIO
    $NewHeight = round(($NewWidth / $ratio), 2);
endif;

//LOADS IMAGE FROM FILE INTO VARIABLE.  ASSUMES IS ALWAYS JPEG
if ($type == "2"):
    $image = imagecreatefromjpeg($ImageFileName);
elseif($type == "3"):
    $image = imagecreatefrompng($ImageFileName);
elseif($type == "1"):
    $image = imagecreatefromgif($ImageFileName);
endif;

//ERROR HANDLER
if (!$image): /* See if it failed */
    $image  = imagecreate($NewWidth, $NewHeight); /* Create a blank image */
    $bgc = imagecolorallocate($image, 255, 255, 255);
    $tc  = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, 150, 30, $bgc);
    /* Output an errmsg */
    imagestring($image, 1, 5, 5, "Error loading $ImageFileName", $tc);
endif;

$image2 = imagecreatetruecolor($NewWidth, $NewHeight);

// if (imagecopyresampled($image2, $image, 0, 0, 0, 0, $NewWidth, $NewHeight, $width, $height) )
$tmp = imagecopyresampled($image2, $image, 0, 0, 0, 0, $NewWidth, $NewHeight, $width, $height);

//THIS DETERMINES OUTPUT FORMAT AND OUTPUTS IMAGE
if ($header == "png"):
    header("Content-type: image/png");
    imagepng($image2);
elseif ($header == "jpeg"):
    header("Content-type: image/jpeg");
    imagejpeg($image2);
endif;
?>
