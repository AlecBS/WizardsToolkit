<?php
/**
* Wizard's Toolkit functions for MaterializeCSS and HTML template methodology.
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
    global $gloPrinting;
    $fncMenu = '';
    if (!$gloPrinting):  // only do if not printing
        $fncSQL  = 'SELECT COUNT(*) FROM `wtkMenuSets` m';
        $fncSQL .= ' INNER JOIN `wtkMenuGroups` g ON g.`MenuUID` = m.`UID`';
        $fncSQL .= ' WHERE m.`MenuName` = ?';
        if (wtkSqlGetOneResult($fncSQL,[$fncMenuSetName]) > 0): // prevent error if no menu exists yet
            global $gloWTKobjConn;
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
            $fncLastDrop = 'N'; // last menu group was a drop list
            $fncDrops = '';
            $fncSideNav = '';
            $fncMenu .= '<div class="navbar-fixed">' . "\n";
            $fncMenu .= '  <nav class="navbar navbar-home">' . "\n";
            $fncMenu .= '   <div class="nav-wrapper">' . "\n";
            if ($fncSpecial == 'BreadCrumbs'):
                $fncMenu .= '     <div class="row">' . "\n";
                $fncMenu .= '        <div class="col m5 s6">' . "\n";
                $fncMenu .= '<div id="myBreadCrumbs"></div>' . "\n";
                $fncMenu .= '        </div>' . "\n";
                $fncMenu .= '        <div class="col m7 s6">' . "\n";
            endif;
            $fncMenu .= '       <a data-target="phoneSideBar" class="sidenav-trigger right"><i class="material-icons">menu</i></a>' . "\n";
            if ($fncMenuSetName == 'WTK-Admin'):
                $fncMenu .= '  &nbsp;&nbsp;' . "\n";
                $fncMenu .= '       <a class="brand-logo" onclick="JavaScript:ajaxGo(\'/wtk/userEdit\')"><i class="material-icons">account_circle</i></a>' . "\n";
            endif;
            $fncMenu .= '       <ul class="right hide-on-med-and-down">' . "\n";
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
                    $fncPriorGroupUID = $fncRow['MenuGroupUID'];
                    if ($fncSideNav != ''):
                        if ($fncLastDrop == 'N'):
                            $fncSideNav .= '<li>' . "\n";
                        else:
                            $fncSideNav .= '      </ul>' . "\n";
                            $fncSideNav .= '    </div>' . "\n";
                            $fncSideNav .= '  </li>' . "\n";
                            $fncSideNav .= '</ul>' . "\n";
                        endif;
                    endif;
                    if ($fncIsGroupURL == false):
                        if ($fncDrops != ''):
                            $fncDrops .= '  </ul>' . "\n";
                        endif;  // $fncDrops != ''
                        if ($fncSideNav != ''):
                        endif;
                        $fncLastDrop = 'Y';
                        $fncGroupName = trim($fncRow['GroupName']);
                        $fncDrops .= '  <ul id="dropdown' . $fncPriorGroupUID . '" class="dropdown-content">' . "\n";
                        $fncLink  = '    <li><a onclick="Javascript:ajaxGo(\'' . $fncFile . '\');">';
                        $fncLink .= $fncRow['PageName'] . '</a></li>' . "\n";
                        $fncDrops .= $fncLink;

                        $fncTmp  = '<li><a class="dropdown-trigger" data-target="dropdown';
                        $fncTmp .= $fncPriorGroupUID . '">' . $fncGroupName;
                        $fncTmp .= '<i class="material-icons top-down">arrow_drop_down</i></a></li>' . "\n";
                        $fncMenu .= $fncTmp;
                        $fncSideNav .=<<<htmVAR
<ul class="collapsible">
  <li>
    <a class="collapsible-header"><i class="material-icons sideDown">arrow_drop_down</i>$fncGroupName</a>
    <div class="collapsible-body">
      <ul>
$fncLink
htmVAR;
                    else:   // Direct Link
                        $fncLastDrop = 'N';
                        switch ($fncFile):
                            case 'logout':
                                $fncTmp  = '    <li><a onclick="Javascript:wtkLogout();">';
                                break;
                            case 'dashboard':
                                $fncTmp  = '    <li><a onclick="Javascript:goHome();">';
                                break;
                            default:
                                $fncTmp  = '    <li><a onclick="Javascript:ajaxGo(\'' . $fncFile . '\');">';
                                break;
                        endswitch;
                        $fncTmp .= $fncRow['GroupName'] . '</a></li>' . "\n";
                        $fncMenu .= $fncTmp;
                        if ($fncSideNav != ''):
                            $fncSideNav .= '</li>' . "\n";
                            $fncSideNav .= $fncTmp;
                        endif;
                    endif;  // is_null($fncRow['GroupURL'])
                else:   // Not $fncRow['MenuGroupUID'] != $fncPriorGroupUID
                    if ($fncIsGroupURL == false):
//                      $fncFile = $fncRow['FileName']; // 2VERIFY if remove this line, it breaks
                        $fncTmp = '';
                        if ($fncRow['ShowDividerAbove'] == 'Y'):
                            $fncTmp .= '    <li class="divider"></li>' . "\n";
                        endif;
                        $fncTmp .= '    <li><a onclick="Javascript:ajaxGo(\'' . $fncFile . '\');">';
                        $fncTmp .= $fncRow['PageName'] . '</a></li>' . "\n";
                        $fncDrops .= $fncTmp;
                    else:
                        $fncLastDrop = 'N';
                        switch ($fncFile):
                            case 'logout':
                                $fncTmp  = '    <li><a onclick="Javascript:wtkLogout();">';
                                break;
                            case 'dashboard':
                                $fncTmp  = '    <li><a onclick="Javascript:goHome();">';
                                break;
                            default:
                                $fncTmp  = '    <li><a onclick="Javascript:ajaxGo(\'' . $fncFile . '\');">';
                                break;
                        endswitch;
                        $fncTmp .= $fncRow['GroupName'] . '</a></li>' . "\n";
                        $fncMenu .= $fncTmp;
                    endif;
                    $fncSideNav .= $fncTmp;
                endif;  // $fncRow['MenuGroupUID'] != $fncPriorGroupUID
            endwhile;
            if ($fncSpecial == 'BreadCrumbs'):
                $fncMenu .= '        </div>' . "\n";
                $fncMenu .= '     </div>' . "\n";
            endif;
            if ($fncDrops != ''):
                // if ($fncLastDrop == 'Y'):
                    $fncDrops .= '  </ul>' . "\n";
                // endif;
                $fncMenu = $fncDrops . $fncMenu;
            endif;  // ($fncLastDrop == 'Y')
            if (($fncSideNav != '') && ($fncLastDrop == 'Y')):
                $fncSideNav .= '      </ul>' . "\n";
                $fncSideNav .= '    </div>' . "\n";
                $fncSideNav .= '  </li>' . "\n";
                $fncSideNav .= '</ul>' . "\n";
                $fncSideNav .= '</li>' . "\n";
            endif;  // $fncSideNav != ''

            $fncMenu .=<<<htmVAR
       </ul>
   </div>
</nav>
</div>
<!-- Next is Side Menu for Phones -->
<div class="sidebar-panel">
    <ul id="phoneSideBar" class="sidenav side-nav">
        <li class="no-padding">
$fncSideNav
     </ul>
</div>
htmVAR;
//          $fncMenu = wtkReplace($fncMenu, '<li>' . "\n" . '</li>', '');
            if ($fncDrops != ''):
                $fncMenu .= '<input type="hidden" id="wtkDropDown" value="Y">' . "\n";
            endif;
        endif;  // wtkSqlGetOneResult($fncSQL) > 0
    endif;  // !$gloPrinting
    $fncMenu = wtkReplace($fncMenu, "ajaxGo('ajxLogout')",'wtkLogout()');
    return $fncMenu;
}  // end of wtkMenu

/**
* Creates an input text field.
*
* If a special Type is passed then additional validation and class styles are added.
* Special Types include:
* <ul><li>date</li><li>time</li><li>number</li><li>email</li><li>tel</li><li>timepicker</li></ul>
*
* @param string $fncTable data table
* @param string $fncColName data column name
* @param string $fncType becomes type="$fncType" and triggers enhancements of JS validation and class styles
* @param string $fncLabel optionally passed, if not then uses $fncColName
* @param string $fncColSize defaults to 'm6 s12'
* @param string $fncRequired defaults to 'N'
* @param string $fncHelpText defaults to '', pass to add <span class="helper-text">
* @uses function wtkFormLabel
* @uses function wtkDisplayData
* @return html returns surrounding HTML for input form field
*/
function wtkFormText($fncTable, $fncColName, $fncType = 'text', $fncLabel = '', $fncColSize = 'm6 s12', $fncRequired = 'N', $fncHelpText = '') {
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
    $fncClassActive = '';
    $fncIcon = '';
    $fncExtra = '';
    $fncShowPW = '';
    switch (strtolower($fncType)):
        case 'password':
            $fncShowPW  = "\n" . '        <span class="material-icons toggle-password" data-toggle="#';
            $fncShowPW .= $fncFormId;
            $fncShowPW .= '" style="cursor:pointer;">visibility</span>';
            break;
        case 'date':
//2FIX      $fncType = 'text';
            if ($gloForceRO == false):
                $fncExtra = ' class="datepicker"';
                global $gloDatePickExist;
                $gloDatePickExist = true;
            endif;
            break;
        case 'time':
            $fncClassActive = 'class="active"';
            $fncExtra = ' class="time-width"';
            break;
        case 'dollar':
            $fncIcon = "\n" . '<i class="material-icons prefix">attach_money</i>';
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
        $fncHelpText = "\n" . '<span class="helper-text">' . $fncHelpText. '</span>';
    endif;
    $fncHtm =<<<htmVAR
    <div class="input-field col $fncColSize">$fncIcon
        <input$fncDisabled type="$fncType"$fncExtra id="$fncFormId" name="$fncFormId" value="@$fncColName@">
        <label for="$fncFormId">$fncLabel</label>$fncHelpText$fncShowPW
    </div>
htmVAR;
    $fncHtm = wtkDisplayData($fncColName, $fncHtm, $fncTable, '', $fncType) . "\n";
    if ($fncType == 'time'):
        $fncHtm = wtkReplace($fncHtm, '<label for="','<label class="active" for="');
    endif;
    return $fncHtm;
}  // wtkFormText

