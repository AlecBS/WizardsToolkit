<?php

/**
 * Create cancel, save and possibly copy buttons.  If read-only then instead shows a "Return" button.
 *
 * Example calling method:
 * <code>
 * $pgHtm .= wtkUpdateBtns();
 * </code>
 *
 * @param string $fncForm defaults to 'wtkForm'
 * @param string $fncSave defaults to '/wtk/lib/Save'
 * @param string $fncCopy defaults to '' blank; if not blank then shows Copy button
 * @return html returns html with update and cancel buttons
 */
function wtkUpdateBtns($fncForm = 'wtkForm', $fncSave = '/wtk/lib/Save', $fncCopy = ''){
    global $gloForceRO, $gloWTKmode, $gloDeviceType;
    if ($fncForm == ''):
        $fncForm = 'wtkForm';
    endif;
    if ($gloForceRO == true):
        $fncBtnTxt = wtkLang('Return');
        $fncHtm =<<<htmVAR
        <div class="text-center mt-5">
            <button type="button" class="btn btn-neutral" onclick="Javascript:wtkGoBack()">$fncBtnTxt</button>
        </div>
htmVAR;
    else:
        $fncCancelTxt = wtkLang('Cancel');
        $fncSaveTxt = wtkLang('Save');
        if ($fncCopy == ''): // No copy requested
            $fncHtm =<<<htmVAR
        <div class="text-center mt-5">
            <button type="button" class="btn btn-neutral" onclick="Javascript:wtkGoBack()">$fncCancelTxt</button>
            &nbsp;&nbsp;
            <button type="button" class="btn btn-primary" onclick="Javascript:ajaxPost('$fncSave', '$fncForm', 'Y')">$fncSaveTxt</button>
        </div>
htmVAR;
        else: // want copy button
            if ($gloWTKmode == 'ADD'):
                $fncBtnText = 'Add and Repeat';
            else:
                if ($gloDeviceType == 'phone'):
                    $fncBtnText = 'Copy';
                else:
                    $fncBtnText = 'Save & Copy';
                endif;
            endif;
            $fncBtnTxt = wtkLang($fncBtnText);
            $fncHtm =<<<htmVAR
        <div class="text-center mt-5">
            <button type="button" class="btn btn-neutral" onclick="Javascript:wtkGoBack()">$fncCancelTxt</button>
            &nbsp;&nbsp;
            <button id="copyBtn" type="button" class="btn btn-secondary" onclick="Javascript:ajaxCopy('$fncSave', '$fncForm')">$fncBtnText</button>
            &nbsp;&nbsp;
            <button type="button" class="btn btn-primary" onclick="Javascript:ajaxPost('$fncSave', '$fncForm', 'Y')">$fncSaveTxt</button>
        </div>
htmVAR;
        endif; // want copy button
    endif;
    return $fncHtm;
} // wtkUpdateBtnsT

function wtkFormText($fncTable, $fncColName, $fncType = 'text', $fncLabel = '', $fncColSize = 'notUsed', $fncRequired = 'N', $fncHelpText = '') {
    global $gloForceRO;
    $fncDisabled = '';
    if ($gloForceRO == true):
        $fncDisabled = ' disabled';
    else:
        if ($fncRequired == 'Y'):
            $fncDisabled = ' required';
        endif;
    endif;
    if (($fncLabel == '') && ($fncColName == 'IPaddress')):
        $fncLabel = 'IP Address';
    endif;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    $fncFormId = 'wtk' . $fncTable . $fncColName;
    $fncIcon = '';
    $fncExtra = '';
    $fncShowPW = '';
    switch (strtolower($fncType)):
        case 'password':
            $fncShowPW  = "\n" . '        <span class="material-icons toggle-password" data-toggle="#';
            // 2FIX
            $fncShowPW .= $fncFormId;
            $fncShowPW .= '" style="cursor:pointer;">visibility</span>';
            break;
        case 'date':
//2FIX      $fncType = 'text';
            break;
        case 'time':
//          $fncExtra = ' class="time-width"';
            break;
        case 'dollar':
//            $fncIcon = "\n" . '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-file-upload"/></svg>';
        case 'number':
            $fncType = 'text';
            if ($gloForceRO != true):
                $fncExtra = ' onChange="JavaScript:wtkValidate(this,\'NUMERIC\');"';
            endif;
            break;
        case 'email':
            $fncExtra = ' onChange="JavaScript:wtkValidate(this,\'EMAIL\');"';
            break;
        case 'tel':
            $fncExtra = ' onChange="JavaScript:wtkValidate(this,\'PHONE\');"';
            break;
        case 'timepicker':
            $fncType = 'text';
            if ($gloForceRO == false):
                $fncExtra = ' class="timepicker"';
                global $gloTimePickExist;
                $gloTimePickExist = true;
            endif;
            break;
    endswitch;
    if ($fncHelpText != ''):
        if (strlen($fncHelpText) < 121):
            $fncHelpText = wtkLang($fncHelpText);
        endif;
        $fncHelpText = "\n" . '<p class="label" style="font-size:smaller;padding-left:15px">' . $fncHelpText . '</p>';
    endif;
    /* $fncHelpText$fncShowPW
    <div class="input-field col $fncColSize">$fncIcon
    */
    $fncHtm =<<<htmVAR
    <div>
        <label for="$fncFormId" class="floating-label">
            <span>$fncLabel</span>
            <input$fncDisabled type="$fncType"$fncExtra id="$fncFormId" name="$fncFormId" value="@$fncColName@" class="input">$fncHelpText            
        </label>
    </div>
htmVAR;
    $fncHtm = wtkDisplayData($fncColName, $fncHtm, $fncTable, '', $fncType) . "\n";
    return $fncHtm;
}  // wtkFormText

