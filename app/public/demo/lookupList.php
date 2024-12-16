<?php
$gloLoginRequired = false;
if (!isset($gloConnected)):
// because Save.php page will include this page for SPA on returning from Save of Edit page
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `LookupType`, `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
ORDER BY `LookupType` ASC, `LookupValue` ASC
SQLVAR;

$gloHideUID = false;  // this allows `UID` to be seen; by default it will be supressed
$gloLineNumbers = true;  // this is false by default; when true will show line count
$gloEditPage = 'lookupEdit';
// $gloPrinting = true; // this will hide add/edit/delete buttons
// optionally set next to columns and edit button
// will only appear when LookupType value is 'Canada'
$gloEditCondCol     = 'LookupType';
$gloEditCondition   = 'Canada';

$gloColumnAlignArray = array (
	'LookupValue' => 'center'
);
wtkSetHeaderSort('LookupType', 'Type');     // Makes column sortable by clicking header
wtkSetHeaderSort('LookupValue', 'Value');   // Makes column sortable by clicking header

$pgList = wtkBuildDataBrowse($pgSQL); // Creates list with AJAX navigation
/*
wtkBuildDataBrowse($fncSQL, $fncSqlFilter = [], $fncTableId = '', $fncURL = '', $fncModalEdit = 'N') {
2nd parameter is for PDO SQL filter
3rd parameter is only needed if you have more than one wtkBuildDataBrowse on a page, then you must have
    different $fncTableId's so the AJAX knows which browse to affect
4th parameter is only needed if page is returned to after saving data from an edit page
5th parameter determines whether Edit buttons show edit page in modal window or ajaxGo to the page
*/

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Lookup List</h4>
    <br>
    <div class="wtk-list card b-shadow">
        $pgList
    </div>
</div>
htmVAR;

if ($gloSiteDesign == 'SPA'):
    echo $pgHtm;  // SPA Method
else: // MPA Method
    wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
endif;
?>
