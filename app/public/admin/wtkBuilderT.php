<?PHP
//  Strips out back-ticks from SQL in case copy/pasted from phpMyAdmin the SELECT statement
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgResult = '';
function wtkMakeFile($fncFileName, $fncContent, $fncFormType) {
    global $pgSQL;
    if (file_exists($fncFileName)):
        $fncMsg = '';
        if ($fncFormType == 'form'):
            $fncMsg = '<br>';
        endif;
        $fncMsg .= '<span id="buildMsg" class="red-text">This ' . $fncFileName;
        $fncMsg .= ' file already exists.  Please change file name then try again.</span>';
    else:   // Not file_exists($fncFileName)
        $fncFileHandle = fopen($fncFileName, 'a');
        if ($fncFileHandle === false):
            $fncMsg  = 'Cannot create/open file.  Please check folder settings.';
        else:   // Not $fncFileHandle === false
            if (fwrite($fncFileHandle, $fncContent) === FALSE):
                $fncMsg  = 'Cannot write to file "' . $fncFileName . '"';
            else: // wrote file
                fclose($fncFileHandle);
                $fncFileGo = wtkReplace($fncFileName, '.php', '');
                $fncURL = '<a onclick="Javascript:ajaxGo(\'' . $fncFileGo . "'";
                if ($fncFormType == 'browse'):
                    $fncURL .= ');">';
                    $fncMsg  = 'Your browse was created as ';
                else:   // Not $fncFormType == 'browse'
                    $fncURL .= ",'ADD',0);\">";
                    $fncMsg  = '<br>Your form was created as ';
                endif;  // $fncFormType == 'browse'
                $fncMsg .= $fncURL . $fncFileName . '</a>';
                // if ($pgSQL != ''):
                //     $fncMsg .= '<br><br>' . "\n" . '<textarea cols="80" rows="3">' . $pgSQL . '</textarea><br>' . "\n";
                // endif;  // $pgSQL != ''
            endif;  // fwrite($fncFileHandle, $fncContent) === FALSE
        endif;  // $fncFileHandle === false
    endif;  // file_exists($fncFileName)
    return $fncMsg;
}  // end of wtkMakeFile

// ABS 07/06/10  BEGIN  section that builds Browse PHP page
if (wtkGetParam('wtkRFBBrPHPfilename') != ''):
    $pgBrPHP = wtkLoadInclude('incWTKbrowseTemplateT.php');
    $pgWhere = wtkGetParam('wtkRFBWhere');
    $pgBrFilter = wtkGetParam('wtkRFBQuickFilter');
    $pgBrFilter2 = wtkGetParam('wtkRFBQuickFilter2');
    if (($pgBrFilter == '') && ($pgBrFilter2 != '')):
        $pgBrFilter  = $pgBrFilter2;
        $pgBrFilter2 = '';
    endif;
    if ($pgBrFilter != ''):
        $pgBrTmp = wtkLoadInclude('incWTKfilter.php');
        $pgBrTmp = wtkReplace($pgBrTmp,'class="hide"', 'class="hidden"');
        if ($pgWhere == ''):
            $pgBrTmp = wtkReplace($pgBrTmp, 'AND lower', 'WHERE lower');
        endif;
        $pgBrTmp = wtkReplace($pgBrTmp, '@Filter@', $pgBrFilter);
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterWhere@', $pgBrTmp);
    else:
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterWhere@', '');
    endif;
    if ($pgBrFilter2 != ''):
        $pgBrTmp = wtkLoadInclude('incWTKfilter.php');
        $pgBrTmp = wtkReplace($pgBrTmp, '$pgHideReset = \' class="hide"\';', '');
        $pgBrTmp = wtkReplace($pgBrTmp, 'pgFilterValue', 'pgFilter2Value');
        $pgBrTmp = wtkReplace($pgBrTmp, "'wtkFilter'", "'wtkFilter2'");
        $pgBrTmp = wtkReplace($pgBrTmp, '@Filter@', $pgBrFilter2);
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilter2Where@', $pgBrTmp);
    else:
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilter2Where@', '');
    endif;
    $pgBrPHP = wtkReplace($pgBrPHP, '@SecLevel@', wtkGetParam('wtkRFBSecLevel'));
    if (strpos($pgWhere, 'DelDate') === false):
        $pgBrPHP = wtkReplace($pgBrPHP, '@Table@DelDate', '@Table@');
    endif;
    if ($pgWhere == ''):
        $pgBrPHP = wtkReplace($pgBrPHP, 'WHERE @Where@', '');
    else:
        $pgBrPHP = wtkReplace($pgBrPHP, '@Where@', $pgWhere);
    endif;
    $pgOrderBy = wtkGetParam('wtkRFBOrderBy');
    if ($pgOrderBy == ''):
        $pgOrderBy = wtkGetParam('wtkRFBUniqueID');
    endif;
    if ((strpos($pgOrderBy, ' ASC') !== false) || (strpos($pgOrderBy, ' DESC') !== false)):
        $pgBrPHP = wtkReplace($pgBrPHP, '@OrderBy@ ASC', '@OrderBy@');
    endif;
    $pgBrPHP = wtkReplace($pgBrPHP, '@OrderBy@', $pgOrderBy);
    $pgBrPHP = wtkReplace($pgBrPHP, '@UpPHPfilename@', wtkGetParam('wtkRFBUpPHPfilename'));
    $pgBrPHP = wtkReplace($pgBrPHP, '@GUID@', wtkGetParam('wtkRFBUniqueID'));
    if ($pgBrFilter != ''):
        $pgBrTmp = wtkLoadInclude('incWTKfilterBoxT.php');
        $pgBrTmp = wtkReplace($pgBrTmp, '@LabelOne@', wtkInsertSpaces($pgBrFilter));
        if ($pgBrFilter2 != ''):
