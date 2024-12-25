<?php
/**
* This contains Wizard's Toolkit functions involving encryption and language translation.
* It also includes wtkTrackVisitor which tracks visitor views on web pages
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
* Add function at top of a page to make page require password to view.
*
* Page will prompt user for password based on parameter passed to this function.
* Only after correct password is entered is page visible.
* Once password is successfully entered a cookie is set allowing access to page for one year.
*
* @param string $fncPagePasscode The passcode you require to access the page
* @param string $fncHTMLtemplate .htm page to use for HTML template; defaults to /wtk/htm/minibox.htm
* @return string Function returns user to a user login page or returns user to the content attempting to access.
*/
function wtkPageProtect($fncPagePasscode, $fncHTMLtemplate = '') {
    global $gloMyPage, $gloWTKmode, $gloShowPrint, $gloSkipConnect,
        $gloConnected, $gloId, $gloRNG;
    $fncSkipConnect = $gloSkipConnect;
    $fncConnected = $gloConnected;
    $gloSkipConnect = 'Y';    // because this is a non-data-related lookup
    $gloConnected = false;
    $gloShowPrint = false;
    $fncPagePasscode = wtkEncode($fncPagePasscode);
    $fncPasscode  = wtkEncode(wtkGetPost('PgPasscode'));
    $fncHeader    = '';
    if ($fncPasscode != ''):
        if ($fncPasscode != $fncPagePasscode):
            $fncHeader = '<div align="center"><strong>Incorrect password - please try again.<br><br>Must enter a password to access this page.</strong></div>';
        else:
            wtkSetCookie('PgPasscode', $fncPasscode, '1year');
        endif;  // $fncPasscode !=  $fncPagePasscode
    else:   // Not $fncPasscode != ''
        $fncPasscode = wtkGetCookie('PgPasscode');
        if (($fncPasscode == '') || ($fncPasscode != $fncPagePasscode)):
            $fncHeader = '<div align="center"><strong>Must enter a password to access this page.</strong></div>';
        endif;  // $fncPasscode == ''
    endif;  // $fncPasscode != ''
    if ($fncHeader != ''):
        $gloWTKmode = 'ADD';
        wtkSearchReplace("<!--@ud_ReadMessage@-->", $fncHeader . '<br>');
        $fncHtm =<<<htmVAR
    <form id="wtkForm" name="wtkForm" action="?" method="POST">
        <div class="row">

htmVAR;
        $fncHtm .= wtkFormText('nadaTable', 'PgPasscode', 'password', 'Passcode');
        $gloMyPage = wtkReplace($gloMyPage, '.php','');
        $fncHtm  = wtkReplace($fncHtm, 'wtknadaTable','');
        $fncHtm  = wtkReplace($fncHtm, 'input-field col m6 s12','input-field col m9 s12');
        $fncHtm .= '    <div class="col m3 s12">' . "\n";
        $fncHtm .= wtkFormHidden('id', $gloId);
        $fncHtm .= wtkFormHidden('rng', $gloRNG);
        $fncHtm .= '<button type="submit" class="btn btn-save b-shadow waves-effect right"';
        $fncHtm .= ' onclick="Javascript:ajaxPost(\'' . $gloMyPage . '\', \'wtkForm\', \'\')">Enter</button>' . "\n";
        $fncHtm .= '    </div>' . "\n";
        $fncHtm .= '</div>' . "\n";
        $fncHtm .= '</form>' . "\n";
        wtkSearchReplace(_WTK_RootPATH . 'lib/Save.php', '');
        if ($fncHTMLtemplate == ''):
            $fncHTMLtemplate = _WTK_RootPATH . 'htm/minibox.htm';
        endif;  // !isset($fncHTMLtemplate)
        wtkMergePage($fncHtm, 'Password Required', $fncHTMLtemplate );
    endif;  // $fncHeader  != ''
    $gloSkipConnect = $fncSkipConnect;
    $gloConnected = $fncConnected;
}  // end of wtkPageProtect

