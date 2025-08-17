<?php
/**
* This contain all functions that do not fit into other Wizard's Toolkit libraries.
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
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official website: https://wizardstoolkit.com
* @version     2.0
*/

if (!isset($gloTrackTime)){$gloTrackTime=false;}
$gloTimeTrackCntr  = 0;
$gloTimeTrackArray = array();
/**
* Time Track is a debug method
*
* This debug method allows displaying debug information and then by changing
* the $gloTrackTime flag all debug will stop displaying. It also shows exact time
* for each call so you can check and debug speed issues.
*
* <code>
* $gloTrackTime = true;  // defaults to false; set to true to enable debug mode<br><br>
* wtkTimeTrack('just after some code');
* </code>
*
* @param string $fncLocation
* @global string $gloTimeTrackCntr
* @global array  $gloTimeTrackArray
* @return null
*/
function wtkTimeTrack($fncLocation){
    global $gloTrackTime, $gloConnected;
    if ($gloTrackTime):
        global $gloTimeTrackCntr, $gloTimeTrackArray;
        $gloTimeTrackCntr += 1;
        $fncMicroTime = microtime(true);
        $fncTimeArray = explode('.',$fncMicroTime);
        $fncRegTime = $fncTimeArray[0];
        $fncMilliseconds = $fncTimeArray[1];
        $fncTime = date('H:i:s', $fncRegTime) . ".$fncMilliseconds";
        $gloTimeTrackArray[$gloTimeTrackCntr][1] = $fncTime;
        $gloTimeTrackArray[$gloTimeTrackCntr][2] = $fncLocation;
        if ($gloConnected):
            if (is_array($fncLocation) || is_object($fncLocation)):
                $fncLocation = json_encode($fncLocation);
            endif;
            if (strlen($fncLocation) > 240):
                $fncLocation = substr($fncLocation, 0, 240);
            endif;
            $fncFilter = array('DevNote' => "$fncTime: $fncLocation");
            wtkSqlExec('INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)', $fncFilter, false);
        endif;
    endif;
} // wtkTimeTrack

/**
* Show Time Tracks
*
* This shows everything that was sent to wtkTimeTrack plus all internal Wizard's Toolkit debug logging.
* It is automatically called at end of each WTK library PHP page but will not show anything
* unless $gloTrackTime = true.  You can also call this earlier for debugging if necessary.
*
* @global string $gloTimeTrack
* @global array  $gloTimeTrackArray
* @return echoes all time tracks
*/
function wtkShowTimeTracks(){
    wtkTimeTrack('Top of ShowTimeTracks');
    global $gloTrackTime, $gloTimeTrackArray;
    if ($gloTrackTime):
        echo "\n\n<!--  \n Time tracking Wizard's Toolkit page \n \n";
        for ($i = 1; $i < (sizeof($gloTimeTrackArray) + 1); ++$i):
            echo $gloTimeTrackArray[$i][1] . '  -  ' . $gloTimeTrackArray[$i][2] . "\n";
        endfor; // $i = 1; $i < (sizeof($gloTimeTrackArray) + 1); ++$i
        $fncMicroTime = microtime(true);
        $fncTimeArray = explode('.',$fncMicroTime);
        $fncRegTime = $fncTimeArray[0];
        $fncMilliseconds = $fncTimeArray[1];
        $fncTime = date('H:i:s', $fncRegTime) . ".$fncMilliseconds";
        echo $fncTime . "  -  Finished ShowTimeTracks \n";
        echo "\n--> \n";
    endif; //$gloTrackTime
} // wtkShowTimeTracks()

/**
* This takes passed HTML and writes it to /tst/ folder for HTML/CSS debugging
*
* The generated HTML files can be given to UI/HTML/CSS developers so they can
* work on the UI without needing PHP nor SQL skills.  This should be at bottom of your page.
*
* @param string $fncHtm
* @return null
*/
function wtkProtoType($fncHtm){
    global $gloPrototype;
    if ($gloPrototype == 'M'):
        $fncPage = $_SERVER['PHP_SELF'];
        $fncPageArray = pathinfo($fncPage);
        $fncFileName = $fncPageArray['filename'];
        $fncFile = fopen(_RootPATH . 'tst/' . $fncFileName . '.htm', 'w');
        fwrite($fncFile, $fncHtm);
        fclose($fncFile);
    endif;
} // wtkProtoType

