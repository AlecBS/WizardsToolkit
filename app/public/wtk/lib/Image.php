<?php
/**
* Wizard's Toolkit functions involving images: listing, resizing, displaying.
*
* This can be used without the rest of the WTK library in which case just:
* include('Image.php');
* The img.php and photo.php file must be accesible within the folder this is called from.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

// 2FIX need to decide how to handle wtkIsSSL() if only including this one file
// 2FIX need to change wtkPhotosSpread to use PDO
// 2VERIFY PopPhoto is JS function but can probably be replaced by new MaterializeCSS method
// 2VERIFY all works without need of the rest of WTK library.

/**
* Image Folder Path
*
* This prevents getimagesize error when image path is higher in the directory heirarchy
*
* @param string $fncImageName
* @return image name after full web URL path
*/
function wtkImgFolderPath($fncImageName) {
    $fncResult = $fncImageName;
    $fncFirstChar = substr($fncImageName, 0, 1);
    if ($fncFirstChar == '/'):
        if (wtkIsSSL() == TRUE):
            $fncResult = 'https://' . $_SERVER['HTTP_HOST'] . $fncImageName;
        else:   // Not wtkIsSSL()
            $fncResult = 'http://' . $_SERVER['HTTP_HOST'] . $fncImageName;
        endif;  // wtkIsSSL()
    endif;  // $fncFirstChar == '/'
    return $fncResult;
}  // end of wtkImgFolderPath

/**
* Pass in parameters and HTML for <img > is generated
*
* @param string $fncImageName
* @param string $fncTitle Defaults to blank
* @param string $fncClass Defaults to blank
* @param string $fncWidth Defaults to '0'
* @param string $fncHeight Defaults to '0'
* @return HTML image syntax
*/
function wtkHtmImage($fncImageName, $fncTitle = '', $fncClass = '', $fncWidth = 0, $fncHeight = 0) {
    $fncResult = '<img src="' . $fncImageName . '" border="0"';
    if ($fncTitle != ''):
        $fncResult .= ' alt="' . $fncTitle . '" title="' . $fncTitle . '"' ;
    endif;  // $fncTitle != ''
    if ($fncClass != ''):
        $fncResult .= ' class="' . $fncClass . '"' ;
    endif;  // $fncClass != ''
    if ($fncWidth != 0):
        $fncResult .= ' width="' . $fncWidth . '"' ;
    endif;  // $fncWidth != 0
    if ($fncHeight != 0):
        $fncResult .= ' height="' . $fncHeight . '"' ;
    endif;  // $fncHeight != 0
    $fncResult .= '>' ;
    return $fncResult;
}  // end of wtkHtmImage

/**
* Resize Image
*
* Pass in image and Width, Height and Type.  If both width and height are passed it will use those exact values.
* If only width is passed it will adjust the height keeping the original ratio.
* If only height is passed it will adjust the width keeping the original ratio.
*
* @param string $fncImageName
* @param integer $Width Defaults to '0'
* @param integer $Height Defaults to '0'
* @return image HTML for image to generate image in correct size
*/
function wtkResizeImg($fncImageName, $fncWidth = 0, $fncHeight = 0){
    $fncImageName = wtkImgFolderPath($fncImageName);  // to handle directory errors
    if (($fncHeight == 0) && ($fncWidth == 0)):
        list($fncWidth2, $fncHeight2, $fncType2, $fncAttr) = getimagesize($fncImageName);
        $fncHeight = $fncHeight2;
        $fncWidth = $fncWidth2;
    elseif ($fncHeight == 0):
        list($fncWidth2, $fncHeight2, $fncType2, $fncAttr) = getimagesize($fncImageName);
        $fncRatio  = $fncWidth2 / $fncHeight2;
        $fncHeight = $fncWidth / $fncRatio;
    elseif ($fncWidth == 0):
        list($fncWidth2, $fncHeight2, $fncType2, $fncAttr) = getimagesize($fncImageName);
        $fncRatio  = $fncWidth2 / $fncHeight2;
        $fncWidth  = $fncHeight * $fncRatio;
    endif;
    $fncWidth = (int) $fncWidth;
    $fncHeight = (int) $fncHeight;

/*
    switch ($fncType): // was last parameter but I removed
        case 0 :
            $fncImgType = 'jpeg';
            break;
        case 1 :
            $fncImgType = 'png';
            break;
        default :
            $fncImgType = 'jpeg'; //IN CASE AN INVALID VALUE IS GIVEN IN THE TYPE FIELD
    endswitch; // Type
    $fncImage  = '<img src="' . _RootPATH . 'img.php?header=' . $fncImgType . "&ImageFileName=" . $fncImageName . "&NewWidth=" . $fncWidth . "&NewHeight=" . $fncHeight . '"';
*/
//  $fncImage  = '<img src="' . $fncImageName . '" class="materialboxed z-depth-4" border="0" width="' . $fncWidth . '" height="' . $fncHeight . '"/>';
// removed img.php since download speeds are so fast now; will add this back in if developers request it
    $fncImage  = '<img src="' . $fncImageName . '" class="materialboxed responsive-img z-depth-4" border="0"/>';
    return $fncImage;
}  // function wtkResizeImg