/**
* wtkNoBookmark
*
* This prevents a page from working if it was bookmarked or the link was sent to someone.
* If the referer is not what you expect, you can redirect them to any page.
* Pass in what page must be referer as first parameter.  The second parameter is where to redirect if refer check fails.
* Here is an example:
* <code><br>
* wtkNoBookmark('mydomain.com', 'no2hackers.php');<br>
* </code>
*
* @param  string $fncMustFrom  what referer page must be
* @param  string $fncFailGoTo  where to redirect to if referer does not match
* @return null
*/
function wtkNoBookmark($fncMustFrom, $fncFailGoTo) {
    $fncReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
    $fncPos = stripos($fncReferer, $fncMustFrom);
    if ($fncPos === false):
        wtkRedirect($fncFailGoTo);
    endif;  // $fncPos !== false
}  // end of wtkNoBookmark

/**
* Pass in how long of a password you want generated.
*
* This excludes 1,l,0,O since those are often difficult to determine when viewed.
*
* Example usage:
* <code>
* $pgNewPW = wtkGeneratePassword(12); // creates 12-character password
* </code>
*
* @param  number $fncPwLength default 8 ; length of password to generate
* @param  number $fncComplex default 'Y' ; includes extra characters like !@#%^*()-+}{>
* @return password
*/
function wtkGeneratePassword($fncPwLength = 8, $fncComplex = 'Y') {
    $fncPossibleChars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    if ($fncComplex == 'Y'):
        $fncPossibleChars .= '!@#%^*()-+}{>';
    endif;  // $fncComplex == 'Y'
    $fncNewPW = '';

    for ($i = 0; $i < $fncPwLength; $i++):
        $fncRand = rand(0, strlen($fncPossibleChars) - 1);
        $fncNewPW .= substr($fncPossibleChars, $fncRand, 1);
    endfor; // $i = 0; $i < $fncPwLength; $i++

    return $fncNewPW;
}  // end of wtkGeneratePassword

/**
* Generate a hash that can be used for directing to a web page.
*
* This excludes 1,l,0,O since those are often difficult to determine when viewed.
* if third parameter is skipped or 'big' it uses sha256 to generate a hash.
* It checks to see if hash aleady exists in wtkLinkLogin table; if it does then
* new hash is created until an unused one is found.
*
* The URL assigned will be redirected to when the hash is verified.
*
* Example usage:
* <code>
* $pgResult = wtkGenerateHash('subscriber', 'https://yourdomain.com/newUser.php?id=123', 'big', 'Y');<br>
* </code>
*
* @param string $fncAction stored in wtkLinkLogin.ActionNote as method of categorizing
* @param string $fncURL where to redirect to when Hash is used
* @param string $fncSize defaults to 'big' which generates 64-characters hash otherwise generates 8-character hash
* @param string $fncSave defaults to 'Y'; when 'Y' verified unique and saves to `wtkLinkLogin` table
* @return password hash
*/
function wtkGenerateHash($fncAction, $fncURL, $fncSize = 'big', $fncSave = 'Y') {
    $fncDone = 1;
    while ($fncDone > 0):
        if ($fncSize == 'big'):
            $fncNewPassHash = hash('sha256', uniqid() . mt_rand(0,25000));
        else:
            $fncNewPassHash = hash('crc32b', $fncURL . '~' . mt_rand(0,25000), false);
        endif;
        if ($fncSave == 'Y'):
            $fncDone = wtkSqlGetOneResult("SELECT COUNT(*) FROM `wtkLinkLogin` WHERE `NewPassHash` = ?", [$fncNewPassHash]);
        else:
            $fncDone = 0;
        endif;
    endwhile; // $fncDone > 0

    if ($fncSave == 'Y'):
        $fncSQL  = 'INSERT INTO `wtkLinkLogin` (`ActionNote`, `GoToUrl`, `NewPassHash`)';
        $fncSQL .= ' VALUES (:Action, :URL, :Hash )';
        $fncSqlFilter = array (
            'Action' => $fncAction,
            'URL' => $fncURL,
            'Hash' => $fncNewPassHash
        );
        wtkSqlExec($fncSQL, $fncSqlFilter);
    endif;
    return $fncNewPassHash;
}  // end of wtkGenerateHash