function wtkFormTextArea($fncTable, $fncColName, $fncLabel = '', $fncColSize = '1', $fncRequired = 'N', $fncHelpText = '') {
    global $gloForceRO, $gloHasTextArea;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    $fncFormId = 'wtk' . $fncTable . $fncColName;
    if ($gloForceRO == true):
        // do nothing
    else:
        if ($fncRequired == 'N'):
            $fncSetReguired = '';
        else:
            $fncSetReguired = ' required';
        endif;
        if ($gloHasTextArea != ''):
            $gloHasTextArea .= ',';
        endif;
        $gloHasTextArea .= $fncFormId;
    endif;
    if ($fncHelpText != ''):
        if (strlen($fncHelpText) < 121):
            $fncHelpText = wtkLang($fncHelpText);
        endif;
        $fncHelpText = "\n" . '<span class="helper-text">' . $fncHelpText. '</span>';
    endif;
//      <label for="$fncFormId">$fncLabel</label>$fncHelpText
    $fncHtm =<<<htmVAR
    </div>
    <div class="flex gap-4 mt-4">
        <div class="flex-auto">
            <label for="$fncFormId" class="floating-label">
                <span>$fncLabel</span> 
                <textarea$fncSetReguired id="$fncFormId" name="$fncFormId" class="textarea w-full"
                    placeholder="$fncLabel">@$fncColName@</textarea>
            </label>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-$fncColSize gap-4">
htmVAR;
    $fncHtm = wtkDisplayData($fncColName, $fncHtm, $fncTable,'','textarea') . "\n";
    if ($gloForceRO == true):
        $fncHtm = wtkReplace($fncHtm, '<textarea ', '<textarea disabled ');
    endif;
    return $fncHtm;
}  // wtkFormTextArea


/**
 * Creates input form fields for type="radio".
 *
 * Example calling method:
 * <code>
 * $pgValues = array(<br>
 * &nbsp;&nbsp;&nbsp; 'Male' => 'M',<br>
 * &nbsp;&nbsp;&nbsp; 'Female' => 'F',<br>
 * &nbsp;&nbsp;&nbsp; 'Uncertain' => 'U'<br>
 * );<br>
 * $pgHtm .= wtkFormRadio('pets', 'Gender', 'Pet Gender', $pgValues, 'm2 s6');<br>
 * </code>
 *
 * @param string $fncTable data table
 * @param string $fncColName data column name
 * @param string $fncLabel optionally passed, if not then uses $fncColName
 * @param array  $fncValueArray must contain values
 * @param string $fncColSize defaults to 'm6 s12'
 * @param string $fncDisplay defaults to 'V' for Vertical; pass anything else for Horizontal
 * @uses function wtkFormLabel
 * @uses function wtkFormPrepUpdField
 * @return html returns surrounding HTML for radio fields
 */