/**
* Max Size Image
*
* Pass in the maximum width or height you want and this will check the image and then call wtkResizeImg function to build it.
* If the image is smaller than the maximum values sent it will not have the image size changed.
*
* @param string $fncImageName
* @param string $MaxWidth Defaults to '0'
* @param string $MaxHeight Defaults to '0'
* @return image HTML using wtkResizeImg
*/
function wtkMaxSizeImg($fncImageName, $fncMaxWidth = 0, $fncMaxHeight = 0) {
    $fncImageName = wtkImgFolderPath($fncImageName);  // to handle directory errors
    list($fncWidth2, $fncHeight2, $fncType, $fncAttr) = getimagesize($fncImageName);
    if (($fncMaxWidth >= $fncWidth2) && ($fncMaxHeight >= $fncHeight2)):
//      $fncImage = '<img src="' . $fncImageName . '" class="materialboxed z-depth-4" border="0"  width="' . $fncWidth2 . '" height="' . $fncHeight2 . '"/>';
        $fncImage = '<img src="' . $fncImageName . '" class="materialboxed responsive-img z-depth-4" border="0"/>';
    else:   // Not ($fncMaxWidth >= $fncWidth2) && ($fncMaxHeight >= $fncHeight2)
        if (($fncWidth2 / $fncHeight2) > 1):
            if ($fncWidth2 > $fncMaxWidth):
                $fncImage = wtkResizeImg($fncImageName, $fncMaxWidth, 0);
            elseif ($fncHeight2 > $fncMaxHeight):
                $fncImage = wtkResizeImg($fncImageName, 0, $fncMaxHeight);
            else:
                $fncImage = wtkResizeImg($fncImageName, 0, 0);
            endif;
        else:
            if ($fncHeight2 > $fncMaxHeight):
                $fncImage = wtkResizeImg($fncImageName, 0, $fncMaxHeight);
            elseif ($fncWidth2 > $fncMaxWidth):
                $fncImage = wtkResizeImg($fncImageName, $fncMaxWidth, 0);
            else:
                $fncImage = wtkResizeImg($fncImageName, 0, 0);
            endif;
        endif;
    endif;  // ($fncMaxWidth >= $fncWidth2) && ($fncMaxHeight >= $fncHeight2)
    return $fncImage;
} // function wtkMaxSizeImg

/**
* Image Popup Window
*
* Wrappper with popup window for full-size image.  Pass in Maximum Width and Height and it calls wtkMaxSizeImg
*
* @param string $fncImageName
* @param string $MaxWidth Defaults to '0'
* @param string $MaxHeight Defaults to '0'
* @param string $fncPath Defaults to blank
* @return HTML with thumbnail of image and link to popup window with full-sized image
*/
function wtkImagePopWin($fncImageName, $fncMaxWidth = 0, $fncMaxHeight = 0, $fncPath = '') {
    $fncImageName = wtkImgFolderPath($fncImageName);  // to handle directory errors
    list($fncWidth, $fncHeight, $fncType, $fncAttr) = getimagesize($fncImageName);
    $fncImageName = trim($fncImageName);
/*
    $fncAstart  = '<a title="click for full size" onClick="JavaScript:PopPhoto(\'' . $fncImageName . "',";
    $fncAstart .= $fncWidth . ',' . $fncHeight;
//    if ($fncPath != ''):
        $fncAstart .=  ",'" . $fncPath . "'";
//    endif;  // $fncPath != ''
    $fncAstart .= ')' . '">';
    $fncImageSrc  = $fncAstart . wtkMaxSizeImg($fncImageName, $fncMaxWidth, $fncMaxHeight);
*/
    $fncImageSrc = wtkMaxSizeImg($fncImageName, $fncMaxWidth, $fncMaxHeight);
//    $fncImageSrc .= '</a>' . "\n";

    return $fncImageSrc;
}  // end of wtkImagePopWin

