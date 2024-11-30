<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
/*
This page shows how easy it is to have multiple Browse Boxes on single page.
It is critical that third parameter is different for each list so AJAX knows which to replace.
*/

// BEGIN wtkLoginLog section
$pgSQL =<<<SQLVAR
SELECT L.`UID`, CONCAT(u.`FirstName`, ' ', u.`LastName`) AS `User`, L.`PagesVisited`,
    TIMEDIFF(L.`LastLogin`, L.`FirstLogin`) AS `TimeDiff`
FROM `wtkLoginLog` L
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = L.`UserUID`
ORDER BY L.`PagesVisited` DESC
SQLVAR;
// This shows how you can easily code your SQL to work with both PostgreSQL and mySQL DBs

$gloColumnAlignArray = array (
    'PagesVisited' => 'center',
    'TimeDiff' => 'center'
);
$gloTotalArray = array (
    'PagesVisited' => 'SUM'
);
wtkSetHeaderSort('PagesVisited'); // Defaults column name but can change with second parameter
wtkSetHeaderSort('TimeDiff', 'Session Duration');
// when third parameter exists it is used for sorting of first field's column
$gloSkipFooter = true;
$gloRowsPerPage = 5;
$pgLoginList = wtkBuildDataBrowse($pgSQL, [], 'wtkLoginLog');
$gloSkipFooter = false;
//  END  wtkLoginLog section

// BEGIN Pet Notes section
$pgSQL =<<<SQLVAR
SELECT `UID`, `PetName`, `City`, DATE_FORMAT(`BirthDate`,'$gloSqlDate') AS `DOB`,
    `fncContactIcons`(`OwnerEmail`,`OwnerPhone`,`Latitude`,`Longitude`,'Y',`UID`,'Y','Y','') AS `OwnerContact`
  FROM `pets`
WHERE `DelDate` IS NULL
ORDER BY `UID` ASC
SQLVAR;
wtkSetHeaderSort('PetName');
wtkSetHeaderSort('DOB', 'Birth Day', 'BirthDate'); // third parameter is what sort uses

$gloColumnAlignArray = array (
    'City' => 'center',
    'DOB'  => 'right'
);
$gloRowsPerPage = 5;
$pgPetList = wtkBuildDataBrowse($pgSQL, [], 'petList', '', 'P');
//  END  Pet Notes section

// BEGIN Lookup List
$pgSQL =<<<SQLVAR
SELECT `UID`, `LookupType`, `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
ORDER BY `LookupType` ASC, `LookupValue` ASC
SQLVAR;

$gloColumnAlignArray = array (
	'LookupValue' => 'center'
);
wtkSetHeaderSort('LookupType', 'Type');     // Makes column sortable by clicking header
wtkSetHeaderSort('LookupValue', 'Value');   // Makes column sortable by clicking header

$gloEditPage = '';
$gloMoreButtons = array();
$pgLookupList = wtkBuildDataBrowse($pgSQL,[],'LookupList');
//  END  Lookup List

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col m6">
        <h4>Pets</h4>
            <div class="wtk-list card b-shadow">
                $pgPetList
            </div>
    </div>
    <div class="col m6">
        <h4>Lookup List</h4>
        <div class="wtk-list card b-shadow">
            $pgLookupList
        </div>
    </div>
    <div class="col s12">
        <hr>
        <h4>Top 5 Login Logs</h4>
        <div class="wtk-list card b-shadow">
            $pgLoginList
        </div>
    </div>
</div>
htmVAR;

wtkProtoType($pgHtm);
wtkSearchReplace('m4 offset-m4 s12','m12 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
?>