$pgHistoryUID = 0;
/**
* This function saves to User History how long it took to generate page.
*
* Data is saved to `wtkUserHistory` table.
* This is called via wtkBuildDataBrowse, wtkFormWriteUpdField and wtkMergePage.  If it is called more
* than once it only updates the `SecondsTaken` instead of making a new data row.
*
* @param string $fncPageTitle optionally pass Page Title
* @return null
*/
function wtkAddUserHistory($fncPageTitle = ''){
    global $gloSkipConnect, $gloId, $gloRNG, $gloUserUID, $gloPageTitle,
        $gloCurrentPage, $gloPageStart, $pgHistoryUID, $gloConnType;
    if ($gloSkipConnect != 'Y'):
        $fncPageTime = round(microtime(true) - $gloPageStart,3);
        if ($pgHistoryUID == 0):
            $fncOtherId = 'NULL';
            if (is_numeric($gloId)):
                $fncOtherId = $gloId;
                if (is_numeric($gloRNG)):
                    if (($gloId == 0) && ($gloRNG != 0)):
                        $fncOtherId = $gloRNG;
                    endif;
                endif;
            elseif (is_numeric($gloRNG)):
                $fncOtherId = $gloRNG;
            endif;
            if ($fncPageTitle == ''):
                $fncPageTitle = $gloPageTitle;
            endif;
            if (!isset($gloUserUID) || ($gloUserUID == '')):
                $gloUserUID = 0;
            endif;
            if ($gloUserUID == 0):
                $fncUserUID = 'NULL';
            else:
                $fncUserUID = $gloUserUID;
            endif;
            $fncSQL  = 'INSERT INTO `wtkUserHistory` (`UserUID`, `OtherUID`, `PageTitle`, `PageURL`, `SecondsTaken`)';
            $gloCurrentPage = trim(substr($gloCurrentPage,0,150));
            if ($gloConnType == 'ADO'):
                $fncPageTitle = wtkEscapeStringForDB($fncPageTitle);
                $fncSQL .= ' VALUES (' . $fncUserUID . ',' . $fncOtherId . ",'" . $fncPageTitle . "','" . $gloCurrentPage . "', $fncPageTime)";
                wtkSqlExec($fncSQL);
                if ($fncUserUID == 'NULL'):
                    $pgHistoryUID = wtkSqlGetOneResult("SELECT `UID` FROM `wtkUserHistory` WHERE `UserUID` IS NULL ORDER BY `UID` DESC LIMIT 1");
                else:
                    $pgHistoryUID = wtkSqlGetOneResult("SELECT `UID` FROM `wtkUserHistory` WHERE `UserUID` = $gloUserUID ORDER BY `UID` DESC LIMIT 1");
                endif;
            else: // PDO
                $fncSQL .= ' VALUES (:UserUID, :OtherUID, :PageTitle, :PageURL, :SecondsTaken )';
                $fncFilter = array (
                    'UserUID' => $fncUserUID,
                    'OtherUID' => $fncOtherId,
                    'PageTitle' => $fncPageTitle,
                    'PageURL' => $gloCurrentPage,
                    'SecondsTaken' => $fncPageTime
                );
                wtkSqlExec($fncSQL, $fncFilter);
                if ($fncUserUID == 'NULL'):
                    $pgHistoryUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkUserHistory` WHERE `UserUID` IS NULL ORDER BY `UID` DESC LIMIT 1', []);
                else:
                    $pgHistoryUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkUserHistory` WHERE `UserUID` = ? ORDER BY `UID` DESC LIMIT 1', [$gloUserUID]);
                endif;
            endif; // PDO or ADO
        else: // History already saved
            if ($gloConnType == 'ADO'):
                wtkSqlExec("UPDATE `wtkUserHistory` SET `SecondsTaken` = $fncPageTime WHERE `UID` = $pgHistoryUID");
            else: // PDO
                $fncSQL = 'UPDATE `wtkUserHistory` SET `SecondsTaken` = :SecondsTaken WHERE `UID` = :UID';
                $fncFilter = array (
                    'UID' => $pgHistoryUID,
                    'SecondsTaken' => $fncPageTime
                );
                wtkSqlExec($fncSQL, $fncFilter);
            endif; // PDO or ADO
        endif; // not $pgHistoryUID
    endif; // $gloSkipConnect != 'Y'
} // wtkAddUserHistory

/**
* Easy error logging
*
* Pass in Error Type and Error Message and this inserts that plus additional information into the wtkErrorLog table.
* This includes logged-in user, Current Page, and Referer Page in addition to the Type and Message passed.
* Several functions in Wizard's Toolkit use this so check your wtkErrorLog table any time you have problems.
*
* @param string $fncErrType
* @param string $fncErrMsg
* @param string $fncLineNum defaults to NULL
* @global string $gloUserUID
* @return null
*/
function wtkLogError($fncErrType, $fncErrMsg, $fncLineNum = 'NULL') {
    global $gloConnected, $gloUserUID, $gloCurrentPage;
    if ($gloConnected == false):
        wtkConnectToDB();
    endif;
    if (!isset($gloUserUID) || ($gloUserUID == '')):
        $gloUserUID = 0;
    endif;
    if ($gloUserUID == 0):
        $fncUserUID = 'NULL';
    else:
        $fncUserUID = $gloUserUID;
    endif;
    $fncReferPage = wtkGetServer('HTTP_REFERER');
    if ($fncReferPage != ''):
        $fncReferPage = trim(substr($fncReferPage, 0, 120));
        $fncReferPage = wtkReplace(trim(substr($fncReferPage, 0, 120)), "'", "''");
    else:   // Not $fncReferPage != ''
        $fncReferPage = 'NULL';
    endif;  // $fncReferPage != ''
    if ($fncLineNum == 'NULL'):
        $fncLineNum = null;
    endif;

    $fncErrType = wtkReplace(trim(substr($fncErrType, 0, 20)), "'", "''");
    // since text type remove 200 char limit
    $fncErrMsg = wtkReplace(trim(substr($fncErrMsg, 0, 2000)), "'", "''");
    $fncCurrentPage = wtkReplace(trim(substr($gloCurrentPage, 0, 120)), "'", "''");
    $fncSQL =<<<SQLVAR
INSERT INTO `wtkErrorLog` (`UserUID`, `ErrType`, `ErrMsg`, `FromPage`, `ReferralPage`, `LineNum`)
 VALUES (:UserUID, :ErrType, :ErrMsg, :CurrentPage, :ReferPage, :LineNum)
SQLVAR;
    $fncFilter = array (
        'UserUID' => $fncUserUID,
        'ErrType' => isset($fncErrType) ? $fncErrType : null,
        'ErrMsg' =>  isset($fncErrMsg) ? $fncErrMsg : null,
        'CurrentPage' => isset($fncCurrentPage) ? $fncCurrentPage : null,
        'ReferPage' => isset($fncReferPage) ? $fncReferPage : null,
    	'LineNum' => isset($fncLineNum) ? $fncLineNum : null
    );
    wtkSqlExec($fncSQL, $fncFilter);
}  // end of wtkLogError