function wtkFormRadio($fncTable, $fncColName, $fncLabel, $fncValueArray,
                      $fncColSize = 'notUsed', $fncDisplay = 'V') {
    global $gloForceRO;
    if ($gloForceRO == true):
        $fncDisabled = ' disabled="disabled"';
    else:
        $fncDisabled = '';
        wtkFormPrepUpdField($fncTable, $fncColName, 'text');
    endif;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    if (!is_array($fncValueArray) || count($fncValueArray)==0):
        // 2ENHANCE test and improve Developer alert
        $fncMsg = '<span class="red-text">Developer Error: radio value options not passed</span>';
        return $fncMsg;
    endif;  // !is_array($fncValueArray) || count($fncValueArray)==0
    $fncValue = wtkSqlValue($fncColName);
    $fncFormId = 'wtk' . $fncTable . $fncColName;
    $fncHtm =<<<htmVAR
<div>
    $fncLabel
    <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncValue">
htmVAR;

    if ($fncDisplay != 'V'):
        $fncHtm .= '<table><tr><td>' . "\n";
    endif;
    $fncInc = 0;
    foreach($fncValueArray as $Label => $RadioOption){
        if ($RadioOption == $fncValue ):
            $fncChoice = ' CHECKED';
        else:
            $fncChoice = '';
        endif; // $Label == $fncValue
        $fncInc++;
        if (($fncDisplay != 'V') && ($fncInc > 1)):
            $fncHtm .= '</td><td>' . "\n";
        endif;
        $fncHtm .=<<<htmVAR
    <p class="ml-5 mt-3">
      <label for="$fncFormId$fncInc" >
        <input class="radio" type="radio" id="$fncFormId$fncInc" name="$fncFormId" value="$RadioOption"$fncDisabled $fncChoice/>
        <span>$Label</span>
      </label>
    </p>
htmVAR;
    } // foreach($fncValueArray as $Label => $RadioOption)
    if ($fncDisplay != 'V'):
        $fncHtm .= '</td></tr></table>' . "\n";
    endif;
    $fncHtm .= "\n" . '</div>' . "\n";
    return $fncHtm;
}  // wtkFormRadio


/**
 * Creates html for Select drop list.
 *
 * The SQL query to use to generate a list of values and display values needs to be passed.
 *
 * Example calling method:
 * <code>
 * $pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAstate' ORDER BY `LookupValue` ASC";<br>
 * $pgHtm .= wtkFormSelect('pets', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s6');<br>
 * </code>
 *
 * @param string $fncTable data table
 * @param string $fncColName data column name
 * @param string $fncSQL SQL query to retrieve value and display to show in select
 * @param array  $fncFilter SQL PDO values if necessary or pass blank array []
 * @param string $fncDisplayField column name from $fncSQL to display
 * @param string $fncValueField column name from $fncSQL to use as value in options
 * @param string $fncLabel optionally passed, if not then uses $fncColName
 * @param string $fncColSize defaults to 'm6 s12'
 * @param string $fncShowBlank defaults to 'N'
 * @uses function wtkFormLabel
 * @uses function wtkFormPrepUpdField
 * @uses function wtkGetSelectOptions
 * @return html returns surrounding HTML for checkbox field
 */
function wtkFormSelect($fncTable, $fncColName, $fncSQL, $fncFilter, $fncDisplayField, $fncValueField, $fncLabel = '', $fncColSize = 'notUsed', $fncShowBlank = 'N') {
    global $gloSelectExist, $gloForceRO;
    $gloSelectExist = true;
    $fncDisabled = '';
    if ($gloForceRO == true):
        $fncDisabled = ' disabled';
    else:
        wtkFormPrepUpdField($fncTable, $fncColName, 'text');
    endif;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    $fncValue = wtkSqlValue($fncColName);
    $fncFormId = 'wtk' . $fncTable . $fncColName;
    $fncHidden = wtkFormHidden('Orig' . $fncFormId, $fncValue);
    $fncList = wtkGetSelectOptions($fncSQL, $fncFilter, $fncDisplayField, $fncValueField, $fncValue);
    if ($fncShowBlank == 'Y'):
        $fncList = '<option value=""></option>' . "\n" . $fncList;
    endif;
    $fncHtm =<<<htmVAR
<div>
    <label for="$fncFormId" class="floating-label">
        $fncHidden
        <span class="label">$fncLabel</span>
        <select$fncDisabled id="$fncFormId" name="$fncFormId" class="select">
            $fncList
        </select>
    </label>
</div>
htmVAR;
    return $fncHtm;
} // wtkFormSelect

/**
 * Creates input form field for type="checkbox".
 *
 * The $pgValues array needs to pass what the value should be when 'checked' and when 'not' checked.
 *
 * Example calling method:
 * <code>
 * $pgValues = array(<br>
 * &nbsp;&nbsp;&nbsp; 'checked' => 'Y',<br>
 * &nbsp;&nbsp;&nbsp; 'not' => 'N'<br>
 * );<br>
 * $pgHtm .= wtkFormCheckbox('pets', 'CanTreat', 'Allowed to give Treats', $pgValues, 'm3 s6');<br>
 * </code>
 *
 * @param string $fncTable data table
 * @param string $fncColName data column name
 * @param string $fncLabel optionally passed, if not then uses $fncColName
 * @param array  $fncValueArray must contain values
 * @param string $fncColSize defaults to 'm6 s12'
 * @uses function wtkFormLabel
 * @uses function wtkFormPrepUpdField
 * @return html returns surrounding HTML for checkbox field
 */
