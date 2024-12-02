<?php
/**
* This contains Wizard's Toolkit functions for Google API calls and Map Links.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
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
* @license     Copyright 2021-2024, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

/**
* Pass in Latitude and Longitude and Google will return Time Zone.
*
* @param string $fncLatitude
* @param string $fncLongitude
* @param string $fncBasic defaults to 'N'; if set to 'Y' then will change 'America/Los_Angeles' to 'US/Pacific'
*    and other similar US time zones to basic options like US/Eastern, US/Mountain, etc.
* @global string $gloGoogleApiKey must be defined in wtk/wtkServerInfo.php for this to work
* @return string time zone
*/
function wtkGetTimeZoneFromGeoCodes($fncLatitude, $fncLongitude, $fncBasic = 'N'){
    global $gloGoogleApiKey;
    // retrieve TimeZone via geo-codes
    $fncUrl  = 'https://maps.googleapis.com/maps/api/timezone/json?location=';
    $fncUrl .= $fncLatitude . ',' . $fncLongitude . '&timestamp=';
    $fncUrl .= time() . '&key=' . $gloGoogleApiKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fncUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $fncResult = curl_exec($ch);
    curl_close($ch);
    $fncJSON = json_decode($fncResult, true);
    $fncTimeZone = '';
    if (json_last_error() === JSON_ERROR_NONE):
        // 2ENHANCE add error logging
        if ($fncJSON['status'] == 'OK'):
            $fncTimeZone = $fncJSON['timeZoneId'];
            if ($fncBasic == 'Y'):
                switch ($fncTimeZone):
                    case 'Pacific/Honolulu':
                        $fncTimeZone = 'US/Hawaii';
                        break;
                    case 'America/Anchorage':
                        $fncTimeZone = 'US/Alaska';
                        break;
                    case 'America/Los_Angeles':
                        $fncTimeZone = 'US/Pacific';
                        break;
                    case 'America/Denver':
                        $fncTimeZone = 'US/Mountain';
                        break;
                    case 'America/New_York':
                        $fncTimeZone = 'US/Eastern';
                        break;
                    case 'America/Phoenix':
                        $fncTimeZone = 'US/Arizona';
                        break;
                    case 'America/Chicago':
                        $fncTimeZone = 'US/Central';
                        break;
                endswitch;
            endif; // $fncBasic == 'Y'
        endif;
    endif;
    return $fncTimeZone;
} // wtkGetTimeZoneFromGeoCodes