/* ------------------------------------
 Above created PHP function that generates a unique NewPassHash and inserts row into wtkLinkLogin.

 Create PHP page that receives NewPassHash
 If unique and has not been visited yet, then set SESSION variable and redirect to GoToUrl .
 Modify wtkLogin.php - if SESSION variable exists then clear it and set Security Level to zero so can open page without logging in.
    Also set VisitDate in wtkLinkLogin so can never be used again.  Plus set $gloOneUse = true.
 Page redirected to should have code that checks to see if $gloOneUse == true.  If so then change buttons to point to a
 "Thank you" page.
 --------------------------------------------------*/
 /**
 * Pass in hash and if valid will redirect to associated link.
 *
 * If unique and has not been visited yet, then set SESSION variable and redirect to GoToUrl .
 * If passed to a Wizard's Toolkit page, wtkLogin.php will recognize SESSION variable exists
 * and will set Security Level to zero so can open page without logging in.
 *
 * Also set VisitDate in wtkLinkLogin so can never be used again.  Plus set $gloOneUse = true.
 * Page redirected to should have code that checks to see if $gloOneUse == true.  If so then change buttons to point to a
 * "Thank you" page.
 *
 * Example usage:
 * <code>
 * $pgNewPW = wtkGenerateHash(12); // creates 12-character password
 * </code>
 *
 * @param  number $fncHash to check for link redirection
 * @return password
 */