function wtkFormCheckbox($fncTable, $fncColName, $fncLabel, $fncValueArray, $fncColSize = 'notUsed') {
    global $gloForceRO;
    $fncDisabled = '';
    if ($gloForceRO == true):
        $fncDisabled = ' disabled="disabled"';
    else:
        wtkFormPrepUpdField($fncTable, $fncColName, 'checkbox');
    endif;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    if (!is_array($fncValueArray) || count($fncValueArray)==0):
        $fncMsg =<<<htmVAR
<div role="alert" class="alert alert-error">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
  <span>Developer Error: Checkbox value options not passed</span>
</div>
htmVAR;
        return $fncMsg;
    endif;  // !is_array($fncValueArray) || count($fncValueArray)==0

    $fncValue = wtkSqlValue($fncColName);
    $fncCheckValue = $fncValueArray['checked'];
    if ($fncValue == $fncValueArray['checked']):
        $fncChecked = 'checked';
    else:
        $fncChecked = '';
    endif;
    $fncFormId  = 'wtk' . $fncTable . $fncColName;
    $fncHidValue = ($fncChecked == 'checked' ? $fncChecked : 'not') . '~' . $fncValueArray['checked'] . '~' . $fncValueArray['not'];
    // note: hidden value saves whether checked or not originally plus what checked and unchecked should save as in format of "checked~Y~N"

    $fncResult =<<<htmVAR
<div>
    <label for="$fncFormId" class="label">
        <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncHidValue">
        <input type="checkbox" value="$fncCheckValue" class="checkbox" id="$fncFormId" name="$fncFormId"$fncDisabled $fncChecked>
        $fncLabel
    </label>
</div>
htmVAR;
    return $fncResult . "\n";
}  // end of wtkFormCheckbox


/**
 * Creates input form field for type="checkbox" but with Switch user interface
 *
 * The $pgValues array needs to pass what the value should be when 'checked' and when 'not' checked.
 *
 * Example calling method:
 * <code>
 * $pgValues = array(<br>
 * &nbsp;&nbsp;&nbsp; 'checked' => 'Y',<br>
 * &nbsp;&nbsp;&nbsp; 'not' => 'N'<br>
 * );<br>
 * $pgHtm .= wtkFormSwitch('wtkShortURL', 'PasswordYN', 'Use Password', $pgValues, 'm2 s12');<br>
 * </code>
 *
 * @param string $fncTable data table
 * @param string $fncColName data column name
 * @param string $fncLabel optionally passed, if not then uses $fncColName
 * @param array  $fncValueArray must contain values
 * @param string $fncColSize defaults to 'm6 s12'
 * @uses function wtkFormLabel
 * @uses function wtkFormPrepUpdField
 * @return html returns surrounding HTML for checkbox field
 */
function wtkFormSwitch($fncTable, $fncColName, $fncLabel, $fncValueArray, $fncColSize = 'm6 s12') {
    global $gloForceRO;
    $fncDisabled = '';
    if ($gloForceRO == true):
        $fncDisabled = ' disabled="disabled"';
    else:
        wtkFormPrepUpdField($fncTable, $fncColName, 'checkbox');
    endif;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    if (!is_array($fncValueArray) || count($fncValueArray)==0):
        // 2ENHANCE test and improve Developer alert
        $fncMsg = '<span class="red-text">Developer Error: Checkbox value options not passed</span>';
        return $fncMsg;
    endif;  // !is_array($fncValueArray) || count($fncValueArray)==0

    $fncValue = wtkSqlValue($fncColName);
    $fncCheckValue = $fncValueArray['checked'];
    if ($fncValue == $fncValueArray['checked']):
        $fncChecked = 'checked';
    else:
        $fncChecked = '';
    endif;
    $fncFormId  = 'wtk' . $fncTable . $fncColName;
    $fncHidValue = ($fncChecked == 'checked' ? $fncChecked : 'not') . '~' . $fncValueArray['checked'] . '~' . $fncValueArray['not'];
    // note: hidden value saves whether checked or not originally plus what checked and unchecked should save as in format of "checked~Y~N"

    $fncResult =<<<htmVAR
<div>
    <label for="$fncFormId" class="label">
        <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncHidValue">
        <input type="checkbox" value="$fncCheckValue" class="toggle" id="$fncFormId" name="$fncFormId"$fncDisabled $fncChecked>
        $fncLabel
    </label>
</div>
htmVAR;
    return $fncResult . "\n";
}  // end of wtkFormSwitch