/**
* Read Directory and fill array with all files based on File Type passed.
*
* Defaults to '.' for same folder as current PHP file but you can pass in the folder
* you want to search. This will find all files in that folder and all subfolders.
*
* Second parameter passed defaults to 'image' and will fill the array with all files
* with case insensitive file extension of: jpg, jpeg, png, gif.
* If pass in 'video' fills array with all files with with case insensitive
* file extension of: mp4, mov, webm, ogv
* If second parameter is anything else then only files with that exact file extension are retrieved.
*
* @param string $fncDir pass in directory to search.  Use '.' for current directory.
* @param string $fncFileType defaults to 'image'. Pass in 'image', 'video' or any single file extension you desire.
* @return array of files within a folder tree based on $fncFileType passed
*/
function wtkReadDir($fncDir = '.', $fncFileType = 'image', $fncSubCall = 'N'){
    $fncResult = array();
    if ($fncHandle = opendir($fncDir)):
        while (($fncSubDir = readdir($fncHandle)) !== false):
            if ($fncSubDir != '.' && $fncSubDir != '..' && $fncSubDir != 'Thumb.db' && $fncSubDir != '.DS_Store'):
                if (is_dir($fncDir . '/' . $fncSubDir)):
                    $fncResult[$fncSubDir] = wtkReadDir($fncDir . '/' . $fncSubDir, $fncFileType, 'Y');
                else: // not a sub folder
                    $fncOkToAdd = false;
                    $fncExt = strtolower(pathinfo($fncSubDir, PATHINFO_EXTENSION));
                    switch ($fncFileType):
                        case 'image':
                            switch ($fncExt):
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    $fncOkToAdd = true;
                                    break;
                            endswitch;
                            break;
                        case 'video':
                            switch ($fncExt):
                                case 'mov':
                                case 'mp4':
                                case 'webm':
                                case 'ogg':
                                    $fncOkToAdd = true;
                                    break;
                            endswitch;
                            break;
                        default:
                            if ($fncExt == $fncFileType):
                                $fncOkToAdd = true;
                            endif;
                    endswitch;
                    if ($fncOkToAdd == true):   // so ignores php files in image folder
                        if (is_file($fncDir . '/' . $fncSubDir)):
                            if (($fncSubCall == 'Y') || ($fncDir == '.')):
                                $fncResult[] = $fncSubDir;
                            else:
                                $fncTmp = $fncDir . '/' . $fncSubDir;
//                                $fncTmp = wtkReplace($fncTmp, $fncDir . '/./' . $fncDir, $fncDir);
                                $fncResult[] = $fncTmp;
                            endif;
                        endif;  // is_file($fncDir.'/'.$fncSubDir)
                    endif;  // $fncPos === false
                endif;  // is_file($fncDir.'/'.$fncSubDir)
            endif;  // $fncSubDir != '.' && $fncSubDir != '..' && $fncSubDir != 'Thumb.db' && $fncSubDir != '.DS_Store'
        endwhile; // ($fncSubDir = readdir($fncHandle)) !== false
        closedir($fncHandle);
    endif;  // $fncHandle = opendir($fncDir)
    return $fncResult;
}  // end of wtkReadDir

