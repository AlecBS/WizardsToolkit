<?php
/**
* This contains Wizard's Toolkit functions for creating HTML excluding form fields.
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
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

$gloIndentCnt   = 0;
/**
* Create the HTML for a phone link.
*
* If $gloMobileApp = 'Y' this creates JavaScript:postMessage('dialPhone- and the phone number,
* which is a WTK mobile app function that natively triggers phone feature on smart phones.
* If $gloMobileApp != 'Y' and user has chosen to "Use Skype for calls" this will
* use "callto:", otherwise it will use "tel:".
*
* @param string $fncPhone
* @param string $fncPhoneExt optionally show phone extension
* @param string $fncShowNumber
* @global string $gloMobileApp should be set to 'Y' if page is called via mobile app
* @global string $gloUseSkype this contains user's preference on whether to use Skype for outbound calls
* @return html for href link to phone number
*/
function wtkPhoneLink($fncPhone, $fncPhoneExt = '', $fncShowNumber = 'Y') {
    global $gloMobileApp, $gloUseSkype;
    $fncResult = '';
    if ($fncPhone != ''):
        $fncJustNum = $fncPhone;
        $fncJustNum = wtkReplace($fncJustNum, '(','');
        $fncJustNum = wtkReplace($fncJustNum, ')','');
        $fncJustNum = wtkReplace($fncJustNum, '-','');
        $fncJustNum = wtkReplace($fncJustNum, '.','');
        $fncJustNum = wtkReplace($fncJustNum, '/','');
        $fncJustNum = wtkReplace($fncJustNum, '\\','');
        $fncJustNum = wtkReplace($fncJustNum, ' ','');
        $fncFirst1Char = substr($fncJustNum, 0, 1);
        if ($fncFirst1Char != '1'):
            $fncJustNum = '+1' . $fncJustNum;
        else:   // Not $fncFirst1Char != '1'
            if ($fncFirst1Char != '+'):
                $fncJustNum = '+' . $fncJustNum;
            endif;  // $fncFirst1Char != '+'
        endif;  // $fncFirst1Char != '1'
        $fncResult = '<a onclick="JavaScript:wtkDialPhone(\'' . $fncJustNum . '\')">';
        /* Old code before wtkDialPhone had special Skype option
            if ($gloUseSkype == 'Y'):
                $fncResult .= 'callto:';
            else:
                $fncResult .= 'tel:';
            endif;
        */
        $fncResult .= '<i class="material-icons">local_phone</i></a>';
        if ($fncShowNumber == 'Y'):
            $fncResult .= $fncPhone ;
            if ($fncPhoneExt != ''):
                $fncResult .= ' Ext: ' . $fncPhoneExt;
            endif;  // fncPhoneExt != ''
        endif;  // $fncShowNumber == 'Y'
    endif;  // $fncPhone != ''
    return $fncResult;
}  // end of wtkPhoneLink