/**
* If there is a failed attack on server it will save information into wtkFailedAttempts table
*
* Pass in 4-character code that we want to log this failed attempt for.
* This includes logged-in user, IP Address, Browser Type, Current Page,
* and Referer Page in addition to the Message passed.
*
* @param string $fncFailCode 4-character code so you can easily count types of failures
* @param string $fncFailNote optionally mention what caused failure
* @global string $gloConnected
* @global string $gloUserUID
* @global string $gloCurrentPage
* @global string $gloWebBaseURL useful if you have multiple domains saving to same database
* @return null
*/
function wtkInsFailedAttempt($fncFailCode, $fncFailNote = '') {
    global $gloConnected, $gloUserUID, $gloCurrentPage, $gloWebBaseURL;
    if (strlen($fncFailCode) > 4):
        $fncFailCode = substr($fncFailCode, 0, 4);
    endif;
    if ($gloConnected == false):
        wtkConnectToDB();
    endif;
    if ($gloUserUID == ''):
        $gloUserUID = 0;
    endif;
    if ($gloUserUID == 0):
        $fncUserUID = 'NULL';
    else:
        $fncUserUID = $gloUserUID;
    endif;
    $fncIPaddress = wtkGetIPaddress();
    $fncBrowserArray = safe_get_browser();
    if ($fncBrowserArray == ''):
        $fncPlatform = 'NULL';
        $fncBrowser = 'NULL';
        $fncBrowserVer = 'NULL';
    else:
        $fncPlatform = isset($fncBrowserArray['platform']) ? substr($fncBrowserArray['platform'], 0, 25) : NULL;
        $fncBrowser = isset($fncBrowserArray['browser']) ? substr($fncBrowserArray['browser'], 0, 20) : NULL;
        $fncBrowserVer = isset($fncBrowserArray['version']) ? substr($fncBrowserArray['version'], 0, 12) : NULL;
    endif;
    if ($fncFailNote != ''):
        $fncFailNote .= '; ';
    endif;
    $fncFailNote .= $gloCurrentPage;
    if ($fncFailNote != ''):
        $fncFailNote .= '; ';
    endif;
    $fncFailNote .= $gloWebBaseURL;
    $fncFailNote = wtkReplace(trim(substr($fncFailNote, 0, 250)), "'", "''");
    $fncSQL  = 'INSERT INTO `wtkFailedAttempts` (`FailCode`, `UserUID`, `IPaddress`, `OpSystem`, `Browser`, `BrowserVer`, `FailNote`)';
    $fncSQL .= ' VALUES (:FailCode, :UserUID, :IPaddress, :OpSystem, :Browser, :BrowserVer, :FailNote )';
    $fncSqlFilter = array(
        'FailCode' => $fncFailCode,
        'UserUID' => $fncUserUID,
        'IPaddress' => $fncIPaddress,
        'OpSystem' => $fncPlatform,
        'Browser' => $fncBrowser,
        'BrowserVer' => $fncBrowserVer,
        'FailNote' => $fncFailNote
    );
    wtkSqlExec(wtkSqlPrep($fncSQL), $fncSqlFilter);
}  // end of wtkInsFailedAttempt

// BEGIN  Error and Exception processing
/**
* Custom Error Handling
*
* This is called from wtkCheckForFatal.
* This calls wtkExceptionHandler to show the error using the client's GUI templates.
*
* @param string $fncNum
* @return null
*/
function wtkErrorHandler( $fncNum, $fncStr, $fncFile, $fncLine, $fncContext = null ) {
    wtkExceptionHandler( new ErrorException( $fncStr, 0, $fncNum, $fncFile, $fncLine ) );
} // end of wtkErrorHandler

/**
* Custom Exception Handling
*
* Uncaught exception handler set using set_exception_handler.
* This shows the error using the client's GUI templates after saving the error to the wtkErrorLog table.
*
* @param string Exception $fncErr
* @return html with error information; if user's SecurityLevel is > 94 then shows additional error->getMessage()
*/
//function wtkExceptionHandler( Exception $fncErr ) {
function wtkExceptionHandler( $fncErr ) {
    global $gloSkipConnect;
    //  print_r($fncErr);
    if ($gloSkipConnect != 'Y'):
        wtkLogError(get_class($fncErr), $fncErr->getMessage(), $fncErr->getLine());
    endif;
    global $gloUserSecLevel, $gloShowPrint, $gloSiteDesign, $gloTrackTime;
    if ($gloUserSecLevel < 95):
        $fncDev = '';
    else: // Developer Level login
        $fncDev = '<tr><th>Message</th><td>' . wtkRemoveStyle($fncErr->getMessage()) . '</td></tr>' . "\n";
        // Above is saved to wtkErrorLog so do not show user the SQL error
    endif;
    $fncErrClass = get_class($fncErr);
    $fncErrFile  = $fncErr->getFile();
    $fncErrLine  = $fncErr->getLine();
    $fncErrMessage  = $fncErr->getMessage();
    $fncErrTrace  = $fncErr->getTraceAsString();
    $fncHtm =<<<htmVAR
<div class="row">
  <div class="col m8 offset-m2 s12">
    <p><a href="JavaScript:wtkGoBack();">Return to prior page</a>.</p>
    <div class="card">
        <div class="card-content">
             <span class="card-title red-text">Exception Occurred</span>
             <table>
                <tbody>
                    <tr><th style="width: 80px;">Type</th><td>$fncErrClass</td></tr>
                    <tr><th>File</th><td>$fncErrFile</td></tr>
                    <tr><th>Line</th><td>$fncErrLine</td></tr>
                    <tr><th>Message</th><td>$fncErrMessage</td></tr>
                    <tr><th>Trace</th><td>$fncErrTrace</td></tr>
                    $fncDev
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>
htmVAR;
    if ($gloSiteDesign == 'SPA'):
        echo $fncHtm;  // SPA Method
        wtkShowTimeTracks();
        exit;
    else: // MPA Method
        $gloShowPrint = false;
        if (function_exists('wtkMergePage')):
            wtkSearchReplace('"col m4 offset-m4 s12"', '"col s12"');
            wtkMergePage($fncHtm, 'Error', _WTK_RootPATH . '/htm/minibox.htm');
        else:
            echo $fncHtm;  // called before Html.php included
        endif;
    endif;
} // end of wtkExceptionHandler

/**
* Trap fatal errors
*
* This captures fatal errors and calls wtkExceptionHandler to display.  This prevents White Screens with no error messaging.
* This uses $fncError = error_get_last() then if 'type' >= 256 then calls wtkErrorHandler and wtkExceptionHandler.
*
* Checks for a fatal error, work around for set_error_handler not working on fatal errors.
* This is set using register_shutdown_function.
*
* @return null
*/
function wtkCheckForFatal() {
    $fncError = error_get_last();
    if (!is_null($fncError)):
        if ($fncError['type'] >= 256): // E_USER_ERROR
            wtkErrorHandler( $fncError['type'], $fncError['message'], $fncError['file'], $fncError['line'] );
            wtkExceptionHandler( new ErrorException( $fncError['message'], 0, $fncError['type'], $fncError['file'], $fncError['line'] ) );
        endif;  // $fncError['type'] >= E_USER_ERROR
    endif;
} // end of wtkCheckForFatal

// if ($pgDebug != 'Y'):
    register_shutdown_function('wtkCheckForFatal');
    set_error_handler('wtkErrorHandler');         // commented out the undeclared var does not cause problem
    set_exception_handler('wtkExceptionHandler'); // 2ENHANCE with Throwable
// endif;