$gloMapJS = '';
$gloMapJScenter = '';
/**
* Call this function to create a Google map with markers where your locations are.
*
* First time this is called the last parameter must be 'init'.
* Thereafter leave last parameter empty and function builds markers.
* This does require gmaps.js to work.
*
* @param string $fncLatitude
* @param string $fncLongitude
* @param string $fncIcon optionally pass icon to use as marker. Put file in /wtk/imgs/map/' .  $fncIcon . ".png
* @param string $fncTitle optionally include 'title'
* @param string $fncInfo optionally include text to be in infoWindow
* @param string $fncCall defaults to 'marker' but initial call this needs to pass 'init'
* @global string $gloGoogleApiKey must be defined in wtk/wtkServerInfo.php for this to work
* @global string $gloMapJScenter filled with code to make Google and GMaps work
* @global string $gloMapJS this receives JavaScript to make Map work
* @return null
*/
function wtkGmap($fncLat, $fncLng, $fncIcon = '', $fncTitle = '', $fncInfo = '', $fncCall = 'marker') {
    global $gloMapJScenter, $gloMapJS, $gloDeviceType, $gloGoogleApiKey;
    if ($fncCall == 'init'):
        $gloMapJScenter .= "  map.setCenter('" . $fncLat . "', '" . $fncLng . "');" . "\n" . '  map.fitZoom();';
//        $gloMapJScenter .= "  map.setCenter('" . $fncLat . "', '" . $fncLng . "');" . "\n" ;
//        $gloMapJScenter .= "  map.setZoom(10);"; // ABS 03/10/16   to rezoom the map then   map.setCenter(bounds.getCenter());
//        $gloMapJScenter .= "  map.setZoom((map.getBoundsZoomLevel(bounds)));"; // much too close
        $fncJS  = '<script src="//maps.google.com/maps/api/js?key=' . $gloGoogleApiKey . '" type="text/javascript"></script>' . "\n";
        $fncJS .= '<script src="/wtk/js/gmaps.js" type="text/javascript"></script>';
        wtkSearchReplace('<!--@BottomJSfiles-->', $fncJS);
        $fncJS  = '   map = new GMaps({' . "\n";
        if ($gloDeviceType == 'phone'):
            wtkSearchReplace('wtkMap','wtkMapSm');
            $fncJS .= "    div: '#wtkMapSm'," . "\n";
            $fncJS .= "    width: '380px'," . "\n";
            $fncJS .= "    height: '310px'," . "\n";
        else:
            $fncJS .= "    div: '#wtkMap'," . "\n";
        endif;
    else:   // Not $fncCall == 'init'
        $fncJS  = '   map.addMarker({' . "\n";
    endif;  // $fncCall == 'init'

    $fncJS .= '    lat: ' . $fncLat . ',' . "\n";
    $fncJS .= '    lng: ' . $fncLng ;
    if ($fncTitle != ''):
        $fncJS .= ',' . "\n" . "    title: '" . wtkReplace($fncTitle, "'", "\'") . "'" ;
    endif;  // $fncTitle != ''
    if ($fncInfo != ''):
        $fncJS .= ',' . "\n" . '    infoWindow: {' . "\n";
        $fncJS .= "      content: '" . wtkReplace($fncInfo, "'", "\'") . "'" . "\n" . '    }';
    endif;  // $fncInfo != ''
    if ($fncIcon != ''):
        global $gloWebBaseURL;
        $fncJS .= ',' . "\n" . '    icon : {' . "\n";
//        $fncJS .= '      size : new google.maps.Size(32, 32),' . "\n";
        if (substr($fncIcon,0,1) == 'B'): // can change size based on first letter of icon
            $fncJS .= '      iconsize: [37, 34],' . "\n";
            // ABS 2FIX - note these do not work, not sure how to change icon/marker size
//            $fncJS .= '      size:new google.maps.Size(74,68),' . "\n";
//            $fncJS .= '      size: "small",' . "\n";
//            $fncJS .= '      color: "blue",' . "\n";

        else:   // Not substr($fncIcon,0,1) == 'B'
            $fncJS .= '      iconsize: [30, 30],' . "\n";
        endif;  // substr($fncIcon,0,1) == 'B'
        $fncJS .= "      url: '" . $gloWebBaseURL . '/wtk/imgs/map/' .  $fncIcon . ".png'" . "\n" . '    }';
    endif;  // $fncIcon != ''
    $fncJS .= "\n" . '   });' . "\n";
    $gloMapJS .= $fncJS;
}  // end of wtkGmap

/**
* Create a link to native mapping based on device type.
*
* If iOS will direct to http://maps.apple.com
* If Android will direct to https://google.com/maps
*  otherwise will direct to http://www.google.com/maps
*
* @param string $fncLatitude
* @param string $fncLongitude
* @uses class Mobile_Detect
* @return html for link to map
*/
function wtkMapLink($fncLatitude, $fncLongitude) {
    // based on passed in parameters create the optimal link so it works
    // using native mapping if called from iOS, Android or Windows phones and tablets
    $fncDetectDevice = new Mobile_Detect;
    if ($fncDetectDevice->isiOS()):
        $fncResult = '"http://maps.apple.com/?z=20&q='. $fncLatitude . ',' . $fncLongitude . '"';
    else:   // Not $fncDetectDevice->isiOS()
        if ($fncDetectDevice->isAndroidOS()):
            $fncResult = '"https://google.com/maps/?q='. $fncLatitude . ',' . $fncLongitude . '&z=20"';
        else:   // Not $fncDetectDevice->isAndroidOS()
            $fncResult = '"http://www.google.com/maps/?q=' . $fncLatitude . ',' . $fncLongitude . '&z=20" target="_blank"';
        endif;  // $fncDetectDevice->isAndroidOS()
    endif;  // $fncDetectDevice->isiOS()

    $fncResult = '<a href=' . $fncResult . '><i class="fa fa-map-marker fa-lg"></i></a>';
    return $fncResult;
}  // end of wtkMapLink
?>