//          $pgBrTmp = wtkReplace($pgBrTmp, '@FilterTitle@', 'Quick Filters');
            $pgBrTmp = wtkReplace($pgBrTmp, 'partial value', 'partial' . wtkInsertSpaces($pgBrFilter));
            $pgTmp  = '<div class="flex-auto">' . "\n";
            $pgTmp .= "\t\t\t\t" . '  <label for="wtkFilter2" class="block text-sm font-medium text-gray-700 mb-1"">' . wtkInsertSpaces($pgBrFilter2) . '</label>' . "\n";
            $pgTmp .= "\t\t\t\t" . '  <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial' . wtkInsertSpaces($pgBrFilter2) . ' to search for" class="input">' . "\n";
            $pgTmp .= "\t\t\t   </div>";
            $pgBrTmp = wtkReplace($pgBrTmp, '@filter2@', $pgTmp);
        else:
            $pgBrTmp = wtkReplace($pgBrTmp, '@filter2@', '');
        endif;
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterBox@', $pgBrTmp);
        $pgBrPHP = wtkReplace($pgBrPHP, '<h4>@BrowseTitle@</h4>', '<h4>@BrowseTitle@');
        $pgResetBtn = wtkLoadInclude('incWTKfilterReset.php');
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterReset@', $pgResetBtn);
    else:
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterReset@', '');
        $pgBrPHP = wtkReplace($pgBrPHP, '@incFilterBox@', '');
    endif;
    $pgBrPHP = wtkReplace($pgBrPHP, '@BrowseTitle@', wtkGetParam('wtkRFBBrowseTitle'));
    $pgBrPHP = wtkReplace($pgBrPHP, '@Table@', wtkGetParam('wtkRFBTable'));
    $pgBrFieldArray = array();
    $pgBrowseSQL = wtkGetParam('wtkRFBBrSQL');
    if (substr($pgBrowseSQL, -1) == ','):
        $pgBrowseSQL = substr($pgBrowseSQL, 0, -1);
    endif;
    // if FORMAT( or AS is in SQL then assume pre-formatted
    $pgFormatted = stripos($pgBrowseSQL, 'FORMAT(');
    if ($pgFormatted === false):
        $pgFormatted = stripos($pgBrowseSQL, ' AS ');
    endif; // not preformatted
    if ($pgFormatted === false):
        $pgFormatted = strpos($pgBrowseSQL, '.'); // aliases used
    endif; // not preformatted
    if ($pgFormatted === false):
        $pgBrowseSQL = wtkReplace($pgBrowseSQL, '`','');
        $pgBrFieldArray = explode(',', $pgBrowseSQL);
        $pgBrowseSQL    = '';
        for ($i = 0; $i < sizeof($pgBrFieldArray); ++$i):
            $pgField = trim($pgBrFieldArray[$i]);
            if ($pgBrowseSQL != ''):
                $pgBrowseSQL .= ', ';
            endif;  // $pgFormSQL != ''
            // ABS 12/03/12  BEGIN  if Date field then add
            if ((substr($pgField, -4, 4 ) == 'Date') || (substr($pgField, -5, 5 ) == '_date')): // ABS 03/23/16
                $pgBrowseSQL .= wtkSqlDateFormat($pgField);
            else:   // Not substr($pgField, -4, 4 ) == 'Date'
                $pgBrowseSQL .= '`' . $pgField . '`';
            endif;  // substr($pgField, -4, 4 ) == 'Date'
            // ABS 12/03/12   END   if Date field then add
        endfor; // $i = 0; $i < sizeof($pgBrFieldArray); ++$i
    endif; // not preformatted
    $pgBrPHP = wtkReplace($pgBrPHP, '@BrSQL@', $pgBrowseSQL);

    $pgFileName = wtkGetParam('wtkRFBBrPHPfilename');
    $pgBrPHP = wtkReplace($pgBrPHP, '@FileName@', $pgFileName);
    $pgResult .= wtkMakeFile($pgFileName . '.php', $pgBrPHP, 'browse');
    if (wtkGetParam('InsertRmPages') == 'on'):
        $pgSQL  = 'INSERT INTO `wtkPages` (`PageName`, `FileName`)';
        $pgSQL .= ' VALUES (:PageName, :FileName)';