/**
* File Display
*
* This generates HTML5 to display images, videos or PDFs showing them several across and
* expanding the view to display them when clicked.
*
* You can use wtkReadDir to create an array of files to pass to this function.  The files can be
* in this folder and/or in subfolders. This function shows all the files in the passed array.
*
* If you do not pass the $fncFilesPerRow parameter it sets that based on count of files in $fncFileArray.
*   if 1 to 3 then show on one line
*   4 photos show in 2 lines of 2
*   5 to 6 show in 2 lines of 3
*   7 to 8 photos show in 2 lines of 4
*   9 show in 3 lines of 3
*   10 to 16 show 4 per line
*   more than 16 then show 6 per line
*
* If $fncFilesPerRow does not equal 0,1,2,3,4, or 6, then invalid so ignored and calculates based on
* above logic.
*
* @param array $fncFileArray containing files you wish to display
* @param string $fncShowName Defaults to 'N'; if 'Y' passed then uses file name for descriptor text
* @param numeric $fncFilesPerRow Defaults to 0 then decides based on count of files in array
* @return HTML showing all files from $fncFileArray array
*/
function wtkFileDisplay($fncFileArray, $fncShowName = 'N', $fncFilesPerRow = 0) {
    if (($fncFilesPerRow == 5) || ($fncFilesPerRow > 6)):
        $fncFilesPerRow = 0;
    endif;
    if ($fncFilesPerRow == 0):
        $fncFileCount = count($fncFileArray, COUNT_RECURSIVE);
        if ($fncFileCount > 16):
            $fncFilesPerRow = 6;
        elseif ($fncFileCount == 9):
            $fncFilesPerRow = 3;
        elseif ($fncFileCount == 4):
            $fncFilesPerRow = 2;
        elseif ($fncFileCount > 6):
            $fncFilesPerRow = 4;
        elseif ($fncFileCount > 4):
            $fncFilesPerRow = 3;
        else:
            $fncFilesPerRow = $fncFileCount;
        endif;
    endif;
    $fncRowCntr = 0;
    $fncResult = '<div class="row">' . "\n";
    foreach ($fncFileArray as $fncKey => $fncFile):
        if (is_array($fncFile)):
            foreach ($fncFile as $fncInnerKey => $fncInnerFile):
                list($fncRowCntr, $fncCell) = wtkFileCell($fncKey . '/' . $fncInnerFile, $fncShowName, $fncFilesPerRow, $fncRowCntr);
                $fncResult  .= $fncCell;
            endforeach;
        else:
            list($fncRowCntr, $fncCell) = wtkFileCell($fncFile, $fncShowName, $fncFilesPerRow, $fncRowCntr);
            $fncResult  .= $fncCell;
        endif;
    endforeach;
    if ($fncRowCntr == 0):
        $fncResult  = 'no files to display';
    else:
        $fncResult .= '</div>';
    endif;
    return $fncResult;
}  // end of wtkFileDisplay

/**
* Called by wtkFileDisplay this generates HTML to display image, video or PDF
*
* Probably should never be called directly.  This receives variables from wtkFileDisplay and
* returns HTML to go within a File Display spread.
*
* @param char $fncFile contains name of file to display
* @param char $fncShowName Defaults to 'N'; if 'Y' passed then uses file name for descriptor text
* @param numeric $fncFilesPerRow determines how many files to display across a row
* @param numeric $fncRowCntr contains counter for row to determine if should start new row
* @return HTML returns HTML to display file that was passed
*/
function wtkFileCell($fncFile, $fncShowName, $fncFilesPerRow, $fncRowCntr) {
    $fncRowCntr += 1;
    $fncResult = '';
    if ($fncRowCntr > $fncFilesPerRow):
        $fncResult .= '</div>' . "\n" . '<div class="row">' . "\n";
        $fncRowCntr = 1;
    endif;
    $fncColSize = 'm' . (12 / $fncFilesPerRow);
    if ($fncFilesPerRow == 6):
        $fncColSize = 's6 ' . $fncColSize;
    else:
        $fncColSize = 's12 ' . $fncColSize;
    endif;
    $fncResult .= '<div class="col ' . $fncColSize . '" align="center">';
    // BEGIN Determine file type to display
    if ($fncShowName == 'Y'):
        $fncName = wtkInsertSpaces(pathinfo($fncFile, PATHINFO_FILENAME));
    endif;
    $fncExt = strtolower(pathinfo($fncFile, PATHINFO_EXTENSION));
    switch ($fncExt):
        case 'pdf':
            $fncResult .= '<br><br><a target="_blank" href="' . $fncFile . '">';
            $fncResult .= '<img class="z-depth-2" src="/wtk/imgs/pdf.png"></a>' . "\n";
            break;
        case 'ogv':
            $fncVidType = 'ogg';
        case 'mov':
        case 'mp4':
        case 'webm':
            if (!isset($fncVidType)):
                $fncVidType = 'mp4';
            endif;
            $fncResult .=<<<htmVAR

<div class="video-container">
    <video class="responsive-video" controls autoplay>
        <source src="$fncFile" type="video/$fncVidType">
    </video>
</div>
htmVAR;
            break;
        default: // jpg, png, gif, jpeg
            $fncResult .= '<img class="responsive-img materialboxed z-depth-4" src="' . $fncFile . '"';
            if ($fncShowName == 'Y'):
                $fncResult .= ' data-caption="' . $fncName . '"';
            endif;
            $fncResult .= '>';
            break;
    endswitch;
    //  END  Determine file type to display
    if ($fncShowName == 'Y'):
        $fncResult .= "<p>$fncName</p>" . "\n";
    endif;
    $fncResult .= '</div>' . "\n";
    return array($fncRowCntr, $fncResult);
}  // end of wtkFileCell

