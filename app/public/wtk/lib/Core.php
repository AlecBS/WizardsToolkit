<?PHP
/**
* Purpose of this page is to include all Wizard's Toolkit core library files
*
* After including all core library files, this checks to see if IP address of visitor
* is on the black list and should be locked out.  If so, they are sent to wtkDeadPage();
*
* All rights reserved.
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

$gloPageStart = microtime(true);
// require(_WTK_RootPATH . 'lib/class.phpmailer.php');
require(_WTK_RootPATH . 'lib/Legacy.php');
require(_WTK_RootPATH . 'lib/Utils.php');
$gloMyPage = wtkGetServer('PHP_SELF');
// wtkDeadPage('offline'); // uncomment if server is offline for DB updates, etc.
require(_WTK_RootPATH . 'lib/DataPDO.php');
if ((strpos($gloMyPage, '/testWTK') === false) ||
   ((strpos($gloMyPage, '/testWTK') !== false) && (wtkGetParam('Step') == 'DB'))):
   require(_WTK_RootPATH . 'lib/Browse.php');
endif;
require(_WTK_RootPATH . 'lib/Form.php');
require(_WTK_RootPATH . 'lib/Security.php');
require(_WTK_RootPATH . 'lib/Html.php');
require(_WTK_RootPATH . 'lib/Image.php');
require(_WTK_RootPATH . 'lib/Google.php');
require(_WTK_RootPATH . 'lib/Mobile.php');
require(_WTK_RootPATH . 'lib/PayPal.php');
require(_WTK_RootPATH . 'lib/Email.php');
require(_WTK_RootPATH . 'lib/Materialize.php');
require(_WTK_RootPATH . 'lib/Social.php');
require(_WTK_RootPATH . 'lib/Twilio.php');
require(_WTK_RootPATH . 'lib/Chart.php');
if ((strpos($gloMyPage, '/testWTK') === false) ||
   ((strpos($gloMyPage, '/testWTK') !== false) && (wtkGetParam('Step') == 'ionCube'))):
    require(_WTK_RootPATH . 'lib/Encrypt.php');
endif;
$gloPageTitle = '';
$gloId = wtkGetParam('id');
$gloRNG = wtkGetParam('rng');
require(_WTK_RootPATH . 'lib/ClientFuncs.php');
require_once(_WTK_RootPATH . 'lib/Mobile_Detect.php');
$pgDetectDevice = new Mobile_Detect;
$gloDeviceType = ($pgDetectDevice->isMobile() ? ($pgDetectDevice->isTablet() ? 'tablet' : 'phone') : 'computer');
// BEGIN  Mobile Testing
if (wtkGetGet('dType') != ''): // to force testing of different device types
    $gloDeviceType = wtkGetGet('dType');
endif;  // wtkGetParam('Type') != ''
if ($gloDeviceType != 'computer'):
    wtkSearchReplace("var pgVarMobile  = 'N';", "var pgVarMobile  = 'M';"); // do not set to 'Y' because that changes full site, this should fix date/email validation issues
endif;  // $gloDeviceType != 'computer'
//  END   Mobile Testing
if (!isset($gloSkipConnect)):
    $gloSkipConnect = 'N';
endif;
if ($gloSkipConnect != 'Y'):
    wtkConnectToDB();
endif;  // isset($gloSkipConnect)

// if (($pgIPaddress != 'specialIP') && ($gloSkipConnect != 'Y')): // white listed
if ($gloSkipConnect != 'Y'):
    // BEGIN see IP address is locked out
    $pgIPaddress = wtkGetIPaddress();  // If on Hacker list then skip to dead.php
    if ($pgIPaddress != 'no-IP'):
        $pgSqlFilter = array('IPaddress' => $pgIPaddress);
        $pgBadCnt = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkLockoutUntil` WHERE `IPaddress` = :IPaddress AND `LockUntil` > NOW()', $pgSqlFilter);
        if ($pgBadCnt > 0):
            $pgSQL  = 'UPDATE `wtkLockoutUntil` SET `BlockedCount` = (`BlockedCount` + 1)';
            $pgSQL .= ' WHERE `IPaddress` = :IPaddress AND `LockUntil` > NOW()';
            wtkSqlExec($pgSQL, $pgSqlFilter);
            wtkDeadPage('');
        endif;
        //  END  see IP address is locked out
        $pgFailSQL  = 'SELECT COUNT(*) FROM `wtkFailedAttempts` WHERE `IPaddress` = :IPaddress';
        $pgTotFailCount = wtkSqlGetOneResult($pgFailSQL, $pgSqlFilter);
        if ($pgTotFailCount > 10):
            wtkInsFailedAttempt('DDOS');
            $pgSQL =<<<SQLVAR
INSERT INTO `wtkLockoutUntil` (`FailCode`,`IPaddress`,`LockUntil`,`BlockedCount`)
  VALUES (:FailCode, :IPaddress, :LockUntil, :BlockedCount )
SQLVAR;
            $pgSqlFilter = array (
                'FailCode' => 'DDOS',
                'IPaddress' => $pgIPaddress,
                'LockUntil' => '2120-01-01',
                'BlockedCount' => $pgTotFailCount
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
            // BEGIN Only send one email per minute
            $pgEmailSQL  = "SELECT COUNT(*) FROM `wtkEmailsSent` WHERE `Subject` = :Subject";
            $pgEmailSQL .= " AND `AddDate` > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
            $pgSqlFilter = array('Subject' => 'Hacker Blocked');
            $pgCount = wtkSqlGetOneResult($pgEmailSQL, $pgSqlFilter);
            if ($pgCount == 0):
                $pgFailSQL  = 'SELECT COUNT(*) FROM `wtkFailedAttempts`';
                $pgFailSQL .= ' WHERE `AddDate` > DATE_SUB(NOW(), INTERVAL 1 MINUTE)';
                $pgFailCount = wtkSqlGetOneResult($pgFailSQL, []);

                $pgMsg  = 'Possible hacker attempt - check wtkFailedAttempts data for details.';
                $pgMsg .= '<br>If there is no wtkFailedAttempts data then something else is wrong.';
                $pgMsg .= '<br><br>PHP Time: ' . date('m/d/Y h:i:s');
                $pgMsg .= '<br><br>Current Page: ' . $gloCurrentPage;
                $pgMsg .= '<hr><br>SERVER_NAME: ' . wtkGetServer('SERVER_NAME');
                $pgMsg .= '<br>URL: ' . wtkGetServer('URL');
                $pgMsg .= '<br>HTTP_HOST: ' . wtkGetServer('HTTP_HOST') ;
                $pgMsg .= '<br>SCRIPT_FILENAME: ' . wtkGetServer('SCRIPT_FILENAME');
                $pgMsg .= '<br>HTTP_REFERER: ' . wtkGetServer('HTTP_REFERER');
                $pgMsg .= '<br>DATABASE_ENDPOINT: ' . wtkGetServer('DATABASE_ENDPOINT');
                $pgMsg .= '<br>DATABASE: ' . wtkGetServer('DATABASE');
                $pgMsg .= '<br>SERVER_ADDR: ' . wtkGetServer('SERVER_ADDR');
                $pgMsg .= '<br>REMOTE_ADDR: ' . wtkGetServer('REMOTE_ADDR');
                $pgMsg .= '<br><br>From IP address: ' . $pgIPaddress ;
                $pgMsg .= '<br><br><br>Failed Attacks within last minute: ' . $pgFailCount ;
                $pgMsg .= '<br>Total Failed Attacks from this IP Address: ' . $pgTotFailCount ;
                $pgSaveArray = array (
                    'FromUID' => 0
                );
                wtkNotifyViaEmailPlain('Hacker Blocked - ' . $pgFailCount, $pgMsg, '', $pgSaveArray);

                $pgSQL  = 'INSERT INTO `wtkEmailsSent` ';
                $pgSQL .= ' (`SendByUserUID`,`SendToUserUID`,`EmailAddress`,`Subject`,`EmailBody`)';
                $pgSQL .= ' VALUES (:SendByUserUID, :SendToUserUID, :EmailAddress, :Subject, :EmailBody)';
                $pgSqlFilter = array(
                    'SendByUserUID' => 0,
                    'SendToUserUID' => 1,
                    'EmailAddress' => $gloTechSupport,
                    'Subject' => 'Hacker Blocked',
                    'EmailBody' => $pgMsg,
                );
                wtkSqlExec(wtkSqlPrep($pgSQL),$pgSqlFilter);
            endif;
            wtkDeadPage('');
            //  END  Only send one email per minute
        endif;
    endif; // if have IP address, then check to see if should lockout
endif;

$gloUserUID = 0;
// next variables are for reporting aspect only
$gloShowPrint       = true;    // show print and export buttons on browse page to allow printing/exporting
$gloPrinting        = false;

if (wtkGetGet('Print') == 'ON'):
    $gloPrinting    = true;
    $gloShowPrint   = false;
    $gloShowExport  = false;
    $gloShowExportXML = false;
    wtkSearchReplace('<header class="main-header">','<header class="hidden">');
    wtkSearchReplace('<aside class="main-sidebar">','<aside class="hidden">');
    wtkSearchReplace('<footer class="main-footer">','<footer class="hidden">');
    wtkSearchReplace('<aside class="control-sidebar control-sidebar-dark">','<aside class="hidden">');
    // ABS 09/11/16  BEGIN  Log to Report Counter
    $pgSQLRptCntr = 'INSERT INTO `wtkReportCntr` (`RptUID`, `RptURL`, `UserUID`, `RptType`)';
    $pgRptUID = wtkGetParam('rng');
    if ($pgRptUID == ''):
        $pgRptUID = 'NULL';
    endif;
    if (!isset($gloUserUID) || $gloUserUID == ''):  // ABS 12/14/16
        $gloUserUID = (wtkGetCookie('UserUID') != '') ? wtkGetCookie('UserUID') : 0;
    endif;  // !isset($gloUserUID) || $gloUserUID == ''
    $pgPos = strpos($gloCurrentPage, 'reports.php?R');
    if ($pgPos === false): // custom report, not from wtkReports
        $pgCurrentPage = substr($gloCurrentPage, 0, 40);
    else:
        $pgCurrentPage = 'NULL';
    endif;
    if ($gloConnType == 'PDO'):
        $pgSQLRptCntr .= ' VALUES (:RptUID, :RptURL, :UserUID, :RptType)';
        $pgSqlFilter = array (
            'RptUID' => $pgRptUID,
            'RptURL' => $pgCurrentPage,
            'UserUID' => $gloUserUID,
            'RptType' => 'P'
        );
        wtkSqlExec($pgSQLRptCntr,$pgSqlFilter);
    else:
        $pgSQLRptCntr .= " VALUES($pgRptUID,'$pgCurrentPage',$gloUserUID,'P')";
        $pgSQLRptCntr = wtkReplace($pgSQLRptCntr, "'NULL'",'NULL');
        wtkSqlExec($pgSQLRptCntr);
    endif;
    //  END   Log to Report Counter
endif;  // wtkGetGet('Print') = 'ON'

/**
* @global boolean $gloIsFileUploadForm
*
* Used for specifying if the form is a file upload form.
* Automatically set to true by wtkFormFile functions in Materialize.php
*/
$gloIsFileUploadForm = false;

$gloLang = wtkGetCookie('wtkLang');
if ($gloLang == 'eng'):
    $gloLang = '';  // so can prepend for lookups in wtkLookups
endif;  // $gloLang == 'eng'
$gloMobileApp = wtkGetCookie('MobileApp');
?>