/**
* Creates a textarea field.
*
* @param string $fncTable data table
* @param string $fncColName data column name
* @param string $fncLabel optionally passed, if not then uses $fncColName
* @param string $fncColSize defaults to 'm6 s12'
* @param string $fncRequired defaults to 'N'
* @param string $fncHelpText defaults to '', pass to add <span class="helper-text">
* @uses function wtkFormLabel
* @uses function wtkDisplayData
* @return html returns surrounding HTML for textarea
*/
function wtkFormTextArea($fncTable, $fncColName, $fncLabel = '', $fncColSize = 'm12 s12', $fncRequired = 'N', $fncHelpText = '') {
    global $gloForceRO, $gloHasTextArea;
    $fncLabel = wtkFormLabel($fncLabel, $fncColName);
    $fncFormId = 'wtk' . $fncTable . $fncColName;
    if ($gloForceRO == true):
        $fncValue = wtkSqlValue($fncColName);
        $fncValue = nl2br($fncValue);
        $fncHtm =<<<htmVAR
    <div class="input-field col $fncColSize">
      <label for="$fncFormId" class="active">$fncLabel</label><br>
      <span style="color: #9e9e9e">
      $fncValue
      </span>
    </div>
htmVAR;
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
        if ($fncHelpText != ''):
            if (strlen($fncHelpText) < 121):
                $fncHelpText = wtkLang($fncHelpText);
            endif;
            $fncHelpText = "\n" . '<span class="helper-text">' . $fncHelpText. '</span>';
        endif;
        $fncHtm =<<<htmVAR
    <div class="input-field col $fncColSize">
      <textarea$fncSetReguired id="$fncFormId" name="$fncFormId" class="materialize-textarea">@$fncColName@</textarea>
      <label for="$fncFormId" style="margin-top:-6px">$fncLabel</label>$fncHelpText
    </div>
htmVAR;
        $fncHtm = wtkDisplayData($fncColName, $fncHtm, $fncTable,'','textarea') . "\n";
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
        $fncColSize = 'm6 s12', $fncDisplay = 'V') {
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
<div class="input-field col $fncColSize">
    $fncLabel
    <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncValue">
htmVAR;

    if ($fncDisplay != 'V'):
        $fncHtm .= '<table class="table-basic"><tr><td>' . "\n";
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
    <p>
      <label for="$fncFormId$fncInc">
        <input class="with-gap" type="radio" id="$fncFormId$fncInc" name="$fncFormId" value="$RadioOption"$fncDisabled $fncChoice/>
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
function wtkFormCheckbox($fncTable, $fncColName, $fncLabel, $fncValueArray, $fncColSize = 'm6 s12') {
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
<div class="input-field col $fncColSize">
    <p><label for="$fncFormId">
        <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncHidValue">
        <input type="checkbox" value="$fncCheckValue" id="$fncFormId" name="$fncFormId"$fncDisabled $fncChecked>
        <span>$fncLabel</span>
    </label></p>
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
    <div class="input-field col $fncColSize">
        <span>$fncLabel</span>
        <div class="switch">
          <label for="$fncFormId">Off
            <input type="hidden" id="Orig$fncFormId" name="Orig$fncFormId" value="$fncHidValue">
            <input type="checkbox" value="$fncCheckValue" id="$fncFormId" name="$fncFormId"$fncDisabled $fncChecked>
            <span class="lever"></span>
            On</label>
        </div>
    </div>
htmVAR;
    return $fncResult . "\n";
}  // end of wtkFormSwitch

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
function wtkFormSelect($fncTable, $fncColName, $fncSQL, $fncFilter, $fncDisplayField, $fncValueField, $fncLabel = '', $fncColSize = 'm6 s12', $fncShowBlank = 'N') {
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
<div class="input-field col $fncColSize">
    $fncHidden
    <select$fncDisabled id="$fncFormId" name="$fncFormId">
        $fncList
    </select>
    <label for="$fncFormId" class="active">$fncLabel</label>
</div>

htmVAR;
    return $fncHtm;
} // wtkFormSelect

/**
 * Creates html for Modal User Select feature
 *
 * The modal "modalUsers" must be in the spa.htm or spaAdmin.htm for this to work.
 * Matching JS functions are in wtkAdmin.js to allow easy User selection via typing a few letters of first name.
 * Works well for large data sets of wtkUsers. Excellent to allow selecting associated user for different parent tables.
 *
 * Example calling method:
 * <code>
 * $pgHtm .= wtkFormUserSelect('SomeTableName', 'UserUID', 'staff', 'StaffName');
 * </code>
 *
 * @param string $fncTable data table
 * @param string $fncColumn data column name
 * @param string $fncUserFilter passed to /admin/ajxUserLookup.php to show filtered wtkUsers
 * @param string $fncDisplayName used both for name displayed and Label
 * @return html returns disabled input field showing user name with icon to open a modal window and select users
 *@uses function wtkFormPrepUpdField
 */
function wtkFormUserSelect($fncTable, $fncColumn, $fncUserFilter, $fncDisplayName){
    wtkFormPrepUpdField($fncTable, $fncColumn, 'text'); // so will save
    $fncUserUID = wtkSqlValue($fncColumn);
    $fncUserName = wtkSqlValue($fncDisplayName);
    $fncLabel = wtkInsertSpaces($fncDisplayName);
    $fncHtm =<<<htmVAR
    <input type="hidden" name="Origwtk$fncTable$fncColumn" id="Origwtk$fncTable$fncColumn" value="$fncUserUID">
    <input type="hidden" name="wtk$fncTable$fncColumn" id="wtk$fncTable$fncColumn" value="$fncUserUID">
    <div class="input-field col m3 s12">
        <i class="material-icons prefix clickable" onclick="JavaScript:openUserLookup('$fncUserFilter', '$fncDisplayName', 'wtk$fncTable$fncColumn')">search</i>
        <input type="text" style="color:#000 !important;" disabled name="$fncDisplayName" id="$fncDisplayName" value="$fncUserName">
        <label for="InstructorName" class="active">$fncLabel</label>
    </div>
htmVAR;
    return $fncHtm;
} // wtkFormUserSelect

/**
* Similar to wtkFormSelect but this one shows font styles while displaying select drop list of fonts
*
* The SQL query to use to generate a list of values and display values needs to be passed.
*
* Example calling method:
* <code>
* $pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = ? ORDER BY `LookupDisplay` ASC";<br>
* $pgTmp .= wtkFormSelectFont('LandingPage', 'BodyFont', $pgSQL, ['Font'], 'LookupDisplay', 'LookupValue', 'Body Font', 'm6 s12');<br>
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
* @uses function wtkFormLabel
* @uses function wtkFormPrepUpdField
* @return html returns surrounding HTML for checkbox field
*/
function wtkFormSelectFont($fncTable, $fncColName, $fncSQL, $fncFilter, $fncDisplayField, $fncValueField, $fncLabel = '', $fncColSize = 'm6 s12') {
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
    // ABS 07/11/21  BEGIN modified wtkGetSelectOptions section
    global $gloWTKobjConn;
    $fncOptions = '';
    $fncObjRS = $gloWTKobjConn->prepare($fncSQL);
    $fncObjRS->execute($fncFilter);
    while ($fncRow = $fncObjRS->fetch()):
        $fncOptionValue = $fncRow[$fncValueField];
        $fncOptions .= '<option value="' . $fncOptionValue . '"';
        if ($fncOptionValue == $fncValue):
            $fncOptions .= ' SELECTED';
        endif;
        $fncOptions .= ' style="font-family:\'' . $fncOptionValue . '\'"';
        $fncOptions .= '>';
        $fncOptions .= $fncRow[$fncDisplayField];
        $fncOptions .= "</option>\n";
    endwhile;
    unset($fncObjRS);
    // ABS 07/11/21   END  modified wtkGetSelectOptions section

    $fncHtm =<<<htmVAR
<div class="input-field col $fncColSize">
    $fncHidden
    <select$fncDisabled id="$fncFormId" name="$fncFormId" class="browser-default">
        $fncOptions
    </select>
    <label for="$fncFormId" class="active">$fncLabel</label>
</div>

htmVAR;
    return $fncHtm;
} // wtkFormSelectFont

/**
* Used to create file input field for uploading files.
*
* Example calling method:
* <code>
* $pgCamera = wtkFileUpload('wtkUsers','FilePath','../imgs/user/','NewFileName','myPhoto');<br>
* $pgHtm = wtkReplace($pgHtm, '@wtkFileUpload@', $pgCamera);<br>
* </code>
*
* @param string $fncTable data table
* @param string $fncColPath stuff
* @param string $fncFilePath
* @param string $fncFileName stuff
* @param string $fncRefresh defaults to '' blank
* @return html returns surrounding HTML for input type="file"
*/
function wtkFileUpload($fncTable, $fncColPath, $fncFilePath, $fncFileName, $fncRefresh = '', $fncFormId = '') {
    // THIS IS NOT COMPLETE - JUST STARTING ON THIS
    global $gloForceRO;
    $fncFile = wtkSqlValue($fncFileName);
    if ($fncFile != ''):
        $fncFileLoc = $fncFilePath . $fncFile;
    else:
        $fncFileLoc = '/wtk/imgs/noPhotoSmall.gif';
    endif;
    $fncHtm = '<img src="' . $fncFileLoc . '" id="imgPreview" class="circle responsive-img">';
    if ($gloForceRO == false):
        $fncHtm .= wtkFormPrepUpdField($fncTable, $fncFileName, 'file');
        $fncHtm .=<<<htmVAR
        <label for="wtkUpload" class="profile-icon-upload">
            <input type="file" id="wtkUpload" name="wtkUpload" accept="image/*" style="display: none;">
            <i class="material-icons">camera_alt</i>
        </label>
        <div id="wtkfPhotoDIV" class="hide">
            <div class="row z-depth-3 hide" id="wtkfDelBtn">
                <div class="col s12 center" style="margin-top: 18px; margin-bottom: 18px">
                    <a onclick="JavaScript:wtkfDelFile('$fncFormId')" title="delete file" class="btn-floating red"><i class="material-icons white-text">delete_forever</i></a>
                </div>
            </div>
            <div class="row z-depth-3" id="wtkf2btns">
                <div class="col s6 center" style="margin-top: 18px; margin-bottom: 18px">
                    <a onclick="JavaScript:wtkfRevertImg()" title="revert image" id="wtkfFileBtn" class="btn btn-floating"><i class="material-icons small">replay</i></a>
                </div>
                <div class="col s6 center" style="margin-top: 18px; margin-bottom: 18px">
                    <a onclick="JavaScript:wtkfPhoto()" title="save photo" id="wtkfSaveBtn" class="btn btn-floating"><i class="material-icons small">save</i></a>
                </div>
            </div>
        </div>

        <input type="hidden" id="wtkfColPath" name="wtkfColPath" value="$fncColPath">
        <input type="hidden" id="wtkfColFile" name="wtkfColFile" value="$fncFileName">
        <input type="hidden" id="wtkfPath" name="wtkfPath" value="$fncFilePath">
        <input type="hidden" id="wtkfRefresh" name="wtkfRefresh" value="$fncRefresh">
        <input type="hidden" id="wtkfDelete" name="wtkfDelete" value="$fncFile">
        <input type="hidden" id="wtkfPhoto" name="wtkfPhoto" value="Y">
        <div id="photoProgressDIV" class="progress hide">
            <div id="photoProgress" class="determinate" style="width: 0%"></div>
        </div>
        <div id="uploadStatus"></div>
        <span id="uploadFileSize"></span>
        <span id="uploadProgress"></span>
    <input type="hidden" id="wtkfOrigPhoto" name="wtkfOrigPhoto" value="$fncFileLoc">
htmVAR;
    endif;
    return $fncHtm;
} // wtkFileUpload

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
    $fncLabel = '', $fncColSize = 'm6 s12', $fncRefresh = '',
    $fncShowOneClickUpload = 'N', $fncAccept = 'accept="image/*"',
    $fncThumbnail = 'Y', $fncFormId = '1', $fncAllowDelete = 'Y') {

    global $gloWTKmode, $gloForceRO, $gloHasImage, $gloIsFileUploadForm,
        $gloAccessMethod, $gloId, $gloHasFileUploads;

    $gloIsFileUploadForm = true;

    $fncLabel = wtkFormLabel($fncLabel, $fncFileName);
    $fncFile  = wtkSqlValue($fncFileName);
    switch ($fncAccept):
        case 'accept="image/*"':
            $fncIcon = 'camera_alt';
            break;
        case 'accept=".pdf"':
            $fncIcon = 'picture_as_pdf';
            break;
        default:
            $fncIcon = 'file_upload';
            break;
    endswitch;
    if ($fncFile != ''):
        $fncDelHide = '';
        $fncUpBtn = '<span id="wtkfAddBtn' . $fncFormId . '" class="btn-floating hide"><i class="material-icons">' . $fncIcon . '</i></span>';
        $fncFileLoc = $fncFilePath . $fncFile;
    else:
        $fncDelHide = ' hide';
        $fncUpBtn = '<span class="btn-floating"><i class="material-icons">' . $fncIcon . '</i></span>';
        if ($fncAccept == 'accept="image/*"'):
            $fncFileLoc = '/wtk/imgs/noPhotoSmall.gif';
        else:
            $fncFileLoc = '';
        endif;
    endif;
    if ($fncAllowDelete == 'Y'):
        $fncDelBtn = '&nbsp;&nbsp;&nbsp;<a onclick="JavaScript:' . "wtkfDelFile('$gloId','$fncFormId')\"";
        $fncDelBtn .= ' title="delete file" id="wtkfDelBtn' . $fncFormId . '" class="btn-floating red' . $fncDelHide . '">';
        $fncDelBtn .= '<i class="material-icons white-text">delete_forever</i></a>';
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
            $fncShowFile .= '><i class="material-icons small">visibility</i></a>';
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
        $fncShowFile .= '><i class="material-icons small">visibility</i></a>';
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
            $fncHtm .= ' <a onclick="JavaScript:wtkSelectImage(' . $gloId .')" title="upload photo" id="wtkfBtn' . $gloId . '" class="btn btn-floating waves-effect waves-light"><i class="material-icons left">file_upload</i>iOS Upload</a>' . "\n";
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
        $fncHtm .= ' class="btn waves-effect waves-light green hide"><i class="material-icons left">file_upload</i>Upload</a>' . "\n";
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
        <div class="row">
            <div class="col s12 center">
                <button type="button" class="btn-small black b-shadow waves-effect waves-light" onclick="Javascript:wtkGoBack()">$fncBtnTxt</button>
            </div>
        </div>
htmVAR;
    else:
        $fncCancelTxt = wtkLang('Cancel');
        $fncSaveTxt = wtkLang('Save');
        if ($fncCopy == ''): // No copy requested
            $fncHtm =<<<htmVAR
        <div class="row col s12 center">
            <button type="button" class="btn-small black b-shadow waves-effect waves-light" onclick="Javascript:wtkGoBack()">$fncCancelTxt</button>
            &nbsp;&nbsp;
            <button type="button" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="Javascript:ajaxPost('$fncSave', '$fncForm', 'Y')">$fncSaveTxt</button>
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
        <div class="row col s12 center">
            <button type="button" class="btn-small black b-shadow waves-effect waves-light" onclick="Javascript:wtkGoBack()">$fncCancelTxt</button>
            &nbsp;&nbsp;
            <button id="copyBtn" type="button" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="Javascript:ajaxCopy('$fncSave', '$fncForm')">$fncBtnText</button>
            &nbsp;&nbsp;
            <button type="button" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="Javascript:ajaxPost('$fncSave', '$fncForm', 'Y')">$fncSaveTxt</button>
        </div>
htmVAR;
        endif; // want copy button
    endif;
    return $fncHtm;
} // wtkUpdateBtns


