<?php
/**
* This contains Wizard's Toolkit functions for building HTML form fields.
*
* These are universal functions required for saving of data, priming fields, hidden fields, etc.
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
* This function can be called at top of page to determine if page should be read-only.
*
* If page is being converted to PDF (aka printed) then it will always be considered Read-Only.
* If the page is currently being edited by someone else, then this will return
* that it should be readonly and it will set the global variable $gloFormMsg
* with the information of who is currently editing the page including their phone number.
* This is based on the wtkLoginLog table and only triggers if the person is currently
* on the page and it has not been more than 3 hours since they opened the page.
*
* @param string $fncPage name of page
* @param string $fncId  id of data being looked up on page
* @global boolean $gloPrinting if printing to PDF then this will be true and result is read-only
* @global string $gloEnableLockout checks to see if 'Y' and should make read-only if others on same page
* @global string $gloFormMsg will have message and user info added if someone already editing page
* @return true if page should be read-only; otherwise returns false
*/
function wtkPageReadOnlyCheck($fncPage, $fncId){
    global $gloPrinting, $gloFormMsg, $gloUserUID, $gloEnableLockout, $gloSqlDateTime;
    // Later this can include call to business rules or other logic
    if ($gloPrinting == true):
        $fncResult = true;
    else:   // Not $gloPrinting == true
        if (($gloEnableLockout == 'Y') && ($fncId != 0)): // $fncId 0 means ADD page
            $fncDate = wtkSqlDateSub('NOW()',3,'HOUR');
            $fncSQL =<<<SQLVAR
SELECT COUNT(*)
  FROM `wtkLoginLog`
 WHERE `CurrentPage` = :CurrentPage AND `PassedId` = :PassedId
   AND `LastLogin` > $fncDate
    AND `UserUID` <> :UserUID
SQLVAR;
            if (!is_numeric($fncId)):
                $fncSQL = wtkReplace($fncSQL, '= :PassedId','IS NULL');
                $fncSqlFilter = array (
                    'CurrentPage' => $fncPage,
                    'UserUID' => $gloUserUID
                );
            else:
                $fncSqlFilter = array (
                    'CurrentPage' => $fncPage,
                    'PassedId' => $fncId,
                    'UserUID' => $gloUserUID
                );
            endif;
            $fncCount = wtkSqlGetOneResult($fncSQL, $fncSqlFilter);
            if ($fncCount > 0):
                $fncSQL =<<<SQLVAR
SELECT CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `UserName`,
    L.`UID`, DATE_FORMAT(L.`LastLogin`, '$gloSqlDateTime') AS `Date`,
    fncContactIcons(u.`Email`,COALESCE(u.`CellPhone`,u.`Phone`),NULL,NULL,'Y',u.`UID`,u.`SMSEnabled`,'Y','') AS `Contact`
  FROM `wtkUsers` u
    INNER JOIN `wtkLoginLog` L ON L.`UserUID` = u.UID
  WHERE L.`CurrentPage` = :CurrentPage AND L.`PassedId` = :PassedId
    AND L.`UserUID` <> :UserUID
 	ORDER BY L.`UID` DESC LIMIT 1
SQLVAR;
                wtkSqlGetRow($fncSQL, $fncSqlFilter);
                $fncUserName = wtkSqlValue('UserName');
                $gloFormMsg  = "<div class='center'>This page is currently being edited by $fncUserName";
                $gloFormMsg .= ' : ' . wtkSqlValue('Contact') . "\n";
                $gloFormMsg .= '<br>they entered page on ' . wtkSqlValue('Date') . "\n";
                // BEGIN Allow unlocking
                $gloFormMsg .= '<a onclick="JavaScript:ajaxGo(\'/wtk/ajxUnlockPage\',' . wtkSqlValue('UID') . ')"';
                $gloFormMsg .= ' class="tooltipped" data-position="bottom" data-tooltip="carefull! - click to unlock">' . "\n";
                $gloFormMsg .= '<i class="material-icons">lock_open</i></a>';
                //  END  Allow unlocking
                $gloFormMsg .= '</div><br><br>';
                $fncResult = true;
            else:
                $fncResult = false;
            endif;
        else:  // $gloEnableLockout != 'Y'
            $fncResult = false;
        endif; // $gloEnableLockout != 'Y'
    endif;  // $gloPrinting == true
    return $fncResult;
} // wtkPageReadOnlyCheck

/**
* Create hidden form field
*
* @param string $fncName used to create name and id of input field
* @param string $fncValue value of field
* @return HTML for hidden form field
*/
function wtkFormHidden($fncName, $fncValue) {
    $fncResult  = '<input type="hidden" name="' . $fncName . '" id="' . $fncName . '" value="' . $fncValue . '">' . "\n";
    return $fncResult;
}  // end of wtkFormHidden

