<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloIconSize = 'btn-small';

$pgSQL =<<<SQLVAR
SELECT e.`UID`, e.`EmailAddress`,
  DATE_FORMAT(COALESCE(e.`EmailDelivered`,e.`AddDate`), '$gloSqlDateTime') AS `Delivered`,
  CASE
    WHEN e.`EmailLinkClicked` IS NULL THEN ''
    ELSE CONCAT('<br>Link Clicked: ', DATE_FORMAT(e.`EmailLinkClicked`, '$gloSqlDateTime'))
  END AS `LinkClicked`,
  CASE
    WHEN COALESCE(e.`SendByUserUID`,0) = 0 THEN 'Server'
    ELSE CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,''), ' (',L2.`LookupDisplay`,')')
  END AS `SentFrom`,
  CASE
    WHEN COALESCE(e.`SendToUserUID`,0) = 0 THEN e.`EmailAddress`
    ELSE CONCAT(u2.`FirstName`, ' ', COALESCE(u2.`LastName`,''), ' (',L2.`LookupDisplay`,')')
  END AS `SentTo`,
  CASE
    WHEN e.`EmailOpened` IS NULL THEN 'not opened'
    ELSE CONCAT('Opened ', DATE_FORMAT(e.`EmailOpened`, '$gloSqlDateTime'))
  END AS `OpenStatus`,
  e.`Subject`, e.`Bounced`,
  CASE
    WHEN e.`SpamComplaint` IS NOT NULL OR e.`Bounced` = 'Y' THEN 'red'
    WHEN e.`EmailLinkClicked` IS NOT NULL THEN 'green'
    WHEN e.`EmailDelivered` IS NOT NULL THEN 'blue'
    ELSE 'yellow'
  END AS `RowColor`,
  CASE
    WHEN e.`SpamComplaint` IS NOT NULL THEN '<div class="chip orange white-text">spam</div><br>'
    WHEN e.`Bounced` = 'Y' THEN '<div class="chip orange white-text">bounce</div><br>'
    ELSE ' '
  END AS `SpamBounce`,
  CASE
    WHEN e.`EmailType` = 'sales' AND e.`OtherUID` IS NOT NULL THEN ''
    ELSE ' hide'
  END AS `HideMsg`
FROM `wtkEmailsSent` e
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = e.`SendByUserUID`
  LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel'
        AND L.`LookupValue` = u.`SecurityLevel`
  LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = e.`SendToUserUID`
  LEFT OUTER JOIN `wtkLookups` L2 ON L2.`LookupType` = 'SecurityLevel'
        AND L2.`LookupValue` = u2.`SecurityLevel`
SQLVAR;
$gloRowsPerPage = 20;
$gloEditPage = '';
$gloAddPage  = '';

$pgHideReset = ' class="hide"';
$pgWhere = '';
$pgFilterSelect = wtkFilterRequest('wtkFilterSel');
if ($pgFilterSelect != ''):
    $pgWhere = " WHERE e.`EmailType` = '$pgFilterSelect'";
endif;
$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    if ($pgWhere == ''):
        $pgWhere  = ' WHERE';
    else:
        $pgWhere .= ' AND';
    endif;
    $pgWhere .= " lower(e.`EmailAddress`) LIKE lower('%" . $pgFilter2Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgFilter3Value = wtkFilterRequest('showClicked');
if ($pgFilter3Value == 'Y'):
    $pgShowClicked = 'checked';
    if ($pgWhere == ''):
        $pgWhere  = ' WHERE';
    else:
        $pgWhere .= ' AND';
    endif;
    $pgWhere .= ' e.`EmailLinkClicked` IS NOT NULL' . "\n";
else:
    $pgShowClicked = '';
endif;

$pgFilter4Value = wtkFilterRequest('showOpened');
if ($pgFilter4Value == 'Y'):
    $pgShowOpened = 'checked';
    if ($pgWhere == ''):
        $pgWhere  = ' WHERE';
    else:
        $pgWhere .= ' AND';
    endif;
    $pgWhere .= ' e.`EmailOpened` IS NOT NULL' . "\n";
else:
    $pgShowOpened = '';