/**
* For modalWTK Modal window create cancel and save buttons.
*
* This calls modalSave function in wtkCore.js.  If page is $gloForceRO = true then
* shows Return button instead of Save and Cancel.
*
* Example calling method:
* <code>
* $pgHtm .= wtkModalUpdateBtns('petList');
* </code>
*
* @param string $fncURL which PHP page to call to do saving; should not include '.php'
* @param string $fncDiv the <div> you want to replace after saving; the <form> should be named the same with a prepended 'F'
* @param string $fncClose whether Saving should close modal window; defaults to 'Y'
* @param string $fncAppend defaults to 'N'; if 'Y' then expects <div> to have id="wtkModalList" and will append result instead of replace it
* @return html returns html with update and cancel buttons
*/
function wtkModalUpdateBtns($fncURL, $fncDiv, $fncClose = 'Y', $fncAppend = 'N'){
    global $gloForceRO;
    if ($gloForceRO == true):
        $fncBtnTxt = wtkLang('Return');
        $fncHtm =<<<htmVAR
        <div class="row">
            <div class="col s12 center">
                <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">$fncBtnTxt</button>
            </div>
        </div>
htmVAR;
    else:
        $fncCancelTxt = wtkLang('Cancel');
        $fncSaveTxt = wtkLang('Save');
        $fncHtm =<<<htmVAR
    <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">$fncCancelTxt</button>
    &nbsp;&nbsp;
    <button type="button" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="Javascript:modalSave('$fncURL','$fncDiv','$fncClose','$fncAppend')">$fncSaveTxt</button>
htmVAR;
    endif;
    return $fncHtm;
} // wtkModalUpdateBtns