/**
* Set Cookie
*
* For the 'Expires' parameter you can pass in any of these: 1day, 1week, 1month, 1year.
* If you pass nothing the cookie expires at end of session.
*
* @param string $fncCookieName
* @param string $fncValue
* @param string $fncExpires Defaults to "0". Switch cases: "1day, 1week, 1month, 1year".
*    If numeric value is passed it is added to time() for when to expire.
* @param string $fncPath Defaults to "/"
* @return null
*/
function wtkSetCookie($fncCookieName, $fncValue, $fncExpires = 0, $fncPath = '/') {
    if ($fncCookieName != ''): // make sure cookie name is given
        if ($fncValue == ''):
            $fncExpires = -1;
        endif;
        // attempt to get expire date. If none, pass 0 for expire at browser close.
        if (is_numeric($fncExpires)):
            if ($fncExpires > 0):
                $fncExpires = time() + $fncExpires;
            elseif ($fncExpires < 0):
                $fncExpires = 1;  // way in the past
            endif;
        else:
            switch ($fncExpires):
                case '1day':
                    $fncExpires = time() + (60*60*24); // 60 seconds * 60 mins * 24 hours
                    break;
                case '1week':
                    $fncExpires = time() + (60*60*24*7);
                    break;
                case '1month':
                    $fncExpires = time() + (60*60*24*30);
                    break;
                case '1year':
                    $fncExpires = time() + (60*60*24*365);
                    break;
                default:
                    $fncExpires = 0;
            endswitch;
        endif;

        if (PHP_VERSION_ID < 70300):
            setcookie($fncCookieName, $fncValue, $fncExpires, $fncPath . '; samesite=strict');
        else:
            setcookie($fncCookieName, $fncValue, [
                'expires' => $fncExpires,
                'path' => $fncPath,
                'samesite' => 'strict'
            ]);
            /*
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            */
        endif;
    endif; // not empty cookie name
} // wtkSetCookie

/**
* Get Cookie - returns either the cookie value or a blank '' value
*
* @param string $fncCookieName
* @return string
*/
function wtkGetCookie($fncCookieName) {
    return isset($_COOKIE[$fncCookieName]) ? $_COOKIE[$fncCookieName] : '';
} // finish function wtkGetCookie

/**
* Delete Cookie
*
* @param string $fncCookieName
* @return null
*/
function wtkDeleteCookie($fncCookieName) {
    wtkSetCookie($fncCookieName, '', -1);
} // finish function wtkDeleteCookie

/**
* Changes Currency into simple Number
*
* For example will change '$12,345.67' into 12345.67
* This will also remove '%'
*
* @param string $fncVal
* @return number from passed currency value
*/
function wtkParseCurrencyToNumber($fncVal = '$0') {
    $fncResult = '';
    if ($fncVal == ''|| is_null($fncVal)):
        $fncResult = '0';
    else:
        $fncVal = wtkReplace($fncVal,'$','');
        $fncVal = wtkReplace($fncVal,'&pound;','');
        $fncVal = wtkReplace($fncVal,'&euro;','');
        $fncVal = wtkReplace($fncVal,'%','');
        $fncArray = array();
        $fncArray = explode(',', $fncVal);
        foreach ($fncArray as $fncArrayVal):
           $fncResult .= $fncArrayVal;
        endforeach;
     endif;
     return $fncResult;
}  // end of wtkParseCurrencyToNumber

/**
* Redirect to passed URL regardless of whether headers have been sent or not
*
* To prevent redirection errors this will change go-to link to use https: or http:
* based on what current web page user is on.  Redirects as a 301 but if pass 'N'
* as second parameter then redirects as 302.
*
* @param string $fncURL
* @param string $fncPermanent defaults to 'N'
* @return null
*/
function wtkRedirect($fncURL, $fncPermanent = 'N') {
    wtkDisconnectToDB();
    $fncURL = wtkConvertLinks($fncURL);
    if (!headers_sent()):
        if ($fncPermanent == 'Y'):
            header("HTTP/1.1 301 Moved Permanently");
        else:
            header("HTTP/1.1 302 Found");
        endif;
        header('Location: ' . $fncURL);
    else:
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $fncURL . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $fncURL . '" />';
        echo '</noscript>';
    endif;
    exit;
}  // end of wtkRedirect

/**
* This will remove HTML encoding from the value passed in.
*
* @param string $fncThisValue
* @return string
*/
function wtkRemoveStyle( $fncThisValue ) {
    if (!empty($fncThisValue) && ($fncThisValue != '')):
        $fncThisValue = wtkReplace($fncThisValue, '<br>', ' ');
        $fncThisValue = wtkReplace($fncThisValue, '<br/>', ' ');
        $fncThisValue = wtkReplace($fncThisValue, '<br />', ' ');
        $fncThisValue = wtkReplace($fncThisValue, '&nbsp;', ' ');
        $fncThisValue = wtkReplace($fncThisValue, '&lsquo;', "'");
        $fncThisValue = wtkReplace($fncThisValue, '&rsquo;', "'");
        $fncThisValue = wtkReplace($fncThisValue, '&ldquo;', '"');
        $fncThisValue = wtkReplace($fncThisValue, '&rdquo;', '"');
        $fncThisValue = wtkReplace($fncThisValue, '&apos;', "'");
        $fncThisValue = wtkReplace($fncThisValue, '&mdash;', '-');
        while (true):
            $fncTag = '';
            $fncPos = strpos($fncThisValue, '</');
            if (strpos($fncThisValue, '</') === false):
                return $fncThisValue;
            else:
                $fncPosE = strpos($fncThisValue, '>', $fncPos);
                if ($fncPosE === false):
                    return $fncThisValue;
                else:
                    $fncTag = substr($fncThisValue, $fncPos+2, $fncPosE-($fncPos+2)) ;
                    $fncThisValue = substr($fncThisValue, 0, $fncPos) . substr($fncThisValue, $fncPosE+1);
                    $fncPos = strpos($fncThisValue, '<'.$fncTag);
                    if ($fncPos === false):
                        return $fncThisValue;
                    else:
                        $fncPosE = strpos($fncThisValue, '>',$fncPos);
                        if ($fncPosE === false):
                            return $fncThisValue;
                        else:
                            $fncThisValue = substr($fncThisValue, 0, $fncPos) . substr($fncThisValue, $fncPosE+1);
                        endif;
                    endif;
                endif;
            endif;
        endwhile; // true
    endif; // not empty
} // wtkRemoveStyle()