/**
 * Used to create file input field for uploading files.  Check to see whether to use wtkFileUpload instead.
 *
 * Example calling method:
 * <code>
 * $pgHtm .= wtkFormFile('pets','FilePath','/imgs/pets/','NewFileName','Pet Photo','m4 s12');
 * </code>
 *
 * @param string $fncTable name of data table
 * @param string $fncColPath name of data column to hold path to image/file
 * @param string $fncFilePath actual path on webserver
 * @param string $fncFileName name of data column to hold new name of file uploaded
 * @param string $fncLabel optionally passed to show as label; if not then uses $fncColName
 * @param string $fncColSize MaterializeCSS column sizing - defaults to 'm6 s12'
 * @param string $fncRefresh defaults to '' blank; set to image ID you want refreshed upon saving (by JS)
 * @param string $fncShowOneClickUpload defaults to 'N' but if set to 'Y' then adds button to upload using AJAX without needing a 'Save' button
 * @param string $fncAccept defaults to 'accept="image/*"'; you can change this to other document filters like accept=".pdf"
 * @param string $fncThumbnail defaults to 'Y'; if set to 'Y' then adds an <img id="imgPreview" ...> which will show a preview of images
 * @param number $fncFormId usually leave this with default of '1' but if you have more than one file upload on a page, each must have this parameter different
 * @param string $fncAllowDelete defaults to 'Y' which shows a Delete button to delete file on server
 * @return html returns surrounding HTML for input type="file"
 */