/**
* By passing a SQL SELECT this will build a nice looking page.
*
* Expects SQL SELECT to contain:
*    UID, Header, TopRight, Description and optionally Row2Left, Row2Right
*
* if $fncLink then must have `UID` in the SELECT result set.
*
* Example calling method:
* <code>
* $pgSQL =<<<SQLVAR<br>
* SELECT e.`UID`,<br>
* &nbsp; DATE_FORMAT(e.`AddDate`, '$gloSqlDateTime') AS `TopRight`,<br>
* &nbsp; e.`Subject` AS `Header`,<br>
* &nbsp; REPLACE(e.`EmailBody`,' href="', ' href="#" title="') AS `Description`<br>
* &nbsp; FROM wtkEmailsSent e<br>
* WHERE e.`SendToUserUID` = ?<br>
* ORDER BY e.`UID` DESC<br>
* SQLVAR;<br>
* <br>
* $pgHtm  = '<div class="container">' . "\n";<br>
* $pgHtm .= ' &nbsp;&nbsp;  <div class="pageList">' . "\n";<br>
* $pgHtm .= wtkPageList($pgSQL, [$gloUserUID], '/wtk/messageDetail');<br>
* $pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no messages yet');<br>
* $pgHtm .= ' &nbsp;&nbsp;  </div>' . "\n";<br>
* $pgHtm .= '</div>' . "\n";<br>
* </code>
*
* @param string $fncSQL SELECT query
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string $fncLink defaults to '' blank, if passed then ajaxGo link is created
* @param string $fnc2Rows defaults to 'N'; if 'Y' passed then 2nd row shows
* @return html returns html with update and cancel buttons
*/
function wtkPageList($fncSQL, $fncSqlFilter, $fncLink = '', $fnc2Rows = 'N'){
    global $gloWTKobjConn;
    $fncTemplate =<<<htmVAR
<div class="card">
    <div class="card-content">
        <div class="row">
            <div class="col m7 s12">
                <h5>@Header@</h5>
            </div>
            <div class="col m5 s12 right-align">
                @TopRight@
            </div>
        </div>
        <hr>
        @2ndRow@
        <div>
            <p>@Description@</p>
        </div>
    </div>
</div><br>
htmVAR;
    if ($fnc2Rows == 'N'):
        $fncTemplate = wtkReplace($fncTemplate, '@2ndRow@','');
    else:
        $fncRow2 =<<<htmVAR
    <div class="row">
        <div class="col s6">
            @Row2Left@
        </div>
        <div class="col s6 right-align">
            <span>@Row2Right@</span>
        </div>
    </div>
htmVAR;
        $fncTemplate = wtkReplace($fncTemplate, '@2ndRow@', $fncRow2);
    endif;
    $fncHtm = '';
    $fncSQL = wtkSqlPrep($fncSQL);
    $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    $fncPDO->execute($fncSqlFilter);
    while ($fncRow = $fncPDO->fetch()):
        $fncUID = $fncRow['UID'];
        $fncHeader = $fncRow['Header'];
        $fncTopRight = $fncRow['TopRight'];
        $fncDescription = $fncRow['Description'];
        $fncTmp = '';
        if ($fncLink != ''):
            $fncTmp = "<a onclick=\"JavaScript:ajaxGo('$fncLink',$fncUID);\">" . "\n";
            $fncDescription = wtkReplace($fncDescription, '<a ', '<span ');
            $fncDescription = wtkReplace($fncDescription, '</a>', '</span>');
        endif;
        $fncTmp .= $fncTemplate;

        $fncTmp = wtkReplace($fncTmp, '@Header@', $fncHeader);
        $fncTmp = wtkReplace($fncTmp, '@TopRight@', $fncTopRight);
        $fncTmp = wtkReplace($fncTmp, '@Description@', $fncDescription);
        if ($fnc2Rows == 'Y'):
            $fncTmp = wtkReplace($fncTmp, '@Row2Left@', $fncRow['Row2Left']);
            $fncTmp = wtkReplace($fncTmp, '@Row2Right@', $fncRow['Row2Right']);
        endif;
        if ($fncLink != ''):
            $fncTmp .= '</a>' . "\n";
        endif;
        $fncHtm .= $fncTmp;
    endwhile;
    if ($fncHtm == ''):
        $fncHtm =<<<htmVAR
<div class="card"><div class="card-content">
    <p>There is no data available.</p>
</div></div>
htmVAR;
    endif;

    return $fncHtm;
} // wtkPageList