/**
* Load and return include file contents
*
* @param string $fncIncludeFile
* @return file contents
*/
function wtkLoadInclude($fncIncludeFile){
    $fncFileText = '';
    if (file_exists($fncIncludeFile)):
        $fncObjFile = fopen($fncIncludeFile, 'r');
        if ($fncObjFile):
            $fncFileText = fread($fncObjFile, filesize($fncIncludeFile));
            fclose ($fncObjFile);
        endif;
    else:
        echo $fncIncludeFile . ' file does not exist. Contact developers.';
        exit;
    endif;
    return($fncFileText);
} // wtkLoadInclude

/**
* Similar to str_replace except it replaces values within arrays.
*
* Case sensitive search and replace. It does not affect the passed-in value to be searched.
* The parameters are in a more intuitive order.
* It returns the result after replacing all matches even in recursive arrays.  For arrays it replaces values, not keys.
* If array is passed in, array is returned.  If a string is passed in, a string is returned.
*
* Example:
* <code>$pgNewStr = wtkReplace($Subject, $SearchFor, $ReplaceWith);</code>
*
* @param string $fncSubject
* @param string $fncSearchFor
* @param string $fncReplaceWith
* @return string or array depending on what was passed in
*/
function wtkReplace($fncSubject, $fncSearchFor, $fncReplaceWith) {
    $fncResult = $fncSubject;
    if (!empty($fncSubject) && !empty($fncSearchFor)):
        if (is_array($fncResult)):
            foreach ($fncResult as &$fncInnerArray):
                $fncInnerArray = wtkReplace($fncInnerArray, $fncSearchFor, $fncReplaceWith);
            endforeach;
            unset($fncInnerArray);
        else:   // Not is_array($fncResult)
            if (isset($fncReplaceWith)):
                $fncResult = str_replace($fncSearchFor, $fncReplaceWith, $fncSubject);
            endif;
        endif;  // is_array($fncResult)
    endif;
    return $fncResult;
} // wtkReplace

/**
* Get Environ
*
* @param string $fncEnvVariable
* @return string Returns a variable from ENV or '' if no value
*/
function wtkGetEnviron($fncEnvVariable) {
    return isset($_ENV[$fncEnvVariable]) ? $_ENV[$fncEnvVariable] : '';
} // end of wtkGetEnviron

/**
* Returns GET if it exists, otherwise returns POSTed value.
*
* wtkGetParam returns default if otherwise would return ''.
*
* @param string $fncParameter
* @param string $fncDefault which defaults to '' blank
* @return string
*/
function wtkGetParam($fncParameter, $fncDefault = '') {
    $fncResult = (wtkGetGet($fncParameter) != '') ? wtkGetGet($fncParameter) : wtkGetPost($fncParameter);
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult;
} // end of wtkGetParam

/**
* Get $_GET[] of passed parameter.
*
* If not blank, returns stripslashes(urldecode($_GET[$fncParameter])).
* If result is blank, then returns $fncDefault.
*
* @param string $fncParameter
* @param string $fncDefault which defaults to '' blank
* @return string
*/
function wtkGetGet($fncParameter, $fncDefault = '') {
    $fncResult = isset($_GET[$fncParameter]) ? stripslashes(urldecode($_GET[$fncParameter])) : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult;
} // end of wtkGetGet

/**
* Get $_POST[]
*
* If result is blank, then returns $fncDefault.
*
* @param string $fncParameter
* @param string $fncDefault which defaults to '' blank
* @return string
*/
function wtkGetPost($fncParameter, $fncDefault = '') {
    $fncResult = isset($_POST[$fncParameter]) ? $_POST[$fncParameter] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;
    return $fncResult;
} // end of wtkGetPost

/**
* Get $_SERVER[]
*
* @param string $fncServVariable
* @param string $fncDefault which defaults to '' blank
* @return string returns a variable from SERVER or if no value returns default
*/
function wtkGetServer($fncParameter, $fncDefault = '') {
    $fncResult = isset($_SERVER[$fncParameter]) ? $_SERVER[$fncParameter] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;
    return $fncResult;
} // end of wtkGetServer

/**
* Set $_SESSION variable.
*
* If setting 'UserLevel' then to prevent other sites on same server from sharing UserLevel it prepends $gloAuthStatus
*
* @param string $fncSessVariable
* @global string $gloAuthStatus
* @param string $fncValue
* @return null
*/
function wtkSetSession($fncSessVariable, $fncValue) {
    if ($fncSessVariable == 'UserLevel'):  // to prevent other sites from sharing UserLevel
        global $gloAuthStatus;
        $fncSessVariable = $gloAuthStatus . $fncSessVariable;
    endif;
    $_SESSION[$fncSessVariable] = $fncValue;
} // end of wtkSetSession

/**
* Get $_SESSION value or '' blank if not set.
*
* If retrieving 'UserLevel' then to prevent other sites on same server from sharing UserLevel it prepends $gloAuthStatus
*
* @param string $fncSessVariable
* @global string $gloAuthStatus
* @return string returns a variable from session or '' if no value
*/
function wtkGetSession($fncSessVariable) {
    if ($fncSessVariable == 'UserLevel'):  // to prevent other sites from sharing UserLevel
        global $gloAuthStatus;
        $fncSessVariable = $gloAuthStatus . $fncSessVariable;
    endif;
    return isset($_SESSION[$fncSessVariable]) ? $_SESSION[$fncSessVariable] : '';
} // end of wtkGetSession

/**
* unset($_SESSION[$fncSessVariable])
*
* If retrieving 'UserLevel' then to prevent other sites on same server from sharing UserLevel it prepends $gloAuthStatus
*
* @param string $fncSessVariable
* @global string $gloAuthStatus
* @return null
*/
function wtkDeleteSession($fncSessVariable) {
    if ($fncSessVariable == 'UserLevel'):  // to prevent other sites from sharing UserLevel
        global $gloAuthStatus;
        $fncSessVariable = $gloAuthStatus . $fncSessVariable;
    endif;
    unset($_SESSION[$fncSessVariable]);
} // end of wtkGetSession