/**
* Help buttons can be created and placed in multiple places on a page.
*
* This creates Help button on a page.  It checks to see if help exists and if it doesn't it inserts data
* so in back-office Help can be added for this page.  If help does not currently exist or is "blank help"
* then it will not show unless User has rights to Add/Edit Help.  Users that do have Help permissions
* can add/edit the help information from the Help popup.
*
* The Help is shown in a MaterializeCSS modal window and can include text and/or video display.
*
* @param string $fncHelpIndex defaults to blank and will use PHP page if nothing passed
* @param string $fncHelpTitle defaults to blank in which case will use $gloPageTitle if it exists
* @global string $gloPageTitle
* @return html of button which calls wtkShowHelp JS function
*/
function wtkHelp($fncHelpIndex = '', $fncHelpTitle = '') {
    global $gloUserUID, $gloPageTitle;
    if ($fncHelpTitle != ''):
        $fncPageTitle = $fncHelpTitle;
    else:
        if (isset($gloPageTitle)):
            $fncPageTitle = wtkReplace($gloPageTitle, "'", "''");
        else:
            $fncPageTitle = 'Need to Define';
        endif;  // isset($gloPageTitle)
    endif;
    if ($fncHelpIndex == ''):
        $fncFullFile = $_SERVER['SCRIPT_NAME'];
        $fncParts = explode('/', $fncFullFile);
        $fncHelpIndex = $fncParts[count($fncParts) - 1];
    endif;  // $fncHelpIndex == ''

    $fncSQL = 'SELECT COUNT(*) FROM `wtkHelp` WHERE `HelpIndex` = :HelpIndex';
    $fncSqlFilter = array('HelpIndex' => $fncHelpIndex );
    $fncCount = wtkSqlGetOneResult($fncSQL,$fncSqlFilter);
    if ($fncCount == '0'):
        $fncSQL = 'INSERT INTO `wtkHelp` (`HelpIndex`, `HelpTitle`) VALUES (:HelpIndex, :HelpTitle)';
        $fncSqlFilter = array (
            'HelpIndex' => $fncHelpIndex,
            'HelpTitle' => $fncPageTitle
        );
        wtkSqlExec($fncSQL,$fncSqlFilter);
    endif;  // fncCount == 0
    $fncSQL =<<<SQLVAR
SELECT CONCAT(h.`UID`,'~',u.`CanEditHelp`,'~',
  CASE
    WHEN COALESCE(h.`HelpText`,'') = '' THEN 'N'
    ELSE 'Y'
  END ) AS `Info`
 FROM `wtkHelp` h, `wtkUsers` u
WHERE u.`UID` = :UserUID AND h.`HelpIndex` = :HelpIndex
SQLVAR;
    $fncSqlFilter = array (
        'HelpIndex' => $fncHelpIndex,
        'UserUID' => $gloUserUID
    );
    $fncInfo = wtkSqlGetOneResult($fncSQL, $fncSqlFilter); // cannot use wtkSqlGetRow because resets $gloPDOrow
    $fncArray = explode('~', $fncInfo);
    $fncUID = $fncArray[0];
    $fncCanEditHelp = $fncArray[1];
    $fncShowHelp = $fncArray[2];
    if ($fncShowHelp == 'N'):
        if ($fncCanEditHelp == 'Y'):
            $fncShowHelp = 'Y';
        endif;
    endif;
    $fncResult = '';
    if ($fncShowHelp == 'Y'):
        $fncResult .= '<a onclick="JavaScript:wtkShowHelp(' . $fncUID . ')">';
        $fncResult .= '<i class="small material-icons blue-text">help_outline</i></a>';
    endif; // <i class="material-icons" title="Help">help_outline</i>
    return $fncResult;
}  // end of wtkHelp

/**
* HTM Table start.
* With newer HTML table definitions this is no longer as useful.
*
* @param string $fncWidth Defaults to '100%'
* @param string $fncCellSpacing Defaults to '0'
* @param string $fncCellPadding Defaults to '0'
* @param string $fncBorder Defaults to '0'
* @param string $fncAttrib Defaults to blank
* @global string $gloIndentCnt
* @return HTML for beginning of a table like <table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
*/
function wtkHtmTableTop($fncWidth = '100%', $fncCellSpacing = '0',  $fncCellPadding = '0',  $fncBorder = '0',  $fncAttrib = '') {
    global $gloIndentCnt;
    $gloIndentCnt ++;
    $fncResult  = str_repeat('  ', $gloIndentCnt) . '<table';
    if ($fncWidth != '' ):
        $fncResult .= ' width="' . $fncWidth . '"';
    endif; // $fncatwtributs != ''
    $fncResult .= ' cellspacing="' . $fncCellSpacing . '" cellpadding="' . $fncCellPadding . '" border="' . $fncBorder . '"';

    if ($fncAttrib != '' ):
        $fncResult .= ' ' . $fncAttrib;
    endif; // $fncattributs != ''
    $fncResult .= '>' . "\n";
    $fncResult .= str_repeat('  ', ($gloIndentCnt + 1)) . '<tr>' . "\n";
    return $fncResult;
}  // end of wtkHtmTableTop