/**
* Create list of chat data.
*
* Example calling method:
* <code>
* $pgSQL =<<<SQLVAR<br>
* SELECT c.`UID`, c.`Message`, c.`SendByUserUID`,<br>
* &nbsp; DATE_FORMAT(c.`AddDate`, '$gloSqlDate') AS `AddDate`<br>
* &nbsp;&nbsp; FROM `wtkChat` c<br>
* WHERE (c.`SendToUserUID` = :ToUserUID<br>
* &nbsp;&nbsp;&nbsp; AND c.`SendByUserUID` = :UserUID)<br>
* &nbsp;&nbsp;&nbsp;  OR (c.`SendByUserUID` = :ToUser2UID<br>
* &nbsp;&nbsp;&nbsp; AND c.`SendToUserUID` = :User2UID)<br>
* ORDER BY c.`UID` ASC<br>
* SQLVAR;<br>
* $pgSQL = wtkSqlPrep($pgSQL);<br>
* $pgSqlFilter = array (<br>
* &nbsp;&nbsp; 'ToUserUID' => $gloId,<br>
* &nbsp;&nbsp; 'UserUID' => $gloUserUID,<br>
* &nbsp;&nbsp; 'ToUser2UID' => $gloId,<br>
* &nbsp;&nbsp; 'User2UID' => $gloUserUID<br>
* );<br>
* $pgChat = wtkChatList($pgSQL, $pgSqlFilter);<br>
* </code>
*
* @param string $fncSQL SELECT query
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @return html returns html with list of a chat discussion
*/
function wtkChatList($fncSQL, $fncSqlFilter){
    // these are chat-style; for Forum-style notes see: wtkForumList
    global $gloWTKobjConn, $gloUserUID;
    $fncLastDate = '';
    $fncLastUser = '';
    $fncCntr = 0;
    $fncHtm  = '<div class="chat-detail">' . "\n";
    $fncHtm .= '    <div id="chatDIV" class="container">' . "\n";
    $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    $fncPDO->execute($fncSqlFilter);
    while ($fncRow = $fncPDO->fetch()):
        $fncDate = $fncRow['AddDate'];
        if ($fncLastDate != $fncDate):
            $fncLastDate = $fncDate;
            if ($fncCntr > 0): // not first time through
                $fncCntr = 0;
                $fncHtm .= '            </div>' . "\n";
                $fncHtm .= '        </div>' . "\n";
                $fncLastUser = '';
            endif;
            $fncHtm .= '        <div class="wrapper-date">' . "\n";
            $fncHtm .= "          <span>$fncDate</span>" . "\n";
			$fncHtm .= '        </div>' . "\n";
        endif;
        $fncChatUser = $fncRow['SendByUserUID'];
        if ($fncLastUser != $fncChatUser):
            $fncLastUser = $fncChatUser;
            $fncMsgCntr = 0;
        endif;
        if ($fncCntr > 0): // not first time through
            $fncHtm .= '            </div>' . "\n";
            $fncHtm .= '        </div>' . "\n";
        endif;
        $fncHtm .= '        <div class="row">' . "\n";
        $fncHtm .= '            <div class="col s12">' . "\n";
        if ($fncChatUser != $gloUserUID):
            $fncSide = 'right right';
        else: // self
            $fncSide = 'left left';
        endif;
        $fncMsgCntr ++;
        if ($fncMsgCntr > 1):
            $fncSide .= ' content-two';
            $fncTriangles = '';
        else:
            $fncTriangles = '    <div class="triangles"></div>' . "\n";
        endif;
        $fncHtm .= '<div class="content-' . $fncSide . '">' . "\n";
        $fncHtm .= $fncTriangles . '<span>';
        $fncHtm .= $fncRow['Message'];
        $fncHtm .= '</span>' . "\n" . '</div>' . "\n";
        $fncCntr ++;
    endwhile;
    if ($fncLastDate == ''):
        $fncHtm  = '<div class="chat-detail">' . "\n";
        $fncHtm .= '    <div id="chatDIV" class="container">' . "\n";
        $fncHtm .= '        <div id="noChat" class="card"><div class="card-content">' . "\n";
        $fncHtm .= '<p>There are no chat messages yet.</p>' . "\n";
    endif;
    $fncHtm .= '            </div>' . "\n";
    $fncHtm .= '        </div>' . "\n";
    if ($fncLastDate == ''):
        $fncHtm .= '    <br>' . "\n";
    endif;
    $fncHtm .= '    </div>' . "\n";
    $fncHtm .= '</div>' . "\n";

    return $fncHtm;
} // wtkChatList