function wtkFormFile($fncTable, $fncColPath, $fncFilePath, $fncFileName,
                     $fncLabel = '', $fncColSize = 'notUsed', $fncRefresh = '',
                     $fncShowOneClickUpload = 'N', $fncAccept = 'accept="image/*"',
                     $fncThumbnail = 'Y', $fncFormId = '1', $fncAllowDelete = 'Y') {

    global $gloWTKmode, $gloForceRO, $gloHasImage, $gloIsFileUploadForm,
           $gloAccessMethod, $gloId, $gloHasFileUploads;

    $gloIsFileUploadForm = true;

    $fncLabel = wtkFormLabel($fncLabel, $fncFileName);
    $fncFile  = wtkSqlValue($fncFileName);
    switch ($fncAccept):
        case 'accept="image/*"':
            $fncIcon = 'camera';
            break;
        case 'accept=".pdf"':
            $fncIcon = 'pdf';
            break;
        default:
            $fncIcon = 'file-upload';
            break;
    endswitch;
    if ($fncFile != ''):
        $fncDelHide = '';
        $fncUpBtn = '<span id="wtkfAddBtn' . $fncFormId . '" class="btn btn-circle hidden"><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-' . $fncIcon . '"/></svg></span>';
        $fncFileLoc = $fncFilePath . $fncFile;
    else:
        $fncDelHide = ' hide';
        $fncUpBtn = '<span class="btn btn-circle"><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-' . $fncIcon . '"/></svg></span>';
        if ($fncAccept == 'accept="image/*"'):
            $fncFileLoc = '/wtk/imgs/noPhotoSmall.gif';
        else:
            $fncFileLoc = '';
        endif;
    endif;
    if ($fncAllowDelete == 'Y'):
        $fncDelBtn = '&nbsp;&nbsp;&nbsp;<a onclick="JavaScript:' . "wtkfDelFile('$gloId','$fncFormId')\"";
        $fncDelBtn .= ' title="delete file" id="wtkfDelBtn' . $fncFormId . '" class="btn btn-circle btn-error' . $fncDelHide . '">';
        $fncDelBtn .= '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-trash"/></svg></a>';
    else:
        $fncDelBtn = '';
    endif;

    $fncWidth = '36px';
    if ($fncAccept == 'accept="image/*"'):
        if ($fncThumbnail == 'Y'):
            $fncShowFile  = '<div id="wtkfImgContainer' . $fncFormId . '">' . "\n";
            $fncShowFile .= '<img id="imgPreview' . $fncFormId . '" src="' . $fncFileLoc . '" class="responsive-img" width="150">';
            $fncShowFile .= '</div>';
            $fncWidth = '150px';
            $gloHasImage = true;
        else:
            $fncShowFile = '<a id="filePreview' . $fncFormId . '"';
            if ($gloWTKmode == 'ADD'):
                $fncShowFile .= ' target="_blank" class="hide"';
            else:
                $fncViewLink = wtkSqlValue('ViewLink');
                if ($fncViewLink == ''):
                    $fncViewLink = 0;
                endif;
                $fncShowFile .= " onclick=\"JavaScript:wtkGoToURL('/wtk/viewFile',$fncViewLink,0,'targetBlank')\"";
                if ($fncFileLoc == ''):
                    $fncShowFile .= ' class="hide"';
                endif;
            endif;
            $fncShowFile .= '><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-eye"/></svg></a>';
        endif;
    else:
        $fncViewLink  = wtkSqlValue('ViewLink');
        if ($fncViewLink == ''):
            $fncViewLink = 0;
        endif;
        $fncShowFile  = '<a id="filePreview' . $fncFormId . '" target="_blank"';
        if ($gloWTKmode == 'ADD'):
            $fncShowFile .= ' class="hide"';
        else:
            $fncShowFile .= " onclick=\"JavaScript:wtkGoToURL('/wtk/viewFile',$fncViewLink,0,'targetBlank')\"";
            if ($fncFileLoc == ''):
                $fncShowFile .= ' class="hide"';
            endif;
        endif;
        $fncShowFile .= '><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-eye"/></svg></a>';
    endif;
    $fncHtm =<<<htmVAR
<div class="input-field col $fncColSize">
    <table><tr><td width="$fncWidth">
        $fncShowFile
      </td><td width="144px">
htmVAR;
    if ($gloForceRO == true):
        $fncHtm .= '&nbsp;';
    else:
        if ($gloWTKmode != 'ADD'):
            $fncHtm .= wtkFormPrepUpdField($fncTable, $fncFileName, 'file');
        endif;
        if ($gloAccessMethod == 'ios'):
            $fncHtm .= ' <a onclick="JavaScript:wtkSelectImage(' . $gloId .')" title="upload photo"';
            $fncHtm .= ' id="wtkfBtn' . $gloId . '" class="btn btn-circle"><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-file-upload"/></svg>iOS Upload</a>' . "\n";
            // 2ENHANCE current methodology only allows one files per data row, cannot have table with two files stored in single row
            // this is possible for websites, but not currently in mobile app SDK
            $fncHtm .= $fncDelBtn;
        else:
            $fncHtm .=<<<htmVAR
          <label class="fileUpload" for="wtkUpload$fncFormId">
              <input type="file" id="wtkUpload$fncFormId" name="wtkUpload$fncFormId" $fncAccept style="display: none;">
              $fncUpBtn
              $fncLabel
          </label>$fncDelBtn
htmVAR;
        endif;
    endif;
    if ($fncShowOneClickUpload == 'Y'):
        $fncHtm .= '</td><td>' . "\n";
        $fncHtm .= '<a onclick="JavaScript:wtkfFileUpload(\'\',\'' . $fncFormId . '\')"';
        $fncHtm .= ' title="upload photo" id="wtkfUploadBtn' . $fncFormId . '"';
        $fncHtm .= ' class="btn btn-success hidden"><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-file-upload"/></svg>Upload</a>' . "\n";
    endif;
    $fncHtm .= '</td></tr>' . "\n" . '</table>' . "\n";
    $fncEncodedTable = wtkEncode($fncTable);
    $fncEncodedUID = wtkEncode('UID'); // 2ENHANCE make Global variable
    // add this line to enable debugging
    // <div id="debugLogDIV"></div>
    if ($gloAccessMethod == 'ios'):
        $fncEncodedTable = wtkEncode($fncTable);
        $fncEncodedUID = wtkEncode('UID'); // 2ENHANCE make Global variable
        $fncHtm .=<<<htmVAR
    <input type="hidden" id="wtkfMode$gloId" name="wtkfMode$gloId" value="$gloWTKmode">
	<input type="hidden" id="wtkfTable$gloId" name="wtkfTable$gloId" value="$fncEncodedTable">
	<input type="hidden" id="wtkfUID$gloId" name="wtkfUID$gloId" value="$fncEncodedUID">
	<input type="hidden" id="wtkfID$gloId" name="wtkfID$gloId" value="$gloId">
	<input type="hidden" id="wtkfPath$gloId" name="wtkfPath$gloId" value="$fncFilePath">
	<input type="hidden" id="wtkfColPath$gloId" name="wtkfColPath$gloId" value="$fncColPath">
	<input type="hidden" id="wtkfColFile$gloId" name="wtkfColFile$gloId" value="$fncFileName">
    <input type="hidden" id="wtkfDelete$gloId" name="wtkfDelete$gloId" value="$fncFile">
htmVAR;
    else:
        $fncHtm .=<<<htmVAR
    <input type="hidden" id="wtkfMode$fncFormId" name="wtkfMode$fncFormId" value="$gloWTKmode">
	<input type="hidden" id="wtkfTable$fncFormId" name="wtkfTable$fncFormId" value="$fncEncodedTable">
	<input type="hidden" id="wtkfUID$fncFormId" name="wtkfUID$fncFormId" value="$fncEncodedUID">
	<input type="hidden" id="wtkfID$fncFormId" name="wtkfID$fncFormId" value="$gloId">
	<input type="hidden" id="wtkfPath$fncFormId" name="wtkfPath$fncFormId" value="$fncFilePath">
	<input type="hidden" id="wtkfColPath$fncFormId" name="wtkfColPath$fncFormId" value="$fncColPath">
	<input type="hidden" id="wtkfColFile$fncFormId" name="wtkfColFile$fncFormId" value="$fncFileName">
    <input type="hidden" id="wtkfDelete$fncFormId" name="wtkfDelete$fncFormId" value="$fncFile">
htmVAR;
    endif;
    $fncHtm .=<<<htmVAR
    <input type="hidden" id="wtkfRefresh$fncFormId" name="wtkfRefresh$fncFormId" value="$fncRefresh">
    <input type="hidden" id="wtkfOrigName$fncFormId" name="wtkfOrigName$fncFormId" value="">
    <div id="photoProgressDIV$fncFormId" class="progress hide">
        <div id="photoProgress$fncFormId" class="determinate" style="width: 25%"></div>
    </div>
    <div id="uploadStatus$fncFormId"></div>
    <span id="uploadFileSize$fncFormId"></span>
    <span id="uploadProgress$fncFormId"></span>
</div>
htmVAR;
    if ($gloForceRO == true):
        $fncHtm = wtkReplace($fncHtm, '<label class="fileUpload"','<p>' . $fncLabel . '</p><label class="hide"');
    endif;
    if ($gloHasFileUploads != ''):
        $gloHasFileUploads .= ',';
    endif;
    $gloHasFileUploads .= $fncFormId;
    return $fncHtm;
} // wtkFormFile


