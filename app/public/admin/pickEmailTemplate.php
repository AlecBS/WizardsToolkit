<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
session_write_close();  // so rest of website still works
date_default_timezone_set('America/Los_Angeles'); // equivalent to MySQL 'US/Pacific'

$pgEmailType = wtkGetPost('Mode');
if ($pgEmailType == 'P'):
    $pgEmailGroup = 'Prospects';
else:
    $pgEmailGroup = 'Affiliates';
endif;

$pgSQL =<<<SQLVAR
SELECT `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'EmailType' AND `LookupValue` = ?
SQLVAR;
$pgTemplateType = wtkSqlGetOneResult($pgSQL, [$pgEmailType]);

$pgSQL =<<<SQLVAR
SELECT `EmailCode`, `Subject`
  FROM `wtkEmailTemplate`
 WHERE `EmailType` = :EmailType
    AND `DelDate` IS NULL
ORDER BY `UID` DESC
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
$pgSqlFilter = array('EmailType' => $pgEmailType);
$gloWTKmode = 'ADD';
$pgForm  = wtkFormSelect('wtkEmailTemplate', 'EmailCode', $pgSQL, $pgSqlFilter, 'Subject', 'EmailCode', 'Pick Email Template', 's12');
$pgDrop1 = wtkReplace($pgForm, 'wtkwtkEmailTemplateEmailCode','EmailCode');

// BEGIN allow choosing HTML template
$pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = :LookupType
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSqlFilter = array('LookupType' => 'EmailHTM');
$pgForm  = wtkFormSelect('wtkEmailTemplate', 'EmailHTM', $pgSQL, $pgSqlFilter, 'LookupDisplay', 'LookupValue', 'Pick HTML Template', 's12');
$pgDrop2 = wtkReplace($pgForm, 'wtkwtkEmailTemplateEmailHTM','EmailHTM');
//  END  allow choosing HTML template

if ($gloDbConnection == 'Live'):
    $pgDevNote = '';
else:
    $pgDevNote  = '<p id="techNote" class="blue-text">Since DB connection is ' . $gloDbConnection;
    $pgDevNote .= ' this will be sent to<br><b>' . $gloTechSupport . '</b> ($gloTechSupport)';
    if ($gloId == 0): // Bulk Emailing
        $pgDevNote .= '<br>and limited to sending only 1 email';
    endif;
    $pgDevNote .= '.</p>';
endif;
if ($gloRNG == 'SendAll'):
    $pgBulkMsg =<<<htmVAR
<p>Currently set to send up to 50 emails. Note that anyone who previuosly
 received this email template is excluded and will not receive the email again.</p>
htmVAR;
else:
    $gloRNG = 'SendOne';
    $pgBulkMsg  = '';
endif;

$pgHtm =<<<htmVAR
<div class="modal-content">
    <form id="FemailResults" name="FemailResults" class="card content b-shadow">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
        <div class="row">
            <div class="col s12">
                <p>Choose from any email template that has Email Type "$pgTemplateType".</p>
            </div>
            $pgDrop1
            $pgDrop2
            <div class="col s12">
                $pgBulkMsg
                $pgDevNote
            </div>
        </div>
        <div id="emailResults"></div>
    </form>
</div>
<div id="modFooter" class="modal-footer right">
    <a class="btn-small black b-shadow waves-effect waves-light modal-close">Close</a> &nbsp;&nbsp;
    <a id="adminSendBtn" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="JavaScript:adminEmailing('$pgEmailGroup',$gloId,'$gloRNG')">Send</a>
</div>
htmVAR;

echo $pgHtm;
exit;
?>