/**
* This creates HTML with saveChat JavaScript function.  Used at bottom of chatEdit.php.
*
* @param integer $fncToUserUID the `UID` of the wtkUsers you want to chat with
* @return html returns html with Javascript:saveChat($fncToUserUID)
*/
function wtkSaveChat($fncToUserUID){
    $fncHtm =<<<htmVAR
<div class="form-bottom" id="wtkChat">
    <div class="col s6 center" id="msgResult"></div>
    <div class="wrap-input">
        <input type="text" name="wtkMsg" id="wtkMsg">
        <a id="btnSendNote" onclick="JavaScript:saveChat($fncToUserUID)"><i class="material-icons">send</i></a>
    </div>
</div>
htmVAR;
    return $fncHtm;
} // wtkSaveChat

/**
* Create list of Forum data.
*
* Example calling method:
* <code>
* $pgSQL =<<<SQLVAR<br>
* SELECT f.`UID`, f.`ForumMsg`, u.`FilePath`, u.`NewFileName`,<br>
* &nbsp; CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `UserName`,<br>
* &nbsp; DATE_FORMAT(f.`AddDate`, '$gloSqlDateTime') AS `AddDate`<br>
* &nbsp;&nbsp; FROM `wtkForumMsgs` f<br>
* &nbsp;&nbsp;&nbsp;&nbsp; INNER JOIN `wtkUsers` u ON u.`UID` = f.`UserUID`<br>
* WHERE f.`ForumUID` = ?<br>
* ORDER BY f.`UID` ASC<br>
* SQLVAR;<br>
* $pgForum = wtkForumList($pgSQL, [$gloId]);<br>
* </code>
*
* @param string $fncSQL SELECT query
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @return html returns html with list of a chat discussion
*/
function wtkForumList($fncSQL, $fncSqlFilter){
    global $gloWTKobjConn;
    $fncTemplate =<<<htmVAR
    <div class="forum-single card b-shadow">
        <div class="content-user">
            <img src="/wtk/imgs/noPhotoAvail.png">
            <h5>@UserName@</h5>
            @AddDate@
        </div>
        <div class="content-text">
            <p>@NoteText@</p>
        </div>
    </div>
htmVAR;
    $fncHtm = '';
    $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    $fncPDO->execute($fncSqlFilter);
    while ($fncRow = $fncPDO->fetch()):
        $fncTmp = "\n" . $fncTemplate;
        $fncTmp = wtkReplace($fncTmp, '@UserName@', $fncRow['UserName']);
        $fncTmp = wtkReplace($fncTmp, '@AddDate@', $fncRow['AddDate']);
        $fncTmp = wtkReplace($fncTmp, '@NoteText@', nl2br($fncRow['ForumMsg']));
        $fncNewFileName = $fncRow['NewFileName'];
        if ($fncNewFileName != ''):
            $fncFilePath = $fncRow['FilePath'];
            $fncTmp = wtkReplace($fncTmp, '/wtk/imgs/noPhotoAvail.png', $fncFilePath . $fncNewFileName);
        endif;
        $fncHtm .= $fncTmp;
    endwhile;
    if ($fncHtm == ''):
        $fncHtm =<<<htmVAR
<div id="forumDIV">
    <div id="noForum" class="forum-single card b-shadow">
        <div class="content-text">
            <p>There are no notes yet.</p>
        </div>
    </div>
</div>
htmVAR;
    else:
        $fncHtm  = '<div id="forumDIV">' . $fncHtm . "\n";
        $fncHtm .= '</div>' . "\n";
    endif;
    return $fncHtm;
} // wtkForumList

/**
* This creates HTML with sendNote JavaScript function.  Used at bottom of forumEdit.php.
*
* @param integer $fncParentUID the `UID` of the wtkForum table
* @param string $fncVer defaults to ''; only needed if more than one needed on a page
* @return html returns html with Javascript:sendNote($fncToUserUID)
*/
function wtkSendNoteForum($fncParentUID, $fncVer = ''){
    $fncHtm =<<<htmVAR
    <div class="card content-reply b-shadow">
        <h6>Reply</h6>
        <form>
            <div class="row item-input-wrap">
                <textarea name="myNote$fncVer" id="myNote$fncVer" class="col s12 materialize-textarea" rows="6" placeholder="message"></textarea>
            </div>
        	<button id="btnSendNote$fncVer" onclick="Javascript:sendNote($fncParentUID,'$fncVer')" class="btn waves-effect waves-light right" type="button">Submit</button>
			<br><br>
		</form>
    </div>
htmVAR;
    return $fncHtm;
} // wtkSendNoteForum
?>