/**
* Image FancyBox Window popup
*
* This assumes that all image names in list are unique otherwise FancyBox will not work
*
* @param string $fncImageName
* @param string $MaxWidth Defaults to '0'
* @param string $MaxHeight Defaults to '0'
* @param string $fncPath Defaults to blank
* @return HTML with thumbnail of image and link to popup window with full-sized image
*/
function wtkImageFancyBox($fncImageName, $fncMaxWidth = 0, $fncMaxHeight = 0) {
    // created as a replacement fo wtkImagePopWin
    $fncImageName = wtkImgFolderPath($fncImageName);  // to handle directory errors
//  list($fncWidth, $fncHeight, $fncType, $fncAttr) = getimagesize($fncImageName);
    $fncImageName = trim($fncImageName);
//  $fncFancyID = wtkReplace($fncImageName, '.','_');
    $fncImageSrc  = '<a class="fancybox" href="' . $fncImageName . '">';
    $fncPos = stripos($fncImageName,'.pdf');
    if ($fncPos === false):
        $fncImageSrc .= wtkMaxSizeImg($fncImageName, $fncMaxWidth, $fncMaxHeight);
    else:   // Not $fncPos === false
        $fncImageSrc .= wtkHtmImage('wtk/imgs/pdf.png');
    endif;  // $fncPos === false
    $fncImageSrc .= '</a>' . "\n";
    return $fncImageSrc;
}  // end of wtkImageFancyBox