// Note:  because of unique index this will fail if a wtkPages with same FileName exists... which is the way we want it
        $pgPageName = wtkGetParam('wtkRFBBrowseTitle');
        $pgSqlFilter = array (
            'PageName' => $pgPageName,
            'FileName' => $pgFileName
        );
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgSQL = wtkReplace($pgSQL,':PageName', "'$pgPageName'");
        $pgSQL = wtkReplace($pgSQL,':FileName', "'$pgFileName'");
        $pgResult .= '<br>SQL for other servers: ' . wtkSqlPrep($pgSQL);
    endif;  // wtkGetParam('InsertRmPages') == 'on'
    $pgSQL = '';
endif;  // wtkGetParam('wtkRFBBrPHPfilename') != ''
// ABS 07/06/10   END   section that builds Browse PHP page

// ABS 07/04/10  BEGIN  section that builds Form PHP page
if (wtkGetParam('wtkRFBUpPHPfilename') != ''):
    $pgFormPHP = wtkLoadInclude('incWTKformTemplateT.php');

    $pgFormPHP = wtkReplace($pgFormPHP, '@SecLevel@', wtkGetParam('wtkRFBSecLevel'));
    $pgFormPHP = wtkReplace($pgFormPHP, '@FormTitle@', wtkGetParam('wtkRFBFormTitle'));
    $pgFormPHP = wtkReplace($pgFormPHP, '@GUID@', wtkGetParam('wtkRFBUniqueID'));

    $pgFieldArray = array();
    $pgFormSQL = wtkGetParam('wtkRFBUpSQL');
    if (substr($pgFormSQL, -1) == ','):
        $pgFormSQL = substr($pgFormSQL, 0, -1);
    endif;
    $pgFormSQL = wtkReplace($pgFormSQL, '`','');
    $pgFieldArray = explode(',', $pgFormSQL);
    $pgFieldsPerRow = wtkGetParam('wtkRFBFieldsPerRow');
    $pgFormPHP = wtkReplace($pgFormPHP, '@ColumnCount@', $pgFieldsPerRow);
    $pgFieldCnt = 0;
    $pgFormSQL  = '';
    $pgNewDataRows = '';
    for ($i = 0; $i < sizeof($pgFieldArray); ++$i):
        $pgField = trim($pgFieldArray[$i]);
        if (($pgField != 'UID') && ($pgField != 'GUID')):
            $pgFieldCnt ++;
            if ($pgFormSQL != ''):
                $pgFormSQL .= ', ';
            endif;  // $pgFormSQL != ''
            $pgFormSQL .= '`' . $pgField . '`';
            // BEGIN Default File Upload
            if (strtolower($pgField) == 'newfilename'):
                $pgNewDataRows .= '$pgHtm .= ' . "wtkFormFile('@Table@','FilePath','/imgs/','NewFileName','Photo');" . "\n";
            else: // not file upload
                //  END  Default File Upload
                if (substr(strtolower($pgField), -4, 4) == 'note'):
                    $pgNewDataRows .= '$pgHtm .= wtkFormTextArea(\'@Table@\', \'' . $pgField . "');" . "\n";
                else:
                    $pgDataType = '';
                    if ((substr ($pgField, -4 , 4 ) == 'Date') || (substr($pgField, -5, 5 ) == '_date')):
                        $pgDataType = 'date';
                    elseif ((substr ($pgField, -4 , 4 ) == 'Time') || (substr($pgField, -5, 5 ) == '_time')):
                        $pgDataType = 'time';
                    elseif (substr(strtolower($pgField), -5, 5) == 'email'):
                        $pgDataType = 'email';
                    elseif (substr(strtolower($pgField), -5, 5) == 'phone'):
                        $pgDataType = 'tel';
                    elseif (substr(strtolower($pgField), -8, 8) == 'password'):
                        $pgDataType = 'password';
                    endif;
                    $pgNewDataRows .= '$pgHtm .= wtkFormText(\'@Table@\', \'' . $pgField . "'";
                    if ($pgDataType == ''):
                        $pgNewDataRows .= ");" . "\n";
                    else: // $pgDataType != ''
                        $pgNewDataRows .= ", '$pgDataType');" . "\n";
                    endif; // $pgDataType != ''
                endif;  // text field
            endif; // not file upload
            if ($pgFieldCnt == 1):
                $pgNewDataRows .= 'if ($gloWTKmode == \'Copy\'): // Copy data feature' . "\n";
                $pgNewDataRows .= '    $pgHtm = wtkReplace($pgHtm, \' name="Origwtk\', \' name="Copywtk\');' . "\n";
                $pgNewDataRows .= '    $gloWTKmode = \'ADD\';' . "\n";
                $pgNewDataRows .= 'endif;' . "\n";
            endif;
        endif;  // ($pgField != 'UID') && ($pgField != 'GUID')
    endfor; // $i = 1; $i < (sizeof($pgFieldArray) + 1); ++$i
    $pgUpFileName = wtkGetParam('wtkRFBUpPHPfilename');
    $pgFormPHP = wtkReplace($pgFormPHP, '@FormFileName@', $pgUpFileName);
    $pgBrFileName = wtkGetParam('wtkRFBBrPHPfilename');
    if ($pgBrFileName == ''):
        $pgBrFileName = wtkReplace($pgUpFileName, 'Edit','List');
    endif;
    $pgUpFileName .= '.php';
    $pgFormPHP = wtkReplace($pgFormPHP, '@BrowseFileName@', $pgBrFileName);
    $pgFormPHP = wtkReplace($pgFormPHP, '@UpSQL@', $pgFormSQL);
    $pgFormPHP = wtkReplace($pgFormPHP, '@FormData@', $pgNewDataRows );
    $pgFormPHP = wtkReplace($pgFormPHP, '@Table@', wtkGetParam('wtkRFBTable'));

    $pgResult .= wtkMakeFile($pgUpFileName, $pgFormPHP, 'form');
