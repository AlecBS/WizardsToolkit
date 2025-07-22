<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

function prepWhere($fncWhere){
    if ($fncWhere == ''):
        $fncWhere  = ' WHERE';
    else:
        $fncWhere .= ' AND';
    endif;
    return $fncWhere;
}

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
    $pgWhere = prepWhere($pgWhere);
    $pgWhere .= " lower(e.`EmailAddress`) LIKE lower('%" . $pgFilter2Value . "%')";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
$pgFilter3Value = wtkFilterRequest('showClicked');
if ($pgFilter3Value == 'Y'):
    $pgShowClicked = 'checked';
    $pgWhere = prepWhere($pgWhere);
    $pgWhere .= ' e.`EmailLinkClicked` IS NOT NULL' . "\n";
else:
    $pgShowClicked = '';
endif;

// BEGIN filter by Subject
$pgFilterSubject = wtkFilterRequest('wtkFilterSubject');
if ($pgFilterSubject != ''):
    $pgWhere = prepWhere($pgWhere);
    $pgWhere .= " lower(e.`Subject`) LIKE lower('%" . $pgFilterSubject . "%')";
endif;
//  END  filter by Subject

$pgOpenStatusNotOpened = '';
$pgOpenStatusOpened = '';
$pgOpenStatusAny = '';
$pgFilter4Value = wtkFilterRequest('OpenStatus');
switch ($pgFilter4Value):
    case 'Opened':
        $pgWhere = prepWhere($pgWhere);
        $pgWhere .= ' e.`EmailOpened` IS NOT NULL' . "\n";
        $pgOpenStatusOpened = 'checked';
        break;
    case 'NotOpened':
        $pgWhere = prepWhere($pgWhere);
        $pgWhere .= ' e.`EmailOpened` IS NULL' . "\n";
        $pgOpenStatusNotOpened = 'checked';
        break;
    default:
        // do nothing
        $pgOpenStatusAny = 'checked';
endswitch;
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
        <div class="row">
           <div class="col m4 s12 input-field">
              <select id="wtkFilterSel" name="wtkFilterSel">
                <option value="">All</option>
                  $pgSelOptions
              </select>
              <label for="wtkFilterSel" class="active" style="top:0">User Type</label>
              <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
           </div>
           <div class="col m4 s12 input-field">
                <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="enter partial email address">
           </div>
           <div class="col m4 s12 input-field">
                <input type="search" name="wtkFilterSubject" id="wtkFilterSubject" value="$pgFilterSubject" placeholder="enter partial subject">
           </div>
        </div>
        <div class="row" style="margin-top:-24px">
           <div class="col m6 s12">
                <span>Opened Status</span>
                <table class="table-basic" style="margin-top:-9px">
                    <tr>
                        <td>
                            <p>
                                <label>
                                  <input value="Any" class="with-gap" name="OpenStatus" type="radio" $pgOpenStatusAny />
                                  <span>Any</span>
                                </label>
                            </p>
                        </td>
                        <td>
                            <p>
                                <label>
                                  <input value="NotOpened" class="with-gap" name="OpenStatus" type="radio" $pgOpenStatusNotOpened />
                                  <span>Never Opened</span>
                                </label>
                            </p>
                        </td>
                        <td>
                            <p>
                                <label>
                                  <input value="Opened" class="with-gap" name="OpenStatus" type="radio" $pgOpenStatusOpened />
                                  <span>Opened</span>
                                </label>
                            </p>
                        </td>
                    </tr>
                </table>
           </div>
           <div class="col m4 s12 input-field">
               <span>Show only those that clicked Link</span>
               <div class="switch">
                 <label for="showClicked">No
                   <input type="checkbox" value="Y" id="showClicked" name="showClicked" $pgShowClicked>
                   <span class="lever"></span>
                   Yes</label>
               </div>
           </div>
           <div class="col m2 s12">
               <button onclick="Javascript:wtkBrowseFilter('emailHistory','wtkEmailsSent')" id="wtkFilterBtn2" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
           </div>
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
wtkSearchReplace('No data.','no emails sent with this filter');
wtkSearchReplace('&nbsp; &nbsp;</div>','</div>');
wtkSearchReplace('class="footerContent"','class="footerContent hide"');
$pgMsgsList = wtkBuildDataBrowse($pgSQL, [], 'wtkEmailsSent', '','Y');
$pgMsgsList = wtkReplace($pgMsgsList, 'border="0" cellpadding="10" cellspacing="0" id="templateHeader"','class="hide"');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="striped"','');
$pgMsgsList = wtkReplace($pgMsgsList, '&nbsp; &nbsp;</div>','</div>');
$pgMsgsList = wtkReplace($pgMsgsList, 'No data.','no emails sent with this filter');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="footerContent"','class="footerContent hide"');
$pgHtm .= $pgMsgsList . "\n";
$pgHtm .= '</div></div>' . "\n";

echo $pgHtm;
exit;
?>