/**
* wtkPhotosSpread - FancyBox based photo gallery
*
* Pass in SQL to retrieve photos and build photo gallery based on # of photos
*
* Determine size and number of lines for photos based on number of photos
*  if 1 to 3 then one line
*  4 photos show in 2 lines of 2
*  5 to 6 photos show in 2 lines of 3
*  7 to 8 photos show in 2 lines of 4
*  9 show in 3 lines of 3
*  10 to 12 show in 3 lines of 4
*
* @param int    $fncPhotoCount - number of photos that will be returned
* @param string $fncSQL - SELECT that retrieves photo URLs from data, must use column names of 'Caption' and 'PhotoURL'
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string  $fncImageFolder - which subfolder the images are located in, defaults to current folder
* @return HTML table with thumbnails and FancyBox to pop full photos
*/
function wtkPhotosSpread($fncPhotoCount, $fncSQL, $fncSqlFilter, $fncImageFolder = '') {
    // Pass in PhotoCount and SQL to show photos using specific column names
    // show 1 to 12 photos in the optimal sizing and spacing
    global $gloWTKobjConn, $gloLang;
    $fncResult = '';
    if ($fncPhotoCount == 0):
        $fncResult .= 'No photos uploaded yet.' . "\n";
    else:   // Not $fncPhotoCount == 0
//      $fncResult .= wtkHtmTableTop('100%', 0, 0, 0, 'class="table-bordered"');
        $fncResult .= '<table><tr>';
//function wtkHtmTableTop($fncWidth = '100%', $fncCellSpacing = '0',  $fncCellPadding = '0',  $fncBorder = '0',  $fncAttrib = '') {
        /* Determine size and number of lines for photos based on number of photos
           if 1 to 3 then one line
           4 photos show in 2 lines of 2
           5 to 6 photos show in 2 lines of 3
           7 to 8 photos show in 2 lines of 4
           9 show in 3 lines of 3
           10 to 12 show in 3 lines of 4
        */
        // Width is based on 420 split by number of images
        if ($fncPhotoCount < 4):
            $fncPhotosLine1 = $fncPhotoCount;
            $fncImgWidth  = round(420 / $fncPhotoCount);
        else:
            $fncImgWidth  = 105;
            switch ($fncPhotoCount):
                case 4 :
                    $fncPhotosLine1 = 2;
                    $fncPhotosLine2 = 2;
                    $fncImgWidth  = 210;
                    break;
                case 5 :
                    $fncPhotosLine1 = 3;
                    $fncPhotosLine2 = 2;
                    $fncImgWidth  = 140;
                    break;
                case 6 :
                    $fncPhotosLine1 = 3;
                    $fncPhotosLine2 = 3;
                    $fncImgWidth  = 140;
                    break;
                case 7 :
                    $fncPhotosLine1 = 4;
                    $fncPhotosLine2 = 3;
                    break;
                case 8 :
                    $fncPhotosLine1 = 4;
                    $fncPhotosLine2 = 4;
                    break;
                case 9 :
                    $fncPhotosLine1 = 3;
                    $fncPhotosLine2 = 3;
                    $fncPhotosLine3 = 3;
                    break;
                case 10 :
                    $fncPhotosLine1 = 4;
                    $fncPhotosLine2 = 3;
                    $fncPhotosLine3 = 3;
                    break;
                case 11 :
                    $fncPhotosLine1 = 4;
                    $fncPhotosLine2 = 4;
                    $fncPhotosLine3 = 3;
                    break;
                case 12 :
                    $fncPhotosLine1 = 4;
                    $fncPhotosLine2 = 4;
                    $fncPhotosLine3 = 4;
                    break;
            endswitch; // fncPhotoCount
        endif;  // $fncPhotoCount < 4

        $fncImgHeight = 150 ;  // ABS 05/06/12  I think we don't need to reduce this...  round(250 / $fncPhotoCount);
        $fncLoopCntr  = 0 ;
        $fncRowCntr   = 1 ;

        $fncWTKobjRS = $gloWTKobjConn->Execute($fncSQL);
        $fncWTKobjRS->MoveFirst();
        while (!$fncWTKobjRS->EOF):
            switch ($fncRowCntr):
                case 1 :
                    if ($fncLoopCntr == $fncPhotosLine1):
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncResult .= '<td colspan="' . $fncPhotosLine1 . '">&nbsp;</td>' . "\n";
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncRowCntr = ($fncRowCntr + 1) ;
                        $fncLoopCntr = 0;
                    endif;  // $fncLoopCntr == $fncPhotosLine1
                    break;
                case 2 :
                    if ($fncLoopCntr == $fncPhotosLine2):
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncResult .= '<td colspan="' . $fncPhotosLine2 . '">&nbsp;</td>' . "\n";
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncRowCntr = ($fncRowCntr + 1) ;
                        $fncLoopCntr = 0;
                    endif;  // $fncLoopCntr == $fncPhotosLine2
                    break;
                case 3 :
                    if ($fncLoopCntr == $fncPhotosLine3):
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncResult .= '<td colspan="' . $fncPhotosLine3 . '">&nbsp;</td>' . "\n";
                        $fncResult .= '</tr>' . "\n" . '<tr>' . "\n";
                        $fncRowCntr = ($fncRowCntr + 1) ;
                        $fncLoopCntr = 0;
                    endif;  // $fncLoopCntr == $fncPhotosLine3
                    break;
            endswitch; // fncRowCntr
// $fncResult .= '<td align="center">' . wtkImagePopWin('PrjImgs/' . $fncWTKobjRS->fields('PhotoURL'), $fncImgWidth, $fncImgHeight, './') . '</td>' . "\n";
            $fncCaption = $fncWTKobjRS->fields('Caption');
            $fncImage = wtkImageFancyBox($fncImageFolder . $fncWTKobjRS->fields('PhotoURL'), $fncImgWidth, $fncImgHeight);
            $fncImage = wtkReplace($fncImage, '<a ','<a rel="ProjGallery" title="' . $fncCaption . '" ');
            $fncResult .= '<td align="center">' . $fncImage . '<br>' . $fncCaption . '</td>' . "\n";

            $fncWTKobjRS->MoveNext();
            $fncLoopCntr = ($fncLoopCntr + 1);
        endwhile;
        $fncResult .= '</tr></table>' . "\n";
    endif;  // $fncPhotoCount == 0
    return $fncResult;
}  // end of wtkPhotosSpread