endif;  // wtkGetParam('wtkRFBFormTitle') != ''
// BEGIN use whatever value was used last
$pgPastUID = wtkGetParam('wtkRFBUniqueID');
if ($pgPastUID == ''):
    $pgPastUID = wtkGetCookie('wtkRFBUniqueID');
else:
    $pgPastUID = wtkSetCookie('wtkRFBUniqueID',$pgPastUID, '1year');
endif;
//  END  use whatever value was used last
if ($pgResult != ''): // called from ajaxWTKbuild
    echo $pgResult ;
    exit;
endif;
//  END   section that builds PHP page

$gloWTKmode = 'ADD';
$pgTableName = 'RFB';

$pgHtm =<<<htmVAR
<div class="row">
  <div class="col s12">
      <h4>Wizard&rsquo;s Toolkit TailwindCSS Page Builder!
      <small><br>create PHP pages in seconds</small></h4><br>
    <div class="card content b-shadow">
        <form action="?" id="wtkBuild" name="wtkBuild" method="POST">
        <div class="row">
            <div class="col s12">
                <h5>SQL Basics</h5>
            </div>
        </div>
        <div class="row">
htmVAR;

$pgTmp  = wtkFormText('RFB', 'Table', 'text', 'SQL Table');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgTmp  = wtkFormText('RFB', 'UniqueID', 'text', 'Primary Key (`UID`, `id`, etc.)', 'm3 s6');
if ($pgPastUID != ''):
    $pgTmp  = wtkReplace($pgTmp, 'value=""','value="' . $pgPastUID . '"');
    $pgTmp  = wtkReplace($pgTmp, '<label for','<label class="active" for');
