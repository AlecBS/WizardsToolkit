<?php
/**
* This contains Wizard's Toolkit functions and setup for time-drop lists
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
 * create <option> values for choosing time in a drop list
 *
 * Has value in military time format like 13:45 and displays as 1:45 PM
 *
 * @param array $fncTimeArray
 * @param string $fncCurrHour defaults to 00:00 but can pass in earliest time you want to start
 * @global string $fncTomorrow defaults to blank; if blank and $pgSchedDate is today then start time values at $fncCurrHour
 *
 * @return html of <option> list
 */
// function wtkHourDropList($fncTimeArray) {
function wtkHourDropList($fncTimeArray, $fncCurrHour = '00:00', $fncTomorrow = '') {
    // Create HTML <select drop list of times available in <option> tags
    global $pgSchedDate;
    $fncStartHr = '00:00';
    if ($fncTomorrow == ''):
        if ($pgSchedDate == date('Y-m-d')):
            $fncStartHr = $fncCurrHour;
        endif;  // $pgSchedDate == date('Y-m-d')
    endif;  // $fncTomorrow == ''
    $fncAfterMidnight = false;
    $fncND = '';
    $fncHtm = '';
	foreach ($fncTimeArray as $fncHourMin): // take passed array of hours and build drop list options
        $fncHour = substr($fncHourMin, 0, 2);
        $fncShowHour = $fncHour ;
        if ($fncHourMin == '00:00'): //crossed the midnight mark for the next day.
            $fncND = 'ND';
		endif;
		if ($fncHour < 12):
            $fncAmPm = 'AM';
            $fncShowHour = ($fncHour + 5);
            $fncShowHour = ($fncShowHour - 5); // to get rid of starting zero
        else:
            $fncAmPm = 'PM';
            if ($fncHour > 12):
                $fncShowHour = ($fncHour - 12);
            endif;  // $fncHour > 12
        endif;  // ($fncAmPm == 'PM') && ($fncHour < 12)
        if ($fncAfterMidnight == false):
            if ($fncStartHr <= $fncHourMin):
                $fncHtm .= '   <option value="' . $fncHourMin . $fncND . '">' . $fncShowHour . ':' . substr($fncHourMin, 3, 2) . ' ' . $fncAmPm . '</option>' . "\n";
            endif;  // $fncStartHr <= $fncHourMin
        endif;  // $fncAfterMidnight == false
    endforeach;
    return $fncHtm ;
}  // end of wtkHourDropList

if (!isset($pgSchedDate) || ($pgSchedDate == '')):
    $pgSchedDate = wtkGetParam('schedDate');
    if ($pgSchedDate == ''):
        $pgSchedDate = date('Y-m-d');
    else:
        // I moved within ELSE statement and removed strtolower
        // Begin Make the date URL encoded and in m/d/Y format as per the calendar
        $pgSchedDate = urldecode($pgSchedDate);
        $pgSchedDate = date('Y-m-d', strtotime($pgSchedDate));
        // $pgSchedDate = strtolower(date('Y-m-d', strtotime($pgSchedDate)));
        // End Make the date URL encoded and in m/d/Y format as per the calendar
    endif;  // $pgSchedDate == ''
endif;  // !isset($pgSchedDate)

if (!isset($pgDayOfWeek)):  // if parent page received $pgDayOfWeek request from AJAX then use that, else use today
    $pgDayOfWeek = strtolower(date('D', strtotime($pgSchedDate)));
endif;  // !isset($pgDayOfWeek)

/*
$pgTimeArray = array();
function wtkAddTimeOption($fncHour, $fncMinute, $fncLastTime){
    $fncTimeValue = str_pad($fncHour,2,'0', STR_PAD_LEFT) . ':' . $fncMinute;
    if (($fncLastTime == 'ok') || ($fncTimeValue < $fncLastTime)):
        global $pgTimeArray;
        $pgTimeArray[] = $fncTimeValue;
    endif;
} // wtkAddTimeOption
*/
?>
