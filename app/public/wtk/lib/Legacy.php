<?php
/**
* This contains alternative functions to be used only if running older PHP version
* which does not have these functions built-in
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
* @license     Copyright 2021-2024, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

/**
 * Alternative if error_get_last does not exist
 *
 */
if( !function_exists('error_get_last') ) {
    set_error_handler(
        create_function(
            '$errno,$errstr,$errfile,$errline,$errcontext',
            '
                global $__error_get_last_retval__;
                $__error_get_last_retval__ = array(
                    \'type\'        => $errno,
                    \'message\'        => $errstr,
                    \'file\'        => $errfile,
                    \'line\'        => $errline
                );
                return false;
            '
        )
    );
    function error_get_last() {
        global $__error_get_last_retval__;
        if( !isset($__error_get_last_retval__) ) {
            return null;
        }
        return $__error_get_last_retval__;
    }
}

/**
 * Alternative if array_fill_keys does not exist
 *
 */
if (!function_exists('array_fill_keys')) {
  function array_fill_keys (array $keys, $value) {
    return array_combine($keys,array_fill(0,count($keys),$value));
  }
}

/**
* Image Type to Extension - used if an older PHP version is used that does not already have this function
*
* @link https://www.php.net/manual/en/function.image-type-to-extension.php
* @param string $imagetype
* @param bool $include_dor Defaults to false
*/
if (!function_exists('image_type_to_extension')):
   function image_type_to_extension($fncImageType, $fncIncludeDot = false){
       if(empty($fncImageType)) return false;
       $fncDot = $fncIncludeDot ? '.' : '';
       switch($fncImageType):
           case IMAGETYPE_GIF    : return $fncDot . 'gif';
           case IMAGETYPE_JPEG   : return $fncDot . 'jpg';
           case IMAGETYPE_PNG    : return $fncDot . 'png';
           case 'image/gif'      : return $fncDot . 'gif';
           case 'image/jpeg'     : return $fncDot . 'jpg';
           case 'image/png'      : return $fncDot . 'png';
           case IMAGETYPE_SWF    : return $fncDot . 'swf';
           case IMAGETYPE_PSD    : return $fncDot . 'psd';
           case IMAGETYPE_WBMP   : return $fncDot . 'wbmp';
           case IMAGETYPE_XBM    : return $fncDot . 'xbm';
           case IMAGETYPE_TIFF_II : return $fncDot . 'tiff';
           case IMAGETYPE_TIFF_MM : return $fncDot . 'tiff';
           case IMAGETYPE_IFF    : return $fncDot . 'aiff';
           case IMAGETYPE_JB2    : return $fncDot . 'jb2';
           case IMAGETYPE_JPC    : return $fncDot . 'jpc';
           case IMAGETYPE_JP2    : return $fncDot . 'jp2';
           case IMAGETYPE_JPX    : return $fncDot . 'jpf';
           case IMAGETYPE_SWC    : return $fncDot . 'swc';
           default               : return $fncDot . 'jpg';  // changed from false to jpg
       endswitch;
   } // end of function image_type_to_extension
endif;  // !function_exists('image_type_to_extension')
?>