endif;
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgHtm .= wtkFormText('RFB', 'SecLevel', 'number', 'Security Level', 'm3 s6');

$pgHtm .= '</div><div class="row">' . "\n";
$pgHtm .= '  <div class="col s12"><hr>' . "\n";
$pgHtm .= '     <br><h5>List Page Settings</h5></div>' . "\n";
$pgHtm .= wtkFormText('RFB', 'BrowseTitle', 'text', 'Header');
$pgTmp  = wtkFormText('RFB', 'QuickFilter', 'text', 'Quick Filter Column', 'm3 s12');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');
$pgTmp  = wtkFormText('RFB', 'QuickFilter2', 'text', 'Second Filter', 'm3 s12');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');
$pgTmp  = wtkFormText('RFB', 'BrSQL', 'text', 'SQL Columns','m12 s12');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgHtm .= '<div class="col s12">(above should be comma delimited column names without SELECT nor FROM)</div>' . "\n";
$pgTmp  = wtkFormText('RFB', 'Where', 'text', 'WHERE');
$pgTmp  = wtkReplace($pgTmp, '<label','<label class="active"');
$pgTmp  = wtkReplace($pgTmp, 'value=""','value="`DelDate` IS NULL"');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgTmp  = wtkFormText('RFB', 'OrderBy', 'text', 'ORDER BY');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgHtm .= '</div>' . "\n";
$pgHtm .= '<div class="row">' . "\n";
$pgHtm .= '  <div class="col s12"><hr>' . "\n";
$pgHtm .= '     <br><h5>Form Page Settings</h5></div>' . "\n";

$pgHtm .= wtkFormText('RFB', 'FormTitle', 'text', 'Form Title');
$pgHtm .= wtkFormText('RFB', 'FieldsPerRow', 'number', 'Fields Per Row (max 6)');
$pgTmp  = wtkFormText('RFB', 'UpSQL', 'text', 'Form SQL Columns','s12');
$pgHtm .= wtkReplace($pgTmp, '<input type','<input class="code-text" type');

$pgHtm .= '</div>' . "\n";
$pgHtm .= '<div class="row">' . "\n";
$pgHtm .= '  <div class="col s12"><hr>' . "\n";
$pgHtm .= '     <br><h5>Name your Wizard&rsquo;s Toolkit files to be generated</h5></div>' . "\n";

$pgHtm .= wtkFormText('RFB', 'BrPHPfilename', 'text', 'Generate List PHP');
$pgHtm .= wtkFormText('RFB', 'UpPHPfilename', 'text', 'Generate Form PHP');
$pgHtm .=<<<htmVAR
        <div class="col m5 offset-m1 s12">
            <div class="switch">
                <br>Add to Menu Data: &nbsp;&nbsp;&nbsp;
                <label>No
                    <input type="checkbox" id="InsertRmPages" name="InsertRmPages">
                    <span class="lever"></span>
                    Yes
                </label>
            </div>
        </div>
        <div class="col m6 s12 center"><br>
            <button type="button" class="btn b-shadow waves-effect waves-light" onclick="Javascript:ajaxWTKbuild('T')">Generate Page(s)</button>
            <br><span id="buildMsg" class="green-text"></span>
        </div>
    </div>
</div>
htmVAR;
/*
$pgTmp  = 'wtkRFBBrPHPfilename.value' . "\n";
$pgTmp .= '  document.forms.wtkBuild.wtkRFBBrPHPfilename.Message = "If you leave this blank it will not generate the file."';

wtkSearchReplace('wtkRFBBrPHPfilename.value', $pgTmp);
*/
$pgHtm .= '</div></form>' . "\n";
echo $pgHtm;
?>
