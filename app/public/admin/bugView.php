<?PHP
$pgSecurityLevel = 90;
$gloSiteDesign = 'MPA';
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSiteDesign = 'SPA';

$pgId = wtkGetParam('id');
$pgDate = wtkSqlDateFormat('b.`AddDate`','BugDate',$gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT b.`UID`, $pgDate, b.`CreatedByUserUID`,
  b.`OpSystem` AS `OperatingSystem`, b.`Browser`, b.`BrowserVer` AS `BrowserVersion`,
  b.`AppVersion`, b.`DeviceType`, b.`ReferralPage`, b.`BugMsg`, b.`InternalNote`,
  CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `User`, b.`IPaddress`,
  fncContactIcons(COALESCE(u.`Email`,u.`AltEmail`),COALESCE(u.`CellPhone`,u.`Phone`),0,0,'Y',u.`UID`,u.`SMSEnabled`,'N','') AS `Contact`
FROM `wtkBugReport` b
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = b.`CreatedByUserUID`
WHERE b.`UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$pgId]);
$pgBugMsg = wtkSqlValue('BugMsg');
$pgBugMsg = nl2br($pgBugMsg);
$pgBugDate = wtkSqlValue('BugDate');
$pgUserUID = wtkSqlValue('CreatedByUserUID');

$pgHtm =<<<htmVAR
<div class="container">
    <div class="row">
        <div class="col m7 s12">
            <h4>Feedback Detail</h4>
        </div>
        <div class="col m5 s12">
            <div class="right" style="margin-top:9px">
                $pgBugDate
            </div>
        </div>
    </div>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
                <div class="col s12">
                    <h6>Message From User
                        <a onclick="wtkModal('/admin/emailWriter','Start', $pgUserUID, $pgId);" class="btn right">Reply</a>
                    </h6><br>
                    $pgBugMsg
                    <br><hr>
                </div>
htmVAR;
$gloForceRO = true;
$pgHtm .= wtkFormText('wtkBugReport', 'User', 'text','','m4 s12');
$pgContact = wtkSqlValue('Contact');
$pgHtm .= '<div class="col m4 s12">' . $pgContact . '</div>' . "\n";
$pgHtm .= wtkFormText('wtkBugReport', 'IPaddress', 'text','IP Address','m4 s12');
$pgHtm .= '</div><div class="row">' . "\n";
$pgHtm .= wtkFormText('wtkBugReport', 'DeviceType');
$pgHtm .= wtkFormText('wtkBugReport', 'OperatingSystem');
$pgHtm .= wtkFormText('wtkBugReport', 'Browser');
$pgHtm .= wtkFormText('wtkBugReport', 'BrowserVersion');
$pgHtm .= wtkFormText('wtkBugReport', 'AppVersion');
$pgHtm .= wtkFormText('wtkBugReport', 'ReferralPage');
$pgHtm .= wtkFormText('wtkBugReport', 'InternalNote', 'text', '', 's12');
$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
if ($gloRNG == 1): // called from bugList so SPA, not MPA
    echo $pgHtm;
    exit;
endif;
$pgApiKey = wtkGetGet('apiKey');
if ($pgApiKey != ''): // clicked on messaage in email then logged in
    $pgHtm .=<<<htmVAR
<script type="text/javascript">
setTimeout(function() {
    pgApiKey = '$pgApiKey';
    console.log('apiKey set');
}, 900);
</script>
htmVAR;
endif;

wtkSearchReplace('col m4 offset-m4 s12','col m8 offset-m2 s12');
wtkMergePage($pgHtm, 'Bug Report', '../wtk/htm/minibox.htm');
?>