/**
 * Create data-driven menu based on Menu Set.
 *
 * This uses the `wtkMenuSets` data table and the child tables of `wtkMenuGroups`, `wtkMenuItems`,
 * and `wtkPages`.  This creates both the top-menu and in case the screen is
 * small, it creates the side-bar menu code as well.
 *
 * The menu structures are available in the WTK /admin/ website.
 *
 * @param string $fncMenuSetName must match `wtkMenuSets`.`MenuName`
 * @param string $fncSpecial defaults to 'N', if set to 'BreadCrumbs' then adds code for Breadcrumbs to use
 * @return html of drop and side-menus
 */
function wtkMenu($fncMenuSetName, $fncSpecial = 'N') {
    global $gloPrinting, $gloWTKobjConn;
    $fncMenu = '';
    if (!$gloPrinting):  // only do if not printing
        $fncSQL  = 'SELECT COUNT(*) FROM `wtkMenuSets` m';
        $fncSQL .= ' INNER JOIN `wtkMenuGroups` g ON g.`MenuUID` = m.`UID`';
        $fncSQL .= ' WHERE m.`MenuName` = ?';
        if (wtkSqlGetOneResult($fncSQL,[$fncMenuSetName]) > 0): // prevent error if no menu exists yet
            $fncSQL =<<<SQLVAR
SELECT COALESCE(i.`MenuGroupUID`, 0) AS `MenuGroupUID`, g.`GroupName`,
    g.`GroupURL`, p.`PageName`, p.`FileName`, p.`Path`, i.`ShowDividerAbove`
 FROM `wtkMenuSets` m
    INNER JOIN `wtkMenuGroups` g ON g.`MenuUID` = m.`UID`
    LEFT OUTER JOIN `wtkMenuItems` i ON i.`MenuGroupUID` = g.`UID`
    LEFT OUTER JOIN `wtkPages` p ON p.`UID` = i.`PgUID`
 WHERE m.`MenuName` = ?
    AND g.`DelDate` IS NULL
    AND i.`DelDate` IS NULL
 ORDER BY g.`Priority` ASC, i.`Priority` ASC
SQLVAR;
            $fncSQL = wtkSqlPrep($fncSQL);
            $fncPriorGroupUID = -1;
            $fncSideMenu = '';
            $pgGroupingOpen = false;
            $fncTopMenu = '';
            $fncPDO = $gloWTKobjConn->prepare($fncSQL);
            $fncPDO->execute([$fncMenuSetName]);
            $fncIsGroupURL = true;
            $fncFile = '';
            while ($fncRow = $fncPDO->fetch()):
                if (is_null($fncRow['GroupURL'])):
                    $fncIsGroupURL = false;
                    $fncFile = $fncRow['FileName'];
                    wtkTimeTrack('$fncFile 1 = ' . $fncFile);
                else:
                    $fncIsGroupURL = true;
                    $fncFile = $fncRow['GroupURL'];
                    wtkTimeTrack('$fncFile 2 = ' . $fncFile);
                endif;
                // BEGIN determine ajaxGo values and construct
                if ($fncFile != ''):
                    if (strpos($fncFile, '.php?id=') !== false):
                        $fncFile = wtkReplace($fncFile, '.php?id=', "','");
                        $fncFile = wtkReplace($fncFile, '&rng=', "','");
                    endif;
                    if (strpos($fncFile, '.php?rng=') !== false):
                        //.php?id=12&rng=3
                        $fncFile = wtkReplace($fncFile, '.php?rng=', "',0,'");
                    endif;
                endif;
                //  END  determine ajaxGo values and construct
                if ($fncRow['MenuGroupUID'] != $fncPriorGroupUID): // New Droplist so build beginning
                    $fncGroupName = trim($fncRow['GroupName']);
                    if (strpos($fncTopMenu,'<details>') !== false):
                        $fncTopMenu .= '        </ul>' . "\n";
                        $fncTopMenu .= '    </details>' . "\n";
                        $fncTopMenu .= '</li>' . "\n";
                    endif;
                    if ($pgGroupingOpen):
                        $fncSideMenu .= '        </ul>' . "\n";
                        $fncSideMenu .= '    </li>' . "\n";
                    endif;

                    $fncPriorGroupUID = $fncRow['MenuGroupUID'];
                    if ($fncIsGroupURL == false):
                        if ($fncTopMenu != ''):
                            $fncTopMenu .= '<li>' . "\n";
                            $fncTopMenu .= "    <details><summary>$fncGroupName</summary>" . "\n";
//                            $fncTopMenu .= '  </ul>' . "\n";
//                            $fncTopMenu .= '</details>' . "\n";
                        endif;  // $fncDrops != ''
                        $fncSideMenu .= '<li><a>' . $fncGroupName . '</a>' . "\n";

                        $fncLink  = '        <ul class="p-2 min-w-max">' . "\n";
                        $fncLink .= '            <li><a onclick="Javascript:closeParentDetails(this);ajaxGo(\'' . $fncFile . '\');">';
                        $fncLink .= $fncRow['PageName'] . '</a></li>' . "\n";

                        $fncTopMenu  .= $fncLink;
                        $fncSideMenu .= $fncLink;

//                        $fncTmp  = '<li><a data-target="dropdown';
//                        $fncTmp .= $fncPriorGroupUID . '">' . $fncGroupName;
//                        $fncMenu .= $fncTmp;
                    else:   // Direct Link
                        switch ($fncFile):
                            case 'logout':
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);wtkLogout();">';
                                break;
                            case 'dashboard':
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);goHome();">';
                                break;
                            default:
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);ajaxGo(\'' . $fncFile . '\');">';
                                break;
                        endswitch;
                        $fncLink .= $fncRow['GroupName'] . '</a></li>' . "\n";
                        $fncTopMenu  .= $fncLink;
                        $fncSideMenu .= $fncLink;
                    endif;  // is_null($fncRow['GroupURL'])
                else:   // Not $fncRow['MenuGroupUID'] != $fncPriorGroupUID
                    if ($fncIsGroupURL == false):
//                      $fncFile = $fncRow['FileName']; // 2VERIFY if remove this line, it breaks
                        $fncLink = '';
                        $fncLink .= '    <li><a onclick="Javascript:closeParentDetails(this);ajaxGo(\'' . $fncFile . '\');">';
                        $fncLink .= $fncRow['PageName'] . '</a></li>' . "\n";
                        $pgGroupingOpen = true;
                    else:
                        switch ($fncFile):
                            case 'logout':
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);wtkLogout();">';
                                break;
                            case 'dashboard':
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);goHome();">';
                                break;
                            default:
                                $fncLink  = '    <li><a onclick="Javascript:closeParentDetails(this);ajaxGo(\'' . $fncFile . '\');">';
                                break;
                        endswitch;
                        $fncLink .= $fncRow['GroupName'] . '</a></li>' . "\n";
                    endif;
                    $fncTopMenu  .= $fncLink;
                    $fncSideMenu .= $fncLink;
                endif;  // $fncRow['MenuGroupUID'] != $fncPriorGroupUID
            endwhile;
            if ($pgGroupingOpen):
                $fncTopMenu .= '  </ul>';
            endif;

            $fncMenu  =<<<htmVAR
<!--start navbar-->
    <div class="navbar bg-base-100 shadow-sm">
        <div class="md:hidden">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost">
                    <svg class="wtk-icon"><use href="/imgs/icons.svg#icon-menu"></use></svg>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                    $fncSideMenu
                </ul>
            </div>
        </div>
        <div class="hidden md:flex justify-center w-full">
            <ul class="menu menu-horizontal px-1">
                $fncTopMenu
            </ul>
        </div>
    </div>
<!-- end navbar -->
<script>
  function closeParentDetails(el) {
    const details = el.closest('details');
    if (details) details.removeAttribute('open');
  }
</script>

htmVAR;
        endif;  // wtkSqlGetOneResult($fncSQL) > 0
    endif;  // !$gloPrinting
    $fncMenu = wtkReplace($fncMenu, "ajaxGo('ajxLogout')",'wtkLogout()');
    $fncMenu = wtkReplace($fncMenu, '>Logout</a>','><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-logout"></use></svg></a>');
    return $fncMenu;
}  // end of wtkMenu
?>