/**
* Format passed number as a percent.
*
* This assumes number needs to be multiplied by 100.  Only first parameter of number is required.
*
* @param string $fncValue
* @param numeric $fncRoundDigits Defaults to '2'
* @param string $fncGroupSep Defaults to '' blank
* @param string $fncDecimalSep Defaults to '.'
* @param string $fncPercentChar Defaults to 'after' to determine whether % should be before or after value
* @return value with percentage using above passed parameters
*/
function wtkFormatPercent($fncValue, $fncRoundDigits = 2, $fncGroupSep = '', $fncDecimalSep = '.', $fncPercentChar = 'after') {
    if (!$fncValue == ''):
        $fncValue = number_format($fncValue * 100, $fncRoundDigits, $fncDecimalSep, $fncGroupSep);
        if ($fncPercentChar == 'after'):
            $fncValue .= '%';
        elseif ($fncPercentChar == 'before'):
            $fncValue = '%'. $fncValue;
        endif;
    endif;
    return $fncValue;
} // end of wtkFormatPercent

/**
* Format passed number as currency.
*
* This assumes number needs to be multiplied by 100.  Only first parameter of number is required.
*
* @param string $fncValue
* @param numeric $fncRoundDigits Defaults to '2'
* @param string $fncMoneyChar Defaults to '$'
* @param string $fncGroupSep Defaults to blank
* @param string $fncDecimalep Defaults to '.'
*/
function wtkFormatCurrency($fncValue, $fncRoundDigits = 2, $fncMoneyChar = '$', $fncGroupSep = ',', $fncDecimalSep = '.') {
    if (!trim($fncMoneyChar) == ''):
        return $fncMoneyChar . number_format(round($fncValue, $fncRoundDigits),$fncRoundDigits,$fncDecimalSep,$fncGroupSep);
    else:
        return number_format(round($fncValue, $fncRoundDigits), $fncRoundDigits, $fncDecimalSep, $fncGroupSep);
    endif;
} // end of wtkFormatCurrency

/**
* Format passed value for Date and Time
*
* Returns '' if either parameter is null, blank, '0000-00-00', or '00/00/0000'
* otherwise sets date format via: <code>date($fncFormat, strtotime($fncVal))</code>
*
* @param string $fncFormat
* @param numeric $fncVal Defaults to blank
* @return formatted date or ''
*/
function wtkFormatDateTime($fncFormat, $fncVal = '') {
    if (!$fncFormat || $fncVal == ''|| is_null($fncVal)):
        return '';
    else:
        if ($fncVal == '0000-00-00' || $fncVal == '00/00/0000'):
            return '';
        else:   // Not $fncVal == '0000-00-00' || $fncVal == '00/00/0000'
            if (strtotime($fncVal) > -1):
                return date($fncFormat, strtotime($fncVal));
            else:
                return $fncVal;
            endif;
        endif;  // $fncVal == '0000-00-00' || $fncVal == '00/00/0000'
    endif;
} // end of wtkFormatDateTime

/**
* HTML Encode using htmlentities($fncString, ENT_QUOTES)
*
* @param string $fncString
* @return encoded HTML
*/
function wtkHtmlEncode($fncString) {
    return htmlentities($fncString, ENT_QUOTES);
} // end of htmlEncode

/**
* HTML Decode
*
* @param string $fncString
* @return decoded HTML
*/
function wtkHtmlDecode($fncString) {
    $fncTransTbl = get_html_translation_table(HTML_ENTITIES);
    $fncTransTbl = array_flip($fncTransTbl);
    return strtr($fncString, $fncTransTbl);
} // end of htmlDecode

/**
* Insert Spaces
*
* This converts under_scored_fields into Under Scored Fields
* and it also converts WordCappedSentences into 'Word Capped Sentences'
*
* @param string $fncFieldName
* @return string with spaces before every capital letter
*/
function wtkInsertSpaces($fncFieldName) {
    $fncWordArray = preg_split('/(?<=[a-z])(?=\d)|(?<=\d)(?=[a-z])|_/', $fncFieldName); // Split words at boundaries between letters and digits or underscores
    $fncResult = '';
    foreach ($fncWordArray as &$fncWord) {
        if (!is_numeric($fncWord) && !ctype_upper(preg_replace("/[^[:alnum:]]/", "",$fncWord))) {
            $fncWord = preg_replace('/([A-Z])/', ' $1', $fncWord); // Add space before capital letters
            $fncResult .= $fncWord;
        } else {
            $fncResult .= ' ' . $fncWord;
        }
    }
    $fncFinal = preg_replace('/\s(?=[A-Z])/', ' ', $fncResult);
    $fncFinal = str_replace(' U R L', ' URL', $fncFinal);
    $fncFinal = str_replace('User U I D', 'UserUID', $fncFinal);
    $fncFinal = str_replace(' U I D', ' UID', $fncFinal);
    $fncFinal = str_replace(' I D', ' ID', $fncFinal);
    $fncFinal = str_replace('G B P ', 'GBP ', $fncFinal);
    $fncFinal = str_replace(' S M S', ' SMS', $fncFinal);
    $fncFinal = str_replace('I P Address', 'IP Address', $fncFinal);
    return $fncFinal;
} // wtkInsertSpaces

/**
* wtkTruncate
*
* This is called by BrowsePDO function to truncate text and add ellipsis.  It makes certain to truncate
* at last space so it doesn't break in middle of word.
*
* Here is an example:
* <code>
*    $fncThisValue = wtkTruncate($fncThisValue, 80);
* </code>
*
* @param string  $fncStr  string passed in to possibly truncate
* @param numeric $fncSize defaults to 80; if fncStr is greater than this it will be truncated and an ellipsis appended
* @return string  original value unless too long in which case truncated and ellipsis added
*/
function wtkTruncate($fncStr, $fncSize = 80) {
    $fncStr = trim($fncStr);
    if ($fncSize < strlen($fncStr)):
        $fncPos = strpos($fncStr, ' ', $fncSize-1);
        if ($fncPos === false):
            $fncStr = substr($fncStr, 0, $fncSize);
            $fncPos = strrpos($fncStr, ' ');
            if ($fncPos === false):
                $fncStr = substr($fncStr, 0, $fncSize);
            else:   // Not $fncPos===false
                $fncStr = substr($fncStr, 0, $fncPos);
            endif;  // $fncPos===false
        else:   // Not $fncPos===false
            $fncStr = substr($fncStr, 0, $fncPos);
        endif;  // $fncPos===false
        $fncStr .= '...';
    endif;  // strlen($fncStr) > $fncSize
    return $fncStr;
} // end of wtkTruncate