/**
* wtkReduceImageSize - actually reduce the size of an image
*
* Pass in the binary of an image and the maximum width you want.
* If current width is > desired new width, calculate what new height should be then generate image and return as binary.
*
* This has not been stress tested against all image types and scenarios yet.
* Calling method:
*
try {
    $pgImageBinary = wtkReduceImageSize($pgOrigImageBinary, 630);
} catch (Exception $e) {
    wtkLogError('Image Resize', $e->getMessage());
}
*
* @param binary $fncImageBinary - binary of an image
* @param int   $fncNewMaxWidth - new maximum width
* @return binary of image at reduced size
*/
function wtkReduceImageSize($fncImageBinary, $fncNewMaxWidth) {
    // Create image from binary data
    $originalImage = imagecreatefromstring($fncImageBinary);

    if ($originalImage === false) {
        throw new Exception("Failed to create image from binary data");
    }

    // Get original dimensions
    $originalWidth = imagesx($originalImage);
    $originalHeight = imagesy($originalImage);

    // Check if resizing is needed
    if ($originalWidth <= $fncNewMaxWidth) {
        // No resizing needed, return original binary
        imagedestroy($originalImage);
        return $fncImageBinary;
    }

    // Calculate new height while maintaining aspect ratio
    $ratio = $originalHeight / $originalWidth;
    $newWidth = $fncNewMaxWidth;
    $newHeight = (int) round($newWidth * $ratio);

    // Create new image with calculated dimensions
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($newImage === false) {
        imagedestroy($originalImage);
        throw new Exception("Failed to create new image resource");
    }

    // Preserve transparency for PNG and GIF
    $isTransparent = false;
    $imageType = null;

    // Try to detect image type and preserve transparency
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $fncImageBinary);
    finfo_close($finfo);

    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        $imageType = ($mimeType === 'image/png') ? IMAGETYPE_PNG : IMAGETYPE_GIF;
        $isTransparent = true;

        // Handle transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        if ($mimeType === 'image/gif') {
            // For GIF, try to handle transparency
            $transparentIndex = imagecolortransparent($originalImage);
            if ($transparentIndex >= 0) {
                $transparentColor = imagecolorsforindex($originalImage, $transparentIndex);
                $transparentIndex = imagecolorallocate($newImage,
                    $transparentColor['red'],
                    $transparentColor['green'],
                    $transparentColor['blue']);
                imagefill($newImage, 0, 0, $transparentIndex);
                imagecolortransparent($newImage, $transparentIndex);
            }
        } else {
            // For PNG, allocate transparent background
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
    }

    // Resize image with high quality
    $success = imagecopyresampled(
        $newImage,      // Destination image
        $originalImage, // Source image
        0, 0,           // Destination x, y
        0, 0,           // Source x, y
        $newWidth,      // Destination width
        $newHeight,     // Destination height
        $originalWidth, // Source width
        $originalHeight // Source height
    );

    if (!$success) {
        imagedestroy($originalImage);
        imagedestroy($newImage);
        throw new Exception("Failed to resize image");
    }
    ob_start(); // Capture output to get binary data

    // Output based on detected image type
    if ($isTransparent) {
        if ($imageType === IMAGETYPE_PNG) {
            imagepng($newImage, null, 9); // Maximum compression for PNG
        } elseif ($imageType === IMAGETYPE_GIF) {
            imagegif($newImage);
        }
    } else {
        // Default to JPEG with 85% quality (good balance of size/quality)
        imagejpeg($newImage, null, 85);
    }

    $fncNewImageBinary = ob_get_clean();

    // Clean up
    imagedestroy($originalImage);
    imagedestroy($newImage);

    if ($fncNewImageBinary === false || strlen($fncNewImageBinary) === 0) {
        throw new Exception("Failed to generate new image binary");
    }

    return $fncNewImageBinary;
} // wtkReduceImageSize
?>