endif;
/*
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    if ($pgWhere == ''):
        $pgWhere  = ' WHERE';
    else:
        $pgWhere .= ' AND';
    endif;
    $pgWhere = " lower(u.`FirstName`) LIKE lower('%" . $pgFilterValue . "%')";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''
*/
$pgSQL .= $pgWhere;
$pgSQL .= ' ORDER BY e.`UID` DESC';
if ($gloDriver1 == 'pgsql'):
    $pgSQL = wtkReplace($pgSQL, '.`LookupValue`','."LookupValue"::smallint');
endif;

// BEGIN droplist of EmailTypes
$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
 WHERE `LookupType` = :LookupType
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSqlFilter = array (
    'LookupType' => 'EmailType'
);
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, $pgSqlFilter, 'LookupDisplay', 'LookupValue', $pgFilterSelect);
//  END  droplist of EmailTypes

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Email History
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('emailHistory','wtkEmailsSent')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <input type="hidden" id="HasTooltip" name="HasTooltip" value="Y">
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow" style="height:162px">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="row input-field">
           <div class="filter-width-50">
              <select id="wtkFilterSel" name="wtkFilterSel">
                <option value="">All</option>
                  $pgSelOptions
              </select>
              <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
           </div>
           <div class="filter-width-50">
                <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial email address">
           </div>
        </div>
        <div class="row input-field">
           <div class="filter-width-50">
               <span>Show only those that were opened</span>
               <div class="switch">
                 <label for="showOpened">No
                   <input type="checkbox" value="Y" id="showOpened" name="showOpened" $pgShowOpened>
                   <span class="lever"></span>
                   Yes</label>
               </div>
           </div>
           <div class="filter-width-50">
               <span>Show only those that clicked Link</span>
               <div class="switch">
                 <label for="showClicked">No
                   <input type="checkbox" value="Y" id="showClicked" name="showClicked" $pgShowClicked>
                   <span class="lever"></span>
                   Yes</label>
               </div>
           </div>
           <button onclick="Javascript:wtkBrowseFilter('emailHistory','wtkEmailsSent')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
        <br>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$gloColHdr = '<th><h5>Past Emails Sent</h5></th>';
$gloRowHtm =<<<htmVAR
<td>
<div class="row valign-wrapper @RowColor@">
    <div class="col m8 s12">
        <h6>@Subject@</h6>
        <strong>From:</strong> @SentFrom@ <br>
        <strong>To:</strong> @SentTo@
        <a onclick="JavaScript:ajaxGo('/admin/emailProHistory','ViewEmail',@UID@);" class="btn btn-floating @HideMsg@"><i class="material-icons">email</i></a>
    </div>
    <div class="col m3 s12 right-align">
        Delivered: @Delivered@<br>
        @OpenStatus@
        @LinkClicked@ @SpamBounce@</div>
    <div class="col m1 s12 right-align">
        <a class="btn btn-floating" onclick="JavaScript:wtkModal('/admin/emailView','EDIT',@UID@,@UID@)"><i class="material-icons small">remove_red_eye</i></a><br>
        <a onclick="JavaScript:wtkDel('wtkEmailsSent',@UID@,'N','SPA');" class="btn btn-floating "><i class="material-icons">delete</i></a>
    </div>
</div>
</td>
htmVAR;

wtkSearchReplace('class="striped"','');
wtkSearchReplace('border="0" cellpadding="10" cellspacing="0" id="templateHeader"','class="hide"');
wtkSearchReplace('No data.','no emails sent yet');
wtkSearchReplace('&nbsp; &nbsp;</div>','</div>');
wtkSearchReplace('class="footerContent"','class="footerContent hide"');
$pgMsgsList = wtkBuildDataBrowse($pgSQL, [], 'wtkEmailsSent', '','Y');
$pgMsgsList = wtkReplace($pgMsgsList, 'border="0" cellpadding="10" cellspacing="0" id="templateHeader"','class="hide"');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="striped"','');
$pgMsgsList = wtkReplace($pgMsgsList, '&nbsp; &nbsp;</div>','</div>');
$pgMsgsList = wtkReplace($pgMsgsList, 'No data.','no emails sent yet');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="footerContent"','class="footerContent hide"');
$pgHtm .= $pgMsgsList . "\n";
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