/**
* wtkGetIPaddress
*
* get IP address of web surfer
*
* @returns IP Address or 'no-IP' if no IP address
*/
function wtkGetIPaddress() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $fncKey):
        if (array_key_exists($fncKey, $_SERVER) === true):
            foreach (explode(',', $_SERVER[$fncKey]) as $fncIP):
                $fncIP = trim($fncIP); // just to be safe
                if (filter_var($fncIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false):
                    return $fncIP;
                endif;
            endforeach;
        endif;  // array_key_exists($fncKey, $_SERVER) === true
    endforeach;
    return 'no-IP'; // ABS 05/21/18
}  // end of wtkGetIPaddress

/**
* Pass in number of seconds and returns value like: <code>3 Days 8 Hours 38 Minutes</code>
*
* @param string $fncSeconds
* @return string like 3 Days 8 Hours 38 Minutes
*/
function wtkSecondsToDays($fncSeconds){
    $fncResult = '';
    if ($fncSeconds >= 86400):
        $fncDays = intval($fncSeconds / 86400);
        $fncSecsInDay = $fncSeconds - ($fncDays * 86400);
        if ($fncDays == 1):
            $fncResult .= $fncDays . ' Day ';
        else:   // Not $fncDays == 1
            $fncResult .= $fncDays . ' Days ';
        endif;  // $fncDays == 1
    else:
        $fncSecsInDay = $fncSeconds;
    endif;  // $fncSeconds >= 86400
    $fncHours = intval($fncSecsInDay / 3600);
    $fncMinutes = (($fncSecsInDay - ($fncHours * 3600)) / 60);
    $fncResult .= $fncHours;
    if ($fncHours == 1):
        $fncResult .= ' Hour';
    else:
        $fncResult .= ' Hours';
    endif;
    $fncResult .= ' ' . intval($fncMinutes);
    if (intval($fncMinutes) == 1):
        $fncResult .= ' Minute';
    else:
        $fncResult .= ' Minutes';
    endif;
    return $fncResult;
} // wtkSecondsToDays

/**
* Checks server's time-zone and if different than passed time-zone, converts time.
*
* Pass in Date-Time and desired TimeZone to receive adjusted Date-Time in preferred format.
* Uses date_default_timezone_get to determine server's time-zone.
*
* @param string $fncDateTime
* @param string $fncTimeZone
* @param string $fncDateFormat - optionally send your preferred PHP date format
* @return string
*/
function wtkTimeZoneAdjust($fncDateTime, $fncTimeZone, $fncDateFormat = '') {
    // Convert common SQL time zones to preferred PHP time zones
    switch ($fncTimeZone):
        case 'US/Eastern' :
            $fncTimeZone = 'America/New_York';
            break;
        case 'US/Pacific' :
            $fncTimeZone = 'America/Los_Angeles';
            break;
        case 'US/Mountain' :
            $fncTimeZone = 'America/Denver';
            break;
        case 'US/Central' :
            $fncTimeZone = 'America/Chicago';
            break;
    endswitch; // fncTimeZone
    $fncOrigTZ = date_default_timezone_get();
    if ($fncOrigTZ != $fncTimeZone):
        $fncUnixDate = strtotime($fncDateTime);
        date_default_timezone_set($fncTimeZone);
        if ($fncDateFormat == ''):
            $fncNewDate = date('Y-m-d H:i:s', $fncUnixDate);
        else:   // Not $fncDateFormat == ''
            $fncNewDate = date($fncDateFormat, $fncUnixDate);
        endif;  // $fncDateFormat == ''
        date_default_timezone_set($fncOrigTZ);
    else:   // Not $fncOrigTZ != $fncTimeZone
        if ($fncDateFormat == ''):
            $fncNewDate = $fncDateTime;   // same timezone and no formatting requested so just return same value
        else:   // Not $fncDateFormat == ''
            $fncNewDate = date($fncDateFormat, strtotime($fncDateTime));
        endif;  // $fncDateFormat == ''
    endif;  // $fncOrigTZ != $fncTimeZone
    return $fncNewDate;
}  // end of wtkTimeZoneAdjust

/**
* Call this function to show a dead-end page.
*
* Normally called when "Nefarious Action Detected" but if you call passing 'offline'
* then it shows a "Server Offline for Maintenance and Upgrades" page.
*
* For the hackers, if the IP adddress is known it shows that with a warning message.
* This is used internally when user does something that looks like they are trying to hack the web server.
*
* @param string $fncType pass 'offline' or blank ''
* @return html page telling hackers they cannot continue
*/
function wtkDeadPage($fncType = ''){
    if ($fncType == 'offline'):
        $fncHtm = wtkLoadInclude(_WTK_RootPATH . 'htm/serverOffline.htm');
        $fncHtm = wtkReplace($fncHtm, '@wtkPath@', _WTK_RootPATH);
    else:
        $fncIPaddress = wtkGetIPaddress();
        if ($fncIPaddress != 'no-IP'):
            $fncTmp = "<p>Your IP address ($fncIPaddress) has been logged and our";
        else:
            $fncTmp = "<p>Our";
        endif;
        $fncTmp .= " technical staff has been notified so they can look into this immediately.</p>";
        $fncHtm =<<<htmVAR
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hacker Prevention</title>
</head>
<body>
    <div align="center"><br><br>
        <h2>Nefarious Action Detected</h2><br>
        $fncTmp
        <p>If this was a legitimate access, please try again later.</p>
    </div>
</body>
</html>
htmVAR;
    endif;
    echo $fncHtm;
    exit;
} // wtkDeadPage

/**
* Is Windows - determines whether servers is a Windows server
*
* @return boolean eturns true if Windows, otherwise returns false
*/
function wtkIsWindows() {
   return (DIRECTORY_SEPARATOR == '\\' ? true : false);
} // end of wtkIsWindows

/**
* This detects whether current URL is SSL or not.
*
* @return boolean true if SSL, else false
*/
function wtkIsSSL(){
    /* ----------------- ABS 03/12/16 -------------------
    Verify this works on your server; may always a return of true.
    --------------------------------------------------*/
    if (!empty($_SERVER['HTTPS'])):  // to prevent Notice: Undefined index: HTTPS warning
        if ($_SERVER['HTTPS'] == 1):  // Apache
            return true;
        else:   // Not $_SERVER['HTTPS'] == 1
            if ($_SERVER['HTTPS'] == 'on'):  // IIS
                return true;
            else:   // Not $_SERVER['HTTPS'] == 'on'
                return false;
            endif;  // $_SERVER['HTTPS'] == 'on'
        endif;  // $_SERVER['HTTPS'] == 1
    else:   // Not !empty($_SERVER['HTTPS'])
        if ($_SERVER['SERVER_PORT'] == 443):
            return true;
        else:   // Not $_SERVER['SERVER_PORT'] == 443
            return false; // just using http
        endif;  // $_SERVER['SERVER_PORT'] == 443
    endif;  // !empty($_SERVER['HTTPS'])
}  // end of wtkIsSSL

