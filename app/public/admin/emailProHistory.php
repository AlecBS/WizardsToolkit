<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT e.`UID`, DATE_FORMAT(e.`AddDate`, '%c/%e/%Y at %l:%i %p') AS 'SentDate',
  CONCAT(s.`FirstName`, ' ', COALESCE(s.`LastName`,'')) AS `SentTo`,
  IF (e.`EmailOpened` IS NULL, 'red', 'green') AS `OpenColor`,
  IF (e.`EmailOpened` IS NULL, 'not opened', DATE_FORMAT(e.`EmailOpened`, '$gloSqlDateTime')) AS `OpenStatus`,
  CASE
    WHEN e.`EmailLinkClicked` IS NOT NULL THEN 'green'
    WHEN e.`EmailOpened` IS NOT NULL THEN 'blue'
    ELSE 'yellow'
  END AS `HeaderColor`,
  e.`Subject`, e.`EmailAddress`, e.`EmailBody`
FROM `wtkEmailsSent` e
  LEFT OUTER JOIN `wtkProspectStaff` s ON s.`UID` = e.`OtherUID`
WHERE e.`EmailType` = :EmailType AND e.`OtherUID` = :OtherUID
 ORDER BY e.`UID` DESC
SQLVAR;

$pgSqlFilter = array (
    'OtherUID' => $gloId,
    'EmailType' => 'sales'
);

$gloRowsPerPage = 20;

// <div class="container">
$pgHtm =<<<htmVAR
<div class="modal-content">
    <h2>Prospect Email History</h2>
    <br><input type="hidden" id="HasTooltip" name="HasTooltip" value="Y">
    <div class="wtk-list card b-shadow">
htmVAR;

$pgDelBtn =<<<htmVAR
<a onclick="JavaScript:wtkDel('wtkEmailsSent',@UID@,'N','SPA');" class="btn btn-floating "><i class="material-icons">delete</i></a>
htmVAR;
$pgDelBtn = '';  // if you want ability to delete emails, comment out this line

$gloColHdr = '<th><h4>Past Emails Sent</h4></th>';
$gloRowHtm =<<<htmVAR
<td>
<div class="row valign-wrapper @HeaderColor@">
    <div class="col m4 s12">
        <strong>Sent:</strong> @SentDate@
    </div>
    <div class="col m4 s12">
        <strong>To:</strong> @SentTo@
    </div>
    <div class="col m4 s12 right-align">
        <div class="chip @OpenColor@ white-text tooltipped" data-tooltip="when opened">@OpenStatus@</div>
        <a class="btn btn-floating" onclick="JavaScript:wtkModal('/admin/emailView','EDIT',@UID@,@UID@)"><i class="material-icons small">remove_red_eye</i></a>
        $pgDelBtn
    </div>
</div>
<div class="row @HeaderColor@ lighten-4">
    <div class="col s12">
        <h4>Subject: @Subject@</h4>
        @EmailBody@
    </div>
</div>
</td>
htmVAR;
$pgMsgsList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkEmailsSent', '','Y');
$pgMsgsList = wtkReplace($pgMsgsList, 'border="0" cellpadding="10" cellspacing="0" id="templateHeader"','class="hide"');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="striped"','');
$pgMsgsList = wtkReplace($pgMsgsList, 'No data.','no emails sent yet');
$pgMsgsList = wtkReplace($pgMsgsList, 'class="footerContent"','class="footerContent hide"');
$pgHtm .= $pgMsgsList . "\n";
$pgHtm .=     '</div>' . "\n";
$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
