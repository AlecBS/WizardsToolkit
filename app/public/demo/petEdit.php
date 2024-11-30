<?PHP
$gloLoginRequired = false;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

if ($gloWTKmode == 'EDIT'):
    $pgID1 = wtkGetPost('ID1');  // filled if from Pet Note add/update
    if ($pgID1 == ''):
        $gloRNG = $gloId;
    endif;
    $pgSQL =<<<SQLVAR
SELECT n.`UID`, DATE_FORMAT(n.`AddDate`, '$gloSqlDateTime') AS 'AddDate',
    u.`FirstName` AS `WrittenBy`, n.`PetNote`
  FROM `petNotes` n
   INNER JOIN `wtkUsers` u ON u.`UID` = n.`UserUID`
WHERE n.`PetUID` = :PetUID
ORDER BY n.`UID` DESC
SQLVAR;
    $pgSqlFilter = array (
        'PetUID' => $gloRNG
    );
    $gloEditPage = '/demo/petNote';
    $gloAddPage  = $gloEditPage;
    $pgNotes = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'petNoteList', '', 'Y');
    if ($pgID1 != ''): // returned from petNote.php
        echo $pgNotes;
        exit;
    endif;
endif;

$gloForceRO = wtkPageReadOnlyCheck('petEdit.php', $gloId);

$pgSQL =<<<SQLVAR
SELECT `UID`, `PetName`, `PetType`, `Gender`, `City`, `State`, `Zipcode`,
 `OwnerPhone`, `OwnerEmail`, `CanTreat`, `BirthDate`, `NextTime`, `FilePath`, `NewFileName`, `Note`
  FROM `pets`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Pet Details</h4>
    <br>
    <div class="content card b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('pets', 'PetName', 'text', '', 'm6 s12');
$pgValues = array(
    'Male' => 'M',
    'Female' => 'F',
    'Uncertain' => 'U'
);
$pgHtm .= wtkFormRadio('pets', 'Gender', 'Gender', $pgValues, 'm2 s6');
$pgHtm .= wtkFormFile('pets','FilePath','/demo/imgs/','NewFileName','Pet Photo','m4 s12');

$pgHtm .= wtkFormText('pets', 'City');
$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'USAstate' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('pets', 'State', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m4 s6');

$pgHtm .= wtkFormText('pets', 'Zipcode', 'number', '', 'm2 s6');

$pgHtm .= wtkFormText('pets', 'OwnerPhone', 'tel');
$pgHtm .= wtkFormText('pets', 'OwnerEmail', 'email');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'PetType' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('pets', 'PetType', $pgSQL, [], 'LookupDisplay', 'LookupValue','','m3 s6');
$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('pets', 'CanTreat', 'Allowed to give Treats', $pgValues, 'm3 s6');

$pgHtm .= wtkFormText('pets', 'BirthDate', 'date', '', 'm3 s6');
$pgHtm .= wtkFormText('pets', 'NextTime', 'timepicker', 'When to Walk', 'm3 s6');
$pgHtm .= wtkFormTextArea('pets', 'Note');

//$pgHtm .= wtkFormHidden('Debug', 'Y');
$pgHtm .= wtkFormPrimeField('pets', 'UserUID', $gloUserUID);
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/petList.php');
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= '</div></form></div>' . "\n";

if ($gloWTKmode == 'EDIT'):
    $pgHtm .=<<<htmVAR
    <br>
    <div class="card b-shadow">
        <div id="petNoteList" class="card-content">
            $pgNotes
        </div>
    </div>
htmVAR;
endif;

$pgHtm .= '</div>' . "\n";

wtkProtoType($pgHtm);
echo $pgHtm;
exit;
?>