/**
* Define Search & Replaces to do on entire final page.
*
* This will affect the entire generated HTML page after data is pulled in and everything else is completed.
* The search and replaces are utilized via wtkMergePage and wtkBuildDataBrowse.
*
* @param string $fncSearch
* @param string $fncReplace
* @global string $pgSearchReplaceCntr
* @global string $gloFormChangeArray
* @return null
*/
$pgSearchReplaceCntr = 0;
function wtkSearchReplace($fncSearch, $fncReplace){
    if (trim($fncSearch) != '' ):
        global $pgSearchReplaceCntr;
        global $gloFormChangeArray;
        $pgSearchReplaceCntr += 1;
        $gloFormChangeArray[1][$pgSearchReplaceCntr] = $fncSearch;
        $gloFormChangeArray[2][$pgSearchReplaceCntr] = $fncReplace;
    endif;// trim($fncSearch) != ''
}// wtkSearchReplace($fncSearch, $fncReplace)

/**
* Create multiple HTM spaces.
* Pass in number of spaces you want and this will generate that many &nbsp;
*
* @param string $fncCnt
* @return HTML &nbsp; based on count passed
*/
function wtkHtmSpace($fncCnt) {
    $fncResult = str_repeat('&nbsp;', $fncCnt);
    return $fncResult;
}  // end of wtkHtmSpace

/**
* Generate <a > linking tag based on whether accessing from a computer, tablet or phone
*
* If $gloMobileApp != 'Y' from website use normal href syntax.
* If $gloMobileApp = 'Y' then uses JavaScript:ajaxGo syntax for WTK Single Page navigation.
*
* @param string $fncURL what to link to
* @param string $fncTitle what to display
* @param string $fncClass optional class style to add
* @return HTML <a> tag based on parameter and access device
*/
function wtkHref($fncURL, $fncTitle, $fncClass = '') {
    global $gloMobileApp;
    $fncResult = '<a';
    if ($fncClass != ''):
        $fncResult .= ' class="' . $fncClass . '"';
    endif;  // $fncClass != ''
    if ($gloMobileApp == 'Y'):
        $fncResult .= ' onClick="JavaScript:ajaxGo(\'' . $fncURL . '\');">' . $fncTitle . '</a>';
    else:   // Not $gloMobileApp == 'Y'
        $fncResult .= ' href="' . $fncURL . '">' . $fncTitle . '</a>';
    endif;  // $gloMobileApp == 'Y'
    return $fncResult;
}  // end of wtkHref