function wtkVerifyHashLink($fncHash) {
    if ($fncHash != ''):
        $fncSqlFilter = array (
            'Hash' => $fncHash
        );
        $fncCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkLinkLogin` WHERE `VisitDate` IS NULL AND `NewPassHash` = :Hash', $fncSqlFilter);
        // first verify was at least one count before doing below
        if ($fncCount == 0):
            $fncHtm = '<h2>Failure</h2><p>This hash is not recognized or has already been used.</p>';
            wtkSearchReplace('col m4 offset-m4 s12','col m6 offset-m3 s12');
        else:
            $fncGoTo = wtkSqlGetOneResult('SELECT `GoToUrl` FROM `wtkLinkLogin` WHERE `NewPassHash` = :Hash', $fncSqlFilter);
            wtkSqlExec('UPDATE `wtkLinkLogin` SET `VisitDate` = NOW() WHERE `NewPassHash` = :Hash', $fncSqlFilter);
            $_SESSION['HashPW'] = 'passed';
            wtkRedirect($fncGoTo);
        endif;  // $fncCount > 0
    else:
        $fncHtm = '<h2>Failure</h2><p>No hash received.</p>';
    endif;  // $fncHash != ''
    wtkMergePage($fncHtm, 'Wrong Page', _RootPATH . 'wtk/htm/minibox.htm');
}  // end of wtkVerifyHashLink

/**
* Multi-Lingual functionality
*
* If no language is chosen it uses default in which case original text is returned.
* Likewise if a translation does not exist in the current language database it returns the original text.
* This is called automatically by some Wizard Toolkit functions and can also be called directly by developer.
* When it is called and the language lookup does not exist, it inserts the request into wtkLanguage data
* table so it can be added later.  Back office administration pages ignore translation calls.
*
* @param  string $fncText to be translated; maximum 150 characters
* @global string $gloLang which holds the language preference of the user; defined in wtk/wtkServerInfo.php
* @return string translated text; maximum 250 characters
*/
function wtkLang($fncText) {  // Multi-Lingual functionality
    global $gloDriver1, $gloLang;  // set in Core.php
    $fncResult = $fncText;
    if (($gloLang != '') && ($gloLang != 'eng')):
        $fncPos = strpos($fncText, '<');
        if ($fncPos === false): // only translate words, not HTML tags
            $fncPos = strpos($_SERVER['PHP_SELF'], 'admin/');  // do not build translations for Admin site
            if ($fncPos === false):
                if (strlen(trim($fncText)) > 120):
                    $fncText = SUBSTR ($fncText, 0, 120 );
                else:  // change this to endif  if want to always translate; for now we skip translating if too long
                    $fncText = wtkReplace($fncText, "'","''");
                    $fncSqlFilter = array (
                        'Lang' => $gloLang,
                        'Text' => $fncText
                    );
                    if (strpos(strtolower($gloDriver1), 'ostgre') > 0):  // Multi-Language function not in MySQL yet but can be added on request
                        $fncSQL = 'SELECT "st_LanguageSwap" ' . "(:Lang,:Text)";
                        $fncResult = wtkSqlGetOneResult($fncSQL,$fncSqlFilter);
                    else:
                        $fncSQL = "SELECT COUNT(*) FROM `wtkLanguage` WHERE `PrimaryText` = :Text AND `Language` = :Lang AND `NewText` IS NOT NULL";
                        $fncCount = wtkSqlGetOneResult($fncSQL,$fncSqlFilter);
                        if ($fncCount > 0):
                            $fncResult = wtkSqlGetOneResult("SELECT `NewText` FROM `wtkLanguage` WHERE `PrimaryText` = :Text AND `Language` = :Lang",$fncSqlFilter);
                        else:
                            $fncSQL = "SELECT COUNT(*) FROM `wtkLanguage` WHERE `PrimaryText` = :Text AND `Language` = :Lang";
                            $fncCount = wtkSqlGetOneResult($fncSQL,$fncSqlFilter);
                            if ($fncCount == 0):
                                wtkSqlExec("INSERT INTO `wtkLanguage` (`Language`, `PrimaryText`) VALUES (:Lang, :Text )",$fncSqlFilter);
                            endif;
                        endif;
                    endif;  // strpos(strtolower($gloDriver1), 'ostgre') > 0
                endif;  // strlen(trim($fncText)) > 120
            endif;  // $fncPos === false
        endif;  // $fncPos === false
    endif;  // $gloLang != ''
    return $fncResult;
}  // end of wtkLang

/**
* Track Visitors as they go through your marketing website
*
* The purpose of this function and the wtkVisitors and wtkVisitorHistory tables
* is to allow clients to see how many pages a visitor went to before signing up
* and/or making a purchase.
*
* @param  string $fncPageTitle optional but helps for analytic reports
*/
function wtkTrackVisitor($fncPageTitle = 'NULL', $fncDateSet = ''){
    global $gloCurrentPage, $pgAffiliate;
    $fncFirstTime = false;
    $fncVisitUID = wtkGetCookie('VisitorUID');
    if ($fncVisitUID == ''): // need to start tracking
        $fncIPaddress = wtkGetIPaddress();
        $fncReferPage = wtkGetServer('HTTP_REFERER');
        $fncDomain = parse_url($fncReferPage, PHP_URL_HOST);
        if ($fncReferPage != ''):
            $fncReferPage = trim(substr($fncReferPage, 0, 240));
        else:   // Not $fncReferPage != ''
            $fncReferPage = 'unknown';
        endif;  // $fncReferPage != ''
        if ($pgAffiliate != ''):
            $fncAffiliateHash = $pgAffiliate;
        else:
            $fncAffiliateHash = wtkGetCookie('Affiliate');
        endif;
        if ($fncAffiliateHash == ''):
            $fncAffiliateUID = 'NULL';
        else:
            $fncAffiliateUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkAffiliates` WHERE `AffiliateHash` = ?', [$fncAffiliateHash]);
        endif;
        $fncSQL =<<<SQLVAR
INSERT INTO `wtkVisitors` (`IPaddress`,`AffiliateUID`, `Referer`,`ReferDomain`,`FirstPage`)
  VALUES (:IPaddress, :AffiliateUID, :Referer, :ReferDomain, :FirstPage )
SQLVAR;
        if ($fncPageTitle == 'NULL'):
            $fncFirstPage = $gloCurrentPage;
        else:
            $fncFirstPage = $fncPageTitle;
        endif;
        $fncSqlFilter = array(
            'IPaddress' => $fncIPaddress,
            'AffiliateUID' => $fncAffiliateUID,
            'Referer' => $fncReferPage,
            'ReferDomain' => $fncDomain,
            'FirstPage' => $fncFirstPage
        );
        wtkSqlExec($fncSQL, $fncSqlFilter);
        $fncSQL =<<<SQLVAR
SELECT `UID`
  FROM `wtkVisitors`
 WHERE `Referer` = :Referer AND `IPaddress` = :IPaddress
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
        $fncSqlFilter = array(
            'IPaddress' => $fncIPaddress,
            'Referer' => $fncReferPage
        );
        $fncVisitUID = wtkSqlGetOneResult($fncSQL, $fncSqlFilter, '', true);
        // added 4th parameter so uses Write DB instead of ReadOnly DB
        wtkSetCookie('VisitorUID',$fncVisitUID);
        $fncFirstTime = true;
    else:
    	if ($fncDateSet != ''):
            $fncSQL =<<<SQLVAR
UPDATE `wtkVisitors`
  SET `$fncDateSet` = NOW()
WHERE `UID` = :UID;
SQLVAR;
            $fncSqlFilter = array('UID' => $fncVisitUID);
            wtkSqlExec($fncSQL, $fncSqlFilter, false);
        endif;
    endif;

    $fncSQL =<<<SQLVAR
INSERT INTO `wtkVisitorHistory` (`VisitorUID`,`PageTitle`,`PageURL`)
  VALUES (:VisitorUID, :PageTitle, :PageURL )
SQLVAR;

    $fncSqlFilter = array(
        'VisitorUID' => $fncVisitUID,
        'PageTitle' => $fncPageTitle,
        'PageURL' => $gloCurrentPage
    );
    wtkSqlExec($fncSQL, $fncSqlFilter, false);

    // BEGIN Calculate time between this page and prior page
    if (!$fncFirstTime):
        $fncSQL =<<<SQLVAR
SELECT COALESCE(`UID`,0) AS `fncPriorUID`
  FROM `wtkVisitorHistory`
WHERE `VisitorUID` = :VisitorUID
ORDER BY `UID` DESC LIMIT 1 OFFSET 1;
SQLVAR;
        $fncSqlFilter = array(
            'VisitorUID' => $fncVisitUID
        );
        $fncPriorUID = wtkSqlGetOneResult($fncSQL, $fncSqlFilter, '', true);
        // added 4th parameter so uses Write DB instead of ReadOnly DB
        $fncSQL =<<<SQLVAR
SELECT TIMESTAMPDIFF(SECOND, `AddDate`, NOW()) AS `fncSecondsViewed`
  FROM `wtkVisitorHistory`
 WHERE `UID` = :PriorUID
SQLVAR;
        $fncSqlFilter = array(
            'PriorUID' => $fncPriorUID
        );
        $fncSecondsViewed = wtkSqlGetOneResult($fncSQL, $fncSqlFilter);
        if ($fncSecondsViewed > 7200): // if more than 2 hours, default to 2 hours
            $fncSecondsViewed = 7200;
        endif;
        $fncSQL =<<<SQLVAR
UPDATE `wtkVisitorHistory`
   SET `SecondsViewed` = :SecondsViewed
 WHERE `UID` = :PriorUID
SQLVAR;
        $fncSqlFilter = array(
            'PriorUID' => $fncPriorUID,
            'SecondsViewed' => $fncSecondsViewed
        );
        wtkSqlExec($fncSQL, $fncSqlFilter, false);
    endif;
    //  END  Calculate time between this page and prior page
    return $fncVisitUID;
} // wtkTrackVisitor
?>