/**
* Internal function called by Form-field functions to enable saving.
*
* This stores table and field names into an array for updating the data table via Save.php
*
* @param string $fncTable SQL data table name
* @param string $fncField aka column name
* @param string $fncFormType this declares what type of field it is; really only important for checkbox fields
* @global array $gloUpdateFieldsArray
* @return null
*/
$gloUpdateFieldsArray = array();
function wtkFormPrepUpdField($fncTable, $fncField, $fncFormType) {
    global $gloUpdateFieldsArray;
    if ($fncFormType == 'checkbox'):
        $fncField = '*' . $fncField;
    endif;  // $fncFormType == 'checbox'
    $fncNotDone = true;
    for ($i = 1; $i < (sizeof($gloUpdateFieldsArray) + 1); ++$i):
        if ($gloUpdateFieldsArray[$i][1] == $fncTable):
            $gloUpdateFieldsArray[$i][2] .= ',' . $fncField;
            $fncNotDone = false;
            break;
        endif;  // $gloUpdateFieldsArray[$i][1] == $fncTable
    endfor; // $i = 1; $i < (sizeof($gloUpdateFieldsArray) + 1); ++$i
    if ($fncNotDone == true):
        $gloUpdateFieldsArray[$i][1] = $fncTable;
        $gloUpdateFieldsArray[$i][2] = $fncField;
    endif;  // $fncNotDone == true
}  // end of wtkFormPrepUpdField

/**
* Write Hidden Update Field
*
* This takes array generated from wtkFormPrepUpdField and creates the hidden field
* values for updating the data using Save.php.  Must be called before closing `</form>` on page.
* This also adds hidden fields which will trigger JS to set up MaterializeCSS functionality
* like Date fields, Droplists, Time fields, etc.
*
* @global string $gloUpdateFieldsArray
* @uses function wtkEncode
* @uses function wtkFormHidden
* @return hidden fields used by Save.php to update database
*/
$gloSelectExist = false;
$gloDatePickExist = false;
$gloHasImage = false;
$gloHasTextArea = '';
$gloHasFileUploads = '';
function wtkFormWriteUpdField() {
    global $gloUpdateFieldsArray, $gloSelectExist, $gloDatePickExist,
      $gloTimePickExist, $gloHasTextArea, $gloHasImage, $gloHasFileUploads;
    $fncTableList = '';
    $fncHiddenFields = '';
    for ($i = 1; $i < (sizeof($gloUpdateFieldsArray) + 1); ++$i):
        if ($fncTableList != ''):
            $fncTableList .= ',';
        endif;  // $fncTableList != ''
        $fncTableList .= $gloUpdateFieldsArray[$i][1];
        $fncTable      = wtkEncode($gloUpdateFieldsArray[$i][1]); // encrypt table name
        $fncFields     = wtkEncode($gloUpdateFieldsArray[$i][2]); // encrypt field list

        $fncHiddenFields .= wtkFormHidden($fncTable, $fncFields);
    endfor; // $i = 1; $i < (sizeof($gloUpdateFieldsArray) + 1); ++$i
    $fncHiddenFields .= wtkFormHidden('T', wtkEncode($fncTableList));
    if ($gloSelectExist == true):
        $fncHiddenFields .= wtkFormHidden('HasSelect', 'Y');
    endif;
    if ($gloDatePickExist == true):
        $fncHiddenFields .= wtkFormHidden('HasDatePicker', 'Y');
    endif;
    if ($gloTimePickExist == true):
        $fncHiddenFields .= wtkFormHidden('HasTimePicker', 'Y');
    endif;
    if ($gloHasTextArea != ''):
        $fncHiddenFields .= wtkFormHidden('HasTextArea', $gloHasTextArea);
    endif;
    if ($gloHasFileUploads != ''):
        $fncHiddenFields .= wtkFormHidden('wtkUploadFiles', $gloHasFileUploads);
    endif;
    if ($gloHasImage == true):
        $fncHiddenFields .= wtkFormHidden('HasImage', 'Y');
    endif;
    $fncPgIx = wtkGetSession('PgIx');
    if ($fncPgIx > 1):
        $fncHiddenFields .= wtkFormHidden('PgIx', $fncPgIx);
    endif;
    wtkAddUserHistory();
    return $fncHiddenFields;
}  // end of wtkFormWriteUpdField

/**
* Prime Field
*
* If (and only if) page is in ADD mode then this function will create a hidden
* form field on the page with the value you passed.
* Upon leaving the page that value will be primed in the insert for this table.
*
* @param string $fncTable SQL data table name
* @param string $fncField aka column name
* @param string $fncValue to be saved
* @global string $gloWTKmode checked to see if = 'ADD'
* @uses function wtkFormPrepUpdField
* @uses function wtkFormHidden
* @return hidden form field
*/
function wtkFormPrimeField($fncTable, $fncField, $fncValue) {
    global $gloWTKmode;
    $fncResult = '';
    if ($gloWTKmode == 'ADD'):
        wtkFormPrepUpdField($fncTable, $fncField, 'text');
        $fncResult = wtkFormHidden('wtk' . $fncTable . $fncField, $fncValue) . "\n";
    endif;  // $gloWTKmode == 'ADD'
    return $fncResult;
}  // end of wtkFormPrimeField
?>