/**
* This data-driven method of notifying users.
*
* Broadcast messages to users and allow them to clear.  This uses wtkBroadcast and wtkBroadcast_wtkUsers tables.
* If there are eligible broadcast messages they will be returned in a <div class="row">.  Once a user has
* cleared it will not be displayed again for that user.
*
* @param string $fncMode defaults to 'display'; other option is 'count'
*    to retrieve count of broadcasts to show in tag or alert
* @global $gloPrinting boolean if count and printing then skip getting count from data
* @return html with listing of broadcast alerts
*/
function wtkBroadcastAlerts($fncMode = 'display') {
    global $gloWTKobjConn, $gloUserUID, $gloUserSecLevel, $gloPrinting;
    $fncResult = ''; // No broadcast messages';
    $fncFilter = '';  // can enhance here based on security level, role, etc.
    if ($gloUserSecLevel < 20):
        $fncAudience = 'Cust'; // can enhance here based on security level, role, etc.
//        $fncFilter = ' AND ';
    elseif ($gloUserSecLevel = 20):
        $fncAudience = 'Staf'; // can enhance here based on security level, role, etc.
    else:
        $fncAudience = 'All'; // can enhance here based on security level, role, etc.
    endif;
    if ($fncMode == 'count'):  // called to show in top-bar
        if ($gloPrinting == true):
            $fncResult = 0;
        else:   // Not $gloPrinting == true
            $fncSQL =<<<SQLVAR
SELECT COUNT(*)
  FROM `wtkBroadcast` b
    LEFT OUTER JOIN `wtkBroadcast_wtkUsers` x ON x.`BroadcastUID` = b.`UID` AND x.`UserUID` = $gloUserUID
  WHERE (x.`UID` IS NULL OR b.`AllowClose` = 'N')
        AND CURRENT_DATE BETWEEN COALESCE(b.`ShowOnDate`,CURRENT_DATE) AND b.`ShowUntilDate`
        AND b.`AudienceType` IN ('ALL', :AudienceType)
        AND b.`DelDate` IS NULL
        $fncFilter
SQLVAR;
            $fncResult = wtkSqlGetOneResult($fncSQL);
        endif;  // $gloPrinting == true
    else:   // Not $fncMode == 'count'
        $fncSQL =<<<SQLVAR
SELECT b.`UID`, b.`MessageType`, COALESCE(b.`MessageHeader`,'') AS `MessageHeader`,
    b.`MessageNote`, b.`AllowClose`, b.`CloseMessage`, b.`AudienceSubType`,
    b.`BroadcastColor`, b.`TextColor`
  FROM `wtkBroadcast` b
    LEFT OUTER JOIN `wtkBroadcast_wtkUsers` x ON x.`BroadcastUID` = b.`UID` AND x.`UserUID` = $gloUserUID
  WHERE (x.`UID` IS NULL OR b.`AllowClose` = 'N')
        AND CURRENT_DATE BETWEEN COALESCE(b.`ShowOnDate`,CURRENT_DATE) AND b.`ShowUntilDate`
        AND b.`AudienceType` IN ('All', :AudienceType )
        AND b.`DelDate` IS NULL
        $fncFilter
  ORDER BY b.`ShowOnDate` ASC, b.`UID` ASC
SQLVAR;

        $fncSqlFilter = array (
            'AudienceType' => $fncAudience
        );
        $fncContent = '';
        $fncCount = 0;
        $fncSQL = wtkSqlPrep($fncSQL);
        $fncObjRS = $gloWTKobjConn->prepare($fncSQL);
        $fncObjRS->execute($fncSqlFilter);
        while ($fncRow = $fncObjRS->fetch()):
            $fncCount ++;
            $fncUID = $fncRow['UID'];
            $fncTextColor = $fncRow['TextColor'];
            if ($fncTextColor != ''):
                $fncTextColor = ' ' . $fncTextColor;
            endif;
            $fncTmp  = '<div id="wtkBC' . $fncUID . '" style="transform: translateY(0px) !important" class="carousel-item ' . $fncRow['BroadcastColor'] . $fncTextColor . '">' . "\n";
            $fncHeader = $fncRow['MessageHeader'];
            if ($fncHeader != ''):
                $fncTmp .= '  <br><h1';
                if ($fncTextColor != ''):
                    $fncTmp .= ' class="' . $fncTextColor . '"';
                endif;
                $fncTmp .= '>' . $fncHeader . '</h1>' . "\n";
            endif;  // fncHeader != ''
            $fncTmp .= '  <br><p>' . nl2br($fncRow['MessageNote']) . '</p>' . "\n";
            if ($fncRow['AllowClose'] == 'Y'):
                $fncTmp .= '<br><button class="btn btn-default" onclick="wtkClearBroadcast(' . $fncUID . ')">' . $fncRow['CloseMessage'] . '</button>';
            endif;
            $fncTmp .= '</div>' . "\n";
            $fncContent .= $fncTmp;
        endwhile;
        unset($fncObjRS);

        if ($fncContent != ''):
            $fncResult =<<<htmVAR
<div id="broadcastDIV" class="carousel carousel-slider center">
    $fncContent
</div>
<input type="hidden" id="HasCarousel" name="HasCarousel" value="Y">
<input type="hidden" id="broadcastCount" name="broadcastCount" value="$fncCount">
htmVAR;
        endif;  // fncResult != ''
    endif;  // $fncMode == 'count'
    return $fncResult;
}  // end of wtkBroadcastAlerts