/**
* Based current URL adjusts link to use similar SSL or not SSL link.
*
* This is used by wtkRedirect to determine whether http:// or https:// should be used when redirecting.
*
* @param string $fncLinks
* @return properly formed link
*/
function wtkConvertLinks($fncLinks) {
    $fncServerStr = '';
    $fncServerStr = isset($fncLinks) ? $fncLinks : '';
    $fncPosition  = strpos($fncServerStr, '://');
    if ($fncPosition > 0):
        $fncServerStr = substr($fncServerStr, $fncPosition + 3);
        $fncServerStr = str_replace('//', '/', $fncServerStr);
        if (wtkIsSSL() == true):
            $fncServerStr = 'https://' . $fncServerStr;
        else:   // Not wtkIsSSL() == true
            $fncServerStr = 'http://' . $fncServerStr;
        endif;  // wtkIsSSL() == true
    else:   // Not $fncPosition > 0
        $fncServerStr = str_replace('//', '/', $fncServerStr);
    endif;  // $fncPosition > 0
    return $fncServerStr;
}  // end of wtkConvertLinks

function wtkCURLcall($fncURL, $fncHeader, $fncPost, $fncPostCount = 1, $fncErrTitle = 'cURL Err', $fncGetOrPost = 'POST'){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $fncHeader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($fncGetOrPost == 'POST'):
        curl_setopt($ch, CURLOPT_POST, $fncPostCount);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fncPost);
    else:
        $fncGet = http_build_query($fncPost); // Convert post data to query string
        // Append query string to URL for GET request
        $fncURL .= '?' . $fncGet;
    endif;
    curl_setopt($ch, CURLOPT_URL, $fncURL);

    $fncResult = curl_exec($ch);
    $fncCurlInfo = curl_getinfo($ch);
    $fncCurlHttp = $fncCurlInfo['http_code'];
    if (($fncCurlHttp != 200) && ($fncCurlHttp != 201)):
        if (!($fncResult)):
            $fncIpAddress = wtkGetIPaddress();
            $fncCurlErrNum = curl_errno($ch);
            $fncCurlErrStr = curl_error($ch);
            wtkLogError($fncErrTitle, "cURL error: [$fncCurlErrNum] $fncCurlErrStr \n Called from $fncIpAddress");
            $fncResult = "cURL error: [$fncCurlErrNum] $fncCurlErrStr";
        else:
            $fncCurlInfo = curl_getinfo($ch);
            $fncCurlHttp = $fncCurlInfo['http_code'];
            if ($fncCurlHttp != 200):
                wtkLogError($fncErrTitle, "HTTP Error : $fncCurlHttp ; Result: $fncResult");
            endif;
        endif;
    endif;
    curl_close($ch);
    return $fncResult;
} // wtkCURLcall

/*
Calling methods:
1)
$pgGoodJSON = wtkSanitizeForJSON($pgQuestion);
$pgMsg .= ',{"role": "user", "content": "' . $pgGoodJSON . '"}';

2)
$pgGoodJSON = wtkSanitizeForJSON($gloPDOrow['DialogueTurn']);
$pgDialogueJSON = json_encode(['role' => $gloPDOrow['Who'],'content' => $pgGoodJSON]);
*/
function wtkSanitizeForJSON($fncText){
    // Step 1: Remove control characters
    $fncSanitize = preg_replace('/[\x00-\x1F\x7F]/u', '', $fncText);
    // Step 2: Escape special characters
//    $fncSanitize = addslashes($fncSanitize);
    $fncSanitize = wtkReplace($fncSanitize, '"','\"'); // escape double quotes
    // Step 3: Encode non-ASCII characters
    $fncSanitize = utf8_encode($fncSanitize);
    // Step 4: Validate and sanitize user input
//    $fncSanitize = htmlspecialchars($fncSanitize);
    return $fncSanitize;
} // wtkSanitizeForJSON

/**
 * wtkShowArrayValue is only used for debugging to display array values
 *
 * This recursively shows all keys and values within an array.
 *
 * @param type $fncArray the array to search
 * @param type $fncAddBR defaults to 'N', if set to 'Y' then adds <br> to display beter in HTML
 * @return string containing all keys and their values
 */
function wtkShowArrayValue($fncArray, $fncAddBR = 'N') {
    // Recursive function to show all keys and values within an array
    $fncResult = '';
    foreach ($fncArray as $key => $value):
        if ($fncAddBR == 'Y'):
            $fncResult .= '<br>';
        endif;
        if (is_array($value) || is_object($value)):
            // If the value is an array or object, recursively call the function
            $fncResult .= 'SubArray: ' . "\n" . wtkShowArrayValue((array)$value, $fncAddBR);
        else:
            $fncResult .= "$key: $value" . "\n";
        endif;
    endforeach;
    return $fncResult;
} // wtkShowArrayValue

/**
 * safe_get_browser will return '' instead of giving an error if get_browser won't work
 *
 * @return string containing browser info or '' if not available
 */
function safe_get_browser() {
    // Check if the 'browscap' setting is defined and points to a readable file
    $browscap = ini_get('browscap');
    if (!$browscap || !is_readable($browscap)):
        // browscap not set or not readable — return null to avoid calling get_browser()
        $browser = '';
    else:
        // browscap is set and usable, so now we can call get_browser safely
        $browser = @get_browser(null, true);
        if ($browser === false):
            $browser = '';
        endif;
    endif;
    return $browser;
} // safe_get_browser

/**
 * Get HTML-encoded currency symbol based on currency code
 * @param $fncCurrencyCode
 * @return string
 */
function wtkCurrencySymbol($fncCurrencyCode) {
    switch ($fncCurrencyCode) {
        case ('EUR'):
            $fncResult = '&euro;';
            break;
        case ('GBP'):
            $fncResult = '&pound;';
            break;
        case ('USD'):
        case ('AUD'):
        case ('CAD'):
        case ('MXN'):
            $fncResult = '$';
            break;
        default:
            $fncResult = '$';
    }
    return $fncResult;
} // wtkCurrencySymbol

wtkTimeTrack('End of Utils.php');
?>