/**
* wtkSPArestart for re-entry into website when returning from outside APIs.
*
* This can be used for SPA designs to allow user to start within app on a certain
* page without re-logging in. For example, when you give Stripe or PayPal a return page URL
* it can include the apiKey and page to display.
*
* @param string $fncApiKey is the pgApiKey that the logged in user currently has showing they are logged in
* @param string $fncPage is the name of the HTML template to be used.  For example thanks.htm
* @param string $fncHtm optional parameter if you have set HTML you want to prepend the $fncPage
* @return html
*/
function wtkSPArestart($fncHtm, $fncTemplateHTML = ''){
    // use apiKey to lookup security level of user and verify page call is legit
    global $gloCoName, $gloSiteDesign;
    $fncApiKey = wtkGetParam('apiKey');
    $fncPage = wtkGetGet('p');
    if (($fncPage != '') && ($gloSiteDesign == 'SPA') && ($fncApiKey != '')):
        $fncSQL =<<<SQLVAR
SELECT u.`SecurityLevel`
  FROM `wtkUsers` u
   INNER JOIN `wtkLoginLog` l ON l.`UserUID` = u.`UID`
 WHERE l.`apiKey` = :apiKey AND l.`LogoutTime` IS NULL
SQLVAR;
        $fncSqlFilter = array (
            'apiKey' => $fncApiKey
        );
        $fncSecLvl = wtkSqlGetOneResult($fncSQL, $fncSqlFilter, 'badKey');
        if ($fncSecLvl == 'badKey'):
            wtkDeadPage();
        endif;
        if ($fncPage == 'ok'): // passed id parameter so must want PHP page
            $fncPage = '';
        else:
            $fncPage = wtkLoadInclude(getcwd() . '/' . $fncPage . '.htm');
        endif;
        $fncPage .= $fncHtm . "\n";
        $fncPage .= wtkFormHidden('SPArestart', 'Y');
        $fncPage .= wtkFormHidden('apiKeyRestart', $fncApiKey);
        $fncPage .= wtkFormHidden('secLvlRestart', $fncSecLvl);
        wtkSearchReplace('id="myNavbar" class="hide"','id="myNavbar"');
        wtkSearchReplace('id="mainPage" class="hide"','id="mainPage"');
        wtkSearchReplace('id="loginPage" class="','id="loginPage" class="hide ');
        if ($fncTemplateHTML == ''):
            $fncTemplateHTML =  _WTK_RootPATH . 'htm/spa';
        endif;
        wtkMergePage($fncPage, $gloCoName, $fncTemplateHTML . '.htm');
    endif;
} // wtkSPArestart

/**
* Use HTML template and display entire page (Multi Page Application)
*
* @param string $fncFiller
* @param string $fncPageTitle
* @param string $fncTemplateHTML defaults to blank in which case will use /wtk/html/spa.htm for HTML template
* @param string $fncSkipExit defaults to false
* @global string $gloCoLogo
* @global string $gloFormMsg
* @global string $gloJsInit
* @global string $gloFormChangeArray
* @global string $pgSearchReplaceCntr
* @global string $gloShowPrint
* @global string $gloPrinting
* @global string $gloShowExport
* @global string $gloShowExportXML
* @global string $gloIconPrint
* @global string $gloIsFileUploadForm
*/
function wtkMergePage($fncFiller, $fncPageTitle, $fncTemplateHTML = '', $fncSkipExit = false) {
    global $gloCoLogo, $gloCoName, $gloMobileApp, $gloFormMsg, $gloJsInit,
           $gloFormChangeArray, $pgSearchReplaceCntr, $gloShowPrint, $gloPrinting,
           $gloShowExport, $gloShowExportXML, $gloIconPrint, $gloIconExport, $gloIconExportXML,
           $gloIsFileUploadForm, $gloUserUID, $gloMyPage, $gloDarkLight;

    // BEGIN  moved here since Header needs to be affected
    if (wtkGetParam('Err') == 'BadSearch'):
        wtkSearchReplace('<!-- @wtkSearchResults@ -->','<font color="red">Search Failed</font><br>');
        wtkSearchReplace('Quick Search:', '<font color="red">Search Failed:</font>');
        wtkSearchReplace('btn-green" id="searchBtn"', 'btn-red" id="searchBtn"');
    endif;  // wtkGetParam('Err') == 'BadSearch'
    //  END   moved here since Header needs to be affected
    if ($gloPrinting):
        if ($gloFormMsg != ''):
            $gloFormMsg = $gloFormMsg . '<br><br>';
        endif;
    endif;
    if ($fncTemplateHTML == ''):
        $fncTemplateHTML = _WTK_RootPATH . 'htm/spa.htm';
    endif;  // $fncTemplateHTML == ''
    $fncTemplate = '';
    $fncObjFile  = fopen($fncTemplateHTML, 'r');
    $fncTemplate = fread($fncObjFile, filesize($fncTemplateHTML));
    fclose ($fncObjFile);
    if ($fncTemplate != ''):
        $fncPrintBtn  = '';
        $fncExportBtn = '';
        $fncExportXMLBtn = '';
        if ($gloMyPage != '/testWTK.php'):
            $fncPageTitle = wtkLang($fncPageTitle);
        endif;
        if ($gloDarkLight == 'Dark'):
            $fncTemplate = wtkReplace($fncTemplate, 'wtkLight.css', 'wtkDark.css');
        endif;
        $fncTemplate = wtkReplace($fncTemplate, '@CompanyLogo@', $gloCoLogo);
        $fncTemplate = wtkReplace($fncTemplate, '@CompanyName@', $gloCoName);
        $fncTemplate = wtkReplace($fncTemplate, '@rootPath@', _RootPATH);
        $fncTemplate = wtkReplace($fncTemplate, '@wtkPath@', _WTK_RootPATH);
        $fncTitle = wtkReplace('<title>' . $fncPageTitle . '</title>', '<br>', ' - ');
        $fncTemplate = wtkReplace($fncTemplate, '<title>@PageTitle@</title>', $fncTitle);
        $fncTemplate = wtkReplace($fncTemplate, '@PageTitle@', $fncPageTitle);
        $fncTemplate = wtkReplace($fncTemplate, '@FormMessage@', $gloFormMsg);
        $fncTemplate = wtkReplace($fncTemplate, '@wtkContent@', $fncFiller);
//      $fncTemplate = wtkReplace($fncTemplate, '//@initAttributes@//', $gloJsInit);
        // BEGIN  Used in upload file forms.
        if ($gloIsFileUploadForm):
            $fncTemplate = wtkReplace($fncTemplate, '@FormEncType@', ' enctype="multipart/form-data"');
            $fncTemplate = wtkReplace($fncTemplate, '//@beforeSave@//', '  fncResponse = beforSaveFiles(fn, arg);' . "\n" . ' //@beforeSave@//');
            $fncTemplate = wtkReplace($fncTemplate,'<!-- Placeholders JS -->', // moved to below jquery2 instead of near top
                 '<script LANGUAGE="JavaScript" type="text/javascript" src="'._WTK_RootPATH.'js/wtkFileUpload.js"></script>' . "\n" . '<!-- Placeholders JS -->');
        else:
            $fncTemplate = wtkReplace($fncTemplate, '@FormEncType@', '');
        endif;
        //  END   Used in upload file forms.

        if (wtkGetGet('Mode') != 'Export' && wtkGetGet('Mode') != 'XML'):
            if ($gloShowPrint == true && $gloPrinting == false):
                $fncPrintBtn = '<a class="btn btn-default btn-sm" style="cursor: pointer;" onclick="JavaScript:wtkPrint(' . $gloUserUID . ');">' . $gloIconPrint . '</a>';
                // ABS 09/26/13  BEGIN  wtkPrint required form
                $fncTmp  = '<form action="' . _WTK_RootPATH . 'lib/Print.php" method="post" name="PrintForm" id="PrintForm">' . "\n";
                $fncTmp .= '<input type="hidden" name="hdr" id="hdr" value="' . $fncPageTitle . '">' . "\n";
                $fncTmp .= '<input type="hidden" name="ftr" id="ftr" value="@ftr@"></form>' . "\n";
                $fncTemplate = wtkReplace($fncTemplate, '<!--@wtkBodyClose@-->', $fncTmp . '<!--@wtkBodyClose@-->'); // ABS 06/23/16
                //  END   wtkPrint required form
            endif;  // $gloShowPrint == true && $gloPrinting == false
            if ($gloShowExport):   // moved Export feature to separate variable check
                // $fncExportBtn = '<img src="' . $gloIconExport . '" width="20" height="20" border="0" alt="Export" onClick="';
                $fncExportBtn = '<a class="btn btn-default btn-sm" style="cursor: pointer;" onclick="JavaScript:wtkSubmitToPrint(\'' . $gloMyPage . '?Mode=Export&' . wtkGetServer("QUERY_STRING") . '\');">' . $gloIconExport . '</a>';
            endif; // ($gloShowExport)
            if ($gloShowExportXML):
                $fncExportXMLBtn = '<a class="btn btn-default btn-sm" style="cursor: pointer;" onclick="JavaScript:wtkSubmitToPrint(\'' . $gloMyPage . '?Mode=XML&' . wtkGetServer("QUERY_STRING") . '\');">' . $gloIconExportXML . '</a>';
            endif; // ($gloShowExportXML)
        endif;  // wtkGetGet('Mode') != 'Export'
        $fncTemplate = wtkReplace($fncTemplate, '<!-- @HeaderButtons@ -->', $fncPrintBtn . ' ' . $fncExportBtn. ' ' . $fncExportXMLBtn . '<!-- @HeaderButtons@ -->');

        global $gloWTKmode;
        if ($gloWTKmode == 'ADD'):
            $fncTemplate = wtkReplace($fncTemplate, '@wtkMode@', 'ADD');
        else:   // Not $gloWTKmode == 'ADD'
            $fncTemplate = wtkReplace($fncTemplate, '@wtkMode@', 'EDIT');
        endif;  // $gloWTKmode == 'ADD'

        for ($i = 1; $i < ($pgSearchReplaceCntr + 1); ++$i):  // changed to $pgSearchReplaceCntr instead of sizeof($gloFormChangeArray)
            if ($gloFormChangeArray[1][$i] != ''):
                $fncTemplate = wtkReplace($fncTemplate, $gloFormChangeArray[1][$i], $gloFormChangeArray[2][$i]);
            endif;
        endfor; // $i = 1; $i < (sizeof($gloFormChangeArray) + 1); ++$i
    endif; // $fncTemplate != ''

    // BEGIN  Log User History - who is surfing what pages
    global $gloCurrentPage, $gloSkipConnect, $gloMapJS, $gloMapJScenter;
    if ($gloCurrentPage != 'SkipSave'):
        error_reporting(0); // to prevent commands out of sync errors
    endif;  // $gloCurrentPage != 'SkipSave'
    if ($fncTemplate != ''):
        $fncTemplate = wtkReplace($fncTemplate, '<!-- @wtkMenu@ -->', '');
        if (strpos($gloMyPage, '/testWTK') === false):
            $fncTemplate = wtkReplace($fncTemplate, '<!-- @wtkUpdateFields@ -->', wtkFormWriteUpdField());
        endif;
        // Note: wtkFormWriteUpdField() calls wtkAddUserHistory()
    endif; // $fncTemplate != ''
    //  END   Log User History - who is surfing what pages
    if ((isset($gloSkipConnect) ? $gloSkipConnect : 'N') != 'Y'):
        wtkDisconnectToDB(); // moved here so Title change will work using wtkLang above
    endif;
    // BEGIN  Map printing at bottom
    if ($gloMapJS != ''):
        $fncTemplate = wtkReplace($fncTemplate, '</body></html>', '');
        print($fncTemplate);
        $fncJS  = '<script type="text/javascript">' . "\n";
        $fncJS .= '  $(document).ready(function() {' . "\n" . $gloMapJS . $gloMapJScenter ;
        $fncJS .= '  });' . "\n" . '</script>' . "\n";
        $fncJS .= '</body></html>';
        if ($fncSkipExit == false):
            print($fncJS);
        endif;  // $fncSkipExit == false
    else:   // Not $gloMapJS != ''
    //  END   Map printing at bottom
        if ($fncTemplate == ''): // could not find .htm template
            print($fncFiller);
        else:
            print($fncTemplate);
        endif;
    endif;  // $gloMapJS != ''
    if ($fncSkipExit == false):
        wtkShowTimeTracks();
        exit;
    endif;  // $fncSkipExit == false
} // wtkMergePage

wtkTimeTrack('End of Html.php');
?>
